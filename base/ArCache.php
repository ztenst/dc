<?php
namespace app\base;

use Yii;
use yii\base\Object;
use yii\db\ActiveRecordInterface;

class ArCache extends Object
{
    protected $server;

    public function __construct($keyPrefix, array $config = [])
    {
        parent::__construct($config);
        $this->server = Yii::$app->cache;
        $this->server->keyPrefix = $keyPrefix;
    }

    public function setArCache(ActiveRecordInterface $activeRecord)
    {
        $value = $activeRecord->toArray();

        $ownerName = $this->getOwnerName($activeRecord);

        $caches = $this->server->get($ownerName);

        $key = $activeRecord->getPrimaryKey();

        if($caches){
            if(array_key_exists($key, $caches)){
                $caches[$key] = $value;
            }else{
                array_push($caches,[$key => $value ]);
            }
        }else{
            $caches = [$key => $value];
        }

        $this->server->set($ownerName, $caches);

        return  $caches[$key];
    }

    public function getArCache($key, ActiveRecordInterface $activerecord)
    {
        $caches = $this->server->get($this->getOwnerName($activerecord));

        if($caches) {
            if (array_key_exists($key, $caches)) {
                return $caches[$key];
            }
        }

        return false;
    }

    public function getArCaches(ActiveRecordInterface $activerecord)
    {
        return $this->server->get($this->getOwnerName($activerecord));
    }

    public function deleteArCache($key, ActiveRecordInterface $activerecord)
    {
        $ownerName = $this->getOwnerName($activerecord);

        $caches = $this->server->get($ownerName);

        $data = $this->getArCache($key, $activerecord);

        if($data){
            unset($caches[$key]);
        }

        return $this->server->set($ownerName, $caches);
    }

    protected function getOwnerName(ActiveRecord $activeRecord)
    {
        $reflect = new \ReflectionClass($activeRecord);
        return $reflect->getShortName();
    }

}
