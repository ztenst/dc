<?php

namespace app\models\ext;

use Yii;

/**
 * This is the model class for table "statistics_desk_use".
 * @vserion 2017-07-17 16:14:59通过gii生成
 */
class StatisticsDeskUse extends \app\models\StatisticsDeskUse
{
    /**
     * 该字段用于mysql使用聚合函数时存放数据的字段
     * 如使用count/sum/average等
     */
    public $groupCount = 0;
    /**
     * 餐桌总数，用于联表查询数据字段存放
     */
    public $deskCount = 0;
}
