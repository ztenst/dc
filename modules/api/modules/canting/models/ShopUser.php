<?php

namespace api\modules\canting\models;

class ShopUser extends \app\models\ext\ShopUser
{
    public function fields()
    {
        return [
            'phone',
            'sex',
            'birthday' => function($model){
                return $model->birthday ? date('Y-m-d',$model->birthday) :'';
            }
        ];
    }

}
