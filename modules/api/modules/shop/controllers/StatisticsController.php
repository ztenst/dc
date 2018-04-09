<?php
namespace api\modules\shop\controllers;

use \api\modules\shop\base\Controller;
use app\models\ext\StatisticsSalePrice;
use app\models\ext\StatisticsMenuSale;
use app\models\ext\StatisticsDeskUse;
use app\models\ext\StatisticsDeskCount;
use app\models\ext\ShopShop;
use app\models\ext\ShopActiveDesk;
use app\models\ext\ShopDesk;
use app\models\ext\UserOrderMenu;
use app\models\ext\UserOrder;
use app\helpers\Timestamp;

class StatisticsController extends Controller
{
    /**
     * 销售额统计折线图
     */
    public function actionSalePrice($begin='', $end='', $day='')
    {
        $shopTableName = ShopShop::tableName();
        if($day!=='') {
            switch($day) {
                case 'shangzhou':
                    $begin = date('Y-m-d', Timestamp::getLastWeekBeginTime());
                    $end = date('Y-m-d', Timestamp::getWeekBeginTime()-1);
                    break;
                case 'benyue':
                    $begin = date('Ymd', Timestamp::getMonthBeginTime());
                    $end = '';
                    break;
                case 'shangyue':
                    $begin = date('Y-m-d', Timestamp::getLastMonthBeginTime());
                    $end = date('Y-m-d', Timestamp::getMonthBeginTime()-1);
                    break;
                case 'benzhou':
                default:
                    $begin = date('Y-m-d', Timestamp::getWeekBeginTime());
                    $end = '';
            }
        }

        $query = StatisticsSalePrice::find()->andWhere('shop_id=:shopId', [':shopId'=>$this->currentShopId])->asArray();

        if($begin && (($begin=strtotime($begin))!==false)) {
            $beginYmd = date('Ymd', $begin);
            $query->andWhere('ymd>=:begin', [':begin'=>$beginYmd]);
        }
        if($end && (($end=strtotime($end))!==false)) {
            $endYmd = date('Ymd', $end);
            $query->andWhere('ymd<=:end', [':end'=>$endYmd]);
        }

        $return = [];
        foreach($query->batch() as $data) {
            foreach($data as $v) {
                $return['x'][] = substr($v['ymd'],0,4).'-'.substr($v['ymd'],4,2).'-'.substr($v['ymd'],6,2);
                $return['y'][] = $v['value'];
            }
        }
        //今天数据通过实时取出来
        switch($day) {
            case 'benzhou':
            case 'benyue':
                //该查询条件需要与crontab定时脚本中逻辑一致，可直接复制过来稍加修改
                $todayData = $this->currentShop->getOrders()
                                    ->status(UserOrder::STATUS_PAID)
                                    ->andWhere('created>=:beginTime',[
                                        ':beginTime' => Timestamp::getDayBeginTime(),
                                    ])
                                    ->select([
                                        'groupCount'=>'sum(total_price)',
                                    ])
                                    ->one();
                $return['x'][] = date('Y-m-d');
                $return['y'][] = (double)$todayData->groupCount;//若没符合条件的数据sum将返回null
                break;
        }
        return $return;
    }

