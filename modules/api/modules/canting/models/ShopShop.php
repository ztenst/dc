<?php

namespace api\modules\canting\models;

use Yii;

class ShopShop extends \app\models\ext\ShopShop
{

    public function fields()
    {
        return [
            'id',
            'name',
            'city' => function($model){
                return $model->city->name;
            },
            'created' => function($model){
                return $model->getFormatCreated();
            },
            'address' => function($model){
                return $model->shopInfo->address;
            },
            'logo' => function($model){
                return '/images/logo.png';
            },
            'description' => function($model){
                return $model->shopInfo->description;
            }
        ];
    }

}
