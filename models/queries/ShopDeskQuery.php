<?php

namespace app\models\queries;

use app\models\ext\ShopDesk;
use app\traits\StatusQueryTrait;

/**
 * This is the ActiveQuery class for [[\app\models\ShopDesk]].
 *
 * @see \app\models\ShopDesk
 */
class ShopDeskQuery extends \app\base\ActiveQuery
{
    use StatusQueryTrait;

    /**
     * 指定所属商家id
     * @return ShopDeskQuery
     */
    public function shop($shop_id)
    {
        return $this->andWhere(['shop_id'=>$shop_id]);
    }

    /**
     * 非空餐桌
     */
    public function nonEmpty()
    {
        return $this->andWhere(['!=', 'status', ShopDesk::STATUS_EMPTY]);
    }

    public function merge($merge_desk_id)
    {
        $this->andWhere(['merge_target_desk_id'=>$merge_desk_id]);
        return $this;
    }
}
