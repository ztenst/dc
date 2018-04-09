<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "statistics_desk_use".
 *
 * @property integer $shop_id
 * @property integer $ymd
 * @property integer $desk_id
 * @property string $desk_number
 * @property integer $value
 * @property integer $updated
 * @property integer $created
 */
class StatisticsDeskUse extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'statistics_desk_use';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'ymd', 'desk_id', 'desk_number', 'value', 'created'], 'required'],
            [['shop_id', 'ymd', 'desk_id', 'value', 'updated', 'created'], 'integer'],
            [['desk_number'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => 'Shop ID',
            'ymd' => 'Ymd',
            'desk_id' => 'Desk ID',
            'desk_number' => 'Desk Number',
            'value' => '餐桌使用次数',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\StatisticsDeskUseQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\StatisticsDeskUseQuery(get_called_class());
    }
}
