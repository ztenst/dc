<?php

namespace app\models\ext;

use Yii;

class UserOrderUser extends \app\models\UserOrderUser
{
    const TYPE_ORDER = 1; //点单
    const TYPE_PLACE_ORDER = 2; //下单

    public function getOrder()
    {
        return $this->hasOne(UserOrder::className(),['id' => 'order_id']);
    }

}
