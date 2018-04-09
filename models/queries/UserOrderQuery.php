<?php

namespace app\models\queries;
use app\models\ext\UserOrder;
use app\traits\StatusQueryTrait;

/**
 * This is the ActiveQuery class for [[\app\models\UserOrder]].
 *
 * @see \app\models\UserOrder
 */
class UserOrderQuery extends \app\base\ActiveQuery
{
    use StatusQueryTrait;
    /**
     * 指定商家id
     */
    public function shop($shopId)
    {
        return $this->andWhere(['shop_id'=>$shopId]);
    }

   public function isComplete($boolean = true)
   {
        if($boolean == true){
            $this->andWhere(['status' => UserOrder::STATUS_PAID]);
        }else{
            $this->andWhere(['>=','status',UserOrder::STATUS_SUBMIT])
                 ->andWhere(['<=','status',UserOrder::STATUS_TO_BE_PAID]);
        }
        return $this;
   }

    public function user($user_id){
        $this->andWhere(['user_id'=>$user_id]);
        return $this;
    }
}
