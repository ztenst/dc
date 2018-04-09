<?php

namespace app\base\sms;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;

/**
 * SMS短信控件
 * @package app\base
 */
class Sms extends Component
{
    /**
     * [
     *    'chuanlan' => [
     *         'account' => '',
     *         'password' => ''
     *     ]
     *  ]
     * @var array  配置
     */
    public $config = [];
    /**
     * 默认的发送方式
     * 必须在config中已配置的方式
     * @var string
     */
    public $default;
    /**
     * 存储provider对象
     * @var array
     */
    protected $providers = [];

    public function init()
    {
        parent::init();
        $this->setDefault($this->default);
    }

    public function send($to, $message)
    {
        //获取短信提供商发送短信
        return $this->provider($this->default)->send($to, $message, $this->config[$this->default]);
    }

    public function provider($name)
    {
        if(!isset($this->providers[$name])){
            $this->providers[$name] = $this->createProvider($name);
        }
        return $this->providers[$name];
    }

    protected function createProvider($name)
    {
        $classname = __NAMESPACE__.'\\providers\\'.ucfirst($name).'Provider';

        if(!class_exists($classname)){
            throw new UnknownClassException(sprintf('%sProvider class not exist.',ucfirst($name)));
        }
        return new $classname();
    }
    

    public function setDefault($name)
    {
        if(!isset($this->config[$name])){
            throw new InvalidConfigException(sprintf('%s is not configured in $config.',$name));
        }
        $this->default = $name;
        return $this;
    }

}
