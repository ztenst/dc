<?php
/**
 * User: jt
 * Date: 2018/4/16
 * Time: 下午7:31
 */
namespace api\modules\canting\controllers;

use api\base\Controller;
use api\modules\canting\filters\ShopFilter;
use api\modules\canting\models\ShopMenuCate;

class HomeController extends Controller
{
    public $shop;

    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'shop' => [
                'class' => ShopFilter::className()
            ]
        ]);
    }
    
    public function actionIndex()
    {   
        $shop_menu_cate = ShopMenuCate::find()
            ->with('menus')
            ->shop($this->shop['id'])
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
