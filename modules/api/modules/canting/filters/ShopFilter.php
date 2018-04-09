<?php

namespace api\modules\canting\filters;

use api\modules\canting\models\ShopShop;
use Yii;
use yii\base\ActionFilter;
use yii\web\BadRequestHttpException;

class ShopFilter extends ActionFilter
{
    public $shop_id = 'shop_id';

    public function beforeAction($action)
    {
        $request = Yii::$app->getRequest();
        if($shop_id = $request->get($this->shop_id)){
            $arCache = $this->owner->getArCache();
            $shop = $arCache->getArCache($shop_id, new ShopShop());
            if(!$shop){
                 $shop = $arCache->setArCache(ShopShop::find()->status()->whereId($shop_id)->one());
            }
            if(is_null($shop)){
                throw new BadRequestHttpException('商家不存在');
            }
            $this->owner->shop = $shop;
            return true;
        }
        throw new BadRequestHttpException('参数错误');
    }

}
