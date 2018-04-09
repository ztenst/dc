<?php

namespace api\modules\shop\base;

use Yii;
use \Exception;

/**
 * shop模块控制器的基类
 * @version 2017-05-22 */
class Controller extends \api\base\Controller
{
    /**
     * websocket相关业务逻辑层对象
     * @var OrderService
     */
    private $_wsService;

    /**
     * websocket相关业务逻辑层对象
     * @return OrderService
     */
    public function getWsService()
    {
        if($this->_wsService===null) {
            return $this->_wsService = new \app\services\WsService();
        }
        return $this->_wsService;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return array_merge($behaviors, [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'denyCallback' => function($rule, $action) {
                    throw new Exception('权限不足');
                },
                'rules' => array_merge([
                    [
                        'allow' => true,
                        'controllers' => ['api/shop/admin'],
                        'actions' => ['login', 'logout', 'user-info']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],$this->rbacRules())
            ]
        ]);
    }

    /**
     * rbacRules规则
     * @return array
     */
    public function rbacRules()
    {
        return [];
    }

    /**
     * 获取当前登录商家帐号信息
     * @return ShopAdmin
     */
    final public function getCurrentShopAdmin()
    {
        return Yii::$app->user->identity;
    }

    /**
     * 获取当前登录商家的id
     * @return interger
     */
    final public function getCurrentShopId()
    {
        return $this->getCurrentShopAdmin()->shop_id;
    }

    /**
     * 获取当前商家数据
     * @return ShopShop
     */
    final public function getCurrentShop()
    {
        return $this->getCurrentShopAdmin()->shop;
    }
}
