<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_attr_value".
 *
 * @property integer $id
 * @property integer $attr_id
 * @property string $name
 * @property integer $menu_id
 * @property integer $updated
 * @property integer $created
 */
class ShopAttrValue extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_attr_value';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['attr_id', 'name', 'menu_id', 'created'], 'required'],
            [['attr_id', 'menu_id', 'updated', 'created'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'attr_id' => 'Attr ID',
            'name' => 'Name',
            'menu_id' => 'Menu ID',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopAttrValueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopAttrValueQuery(get_called_class());
    }
}
