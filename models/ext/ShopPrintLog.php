<?php

namespace app\models\ext;

use Yii;

/**
 * This is the model class for table "shop_print_log".
 * @vserion 2017-07-19 17:20:22通过gii生成
 */
class ShopPrintLog extends \app\models\ShopPrintLog
{
    /**
     * 前台打印
     */
    const PRINT_TYPE_FRONT = 1;
    /**
     * 后厨打印
     */
    const PRINT_TYPE_KITCHEN = 2;

    /**
     * 验证规则
     */
    public function rules()
    {
        return array_merge([
            ['print_type', 'in', 'range'=>[self::PRINT_TYPE_FRONT, self::PRINT_TYPE_KITCHEN], 'message'=>'打印类型指定错误'],
        ], parent::rules());
    }
}
