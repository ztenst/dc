<?php
namespace api\modules\canting\controllers;

use api\modules\canting\filters\ShopFilter;
use app\models\ext\ShopShop;
use Yii;
use api\modules\canting\models\ShopMenu;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class MenuController extends Controller
{
    public $shop;

    public function verbs()
    {
        return [
            '*' => ['get']
        ];
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'shop' => [
                'class' => ShopFilter::className(),
            ]
        ]);
    }
    //关键字
    public function actionKeyword()
    {
        //数组再重新转成对象= =！
        $shop = ShopShop::instantiate($this->shop);
        ShopShop::populateRecord($shop, $this->shop);

        $keywords = $shop->shopSettings->searchKeywords;
        if($keywords){
            $keywords = str_replace('，', ',', $keywords);
            $keywords = explode(',', $keywords);
            return array_map(function($kw){
                return trim($kw);
            }, $keywords);
        }
    }

    //菜单列表
    public function actionList()
    {
        $keyword = Yii::$app->request->get('keyword','');

        $shop_menu_query = ShopMenu::find()
            ->shop($this->shop['id'])
            ->status()
            ->likeName($keyword);
        $dataProvider = new ActiveDataProvider([
                'query' => $shop_menu_query,
                'pagination' => [
                    'pageSize' => 10
                ],
                'sort' => [
                    'defaultOrder' => [
                        'created' => SORT_DESC
                    ]
                ],
            ]);
         return $dataProvider;
    }
    //菜单详情
    public function actionItem()
    {
        $id = Yii::$app->request->get('id');

        $menu = ShopMenu::find()->status()
                                ->whereId(['id' => $id ])
                                ->one();
        if(is_null($menu)) {
            throw new BadRequestHttpException('菜单不存在');
        }
        return $menu;
    }

}
