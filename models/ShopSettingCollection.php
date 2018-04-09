<?php
namespace app\models;

use Yii;
use yii\base\Component;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use app\models\ext\ShopSetting;
use yii\base\InvalidParamException;
use ArrayIterator;

/**
 * 商家配置信息收集类
 * 一般使用方式，通过商家对象信息获取到其所有的配置：
 * $shop = ShopShop::findOne($shopId);
 * $settings = $shop->shopSettings;
 * //获取某个配置值
 * $searchKeywords = $settings->searchKeywords;
 * //修改searchKeywords配置
 * $settings->searchKeywords='content';
 * $settings->save();
 */
class ShopSettingCollection extends Model
{
    /**
     * ShopShop
     */
    public $shop;

    /**
     * 初始化默认配置项及默认值
     * @return array
     */
    public static function defaultSettings()
    {
        return [
            'searchKeywords' => '',//搜索关键词
            'printerServer' => '',//店内云打印服务机器局域网地址，如192.168.0.13:8000
            'kitchenPrinter' => -1,//后厨打印机
        ];
    }

    /**
     * 获取配置项默认值
     * 该函数目前暂时与{defaultSettings()}冗余，以防后续{defaultSettings()}返回结果
     * 扩展成其他格式
     * @return array 该函数始终返回:配置项名=>默认值  格式
     */
    public static function getSettingDefaultValues()
    {
        return self::defaultSettings();
    }

    /**
     * 配置项的验证规则
     * @return array
     */
    public function rules()
    {
        return [
            [['searchKeywords','printerServer','kitchenPrinter'], 'string'],
            ['printerServer', 'ip','ipv6'=>false, 'message'=>'云打印服务地址必须是一个有效IP地址'],
            //searchKeywords验证处理
            ['searchKeywords', 'filter', 'filter'=>function($value) {
                $value = trim(trim($value), ',');
                $value = str_replace('，',',',$value);
                $value = explode(',',$value);
                array_walk($value, function(&$item, $key){
                    trim($item);
                });
                return $value;
            }]
        ];
    }

    /**
     * 配置模型类中的配置项标识名
     */
    public function attributes()
    {
        return array_keys(self::defaultSettings());
    }

    private $_settings = [];

    /**
     * 构造函数
     */
    public function __construct(array $shopSettings, $config=[])
    {
        parent::__construct($config);
        $this->fill($shopSettings);
    }

    /**
     * 填充商家的配置信息，并删除多余无用的数据
     * @param array $shopSettings 数据库取出的配置数据对象
     */
    private function fill($shopSettings)
    {
        $delete = $shopSettings;
        foreach(self::defaultSettings() as $settingName=>$defaultValue) {
            if(isset($shopSettings[$settingName])) {
                $this->_settings[$settingName] = $shopSettings[$settingName];
            } else {
                $obj = new ShopSetting([
                    'setting_name' => $settingName,
                    'value' => $defaultValue,
                    'shop_id' => $this->shop->id,
                ]);
                $this->_settings[$settingName] = $obj;
            }
            //反推得出最后需要删除的配置
            $delete = ArrayHelper::filter($shopSettings, ['!'.$settingName]);
        }
        foreach($delete as $obj) {
            $obj->delete();
        }
    }

    /**
     * 判断是否有指定配置项
     * @return boolean
     */
    public function hasSetting($settingName)
    {
        return isset($this->_settings[$settingName]);
    }

    /**
     * Returns the named attribute value.
     * If this record is the result of a query and the attribute is not loaded,
     * `null` will be returned.
     * @param string $name the attribute name
     * @return mixed the attribute value. `null` if the attribute is not set or does not exist.
     * @see hasAttribute()
     */
    public function getSetting($settingName)
    {
        if($this->hasSetting($settingName)) {
            return $this->_settings[$settingName]->value;
        }
    }

    /**
     * Sets the named attribute value.
     * @param string $name the attribute name
     * @param mixed $value the attribute value.
     * @throws InvalidParamException if the named attribute does not exist.
     * @see hasAttribute()
     */
    public function setSetting($name, $value)
    {
        if ($this->hasSetting($name)) {
            $this->_settings[$name]->value = $value;
        } else {
            throw new InvalidParamException(get_class($this) . ' has no setting named "' . $name . '".');
        }
    }

    /**
     * __get
     */
    public function __get($settingName)
    {
        if(($setting = $this->getSetting($settingName))!==null) {
            return $setting;
        }
        return parent::__get($settingName);
    }

    /**
     * __set
     */
    public function __set($settingName, $value)
    {
        if($this->hasSetting($settingName)) {
            $this->setSetting($settingName, $value);
        } else {
            parent::__set($settingName, $value);
        }
    }

    /**
     * 配置项的值是否是默认值
     * @param ShopSetting $setting
     * @return boolean
     */
    private function isDefaultValue($setting)
    {
        $defaultValues = self::getSettingDefaultValues();
        return isset($defaultValues[$setting->setting_name]) && $defaultValues[$setting->setting_name] === $setting->value;
    }

    /**
     * 保存所有更改的配置
     * @return 保存成功返回true，保存失败返回false
     */
    public function save()
    {
        foreach($this->_settings as $name=>$setting) {
            //只对改动过的value配置进行save操作，并且出错了直接把errors拿过来
            if($setting->isAttributeChanged('value') && !$this->isDefaultValue($setting) && !$setting->save()) {
                foreach($setting->getFirstErrors() as $name=>$error) {
                    $this->addError($name, $error);
                }
                return false;
            }
        }
        return true;
    }

    //=========================抽象类实现函数=============================
    /**
     * Returns an iterator for traversing the cookies in the collection.
     * This method is required by the SPL interface [[\IteratorAggregate]].
     * It will be implicitly called when you use `foreach` to traverse the collection.
     * @return ArrayIterator an iterator for traversing the cookies in the collection.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_settings);
    }

    /**
     * Returns whether there is a cookie with the specified name.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `isset($collection[$name])`.
     * @param string $name the setting name
     * @return bool whether the named setting exists
     */
    public function offsetExists($name)
    {
        return $this->hasSetting($name);
    }

    /**
     * Returns the cookie with the specified name.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `$cookie = $collection[$name];`.
     * This is equivalent to [[get()]].
     * @param string $name the setting name
     */
    public function offsetGet($name)
    {
        return $this->getSetting($name);
    }

    /**
     * Adds the cookie to the collection.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `$collection[$name] = $cookie;`.
     * This is equivalent to [[add()]].
     * @param string $name the setting name
     * @param ShopSetting $value the setting to be set
     */
    public function offsetSet($name, $value)
    {
        $this->setSetting($name, $value);
    }

    public function offsetUnset($name)
    {
        //空
    }

    public function count()
    {
        return count($this->_settings);
    }
}
