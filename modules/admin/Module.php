<?php

namespace app\modules\admin;

use Yii;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    use \app\traits\SideMenu;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        //注册该模块的组件
        $this->setComponents([
            'errorHandler' => [
                'class' => 'yii\web\ErrorHandler',
                'errorAction' => 'admin/default/error'
            ],
        ]);
        //注册该模块错误处理器
        $this->errorHandler->register();

        Yii::configure(Yii::$app, [
            'homeUrl' => ['/admin/default/index'],
            'components' => [
                'user' => [
                    'class' => 'yii\web\User',
                    'identityClass' => 'app\models\ext\AdminAdmin',
                    'enableAutoLogin' => true,
                    'identityCookie' => ['name' => '_admin', 'httpOnly' => true],
                    'idParam' => '_admin_id',
                    'loginUrl' => ['/admin/default/login'],
                    'returnUrlParam' => '_admin_returnUrl',//配置此项，以区别每个模块登录完成后跳转的地址，否则会导致其他模块登录完成后跳转到该模块中设置的returnUrl页面
                    'accessChecker' => Yii::$app->adminAuthManager, //设置该模块的authManager来检查权限
                ],
            ]
        ]);
        parent::init();
    }

    /**
	 * 重写配置菜单
	 * @return array
	 */
	public function getMenu() {
		$route = Yii::$app->controller->route;
		$menu = [
			["label" => "首页", "url" => ["/admin"], "icon" => "icon-settings"],
            ['label' => '分销商管理', 'url'=>['/admin/distribution/admin-list'], 'icon'=>'fa fa-exchange', 'active'=>$this->setRouteActive(['admin/distribution/*'])],
            ['label' => '区域管理', 'url'=>['/admin/area/list'],'icon'=>'fa fa-map'],
            ['label' => '监控管理', 'url'=>['/admin/monitor/index'],'icon'=>'fa fa-cloud'],
			["label" => "后台管理", "icon" => "icon-settings", 'items'=>[
                ['label' => '帐号管理', 'url'=>['/admin/admin/list'], "icon" => "icon-settings", 'active'=>$this->setRouteActive(['admin/admin/*'])],
                ['label' => '权限管理', 'url'=>['/admin/rbac/list'], 'icon'=>'icon-settings', 'active'=>$this->setRouteActive(['admin/rbac/*'])]
            ]],
			// ['label' => '服务部分', 'options' => ['class' => 'heading']],
			// ["label" => "接入流程表", "icon" => "icon-settings", "items" => [
			// 	["label" => "部署管理", "url" => ["/admin/access-flow/list?AccessFlow[status]=0"], "icon" => "icon-settings", 'active' => $this->setRouteActive(['admin/access-flow/*'])],
            //
			// ]],
			// ["label" => "日程管理", "icon" => "icon-settings", "items" => [
			// 	["label" => "日程列表", "url" => ["/admin/record/list"], "icon" => "icon-settings", 'active' => $this->setRouteActive(['admin/record/*'])],
			// ]],
		];
		return $menu;
	}
}
