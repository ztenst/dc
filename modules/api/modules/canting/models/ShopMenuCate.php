<?php

namespace api\modules\canting\models;

use Yii;

class ShopMenuCate extends \app\models\ext\ShopMenuCate
{
    public function fields()
    {
        return [
            'id',
            'name',
            'menus' =>function($model){
                $menus = array();
                foreach ($model->menus as $key => $menu){
                   $menus[$key] = [
                       'id'=>$menu->id,
                       'name'=>$menu->name,
                       'price'=>$menu->price,
                       'is_recommend'=>$menu->isRecommend,
                       'stock'=>$menu->stock,
                       'image'=> $menu->getImage(),
                       'sale'=>$menu->sale,
                       'unit'=>$menu->unit->name,
                       'attrs'=>$menu->getConfigField('attrs')
                   ];
                }
                return $menus;
            }
        ];
    }

    /**
     * 获取分类下的菜品
     */
    public function getMenus()
    {
       return parent::getMenus()->status();
    }
}
