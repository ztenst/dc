<?php

namespace app\models\ext;

use Yii;

class UserOrder extends \app\models\UserOrder
{   
    const  STATUS_NO = 0;
    const STATUS_PAY = 1;
    const STATUS_CANCEL = 2;

    public static $statusArray = [
        self::STATUS_PAY => '支付成功',
        self::STATUS_CANCEL => '支付取消',
        self::STATUS_NO => '未支付'
    ];

    /**
     * @var config字段中的虚拟字段，字段名=>默认值
     */
    private $_defaultConfig = [
        'shopAdminUsername' => '',//最后操作商家用户名
        'menuNum' => 0,//点菜种类数量
        'statusRecord' => []
    ];

    /**
     * 该字段用于mysql使用聚合函数时存放数据的字段
     * 如使用count/sum/average等
     */
    public $groupCount = 0;
    /**
     * 该字段用于分日期统计时用到
     * 目前用于每日定时统计销售额脚本
     */
    public $ymd;

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        list($usec, $sec) = explode(" ", microtime());
        return array_merge([
            ['menuNum', 'integer'],
            ['shopAdminUsername', 'string'],
            [['trade_no'], 'default', 'value'=>(float)sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000).sprintf('%03d', rand(0, 999))],//生成交易号，最好指定使用场景
        ], parent::rules());
    }


    /**
     * 获取订单相关的已点菜单
     * @return UserOrderMenuQuery
     */
    public function getMenus()
    {
        return $this->hasMany(UserOrderMenu::className(),['order_id'=>'id']);
    }

    /**
     * 获取该订单关联的用户
     * @return UserMemberQuery
     */
    public function getUsers()
    {
        return $this->hasMany(UserMember::className(), ['id'=>'user_id'])->viaTable('user_order_menu', ['order_id'=>'id']);
    }

    public function getShop()
    {
        return $this->hasOne(ShopShop::className(),['id' => 'shop_id']);
    }


    /**
     * 获取最后操作的商家姓名
     * @return string
     */
    public function getShopAdminUsername($default='(无法获取)')
    {
        return $this->getConfigField('shopAdminUsername', $default);
    }

    /**
     * 设置最后操作管理员名称
     * @param string $value
     */
    public function setShopAdminUsername($value)
    {
        $this->setConfigField('shopAdminUsername', $value);
    }

    /**
     * 获取点菜数量
     * @return integer
     */
    public function getMenuNum()
    {
        return $this->getConfigField('menuNum');
    }

    /**
     * 设置点菜数量
     * @param integer $num 点菜数量
     */
    public function setMenuNum($num)
    {
        $this->setConfigField('menuNum', $num);
    }

    public function setStatusRecord($status = [])
    {
        $this->setConfigField('statusRecord',$status);
    }

    public function getStatusRecord()
    {
        return $this->getConfigField('statusRecord');
    }

    public function addStatusRecord($status)
    {
        $statusRecord = $this->getStatusRecord();
        array_push($statusRecord,[
            'status' => $status,
            'time' => time()
        ]);
        $this->setStatusRecord($statusRecord);
    }

    public function getFormatStatusRecord()
    {
        $statusRecord = $this->getStatusRecord();
        if($statusRecord){
            foreach ($statusRecord as &$status){
                $status['time'] = date('H:i',$status['time']);
                $status['msg'] = self::$statusArray[$status['status']];
            }
        }
        return $statusRecord;
    }
}
