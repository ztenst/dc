<?php
namespace api\modules\shop\controllers;

use Yii;
use \Exception;

class WsController extends \api\modules\shop\base\Controller
{
    /**
     * 初次建立连接进行绑定接口
     * POST参数：
     * - clientId: 客户端与socket服务建立连接后获得的clientId
     */
    public function actionBind()
    {
        $clientId = Yii::$app->request->post('clientId');
        if(!$clientId) {
            throw new Exception('请求方式或参数错误');
        }
        try {
            //绑定clientId和商家帐号uid
            $this->wsService->bindShopAdminId($clientId, $this->currentShopAdmin);
            //将clientid加入该所属商家群组
            $this->wsService->joinShopGroup($clientId, $this->currentShop);
            //推个消息表示成功
            // $this->wsService->sendToShopAdmin($this->currentShopAdmin, '连接成功');
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
        return '连接成功';
    }

    public function actionCs()
    {
        $this->wsService->sendToShopGroup($this->currentShop, '请刷新餐桌信息', 'refreshMenuList');
        $this->wsService->sendToShopGroup($this->currentShop, '请刷新餐桌信息', 'refreshIndexList');
        return '已推送';
    }
}
