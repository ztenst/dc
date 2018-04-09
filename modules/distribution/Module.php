<?php

namespace app\modules\distribution;

use Yii;

/**
 * distribution module definition class
 * @version 2017-05-12 */
class Module extends \yii\base\Module
{
    use \app\traits\SideMenu;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\distribution\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::configure(Yii::$app, [
            'homeUrl' => ['/distribution/default/index'],
            'components' => [
                'user' => [
                    'class' => 'yii\web\User',
                    'identityClass' => 'app\models\ext\DistributionAdmin',
                    'enableAutoLogin' => true,
                    //以下两项必须同时修改，否则其他后台会窜号
                    'identityCookie' => ['name' => '_distribution', 'httpOnly' => true],
                    'idParam' => '_distribution_id',
                    'loginUrl' => ['/distribution/default/login'],
                    'returnUrlParam' => '_distribution_returnUrl',//配置此项，以却别每个模块登录完成后跳转的地址，否则会导致其他模块登录完成后跳转到该模块中设置的returnUrl页面
                ],

            ]
        ]);
        //注册该模块的组件
        $this->setComponents([
            'errorHandler' => [
                'class' => 'yii\web\ErrorHandler',
                'errorAction' => 'destribution/default/error'
            ],
        ]);
        //注册该模块错误处理器
        $this->errorHandler->register();
        parent::init();
    }

    /**
	 * 重写配置菜单
	 * @return array
	 */
	public function getMenu() {
		$route = Yii::$app->controller->route;
		$menu = [
			["label" => "首页", "url" => ["/distribution/default/index"], "icon" => "icon-settings"],
            ['label' => '商家管理', 'icon'=>'fa fa-archive', 'items'=>[
                ['label'=>'店铺管理', 'url'=>['shop/list']],
                ['label'=>'帐号管理', 'url'=>['shop-admin/list']]
            ]],
            ['label' => '管理员设置','url'=>['/distribution/admin/index'],'icon'=>'fa fa-user',
            'active'=>$this->setRouteActive(['distribution/admin/*'])]
		];
		return $menu;
	}
}
