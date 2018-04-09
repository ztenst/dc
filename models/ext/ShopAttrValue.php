<?php

namespace app\models\ext;

use Yii;

/**
 *  规格值
 */
class ShopAttrValue extends \app\models\ShopAttrValue
{

    public function getAttr()
    {
        return $this->hasOne(ShopAttr::className(),['id'=>'attr_id']);
    }

    public function getMenu()
    {
        return $this->hasOne(ShopMenu::className(),['id' => 'menu_id']);
    }

}
