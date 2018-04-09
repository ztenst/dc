<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\AreaDistrict]].
 *
 * @see \app\models\AreaDistrict
 */
class AreaDistrictQuery extends \app\base\ActiveQuery
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
}
