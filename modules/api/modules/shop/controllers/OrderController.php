<?php
namespace api\modules\shop\controllers;

use Yii;
use yii\helpers\Json;
use app\models\ext\UserOrder;
use app\models\ext\UserOrderMenu;
use app\models\ext\ShopPrintLog;
use yii\base\DynamicModel;
use \Exception;

class OrderController extends \api\modules\shop\base\Controller
{
    /**
     * 餐桌相关业务逻辑层对象
     * @var DeskService
     */
    private $_orderService;
    /**
     * 餐桌相关业务逻辑层对象
     * @return DeskService
     */
    public function getOrderService()
    {
        if($this->_orderService===null) {
            return $this->_orderService = new \api\modules\shop\services\OrderService(['context'=>$this]);
        }
        return $this->_orderService;
    }

    /**
     * 餐桌详情页|确认点单按钮接口
     * POST请求参数：
     * order_id: 订单id
     */
    public function actionOrderConfirm()
    {
        $orderId = Yii::$app->request->post('order_id', 0);
        // $menu = Yii::$app->request->post('menu', '[]');
        // $menu = Json::decode($menu);
        if(!$orderId) {
            throw new Exception('请求方式或参数错误');
        }
        $order = $this->orderService->confirmOrder($orderId);
        $this->wsService->pushRefreshDeskInfo($this->currentShop);
        $this->wsService->pushRefreshIndex($this->currentShop);
        $this->wsService->pushShopConfirmOrder($order->desk_id);
        return '确认成功';
    }

    /**
     * 餐桌详情页|左侧删除已点菜数据
     * POST请求参数：
     * - id: 已点菜数据id
     */
    public function actionOrderMenuDelete()
    {
        $orderMenuId = Yii::$app->request->post('id');
        if(!$orderMenuId) {
            throw new Exception('请求方式或参数错误');
        }
        $this->orderService->deleteOrderMenu($orderMenuId);
        $this->wsService->pushRefreshDeskInfo($this->currentShop);
        return '删除成功';
    }

    /**
     * 餐桌详情页|左侧退菜接口
     */
    public function actionOrderMenuCancel()
    {
        $orderMenuId = Yii::$app->request->post('id');
        if(!$orderMenuId) {
            throw new Exception('请求方式或参数错误');
        }
        $printLog = $this->orderService->cancelOrderMenu($orderMenuId);
        $this->wsService->pushRefreshDeskInfo($this->currentShop);
        $this->wsService->pushRefreshIndex($this->currentShop);

        return [
            'msg' => '退菜成功',
            'htmlContent' => $printLog->content,
            'printId' => $printLog->id,
        ];
    }

    /**
     * 餐桌详情页|左侧增减菜品数量接口
     * POST请求参数：
     * - id: 已点菜的id
     * - num: 数量
     */
    public function actionOrderMenuNumUpdate()
    {
        $id = Yii::$app->request->post('id');
        $num = Yii::$app->request->post('num', null);
        if(!$id || $num===null) {
            throw new Exception('请求方式或参数错误');
        }
        $this->orderService->updateOrderMenuNum($id, $num);
        return '修改成功';
    }

    /**
     * 餐桌详情页|预结算接口
     * POST请求参数：
     * - order_id: 订单id
     * - price: 最终价格（包含包括折后价）
     */
    public function actionPriceConfirm()
    {
        $orderId = Yii::$app->request->post('order_id');
        $price = Yii::$app->request->post('price');
        // $free = Yii::$app->request->post('free', null);
        if(!$orderId || $price===null) {
            throw new Exception('请求方式或参数错误');
        }
        $this->orderService->confirmPrice($orderId, $price);
        $this->wsService->pushRefreshDeskInfo($this->currentShop);
        $this->wsService->pushRefreshIndex($this->currentShop);
        return '操作成功';
    }

    /**
     * 餐桌详情页|完成付款接口
     */
    public function actionFinishPay()
    {
        $orderId = Yii::$app->request->post('order_id');
        if(!$orderId) {
            throw new Exception('请求方式或参数错误');
        }
        $order = $this->orderService->finishPay($orderId);
        $this->wsService->pushRefreshDeskInfo($this->currentShop);
        $this->wsService->pushRefreshIndex($this->currentShop);
        $this->wsService->pushScanCode($order->desk_id);
        return '操作成功';
    }

