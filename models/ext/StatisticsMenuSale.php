<?php

namespace app\models\ext;

use Yii;

/**
 * This is the model class for table "statistics_menu_sale".
 * @vserion 2017-07-17 10:00:26通过gii生成
 */
class StatisticsMenuSale extends \app\models\StatisticsMenuSale
{
    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge([
            [['ymd'], 'default', 'value'=>function($model, $attribute) {
                $model->$attribute = date('Ymd');
            }],
        ], parent::rules());
    }
}
