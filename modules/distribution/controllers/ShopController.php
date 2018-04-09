<?php
namespace app\modules\distribution\controllers;

use Yii;
use app\models\ext\AreaCity;
use app\models\ext\ShopShop;
use app\models\ext\ShopShopInfo;
use app\modules\distribution\base\Controller;
use app\modules\distribution\models\ShopShopSearch;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;

class ShopController extends Controller
{
    public function actionList()
    {
        $model = new ShopShopSearch;
        $query = $model->search(Yii::$app->request->get())->andWhere([
            'city_id'=>Yii::$app->user->identity->city_id
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'model' => $model
        ]);
    }

    /**
     * 商家编辑\添加页
     * @param integer $id 要创建\编辑的商家id
     */
    public function actionEdit($id=0)
    {
        $shop = $this->loadShop($id);
        $shopInfo = $this->loadShopInfo($shop);

        if(Yii::$app->request->getIsPost() && $shop->load($_POST)) {
            //关联表保存开启事务
            $transaction = Yii::$app->getDb()->beginTransaction();
            //无论数据库层面还是代码逻辑层面出错统一throw异常并接收处理
            try {
                if(!$shop->save()) {
                    $msg = $shop->hasErrors() ? current($shop->getFirstErrors()) : '基本信息保存失败';
                    throw new \Exception($msg);
                }
                $shopInfo->load(Yii::$app->request->post());
                $shopInfo->shop_id = $shop->id;
                if(!$shopInfo->save()) {
                    $msg = $shopInfo->hasErrors() ? current($shopInfo->getFirstErrors()) : '扩展信息保存失败';
                    throw new \Exception($msg);
                }
                $transaction->commit();
                $this->setMessage('保存成功', 'success', ['list']);
            } catch (\Exception $e) {
                $transaction->rollback();
                $this->setMessage($e->getMessage(), 'error');
            }

        }
        return $this->render('edit', [
            'shop' => $shop,
            'shopInfo' => $shopInfo,
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
            if(($admin = ShopShop::findOne($id)) && $admin->delete()) {
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
     * @return ShopShop 后台帐号对象
     */
    private function loadShop($id)
    {
        if($id>0) {
            if(($model=ShopShop::find()->where(['id'=>$id])->with('shopInfo')->one())===null) {
                throw new Exception('找不到该商家');
            }
        } else {
            $identity = Yii::$app->user->identity;
            $model = new ShopShop([
                'city_id' => $identity->city_id,
                'status' => ShopShop::STATUS_ACTIVE,
            ]);
        }
        return $model->loadDefaultValues();
    }

    /**
     * 加载获取商家信息模型对象
     * @param ShopShop $shop 已经获得到的商家基本模型对象
     * @param boolean $createIfNull 如果加载不到则new一个
     *
     * Shop与ShopInfo两个模型间的link()操作放在外层逻辑中走，不放在这函数里
     */
    private function loadShopInfo(ShopShop $shop, $createIfNull=false)
    {
        if($shop->getIsNewRecord() || !($shopInfo = $shop->shopInfo)&&$createIfNull) {
            $identity = Yii::$app->user->identity;
            return new ShopShopInfo([
                'distribution_admin_id' => $identity->id,
                'description' => '未填写'
            ]);
        }
        return $shopInfo;
    }
}
