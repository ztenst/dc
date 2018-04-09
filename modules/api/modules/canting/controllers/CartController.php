<?php
namespace api\modules\canting\controllers;

use api\modules\canting\components\ShopCart;
use api\modules\canting\filters\ShopFilter;
use api\modules\canting\models\ShopMenu;
use app\models\ext\ShopActiveDesk;
use app\models\ext\ShopDesk;
use app\models\ext\ShopShop;
use app\models\ext\UserOrder;
use app\models\ext\UserOrderMenu;
use app\models\ext\UserOrderUser;
use app\services\WsService;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;

class CartController extends Controller
{
    public $shop;

    private $shopDesk;

    private $cart;

    private $ws;

    public function verbs()
    {
        return [
            'add' => ['post'],
            'submit' => ['post'],
            'update' => ['post'],
            'remove' => ['post'],
            'bind' => ['post'],
            '*' => ['get']
        ];
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'shop' => [
                'class' => ShopFilter::className()
            ]
        ]);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $shop_id = Yii::$app->request->get('shop_id','');
        $desk_id = Yii::$app->request->get('desk_id','');
        $shop_desk = ShopDesk::find()->shop($shop_id)->whereId($desk_id)->nonEmpty()->one();
        if(is_null($shop_desk)){
            throw new BadRequestHttpException('请先选择就餐人数');
        }
        $this->shopDesk = $shop_desk;
        $this->ws = new WsService();
        $this->cart = new ShopCart(md5($shop_desk->active_desk_id));
        return true;
    }

    /**
     * websocket mvc后端uid和client_id绑定
     */
    public function actionBind()
    {
        $client_id = Yii::$app->request->getBodyParam('client_id');
        if(!$client_id){
            throw new BadRequestHttpException('请求错误');
        }
        //绑定
        try {
            $this->ws->bindShopUserId($client_id, $this->user->id);
            $this->ws->joinShopDeskGroup($client_id,$this->shopDesk->id);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
        return true;
    }

    public function actionList()
    {

        $list = [];
        foreach ($this->cart as $key => $value){
            $specs = [];
            $attrs = [];
            if($value['attrValue'] && is_array($value['attrValue'])){
                foreach ($value['attrValue'] as $attr){
                    if($attr['name'] == '尺寸'){
                        $specs[] = $attr;
                    }else{
                        $attrs[] = $attr;
                    }
                }
            }
            $item = [
                'num' => $value['qty'],
                'id' => $value['id'],
                'name' => $value['name'],
                'price' =>$value['price'],
                'specs' => $specs,
                'attrs' => $attrs,
                'rawId' => $key
            ];
            $list[] = $item;
        }

        return [
            'list' =>[$this->shop['id'] => [
                $this->shopDesk->id => $list
            ]],
            'count' => $this->cart->count(),
            'totalPrice' => $this->cart->totalPrice(),
            'desk_number' => $this->shopDesk->number
        ];
    }

    public function actionAdd()
    {
        //菜单ID
        $id = (int)Yii::$app->request->getBodyParam('id');
        //点的数量
        $qty = (int)Yii::$app->request->getBodyParam('qty');
        //规格 eg: [['name'=>'尺寸','value'=>'半份']]
        $attrs = Yii::$app->request->getBodyParam('attrs');

        $menu = $this->loadMenu($id, $qty);

        //判断传过来的规格是否带有尺寸 将尺寸的价格替换menu的price
        if($attrs){
            $attrs = $this->formatAttrs($attrs);
            $size = null;
            foreach ($attrs as $attr){
                if($attr['name'] == '尺寸'){
                    $size = $attr['value'];
                }
            }
            if($size){
                $configAttrs = $menu->getConfigField('attrs');
                if($sizeAttrValues =  $configAttrs ['sizeAttr']['attrValues']){
                    foreach ($sizeAttrValues as $sizeAttrValue){
                        if($sizeAttrValue['name'] == $size){
                            $menu->price = $sizeAttrValue['price'];
                            break;
                        }
                    }
                }
            }
        }

        $field = $this->cart->generateField($id, $attrs);

        $message = [
            'user_name' => $this->user->nickname,
            'user_avatar' => $this->user->avatar
        ];

        if($this->cart->hasField($field)){
            $cart_menu = $this->cart->getField($field);
            $this->cart->editField($field, $qty);
            $message['menu_id'] = $cart_menu['id'];
            $message['menu_name'] = $cart_menu['name'];
            $message['menu_qty'] = $qty - $cart_menu['qty'];
        }else{
            $this->cart->addField($id, $this->user->id, $menu->name, $menu->price, $qty, $attrs);
            $message['menu_id'] = $menu->id;
            $message['menu_name'] = $menu->name;
            $message['menu_qty'] = $qty;
        }
        //socket
        $this->ws->pushAddCart($this->shopDesk->id,$message);

        return true;
    }

    public function actionRemove()
    {
        //清空购物车socket
        $this->ws->pushRemoveCart($this->shopDesk->id);
        return $this->cart->removeAll();
    }

    //下单 还没考虑加菜订单
    public function actionSubmit()
    {
        //循环缓存中的购物车数据保存
        $list = $this->cart->all();
        if(empty($list)){
            throw new BadRequestHttpException('购物车不能为空');
        }
        $commit_key = 'rms_shop_cart_commit_'.$this->shopDesk->active_desk_id;
        $is_commit = Yii::$app->cache->get($commit_key);
        if($is_commit){
            throw new BadRequestHttpException('有其它用户正在提交，请稍后');
        }
        Yii::$app->cache->set($commit_key,true);

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            $shop_active_desk = ShopActiveDesk::findOne($this->shopDesk->active_desk_id);
            //加菜
            if($shop_active_desk->order_id){
               $user_order = UserOrder::findOne($shop_active_desk->order_id);
               if(is_null($user_order)){
                    throw new BadRequestHttpException('订单不存在');
               }
                $user_order->user_id = $this->user->id;
                $user_order->status = UserOrder::STATUS_ADD_SUBMIT;
                //记录状态
                $user_order->addStatusRecord(UserOrder::STATUS_ADD_SUBMIT);
                if(!$user_order->save()){
                    throw new BadRequestHttpException(current($user_order->getErrors())[0]);
                }
            }else{
                $user_order = new UserOrder();
                $attribute_order = [
                    'desk_id' => $this->shopDesk->id,
                    'desk_number' => $this->shopDesk->number,
                    'shop_id' => $this->shopDesk->shop_id,
                    'user_id' => $this->user->id,
                    'total_price' => 0,
                    'status' =>  UserOrder::STATUS_SUBMIT
                ];
                $user_order->attributes = $attribute_order;
                $user_order->addStatusRecord(UserOrder::STATUS_SUBMIT);
                if(!$user_order->save()){
                    throw new BadRequestHttpException(current($user_order->getErrors())[0]);
                }
                $shop_active_desk->order_id = $user_order->id;
                if(!$shop_active_desk->save()){
                    throw new BadRequestHttpException(current($shop_active_desk->getErrors())[0]);
                }
            }
            //批次
            $addNo = $this->getAddno();

            $user_ids = [$this->user->id];
            foreach ($list as $item){
                $this->loadMenu($item['id'], $item['qty']);
                $user_order_menu = new UserOrderMenu();
                $attribute_order_menu = [
                    'order_id' => $user_order->id,
                    'user_id' => $item['user_id'],
                    'menu_id' => $item['id'],
                    'menu_name' => $item['name'],
                    'menu_price' => $item['price'],
                    'menu_num' =>$item['qty'],
                    'menu_attr_info' => $item['attrValue'],
                    'add_no' => $addNo
                ];
                $user_order_menu->attributes = $attribute_order_menu;
                if(!$user_order_menu->save()){
                    throw new BadRequestHttpException(current($user_order_menu->getErrors())[0]);
                }
                array_push($user_ids,$item['user_id']);
            }

            $user_ids = array_unique($user_ids);
            //去除已经存在的用户ID 主要在加菜的订单中处理
            if($shop_active_desk->order_id) {
                $user_order_users = UserOrderUser::find()->order($user_order->id)->all();
                if (!is_null($user_order_users)) {
                    $user_ids_exist = ArrayHelper::getColumn($user_order_users, 'user_id');
                    $user_ids = array_diff($user_ids, $user_ids_exist);
                }
            }
            if($user_ids) {
                foreach ($user_ids as $user_id) {
                    $user_order_user = new UserOrderUser();
                    $user_order_user->order_id = $user_order->id;
                    $user_order_user->user_id = $user_id;
                    $user_order_user->save();
                }
            }

            //餐桌状态改为待确认
            $this->shopDesk->status = ShopDesk::STATUS_WAIT_COMFIRM;
            if(!$this->shopDesk->save()){
                throw new BadRequestHttpException(current($this->shopDesk->getErrors())[0]);
            }
            //清空购物车
            $this->cart->removeAll();
            $transaction->commit();
            $shop = ShopShop::findOne($this->shop['id']);
            //socket
            $this->ws->pushCommitOrder($this->shopDesk->id);
            $this->ws->pushRefreshDeskInfo($shop);
            $this->ws->pushRefreshIndex($shop);

            Yii::$app->cache->set($commit_key,false);
        }catch (BadRequestHttpException $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw new BadRequestHttpException('订单提交失败');
        }
        return 'success';
    }

    /**
     * 获取菜单
     * @param $id
     * @param $qty
     * @return array|null|\yii\db\ActiveRecord
     * @throws BadRequestHttpException
     */
    protected  function loadMenu($id , $qty)
    {
        $shop_menu = ShopMenu::find()
            ->status()
            ->whereId($id)
            ->shop($this->shopDesk->shop_id)
            ->one();
        if(is_null($shop_menu)){
            throw new BadRequestHttpException('菜单不存在');
        }
        //这里只做判断，不做扣减，扣减放到后台确认点单再判断
        //避免到处要写扣了之后又要加回去的逻辑
        if(($stock=$shop_menu->getTodayStock()) < $qty) {
            throw new BadRequestHttpException($shop_menu->name . '库存不足，剩余'. $stock);
        }
        return $shop_menu;
    }

    /**
     * 格式化从前台传来的规格值
     * @param array $attrs
     * @return array
     */
    protected function formatAttrs(array $attrs)
    {
        $return = [];
        foreach ($attrs as $key => $attr){
            $return[$key] = [
                'name' => $attr['name'],
                'value' => $attr['value']
            ];
        }
        return $return;
    }

    /**
     * 获取批次 使用缓存保存批次
     * @return int|mixed
     */
    protected function getAddno()
    {
        $cache = Yii::$app->cache;
        $commitCacheKey = 'rms_shop_cart_commit_no_'.$this->shopDesk->active_desk_id;
        $addNo = $cache->get($commitCacheKey);
        if($addNo === false){
            $addNo = 1;
            $cache->set($commitCacheKey,$addNo);
        }else{
            $addNo++;
            $cache->set($commitCacheKey,$addNo);
        }
        return $addNo;
    }
}
