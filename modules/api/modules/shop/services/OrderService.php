<?php
namespace api\modules\shop\services;

use Yii;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use app\models\ext\ShopDesk;
use app\models\ext\ShopMenu;
use app\models\ext\ShopActiveDesk;
use app\models\ext\UserOrder;
use app\models\ext\UserOrderMenu;
use app\models\ext\ShopPrintLog;
use \Exception;

/**
 * 订单与菜品业务逻辑处理服务层
 */
class OrderService extends Service
{
    /**
     * 修改菜品数量并确认订单菜品
     * 逻辑注意：1.修改订单、餐桌状态；2.价格方面
     * @param integer $orderId 订单id
     * 注意id不是菜品的id
     */
    public function confirmOrder($orderId)
    {
        $order = UserOrder::find()->shop($this->context->currentShopId)
                        ->andWhere(['id'=>$orderId])
                        ->with([
                            'activeDesk',
                            'desk',
                            'menus'=>function($query) {
                            $query->with('menu');
                        }])
                        ->one();
        if(!$order) {
            throw new Exception('订单不存在');
        }
        $totalPrice = 0;
        $transaction = Yii::$app->db->beginTransaction();
        $addNo = 1;
        try {
            //处理未确认的菜品，后期循环处理性能优化点，根据业务情况调整（平均一桌10个菜以内）
            foreach($order->menus as $orderMenu) {
                if($orderMenu->is_cancel==UserOrderMenu::CANCEL_YES) continue;
                if($orderMenu->is_confirm==UserOrderMenu::CONFIRM_NO) {
                    //菜品不存在则删除，性能优化点(批量处理保存)
                    if(!($menu = $orderMenu->menu) || $orderMenu->menu_num<=0) {
                        try {
                            $this->deleteOrderMenu($orderMenu);
                            continue;
                        }catch(Exception $e) {
                            throw new Exception($this->getModelError($orderMenu, $orderMenu->menu_name.'操作失败，请重试'));
                        }
                    }
                    if(($menu->updateTodayStock(-$orderMenu->menu_num))<0) {
                        $stock = $menu->updateTodayStock($orderMenu->menu_num);
                        throw new Exception($menu->name.'库存不足，剩余'.$stock);
                    }

                    $orderMenu->is_confirm = UserOrderMenu::CONFIRM_YES;
                    //性能优化点(批量处理保存)
                    if(!$orderMenu->save()) {
                        throw new Exception($this->getModelError($orderMenu, '操作失败'));
                    }
                    //库存记录修改
                    $menu->sale += $orderMenu->menu_num;
                    $addNo = $orderMenu->add_no >= $addNo ? $orderMenu->add_no : $addNo;
                    if(!$menu->save()) {
                        throw new Exception($this->getModelError($menu, '销量修改失败'));
                    }
                }
                //业务规则：每次确认点单都会将已经确认的最终价格重置清空
                $totalPrice += ($orderMenu->menu_price * $orderMenu->menu_num);
            }
            $order->status = $addNo>1 ? UserOrder::STATUS_ADD_CONFIRM : UserOrder::STATUS_CONFIRM;
            $this->ensureOrderDeskStatus($order);
            $order->total_price = $totalPrice;
            $order->addStatusRecord($addNo>1 ? UserOrder::STATUS_ADD_CONFIRM : UserOrder::STATUS_CONFIRM);
            $shopAdmin = $this->context->currentShopAdmin;
            $order->shopAdminUsername = $shopAdmin->account.($shopAdmin->username?'('.$shopAdmin->username.')':'');
            if(!$order->save()) {
                throw new Exception($this->getModelError($order, '订单状态更改失败'));
            }

            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
            throw new Exception($e->getMessage());
        }
        return $order;
    }

    /**
     * 更新已点菜的数量
     * 更新数量不做任何的状态修改，也不做任何库存扣减。这些逻辑需要放到确认点单里面去做。
     * @param integer $id 已点菜的orderMenu的id
     * @param integer $num 该菜数量
     */
    public function updateOrderMenuNum($id, $num)
    {
        $shopId = $this->context->currentShopId;
        $orderMenu = UserOrderMenu::find()->innerJoinWith(['order'=>function($query) use($shopId){
            $query->andWhere(UserOrder::tableName().'.shop_id=:shopid', [':shopid'=>$shopId]);
        }, 'menu'], false)->andWhere(UserOrderMenu::tableName().'.id=:id',[':id'=>$id])->one();
        if(!$orderMenu) {
            throw new Exception('数据不存在');
        }
        if($num<=0) {
            $this->deleteOrderMenu($orderMenu);
        } else {
            $orderMenu->menu_num = (int)$num;
            $orderMenu->menu_price = $orderMenu->menu->price * $orderMenu->menu_num;

            //只存库存检查，不做扣减，扣减放到确认点菜
            if(($stock=$orderMenu->menu->getTodayStock()) < $orderMenu->menu_num) {
                throw new Exception($orderMenu->menu_name.'库存不足，剩余'.$stock);
            }

            if(!$orderMenu->save()) {
                throw new Exception($this->getModelError($orderMenu, '修改失败'));
            }
        }
    }

