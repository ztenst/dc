<?php
namespace app\assets\plugins;

use yii\web\AssetBundle;

class ToastrNotificationAsset extends AssetBundle
{
    public $basePath = '@webroot/web/metronic47';
    public $baseUrl = '@web/metronic47';
    public $cssOptions = [];
    public $css = [
        'global/plugins/bootstrap-toastr/toastr.min.css',
    ];
    public $js = [
        'global/plugins/bootstrap-toastr/toastr.min.js',
    ];
    public $depends = [

    ];
}
