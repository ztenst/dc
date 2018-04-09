<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_member".
 *
 * @property integer $id
 * @property string $nickname
 * @property string $phone
 * @property integer $sex
 * @property integer $birthday
 * @property string $avatar
 * @property string $unionid
 * @property integer $updated
 * @property integer $created
 */
class UserMember extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nickname', 'sex', 'avatar', 'unionid', 'created'], 'required'],
            [['sex', 'birthday', 'updated', 'created'], 'integer'],
            [['nickname', 'avatar', 'unionid'], 'string', 'max' => 255],
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
            'nickname' => 'Nickname',
            'phone' => 'Phone',
            'sex' => 'Sex',
            'birthday' => 'Birthday',
            'avatar' => 'Avatar',
            'unionid' => 'Unionid',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\UserMemberQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\UserMemberQuery(get_called_class());
    }
}
