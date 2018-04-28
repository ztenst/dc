<?php

namespace app\models\ext;

use Yii;

class UserOrder extends \app\models\UserOrder
{   
    const  STATUS_NO = 0;
    const STATUS_PAY = 1;
    const STATUS_CANCEL = 2;

    public static $statusArray = [
        self::STATUS_PAY => '支付成功',
        self::STATUS_CANCEL => '支付取消',
        self::STATUS_NO => '未支付'
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
     * 验证规则
     * @return array
     */
    public function rules()
    {
        list($usec, $sec) = explode(" ", microtime());
        return array_merge([
            ['menuNum', 'integer'],
            [['trade_no'], 'default', 'value'=>(float)sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000).sprintf('%03d', rand(0, 999))],//生成交易号，最好指定使用场景
        ], parent::rules());
    }


    /**
     * 获取订单相关的已点菜单
     * @return UserOrderMenuQuery
     */
    public function getMenus()
    {
        return $this->hasMany(UserOrderMenu::className(),['order_id'=>'id']);
    }

    /**
     * 获取该订单关联的用户
     * @return UserMemberQuery
     */
    public function getUsers()
    {
        return $this->hasMany(UserMember::className(), ['id'=>'user_id'])->viaTable('user_order_menu', ['order_id'=>'id']);
    }

    public function getShop()
    {
        return $this->hasOne(ShopShop::className(),['id' => 'shop_id']);
    }
}
