<?php
namespace app\behaviors;

use yii\behaviors\AttributeBehavior;
use yii\helpers\ArrayHelper;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * AR类数据字段扩展行为类
 * 一个扩展字段（该字段必须是text类型）就添加一个该行为类，配置格式如下：
 * ```php
 * public function behaviors()
 * {
 *      return [
 *          'class'=>ArExpandFieldBehavior::className(),
 *          'fieldDefaultValue' => xxxx,
 *          'getter' => 'xxx',
 *          'setter' => 'xxx',
 *      ];
 * }
 * ```
 * 其中配置项
 * - fieldDefaultValue: 该字段的默认值
 * - setter: 设置该字段中子字段数据的方法名，如设置为setConfigField并且该字段中有个叫
 * price子字段，则通过$model->setConfigField('price', 12)来进行设置子字段的值为12
 * - getter: 获取该字段中子字段数据的方法名，如设置为getConfigField，则可以通过
 * $model->getConfigField('price', 0);来获取子字段price的值，若获取不到则可以通过第二
 * 参数设置默认返回值
 */
class ArExpandFieldBehavior extends AttributeBehavior
{
    /**
     * 扩展字段名
     */
    private $_expandFieldName;

    /**
     * 扩展字段默认值，默认空数组
     */
    public $fieldDefaultValue = [];
    /**
     * 针对不同事件进行处理的回调函数
     */
    private $callbacks = [];
    /**
     * 获取该字段中子字段数据的方法名
     * @var string
     */
    public $getter = 'getExpandField';
    /**
     * 设置该字段中子字段数据的方法名
     * @var string
     */
    public $setter = 'setExpandField';

    /**
     * init
     */
    public function init()
    {
        parent::init();
        $this->attributes = [
            ActiveRecord::EVENT_AFTER_FIND => $this->expandFieldName,
            ActiveRecord::EVENT_BEFORE_VALIDATE => $this->expandFieldName,
        ];
        $this->callbacks = [
            ActiveRecord::EVENT_AFTER_FIND => [$this, 'decodeField'],
            ActiveRecord::EVENT_BEFORE_VALIDATE => [$this, 'encodeField'],
        ];
    }

    /**
     * overwrite
     */
    public function getValue($event)
    {
        //避免查询语句中没有select该字段，因此不做任何修改值的操作，直接返回null
        if($event->name==ActiveRecord::EVENT_AFTER_FIND) {
            $oldValue = $this->owner->getOldAttribute($this->_expandFieldName);
            if($oldValue===null) {
                return;
            }
        }
        if($callback = ArrayHelper::getValue($this->callbacks, $event->name)) {
            return call_user_func($callback, $event);
        }
    }

    /**
     * 设置扩展字段名
     */
    public function setExpandFieldName($name)
    {
        $this->_expandFieldName = (string)$name;
    }

    /**
     * 获取扩展字段名
     * @return string
     */
    public function getExpandFieldName()
    {
        return $this->_expandFieldName;
    }

    /**
     * 对该字段数据进行解码的函数
     * @return mixed
     */
    public function decodeField($event)
    {
        $decodeConfig = Json::decode($this->owner->{$this->expandFieldName});
        $config = $this->fieldDefaultValue;
        if(is_array($decodeConfig)) {
            foreach($decodeConfig as $name=>$value) {
                if($this->hasConfigField($name)) {
                    $config[$name] = $value;
                }
            }
        }
        return $config;
    }

    /**
     * 对该字段数据进行编码的函数
     */
    public function encodeField($event)
    {
        //新记录做赋默认值操作
        if($this->owner->getIsNewRecord()) {
            if(is_array($this->owner->{$this->_expandFieldName})) {
                $this->owner->{$this->_expandFieldName} = array_merge($this->fieldDefaultValue, $this->owner->{$this->_expandFieldName});
            } else {
                $this->owner->{$this->_expandFieldName} = $this->fieldDefaultValue;
            }
        }
        return Json::encode($this->owner->{$this->expandFieldName});
    }

    /**
     * config字段中是否存在指定虚拟字段
     * @param string $fieldName 虚拟字段名称
     * @return boolean 返回布尔值，true表示存在，false表示不存在
     */
    public function hasConfigField($fieldName)
    {
        return isset($this->fieldDefaultValue[$fieldName]);
    }

    /**
     * getter方法，获取config字段数组形式
     * @return mixed|null
     */
    private function getField($fieldName='', $default=null)
    {
        if($this->hasField($fieldName)) {
            return ArrayHelper::getValue($this->owner->{$this->expandFieldName}, $fieldName, $this->fieldDefaultValue[$fieldName]);
        } else {
            return $default;
        }
    }

    /**
     * setter方法，设置config字段数组形式
     * @param string $field 虚拟字段名
     * @param mixed $value 虚拟字段值
     */
    private function setField($field, $value)
    {
        if($this->hasField($field)) {
            $config = $this->owner->{$this->expandFieldName};
            $config[$field] = $value;
            $this->owner->{$this->expandFieldName} = $config;
        }
    }

    /**
     * config字段中是否存在指定虚拟字段
     * @param string $fieldName 虚拟字段名称
     * @return boolean 返回布尔值，true表示存在，false表示不存在
     */
    public function hasField($fieldName)
    {
        return isset($this->fieldDefaultValue[$fieldName]);
    }

    public function __call($name, $args)
    {
        if($this->getter==$name) {
            return call_user_func_array([$this,'getField'], $args);
        } elseif($this->setter==$name) {
            return call_user_func_array([$this,'setField'], $args);
        }
        return parent::__call($name, $args);
    }

    /**
     * 重写以识别定义的setter和getter名
     */
    public function hasMethod($name)
    {
        if(in_array($name, [$this->getter, $this->setter])) {
            return true;
        }
        return parent::hasMethod($name);
    }
}
