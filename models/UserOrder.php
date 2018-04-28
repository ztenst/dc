<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_order".
 *
 * @property integer $id
 * @property string $trade_no
 * @property integer $shop_id
 * @property integer $user_id
 * @property string $total_price
 * @property integer $status
 * @property integer $shop_admin_id
 * @property integer $updated
 * @property integer $created
 */
class UserOrder extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['trade_no', 'shop_id', 'user_id', 'total_price', 'status', 'created'], 'required'],
            [['shop_id', 'user_id', 'status', 'shop_admin_id', 'updated', 'created'], 'integer'],
            [['total_price'], 'number'],
            [['trade_no'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trade_no' => '订单号',
            'shop_id' => '店铺',
            'user_id' => '用户',
            'total_price' => '总金额',
            'status' => '订单状态',
            'shop_admin_id' => '管理员',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\UserOrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\UserOrderQuery(get_called_class());
    }
}
