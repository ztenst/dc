<?php
namespace app\modules\distribution\controllers;

use Yii;
use yii\helpers\Json;
use app\modules\distribution\models\LoginForm;
use app\modules\distribution\base\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if(!Yii::$app->user->getIsGuest()) {
            $this->goHome();
        }
        $this->layout = false;

        $loginForm = new LoginForm();
        $error = '';
        $response = [
            'status' => false,
            'msg' => '',
            'data' => [

            ]
        ];
        if(Yii::$app->request->getIsAjax() && $loginForm->load($_POST)) {
            if($loginForm->login()) {
                $response['status'] = true;
                $response['msg'] = '登录成功';
                $this->goBack();
                // return $this->goBack();
            } else {
                $error = $loginForm->hasErrors() ? current(current($loginForm->getErrors())) : '登录验证失败';
                $response['msg'] = $error;
            }
            return Json::encode($response);
            Yii::$app->end();
        }
        return $this->render('login', [
            'loginForm' => $loginForm,
            'error' => $error,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout(false);
        $this->goHome();
    }

    public function actionError()
    {
        $exception = $this->module->errorHandler->exception;
        return $this->render('error', [
            'exception' => $exception,
        ]);
    }
}
