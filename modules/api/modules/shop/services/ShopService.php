<?php
namespace api\modules\shop\services;

use Yii;
use app\models\ext\ShopShop;
use yii\helpers\ArrayHelper;
use \Exception;

class ShopService extends \api\modules\shop\services\Service
{
    /**
     * 保存商家信息
     * @param array $post 参数数组，包含
     * - name: 店名
     * - address: 地址
     * - phone: 电话
     * - description: 简介
     */
    public function saveShop($post)
    {
        $shop = ShopShop::find()->innerJoinWith('shopInfo')->andWhere(['id'=>$this->context->currentShopId])->one();
        if(!$shop) {
            throw new Exception('商家信息未找到，请重试');
        }
        $shop->scenario = $shop->shopInfo->scenario = ShopShop::SCENARIO_SHOP_EDIT;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if($shop->load($post, '') && !$shop->save()) {
                throw new Exception($this->getModelError($shop), '修改失败');
            }
            if($shop->shopInfo->load($post, '') && !$shop->shopInfo->save()) {
                throw new Eception($this->getModelError($shop->shopInfo),'保存失败');
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
            throw new Exception($e->getMessage());
        }
    }
}
