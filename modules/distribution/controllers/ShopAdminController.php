<?php
namespace app\modules\distribution\controllers;

use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use app\models\ext\ShopAdmin;
use app\models\ext\ShopShop;
use app\modules\distribution\models\ShopAdminSearch;
use app\modules\distribution\base\Controller;
use yii\data\ActiveDataProvider;


class ShopAdminController extends Controller
{
    public function actionList()
    {
        $identity = Yii::$app->user->identity;
        $model = new ShopAdminSearch;
        $query = $model->search(Yii::$app->request->get())->andWhere(['distribution_admin_id'=>$identity->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $shops = ShopShop::find()->select(['id','name'])->city($identity->city_id)->all();

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'shops' => $shops,
        ]);
    }

    /**
     * 商家帐号编辑页
     * @param integer $id 需要编辑的商家帐号id
     */
    public function actionEdit($id=0)
    {
        $admin = $this->loadShopAdmin($id);
        if(Yii::$app->request->getIsPost() && $admin->load($_POST)) {
            if(Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($admin);
                Yii::$app->end();
            }
            if($admin->save()) {
                $this->setMessage('保存成功', 'success', ['list', 'shopId'=>$admin->shop_id]);
            } else {
                $msg = $admin->hasErrors() ? current($admin->getFirstErrors()) : '保存失败';
                $this->setMessage($msg, 'error');
            }
        }

        $shops = ShopShop::find()->city($this->getIdentity()->city_id)->all();

        return $this->render('edit', [
            'admin' => $admin,
            'shops' => $shops,
        ]);
    }

    /**
     * 加载获取帐号模型对象
     * @param  integer $id 帐号id，编辑时用
     * @throws Exception 找不到帐号时抛出异常
     * @return ShopAdmin 商家帐号对象
     */
    private function loadShopAdmin($id)
    {
        if($id>0) {
            if(($model=ShopAdmin::findOne($id))===null) {
                throw new Exception('找不到该帐号');
            }
        } else {
            $model = new ShopAdmin([
                'status' => ShopAdmin::STATUS_ACTIVE,
                'scenario' => ShopAdmin::SCENARIO_DISTRIBUTION_EDIT,
                'distribution_admin_id' => $this->getIdentity()->id
            ]);
        }
        return $model->loadDefaultValues();
    }

    /**
     * 获取当前登录用户的信息
     * @return IdentityInterface
     */
    private function getIdentity()
    {
        return Yii::$app->user->identity;
    }
}
