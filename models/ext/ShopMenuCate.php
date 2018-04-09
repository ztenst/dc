<?php

namespace app\models\ext;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * 菜单分类
 */
class ShopMenuCate extends \app\models\ShopMenuCate
{
    /**
     * status字段枚举值，释义见{getStatusList()}
     * [2017-07-28]该常量值暂废弃不用，给以后做预留
     */
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * show_type字段枚举值，释义见{getShowTypeList()}
     */
    const SHOW_ALL = 1;
    const SHOW_ONLY_SHOP = 2;

    /**
     * 商家编辑\新增场景
     */
    const SCENARIO_SHOP_EDIT = 'shop_edit';

    public $menuCount = 0;

    public function init()
    {
        parent::init();
        $this->show_type = self::SHOW_ALL;
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * scenarios
     * @return array
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(),  [
            self::SCENARIO_SHOP_EDIT => ['name','show_type'],//该场景下需要验证的字段
        ]);
    }

    /**
     * 获取显示状态列表
     * @return array
     */
    public static function getShowTypeList()
    {
        return [
            self::SHOW_ALL => '前后台显示',
            self::SHOW_ONLY_SHOP => '仅后台显示',
        ];
    }

    /**
     * 状态列表
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
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['menuCount'], 'integer'],
            ['show_type', 'in', 'range'=>array_keys(self::getShowTypeList())],
            [['status'], 'default', 'value'=>self::STATUS_ACTIVE]
        ]);
    }

    /**
     * 获取分类下的菜品
     */
    public function getMenus()
    {
        return $this->hasMany(ShopMenu::className(), [
            'cate_id' => 'id',
        ]);
    }

    /**
     * 获取状态文字
     * @return string 状态文字
     */
    public function getStatusText($default='(未知)')
    {
        $statusList = self::getStatusList();
        return ArrayHelper::getValue($statusList, $this->status, $default);
    }

    /**
     * 获取状态文字
     * @return string 状态文字
     */
    public function getShowTypeText($default='(未知)')
    {
        $statusList = self::getShowTypeList();
        return ArrayHelper::getValue($statusList, $this->show_type, $default);
    }
}
