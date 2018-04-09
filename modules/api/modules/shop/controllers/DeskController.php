<?php
namespace api\modules\shop\controllers;

use Yii;
use Exception;
use api\modules\shop\base\Controller;
use app\helpers\QRcode;
use yii\helpers\Url;
use yii\helpers\Json;
use app\models\ext\ShopDesk;
use app\models\ext\ShopMenu;
use app\models\ext\ShopMenuCate;
use app\models\ext\UserOrderMenu;
use app\models\ext\UserOrder;
use yii\helpers\ArrayHelper;

class DeskController extends Controller
{
    /**
     * 餐桌相关业务逻辑层对象
     * @var OrderService
     */
    private $_orderService;
    /**
     * 餐桌相关业务逻辑层对象
     * @var OrderService
     */
    private $_deskService;

    /**
     * 餐桌相关业务逻辑层对象
     * @return OrderService
     */
    public function getOrderService()
    {
        if($this->_orderService===null) {
            return $this->_orderService = new \api\modules\shop\services\OrderService(['context'=>$this]);
        }
        return $this->_orderService;
    }

    /**
     * 餐桌相关业务逻辑层对象
     * @return OrderService
     */
    public function getDeskService()
    {
        if($this->_deskService===null) {
            return $this->_deskService = new \api\modules\shop\services\DeskService(['context'=>$this]);
        }
        return $this->_deskService;
    }

    /**
     * 商家首页餐台管理弹出框|获取当前登录商家的餐桌列表
     */
    public function actionDesk()
    {
        $desks = ShopDesk::find()->select(['id','number'])->shop($this->currentShopId)->all();
        $data = ArrayHelper::toArray($desks, ['id','number']);
        return $data;
    }

    /**
     * 商家首页餐台管理弹出框|修改餐桌信息
     * POST请求参数：
     * - id:餐桌id（可选，修改数据时必须）
     * - number:餐桌桌号（可选），前台设计导致该餐桌号添加时暂为空，需要修改时填写
     */
    public function actionEdit()
    {
        if(Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $id = ArrayHelper::remove($post, 'id', null);
            $desk = $this->loadShopDesk($id);

            if((!$post || $desk->load($post, '')) && $desk->save()) {
                $this->wsService->pushRefreshIndex($this->currentShop);
                return [
                    'id' => $desk->id,
                    'msg' => '保存成功',
                ];
            } else {
                throw new Exception($this->orderService->getModelError($desk, '保存失败，请稍后重试'));
            }
        } else {
            throw new Exception('请求方式或参数错误');
        }
    }

