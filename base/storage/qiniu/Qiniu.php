<?php
namespace app\base\storage\qiniu;

use Qiniu\Auth;
use Qiniu\Processing\ImageUrlBuilder;
use Qiniu\Processing\Operation;
use app\base\storage\Storage;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

class Qiniu extends Storage
{
    //==============================七牛必要配置参数=========================
    /**
     * accessToken
     * @var sintrg
     */
    private $_ak;
    /**
     * secretToken
     * @var sintrg
     */
    private $_sk;
    /**
     * bucketName
     * @var sintrg
     */
    private $_bucket;
    /**
     * domain
     * @var sintrg
     */
    private $_domain = '';
    //================================其他配置============================
    /**
     * 上传策略配置数组
     * @var array
     */
    private $_policy = [];

    public function init()
    {
        parent::init();
        $this->resetPolicy();
    }

    public function setAccessToken($value)
    {
        if(is_callable($value)) {
            $value = call_user_func($value);
        }
        $this->_ak = $value;
    }

    public function getAccessToken()
    {
        if($this->_ak===null) {
            throw new \Exception('accessToken not set');
        }
        return $this->_ak;
    }

    public function setSecretToken($value)
    {
        if(is_callable($value)) {
            $value = call_user_func($value);
        }
        $this->_sk = $value;
    }

    public function getSecretToken()
    {
        if($this->_sk===null) {
            throw new \Exception('secretToken not set');
        }
        return $this->_sk;
    }

    public function setBucket($value)
    {
        if(is_callable($value)) {
            $value = call_user_func($value);
        }
        $this->_bucket = $value;
    }

    public function getBucket()
    {
        if($this->_bucket===null) {
            throw new \Exception('bucket not set');
        }
        return $this->_bucket;
    }

    /**
     * 配置domain
     * @param string
     */
    public function setDomain($value)
    {
        $this->_domain = trim($value, '\\/') . '/';
    }

    /**
     * 获取domain
     * @return string
     */
    public function getDomain()
    {
        return $this->_domain;
    }

    /**
     * 清除上传策略
     */
    public function resetPolicy()
    {
        $this->_policy = [
            'saveKey' => $this->getKey(),
        ];
    }

    /**
     * 设置上传策略
     * @param array $value 上传策略配置数组
     */
    public function setPolicy(array $value)
    {
        $this->_policy = array_merge($this->_policy, $value);
    }

    /**
     * 获取上传策略
     * @return array|null
     */
    public function getPolicy()
    {
        return $this->_policy;
    }

    /**
     * 获取上传凭证
     * @return string 上传凭证字符串
     */
    public function getUploadToken()
    {
        $auth = new Auth($this->accessToken, $this->secretToken);
        return $auth->uploadToken($this->bucket, null, 3600, $this->getPolicy());
    }

    /**
     * 前端渲染小物件的配置项
     * @var array
     */
    public $widgetOptions = [];

    /**
     * 绑定前端小物件与表单字段生成展示区块页面
     */
    public function bindFormField(\yii\widgets\ActiveField $field)
    {
        return widgets\Single::widget([
            'field' => $field,
            'storage' => $this,
            'options' => array_merge($this->widgetOptions, $field->storageJsOptions),
        ]);
    }

    /**
     * 重写父类方法
     * @return string 七牛魔术变量
     */
    public function getFileExt()
    {
        return '$(ext)';
    }

    private $_imageUrlbuilder;

    public function getImageUrlBuilder()
    {
        if($this->_imageUrlbuilder === null) {
            $this->_imageUrlbuilder = new ImageUrlBuilder;
        }
        return $this->_imageUrlbuilder;
    }

    private $_operation;

    public function getOperation()
    {
        if($this->_operation === null) {
            $this->_operation = new Operation;
        }
        return $this->_operation;
    }

    /**
     *  处理存储资源key为完整地址
     *  @return string
     */
    public function fixFileUrl(&$key)
    {
        if(strpos($key, 'http')!==false) {
            $url = $key;
            $key = '';
            return $url;
        }
        return $key ? $this->domain . $key : $key;
    }

    /**
     * 处理图片资源
     * @param string $key 资源key
     * @param integer $width 要处理成的图片宽度数值
     * @param integer $height 要处理成的图片高度数值
     */
    public function fixImageUrl($key, $width=0, $height=0)
    {
        $width && $width = $width;
        $height && $height = $height;
        $url = $this->fixFileUrl($key);
        if($key && ($width || $height)) {
            return $this->ImageUrlBuilder->thumbnail($url, 1, $width, $height);
        } else {
            return $url;
        }
    }
}
