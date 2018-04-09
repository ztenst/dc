<?php

namespace app\models;

use app\models\queries\ShopAttrQuery;
use Yii;

/**
 * This is the model class for table "shop_attr".
 *
 * @property integer $id
 * @property string $name
 * @property integer $shop_id
 * @property integer $updated
 * @property integer $created
 */
class ShopAttr extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_attr';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'shop_id', 'created'], 'required'],
            [['shop_id', 'updated', 'created'], 'integer'],
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
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return ShopAttrQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopAttrQuery(get_called_class());
    }
}
