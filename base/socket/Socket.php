<?php
namespace app\base\socket;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use \Exception;

class Socket extends Component
{
    /**
     * 客户端连接socket服务端的地址
     */
    public $serverAddress;
    //可选swoole、gateway
    public $driverName = 'gateway';
    /**
     * 单例
     * @var Socket
     */
    public static $driver;
    /**
     *  工厂方法
     *  @return Socket
     */
    public static function createSocketDriver($config, $params=[])
    {
        if(self::$driver===null) {
            $config['driverName'] = $driverName = ArrayHelper::remove($config, 'driverName');
            $class = self::getBuiltInDriver($driverName);
            $config['class'] = $class;
            self::$driver = Yii::createObject($config, $params);
        }
        return self::$driver;
    }

    public static function getBuiltInDriver($driverName = null)
    {
        $drivers = [
            'gateway' => __NAMESPACE__  . '\GatewaySocket',
            'swoole' => __NAMESPACE__ . '\Swoole',
        ];
        if($driverName!==null) {
            if(!isset($drivers[$driverName])) {
                throw new Exception('未找到文件驱动类');
            }
            return $drivers[$driverName];
        } else {
            return $drivers;
        }
    }

    /**
     * 绑定客户端id与用户id
     * - 客户端id: 指网页客户端（浏览器端）与socket建立的长连接
     */
    public function bindUid($clientId, $uid)
    {
        throw new Exception('该方法需要重写');
    }
}
