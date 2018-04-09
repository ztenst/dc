<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\base\Controller;
use app\models\ext\AreaProvince;
use app\models\ext\AreaCity;
use app\models\ext\AreaDistrict;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * 区域相关数据页面
 * @author weibaqiu
 * @version 2017-05-11
 */
class AreaController extends Controller
{
    /**
     * 编辑城市操作标识
     */
    const TYPE_CITY = 'city';
    /**
     * 编辑省份操作标识
     */
    const TYPE_PROVINCE = 'province';
    /**
     * 编辑地区操作标识
     */
    const TYPE_DISTRICT = 'district';

    /**
     * 编辑、创建区域
     * @param integer $id 需要编辑的区域id
     * @param string $type 常量TYPE_CITY、TYPE_PROVINCE、TYPE_DISTRICT中的一个值，
     * 代表要操作编辑的数据分类
     */
    public function actionEdit($id=0, $type)
    {
        $model = $this->loadArea($id, $type);

        //处理提交的数据
        if(Yii::$app->request->getIsPost() && $model->load($_POST)) {
            if($model->save()) {
                switch($type) {
                    case self::TYPE_PROVINCE: $route = ['list'];break;
                    case self::TYPE_CITY: $route = ['list', 'provinceId'=>$model->province_id];break;
                    case self::TYPE_DISTRICT: $route = ['list', 'cityId'=>$model->city_id];break;
                }
                $route = isset($_POST['next']) ? ['edit','type'=>$type] : $route;
                $this->setMessage('保存成功', 'success', $route);
            } else {
                $msg = $model->hasErrors() ? current($model->getFirstErrors()) : '保存失败';
                $this->setMessage($msg, 'error');
            }
        }

        //处理页面title相关
        switch($type) {
            case self::TYPE_PROVINCE: $typeName = '省份';break;
            case self::TYPE_CITY: $typeName = '城市';break;
            case self::TYPE_DISTRICT: $typeName = '地区';break;
        }
        $operateName = $model->getIsNewRecord() ? '创建' : '编辑';

        return $this->render('edit', [
            'type' => $type,
            'typeName' => $typeName,
            'operateName' => $operateName,
            'model' => $model,
        ]);
    }

    /**
     * 区域列表
     * @param string $type 常量TYPE_CITY、TYPE_PROVINCE、TYPE_DISTRICT中的一个值，
     * 代表要操作编辑的数据分类
     */
    public function actionList($type=self::TYPE_PROVINCE, $provinceId=0, $cityId=0)
    {
        if($cityId>0) {
            $type = self::TYPE_DISTRICT;
        } elseif($provinceId>0) {
            $type = self::TYPE_CITY;
        }

        $gridViewCaption = '';

        switch($type) {
            case self::TYPE_PROVINCE:
                $query = AreaProvince::find();
                break;
            case self::TYPE_CITY:
                if(!($province = AreaProvince::findOne($provinceId))) {
                    throw new Exception('参数数据不存在');
                }
                $query = AreaCity::find()->andWhere('province_id=:provinceId', [':provinceId'=>$provinceId]);
                $gridViewCaption = Html::a(Html::tag('i','',['class'=>'fa fa-mail-reply']).$province->name, ['list']);
                break;
            case self::TYPE_DISTRICT:
                if(!($city = AreaCity::findOne($cityId))) {
                    throw new Exception('参数数据不存在');
                }
                $query = AreaDistrict::find()->andWhere('city_id=:cityId', [':cityId'=>$cityId]);
                $gridViewCaption = Html::a(Html::tag('i','',['class'=>'fa fa-mail-reply']).$city->name, ['list', 'provinceId'=>$city->province_id]);
                break;
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 99,
            ],
        ]);
        return $this->render('list', [
            'type' => $type,
            'gridViewCaption' => $gridViewCaption,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDelete($id=0, $type)
    {
        $msg = '删除失败';
        $msgType = 'error';
        if(Yii::$app->request->isPost && $id>0) {
            switch($type) {
                case self::TYPE_PROVINCE: $modelName = '\app\models\ext\AreaProvince';break;
                case self::TYPE_CITY: $modelName = '\app\models\ext\AreaCity';break;
                case self::TYPE_DISTRICT: $modelName = '\app\models\ext\AreaDistrict';break;
            }
            if(($model = $modelName::findOne($id)) && $model->delete()) {
                $msg = '删除成功';
                $msgType = 'success';
            }
        }
        $this->setMessage($msg, $msgType, true);
    }

    /**
     * 获取加载对应区域的AR模型类
     * @param integer $id 需要编辑的区域id
     * @param string $type 常量TYPE_CITY、TYPE_PROVINCE、TYPE_DISTRICT中的一个值，
     * 代表要操作编辑的数据分类，以实例化不同的AR模型
     */
    private function loadArea($id, $type)
    {
        switch($type) {
            case self::TYPE_PROVINCE:
                $modelName = 'app\models\ext\AreaProvince';
                break;
            case self::TYPE_CITY:
                $modelName = 'app\models\ext\AreaCity';
                break;
            case self::TYPE_DISTRICT:
                $modelName = 'app\models\ext\AreaDistrict';
                break;
            default:
                throw new Exception('参数错误');
        }
        if($id>0) {
            if(($model=$modelName::findOne($id))===null) {
                throw new Exception('找不到该数据');
            }
        } else {
            $model = new $modelName();
        }
        return $model->loadDefaultValues();
    }
}
