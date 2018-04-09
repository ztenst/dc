<?php

namespace app\models\queries;
use app\models\ext\ShopSetting;

/**
 * This is the ActiveQuery class for [[\app\models\ShopSetting]].
 *
 * @see \app\models\ShopSetting
 */
class ShopSettingQuery extends \app\base\ActiveQuery
{
    /**
     * 指定商家id
     * @return ShopSettingQuery
     */
    public function shop($shopId)
    {
        return $this->andWhere(['shop_id'=>$shopId]);
    }

    public function searchKeywords()
    {
        return $this->andWhere(['setting_name' => 'searchKeywords']);
    }
}
