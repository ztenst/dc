<?php

namespace app\models\ext;

use Yii;
use app\helpers\redis\RedisHash;
use app\helpers\Storage;
use app\helpers\Timestamp;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;

/**
 * 菜单
 */
class ShopMenu extends \app\models\ShopMenu
{
    const STATUS_ACTIVE = 1;

    const STATUS_INACTIVE = 0;

    const RECOMMEND_YES = 1;
    const RECOMMEND_NO = 0;
    const NEW_YES = 1;
    const NEW_NO = 0;

    /**
     * config扩展字段默认值
     */
    private static function defaultConfig()
    {
        return [
            'attrs' => [
                'customAttr' => [],
                'sizeAttr' => [
                    'attrName' => '尺寸',
                    'attrValues' => []
                ],
            ],
            'isRecommend' => self::RECOMMEND_NO,
            'isNew' => self::NEW_NO,
        ];
    }

    /**
     * 商家编辑\新增场景
     */
    const SCENARIO_SHOP_EDIT = 'shop_edit';

    /**
     * init
     */
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_UPDATE, [$this, 'updateTodayStock']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'updateTodayStock']);
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
     * scenarios
     * @return array
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(),  [
            self::SCENARIO_SHOP_EDIT => ['name','price','config','cate_id','image','unit_id','stock','status','isNew','isRecommend'],//这些字段能被load进来并验证
        ]);
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge([
            ['isNew', 'in','range'=>[self::NEW_YES,self::NEW_NO]],
            ['isRecommend', 'in', 'range'=>[self::RECOMMEND_NO, self::RECOMMEND_YES]],
        ], parent::rules());
    }

    /**
     * 获得状态列表
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => '在售',
            self::STATUS_INACTIVE => '估清',
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

    public function getUnit()
    {
        return  $this->hasOne(ShopMenuUnit::className(),['id'=>'unit_id']);
    }

    public function getCate()
    {
        return $this->hasOne(ShopMenuCate::className(),['id'=>'cate_id']);
    }

    /**
     * 设置扩展字段attrs值
     */
    public function setAttrs($value)
    {
        $this->setConfigField('attrs', $value);
    }

    /**
     * 获取扩展字段attrs
     */
    public function getAttrs()
    {
        return $this->getConfigField('attrs');
    }

    /**
     * 获取完整连接的logo地址
     * @return string logo地址
     */
    public function getImage($width=0, $height=0)
    {
        return Storage::fixImageUrl($this->image, $width, $height);
    }

    /**
     * 格式化菜品规格数据格式，数组的json格式为：
     * {
     *    "customAttr": [
     *        {
     *            "attrName": "口味",
     *            "attrValues": [
     *                {
     *                    "name": "番茄汁"
     *                },
     *                {
     *                    "name": "蘑菇汁"
     *                }
     *            ]
     *        },
     *    ],
     *    "sizeAttr": {
     *        "attrName": "尺寸",
     *        "attrValues": [
     *            {
     *                "name": "半份",
     *                "price": 32
     *            },
     *            {
     *                "name": "一份",
     *                "price": 64
     *            }
     *        ]
     *    }
     *}
     */
    public function normalizeAttrs(array $attrs)
    {
        $defaultConfig = self::defaultConfig();
        $customAttr = ArrayHelper::getValue($attrs, 'customAttr', $defaultConfig['attrs']['customAttr']);
        foreach($customAttr as $key => $attrItem) {
            $this->normalizeAttrItem($customAttr, $key);
        }
        $sizeAttr = ArrayHelper::getValue($attrs, 'sizeAttr', $defaultConfig['attrs']['sizeAttr']);
        $this->normalizeAttrValues([$sizeAttr], 0);
        return $attrs;
    }

    /**
     * 处理规格数组
     * [
     *      'attrName'=>'xxx',
     *      'attrValues' => []
     * ]
     * - attrName: 必须
     * - attrValues: 默认空数组
     */
    private function normalizeAttrItem(&$attr, $key)
    {
        $attrItem = ArrayHelper::filter($attr[$key], ['attrName','attrValues']);
        if(!isset($attr[$key]['attrName'])) {
            unset($attr[$key]);
        }
        $attrValues = ArrayHelper::getValue($attr[$key], 'attrValues', []);
        $attrItem['attrValues'] = $this->normalizeAttrValues($attrValues);
        // 判断单个规格下面的规格值不准重复，防止设置了相同名的规格值但价格不同，会出问题
        $tmp = [];
        foreach($attrItem['attrValues'] as $k=>$item) {
            if(in_array($item['name'], $tmp)) {
                $this->addError('attrs', $attrItem['attrName'].'-'.$item['name'].'不能重复');
                break;
            }
            $tmp[] = $item['name'];
        }
    }

    /**
     * 处理规格的规格值数组
     * [
     *      ['name'=>'xx', 'price'=>'xx']
     * ]
     */
    private function normalizeAttrValues($attrValues)
    {
        foreach($attrValues as $attrKey => $attrValueItem) {
            $this->normalizeAttrValue($attrValues, $attrKey);
        }
        return $attrValues;
    }

    /**
     * 处理单独规格
     * ['name'=>xx', 'price'=>'xx']
     * - name: 必须
     * - price: 可选
     */
    public function normalizeAttrValue(&$attrValues, $key)
    {
        $item = ArrayHelper::filter($attrValues[$key], ['name','price']);
        if(!isset($item['name'])) {
            unset($attrValues[$key]);
        }
    }

    /**
     * 今天已售出量
     * @return integer
     */
    public function getTodaySaleNum()
    {
        return UserOrderMenu::find()->where('menu_id=:menuId and is_cancel=:nocancel and is_confirm=:isconfirm and created>=:begin')
                                    ->params([
                                        ':menuId' => $this->id,
                                        ':nocancel' => UserOrderMenu::CANCEL_NO,
                                        ':isconfirm' => UserOrderMenu::CONFIRM_YES,
                                        ':begin'=>Timestamp::getDayBeginTime()
                                    ])
                                    ->select('count(id)')
                                    ->scalar();
    }

    /**
     * 该商品是否有每日库存限制
     * @return boolean true表示有，false表示无
     */
    public function hasStockLimit()
    {
        return $this->stock>0;
    }

    /**
     * 获取今日剩余库存量
     * @param boolean $refresh 是否重新计算剩余库存
     * @return integer 剩余库存数量
     */
    public function getTodayStock($refresh=false)
    {
        if(!$this->hasStockLimit()) {
            return 99999;
        }
        list($key, $field) = $this->getTodayStockRedisKeyAndField();
        $redis = Yii::$app->redis;
        $stock = $redis->hget($key, $field);
        //redis不存在该数据，重新生成
        if($stock===false || $refresh) {
            $stock = $this->stock - $this->getTodaySaleNum();
            $redis->hset($key, $field, $stock);
            $redis->expireat($key, Timestamp::getDayEndTime());
        }
        return intval($stock);
    }

    /**
     * 更新今日库存量redis
     * @param integer $incrementValue 增加（扣减）库存的值，扣减时该值必须为负数
     * @return integer 返回今日剩余库存量
     */
    public function updateTodayStock($incrementValue=null)
    {
        if(!$this->hasStockLimit()) {
            return 99999;
        }
        //用于事件时，$incrementValue为 Event，目前只有insert和update两个事件
        if(($incrementValue instanceof \yii\base\Event)) {
            //如果两个事件发生在商家编辑场景
            if($incrementValue->sender->scenario==self::SCENARIO_SHOP_EDIT) {
                $incrementValue = null;
            } else {
                //这个貌似目前还没有应用场景会走进来= =！除非批量跑脚本
                return $this->getTodayStock();
            }
        }
        if($incrementValue===null) {
            return $this->getTodayStock(true);
        } else {
            list($key, $field) = $this->getTodayStockRedisKeyAndField();
            $redis = Yii::$app->redis;
            return $redis->hincrby($key, $field, $incrementValue);
        }
    }

    /**
     * 获取今日库存redis的key和field
     * @return ['key name','field name']
     */
    public function getTodayStockRedisKeyAndField()
    {
        //[keyname, fieldname]
        //keyname=>  appId:menu:stock
        //fieldname=>  s:shopId:m:menuId
        return [Yii::$app->id.':menu:stock', 's:'.$this->shop_id.':m:'.$this->id];
    }

    /**
     * 该菜品在点菜时是否需要选择规格
     * @return boolean true为是，false为否
     */
    public function getIsNeedSelectAttrs()
    {
        $attrs = $this->getConfigField('attrs');
        if($attrs['customAttr'] && $attrs['customAttr'][0]['attrValues'] || $attrs['sizeAttr']['attrValues']) {
            return true;
        }
        return false;
    }

    /**
     * 检查规格名和规格值是否都存在
     * 用于提交菜品到订单时判断，不需要选择规格的会直接返回false
     * 如“口味”下面有“微辣”“中辣”，那么会先检查“口味”存不存在，再检查“微辣”是否存在“口味”下面
     * @return boolean 存在返回true，否则返回false
     */
    public function checkAttrExist($attrName=null, $attrValue=null)
    {
        if(!$this->getIsNeedSelectAttrs()) {
            return false;
        }
        if($attrName===null && $attrValue===null) {
            throw new Exception('至少传入一个参数');
        }
        $attrs = $this->getConfigField('attrs');
        //把自定义的和内置的合并进行验证
        $allAttrs = array_merge($attrs['customAttr'], [$attrs['sizeAttr']]);

        $attrNameIsNull = $attrName===null;
        foreach($allAttrs as $attr) {
            if(!$attrNameIsNull) {
                if($attr['attrName']==$attrName) {
                    if($attrValue===null) {
                        return true;
                    }
                }
            }
            //能走到这只有一种情况：attrName正确并且attrValue要验证
            foreach($attr['attrValues'] as $item) {
                if($item['name']==$attrValue) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 获取内置尺寸规格的名称
     * @return string
     */
    public static function getSizeAttrName()
    {
        return ArrayHelper::getValue(self::defaultConfig(), 'sizeAttr.attrName', '尺寸');
    }

    /**
     * 设置是否推荐
     */
    public function setIsRecommend($value)
    {
        $this->setConfigField('isRecommend', $value);
    }

    /**
     * 获取是否推荐
     */
    public function getIsRecommend()
    {
        return $this->getConfigField('isRecommend');
    }

    /**
     * 设置是否新品
     */
    public function setIsNew($value)
    {
        $this->setConfigField('isNew', $value);
    }

    /**
     * 获取是否新品
     */
    public function getIsNew()
    {
        return $this->getConfigField('isNew');
    }
}
