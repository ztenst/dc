<?php

namespace api\modules\canting\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\redis\Connection;

/**
 * 使用redis来保存登录状态的信息
 */
class User extends Component
{
    public $redis = 'redis';
    //过期时间30天
    public $duration = 2592000;

    public $keyPrefix;

    public function init()
    {
        if(is_string($this->redis)){
            $this->redis = Yii::$app->get($this->redis);
        }elseif (is_array($this->redis)){
            if(!isset($this->redis)){
                $this->redis['class'] = Connection::className();
            }
        }
        if(!$this->redis instanceof Connection){
            throw new InvalidConfigException("User::redis must be either a Redis connection instance or the application component ID of a Redis connection.");
        }
        parent::init();
    }

    public function buildKey($key)
    {
        return $this->keyPrefix.'_'.$key;
    }

    protected function generateKey($value)
    {
        return md5($value . time());
    }

    /**
     * eg: {
     *    "openid" : "OPENID", 用户唯一标识
     *    "session_key" : "" 会话密钥,
     * }
     * @param array $value
     * @param int $duration
     * @return boolean | string
     */
    public function add($value = [] , $duration = 0)
    {
        $value = json_encode($value);

        $key =  $this->generateKey($value);

        $boolean =  (boolean)$this->redis->executeCommand('SET', [
            $this->buildKey($key) ,
            $value ,
            'EX',
            $duration > 0 ? $duration : $this->duration,
            'NX'
        ]);
        if($boolean){
            return $key;
        }
        return $boolean;
    }

    public function edit($key , $value = []){

        $key = $this->buildKey($key);
        //获取剩余时间
        $expire_time = $this->redis->executeCommand('TTL',[ $key ]);

        if($expire_time <= 0){
            return false;
        }

        return (boolean)$this->redis->executeCommand('SET',[
            $key ,
            json_encode($value),
            'EX',
            $expire_time,
            'XX'
        ]);
    }

    public function destroy($key)
    {
        $this->buildKey($key);
        $this->redis->executeCommand('DEL', [$key]);
        return true;
    }

    public function get($key){
        $key = $this->buildKey($key);
        $data = $this->redis->executeCommand('GET', [$key]);
        return $data === false || $data === null ? '' : json_decode($data,true);
    }

    public function exist($key){
        $key = $this->buildKey($key);
        return (bool) $this->redis->executeCommand('EXISTS', [$key]);
    }
}
