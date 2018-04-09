<?php

namespace app\models\ext;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "shop_desk".
 * @vserion 2017-05-22 09:15:19通过gii生成
 */
class ShopDesk extends \app\models\ShopDesk
{
    const STATUS_EMPTY = 0;
    const STATUS_ORDER = 1;
    const STATUS_WAIT_COMFIRM = 2;
    const STATUS_SERVE = 3;
    const STATUS_WILL_PAY = 4;
    const STATUS_MERGE = 5;

    /**
     * 商家创建餐桌
     */
    const SCENARIO_SHOP_CREATE = 'shopCreate';
    /**
     * 商家编辑餐桌
     */
    const SCENARIO_SHOP_EDIT = 'shopEdit';
    /**
     * 分组统计字段
     * @var integer
     */
    public $groupCount = 0;

    /**
     * @var config字段中的虚拟字段，字段名=>默认值
     */
    private $_configFields = [
    ];
    /**
     * @var config字段的数组形式，程序set\get都走的这个数组形式，入库才进行json_encode
     */
    private $_configArr;

    /**
     * init
     */
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_FIND, [$this, 'decodeConfig']);
    }

    /**
     * scenarios
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return array_merge($scenarios,  [
            self::SCENARIO_SHOP_CREATE => [],
            self::SCENARIO_SHOP_EDIT => ['number'/*,'shop_id','updated','created','config'*/],//该场景下需要验证的字段
        ]);
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge([
            [['number'], 'default', 'value'=>'(未编号)'],
            [['status'], 'default', 'value'=>self::STATUS_ORDER],//开桌默认状态
            ['number', 'unique', 'targetAttribute'=>['number', 'shop_id'], 'message'=>'该桌号已存在'],
            [['config'], 'default', 'value'=>function($model, $attribute){
                try {
                    return Json::encode($model->configArr);
                } catch(Exception $e) {
                    $model->addError('config', $e->getMessage());
                }
            }],//保存前进行数据编码操作
        ],parent::rules());
    }

    /**
     * 对config字段进行json解码
     */
    public function decodeConfig($event)
    {
        $configArr = Json::decode($this->config);
        $event->sender->configArr = array_merge($this->_configFields, (array)$configArr);
    }

    /**
     * getter方法，获取config字段数组形式
     * @return mixed|null
     */
    public function getConfigField($fieldName='', $default=null)
    {
        if($this->hasConfigField($fieldName)) {
            return ArrayHelper::getValue($this->_configArr, $fieldName, $this->_configFields[$fieldName]);
        } else {
            return $default;
        }
    }

    /**
     * setter方法，设置config字段数组形式
     * @param string $field 虚拟字段名
     * @param mixed $value 虚拟字段值
     */
    public function setConfigField($field, $value)
    {
        if($this->hasConfigField($field)) {
            $this->_configArr[$field] = $value;
        }
    }

    /**
     * config字段中是否存在指定虚拟字段
     * @param string $fieldName 虚拟字段名称
     * @return boolean 返回布尔值，true表示存在，false表示不存在
     */
    public function hasConfigField($fieldName)
    {
        return isset($this->_configFields[$fieldName]);
    }

    /**
     * 设置config字段数组形式
     */
    public function setConfigArr($value)
    {
        $this->_configArr = $value;
    }

    /**
     * 获取config字段的数组形式
     */
    public function getConfigArr()
    {
        return $this->_configArr;
    }

    /**
     * 获取餐桌状态列表
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_EMPTY => '空桌',
            self::STATUS_ORDER => '点餐中',
            self::STATUS_WAIT_COMFIRM => '待确认',
            self::STATUS_SERVE => '上菜中',
            self::STATUS_WILL_PAY => '预结',
            self::STATUS_MERGE => '并桌',
        ];
    }

    /**
     * 字段labels
     * @return array
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        return array_merge($attributeLabels, [
            'number' => '桌号',
            'shop_id' => '商家',
        ]);
    }

    /**
     * 获取当前餐桌开桌对应的流水记录
     * @return ShopActiveDeskQuery
     */
    public function getActiveInfo()
    {
        return $this->hasOne(ShopActiveDesk::className(), ['id'=>'active_desk_id']);
    }

    /**
     * 该桌是否是空桌（注意：该函数只判断是否是空桌，若与其他桌合并了也依然可能显示是空桌）
     * @return boolean 是返回true，否返回false
     */
    public function getIsClear()
    {
        return $this->status===null || $this->status == self::STATUS_EMPTY;
    }

    /**
     * 是否有并桌信息
     * @return boolean
     */
    public function getIsMerge()
    {
        return $this->status == self::STATUS_MERGE;
    }

    /**
     * 检查指定餐桌是否存在
     */
    public static function checkIsExist($deskId, $shopId=0, $returnInstance=false)
    {
        $query = self::find()->where(['id'=>$deskId]);
        if($shopId>0) {
            $query->andWhere(['shop_id'=>$shopId]);
        }
        return $returnInstance ? $query->one() : $query->exists();
    }

    /**
     * 获取餐桌当前状态文字
     * @return string
     */
    public function getStatusText($default='(无法获取)')
    {
        if($this->status==self::STATUS_MERGE && $this->mergeTargetDesk) {
            return '并桌至' . $this->mergeTargetDesk->number;
        } else {
            return ArrayHelper::getValue(self::getStatusList(), $this->status, $default);
        }
    }

    /**
     * 获取并桌目标桌
     * @return ShopDeskQuery
     */
    public function getMergeTargetDesk()
    {
        return $this->hasOne(self::className(), ['id'=>'merge_target_desk_id'])->shop($this->shop_id);
    }

    /**
     * 获取并桌来源桌
     * @return ShopDeskQuery
     */
    public function getMergeSourceDesks()
    {
        return $this->hasMany(self::className(), ['merge_target_desk_id'=>'id'])->shop($this->shop_id);
    }

    /**
     * 获取正在用餐桌的用餐人数
     * @return integer
     */
    public function getActivePeopleNumber()
    {
        if(($this->status == self::STATUS_MERGE) && $this->mergeTargetDesk) {
            return $this->mergeTargetDesk->activePeopleNumber;
        } elseif(($this->status != self::STATUS_EMPTY) && $this->activeInfo) {
            return $this->activeInfo->people_num;
        } else {
            return 0;
        }
    }

    /**
     * 获取正在用餐桌的开桌时间
     * @return timestamp
     */
    public function getActiveOpenTime()
    {
        if(($this->status == self::STATUS_MERGE) && $this->mergeTargetDesk) {
            return $this->mergeTargetDesk->activeOpenTime;
        } elseif(($this->status != self::STATUS_EMPTY) && $this->activeInfo) {
            return $this->activeInfo->created;
        } else {
            return 0;
        }
    }

    /**
     * 获取正在用餐桌的订单价格
     * @return float
     */
    public function getActivePrice()
    {
        if(($this->status == self::STATUS_MERGE) && $this->mergeTargetDesk) {
            return $this->mergeTargetDesk->activePrice;
        } elseif(($this->status != self::STATUS_EMPTY) && isset($this->activeInfo->order)) {
            return $this->activeInfo->order->total_price;
        } else {
            return 0;
        }
    }
}
