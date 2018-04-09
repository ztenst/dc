<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "statistics_menu_sale".
 *
 * @property integer $shop_id
 * @property integer $menu_id
 * @property integer $ymd
 * @property string $menu_name
 * @property string $value
 * @property integer $updated
 * @property integer $created
 */
class StatisticsMenuSale extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'statistics_menu_sale';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'menu_id', 'ymd', 'menu_name', 'value', 'created'], 'required'],
            [['shop_id', 'menu_id', 'ymd', 'updated', 'created'], 'integer'],
            [['value'], 'number'],
            [['menu_name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => 'Shop ID',
            'menu_id' => 'Menu ID',
            'ymd' => '某日标识ymd格式',
            'menu_name' => 'Menu Name',
            'value' => 'Value',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\StatisticsMenuSaleQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\StatisticsMenuSaleQuery(get_called_class());
    }
}