    /**
     * 菜品销售量统计饼图接口
     */
    public function actionMenuSale($begin='',$end='',$day='')
    {
        if($day!=='') {
            switch($day) {
                case 'zuotian':
                    $begin = $end = date('Y-m-d',Timestamp::getDayBeginTime()-1);
                    break;
                case 'benzhou':
                    $begin = date('Y-m-d', Timestamp::getWeekBeginTime());
                    $end = '';
                    break;
                case 'shangzhou':
                    $begin = date('Y-m-d', Timestamp::getLastWeekBeginTime());
                    $end = date('Y-m-d', Timestamp::getWeekBeginTime()-1);
                    break;
                case 'benyue':
                    $begin = date('Y-m-d', Timestamp::getMonthBeginTime());
                    $end = '';
                    break;
                case 'shangyue':
                    $begin = date('Y-m-d', Timestamp::getLastMonthBeginTime());
                    $end = date('Y-m-d', Timestamp::getMonthBeginTime()-1);
                    break;
                deafult:
                    $begin = $end = date('Y-m-d');
            }
        }

        $query = StatisticsMenuSale::find()->andWhere('shop_id=:shopId', [':shopId'=>$this->currentShopId])
        ->groupBy('menu_id')
        ->select([
            'value' => 'sum(value)',
            'menu_name',
            'menu_id',
        ]);

        if($begin && (($begin=strtotime($begin))!==false)) {
            $beginYmd = date('Ymd', $begin);
            $query->andWhere('ymd>=:begin', [':begin'=>$beginYmd]);
        }

        if($end && (($end=strtotime($end))!==false)) {
            $endYmd = date('Ymd', $end);
            $query->andWhere('ymd<:end', [':end'=>$endYmd+1]);

        }

        $query->asArray();
        $return = [];
        foreach($query->batch() as $data) {
            foreach($data as $v) {
                $return['legend'][$v['menu_id']] = $v['menu_name'];
                $return['data'][$v['menu_id']] = ['name'=>$v['menu_name'],'value'=>$v['value']];
            }
        }

        //今天数据通过实时取出来
        switch($day) {
            case 'benzhou':
            case 'benyue':
                //该查询条件需要与crontab定时脚本中逻辑一致，可直接复制过来稍加修改
                $todayData = UserOrderMenu::find()
                    ->alias('u')
                    ->andWhere('is_cancel=:noCancel and is_confirm=:confirm and u.created>=:begin', [':noCancel'=>UserOrderMenu::CANCEL_NO, ':confirm'=>UserOrderMenu::CONFIRM_YES, ':begin'=>Timestamp::getDayBeginTime()])
                    ->innerJoinWith(['order o'=>function($query) {
                        $query->select('o.id,o.shop_id');
                    }])
                    ->select([
                        'u.menu_id',
                        'u.order_id',
                        'u.menu_name',
                        'groupCount' => 'sum(u.menu_num)',
                    ])
                    ->indexBy('menu_id')
                    ->groupBy('menu_id')//我们需要每天每个菜品的量
                    ->all();
                foreach($todayData as $v) {
                    if(isset($return['data'][$v->menu_id])) {
                        $return['data'][$v->menu_id]['value'] += $v->groupCount;
                    } else {
                        $return['legend'][$v->menu_id] = $v->menu_name;
                        $return['data'][$v->menu_id] = ['name'=>$v->menu_name, 'value'=>$v->groupCount];
                    }
                }
        }
        if(isset($return['legend'], $return['data'])) {
            $return['legend'] = array_values($return['legend']);
            $return['data'] = array_values($return['data']);
        }
        return $return;
    }

    /**
     * 获取指定区间内的日期
     * @param string $begin 开始日期当天的任意时间点的时间戳
     * @param string $end 结束日期当天的任意时间点的时间戳
     */
    private function getDateRange($beginYmd, $endYmd, $format='Ymd')
    {
        $begin = mktime(0,0,0,substr($beginYmd,4,2),substr($beginYmd,6,2),substr($beginYmd,0,4));
        $end = mktime(0,0,0,substr($endYmd,4,2),substr($endYmd,6,2),substr($endYmd,0,4));
        // 计算日期段内有多少天
        $days = ($end-$begin)/86400+1;

        // 保存每天日期
        $date = [];

        for($i=0; $i<$days; $i++){
            $date[] = date($format, $begin+(86400*$i));
        }

        return $date;
    }

