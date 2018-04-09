<?php

namespace app\models;

use app\models\queries\ShopMenuRecomQuery;
use Yii;

/**
 * This is the model class for table "shop_menu_recom".
 *
 * @property integer $id
 * @property integer $menu_id
 * @property integer $shop_id
 * @property integer $sort
 * @property integer $updated
 * @property integer $created
 */
class ShopMenuRecom extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_menu_recom';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'menu_id', 'shop_id', 'created'], 'required'],
            [['id', 'menu_id', 'shop_id', 'sort', 'updated', 'created'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'menu_id' => 'Menu ID',
            'shop_id' => 'Shop ID',
            'sort' => 'Sort',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return ShopMenuRecomQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopMenuRecomQuery(get_called_class());
    }
}
