<?php
namespace api\modules\shop\models;

use app\models\ext\ShopMenu;

class ShopMenuSearch extends ShopMenu
{
    public function rules()
    {
        return [
            [['name'],'string'],
            [['cate_id','status'],'integer']
        ];
    }

    /**
     * @return ShopMenuQuery
     */
    public function search($getParams)
    {
        $query = ShopMenu::find();
        if($this->load($getParams,'') && $this->validate()) {
            $query->andFilterWhere(['like','name',$this->name.'%', false]);
            $query->andFilterWhere(['status'=>$this->status]);
            $query->andFilterWhere(['cate_id'=>$this->cate_id]);
        }
        return $query;
    }
}
