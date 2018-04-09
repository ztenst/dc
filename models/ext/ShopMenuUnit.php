<?php

namespace app\models\ext;

use Yii;

/**
 * This is the model class for table "shop_menu_unit".
 * @vserion 2017-05-22 13:49:30通过gii生成
 */
class ShopMenuUnit extends \app\models\ShopMenuUnit
{
    /**
     * 商家编辑场景
     */
    const SCENARIO_SHOP_EDIT = 'shop_edit';

    /**
     * 场景验证字段设置
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_SHOP_EDIT => ['name'],
        ];
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge([
            [['name'], 'default', 'value'=>'(未命名)'],
            ['name', 'unique', 'targetAttribute'=>['name', 'shop_id'], 'message'=>'该单位已存在'],
        ], parent::rules());
    }
}