    /**
     * 确保订单状态与餐桌状态的映射关系
     * @param UserOrder $order 订单对象
     * @return void
     */
    private function ensureOrderDeskStatus($order)
    {
        switch($order->status) {
            case UserOrder::STATUS_SUBMIT:
                    $order->desk->status = ShopDesk::STATUS_WAIT_COMFIRM;
                    break;
            case UserOrder::STATUS_CONFIRM:
                    $order->desk->status = ShopDesk::STATUS_SERVE;
                    break;
            case UserOrder::STATUS_ADD_SUBMIT:
                    $order->desk->status = ShopDesk::STATUS_WAIT_COMFIRM;
                    break;
            case UserOrder::STATUS_ADD_CONFIRM:
                    $order->desk->status = ShopDesk::STATUS_SERVE;
                    break;
            case UserOrder::STATUS_TO_BE_PAID:
                    $order->desk->status = ShopDesk::STATUS_WILL_PAY;
                    break;
            case UserOrder::STATUS_PAID:
                    $order->desk->status = ShopDesk::STATUS_EMPTY;
        }
        if(!$order->desk->save()) {
            throw new Exception($this->getModelError($order->desk, '餐桌状态变更失败'));
        }
    }

    /**
     * 检查库存（弃用）
     * @param ShopMenu $menu
     * @param integer $num 点菜数量，一般情况都是正数，代表扣减的数量。如果给出复数，
     * 表明增加库存
     * @throws 库存不足抛出异常
     */
    private function checkAndUpdateStock($menu, $num)
    {
        //扣减库存，如果库存不够则再加回来
        //不通过$menu->getTodayStock()<$num
        //以防时间差内有数据修改导致库存问题
        if($menu->updateTodayStock(-$num)<0) {
            $stock = $menu->updateTodayStock($num);
            throw new Exception($menu->name . '库存不足，剩余'. $stock);
        }
    }

    /**
     * 删除已点菜
     * @param integer|UserOrderMenu $id 已点菜id 或 已点菜数据实例对象
     */
    public function deleteOrderMenu($id)
    {
        if($id instanceof UserOrderMenu) {
            $orderMenu = $id;
        } else {
            $orderMenu = UserOrderMenu::find()->andWhere([UserOrderMenu::tableName().'.id'=>$id, UserOrder::tableName().'.shop_id'=>$this->context->currentShopId])->joinWith('order', false, 'INNER JOIN')->one();
            if(!$orderMenu) {
                throw new Exception('点菜信息不存在');
            }
        }
        if(!$orderMenu->delete()) {
            throw new Exception($this->getModelError($orderMenu, '已点菜删除失败'));
        }
    }

