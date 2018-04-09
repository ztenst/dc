<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_order".
 *
 * @property integer $id
 * @property string $trade_no
 * @property integer $desk_id
 * @property string $desk_number
 * @property integer $shop_id
 * @property integer $user_id
 * @property string $total_price
 * @property integer $status
 * @property string $config
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
            [['trade_no', 'desk_id', 'desk_number', 'shop_id', 'user_id', 'total_price', 'status', 'config', 'created'], 'required'],
            [['desk_id', 'shop_id', 'user_id', 'status', 'shop_admin_id', 'updated', 'created'], 'integer'],
            [['total_price'], 'number'],
            [['config'], 'string'],
            [['trade_no'], 'string', 'max' => 20],
            [['desk_number'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trade_no' => 'Trade No',
            'desk_id' => 'Desk ID',
            'desk_number' => 'Desk Number',
            'shop_id' => 'Shop ID',
            'user_id' => 'User ID',
            'total_price' => 'Total Price',
            'status' => 'Status',
            'config' => 'Config',
            'shop_admin_id' => 'Shop Admin ID',
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
