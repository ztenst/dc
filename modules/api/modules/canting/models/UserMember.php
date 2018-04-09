<?php

namespace api\modules\canting\models;

class UserMember extends \app\models\ext\UserMember
{
    public function fields()
    {
        return [
            'nickname',
            'avatar',
            'orderCount' => function($model){
                return $model->ordersCount;
            }
        ];
    }
}
