<?php
namespace api\modules\shop\controllers;

use Yii;
use api\modules\shop\base\Controller;
use app\models\ext\ShopAdmin;
use api\modules\shop\models\LoginForm;
use \Exception;

class AdminController extends Controller
{
    private $_adminService;

    public function getAdminService()
    {
        if($this->_adminService===null) {
            $this->_adminService = new \api\modules\shop\services\AdminService(['context'=>$this]);
        }
        return $this->_adminService;
    }
    /**
     * 商家登录
     * POST请求参数：
     * - username:用户名
     * - password 密码
     */
    public function actionLogin()
    {
        $loginForm = new LoginForm();
        if(Yii::$app->request->getIsPost() && $loginForm->load($_POST, '')) {
            if(!Yii::$app->user->getIsGuest() || $loginForm->login()) {
                return '登录成功';
            } else {
                $error = $loginForm->hasErrors() ? current($loginForm->getFirstErrors()) : '登录验证失败，请稍候重试';
                throw new Exception($error);
            }
        } else {
            throw new Exception('请求方式或参数错误');
        }
    }

    /**
     * 商家帐号退出接口
     * GET请求
     */
    public function actionLogout()
    {
        if(Yii::$app->user->logout()) {
            return '退出成功';
        } else {
            throw new Exception('退出失败，请重新尝试');
        }
    }

    /**
     * 获取当前登录用户信息
     */
    public function actionUserInfo()
    {

        $user = Yii::$app->user;
        $identity = $user->identity;
        $isLogin = !$user->getIsGuest();
        $settings = [];
        if($isLogin) {
            $settings = [
                'printerServer' => $this->currentShop->shopSettings->printerServer,
                'kitchenPrinter' => $this->currentShop->shopSettings->kitchenPrinter,
            ];
        }
        return [
            'isLogin' => $isLogin,
            'account' => $isLogin ?  $identity->account : '',
            'username' => $isLogin ?  $identity->username : '',
            'shopname' => $isLogin ?  $identity->shop->name : '',
            'qiniuDomain' => $isLogin ? Yii::$app->storage->domain : '',
            'socketDomain' => $isLogin ? Yii::$app->socket->serverAddress : '',
            'settings' => $settings,
        ];
    }

    /**
     * 修改密码
     * POST参数：
     * - password: 新密码
     */
    public function actionPwdUpdate()
    {
        $password = Yii::$app->request->post('password');
        if(!$password) {
            throw new Exception('密码不能为空');
        }
        $shopAdmin = $this->currentShopAdmin;
        $shopAdmin->newPassword = $shopAdmin->repeatPassword = $password;
        if(!$shopAdmin->save()) {
            $msg = $shopAdmin->hasErrors() ? current($shopAdmin->getFirstErrors()) : '保存失败';
            throw new Exception($msg);
        }
        return '保存成功';
    }

    /**
     * 职员列表接口
     */
    public function actionList()
    {
        $staffs = ShopAdmin::find()->shop($this->currentShopId)->andWhere(['role'=>ShopAdmin::ROLE_STAFF])->all();
        $data = [];
        foreach($staffs as $staff) {
            $data[] = [
                'id' => $staff->id,
                'account' => $staff->account,
                'lastLogin' => $staff->last_login_time ? date('m-d H:i', $staff->last_login_time): '-',
            ];
        }
        return $data;
    }

    /**
     * 新增\编辑员工资料
     * POST请求参数：
     * - id: 帐号id
     * - account: 账号名
     * - newPassword: 新密码
     */
    public function actionStaffEdit($id=0)
    {
        if(Yii::$app->request->isPost) {
            if(!($post = Yii::$app->request->post())) {
                throw new Exception('参数错误');
            }
            $admin = $this->adminService->saveStaff($post);
            $msg = '保存成功';
        }elseif($id>0) {
            $admin = ShopAdmin::find()->shop($this->currentShopId)->andWhere(['id'=>$id])->one();
            if(!$admin) {
                throw new Exception('帐号不存在');
            }
            $msg = '获取成功';
        } else {
            throw new Exception('请求方式或参数错误');
        }
        return [
            'msg' => $msg,
            'editInfo' => [
                'id' => $admin->id,
                'account' => $admin->account,
            ]
        ];
    }

    /**
     * 删除指定职员账号
     * POST参数
     * - id: 职员帐号id
     */
    public function actionStaffDelete()
    {
        $id = Yii::$app->request->post('id');
        if(!$id) {
            throw new Exception('请求方式或参数错误');
        }
        $this->adminService->deleteStaff($id);
        return '删除成功';
    }
}
