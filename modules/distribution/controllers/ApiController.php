<?php
namespace app\modules\distribution\controllers;

use Yii;
use app\modules\distribution\base\Controller;

class ApiController extends Controller
{
    /**
     * 获取上传凭证
     */
    public function actionGetUptoken()
    {
        $token = Yii::$app->storage->getUploadToken();
        $this->asJson(['uptoken'=>$token]);
    }
}