    /**
     * 翻桌率折现统计图接口
     */
    public function actionDeskUseRate($begin='', $end='', $day='benzhou')
    {
        if($begin&&(($beginTime=strtotime($begin))!==false) || $end&&(($endTime=strtotime($end))!==false)) {
            if(isset($beginTime)) {
                $beginYmd = date('Ymd', $beginTime);
                //没有结尾或者超过365天
                if(!isset($endTime) || (($endTime-$beginTime)/86400)>365) {
                    $endYmd = date('Ymd', $beginTime+86400*365);
                }
            }
            if(isset($endTime)) {
                $endYmd = date('Ymd', $endTime);
                if(!isset($beginTime) || (($endTime-$beginTime)/86400)>365) {
                    $beginYmd = date('Ymd', $endTime-86400*365);
                }
            }
        } else {
            switch($day) {
                case 'shangzhou':
                    $beginYmd = date('Ymd', Timestamp::getLastWeekBeginTime());
                    $endYmd = date('Ymd', Timestamp::getWeekBeginTime()-1);
                    break;
                case 'benyue':
                    $beginYmd = date('Ymd', Timestamp::getMonthBeginTime());
                    $endYmd = date('Ymd');
                    break;
                case 'shangyue':
                    $beginYmd = date('Ymd', Timestamp::getLastMonthBeginTime());
                    $endYmd = date('Ymd', Timestamp::getMonthBeginTime()-1);
                    break;
                case 'benzhou':
                default:
                    $beginYmd = date('Ymd', Timestamp::getWeekBeginTime());
                    $endYmd = date('Ymd');
            }
        }


        $deskCountQuery = StatisticsDeskCount::find()->alias('d')
        ->andWhere('d.shop_id=s.shop_id and d.ymd=s.ymd')
        ->select('d.number');

        $data = StatisticsDeskUse::find()
        ->alias('s')
        ->andWhere('shop_id=:shopId and ymd>=:begin and ymd<=:end', [
            ':shopId' => $this->currentShopId,
            ':begin' => $beginYmd,
            ':end' => $endYmd,
        ])
        ->select([
            's.ymd',
            'groupCount' => 'sum(s.value)',
            'deskCount' => $deskCountQuery
        ])
        ->groupBy('s.ymd')//获得每天所有餐桌使用的总次数
        ->indexBy('ymd')
        ->asArray()
        ->all();

        $dataRange = $this->getDateRange($beginYmd, $endYmd);
        $return = [];

        foreach($dataRange as $ymd) {
            $return['x'][] = substr($ymd,0,4).'-'.substr($ymd,4,2).'-'.substr($ymd,6,2);
            //有订单统计数据并且总餐桌数统计大于0，否则就算那一天数据无效，-100%表示根本没有营业
            if(isset($data[$ymd]) && $data[$ymd]['deskCount']>0) {
                $return['y'][$ymd] = 100*($data[$ymd]['groupCount']-$data[$ymd]['deskCount'])/$data[$ymd]['deskCount'];
            } else {
                $return['y'][$ymd] = -100;
            }
        }

        //今天数据通过实时取出来
        switch($day) {
            case 'benzhou':
            case 'benyue':
                //该查询条件需要与crontab定时脚本中逻辑一致，可直接复制过来稍加修改
                $deskCount = ShopDesk::find()->shop($this->currentShopId)->select([
                    'groupCount' => 'count(id)',
                ])->scalar();
                $activeDeskTableName = ShopActiveDesk::tableName();
                $todayUseCount = ShopActiveDesk::find()
                    ->andWhere('a.shop_id=:shopId and a.created>=:begin',[':begin'=>Timestamp::getDayBeginTime(),':shopId'=>$this->currentShopId])
                    ->alias('a')
                    ->innerJoinWith(['order o'=>function($query) {
                        $query->andWhere('o.status=:orderStatus', [':orderStatus'=>UserOrder::STATUS_PAID])
                            ->select('o.desk_number,o.id');
                    }])->count();
                $return['y'][date('Ymd')] = $deskCount>0 ? (100*($todayUseCount-$deskCount)/$deskCount) : 0;
        }
        if(isset($return['y'])) {
            $return['y'] = array_values($return['y']);
        }
        return $return;
    }

