<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\ShopAdmin]].
 *
 * @see \app\models\ShopAdmin
 */
class ShopAdminQuery extends \app\base\ActiveQuery
{
    /**
     * 指定商家id
     * @return ShopAdminQuery
     */
    public function shop($shopId)
    {
        return $this->andWhere(['shop_id'=>$shopId]);
    }
    /**
     * 未删除的
     * @return AdminAdminQuery
     */
    public function undeleted()
    {
        $this->andWhere(['is_deleted'=>0]);
        return $this;
    }
}
