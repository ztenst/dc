<?php
namespace api\modules\canting\controllers;

use api\modules\canting\models\UserOrder;
use app\models\ext\ShopActiveDesk;
use app\models\ext\ShopDesk;
use app\models\ext\UserOrderUser;
use Yii;
use yii\web\BadRequestHttpException;

class OrderController extends Controller
{
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
        print_r($data);die;
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            $total_price = 0;
            $user_order = new UserOrder();
            $attribute_order = [
                'shop_id' => 1,
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
