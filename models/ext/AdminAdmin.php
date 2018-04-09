<?php

namespace app\models\ext;

use Yii;

/**
 * This is the model class for table "admin_admin".
 * @vserion 2017-05-08 09:06:50通过gii生成
 */
class AdminAdmin extends \app\models\AdminAdmin implements \yii\web\IdentityInterface
{
    /**
     * 注册场景
     */
    const SCENARIO_REGISTER = 'register';
    /**
     * 启用状态
     */
    const STATUS_ACTIVE = 1;
    /**
     * 禁用状态
     */
    const STATUS_INACTIVE = 0;
    /**
     * @var string 设置的新密码明文
     */
    private $_newPassword = '';
    /**
     * @var string 重复密码明文
     */
    public $repeatPassword = '';

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['newPassword'], 'required', 'on'=>self::SCENARIO_REGISTER],
            [['newPassword'],'string','min'=>5],
            [['repeatPassword'], 'compare', 'compareAttribute'=>'newPassword', 'skipOnEmpty'=>false, 'message'=>'两次密码不一致'],
            [['username'],'required'],
            [['status'],'default','value'=>self::STATUS_ACTIVE],
            ['account','unique', 'targetAttribute'=>['account','is_deleted'],'message'=>'"{value}"已被注册'],
        ]);
    }

    /**
     * @param string $value 新密码明文
     * @return void
     */
    public function setNewPassword($value)
    {
        $this->_newPassword = $value;
        if(!empty($value)) {
            $this->password = Yii::$app->getSecurity()->generatePasswordHash($value);
        }
    }

    /**
     * 获取新密码明文
     */
    public function getNewPassword()
    {
        return $this->_newPassword;
    }

    /**
     * 将密码hash化存储[场景：注册]
     * @return void
     */
    public function passwordHash()
    {
        if (!empty($this->newPassword)) {
            $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->newPassword);
        }
    }

    /**
     * 字段labels
     * @return array
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        return array_merge($attributeLabels, [
            'account' => '账号名',
            'password' => '密码',
            'newPassword' => '新密码',
            'repeatPassword' => '重复新密码',
            'status' => '状态',
            'statusText' => '状态',
            'username' => '姓名',
        ]);
    }

    /**
     * 获得状态列表
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => '启用',
            self::STATUS_INACTIVE => '禁用',
        ];
    }

    /**
     * 获取状态文字
     * @return string 状态文字
     */
    public function getStatusText()
    {
        $statusList = self::getStatusList();
        return $statusList[$this->status];
    }

    /**
     * 重要删除逻辑修改
     * 由于该表的重要性，所有删除逻辑改为逻辑删除
     * 该函数会影响到[[delete()]]、[[deleteInternal()]]等删除函数
     */
    public static function deleteAll($condition = '', $params = [])
    {
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), ['is_deleted'=>1, 'status'=>self::STATUS_INACTIVE, 'updated'=>time()], $condition, $params);

        return $command->execute();
    }

    /**
     * 根据账号名查找用户，主要用于登录
     */
    public static function findByAccount($account)
    {
        return static::findOne(['account'=>$account]);
    }

    /**
     * 验证输入密码是否正确
     * @param  string $inputPassword 表单输入的密码
     * @return boolean 验证成功返回true，验证失败返回false
     */
    public function validatePassword($inputPassword)
    {
        return Yii::$app->getSecurity()->validatePassword($inputPassword, $this->password);

    }

    /**
     * 重写find()以设置默认作用域
     */
    public static function find()
    {
        return parent::find()->undeleted();
    }

    //------------------------接口函数实现-------------------------------
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type=null)
    {

    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {

    }

    public function validateAuthKey($authKey)
    {
        return true;
    }
}
