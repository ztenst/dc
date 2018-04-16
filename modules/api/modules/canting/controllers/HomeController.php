<?php
/**
 * User: jt
 * Date: 2018/4/16
 * Time: 下午7:31
 */
namespace api\modules\canting\controllers;

use api\base\Controller;

class HomeController extends Controller
{
    public function actionIndex()
    {   
        $shopId = 1;
        $shop_menu_cate = ShopMenuCate::find()
            ->with('menus')
            ->shop($shopId)
            ->andWhere(['show_type'=>ShopMenuCate::SHOW_ALL])
            ->all();
        $menu_cates = [];
        foreach ($shop_menu_cate as $i => $model) {
            $menu_cates[$i] =  $model->toArray();
        }
        $recommends = [];
        foreach ($menu_cates as $menu_cate){
            foreach ($menu_cate['menus'] as $menu){
                if($menu['is_recommend']){
                    array_push($recommends, $menu);
                }
            }
        }
        if($recommends) {
            array_unshift($menu_cates, [
                'name' => '推荐',
                'id' => 0,
                'menus' => $recommends
            ]);
        }
        return [
            'shop' => $this->shop,
            'menu_cates' => $menu_cates
        ];
    }
}
