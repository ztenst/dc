<?php

namespace app\models\ext;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "shop_setting".
 * @vserion 2017-06-27 09:25:02通过gii生成
 */
class ShopSetting extends \app\models\ShopSetting
{
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'encodeValue']);
        $this->on(self::EVENT_AFTER_FIND, [$this, 'decodeValue']);
    }

    public function decodeValue()
    {
        if(is_string($this->value) && ($value = Json::decode($this->value))) {
            $this->value = $value;
        }
    }

    public function encodeValue()
    {
        $this->value = Json::encode($this->value);
    }
}
