<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\UserOrderUser]].
 *
 * @see \app\models\UserOrderUser
 */
class UserOrderUserQuery extends \app\base\ActiveQuery
{

    public function order($order_id)
    {
        $this->andWhere(['order_id'=>$order_id]);
        return $this;
    }

    public function user($user_id)
    {
        $this->andWhere(['user_id'=>$user_id]);
        return $this;
    }

}
