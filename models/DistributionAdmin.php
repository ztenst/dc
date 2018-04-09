<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "distribution_admin".
 *
 * @property integer $id
 * @property string $account
 * @property string $password
 * @property string $username
 * @property string $phone
 * @property integer $city_id
 * @property integer $is_deleted
 * @property integer $status
 * @property string $config
 * @property integer $updated
 * @property integer $created
 */
class DistributionAdmin extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'distribution_admin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account', 'password', 'city_id', 'status', 'config', 'created'], 'required'],
            [['city_id', 'is_deleted', 'status', 'updated', 'created'], 'integer'],
            [['config'], 'string'],
            [['account', 'password'], 'string', 'max' => 255],
            [['username'], 'string', 'max' => 20],
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
            'account' => 'Account',
            'password' => 'Password',
            'username' => 'Username',
            'phone' => 'Phone',
            'city_id' => 'City ID',
            'is_deleted' => 'Is Deleted',
            'status' => 'Status',
            'config' => 'Config',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\DistributionAdminQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\DistributionAdminQuery(get_called_class());
    }
}
