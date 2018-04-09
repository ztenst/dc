<?php

namespace app\modules\distribution\controllers;

use Yii;
use app\modules\distribution\base\Controller;
use app\models\ext\DistributionAdmin;
use yii\web\BadRequestHttpException;

/**
 * 管理员设置
 */
class AdminController extends Controller
{
    public function actionIndex()
    {
        $model = DistributionAdmin::find()->where(['id'=>Yii::$app->user->id])->undeleted()->active()->one();
        if (!$model) {
            throw new BadRequestHttpException;
        }
        if($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->setMessage('保存成功', 'success', true);
        }
        return $this->render('index', [
            'model'=>$model
        ]);
    }
}