    /**
     * 退菜业务逻辑
     * @param integer $id 已点菜数据id
     * @return ShopPrintLog 小票打印日志模型
     */
    public function cancelOrderMenu($id)
    {
        $currentShopId = $this->context->currentShopId;
        $orderMenu = UserOrderMenu::find()->innerJoinWith([
            'order'=>function($query) use($currentShopId){
                $query->shop($currentShopId);
            }])->andWhere([UserOrderMenu::tableName().'.id'=>$id])
            ->with(['menu'=>function($query) {
                $query->select('id,sale');
            }])->one();
        if(!$orderMenu) {
            throw new Exception('已点菜数据不存在');
        }
        if($orderMenu->is_confirm!=UserOrderMenu::CONFIRM_YES) {
            throw new Exception('已点菜品未确认，请直接删除');
        }
        $orderMenu->is_cancel = UserOrderMenu::CANCEL_YES;
        $transaction = Yii::$app->db->beginTransaction();
        try {

            if(!$orderMenu->save()) {
                throw new Exception($this->getModelError($orderMenu, '退菜失败'));
            }

            //处理菜品销量（扣减）
            if($menu = $orderMenu->menu) {
                $menu->sale = ($menu->sale - $orderMenu->menu_num)>0 ? $menu->sale - $orderMenu->menu_num : 0;
                if(!$menu->save(false)) {
                    throw new Exception($this->getModelError($menu, $menu->name.'销量修改失败'));
                }
            }

            //处理价钱
            //退菜需要扣减金额，但需要在原价上进行扣减，而不是对折后价扣减，否则可能会负数
            //1.可以先退，再统计有效价格；2.可以先统计有效价格，再减去退菜价格
            $order = $orderMenu->order;
            $price = UserOrderMenu::find()->order($order->id)->cancel()->select('sum(menu_price*menu_num)')->scalar();
            $order->total_price = $price;
            if(!$order->save()) {
                throw new Exception($this->getModelError($order, '订单金额修改失败'));
            }

            $attrs = [];
            foreach($orderMenu->menu_attr_info as $attr) {
                if(isset($attr['value'])) $attrs[] = $attr['value'];
            }
            $menu = [
                'name' => $orderMenu->menu_name,
                'num' => $orderMenu->menu_num,
                'attrs' => implode('/', $attrs),
            ];
            $content = $this->context->renderPartial('kitchen_cancel',[
                'menu' => $menu,
                'tradeNo' => $orderMenu->order->trade_no,
                'deskNo' => $orderMenu->order->desk_number,
                'time' => date('Y-m-d H:i:s')
            ]);
            $taskName = $orderMenu->order->desk_number.'后厨退菜-'.$orderMenu->menu_name;
            $printLog = $this->context->printLog($taskName, $content, ShopPrintLog::PRINT_TYPE_KITCHEN);

            $transaction->commit();
        }catch(Exception $e) {
            $transaction->rollback();
            throw new Exception($e->getMessage());
        }
        return $printLog;
    }

    /**
     * 商家提交菜品到订单
     * @param integer $orderId 订单id
     * @param array $menuInfo 添加菜品的数组，格式:[{id:菜品id, num:3, attrs:{"口味":"麻辣"}}]
     */
    public function submitMenu($orderId,array $menuInfos)
    {
        //$menuInfo验证参数正确性
        foreach($menuInfos as $k=>$menuInfo) {
            if(!isset($menuInfo['id']) || !isset($menuInfo['num'])) {
                throw new Exception('参数格式错误');
            }

        }
        $order = UserOrder::find()->where(['id'=>$orderId, 'shop_id'=>$this->context->currentShopId])->one();
        if(!$order) {
            throw new Exception('订单不存在');
        }
        $menuIds = ArrayHelper::getColumn($menuInfos, 'id', false);
        $menus = ShopMenu::find()->shop($this->context->currentShopId)->andWhere(['id'=>$menuIds])->indexBy('id')->all();
        $addNo = UserOrderMenu::find()->andWhere(['order_id'=>$order->id])->orderBy('add_no desc')->select('add_no')->limit(1)->scalar();

        $addPrice = 0;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach($menuInfos as $menuInfo) {
                if(($menu = ArrayHelper::getValue($menus, $menuInfo['id']))===null) {
                    throw new Exception('id'.$menuInfo['id'].'菜品不存在');
                }
                if(!isset($menuInfo['num'])) {
                    throw new Exception($menu->name.'数量为选择');
                }
                $attrs = $normalizeAttrValues = [];
                foreach(ArrayHelper::getValue($menuInfo, 'attrs', []) as $item) {
                    $attrs = array_merge($attrs, $item);
                }
                //如果需要选择规格但规格数据又不对则报错
                foreach((array)$attrs as $attrName=>$attrValue) {
                    if(!$menu->checkAttrExist($attrName, $attrValue)) {
                        unset($attrs[$attrName]);
                        if($menu->getIsNeedSelectAttrs()) {
                            throw new Exception($menu->name.$attrName.'规格选择错误');
                        }
                        continue;
                    }
                    $normalizeAttrValues[] = ['name'=>$attrName, 'value'=>$attrValue];
                }
                if($menu->getIsNeedSelectAttrs() && !$attrs) {
                    throw new Exception($menu->name.'未选择规格');
                }
                //格式化数据
                $orderMenu = new UserOrderMenu;
                $sizeAttrName = ShopMenu::getSizeAttrName();
                // 该菜品单价
                $price = $menu->price;
                //如果该菜需要选择尺寸
                if(isset($attrs[$sizeAttrName])) {
                    foreach(ArrayHelper::getValue($menu->attrs,'sizeAttr.attrValues') as $val) {
                        if($val['name']==$attrs[$sizeAttrName]) {
                            $price = $val['price'];
                        }
                    }
                }

                $attributes = [
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'menu_price' => $price * $menuInfo['num'],
                    'menu_num' => $menuInfo['num'],
                    'menu_attr_info' => $normalizeAttrValues,
                    'add_no' => $addNo+1,
                ];
                if(!($orderMenu->load($attributes, '') && $orderMenu->save())){
                    throw new Exception($this->getModelError($orderMenu), '提交失败');
                }
                $addPrice += $orderMenu->menu_price;
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
            throw new Exception($e->getMessage());
        }
        return [
            'order' => $order,
        ];
    }

