<?php

namespace api\modules\canting;

use Yii;

class Module extends \yii\base\Module
{
    /*
     *  控制器命名空间
     */
    public $controllerNamespace = 'api\modules\canting\controllers';

    public function init()
    {
        parent::init();
        Yii::configure(Yii::$app, [
            'components' => [
                'request' => [
                    'class' => 'yii\web\Request',
                    'parsers' => [
                        'application/json' => 'yii\web\JsonParser',
                    ]
                ],
                'user' => [
                    'class' => 'api\modules\canting\components\User',
                    'keyPrefix' => 'rms_applet_user'
                ],
                'wxPay' => [
                    'class' => 'api\modules\canting\components\WxPay',
                ],
                'applet' => [
                    'class' => 'api\modules\canting\components\Applet',
                    'appid' => getenv('APPLET_APPID'),
                    'secret' => getenv('APPLET_SECRET')
                ]
            ],
        ]);
    }
}


