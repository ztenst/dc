<?php
namespace api\modules\shop\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use api\modules\shop\base\Controller;
use yii\data\ActiveDataProvider;
use app\models\ext\ShopDesk;
use app\models\ext\ShopActiveDesk;
use app\models\ext\ShopMergeDesk;
use \Exception;

class IndexController extends Controller
{
    /**
     * 餐桌相关业务逻辑层对象
     * @var DeskService
     */
    private $_deskService;

    /**
     * 餐桌相关业务逻辑层对象
     * @return DeskService
     */
    public function getDeskService()
    {
        if($this->_deskService===null) {
            return $this->_deskService = new \api\modules\shop\services\DeskService(['context'=>$this]);
        }
        return $this->_deskService;
    }
    /**
     * 商家后台首页餐桌信息接口
     * GET请求参数：
     * - status: 0全部，1空桌，2已开台，3等待确认，4预结
     * - page:分页参数
     */
    public function actionIndex()
    {
        $desks = [];
        $status = Yii::$app->request->get('status', 0);
        $query = ShopDesk::find()->select(['id','shop_id','number','status','merge_target_desk_id','active_desk_id'])->with(['activeInfo'=>function($query) {
            $query->with('order');
        }])->indexBy('id')->shop($this->currentShopId);
        switch($status) {
            case 1: $query->status(ShopDesk::STATUS_EMPTY);break;
            case 2: $query->nonEmpty();break;
            case 3: $query->status(ShopDesk::STATUS_WAIT_COMFIRM);break;
            case 4: $query->status(ShopDesk::STATUS_WILL_PAY);break;
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 15
            ]
        ]);

        foreach($dataProvider->getModels() as $desk) {
            //该餐桌状态，给前端使用图标时判断
            $fields = ArrayHelper::filter($desk, ['id','number','status']);
            $desks[] = array_merge($fields, [
                'mergeInfo' => [
                    'isMerge' => $desk->isMerge,
                    'mergeDeskNumber' => $desk->isMerge ? $desk->mergeTargetDesk->number : '',
                ],
                'statusStr' => $desk->statusText,
                'status' => $desk->status,
                'peopleNumber' => $desk->activePeopleNumber,
                'price' => $desk->activePrice,
                'openTime' => $desk->activeOpenTime ? date('H:i', $desk->activeOpenTime) : '-',
            ]);
        }

        //桌数相关
        $groupCountData = ShopDesk::find()
                        ->addSelect(['status','count(id) as groupCount'])
                        ->shop($this->currentShopId)
                        ->groupBy('status')
                        ->indexBy('status')->all();
        $groupCount = ArrayHelper::map($groupCountData, 'status', 'groupCount');
        $all = array_sum($groupCount);
        $kongzhuo = ArrayHelper::getValue($groupCount, ShopDesk::STATUS_EMPTY, 0);
        $daiqueren = ArrayHelper::getValue($groupCount, ShopDesk::STATUS_WAIT_COMFIRM, 0);
        $yujie = ArrayHelper::getValue($groupCount, ShopDesk::STATUS_WILL_PAY, 0);
        $yikaitai = $all - $kongzhuo;

        $data = [
            'desks' => $desks,//每页桌子列表
            'all' => $all,//总桌数
            'kongzhuo' => $kongzhuo,//空桌
            'yikaitai' => $yikaitai,//已开台
            'daiqueren' => $daiqueren,//待确认
            'yujie' => $yujie,//预结
            'totalPage' => $dataProvider->pagination->pageCount,//总页数
        ];

        return $data;
    }

    /**
     * 交换餐桌
     * POST请求参数
     * - desk1:餐桌1的餐桌号
     * - desk2:餐桌2的餐桌号
     */
    public function actionExchange()
    {
        if(Yii::$app->request->getIsPost()
        && ($desk1 = Yii::$app->request->post('desk1', 0))
        && ($desk2 = Yii::$app->request->post('desk2', 0))) {
            $this->deskService->exchangeDesk($desk1, $desk2);
            $this->wsService->pushRefreshIndex($this->currentShop);
            return '更换成功';
        } else {
            throw new Exception('请求方式或参数错误');
        }
    }

    /**
     * 合并餐桌
     * POST请求参数
     * - from_desk:餐桌1的餐桌号
     * - to_desk:餐桌2的餐桌号
     */
    public function actionMerge()
    {
        if(Yii::$app->request->getIsPost()
        && ($fromDesk = Yii::$app->request->post('from_desk', 0))
        && ($toDesk = Yii::$app->request->post('to_desk', 0))) {
            $this->deskService->mergeDesk($fromDesk, $toDesk);
            $this->wsService->pushRefreshIndex($this->currentShop);
            return '并桌成功';
        } else {
            throw new Exception('请求方式或参数错误');
        }
    }

    /**
     * 解除并桌
     * POST请求参数
     * - from_desk:来源桌的餐桌号
     */
    public function actionRemoveMerge()
    {
        if(Yii::$app->request->getIsPost()
        && ($fromDesk = Yii::$app->request->post('from_desk', 0))) {
            $this->deskService->removeMerge($fromDesk);
            $this->wsService->pushRefreshIndex($this->currentShop);
            return '操作成功';
        } else {
            throw new Exception('请求方式或参数错误');
        }
    }
}
