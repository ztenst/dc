<?php

namespace app\models\queries;

use app\models\ext\UserOrderMenu;

/**
 * This is the ActiveQuery class for [[\app\models\UserOrderMenu]].
 *
 * @see \app\models\UserOrderMenu
 */
class UserOrderMenuQuery extends \app\base\ActiveQuery
{
    /**
     * 指定订单id
     * @return $this
     */
    public function order($order_id)
    {
        $this->andWhere(['order_id' => $order_id]);
        return $this;
    }

    /**
     * 指定是否退菜字段
     * @return $this
     */
    public function cancel($cancel=UserOrderMenu::CANCEL_NO)
    {
        return $this->andWhere(['is_cancel'=>$cancel]);
    }

    public function user($user_id)
    {
        $this->andWhere(['user_id'=>$user_id]);
        return $this;
    }

}
