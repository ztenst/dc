<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sms_code".
 *
 * @property integer $id
 * @property string $phone
 * @property string $msg
 * @property string $code
 * @property integer $created
 * @property integer $updated
 * @property integer $status
 */
class SmsCode extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sms_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'msg', 'code', 'created'], 'required'],
            [['created', 'updated', 'status'], 'integer'],
            [['phone'], 'string', 'max' => 13],
            [['msg', 'code'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Phone',
            'msg' => 'Msg',
            'code' => 'Code',
            'created' => 'Created',
            'updated' => 'Updated',
            'status' => 'Status',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\SmsCodeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\SmsCodeQuery(get_called_class());
    }
}