    /**
     * 餐桌详情页|餐桌基本信息（不变信息）
     */
    public function actionDeskInfo($id)
    {
        //餐桌相关信息
        $desk = ShopDesk::find()->where(['id'=>$id])->shop($this->currentShopId)->with(['activeInfo'])->one();
        if(!$desk) {
            throw new Exception('餐桌不存在');
        }
        $users = [];
        $order = null;
        if($activeInfo = $desk->activeInfo) {
            if($order = $activeInfo->getOrder()->with('users')->one()) {
                foreach($order->users as $user) {
                    $users[$user->id] = [
                        'id' => $user->id,
                        'username' => $user->nickname,
                        'avatar' => $user->getAvatar(),
                        'xiadan' => $order->user_id==$user->id,
                    ];
                }
            }
        }
        $isClear = $desk->isClear;

        //点单相关信息
        //注意原价和折后价的逻辑，原价始终可以算出来正确的价格。折后价是任意修改后的结果，
        //所以折后价直接调取订单字段total_price
        $menuInfo = [
            'list' => [],
            'menuItemCount' => 0,
            'originalPrice' => 0,
            'orderId' => $order ? $order->id : 0,
        ];
        $menuCates = [];
        $needConfirm = true;//是否需要确认点单按钮
        if(!$isClear && $activeInfo && ($order = $activeInfo->getOrder()->with(['menus'=>function($query) {
            $query->andWhere(['is_cancel'=>UserOrderMenu::CANCEL_NO]);
        }])->one())) {
            $menuInfo['menuItemCount'] = count($order->menus);
            foreach($order->menus as $userOrderMenu) {
                $menuCates[$userOrderMenu->add_no] = [
                    'addNo'=>$userOrderMenu->add_no,
                    'name'=>$userOrderMenu->add_no>1 ? '加菜'.($userOrderMenu->add_no-1) : '点餐',
                ];
                $attributes = [];
                foreach($userOrderMenu->menu_attr_info as $item) {
                    $attributes[] = $item['value'];
                }
                $menuInfo['list'][] = [
                    'order_menu_id' => $userOrderMenu->id,
                    'name' => $userOrderMenu->menu_name,
                    'number' => $userOrderMenu->menu_num,
                    'price' => $userOrderMenu->menu_price,
                    'totalPrice' => $userOrderMenu->totalPrice,
                    'attributes' => (array)$attributes,
                    'addNo' => $userOrderMenu->add_no,
                    'isConfirm' => (bool)$userOrderMenu->is_confirm,
                ];
                $needConfirm &= boolval($userOrderMenu->is_confirm);
                if($userOrderMenu->is_confirm) {
                    $menuInfo['originalPrice'] += $userOrderMenu->totalPrice;
                }
            }
        }
        $menuInfo['discountPrice'] = $order ? $order->total_price : 0;

        $deskInfo = [
            'needConfirm' => !$needConfirm,
            'number' => $desk->number,
            'peopleNumber' => $activeInfo ? $activeInfo->people_num : 0,
            'openTime' => $activeInfo ? date('Y-m-d H:i:s', $activeInfo->created) : '-',
            'orderUsers' => array_values($users),
            'qrCode' => Url::to(['desk-qrcode','deskId'=>$desk->id], true),
            'qrCodeDownload' => Url::to(['desk-qrcode','deskId'=>$desk->id,'download'=>1], true),
        ];

        return [
            'deskInfo' => $deskInfo,
            'menuCates' => array_values($menuCates),
            'menuInfo' => $menuInfo,
        ];
    }

    /**
     * 商家首页餐台管理弹出框|删除餐桌
     */
    public function actionDelete()
    {
        if(Yii::$app->request->isPost && $id=Yii::$app->request->post('id', 0)) {
            $this->deskService->deleteDesk($id);
            $this->wsService->pushRefreshIndex($this->currentShop);
            return '删除成功';
        } else {
            throw new Exception('请求方式或参数错误');
        }
    }

    /**
     * 加载获取餐桌模型对象
     * @param  integer $id 餐桌id，编辑时使用
     * @throws Exception 找不到帐号时抛出异常
     * @return ShopDesk 餐桌对象
     */
    private function loadShopDesk($id=null)
    {
        if($id!==null) {
            if($id<=0 || ($model=ShopDesk::find()->shop($this->currentShopId)->andWhere(['id'=>$id])->one())===null) {
                throw new Exception('该数据不存在');
            }
            $model->scenario = ShopDesk::SCENARIO_SHOP_EDIT;//商家编辑场景
        } else {
            $model = new ShopDesk([
                'shop_id' => Yii::$app->user->identity->shop_id,
            ]);
            $model->scenario = ShopDesk::SCENARIO_SHOP_CREATE;//商家创建场景
        }
        return $model->loadDefaultValues();
    }

    /**
     * 餐桌详情页|餐桌二维码图片
     * 小程序里与传统h5页面做法不一样，传统做法中二维码存储了带有参数的网址信息，用户扫描后就会跳转过去。
     * 而小程序里通过调用扫一扫功能之后，是直接获取二维码中存储的参数，需要前端将这些扫到的参数发请求到后端去。
     * 让前端将获取到的参数对象直接转发送到开桌的接口即可。
     */
    public function actionDeskQrcode($deskId, $download=0)
    {
        $desk = $this->loadShopDesk($deskId);
        $size = 10;
        $errorCorrectionLevel = 'L';
        $data = Url::toRoute(['/api/canting/auth/scode','shop_id' => $desk->shop_id,'desk_id' => $desk->id], true);
        // $data = Json::encode([
        //     'shop_id' => $desk->shop_id,
        //     'desk_id' => $desk->id,
        // ]);
        if($download) {
            header("Content-Type: application/force-download");
            header('Content-Disposition: attachment; filename="餐桌'.$desk->number.'.png"');
        }
        QRcode::png($data, false, $errorCorrectionLevel, $size, true);die;
    }

