<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_desk".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $number
 * @property integer $status
 * @property string $config
 * @property integer $active_desk_id
 * @property integer $merge_target_desk_id
 * @property integer $updated
 * @property integer $created
 */
class ShopDesk extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_desk';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'number', 'status', 'config', 'active_desk_id', 'created'], 'required'],
            [['shop_id', 'status', 'active_desk_id', 'merge_target_desk_id', 'updated', 'created'], 'integer'],
            [['config'], 'string'],
            [['number'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_id' => 'Shop ID',
            'number' => 'Number',
            'status' => 'Status',
            'config' => 'Config',
            'active_desk_id' => 'Active Desk ID',
            'merge_target_desk_id' => 'Merge Target Desk ID',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopDeskQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopDeskQuery(get_called_class());
    }
}
