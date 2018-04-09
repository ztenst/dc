<?php
namespace app\modules\admin\assets;

use yii\web\AssetBundle;

class MetronicErrorAsset extends AssetBundle
{
    public $basePath = '@webroot/web/metronic47';
    public $baseUrl = '@web/metronic47';

    public $css = [
        'pages/css/error.min.css',

    ];

    public $js = [
    ];

    public $depends = [
        // 'app\modules\admin\assets\MetronicAsset',
    ];
}
