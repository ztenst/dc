<?php
namespace app\helpers;

use Yii;

class Storage
{
    public static function getStorage()
    {
        return Yii::$app->storage;
    }

    public static function fixFileUrl($value)
    {
        return self::getStorage()->fixFileUrl($value);
    }

    public static function fixImageUrl($value, $width=0, $height=0)
    {
        return self::getStorage()->fixImageUrl($value, $width, $height);
    }
}
