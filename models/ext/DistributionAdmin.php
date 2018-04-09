<?php

namespace app\models\ext;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "distribution_admin".
 * @vserion 2017-05-11 10:48:46通过gii生成
 */
class DistributionAdmin extends \app\models\DistributionAdmin implements \yii\web\IdentityInterface
{
    /**
     * 注册场景（注册只能通过总后台来注册，不开放用户自己注册）
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
     * @var config字段中的虚拟字段，字段名=>默认值
     */
    private $_configFields = [

    ];
    /**
     * @var config字段的数组形式，程序set\get都走的这个数组形式，入库才进行json_encode
     */
    private $_configArr;

    /**
     * init
     */
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_FIND, [$this, 'decodeConfig']);
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
            'phone' => '手机号',
            'city_id' => '城市',
        ]);
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge([
            [['newPassword'], 'required', 'on'=>self::SCENARIO_REGISTER],
            [['newPassword'],'string','min'=>5],
            [['repeatPassword'], 'compare', 'compareAttribute'=>'newPassword', 'skipOnEmpty'=>false, 'message'=>'两次密码不一致'],
            [['username'],'required'],
            [['status'],'default','value'=>self::STATUS_ACTIVE],
            ['account','unique', 'targetAttribute'=>['account','is_deleted'],'message'=>'"{value}"已被注册'],
            [['config'], 'default', 'value'=>function($model, $attribute){
                try {
                    return Json::encode($model->configArr);
                } catch(Exception $e) {
                    $model->addError('config', $e->getMessage());
                }
            }],//保存前进行数据编码操作
        ], parent::rules());
    }

    /**
     * 对config字段进行json解码
     */
    public function decodeConfig()
    {
        $configArr = Json::decode($this->config);
        $this->_configArr = array_merge($this->_configFields, $configArr);
    }

    /**
     * getter方法，获取config字段数组形式
     * @return array
     */
    public function getConfigArr()
    {
        if($this->_configArr===null) {
            $this->_configArr = $this->_configFields;
        }
        return $this->_configArr;
    }

    /**
     * setter方法，设置config字段数组形式
     * @param string $field 虚拟字段名
     * @param mixed $value 虚拟字段值
     */
    public function setConfigArr($field, $value)
    {
        if($this->hasConfigField($field)) {
            $this->_configArr[$field] = $value;
        }
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
        return static::find()->where(['account'=>$account])->undeleted()->status(self::STATUS_ACTIVE)->one();
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
     * config字段中是否存在指定虚拟字段
     * @param string $fieldName 虚拟字段名称
     * @return boolean 返回布尔值，true表示存在，false表示不存在
     */
    public function hasConfigField($fieldName)
    {
        return isset($this->_configFields[$fieldName]);
    }

    /**
     * 重写find()以设置默认作用域
     */
    public static function find()
    {
        return parent::find()->undeleted();
    }

    /**
     * 获取所属城市
     * @return Query
     */
    public function getCity()
    {
        return $this->hasOne(AreaCity::className(), ['id'=>'city_id']);
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
