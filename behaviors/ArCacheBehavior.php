<?php
namespace app\behaviors;

use app\base\ArCache;
use Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;

class ArCacheBehavior extends Behavior
{
    public $keyPrefix = 'rms_shop_arcache_';

    public function events()
    {
        return array_fill_keys([
            BaseActiveRecord::EVENT_AFTER_UPDATE,
            BaseActiveRecord::EVENT_AFTER_DELETE
        ],'deleteArCache');
    }

    public function deleteArCache()
    {
        $arCache = new ArCache($this->keyPrefix);

        $key = $this->owner->getPrimaryKey();

        return $arCache->deleteArCache($key, $this->owner);
    }

}