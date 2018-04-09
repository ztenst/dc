<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "statistics_desk_count".
 *
 * @property integer $shop_id
 * @property string $number
 * @property integer $ymd
 * @property integer $updated
 * @property integer $created
 */
class StatisticsDeskCount extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'statistics_desk_count';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'number', 'ymd', 'created'], 'required'],
            [['shop_id', 'number', 'ymd', 'updated', 'created'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => 'Shop ID',
            'number' => '餐桌数量',
            'ymd' => 'Ymd',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\StatisticsDeskCountQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\StatisticsDeskCountQuery(get_called_class());
    }
}