    /**
     * 确认订单价格（预结）
     * 改订单状态、改餐桌状态
     * 注意：预结的时候需要把未确认菜品删除，否则前台会把未确认菜品价格算进原始价格
     * @param integer $orderId 订单id
     * @param float $price 最终订单价格
     */
    public function confirmPrice($orderId, $price)
    {
        $order = UserOrder::find()->shop($this->context->currentShopId)
                                ->with([
                                    'desk',
                                    'menus'=>function($query) {//取出未确认的菜
                                        $query->andWhere('is_confirm=:noconfirm', [':noconfirm'=>UserOrderMenu::CONFIRM_NO]);
                                    }
                                ])
                                ->andWhere(['id'=>$orderId])
                                ->one();
        if(!$order) {
            throw new Exception('结算订单不存在');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //获取不到餐桌不影响更改订单状态
            if($order->desk) {
                $order->desk->status = ShopDesk::STATUS_WILL_PAY;
                if(!$order->desk->save()) {
                    throw new Exception($this->getModelError($or));
                }
            }
            //删除未确认菜品，以确保前台显示原始总价正确
            foreach($order->menus as $orderMenu) {
                $orderMenu->delete();
            }

            $order->status = UserOrder::STATUS_TO_BE_PAID;
            $order->total_price = $price;
            $order->addStatusRecord(UserOrder::STATUS_TO_BE_PAID);
            if(!$order->save()) {
                throw new Exception($this->getModelError($order, '价格修改失败'));
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 结算
     * 改订单状态、改餐桌状态
     * 业务逻辑中有很多数据需要处理和注意
     */
    public function finishPay($orderId)
    {
        $order = UserOrder::find()->shop($this->context->currentShopId)->with(['desk'=>function($query) {
            $query->with(['mergeSourceDesks']);
        }, 'menus'=>function($query) {
            $query->andWhere(['is_cancel'=>UserOrderMenu::CANCEL_NO]);
        }])->andWhere(['id'=>$orderId])->one();
        if(!$order) {
            throw new Exception('结算订单不存在');
        }
        //保证价格正确后再完成支付清桌等步骤
        if($order->status!=UserOrder::STATUS_TO_BE_PAID) {
            throw new Exception('请先点击预结确认价格');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //处理餐桌状态
            if($desk = $order->desk) {
                $desk->status = ShopDesk::STATUS_EMPTY;
                $desk->active_desk_id = 0;
                $desk->merge_target_desk_id = 0;
                if($desk->mergeSourceDesks) {
                    foreach($desk->mergeSourceDesks as $mergeDesk) {
                        $mergeDesk->status = ShopDesk::STATUS_EMPTY;
                        $mergeDesk->merge_target_desk_id = 0;
                        $mergeDesk->active_desk_id = 0;
                        if(!$mergeDesk->save()) {
                            throw new Exception('并桌信息清理失败');
                        }
                    }
                }
                if(!$desk->save()) {
                    throw new Exception($this->getModelError($desk, '餐桌信息更改失败'));
                }
            }
            //处理订单
            $order->status = UserOrder::STATUS_PAID;
            $menuNum = [];
            foreach($order->menus as $orderMenu) {
                //删除未确认的菜，节省控件
                if($orderMenu->is_confirm==UserOrderMenu::CONFIRM_NO) {
                    $orderMenu->delete();
                    continue;
                }
                $menuNum[$orderMenu->menu_id] = 1;
            }
            $order->menuNum = array_sum($menuNum);
            $order->addStatusRecord(UserOrder::STATUS_PAID);
            $shopAdmin = $this->context->currentShopAdmin;
            $order->shopAdminUsername = $shopAdmin->account.($shopAdmin->username?'('.$shopAdmin->username.')':'');
            if(!$order->save()) {
                throw new Exception($this->getModelError($order, '价格修改失败'));
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
            throw new Exception($e->getMessage());
        }
        return $order;
    }
}
