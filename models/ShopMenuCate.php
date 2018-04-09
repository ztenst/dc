<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_menu_cate".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $name
 * @property integer $show_type
 * @property integer $status
 * @property integer $updated
 * @property integer $created
 */
class ShopMenuCate extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_menu_cate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'name', 'show_type', 'status', 'created'], 'required'],
            [['shop_id', 'show_type', 'status', 'updated', 'created'], 'integer'],
            [['name'], 'string', 'max' => 30],
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
            'show_type' => '显示类型',
            'status' => 'Status',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopMenuCateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopMenuCateQuery(get_called_class());
    }
}
