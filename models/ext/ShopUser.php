<?php

namespace app\models\ext;

use Yii;

/**
 * This is the model class for table "shop_user".
 * @vserion 2017-06-02 14:44:38通过gii生成
 */
class ShopUser extends \app\models\ShopUser
{
    /**
     * @var int 消费次数
     */
    public $consumptionTimes = 0;
    /**
     * @var int 消费金额
     */
    public $consumptionMoney = 0;
    /**
     * @var int 最后消费时间（时间戳格式）
     */
    public $lastTime;

    /**
     * 商家编辑场景
     */
    const SCENARIO_SHOP_EDIT = 'shop_edit';

    /**
     * 用户修改电话
     */
    const SCENARIO_USER_PHONE = 'user_phone_edit';

    /**
     * config扩展字段默认值
     */
    private $_defaultConfig = [

    ];

    /**
     * 场景设置
     * @return array
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_SHOP_EDIT => ['sex','birthday'],//商家可编辑验证字段
            self::SCENARIO_USER_PHONE => ['user_id','shop_id']
        ]);
    }

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
     * 获取用户信息
     * @return UserUserQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserMember::className(), ['id'=>'user_id']);
    }

    /**
     * 获取用户订单
     * @return UserOrderQuery
     */
    public function getOrders()
    {
        return $this->hasMany(UserOrder::className(), ['id'=>'order_id'])->viaTable('user_order_user', ['user_id'=>'user_id']);
    }

    public static function findUser($user_id , $shop_id)
    {
        return static::find()->where([
            'user_id' => $user_id,
            'shop_id' => $shop_id
        ])->one();
    }
}
