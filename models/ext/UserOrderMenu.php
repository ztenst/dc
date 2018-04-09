<?php

namespace app\models\ext;

use Yii;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;

class UserOrderMenu extends \app\models\UserOrderMenu
{
    /**
     * 退菜常量
     */
    const CANCEL_YES = 1;
    const CANCEL_NO = 0;
    /**
     * 确认菜品常量
     */
    const CONFIRM_YES = 1;
    const CONFIRM_NO = 0;

    /**
     * 该字段用于mysql使用聚合函数时存放数据的字段
     * 如使用count/sum/average等
     */
    public $groupCount = 0;
    /**
     * 该字段用于分日期统计时用到
     * 目前用于每日定时统计销售额脚本
     */
    public $ymd;

    /**
     * @var config字段中的虚拟字段，字段名=>默认值
     */
    private $_defaultConfig = [
    ];

    /**
     * init
     */
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_FIND, [$this, 'decodeMenuAttrInfo']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'decodeMenuAttrInfo']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, 'decodeMenuAttrInfo']);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'encodeMenuAttrInfo']);
    }

    /**
     * 商家编辑\新增场景
     */
    const SCENARIO_SHOP_EDIT = 'shop_edit';

    /**
     * behaviors
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => \app\behaviors\ArExpandFieldBehavior::className(),
                'expandFieldName' => 'config',
                'fieldDefaultValue' => $this->_defaultConfig,
                'getter' => 'getConfigField',
                'setter' => 'setConfigField',
            ]
        ];
    }

    /**
     * 验证规则
     */
    public function rules()
    {
        return array_merge([
        ],parent::rules());
    }

    /**
     * 退菜状态
     * @return array
     */
    public static function getCancelList()
    {
        return [
            self::CANCEL_NO => '未退菜',
            self::CANCEL_YES => '已退菜'
        ];
    }

    public function getUser()
    {
        return $this->hasOne(UserMember::className(),['id' => 'user_id']);
    }

    /**
     * 获取关联的订单数据
     * @return UserOrderQuery
     */
    public function getOrder()
    {
        return $this->hasOne(UserOrder::className(), ['id'=>'order_id']);
    }

    /**
     * 对menu_attr_info字段进行json解码
     */
    public function decodeMenuAttrInfo($event)
    {
        if($this->menu_attr_info) {
            $decode = Json::decode($this->menu_attr_info);
            $this->menu_attr_info = $decode ?: [];
        } else {
            $this->menu_attr_info = [];
        }
    }

    /**
     * 对menu_attr_info字段进行json编码
     */
    public function encodeMenuAttrInfo($event)
    {
        $this->menu_attr_info = is_array($this->menu_attr_info) ? $this->menu_attr_info : [];
        $this->menu_attr_info = Json::encode($this->menu_attr_info);
    }

    /**
     * 获取关联的菜品数据
     * @return ShopMenuQuery
     */
    public function getMenu()
    {
        return $this->hasOne(ShopMenu::className(), ['id'=>'menu_id']);
    }

    /**
     * 获取本次所点菜品的总价
     * @return integer
     */
    public function getTotalPrice()
    {
        return $this->menu_num * $this->menu_price;
    }
}
