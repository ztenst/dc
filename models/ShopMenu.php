<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_menu".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $name
 * @property string $price
 * @property integer $stock
 * @property integer $status
 * @property string $config
 * @property integer $cate_id
 * @property string $image
 * @property integer $unit_id
 * @property integer $updated
 * @property integer $created
 * @property integer $sale
 */
class ShopMenu extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'name', 'status', 'cate_id', 'unit_id', 'created'], 'required'],
            [['shop_id', 'stock', 'status', 'cate_id', 'unit_id', 'updated', 'created', 'sale'], 'integer'],
            [['price'], 'number'],
            [['config'], 'string'],
            [['name'], 'string', 'max' => 30],
            [['image'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'price' => 'Price',
            'stock' => 'Stock',
            'status' => 'Status',
            'config' => 'Config',
            'cate_id' => 'Cate ID',
            'image' => 'Image',
            'unit_id' => 'Unit ID',
            'updated' => 'Updated',
            'created' => 'Created',
            'sale' => 'Sale',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopMenuQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopMenuQuery(get_called_class());
    }
}
