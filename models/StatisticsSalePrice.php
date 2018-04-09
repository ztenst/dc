<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "statistics_sale_price".
 *
 * @property integer $shop_id
 * @property integer $ymd
 * @property string $value
 * @property integer $updated
 * @property integer $created
 */
class StatisticsSalePrice extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'statistics_sale_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'ymd', 'value', 'created'], 'required'],
            [['shop_id', 'ymd', 'updated', 'created'], 'integer'],
            [['value'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => 'Shop ID',
            'ymd' => '某日标识ymd格式',
            'value' => '销售额',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\StatisticsSalePriceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\StatisticsSalePriceQuery(get_called_class());
    }
}
