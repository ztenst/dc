<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_print_log".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $name
 * @property string $content
 * @property integer $print_type
 * @property integer $success
 * @property integer $fail
 * @property integer $updated
 * @property integer $created
 */
class ShopPrintLog extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_print_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'name', 'content', 'print_type', 'created'], 'required'],
            [['shop_id', 'print_type', 'success', 'fail', 'updated', 'created'], 'integer'],
            [['content'], 'string'],
            [['name'], 'string', 'max' => 255],
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
            'content' => '打印的html内容',
            'print_type' => '打印类型',
            'success' => '打印成功次数',
            'fail' => '打印失败次数',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopPrintLogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopPrintLogQuery(get_called_class());
    }
}
