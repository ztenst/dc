<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
// comment out the following two lines when deployed to production

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

(new \Dotenv\Dotenv(dirname(__DIR__)))->load();

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
