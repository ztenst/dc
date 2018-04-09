<?php

namespace app\models\ext;

use app\behaviors\ArCacheBehavior;
use Yii;
use app\helpers\Storage;

/**
 * This is the model class for table "shop_shop_info".
 * @vserion 2017-05-15 14:09:09通过gii生成
 */
class ShopShopInfo extends \app\models\ShopShopInfo
{
    /**
     * config扩展字段默认值
     */
    private $_defaultConfig = [
        'phone' => '',
    ];

    /**
     * 场景设置
     * @return array
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            ShopShop::SCENARIO_SHOP_EDIT => ['address','phone','description'],
        ]);
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge([
            [['phone'], 'string']
        ], parent::rules());
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
                'fieldDefaultValue' => $this->_defaultConfig,
                'getter' => 'getConfigField',
                'setter' => 'setConfigField',
            ],
            [
                'class' => ArCacheBehavior::className()
            ]
        ];
    }

    /**
     * 字段labels
     * @return array
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        return array_merge($attributeLabels, [
            'logo' => '商家logo',
            'fullLogo' => 'logo',
            'address' => '地址',
            'description' => '商家介绍',
        ]);
    }

    /**
     * 商家基本信息
     * @return Query
     */
    public function getShop()
    {
        return $this->hasOne(ShopShop::className(), ['id'=>'shop_id']);
    }

    /**
     * 获取完整连接的logo地址
     * @return string logo地址
     */
    public function getLogo($width=0, $height=0)
    {
        return Storage::fixImageUrl($this->logo, $width, $height);
    }

    /**
     * 赋值电话字段
     * @param string $phone 电话
     */
    public function setPhone($value)
    {
        $this->setConfigField('phone', $value);
    }

    /**
     * 获取电话
     * @return string
     */
    public function getPhone()
    {
        return $this->getConfigField('phone');
    }
}
