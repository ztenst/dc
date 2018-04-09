<?php
namespace api\modules\shop\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use api\modules\shop\base\Controller;
use app\models\ext\ShopPrintLog;

/**
 * 打印清单相关
 */
class PrinterController extends Controller
{
    /**
     * 打印记录清单列表
     * GET请求参数：
     * - page: 翻页页码
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ShopPrintLog::find()->andWhere('shop_id=:shopId', [':shopId'=>$this->currentShopId])->orderBy('id desc'),
            'pagination' => [
                'pageSize' => 15
            ]
        ]);

        $logs = [];

        foreach($dataProvider->getModels() as $log) {
            $logs[] = [
                'id' => $log->id,
                'time' => date('Y-m-d H:i:s', $log->created),
                'taskName' => $log->name,
                'status' => ($log->fail>0||$log->success==0) ? '打印失败' : '打印成功',
            ];
        }

        return [
            'logs' => $logs,
            'totalPage' => $dataProvider->pagination->pageCount,//总页数
        ];
    }

    /**
     * 获取重新打印内容接口
     * GET参数:
     * -id: 打印记录id
     */
    public function actionReprint($id=0)
    {
        $log = ShopPrintLog::findOne($id);
        if(!$log) {
            throw new Exception('打印记录不存在');
        }
        $kitchenPrinter = -1;
        if($log->print_type==ShopPrintLog::PRINT_TYPE_KITCHEN) {
            $kitchenPrinter = $this->currentShop->shopSettings->kitchenPrinter;
        }
        return [
            'printId' => $log->id,
            'kitchenPrinter' => $kitchenPrinter,
            'htmlContent' => $log->content,
        ];
    }
}
