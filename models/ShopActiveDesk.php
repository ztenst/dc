<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_active_desk".
 *
 * @property integer $id
 * @property integer $merge_active_id
 * @property integer $user_id
 * @property integer $desk_id
 * @property integer $order_id
 * @property integer $shop_id
 * @property integer $people_num
 * @property string $config
 * @property integer $updated
 * @property integer $created
 */
class ShopActiveDesk extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_active_desk';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merge_active_id', 'user_id', 'desk_id', 'order_id', 'shop_id', 'people_num', 'updated', 'created'], 'integer'],
            [['user_id', 'desk_id', 'shop_id', 'config', 'created'], 'required'],
            [['config'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merge_active_id' => 'Merge Active ID',
            'user_id' => 'User ID',
            'desk_id' => 'Desk ID',
            'order_id' => 'Order ID',
            'shop_id' => 'Shop ID',
            'people_num' => 'People Num',
            'config' => 'Config',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopActiveDeskQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopActiveDeskQuery(get_called_class());
    }
}
