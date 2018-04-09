<?php
namespace app\commands;

use Yii;
use yii\db\Query;
use yii\console\Controller;
use yii\helpers\Console;
use app\models\ext\ShopMenu;
use app\models\ext\UserOrderMenu;
use app\models\ext\ShopShop;
use app\models\ext\ShopDesk;
use app\models\ext\ShopActiveDesk;
use app\models\ext\UserOrder;
use app\models\ext\StatisticsDeskCount;
use app\models\ext\StatisticsSalePrice;
use app\models\ext\StatisticsMenuSale;
use app\models\ext\StatisticsDeskUse;
use app\helpers\Timestamp;
use \Exception;

/**
 * 定时统计脚本
 * 刷新所有商家菜品库存
 * 0 0 * * * /usr/local/php/bin/php /home/www/hj_rms/yii crontab/refresh-stock
 * 2 0 * * * /usr/local/php/bin/php /home/www/hj_rms/yii crontab/statistics-sale-price
 * 4 0 * * * /usr/local/php/bin/php /home/www/hj_rms/yii crontab/statistics-menu-sale
 * 6 0 * * * /usr/local/php/bin/php /home/www/hj_rms/yii crontab/statistics-desk-use
 * 50 23 * * * /usr/local/php/bin/php /home/www/hj_rms/yii crontab/statistics-desk-count
 */
class CrontabController extends Controller
{
    /**
     * 刷新每日库存
     */
    public function actionRefreshStock()
    {
        $menus = ShopMenu::find()->status(ShopMenu::STATUS_ACTIVE)->all();
        foreach($menus as $menu) {
            $menu->updateTodayStock();
        }
        Yii::info('finished', 'refreshStock');
        $this->stdout('finished');
    }

    /**
     * 转化需要统计数据的时间区间
     * @param string $timeStr 字符串格式：yyyy-mm-dd|days，如"2017-07-04|12"表示统
     * 计2017-07-04开始连续12天（包括07-04）的数据
     * @return  [20170714=>1499961600] 注意每个元素都是一天的开始时间戳
     */
    private function getYmdAndTimestamp($timeStr)
    {
        $map = [];
        $beginTimestamp = time()-86400;//默认统计的应该是前一天的数据
        $timeStr = $timeStr ? : date('Y-m-d|1', $beginTimestamp);

        if(!$this->confirm('指定的参数日期是否确定无误？')) {
            $this->stderr('程序终止', Console::FG_RED, Console::UNDERLINE);
            Yii::$app->end();
        }
        $timeStr = explode('|', $timeStr);
        $days = isset($timeStr[1])&&($timeStr[1]>1) ? $timeStr[1] : 1;
        if(!isset($timeStr[0]) || (($beginTimestamp=strtotime($timeStr[0]))===false)) {
            $this->stdout('日期格式有误，应为yyyy-mm-dd|days，其中竖线前面的代表统计起始日期，竖线之后代表需要统计的天数，默认1.', Console::FG_RED, Console::UNDERLINE);
            Yii::$app->end();
        }
        for($i=0;$i<$days;$i++) {
            if($beginTimestamp>time()) break;
            $begin = Timestamp::getDayBeginTime($beginTimestamp+$i*86400);
            $map[date('Ymd', $begin)] = $begin;
        }

        return $map;
    }

    /**
     * 保存销售额统计数据
     * @throws 保存失败会抛出异常，请捕获进行处理
     */
    private function saveStaticsSalePrice($shopId,$ymd,$value)
    {
        $model = new StatisticsSalePrice([
            'shop_id' => $shopId,
            'ymd' => $ymd,
            'value' => $value,
        ]);
        if(!$model->save()) {
            $msg = $model->hasErrors() ? current($model->getFirstErrors()) : '销售额统计保存失败';
            throw new Exception($msg);
        }
    }