    /**
     * 打印小票接口
     * GET参数:
     * - type: 小票类别。前台小票：1，后厨小票：2
     * - orderId: 订单id
     * - addNo: 打印菜品批次
     * 后台打印的小票共四种类型
     * 1. 前台给顾客点菜确认的小票（触发条件：选择点菜批次的tab按钮后人工主动进行打印）
     * 2. 前台结账时给顾客的总账单小票（触发条件：选择点菜批次的“全部”tab按钮后人工主动进行打印）
     * 3. 给后厨打印的小票（触发条件：择点菜批次的tab按钮后人工主动进行打印）
     * 4. 给后厨退菜的小票（触发条件：点击“退菜”成功后自动打印）
     */
    public function actionPrint($type, $orderId, $addNo=0)
    {
        $addNo = intval($addNo);
        $model = DynamicModel::validateData(compact('type', 'orderId', 'addNo'), [
            [['type'],'in','range'=>['1','2'],'message'=>'打印类型必须是1或2'],
            [['orderId','type'],'integer','integerOnly'=>true],
        ]);
        if($model->hasErrors()) {
            throw new Exception($this->orderService->getModelError($model,'参数错误'));
        }
        //加shop_id条件避免其他登录商家恶意访问该接口造成打印机打印泛滥
        $order = UserOrder::find()->shop($this->currentShopId)->andWhere(['shop_id'=>$this->currentShopId,'id'=>$orderId])->with(['menus'=>function($query) use($addNo){
            if($addNo>0) {
                $query->andWhere('add_no=:addNo', [':addNo'=>$addNo]);
            }
            //未退菜、已确认
            $query->andWhere('is_cancel=:nocancel and is_confirm=:isconfirm', [':nocancel'=>UserOrderMenu::CANCEL_NO,':isconfirm'=>UserOrderMenu::CONFIRM_YES])->orderBy('add_no asc');
        }])->one();
        if(!$order) {
            throw new Exception('订单不存在');
        } elseif(!$order->menus) {
            throw new Exception('无法获取点餐信息');
        }
        switch($type) {
            case '1': $printLog = $this->printBill($order,$addNo);break;
            case '2': $printLog = $this->printKitchen($order,$addNo);break;
        }
        if(isset($printLog)) {
            return [
                'printId' => $printLog->id,
                'htmlContent' => $printLog->content,
            ];
        } else {
            throw new Exception('无法生成打印内容');
        }
    }

    /**
     * 后厨小票模板
     * @return Html
     */
    private function printKitchen($order, $addNo)
    {
        foreach($order->menus as $menu) {
            $attrs = [];
            foreach($menu->menu_attr_info as $attr) {
                if(isset($attr['value'])) $attrs[] = $attr['value'];
            }
            $menus[$menu->id] = [
                'name' => $menu->menu_name,
                'num' => $menu->menu_num,
                'attrs' => implode('/', $attrs),
            ];
        }
        $params = [
            'addNo' => $addNo,
            'tradeNo' => $order->trade_no,
            'deskNo' => $order->desk_number,
            'menus' => $menus,
            'time' => date('Y-m-d H:i:s'),
        ];

        $content =  $this->renderPartial('kitchen', $params);
        $taskName = $order->desk_number.'桌厨房小票'.($addNo>0?'-'.$addNo:'总单');
        return $this->printLog($taskName, $content, ShopPrintLog::PRINT_TYPE_KITCHEN);
    }

    /**
     * 打印前台总单
     */
    private function printBill($order, $addNo)
    {
        $price = 0;//订单总价
        foreach($order->menus as $menu) {
            $attrs = [];
            foreach($menu->menu_attr_info as $attr) {
                if(isset($attr['value'])) $attrs[] = $attr['value'];
            }
            $menus[$menu->add_no<=1 ? 'diancai':'jiacai'][$menu->id] = [
                'name' => $menu->menu_name,
                'addNo' => $menu->add_no,
                'num' => $menu->menu_num,
                'price' => $menu->totalPrice,
                'attrs' => implode('/', $attrs),
            ];
            $price += $menu->totalPrice;
        }
        $params = [
            'addNo' => $addNo,
            'tradeNo' => $order->trade_no,
            'deskNo' => $order->desk_number,
            'admin' => $this->getCurrentShopAdmin()->account,
            'time' => date('Y-m-d H:i:s'),
            'menus' => $menus,
            'originalPrice' => $price,
            'discountPrice' => $order->total_price,
            'discount' => ($price-$order->total_price)>0 ? $price-$order->total_price : 0,
            'shopName' => $this->currentShop->name,
        ];
        $content = $this->renderPartial('bill', $params);
        $taskName = $order->desk_number .'桌顾客'.($addNo>0?'点菜小票-'.$addNo:'总账单');
        return $this->printLog($taskName, $content, ShopPrintLog::PRINT_TYPE_FRONT);
    }

    /**
     * 记录打印日志
     * @param string $taskName 打印任务名称
     * @param string $content 打印的小票内容
     * @param string $printType 打印类型
     * @return ShopPrintLog
     */
    public function printLog($taskName, $content, $printType)
    {
        $model = new ShopPrintLog([
            'shop_id' => $this->currentShopId,
            'name' => $taskName,
            'content' => $content,
            'print_type' => $printType,
        ]);
        if(!$model->save()) {
            $msg = $model->hasErrors() ? current($model->getFirstErrors()) : '打印日志记录失败';
            throw new Exception($msg);
        }
        return $model;
    }

    /**
     * 报告打印结果
     * POST请求参数：
     * -id: 打印日志id
     * -success: 是否打印成功，成功给出1，失败给出0
     */
    public function actionPrintLog()
    {
        $request = Yii::$app->request;
        $id = $request->post('id');
        $success = $request->post('success', 0);

        $printLog = ShopPrintLog::findOne($id);
        if(!$printLog) {
            throw new Exception('打印记录不存在');
        }
        if($success) {
            //打印成功的话就把错误清零
            $printLog->success++;
            $printLog->fail = 0;
        } else {
            $printLog->fail++;
        }
        if(!$printLog->save()) {
            $msg = $printLog->hasErrors() ? current($printLog->getFirstErrors()) : '保存失败';
            throw new Exception($msg);
        }
        return '保存成功';
    }
}