    /**
    * 餐桌详情页|获取当前订单价格
     */
    public function actionGetPrice()
    {
        $orderId = Yii::$app->request->post('order_id', 0);
        if(!$orderId) {
            throw new Exception('请求方式或参数错误');
        }

        if(!($order = UserOrder::find()->shop($this->currentShopId)->andWhere(['id'=>$orderId])->one())) {
            throw new Exception('订单数据不存在');
        }

        return $order->total_price;
    }

    /**
     * 餐桌详情页右侧|菜单列表
     */
    public function actionMenuList($kw='', $cid=0)
    {
        $menuCateData = ShopMenuCate::find()->shop($this->currentShopId)
                                            ->with(['menus'=>function($menuQuery) use($kw){
                                                $menuQuery->status()
                                                        ->andFilterWhere(['like','name',$kw.'%', false]);
                                            }])
                                            ->all();
        $list = $menuList = [];
        foreach($menuCateData as $menuCate) {
            foreach($menuCate->menus as $menu) {
                $attributes = $menu->getConfigField('attrs');
                //在尺寸值后面加括号标明价格
                // foreach($attributes['sizeAttr']['attrValues'] as $k=>$value) {
                //     $attributes['sizeAttr']['attrValues'][$k]['name'] = $value['name'].'('.$value['price'].'元/'.$menu->unit->name.')';
                // }
                $attributes['sizeAttr'] = [$attributes['sizeAttr']];
                $menuList[] = [
                    'id' => $menu->id,
                    'cid' => $menuCate->id,
                    'name' => $menu->name,
                    'attributes' => $attributes,
                    'price' => '￥'.$menu->price.($menu->unit?'/'.$menu->unit->name:''),
                    'needSelect' => $menu->getIsNeedSelectAttrs(),
                ];
            }
            $menuCates[] = [
                'id' => $menuCate->id,
                'name' => $menuCate->name,
            ];
        }
        return [
            'menuCate' => $menuCates,
            'menuList' => $menuList,
        ];
    }

    /**
     * 餐桌详情页右侧|商家提交菜品到左侧订单中
     * POST请求参数：
     * order_id: 订单id
     * menu: 已点菜品，json字符串格式：[{id:菜品id, num:3, attrs:{"口味":"麻辣"}}]
     */
    public function actionSubmitMenu()
    {
        $post = Yii::$app->request->post();
        $orderId = ArrayHelper::remove($post, 'order_id');
        $menuInfo = ArrayHelper::getValue($post, 'menu');
        $menuInfo = Json::decode($menuInfo);
        if($orderId===null || !is_array($menuInfo)) {
            throw new Exception('参数错误');
        }
        if($orderId<=0) {
            throw new Exception('请确认餐桌是否开桌或订单是否提交');
        }
        if(!$menuInfo) {
            throw new Exception('请选择菜品');
        }
        $return = $this->orderService->submitMenu($orderId, $menuInfo);
        // 发个推送更新列表
        $this->wsService->pushRefreshDeskInfo($this->currentShop);
        return '添加成功';
    }

    /**
     * 餐桌详情页|清桌接口
     * POST请求参数：
     * - desk_id: 餐桌id
     */
    public function actionClear()
    {
        $deskId = Yii::$app->request->post('desk_id');
        if($deskId===null) {
            throw new Exception('请求方式或参数错误');
        }
        $desk = $this->deskService->clearDesk($deskId);
        $this->wsService->pushRefreshDeskInfo($this->currentShop);
        $this->wsService->pushRefreshIndex($this->currentShop);
        $this->wsService->pushScanCode($desk->id);
        return '操作成功';
    }
}
