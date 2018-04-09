<?php

namespace app\models\queries;
use app\traits\StatusQueryTrait;

/**
 * This is the ActiveQuery class for [[\app\models\SmsCode]].
 *
 * @see \app\models\SmsCode
 */
class SmsCodeQuery extends \app\base\ActiveQuery
{
    use StatusQueryTrait;

    public function phone($phone)
    {
        $this->andWhere(['phone'=>$phone]);
        return $this;
    }

    public function code($code)
    {
        $this->andWhere(['code' => $code]);
        return $this;
    }
    
}
