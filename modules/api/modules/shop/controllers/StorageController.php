<?php
namespace api\modules\shop\controllers;

use Yii;
use yii\helpers\Json;
use api\modules\shop\base\Controller;

class StorageController extends Controller
{
    /**
     * 获取上传凭证
     */
    public function actionGetUptoken()
    {
        $token = Yii::$app->storage->getUploadToken();
        echo Json::encode(['uptoken'=>$token]);die;
    }
}
