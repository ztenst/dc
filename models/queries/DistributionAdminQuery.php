<?php

namespace app\models\queries;

use app\traits\StatusQueryTrait;


/**
 * This is the ActiveQuery class for [[\app\models\DistributionAdmin]].
 *
 * @see \app\models\DistributionAdmin
 */
class DistributionAdminQuery extends \app\base\ActiveQuery
{
    use StatusQueryTrait;
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
