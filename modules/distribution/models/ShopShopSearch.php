<?php
namespace app\modules\distribution\models;

use app\models\ext\ShopShop;

class ShopShopSearch extends ShopShop
{
    public function rules()
    {
        return [
            [['name'],'string'],
            [['status'],'integer'],
        ];
    }

    public function search($getParams)
    {
        $query = self::find();
        if($this->load($getParams) && $this->validate()) {
            $query->andFilterWhere(['like','name',$this->name.'%', false]);
            $query->andFilterWhere(['status'=>$this->status]);
        }
        return $query;
    }
}
