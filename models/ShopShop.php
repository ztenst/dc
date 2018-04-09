<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_shop".
 *
 * @property integer $id
 * @property string $name
 * @property integer $city_id
 * @property integer $cate_id
 * @property integer $status
 * @property integer $sort
 * @property integer $is_deleted
 * @property integer $updated
 * @property integer $created
 */
class ShopShop extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_shop';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'city_id', 'status', 'created'], 'required'],
            [['city_id', 'cate_id', 'status', 'sort', 'is_deleted', 'updated', 'created'], 'integer'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'city_id' => 'City ID',
            'cate_id' => 'Cate ID',
            'status' => 'Status',
            'sort' => 'Sort',
            'is_deleted' => 'Is Deleted',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopShopQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopShopQuery(get_called_class());
    }
}
