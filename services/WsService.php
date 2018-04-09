<?php
namespace app\services;

use Yii;
use yii\helpers\Json;
use app\models\ext\ShopShop;

class WsService extends Service
{
    //gateway分组操作前缀标识
    const GROUP_ACTIVE_DESK = 'shopActiveDesk';//以activeDesk信息作为分组的前缀，兼容小程序7.6版本
    const GROUP_DESK = 'shopDesk';//以desk信息作为分组的前缀
    const GROUP_SHOP = 'shop';//以商家信息作为分组的前缀

    //gateway BIND操作标识前缀
    const BIND_SHOP_USER = 'shopUser';//小程序顾客绑定前缀
    const BIND_SHOP_ADMIN = 'shopAdmin';//商家后台职员帐号绑定前缀

    /**
     * 格式化返回数据
     * @param mixed $data 数据
     * @return string 数据的json字符串格式
     */
    private function formatData($data, $type)
    {
        $time = time();
        $dataFormat = [
            'type' => $type,
            'data' => $data,
            'time' => $time,
            'datetime' => date('Y-m-d H:i:s', $time),
        ];
        return Json::encode($dataFormat);
    }

    /**
     * 推送商家后台餐桌详情页信息
     * 前端约定事件type:refreshMenuList
     * @param ShopShop $shop 推送指定商家
     */
    public function pushRefreshDeskInfo(ShopShop $shop)
    {
        $this->sendToShopGroup($shop, '请刷新餐桌详情信息', 'refreshMenuList');
    }

    /**
     * 推送商家后台首页刷新信息
     * 前端约定事件type:refreshIndexList
     * @param ShopShop $shop 推送指定商家
     */
    public function pushRefreshIndex(ShopShop $shop)
    {
        $this->sendToShopGroup($shop, '请刷新首页信息', 'refreshIndexList');
    }


    public function pushRemoveCart($desk_id)
    {
        //$this->sendToShopActiveDesk($active_desk_id, '用户已清空购物车', 'removeUserCart');
        $this->sendToShopDesk($desk_id, '用户已清空购物车', 'removeUserCart');
    }

    public function pushOpenDesk($desk_id)
    {
        $this->sendToShopDesk($desk_id, '用户已经开桌', 'openShopDesk');
    }

    /**
     * 小程序端用户加菜到购物车推送
     * @param $desk_id
     * @param $message
     */
    public function pushAddCart($desk_id, $message){
        //$this->sendToShopActiveDesk($active_desk_id, $message, 'addUserCart');
        $this->sendToShopDesk($desk_id, $message, 'addUserCart');
    }

    /**
     * 小程序段用户提交订单
     * @param $desk_id
     */
    public function pushCommitOrder($desk_id){
        //$this->sendToShopActiveDesk($active_desk_id, '用户已提交订单', 'commitUserOrder');
        $this->sendToShopDesk($desk_id, '用户已提交订单', 'addUserCart');
    }

    /**
     * 推送给小程序前台指定餐桌上的人，让他们重新扫码
     * @param $deskId
     */
    public function pushScanCode($deskId)
    {
        $this->sendToShopDesk($deskId, '请重新扫码', 'scanCode');
    }

    /**
     * 给所有socket链接客户端推送重新扫码scanCode事件
     */
    public function pushScanCodeToAll()
    {
        $this->sendToAll('全部重新扫码', 'scanCode');
    }

    /**
     * 推送商家已确认订单到小程序的指定餐桌
     * @param $deskId
     */
    public function pushShopConfirmOrder($deskId)
    {
        $this->sendToShopDesk($deskId, '商家已确认订单', 'shopConfirmOrder');
    }

    //↑↑↑↑上面是基于分割线下面函数的二次封装和使用↑↑↑↑
    //=============================分割线======================================

    /*
     * 用户加入到开桌组中
     * 绑定当前用户clientId与餐桌信息的关系
     */
    public function joinShopActiveDeskGroup($clientId, $active_desk_id){
        $this->socket->joinGroup($clientId,  self::GROUP_ACTIVE_DESK.$active_desk_id);
    }

    /*
     * 推送消息给开桌组的人（若并桌，并桌餐桌的用户也将收到消息）
     * 绑定clientid与餐桌id的关系
     */
    public function sendToShopActiveDesk($active_desk_id, $data, $type)
    {
        $this->socket->sendToGroup(self::GROUP_ACTIVE_DESK.$active_desk_id,$this->formatData($data, $type));
    }

    /*
     * 用户加入到桌子组
     * 绑定当前用户clientId与餐桌信息的关系
     */
    public function joinShopDeskGroup($clientId, $desk_id){
        $this->socket->joinGroup($clientId,  self::GROUP_DESK.$desk_id);
    }

