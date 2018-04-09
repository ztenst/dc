<?php
namespace app\base\socket;

use Yii;
use \GatewayWorker\Lib\Gateway;
use \Exception;

class GatewaySocket extends Socket
{
    public $registerAddress;

    public function init()
    {
        parent::init();
        set_exception_handler([$this, 'handleException']);
        Gateway::$registerAddress = $this->registerAddress;
    }

    public function handleException($exception)
    {
        Yii::error('调用gateway失败:'.$exception->getMessage());
        restore_exception_handler();
    }

    public function bindUid($clientId, $uid)
    {
        Gateway::bindUid($clientId, $uid);
    }

    public function joinGroup($clientId, $group)
    {
        Gateway::joinGroup($clientId, $group);
    }

    public function getClientCountByGroup($group)
    {
        return Gateway::getClientCountByGroup($group);
    }

    public function getAllClientCount()
    {
        return Gateway::getAllClientCount();
    }

    public function sendToAll($data)
    {
        Gateway::sendToAll($data);
    }

    public function sendToUid($uid, $data)
    {
        Gateway::sendToUid($uid, $data);
    }

    public function sendToGroup($group, $message)
    {
        Gateway::sendToGroup($group, $message);
    }

    public function setSession($clientId, $sessionData = [])
    {
        $sessionData = (array)$sessionData;
        Gateway::setSession($clientId, $sessionData);
    }
}
