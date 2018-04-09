<?php

$params = require(__DIR__ . '/params.php');
$aliases = require(__DIR__ . '/aliases.php');

$config = [
    'id' => 'rms',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'PRC',
    'aliases' => $aliases,
    'language' => 'zh-CN',
    'aliases' => [
        '@api' => '@app/modules/api'
    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
            'layout' => 'layout',//布局页面
        ],
        'distribution' => [
            'class' => 'app\modules\distribution\Module',
            'layout' => 'layout',//布局页面
        ],
        'api' => [
           'class' => 'api\Module',
        ]
    ],
    'components' => array_merge([
        'storage' => function(){
            return app\base\storage\Storage::createFileUploadDriver([
                'driverName' => getenv('STORAGE_DRIVER'),
                'domain' => getenv('STORAGE_DOMAIN'),
                'accessToken' => getenv('STORAGE_ACCESS'),
                'secretToken' => getenv('STORAGE_SECRET'),
                'bucket' => getenv('STORAGE_BUCKET'),
            ]);
        },
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'AuftMGRCirVLN1KcEM7uXfh-BgMRGdQQ',
        ],
        'cache' => [
            'class' => 'yii\caching\MemCache',
            'useMemcached' => getenv('MEMCACHE_DRIVER')=== 'memcached' ? true : false,
            'servers' => [
                [
                    'host' => getenv('MEMCACHE_HOST'),
                    'port' => getenv('MEMCACHE_PORT')
                ]
            ]
        ],
        'sms' => [
            'class' => 'app\base\sms\Sms',
            'default' => getenv('SMS_DEFAULT'),
            'config' => [
                'chuanglan' => [
                    'account' => getenv('CHUANGLAN_ACCOUNT'),
                    'pswd' => getenv('CHUANGLAN_PSWD')
                ]
            ]
        ],
//        'errorHandler' => [
//            'errorAction' => 'site/error',
//        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
            ],
        ],
        //有一个缺点，如果在module层级中重新配置view组件，则yii debug toolbar就不显示
        //了，因为原理是是基于之前view组件设置的事件
        'view' => '\app\base\View',
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'admin' => 'admin/default/index',
                '<m:\w+>/<c:\w+>/<a:\w+>' => '<m>/<c>/<a>',
            ],
        ],
    ], require(__DIR__ . '/components.php')),
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1','192.168.10.1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1','192.168.10.1'],
        'generators' => [
            //自定义模块模板
            'module' => [
                'class' => 'app\gii\generators\module\Generator',
                'template' => '自定义模板',
                'templates' => [
                    '自定义模板' => '@app/gii/generators/module/templates'
                ],
            ],
            'model' => [
                'class' => 'app\gii\generators\model\Generator',
                'baseClass' => 'app\base\ActiveRecord',
                'queryBaseClass' => 'app\base\ActiveQuery',
                'template' => '自定义模板',
                'templates' => [
                    '自定义模板' => '@app/gii/generators/model/templates',
                ]
            ],
        ]
    ];
}

return $config;
