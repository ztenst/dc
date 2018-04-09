<?php
namespace app\modules\admin\assets;

use yii\web\AssetBundle;

class MetronicLoginPageAsset extends AssetBundle
{
    public $basePath = '@webroot/web/metronic47';
    public $baseUrl = '@web/metronic47';

    public $css = [
        // BEGIN PAGE LEVEL PLUGINS
        'global/plugins/select2/css/select2.min.css',
        'global/plugins/select2/css/select2-bootstrap.min.css',
        // END PAGE LEVEL PLUGINS

        // BEGIN PAGE LEVEL STYLES
        'pages/css/login-5.min.css',
        // END PAGE LEVEL STYLES

    ];

    public $js = [
        // BEGIN PAGE LEVEL PLUGINS
        'global/plugins/jquery-validation/js/jquery.validate.min.js',
        'global/plugins/jquery-validation/js/additional-methods.min.js',
        'global/plugins/select2/js/select2.full.min.js',
        'global/plugins/backstretch/jquery.backstretch.min.js',
        // END PAGE LEVEL PLUGINS

        // BEGIN THEME GLOBAL SCRIPTS
        'global/scripts/app.min.js',
        // END THEME GLOBAL SCRIPTS

        // BEGIN PAGE LEVEL SCRIPTS
        'custom/admin/login.js',
        // END PAGE LEVEL SCRIPTS
    ];

    public $depends = [
        'app\modules\admin\assets\MetronicAsset',
    ];
}
