<?php
return [
    'socket' => function() {
        return app\base\socket\Socket::createSocketDriver([
            'serverAddress' => getenv('SERVER_ADDRESS'),
            'driverName' => getenv('SOCKET_DRIVER'),
            'registerAddress' => getenv('REGISTER_ADDRESS'),
        ]);
    },
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => getenv('REDIS_HOST'),
        'port' => getenv('REDIS_PORT'),
        'password'=>getenv('REDIS_PASSWORD') == 'null' ? null : getenv('REDIS_PASSWORD')
    ],
    'adminAuthManager' => [
        'class' => 'yii\rbac\DbManager',
        'itemTable' => 'admin_auth_item',
        'itemChildTable' => 'admin_auth_item_child',
        'assignmentTable' => 'admin_auth_assignment',
        'ruleTable' => 'admin_auth_rule',
        'cacheKey' => 'admin_rbac',
    ],
    'distributionAuthManager' => [
        'class' => 'yii\rbac\DbManager',
        'itemTable' => 'distribution_auth_item',
        'itemChildTable' => 'distribution_auth_item_child',
        'assignmentTable' => 'distribution_auth_assignment',
        'ruleTable' => 'distribution_auth_rule',
        'cacheKey' => 'distribution_rbac',
    ],
    'shopAuthManager' => [
        'class' => 'yii\rbac\DbManager',
        'itemTable' => 'shop_auth_item',
        'itemChildTable' => 'shop_auth_item_child',
        'assignmentTable' => 'shop_auth_assignment',
        'ruleTable' => 'shop_auth_rule',
        'cacheKey' => 'shop_rbac',
    ]
];
