<?php
namespace app\modules\admin\controllers;

use Yii;
use yii\helpers\Json;
use app\services\WsService;
use app\modules\admin\base\Controller;

class MonitorController extends Controller
{
    private $_wsService;

    public function getWsService()
    {
        if($this->_wsService===null) {
            $this->_wsService = new WsService(['context'=>$this]);
        }
        return $this->_wsService;
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 返回api接口数据
     * @return string
     */
    private function returnApi($msg, $data=[], $code=0)
    {
        return Json::encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 获取实时在线人数
     */
    public function actionClientCount()
    {
        $range = 30;//显示时间区间长度，默认3分钟（180秒），单位：秒
        $interval = 3;//间隔请求时间，单位：秒
        $size = intval($range/$interval);
        $init = [];
        $now = $time = time();

        //获取所有socket客户端数量
        try {
            $allCount = Yii::$app->socket->getAllClientCount();
        }catch(Exception $e) {
            $allCount = 0;
        }
        $allClient = [
            'name' => date('Y/m/d H:i:s', $now),
            'value' => [
                date('Y/m/d H:i:s', $now),
                $allCount,
            ]
        ];

        //小程序的socket客户端（顾客连接socket的数据）
        $guestCount = $this->wsService->getGuestOnlineCount();
        $guestClient = [
            'name' => date('Y/m/d H:i:s', $now),
            'value' => [
                date('Y/m/d H:i:s', $now),
                $guestCount,
            ]
        ];

        //后台商家socket连接数
        $shopCount = $allCount - $guestCount;
        $shopClient = [
            'name' => date('Y/m/d H:i:s', $now),
            'value' => [
                date('Y/m/d H:i:s', $now),
                $shopCount,
            ]
        ];


        return $this->returnApi('获取成功', [
            'allClient' => $allClient,
            'guestClient' => $guestClient,
            'shopClient' => $shopClient,
        ]);
    }
}
