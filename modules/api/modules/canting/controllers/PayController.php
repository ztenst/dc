<?php
namespace api\modules\canting\controllers;

use api\modules\canting\components\ShopCart;
use api\modules\canting\filters\ShopFilter;
use api\modules\canting\models\ShopMenu;
use app\models\ext\ShopActiveDesk;
use app\models\ext\ShopDesk;
use app\models\ext\ShopShop;
use app\models\ext\UserOrder;
use app\models\ext\UserOrderMenu;
use app\models\ext\UserOrderUser;
use app\services\WsService;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use api\modules\canting\components\WxPay;

class PayController extends Controller
{
	public function verbs()
    {
        return [
            'addPay' => ['post'],
            '*' => ['get']
        ];
    }

	public function actionAddPay()
	{
		$obj = new WxPay;
        var_dump($obj);exit;
        return [];
	}

    public function actionRecallPay()
    {
        return [];
    }
}