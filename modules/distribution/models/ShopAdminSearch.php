<?php
namespace app\modules\distribution\models;

use app\models\ext\ShopAdmin;

class ShopAdminSearch extends ShopAdmin
{
    public function rules()
    {
        return [
            [['account', 'phone'],'string'],
            [['status', 'shop_id'],'integer']
        ];
    }

    public function search($getParams)
    {
        $query = self::find();
        if($this->load($getParams) && $this->validate()) {
            $query->andFilterWhere(['like','account',$this->account.'%', false]);
            $query->andFilterWhere(['like','phone',$this->phone.'%', false]);
            $query->andFilterWhere(['status'=>$this->status]);
            $query->andFilterWhere(['shop_id'=>$this->shop_id]);
        }
        return $query;
    }
}
