<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'rms',
    'basePath' => dirname(__DIR__),
    'timeZone' => 'PRC',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => array_merge([
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                //记录定时任务日志
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['refreshStock','statisticsSalePrice','statisticsMenuSale','statisticsDeskUse','statisticsDeskCount'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/crontab.log',
                ]
            ],
        ],
        'db' => $db,
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ], require(__DIR__ . '/components.php')),
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
