<?php

namespace app\models\queries;
use app\traits\StatusQueryTrait;

/**
 * This is the ActiveQuery class for [[ShopMenu]].
 *
 * @see ShopMenu
 */
class ShopMenuQuery extends \app\base\ActiveQuery
{
    use StatusQueryTrait;

    public function shop($shop_id)
    {
        $this->andWhere(['shop_id'=>$shop_id]);
        return $this;
    }
    public function likeName($keyword)
    {
        $this->andWhere(['like', 'name', $keyword]);
        return $this;
    }
}
