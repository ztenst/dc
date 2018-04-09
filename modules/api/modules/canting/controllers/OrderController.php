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
            '*' => ['get']
        ];
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
