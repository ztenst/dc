<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_admin".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $account
 * @property string $password
 * @property string $avatar
 * @property string $username
 * @property string $phone
 * @property integer $is_deleted
 * @property integer $status
 * @property integer $role
 * @property integer $distribution_admin_id
 * @property string $config
 * @property integer $last_login_time
 * @property integer $updated
 * @property integer $created
 */
class ShopAdmin extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_admin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'account', 'password', 'status', 'role', 'distribution_admin_id', 'config', 'created'], 'required'],
            [['shop_id', 'is_deleted', 'status', 'role', 'distribution_admin_id', 'last_login_time', 'updated', 'created'], 'integer'],
            [['config'], 'string'],
            [['account', 'password', 'avatar'], 'string', 'max' => 255],
            [['username'], 'string', 'max' => 32],
            [['phone'], 'string', 'max' => 11],
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
            'account' => 'Account',
            'password' => 'Password',
            'avatar' => 'Avatar',
            'username' => 'Username',
            'phone' => 'Phone',
            'is_deleted' => 'Is Deleted',
            'status' => 'Status',
            'role' => 'Role',
            'distribution_admin_id' => 'Distribution Admin ID',
            'config' => 'Config',
            'last_login_time' => 'Last Login Time',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\ShopAdminQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\ShopAdminQuery(get_called_class());
    }
}
