<?php
namespace api\modules\canting\controllers;

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
