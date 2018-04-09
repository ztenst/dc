<?php
namespace api\modules\shop\controllers;

use Yii;
use app\helpers\Timestamp;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use app\models\ext\UserOrder;
use app\models\ext\UserOrderMenu;
use \Exception;

class BillController extends \api\modules\shop\base\Controller
{
    /**
     * 账单列表接口
     * 显示所有的订单（包括未结账等之类的），显示的价格是订单最终折后确认价，因此点开详情
     * 后的接口需要只列出已确认和未退菜的菜品
     */
    public function actionList($d='', $begin=null, $end=null, $download=0)
    {
        $list = [];
        $query = UserOrder::find()->where(['shop_id'=>$this->currentShopId])->andWhere(['status'=>UserOrder::STATUS_PAID])->orderBy('id desc');

        if($begin) {
            $begin = strtotime($begin);
        }
        if($end) {
            $end = strtotime($end)+86400;
        }

        switch($d) {
            case 'zuori':
                $begin = Timestamp::getDayBeginTime(strtotime('-1 day'));
                $end = Timestamp::getDayBeginTime();
                break;
            case 'jinri':
                $begin = Timestamp::getDayBeginTime();
                break;
            case 'benzhou':
                $begin = Timestamp::getWeekBeginTime();
                break;
            case 'shangzhou':
                $begin = Timestamp::getLastWeekBeginTime();
                $end = Timestamp::getWeekBeginTime();
                break;
            case 'benyue':
                $begin = Timestamp::getMonthBeginTime();
                break;
            case 'shangyue':
                $begin = Timestamp::getLastMonthBeginTime();
                $end = Timestamp::getMonthBeginTime();
                break;
        }

        if($begin) {
            $query->andWhere('created>=:begin')->addParams([':begin'=>$begin]);
        }
        if($end) {
            $query->andWhere('created<:end')->addParams([':end'=>$end]);
        }
        $priceQuery = clone $query;
        $totalPrice = $priceQuery->select('sum(total_price)')->scalar();

        $query = $query->with('activeDesk');

        if($download) {
            $excel = new \PHPExcel;
            $title = '餐厅账单报表';
            $i = 1;
            //  存储方式 GZIP压缩
            \PHPExcel_Settings::setCacheStorageMethod(\PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip);
            $excel->getProperties()->setCreator('JianDanDian');
            $excel->getActiveSheet()->getColumnDimension()->setAutoSize(true);

            $header = ['订单号','用餐时间','用餐人数','用餐桌号','操作人','点餐详情','点餐价格'];
            foreach ($query->batch() as $orders) {

                if ($i === 1) {
                    $excel->getProperties()->setTitle($title);
                    $excel->getProperties()->setSubject($title);
                    $excel->setActiveSheetIndex(0);
                } else {
                    $excel->createSheet();
                    $excel->setactivesheetindex($i-1);
                }

                $sheet = $excel->getActiveSheet();
                $sheet->setTitle('第'.$i.'页');
                $start_s = 'A';
                $start_i = 1;

                if ($header)
                {
                    foreach ($header as $item)
                    {
                        $sheet->setCellValue($start_s . $start_i, $item);
                        $start_s++;
                    }
                    $start_i++;
                }


                foreach ($orders as $order)
                {
                    $start_s = 'A';
                    $row = [
                        'tradeNo' => $order->trade_no,
                        'time' => date('Y-m-d H:i', $order->created),
                        'peopleNumber' => $order->activeDesk ? $order->activeDesk->people_num : '(无法获取)',
                        'deskNumber' => $order->desk_number,
                        'operateUser' => $order->shopAdminUsername,
                        'menuNum' => $order->menuNum,
                        'price' => $order->total_price,
                    ];
                    foreach($row as $value) {
                        if (strpos($value, '=') === 0) {
                            $value = "'".$value;
                        }
                        $sheet->setCellValue($start_s . $start_i, $value);
                        $start_s++;
                    }
                    $start_i++;
                }
                $i++;
            }
            //重置焦点到第一个SHEET
            $excel->setActiveSheetIndex(0);
            $writer = new \PHPExcel_Writer_Excel2007($excel);
            ob_end_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment;filename = ' . $title . '.xlsx');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check = 0, pre-check = 0');
            header('Pragma: no-cache');
            $writer->save('php://output');
            die;
        }

        $dataProvider = new ActiveDataProvider([
            'query' =>$query,
            'pagination' => [
                'pageSize' => 10
            ]
        ]);

        $orders = $dataProvider->getModels();
        $orderIds = ArrayHelper::map($orders, 'id', 'id');

        foreach($orders as $order) {
            $activeDesk = $order->activeDesk;
            $list[] = [
                'id' => $order->id,
                'tradeNo' => $order->trade_no,
                'time' => date('Y-m-d H:i', $order->created),
                'peopleNumber' => $activeDesk ? $activeDesk->people_num : '(无法获取)',
                'deskNumber' => $order->desk_number,
                'operateUser' => $order->shopAdminUsername,
                'menuNum' => $order->menuNum,
                'price' => $order->total_price,
            ];
        }
        return [
            'list' => $list,
            'totalPrice' => $totalPrice,
            'totalPage' => $dataProvider->pagination->pageCount,
        ];
    }

    /**
     * 账单列表页|订单详情
     */
    public function actionOrderDetail()
    {
        $request = Yii::$app->request;
        $orderId = $request->get('order_id');

        if(!$orderId) {
            throw new Exception('参数错误');
        }
        $order = UserOrder::find()->shop($this->currentShopId)
                                ->andWhere(['id'=>$orderId])
                                ->with(['menus'=>function($query) {
                                    $query->andWhere('is_confirm=:confirm and is_cancel=:noCancel', [':confirm'=>UserOrderMenu::CONFIRM_YES,':noCancel'=>UserOrderMenu::CANCEL_NO]);
                                }])
                                ->one();
        if(!$order) {
            throw new Exception('订单不存在');
        }
        $menuList = [];
        foreach($order->menus as $orderMenu) {
            $attributes = is_array($orderMenu->menu_attr_info) ? array_values($orderMenu->menu_attr_info) : [];
            $menuList[] = [
                'name' => $orderMenu->menu_name,
                'number' => $orderMenu->menu_num,
                'totalPrice' => $orderMenu->totalPrice,
                'attributes' => (array)$attributes,
            ];
        }
        return [
            'deskNumber' => $order->desk_number,
            'created' => date('Y-m-d H:i', $order->created),
            'menuList' => $menuList,
            'price' => $order->total_price,
        ];
    }
}
