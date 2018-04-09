<?php
namespace api\modules\canting\controllers;

use api\modules\canting\filters\ShopFilter;
use api\modules\canting\models\ShopMenuCate;
use api\modules\canting\models\ShopUser;
use app\models\ext\ShopActiveDesk;
use app\models\ext\ShopDesk;
use app\models\ext\ShopShop;
use app\models\ext\SmsCode;
use app\services\WsService;
use Yii;
use yii\web\BadRequestHttpException;

class ShopController extends Controller
{
    public $shop;

    public $shopUser;

    public function verbs()
    {
        return [
            'open' => ['post'],
            'userphone' => ['post'],
            'saveuser' => ['post'],
            'join-desk' => ['post'],
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
        $shop_user = ShopUser::findUser($this->user->id, $this->shop['id']);;
        if(is_null($shop_user)){
            $shop_user = new ShopUser();
            $shop_user->scenario = ShopUser::SCENARIO_USER_PHONE;
            $shop_user->user_id = $this->user->id;
            $shop_user->shop_id = $this->shop['id'];
            $shop_user->save();
        }
        $this->shopUser = $shop_user;
        return true;
    }

    public function actionJoinDesk()
    {
        $desk_id = Yii::$app->request->get('desk_id');
        $client_id = Yii::$app->request->getBodyParam('client_id');
        $shop_desk = ShopDesk::find()->shop($this->shop['id'])->whereId($desk_id)->one();
        if(!$client_id || !$shop_desk){
            throw new BadRequestHttpException('请求错误');
        }
        //绑定
        try {
            $ws = new WsService();
            $ws->bindShopUserId($client_id, $this->user->id);
            $ws->joinShopDeskGroup($client_id, $desk_id);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
        return true;

    }

    //空桌的情况开始开桌
    public function actionOpen()
    {
        $desk_id = Yii::$app->request->get('desk_id');
        $body = Yii::$app->request->getBodyParams();
        $shop_id = $this->shop['id'];
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            $shop_desk = ShopDesk::find()->shop($shop_id)->whereId($desk_id)->one();
            if(is_null($shop_desk)){
                throw new BadRequestHttpException('餐桌不存在');
            }
            if($shop_desk->status != ShopDesk::STATUS_EMPTY){
                return true;
            }
            $attributes = [
                'desk_id' => $desk_id,
                'shop_id' => $shop_id,
                'people_num' => $body['people_num'],
                'user_id' => $this->user->id
            ];
            $shop_active_desk =  new ShopActiveDesk();
            $shop_active_desk->attributes = $attributes;
            if(!$shop_active_desk->save()){
                throw new BadRequestHttpException('开桌失败');
            }
            //并桌加入流水
            $shop_desks = ShopDesk::find()->shop($shop_id)->merge($desk_id)->all();
            if($shop_desks){
                foreach ($shop_desks as $item){
                    $merge_attributes = [
                        'desk_id' => $item->id,
                        'shop_id' => $shop_id,
                        'people_num' => $body['people_num'],
                        'user_id' => $this->user->id,
                        'merge_active_id' => $shop_active_desk->id
                    ];
                    $merge_shop_active_desk = new ShopActiveDesk();
                    $merge_shop_active_desk->attributes = $merge_attributes;
                    if(!$merge_shop_active_desk->save()){
                        throw new BadRequestHttpException('开桌失败');
                    }
                }
            }
            $shop_desk->status = ShopDesk::STATUS_ORDER;
            $shop_desk->active_desk_id = $shop_active_desk->id;
            if(!$shop_desk->save()){
                throw new BadRequestHttpException('开桌失败');
            }
            $transaction->commit();
            //cache中加入开桌后这桌是否正在下单 解决几个人同时下单这种并发操作
            Yii::$app->cache->set('rms_shop_cart_commit_'.$shop_active_desk->id,false);

            $ws = new WsService();
            $ws->pushRefreshIndex(ShopShop::findOne($shop_id));
            $ws->pushOpenDesk($desk_id);
            return true;
        }catch (BadRequestHttpException $e){
            $transaction->rollBack();
            throw $e;
        }
    }

    //点餐菜单
    public function actionHome()
    {
        $shop_menu_cate = ShopMenuCate::find()
            ->with('menus')
            ->shop($this->shop['id'])
            ->andWhere(['show_type'=>ShopMenuCate::SHOW_ALL])
            ->all();
        $menu_cates = [];
        foreach ($shop_menu_cate as $i => $model) {
            $menu_cates[$i] =  $model->toArray();
        }
        $recommends = [];
        foreach ($menu_cates as $menu_cate){
            foreach ($menu_cate['menus'] as $menu){
                if($menu['is_recommend']){
                    array_push($recommends, $menu);
                }
            }
        }
        if($recommends) {
            array_unshift($menu_cates, [
                'name' => '推荐',
                'id' => 0,
                'menus' => $recommends
            ]);
        }
        return [
            'shop' => $this->shop,
            'menu_cates' => $menu_cates
        ];
    }

    //绑定手机
    public function actionUserphone()
    {
        $body = Yii::$app->request->getBodyParams();

        if(!isset($body['phone']) || !isset($body['code'])){
            throw new BadRequestHttpException('参数错误');
        }
        //5分钟内有效 当前时间-5分钟时间 < created
        $activeTime = time() - 300;
        $smsCode = SmsCode::find()
            ->code($body['code'])
            ->phone($body['phone'])
            ->status(0)
            ->andFilterWhere(['>=','created',$activeTime])
            ->one();
        if(!$smsCode){
            throw new BadRequestHttpException('验证码错误或已过期');
        }
        $smsCode->status = 1;
        $smsCode->save();

        $this->shopUser->phone = $body['phone'];
        $this->shopUser->scenario = ShopUser::SCENARIO_USER_PHONE;
        if(!$this->shopUser->save()){
            throw new BadRequestHttpException(current($this->shopUser->getErrors())[0]);
        }
        return '保存成功';
    }

    //保存个人信息
    public function actionSaveuser()
    {
        $sex = Yii::$app->request->getBodyParam('sex');
        $birthday = Yii::$app->request->getBodyParam('birthday');
        $birthday = $birthday ? strtotime($birthday) : 0;

        $this->shopUser->attributes = [
            'sex'=>$sex,
            'birthday' => $birthday
        ];
        $this->shopUser->scenario = ShopUser::SCENARIO_SHOP_EDIT;
        if(!$this->shopUser->save()){
            throw new BadRequestHttpException(current($this->shopUser->getErrors())[0]);
        }
        return '保存成功';
    }

    public function actionUser()
    {
        return $this->shopUser;
    }

}
