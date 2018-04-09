<?php
namespace app\commands;

use Yii;
use app\services\WsService;
use yii\console\Controller;
use yii\helpers\Console;
use app\helpers\Timestamp;
use \Exception;

class SocketController extends Controller
{
    private $_wsService;

    public function getWsService()
    {
        if($this->_wsService===null) {
            $this->_wsService = new WsService(['context'=>$this]);
        }
        return $this->_wsService;
    }
    /**
     * 重新刷新redis存储的在线顾客信息（注意，这会人工将小程序用户强制退出socket）
     * 运行时间久了，可能redis存储的数据量与实际使用的用户量不匹配，所以需要通过该脚本
     * 命令进行重新统计
     */
    public function actionRefreshGuestOnlineCount()
    {
        //1. 通知所有在线已经连上socket的用户，让小程序响应scanCode事件退回到扫码首页
        $this->wsService->pushScanCodeToAll();
        //2. 清除redis存储的在线顾客信息
        $this->wsService->removeAllGestInfo();
    }
}
