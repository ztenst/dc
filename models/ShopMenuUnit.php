<?php

namespace app\models;

use app\models\queries\ShopMenuCateQuery;
use Yii;

/**
 * This is the model class for table "shop_menu_unit".
 *
 * @property integer $id
 * @property string $name
 * @property integer $shop_id
 * @property integer $created
 * @property integer $updated
 */
class ShopMenuUnit extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_menu_unit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'shop_id', 'created'], 'required'],
            [['shop_id', 'created', 'updated'], 'integer'],
            [['name'], 'string', 'max' => 20],
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
            'shop_id' => 'Shop ID',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    /**
     * @inheritdoc
     * @return ShopMenuUnitQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopMenuCateQuery(get_called_class());
    }
}
