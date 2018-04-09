<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_order_menu".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $user_id
 * @property integer $menu_id
 * @property string $menu_name
 * @property string $menu_price
 * @property integer $menu_num
 * @property string $menu_attr_info
 * @property integer $add_no
 * @property integer $is_cancel
 * @property integer $is_confirm
 * @property string $config
 * @property integer $updated
 * @property integer $created
 */
class UserOrderMenu extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_order_menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'menu_id', 'menu_name', 'menu_price', 'menu_num', 'created'], 'required'],
            [['order_id', 'user_id', 'menu_id', 'menu_num', 'add_no', 'is_cancel', 'is_confirm', 'updated', 'created'], 'integer'],
            [['menu_price'], 'number'],
            [['menu_attr_info', 'config'], 'string'],
            [['menu_name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'menu_id' => 'Menu ID',
            'menu_name' => 'Menu Name',
            'menu_price' => 'Menu Price',
            'menu_num' => 'Menu Num',
            'menu_attr_info' => 'Menu Attr Info',
            'add_no' => 'Add No',
            'is_cancel' => 'Is Cancel',
            'is_confirm' => 'Is Confirm',
            'config' => 'Config',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\UserOrderMenuQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\UserOrderMenuQuery(get_called_class());
    }
}
