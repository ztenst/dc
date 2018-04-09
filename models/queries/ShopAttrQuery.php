<?php

namespace app\models\queries;
use app\traits\StatusQueryTrait;

/**
 * This is the ActiveQuery class for [[ShopAttr]].
 *
 * @see ShopAttr
 */
class ShopAttrQuery extends \app\base\ActiveQuery
{
    use StatusQueryTrait;

    public function shop($shop_id)
    {
        $this->andWhere(['shop_id'=>$shop_id]);
        return $this;
    }
}
