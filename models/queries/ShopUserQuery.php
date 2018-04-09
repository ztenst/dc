<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\ShopUser]].
 *
 * @see \app\models\ShopUser
 */
class ShopUserQuery extends \app\base\ActiveQuery
{
    /**
     * 指定商家id
     * @return ShopUserQuery
     */
    public function shop($shopId)
    {
        return $this->andWhere(['shop_id'=>$shopId]);
    }
}
