<?php

namespace app\models\queries;

use app\models\ext\ShopActiveDesk;

/**
 * This is the ActiveQuery class for [[\app\models\ShopActiveDesk]].
 *
 * @see \app\models\ShopActiveDesk
 */
class ShopActiveDeskQuery extends \app\base\ActiveQuery
{

    /**
     * 指定餐桌id
     */
    public function desk($deskId)
    {
        return $this->andWhere(['desk_id'=>$deskId]);
    }

    /**
     * 指定餐桌状态
     */
    public function status($status=ShopActiveDesk::STATUS_EMPTY)
    {
        return $this->andWhere(['status'=>$status]);
    }

    /**
     * 非空餐桌
     */
    public function nonEmpty()
    {
        return $this->andWhere(['!=', 'status', ShopActiveDesk::STATUS_EMPTY]);
    }

    /**
     * 指定商家
     */
    public function shop($shopId)
    {
        return $this->andWhere(['shop_id'=>$shopId]);
    }

    public function user($user_id){
        return $this->andWhere(['user_id'=>$user_id]);
    }
}
