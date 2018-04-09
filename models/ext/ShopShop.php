<?php

namespace app\models\ext;

use app\behaviors\ArCacheBehavior;
use Yii;
use app\models\ShopSettingCollection;

/**
 * This is the model class for table "shop_shop".
 * @vserion 2017-05-15 11:46:29通过gii生成
 */
class ShopShop extends \app\models\ShopShop
{
    /**
     * 启用状态
     */
    const STATUS_ACTIVE = 1;
    /**
     * 禁用状态
     */
    const STATUS_INACTIVE = 0;

    /**
     * 商家修改编辑场景
     */
    const SCENARIO_SHOP_EDIT = 'shop_edit';

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_SHOP_EDIT => ['name'],
        ]);
    }

    public function behaviors()
    {
        return [
            [
                'class' => ArCacheBehavior::className()
            ]
        ];
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
     * 字段labels
     * @return array
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        return array_merge($attributeLabels, [
            'name' => '商家名称',
            'city_id' => '城市',
            'city' => '城市',
            'sort' => '排序',
            'status' => '状态',
            'statusText' => '状态',
        ]);
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

    /**
     * 获取商家其他信息模型
     * @return Query
     */
    public function getShopInfo()
    {
        return $this->hasOne(ShopShopInfo::className(), ['shop_id'=>'id']);
    }

    /**
     * 获取该商家所有订单
     * @return UserOrderQuery
     */
    public function getOrders()
    {
        return $this->hasMany(UserOrder::className(), ['shop_id'=>'id']);
    }

    /**
     * 获取该商家第一个完成状态的订单
     * @return UserOrderQuery
     */
    public function getFirstPaidOrder()
    {
        return $this->hasOne(UserOrder::className(), ['shop_id'=>'id'])
                    ->orderBy('id asc')
                    ->andWhere(UserOrder::tableName().'.status=:status', [':status'=>UserOrder::STATUS_PAID]);
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

    private $_shopSettings;

    /**
     * 从数据库加载所有商家配置
     * @return ShopSetting[]
     */
    private function loadShopSettings()
    {
        $all = ShopSetting::find()->shop($this->id)->indexBy('setting_name')->all();
        return $all;
    }

    public function getShopSettings()
    {
        if($this->_shopSettings===null) {
            $this->_shopSettings = new ShopSettingCollection($this->loadShopSettings(), [
                'shop' => $this,
            ]);
        }
        return $this->_shopSettings;
    }
}
