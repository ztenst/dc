<?php

namespace app\models\ext;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "shop_admin".
 * @vserion 2017-05-18 10:25:47通过gii生成
 */
class ShopAdmin extends \app\models\ShopAdmin implements \yii\web\IdentityInterface
{
    /**
     * 分销商编辑场景
     */
    const SCENARIO_DISTRIBUTION_EDIT = 'distribution_edit';
    /**
     * 分销商注册场景
     */
    const SCENARIO_DISTRIBUTION_REGISTER = 'distribution_register';
    /**
     * 商家后台编辑场景
     */
    const SCENARIO_SHOP_EDIT = 'shop_edit';
    /**
     * 商家后台注册场景
     */
    const SCENARIO_SHOP_REGISTER = 'shop_register';

    /**
     * 启用状态
     */
    const STATUS_ACTIVE = 1;
    /**
     * 禁用状态
     */
    const STATUS_INACTIVE = 0;
    /**
     * 商家管理员角色
     */
    const ROLE_ADMIN = 1;
    /**
     * 商家员工角色
     */
    const ROLE_STAFF = 2;
    /**
     * @var string 设置的新密码明文
     */
    private $_newPassword = '';
    /**
     * @var string 重复密码明文
     */
    public $repeatPassword = '';

    /**
     * config扩展字段默认值
     */
    private static function defaultConfig()
    {
        return [];
    }

    /**
     * behaviors
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => \app\behaviors\ArExpandFieldBehavior::className(),
                'expandFieldName' => 'config',
                'fieldDefaultValue' => self::defaultConfig(),
                'getter' => 'getConfigField',
                'setter' => 'setConfigField',
            ]
        ];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_DISTRIBUTION_EDIT => ['account','newPassword','repeatPassword','phone','username','stauts'],
            self::SCENARIO_DISTRIBUTION_REGISTER => ['account','newPassword','repeatPassword','phone','username','stauts','role'],
            self::SCENARIO_SHOP_EDIT => ['account', 'newPassword'],
            self::SCENARIO_SHOP_REGISTER => ['account', 'newPassword','status','role'],
        ]);
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge([
            [['newPassword'], 'required', 'on'=>[self::SCENARIO_DISTRIBUTION_REGISTER,self::SCENARIO_SHOP_REGISTER]],
            [['newPassword'],'string','min'=>5],
            [['repeatPassword'], 'compare', 'compareAttribute'=>'newPassword', 'skipOnEmpty'=>false, 'message'=>'两次密码不一致'],
            [['username'],'required'],
            [['status'],'default','value'=>self::STATUS_ACTIVE],
            ['account','unique', 'targetAttribute'=>['account','is_deleted'],'message'=>'"{value}"已被注册'],
            [['role'], 'default', 'value'=>$this->scenario==self::SCENARIO_DISTRIBUTION_EDIT?self::ROLE_ADMIN:self::ROLE_STAFF]
        ],parent::rules());
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
            'shop_id' => '商家',
            'phone' => '手机号'
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
     * 获取角色列表
     * @return array
     */
    public static function getRoleList()
    {
        return [
            self::ROLE_ADMIN => '店长',
            self::ROLE_STAFF => '职员',
        ];
    }

    /**
     * 获取状态文字
     * @return string 状态文字
     */
    public function getStatusText()
    {
        $statusList = self::getStatusList();
        return isset($statusList[$this->status]) ? $statusList[$this->status] : '';
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

    /**
     * 获得所属商家
     * @return ShopShopQuery
     */
    public function getShop()
    {
        return $this->hasOne(ShopShop::className(), ['id'=>'shop_id']);
    }

    /**
     * 获取商家名称
     * @return string
     */
    public function getShopName($default = '(商家不存在)')
    {
        return $this->shop === null ? $default : $this->shop->name;
    }

    /**
     * 记录登录时间
     * @return boolean
     */
    public function recordLoginTime()
    {
        $this->last_login_time = time();
        return $this->save(true, ['last_login_time']);
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