    /*
     * 推送消息给桌子组的人
     * 绑定clientid与餐桌id的关系
     */
    public function sendToShopDesk($desk_id, $data, $type)
    {
        $this->socket->sendToGroup(self::GROUP_DESK.$desk_id, $this->formatData($data, $type));
    }

    /**
     * 给所有socket客户端连接推送消息
     */
    public function sendToAll($data, $type)
    {
        $this->socket->sendToAll($this->formatData($data, $type));
    }

    /**
     * 加入到该商家组
     * @param string $clientId 当前用户连接socket后得到的clientId
     * @param ShopShop $shop 当前用户所属商家信息
     */
    public function joinShopGroup($clientId, ShopShop $shop)
    {
        $this->socket->joinGroup($clientId, $this->generateShopGroupName($shop));
    }

    /**
     * 发送消息给商家群组
     * @param ShopShop $shop
     * @param mixed $data
     */
    public function sendToShopGroup($shop, $data, $type)
    {
        $this->socket->sendToGroup($this->generateShopGroupName($shop), $this->formatData($data, $type));
    }

    /*
     * 小程序端绑定用户ID和clientId
     */
    public function bindShopUserId($clientId , $userId)
    {
        $this->socket->bindUid($clientId,self::BIND_SHOP_USER.$userId);
        $this->setGuestInfo($userId, []);
        //用于clientId断开时，可以从session中知道对应的uid是哪个
        $this->socket->setSession($clientId, [
            'uid' => $userId,
        ]);
    }

    /**
     * 绑定当前连接id与当前登录商家帐号关系
     */
    public function bindShopAdminId($clientId, $shopAdmin)
    {
        $this->socket->bindUid($clientId, $this->generateShopAdminId($shopAdmin));
        //下面注释是测试用的模拟小程序端连接绑定测试用的，不要开起来
        // $this->setGuestInfo($shopAdmin->id, []);
        // //用于clientId断开时，可以从session中知道对应的uid是哪个
        // $this->socket->setSession($clientId, [
        //     'uid' => $shopAdmin->id,
        // ]);
    }

    /**
     * 发送消息给指定登录的商家帐号
     */
    public function sendToShopAdmin($shopAdmin, $data, $type)
    {
        $this->socket->sendToUid($this->generateShopAdminId($shopAdmin), $this->formatData($data, $type));
    }

    //↑↑↑↑上面是基于分割线下面函数的二次封装和使用↑↑↑↑
    //==============================分割线=====================================

    /**
     * 生成商家帐号uid
     */
    public function generateShopAdminId($shopAdmin)
    {
        return self::BIND_SHOP_ADMIN.$shopAdmin->id;
    }

    /**
     * 获取以商家作为分组的分组名
     */
    private function generateShopGroupName(ShopShop $shop)
    {
        return self::GROUP_SHOP.$shop->id;
    }

    /**
     * 获取socket组件
     * @return Socket
     */
    protected function getSocket()
    {
        return Yii::$app->socket;
    }

    /**
     * 设置小程序顾客在线信息
     * hash的key为self::REDIS_KEY_GUEST_ONLINE_INFO
     * hash的field为用户uid
     * 要获得顾客在线人数时，直接hlen(self::REDIS_KEY_GUEST_ONLINE_INFO)可以获得顾客在线人数
     * 要获得某个顾客的其他信息通过hget(self::REDIS_KEY_GUEST_ONLINE_INFO,顾客uid)得到value值并处理
     * 用户下线后通过hdel删除之
     */
    const REDIS_KEY_GUEST_ONLINE_INFO = 'guestOnlineInfo';

    /**
     * 设置用户信息
     * @param integer $uid 用户uid
     * @param mixed $data 需要存储的数据
     */
    public function setGuestInfo($uid, $data)
    {
        $data = Json::encode($data);
        $this->redis->hset(self::REDIS_KEY_GUEST_ONLINE_INFO, $uid, $data);
    }

    /**
     * 移除指定uid用户信息
     * @param integer $uid 用户uid
     * @return boolean 成功返回true，失败返回false
     */
    public function removeGuestInfo($uid)
    {
        return $this->redis->hdel(self::REDIS_KEY_GUEST_ONLINE_INFO, $uid)>0;
    }

    /**
     * 删除所有在线用户信息的key
     * @return void
     */
    public function removeAllGestInfo()
    {
        return $this->redis->del(self::REDIS_KEY_GUEST_ONLINE_INFO);
    }

    /**
     * 获取顾客在线人数
     * @return integer 返回小程序端顾客在线人数
     */
    public function getGuestOnlineCount()
    {
        return $this->redis->hlen(self::REDIS_KEY_GUEST_ONLINE_INFO);
    }

    protected function getRedis()
    {
        return Yii::$app->redis;
    }
}
