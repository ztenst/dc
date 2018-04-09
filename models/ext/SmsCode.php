<?php

namespace app\models\ext;

use Yii;

class SmsCode extends \app\models\SmsCode
{
    /**
     * @param int $randLength
     * @return string
     */
    public static function getCode($randLength = 6){
        $chars = '0123456789';
        $len = strlen($chars);
        $randStr = '';
        for ($i = 0; $i < $randLength; $i++) {
            $randStr.=$chars[rand(0, $len - 1)];
        }
        return $randStr;
    }
}
