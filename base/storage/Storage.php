<?php
namespace app\base\storage;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use \Exception;

/**
 * 配置方法：
 * [
 *  'components' =>
 *      'storage' => function(){
 *          return app\base\storage\Storage::createFileUploadDriver([
 *             'driverName' => 'qiniu',
 *             'domain' => 'http://7xn3sc.com1.z0.glb.clouddn.com/',
 *             'accessToken' => 'svRoYHzIF4jbuU5WPfleBV_snCeEqEGx_mc9sdD6',
 *             'secretToken' => 'sQFlkTlelB3Pmu1QJdISOG8KBgdRk0m5s9Zj1NXR',
 *             'bucket' => 'ceshi',
 *          ]);
 *       },
 * ]
 * - driverName: 必填，可选项为qiniu或oss
 * 其他为对应driver的配置项，七牛配置项需要有
 * - accessToken
 * - secretToken
 * - bucket
 * oss配置项暂未定
 */
class Storage extends Component
{
    public $driverName;
    /**
     * 单例
     * @var Storage
     */
    public static $driver;
    /**
     * @var array 每个元素作为一个文件夹名，如array('dir1','dir2','dir3')代表文件夹/root/dir1/dir2/dir3/或http://qiniu.com/dir1/dir2/dir3/
     */
    private $_path = [];

    /**
     * 内置文件上传驱动类
     * @var array
     */
    // public static $builtInDriver =

    public static function getBuiltInDriver($driverName = null)
    {
        $drivers = [
            'qiniu' => __NAMESPACE__  . '\qiniu\Qiniu',
            'oss' => __NAMESPACE__ . '\oss\Oss',
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
     *  工厂方法
     *  @return Storage
     */
    public static function createFileUploadDriver($config, $params=[])
    {
        if(self::$driver===null) {
            $config['driverName'] = $driverName = ArrayHelper::remove($config, 'driverName');
            $class = self::getBuiltInDriver($driverName);
            $config['class'] = $class;
            self::$driver = Yii::createObject($config, $params);
        }
        return self::$driver;
    }

    /**
     * 获取文件后缀扩展名
     * 目前由于直接通过js上传云存储，请在具体类中实现
     */
    public function getFileExt()
    {
        throw new \Exception('请在具体类中重写实现');
    }

    /**
     * 获取文件上传相对路径，不带文件名及后缀
     * @return string 相对路径地址
     */
    public function getFilePath()
    {
        $path = '';
        $path .= implode('/', $this->path) .'/';
        return $path;
    }

    /**
     * 获得随机文件名，用于要上传的文件，不带后缀
     * @return string 文件名
     */
    public function getRandFileName()
    {
        return str_replace('.', '', microtime(1)) . rand(100000,999999);
    }

    /**
     * 获取上传文件时的key
     * @return string
     */
    public function getKey()
    {
        return $this->getFilePath() . $this->getRandFileName() . $this->getFileExt();
    }

    /**
     * 设置存储路径（key）
     * @param array $value 每个元素作为一个文件夹名，如array('dir1','dir2','dir3')代表文件夹/root/dir1/dir2/dir3/或http://qiniu.com/dir1/dir2/dir3/
     */
    public function setPath($value)
    {
        if(is_array($value))
            $this->_path = $value;
    }

    /**
     * 获得存储路径（key）
     * @return array
     */
    public function getPath()
    {
        if($this->_path===[])
            return array(date('Y'),date('md'));
        else
            return $this->_path;
    }
}
