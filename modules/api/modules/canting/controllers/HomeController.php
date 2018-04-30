<?php
/**
 * User: jt
 * Date: 2018/4/16
 * Time: 下午7:31
 */
namespace api\modules\canting\controllers;

use Yii;
use api\base\Controller;
use api\modules\canting\filters\ShopFilter;
use api\modules\canting\models\ShopMenuCate;
use api\modules\canting\components\WxPay;

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

    public function actionNotify()
    {
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $res = $this->xmlToArray($xml);
        $encode = json_encode($res);
        Yii::error($xml);
        Yii::error($encode);
    }

    private function xmlToArray($xml) {
        if(!$xml){
            throw new WxPayException("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
    
    public function actionError()
    {
        Yii::error('测试');
    }
}
