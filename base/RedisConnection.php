<?php
namespace app\base;

use yii\redis\Connection;
use \Redis;

/**
 * RedisConnection
 * @author weibaqiu
 * @version 2017-06-12
 */
class RedisConnection extends Connection
{
    //
    // private $_prefix;
    //
    // /**
    //  * use custom prefix on all keys
    //  * @param string $value custom prefix name
    //  */
    // public function setPrefix($value)
    // {
    //     if(is_callable($value)) {
    //         $value = call_user_func($value);
    //     }
    //     $this->_prefix = (string)$value;
    //     if($this->_prefix) {
    //         $this->setOption(Redis::OPT_PREFIX, $this->_prefix);
    //     }
    // }
    //
    // public function getPrefix()
    // {
    //     return $this->_prefix;
    // }
}
