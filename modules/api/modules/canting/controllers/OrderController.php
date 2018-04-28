<?php
namespace api\modules\canting\controllers;

use api\modules\canting\components\WxPay;
use api\modules\canting\models\ShopMenu;
use api\modules\canting\models\UserOrder;
use app\models\ext\ShopActiveDesk;
use app\models\ext\ShopDesk;
use app\models\ext\UserOrderMenu;
use app\models\ext\UserOrderUser;
use Yii;
use yii\web\BadRequestHttpException;

class OrderController extends Controller
{   
    public $shop_id = 1;
    
    public function verbs()
    {
        return [
            'add' => ['post'],
            '*' => ['get']
        ];
    }
    
    public function actionAdd()
    {
        $data = Yii::$app->request->post();
        if(!$data){
            throw new BadRequestHttpException('购物车不能为空');
        }
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            $total_price = 0;
            foreach ($data as $item){
                $total_price += $item['num'] * $item['price'];
            }
            $user_order = new UserOrder();
            $user_order->shop_id = $this->shop_id;
            $user_order->user_id = $this->user->id;
            $user_order->total_price = $total_price;
            $user_order->status = UserOrder::STATUS_NO;
            if(!$user_order->save()){
                throw new BadRequestHttpException(current($user_order->getFirstErrors()));
            }
            foreach ($data as $item){
                $this->loadMenu($item['id'], $item['num']);
                $user_order_menu = new UserOrderMenu();
                $user_order_menu->order_id = $user_order->id;
                $user_order_menu->menu_id = $item['id'];
                $user_order_menu->menu_name = $item['name'];
                $user_order_menu->menu_price = $item['price'];
                $user_order_menu->menu_num = $item['num'];
                $user_order_menu->menu_attr_info = $item['attrs'];
                if(!$user_order_menu->save()){
                    throw new BadRequestHttpException(current($user_order_menu->getFirstErrors()));
                }
            }
            $transaction->commit();
            $wxPay = new WxPay();
            $res = $wxPay->setPay('购买支付', $total_price, $this->user->openid);
        }catch (BadRequestHttpException $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw new BadRequestHttpException('订单提交失败');
        }
        return $res;
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
            ->shop($this->shop_id)
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
    
    //订单详情
    public function actionItem()
    {
      $shop_id = Yii::$app->request->get('shop_id');
      $desk_id = Yii::$app->request->get('desk_id');

      $shop_desk = ShopDesk::find()->shop($shop_id)->whereId($desk_id)->one();
      if(is_null($shop_desk)){
          throw new BadRequestHttpException('餐桌不存在');
      }

      $shop_active_desk = ShopActiveDesk::findOne($shop_desk->active_desk_id);
      if(is_null($shop_active_desk) || empty($shop_active_desk->order_id)){
          throw new BadRequestHttpException('订单不存在');
      }
      $user_order_user  =  UserOrderUser::find()
                                    ->order($shop_active_desk->order_id)
                                    ->user($this->user->id)
                                    ->one();
      if(is_null($user_order_user)){
          throw new BadRequestHttpException('订单不存在');
      }
      return UserOrder::findOne($shop_active_desk->order_id);
    }

    //订单状态
    public function actionStatus()
    {
        $id = Yii::$app->request->get('id');
        $user_order_user = UserOrderUser::find()->with('order')->order($id)->user($this->user->id)->one();
        if(is_null($user_order_user)){
            throw new BadRequestHttpException('订单不存在');
        }
        //处理订单
        $statusArray = $user_order_user->order->getFormatStatusRecord();
        if($statusArray) {
            $assetUrl = 'http://pic.hangjiayun.com/rms/assets/images/';
            $iconArray = [
                UserOrder::STATUS_SUBMIT => $assetUrl . 'icon-FSsubmit.png',
                UserOrder::STATUS_CONFIRM => $assetUrl . 'icon-FSsureorder.png',
                UserOrder::STATUS_ADD_SUBMIT => $assetUrl . 'icon-FSjiacai.png',
                UserOrder::STATUS_ADD_CONFIRM => $assetUrl . 'icon-FSjiacai.png',
                UserOrder::STATUS_TO_BE_PAID => $assetUrl . 'icon-FSwaitpay.png',
                UserOrder::STATUS_PAID => $assetUrl . 'icon-FSpay.png'
            ];

            foreach ($statusArray as &$status) {
                $status['icon'] = $iconArray[$status['status']];
            }
            /*$endStatus = end($statusArray);
            if($endStatus < 5){
                $statusArray = array_merge($statusArray, [
                    [
                        'msg' => UserOrder::$statusArray[UserOrder::STATUS_TO_BE_PAID],
                        'icon' => $assetUrl.'icon-nowaitpay.png'
                    ],
                    [
                        'msg' => UserOrder::$statusArray[UserOrder::STATUS_PAID],
                        'icon' => $assetUrl.'icon-nopay.png'
                    ]
                ]);
            }elseif ($endStatus == 5){
            }*/
        }
        return $statusArray;
    }

}
