<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_shop_info".
 *
 * @property integer $shop_id
 * @property string $address
 * @property string $config
 * @property string $logo
 * @property string $description
 * @property integer $distribution_admin_id
 * @property integer $updated
 * @property integer $created
 */
class ShopShopInfo extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_shop_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'config', 'description', 'distribution_admin_id', 'created'], 'required'],
            [['shop_id', 'distribution_admin_id', 'updated', 'created'], 'integer'],
            [['config', 'description'], 'string'],
            [['address', 'logo'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => 'Shop ID',
            'address' => 'Address',
            'config' => 'Config',
            'logo' => 'Logo',
            'description' => 'Description',
            'distribution_admin_id' => 'Distribution Admin ID',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopShopInfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopShopInfoQuery(get_called_class());
    }
}
