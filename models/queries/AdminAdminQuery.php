<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\AdminAdmin]].
 *
 * @see \app\models\AdminAdmin
 */
class AdminAdminQuery extends \app\base\ActiveQuery
{
    /**
     * 未删除的
     * @return AdminAdminQuery
     */
    public function undeleted()
    {
        $this->andWhere(['is_deleted'=>0]);
        return $this;
    }

    /**
     * 根据状态筛选
     * @return AdminAdminQuery
     */
    public function status($status=AdminAdmin::STATUS_ACTIVE)
    {
        $this->andWhere(['status'=>$status]);
        return $this;
    }
}
