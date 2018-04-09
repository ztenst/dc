<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\base\Controller;
use app\models\ext\AdminAdmin;
use yii\data\ActiveDataProvider;
use yii\widgets\ActiveForm;
use yii\web\Response;

class AdminController extends Controller
{
    /**
     * 后台帐号编辑页面
     * @param  integer $id 编辑帐号的id，为0表示新建帐号
     * @return string
     */
    public function actionEdit($id=0)
    {
        $admin = $this->loadAdmin($id);
        if(Yii::$app->request->getIsPost() && $admin->load($_POST)) {
            if(Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($admin);
                Yii::$app->end();
            }
            if($admin->save()) {
                $this->setMessage('保存成功', 'success', ['list']);
            } else {
                $msg = $admin->hasErrors() ? current($admin->getFirstErrors()) : '保存失败';
                $this->setMessage($msg, 'error');
            }
        }
        return $this->render('edit', [
            'admin' => $admin
        ]);
    }

    /**
     * 帐号列表
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => AdminAdmin::find()->undeleted(),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 删除帐号
     */
    public function actionDelete()
    {
        $msg = '删除失败';
        $type = 'error';
        if(Yii::$app->request->isPost && $id = Yii::$app->request->get('id',0)) {
            if(($admin = AdminAdmin::findOne($id)) && $admin->delete()) {
                $msg = '删除成功';
                $type = 'success';
            }
        }
        $this->setMessage($msg, $type, ['list']);
    }

    /**
     * 加载获取帐号模型对象
     * @param  integer $id 帐号id，编辑时用
     * @throws Exception 找不到帐号时抛出异常
     * @return AdminAdmin 后台帐号对象
     */
    private function loadAdmin($id)
    {
        if($id>0) {
            if(($model=AdminAdmin::findOne($id))===null) {
                throw new Exception('找不到该帐号');
            }
        } else {
            $model = new AdminAdmin([
                'status' => AdminAdmin::STATUS_ACTIVE,
                'scenario' => AdminAdmin::SCENARIO_REGISTER,
            ]);
        }
        return $model->loadDefaultValues();
    }
}
