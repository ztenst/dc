<?php
namespace app\base;

class ActiveQuery extends \yii\db\ActiveQuery
{
    
    public function whereId($id){
        $this->andWhere(['id'=>$id]);
        return $this;
    }

}