    /**
     * 销售额统计定时脚本
     * 有几点注意点：
     * 1. PHP运行时长设置
     * 2. 内存溢出控制，目前采用pdo的fetch（即yii\db\query::batch）来减少内存占用
     * 3. 尽量减少查询次数
     * 由于逻辑较为复杂，还是需要以控制内存不溢出为主，所以在查询次数上优化的优先级低于
     * 控制内存溢出，下面遍历商家和订单都使用的是batch，但foreach遍历中查询次数可能较多
     * 无法在同时batch遍历的同时再batch
     * --------------------------------------------------------------------
     * 每个商家统计开始数据从该商家第一个订单已完成支付订单开始计算数据；没有任何订单的
     * 商家不生成统计数据。
     * @param string $timeStr 详情见{getYmdAndTimestamp()}
     */
    public function actionStatisticsSalePrice($timeStr='')
    {
        //[20170714=>1499961600]
        $timeMap = $this->getYmdAndTimestamp($timeStr);

        $userOrderTableName = Userorder::tableName();
        $shopTableName = ShopShop::tableName();

        $beginTime = min($timeMap);
        $endTime = max($timeMap);

        //取出有完成订单的商家
        $shopQuery = ShopShop::find()->andWhere("$shopTableName.status=:shopStatus", [':shopStatus'=>ShopShop::STATUS_ACTIVE])
                                ->innerJoinWith(['firstPaidOrder'])
                                ->select("$shopTableName.id");

        //去掉下面注释可以查看sql
        // echo $query->createCommand()->sql;die;
        $transaction = Yii::$app->db->beginTransaction();
        $hasData = false;
        try {
            foreach($shopQuery->batch() as $shops) {
                foreach($shops as $shop) {
                    $orderQuery = $shop->getOrders()
                                        ->status(UserOrder::STATUS_PAID)
                                        ->andWhere('created>=:beginTime and created<:endTime',[
                                            ':beginTime' => $beginTime,
                                            ':endTime' => $endTime+86400,
                                        ])
                                        ->select([
                                            'groupCount'=>'sum(total_price)',
                                            'ymd'=>'FROM_UNIXTIME(created, \'%Y%m%d\')',
                                        ])
                                        ->orderBy('created asc')
                                        ->groupBy('ymd');
                    //处理mysql错误码23000，代表数据库复合主键重复，该错误可以忽略并继续执行
                    try {
                        foreach($orderQuery->batch() as $orders) {
                            foreach($orders as $order) {
                                $this->saveStaticsSalePrice($shop->id,$order->ymd,$order->groupCount);
                                unset($timeMap[$order->ymd]);
                            }
                        }
                        foreach($timeMap as $ymd=>$timestamp) {
                            //如果该商家第一条已完成支付订单时间晚于当前查询最早时间，就
                            //不用往数据库插入数据了，节省空间
                            if($ymd<date('Ymd',$shop->firstPaidOrder->created)) continue;
                            $this->saveStaticsSalePrice($shop->id,$ymd,0);
                        }
                    } catch(Exception $e) {
                        if($e->getCode()==23000) {
                            continue;
                        } else {
                            throw $e;
                        }
                    }

                }
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
            Yii::info($e->getMessage(), 'statisticsSalePrice');
            throw new Exception($e->getMessage());
        }
        $msg = $timeStr.' finished';
        Yii::info($msg, 'statisticsSalePrice');
        $this->stdout($msg);
    }

    /**
     * 菜品销售统计（饼图）
     * 分析：由于是饼图，需要把每个菜品每天的销量记下来，以便于后期可以针对任何时间段
     * 获得饼图所需要的数据。如果菜品没有销量，就不需要记录统计了，没有意义的，这是与
     * 销售额折线统计图的区别。没有任何销量则就意味着饼图扇形区块占0%，就是一条线，所以
     * 不用记录。
     */
    public function actionStatisticsMenuSale($timeStr='')
    {
        //[20170714=>1499961600]
        $timeMap = $this->getYmdAndTimestamp($timeStr);
        $shopMenuTableName = ShopMenu::tableName();
        $userOrderMenuTableName = UserOrderMenu::tableName();

        $beginTime = min($timeMap);
        $endTime = max($timeMap);

        $userOrderQuery = UserOrderMenu::find()
        ->alias('u')
        ->andWhere('is_cancel=:noCancel and is_confirm=:confirm and u.created>=:begin and u.created<:end', [':noCancel'=>UserOrderMenu::CANCEL_NO, ':confirm'=>UserOrderMenu::CONFIRM_YES, ':begin'=>$beginTime, ':end'=>$endTime+86400])
        ->innerJoinWith(['order o'=>function($query) {
            $query->select('o.id,o.shop_id');
        }])
        ->select([
            'u.menu_id',
            'o.shop_id',
            'u.order_id',
            'u.menu_name',
            'groupCount' => 'sum(u.menu_num)',
            'ymd' => 'FROM_UNIXTIME(u.created, \'%Y%m%d\')'
        ])
        ->groupBy('menu_id,ymd');//我们需要每天每个菜品的量

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach($userOrderQuery->batch() as $userOrderMenus) {
                foreach($userOrderMenus as $userOrderMenu) {
                    $model = new StatisticsMenuSale([
                        'shop_id' => $userOrderMenu->order->shop_id,
                        'menu_id' => $userOrderMenu->menu_id,
                        'ymd' => $userOrderMenu->ymd,//这里用确认点餐的时间，若没保存好，就用数据添加时间来代替
                        'menu_name' => $userOrderMenu->menu_name,
                        'value' => $userOrderMenu->groupCount,
                    ]);

                    //处理mysql错误码23000，代表数据库复合主键重复，该错误可以忽略并继续执行
                    try {
                        if(!$model->save()) {
                            $msg = $model->hasErrors() ? current($model->getFirstErrors()) : '菜品销量统计失败';
                            throw new Exception($msg);
                        }
                    } catch(Exception $e) {
                        if($e->getCode()==23000) {
                            continue;
                        } else {
                            throw $e;
                        }
                    }
                }
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
            Yii::info($e->getMessage(), 'statisticsMenuSale');
            throw new Exception($e->getMessage());
        }
        $msg = $timeStr.' finished';
        Yii::info($msg, 'statisticsMenuSale');
        $this->stdout($msg);
    }

    /**
     * 餐桌使用情况统计
     * 用于“餐厅每天翻桌率统计”、“餐厅每个餐桌使用次数”
     * “餐厅每天翻桌率统计”：需要餐厅每个餐桌每天的使用次数、餐厅每天餐桌总数
     * “餐厅每个餐桌使用次数”：需要餐厅每个餐桌每天的使用次数
     * -------------------------------------------------------
     * 分析：
     * 这里把有使用次数的餐桌信息保存下来，到时候统计“每天翻桌率”时，获取商家所有餐桌并
     * 关联该统计表，没有保存进来的数据算作0次使用。再获取当天的总桌数做计算；
     * 算每个餐桌使用次数时，由于是饼图，所以0次使用的数据无法使用（因为是个线条，画不出区块）
     * 也用不着画出来
     */
    public function actionStatisticsDeskUse($timeStr='')
    {
        //[20170714=>1499961600]
        $timeMap = $this->getYmdAndTimestamp($timeStr);
        $deskTableName = ShopDesk::tableName();
        $orderTableName = UserOrder::tableName();
        $activeDeskTableName = ShopActiveDesk::tableName();

        $beginTime = min($timeMap);
        $endTime = max($timeMap);
        // var_dump($beginTime,$endTime+86400,$timeMap);die;

        $query = ShopActiveDesk::find()
        ->andWhere('a.created>=:begin and a.created<:end',[':begin'=>$beginTime, ':end'=>$endTime+86400])
        ->alias('a')
        ->innerJoinWith(['order o'=>function($query) {
            $query->andWhere('o.status=:orderStatus', [':orderStatus'=>UserOrder::STATUS_PAID])
                ->select('o.desk_number,o.id');
        }])
        ->groupBy('a.desk_id,ymd')
        ->select([
            'ymd' => 'FROM_UNIXTIME(a.created,\'%Y%m%d\')',
            'groupCount' => 'count(a.id)',
            'a.shop_id',
            'a.desk_id',
            'a.order_id',
        ]);

        // echo $query->createCommand()->sql;die;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach($query->batch() as $rows) {
                foreach($rows as $row) {
                    $model = new StatisticsDeskUse([
                        'shop_id' => $row->shop_id,
                        'ymd' => $row->ymd,
                        'desk_id' => $row->desk_id,
                        'desk_number' => $row->order->desk_number,
                        'value' => $row->groupCount,
                    ]);
                    //处理mysql错误码23000，代表数据库复合主键重复，该错误可以忽略并继续执行
                    try {
                        if(!$model->save()) {
                            $msg = $model->hasErrors() ? current($model->getFirstErrors()) : '餐桌使用次数统计失败';
                            throw new Exception($msg);
                        }
                    } catch(Exception $e) {
                        if($e->getCode()==23000) {
                            continue;
                        } else {
                            throw $e;
                        }
                    }

                }
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
            Yii::info($e->getMessage(), 'statisticsDeskUse');
            throw new Exception($e->getMessage());
        }
        $msg = $timeStr.' finished';
        Yii::info($msg, 'statisticsDeskUse');
        $this->stdout($msg);
    }

    /**
     * 餐桌数量统计
     */
    public function actionStatisticsDeskCount()
    {
        $query = ShopDesk::find()->select([
            'groupCount' => 'count(id)',
            'shop_id'
        ])->groupBy('shop_id');

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach($query->batch() as $desks) {
                foreach($desks as $desk) {
                    $model = new StatisticsDeskCount([
                        'shop_id' => $desk->shop_id,
                        'number' => $desk->groupCount,
                        'ymd' => date('Ymd'),
                    ]);
                    //处理mysql错误码23000，代表数据库复合主键重复，该错误可以忽略并继续执行
                    try {
                        if(!$model->save()) {
                            $msg = $model->hasErrors() ? current($model->getFirstErrors()) : '餐桌数量统计失败';
                            throw new Exception($msg);
                        }
                    } catch(Exception $e) {
                        if($e->getCode()==23000) {
                            continue;
                        } else {
                            throw $e;
                        }
                    }
                }
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
            Yii::info($e->getMessage(), 'statisticsDeskCount');
            throw new Exception($e->getMessage());
        }
        Yii::info('finished', 'statisticsDeskCount');
        echo 'finished';
    }
}
