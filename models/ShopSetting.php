<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_setting".
 *
 * @property string $setting_name
 * @property integer $shop_id
 * @property string $value
 * @property integer $updated
 * @property integer $created
 */
class ShopSetting extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['setting_name', 'shop_id', 'value', 'created'], 'required'],
            [['shop_id', 'updated', 'created'], 'integer'],
            [['value'], 'string'],
            [['setting_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'setting_name' => 'Setting Name',
            'shop_id' => 'Shop ID',
            'value' => 'Value',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopSettingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopSettingQuery(get_called_class());
    }
}
