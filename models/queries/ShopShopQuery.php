<?php

namespace app\models\queries;
use app\traits\StatusQueryTrait;

/**
 * This is the ActiveQuery class for [[\app\models\ShopShop]].
 *
 * @see \app\models\ShopShop
 */
class ShopShopQuery extends \app\base\ActiveQuery
{
    use StatusQueryTrait;
    /**
     * 未删除的
     * @return ShopShopQuery
     */
    public function undeleted()
    {
        $this->andWhere(['is_deleted'=>0]);
        return $this;
    }

    /**
     * 指定城市的
     * @return ShopShopQuery
     */
    public function city($cityId)
    {
        $this->andWhere(['city_id'=>$cityId]);
        return $this;
    }
}
