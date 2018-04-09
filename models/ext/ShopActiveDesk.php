<?php

namespace app\models\ext;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "shop_active_desk".
 * @vserion 2017-05-23 11:53:43通过gii生成
 */
class ShopActiveDesk extends \app\models\ShopActiveDesk
{
    /**
     * @var config字段中的虚拟字段，字段名=>默认值
     */
    private $_defaultConfig = [
    ];

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
     * rules
     * @return array
     */
    public function rules()
    {
        return array_merge([
        ], parent::rules());
    }

    /**
     * 获取关联的餐桌信息
     * @return ShopDeskQuery
     */
    public function getDesk()
    {
        return $this->hasOne(ShopDesk::className(), ['id'=>'desk_id']);
    }

    public function getShop()
    {
        return $this->hasOne(ShopShop::className(),['id'=>'shop_id']);
    }

    /**
     * 获取相关订单
     * @return UserorderQuery
     */
    public function getOrder()
    {
        return $this->hasOne(UserOrder::className(), ['id'=>'order_id']);
    }
}
