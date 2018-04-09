<?php
return [
    '@GatewayWorker' => strpos(strtoupper(PHP_OS), 'WIN')===false ? '@vendor/workerman/gateway-worker/src' : '@vendor/workerman-for-win/gateway-worker-for-win/src',
    '@Workerman' => strpos(strtoupper(PHP_OS), 'WIN')===false ? '@vendor/workerman/workerman' : '@vendor/workerman-for-win/workerman-for-win',
];
