<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[ShopMenuUnit]].
 *
 * @see ShopMenuUnit
 */
class ShopMenuUnitQuery extends \app\base\ActiveQuery
{
    public function shop($shopId)
    {
        return $this->andWhere(['shop_id'=>$shopId]);
    }
}
