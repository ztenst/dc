<?php

namespace app\models\ext;

use Yii;

/**
 *  规格
 */
class ShopAttr extends \app\models\ShopAttr
{

    public function getValues()
    {
        return $this->hasMany(ShopAttrValue::className(),['attr_id' => 'id']);
    }
}
