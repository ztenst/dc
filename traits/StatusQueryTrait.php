<?php
namespace app\traits;

trait StatusQueryTrait
{
    public function status($status  = 1)
    {
        $this->andWhere(['status' => $status]);
        return $this;
    }
}
