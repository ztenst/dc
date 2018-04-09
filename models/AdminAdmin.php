<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "admin_admin".
 *
 * @property integer $id
 * @property string $account
 * @property string $password
 * @property string $username
 * @property integer $status
 * @property integer $is_deleted
 * @property integer $updated
 * @property integer $created
 */
class AdminAdmin extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_admin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account', 'password', 'status', 'created'], 'required'],
            [['status', 'is_deleted', 'updated', 'created'], 'integer'],
            [['account', 'password', 'username'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account' => 'Account',
            'password' => 'Password',
            'username' => 'Username',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\AdminAdminQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\AdminAdminQuery(get_called_class());
    }
}
