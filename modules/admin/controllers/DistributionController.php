<?php
namespace app\modules\admin\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\data\ActiveDataProvider;
use app\modules\admin\base\Controller;
use app\models\ext\DistributionAdmin;
use app\models\ext\AreaCity;
use app\models\ext\AreaProvince;

class DistributionController extends Controller
{
    /**
     * 分销商帐号编辑\创建
     * @param integer $id 分销商帐号id
     */
    public function actionAdminEdit($id=0)
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

        $cities = AreaCity::find()->all();
        $cities = ArrayHelper::map($cities, 'id', 'name', 'province.name');

        return $this->render('adminEdit', [
            'admin' => $admin,
            'cities' => $cities
        ]);
    }

    public function actionAdminList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => DistributionAdmin::find()->undeleted(),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        return $this->render('adminList', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 删除帐号
     */
    public function actionDelete($id)
    {
        $msg = '删除失败';
        $type = 'error';
        if(Yii::$app->request->isPost && $id) {
            if(($admin = DistributionAdmin::findOne($id)) && $admin->delete()) {
                $msg = '删除成功';
                $type = 'success';
            }
        }
        $this->setMessage($msg, $type, ['admin-list']);
    }

    /**
     * 加载获取帐号模型对象
     * @param  integer $id 帐号id，编辑时用
     * @throws Exception 找不到帐号时抛出异常
     * @return DistributionAdmin 后台帐号对象
     */
    private function loadAdmin($id)
    {
        if($id>0) {
            if(($model=DistributionAdmin::findOne($id))===null) {
                throw new Exception('找不到该帐号');
            }
        } else {
            $model = new DistributionAdmin([
                'status' => DistributionAdmin::STATUS_ACTIVE,
                'scenario' => DistributionAdmin::SCENARIO_REGISTER,
            ]);
        }
        return $model->loadDefaultValues();
    }
}
