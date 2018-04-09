<?php

namespace app\models\ext;

use Yii;

/**
 * This is the model class for table "statistics_sale_price".
 * @vserion 2017-07-14 09:17:49通过gii生成
 */
class StatisticsSalePrice extends \app\models\StatisticsSalePrice
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
