<?php

namespace api\modules\shop;

use Yii;

/**
 * shop module definition class
 * @version 2017-05-22 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'api\modules\shop\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::configure(Yii::$app, [
            'components' => [
                'user' => [
                    'class' => 'yii\web\User',
                    'identityClass' => 'app\models\ext\ShopAdmin',
                    'enableAutoLogin' => true,
                    'identityCookie' => ['name' => '_shop', 'httpOnly' => true],
                    'idParam' => '_shop_id',
                    // 'loginUrl' => ['/admin/default/login'],
                    // 'returnUrlParam' => '_admin_returnUrl',//配置此项，以区别每个模块登录完成后跳转的地址，否则会导致其他模块登录完成后跳转到该模块中设置的returnUrl页面
                    'accessChecker' => Yii::$app->shopAuthManager, //设置该模块的authManager来检查权限
                ],
            ]
        ]);
    }
}
