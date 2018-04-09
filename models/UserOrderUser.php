<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_order_user".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $user_id
 * @property integer $created
 * @property integer $updated
 */
class UserOrderUser extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_order_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'created'], 'required'],
            [['order_id', 'user_id', 'created', 'updated'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\UserOrderUserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\UserOrderUserQuery(get_called_class());
    }
}
