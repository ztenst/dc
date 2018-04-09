<?php
namespace api\modules\shop\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use \Exception;

class SettingController extends \api\modules\shop\base\Controller
{
    /**
     * 商家服务层逻辑类
     */
    private $_shopService;

    /**
     * 商家服务层逻辑类
     */
    public function getShopService()
    {
        if($this->_shopService===null) {
            $this->_shopService = new \api\modules\shop\services\ShopService(['context'=>$this]);
        }
        return $this->_shopService;
    }

    /**
     * 获取设置页面所有信息接口
     */
    public function actionAllInfo()
    {
        $shopInfo = $searchInfo = $helpInfo = [];
        $shop = $this->currentShop;
        $shopInfo = [
            'name' => $shop->name,
            'address' => $shop->shopInfo->address,
            'phone' => $shop->shopInfo->phone,
            'description' => $shop->shopInfo->description,
        ];
        return [
            'shopInfo' => $shopInfo,
            'settings' => $this->actionSettingEdit(),
            'helpeInfo' => [],
        ];
    }

    /**
     * 修改商家信息
     * POST参数：
     * - name: 商家店名
     * - address: 地址
     * - phone: 电话
     */
    public function actionShopEdit()
    {
        if(!($post = Yii::$app->request->post())) {
            throw new Exception('请求方式或参数错误');
        }
        $this->shopService->saveShop($post);
        return '保存成功';
    }

    /**
     * 保存商家配置接口
     * POST参数：配置标识和值的键值对，如['searchKeywords'=>'牛排，鸡排']
     */
    public function actionSettingEdit()
    {
        $settings = $this->currentShop->shopSettings;
        if(Yii::$app->request->isPost) {
            if($settings->load(Yii::$app->request->post(),'') && !$settings->save()) {
                throw new Exception($this->shopService->getModelError($settings, '保存失败'));
            }
            return '保存成功';
        } else {
            foreach($settings as $key=>$setting) {
                $settings[$key] = ArrayHelper::getValue($setting,'value');
            }
            return $settings;
        }
    }
}