    /**
     * 每桌使用情况统计接口
     */
    public function actionDeskUse($begin='', $end='', $day='jintian')
    {
        if($begin&&(($beginTime=strtotime($begin))!==false) || $end&&(($endTime=strtotime($end))!==false)) {
            if(isset($beginTime)) {
                $beginYmd = date('Ymd', $beginTime);
                //没有结尾或者超过365天
                if(!isset($endTime) || (($endTime-$beginTime)/86400)>365) {
                    $endYmd = date('Ymd', $beginTime+86400*365);
                }
            }
            if(isset($endTime)) {
                $endYmd = date('Ymd', $endTime);
                if(!isset($beginTime) || (($endTime-$beginTime)/86400)>365) {
                    $beginYmd = date('Ymd', $endTime-86400*365);
                }
            }
        } else {
            switch($day) {
                case 'zuotian':
                    $beginYmd = $endYmd = date('Ymd', Timestamp::getDayBeginTime()-1);
                    break;
                case 'benzhou':
                    $beginYmd = date('Ymd', Timestamp::getWeekBeginTime());
                    $endYmd = date('Ymd');
                    break;
                case 'shangzhou':
                    $beginYmd = date('Ymd', Timestamp::getLastWeekBeginTime());
                    $endYmd = date('Ymd', Timestamp::getWeekBeginTime()-1);
                    break;
                case 'benyue':
                    $beginYmd = date('Ymd', Timestamp::getMonthBeginTime());
                    $endYmd = date('Ymd');
                    break;
                case 'shangyue':
                    $beginYmd = date('Ymd', Timestamp::getLastMonthBeginTime());
                    $endYmd = date('Ymd', Timestamp::getMonthBeginTime()-1);
                    break;
                case 'jintian':
                default:
                    $beginYmd = $endYmd = date('Ymd');
            }
        }

        $query = StatisticsDeskUse::find()
                ->andWhere('shop_id=:shopId and ymd>=:beginYmd and ymd<=:endYmd', [
                    ':shopId'=>$this->currentShopId,
                    ':beginYmd'=>$beginYmd,
                    ':endYmd'=>$endYmd,
                ])
                ->groupBy('shop_id,ymd,desk_number')
                ->select([
                    'desk_number',
                    'groupCount' => 'sum(value)',
                ])
                ->asArray();

        $return = $deskCount = [];
        foreach($query->batch() as $uses) {
            foreach($uses as $use) {
                $return['legend'][$use['desk_number']] = $use['desk_number'];
                $deskCount[$use['desk_number']] = (isset($deskCount[$use['desk_number']]) ? $deskCount[$use['desk_number']] : 0) + $use['groupCount'];
                $return['data'][$use['desk_number']] = [
                    'name'=>$use['desk_number'],
                    'value'=>$deskCount[$use['desk_number']],
                ];
            }
        }

        switch($day) {
            case 'jintian':
            case 'benzhou':
            case 'benyue':
                $todayData = ShopActiveDesk::find()
                    ->andWhere('a.created>=:begin',[':begin'=>Timestamp::getDayBeginTime()])
                    ->alias('a')
                    ->innerJoinWith(['order o'=>function($query) {
                        $query->andWhere('o.status=:orderStatus', [':orderStatus'=>UserOrder::STATUS_PAID])
                            ->select('o.desk_number,o.id');
                    }])
                    ->groupBy('a.desk_id')
                    ->select([
                        'groupCount' => 'count(a.id)',
                        'a.order_id'
                    ])
                    ->all();
                foreach($todayData as $v) {
                    $deskNumber = $v->order->desk_number;
                    if(isset($return['data'][$deskNumber])) {
                        $return['data'][$deskNumber]['value'] += $v->groupCount;
                    } else {
                        $return['legend'][$deskNumber] = $deskNumber;
                        $return['data'][$deskNumber] = ['name'=>$deskNumber, 'value'=>$v->groupCount];
                    }
                }
        }
        if(isset($return['legend'],$return['data'])) {
            $return['legend'] = array_values($return['legend']);
            $return['data'] = array_values($return['data']);
        }
        return $return;
    }
}
