<?php
namespace api\modules\canting\models;

use Yii;

class ShopMenu extends \app\models\ext\ShopMenu
{
    public function fields()
    {
        return [
            'id',
            'name',
            'price',
            'stock',
            'image' => function($model){
                return $model->getImage();
            },
            'sale',
            'unit'=>function($model){
                return $model->unit->name;
            },
            'attrs'=>function($model){
                return $model->getConfigField('attrs');
            }
        ];
    }
}