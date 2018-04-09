<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_user".
 *
 * @property integer $user_id
 * @property integer $shop_id
 * @property string $phone
 * @property integer $sex
 * @property integer $birthday
 * @property string $config
 * @property integer $updated
 * @property integer $created
 */
class ShopUser extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'shop_id', 'config', 'created'], 'required'],
            [['user_id', 'shop_id', 'sex', 'birthday', 'updated', 'created'], 'integer'],
            [['config'], 'string'],
            [['phone'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'shop_id' => 'Shop ID',
            'phone' => 'Phone',
            'sex' => 'Sex',
            'birthday' => 'Birthday',
            'config' => 'Config',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopUserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopUserQuery(get_called_class());
    }
}
