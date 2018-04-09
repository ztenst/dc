<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\AreaCity]].
 *
 * @see \app\models\AreaCity
 */
class AreaCityQuery extends \app\base\ActiveQuery
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
    public function status($status=null)
    {
        if($status===null) {
            $modelClass = $this->modelClass;
            $status = $modelClass::STATUS_ACTIVE;
        }
        $this->andWhere(['status'=>$status]);
        return $this;
    }
}
