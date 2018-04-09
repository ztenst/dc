<?php
namespace app\modules\admin\assets;

use yii\web\AssetBundle;

class MetronicAsset extends AssetBundle
{
    public $basePath = '@webroot/web/metronic47';
    public $baseUrl = '@web/metronic47';

    public $css = [
        // 'http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all',
        //BEGIN GLOBAL MANDATORY STYLES
        'global/plugins/font-awesome/css/font-awesome.min.css',
        'global/plugins/simple-line-icons/simple-line-icons.min.css',
        'global/plugins/bootstrap/css/bootstrap.min.css',
        'global/plugins/bootstrap-switch/css/bootstrap-switch.min.css',
        //END GLOBAL MANDATORY STYLES

        //BEGIN PAGE LEVEL PLUGINS
        //END PAGE LEVEL PLUGINS

        //BEGIN THEME GLOBAL STYLES
        'global/css/components.min.css',
        'global/css/plugins.min.css',
        //END THEME GLOBAL STYLES

    ];

    public $js = [
        //BEGIN CORE PLUGINS
        // 'global/plugins/jquery.min.js',
        'global/plugins/bootstrap/js/bootstrap.min.js',
        'global/plugins/js.cookie.min.js',
        'global/plugins/jquery-slimscroll/jquery.slimscroll.min.js',
        'global/plugins/jquery.blockui.min.js',
        'global/plugins/bootstrap-switch/js/bootstrap-switch.min.js',
        //END CORE PLUGINS

        //BEGIN THEME GLOBAL SCRIPTS
        'global/scripts/app.min.js',
        //END THEME GLOBAL SCRIPTS

        // BEGIN THEME LAYOUT SCRIPTS
        'layouts/layout/scripts/layout.min.js',
        'layouts/layout/scripts/demo.min.js',
        //END THEME LAYOUT SCRIPTS
    ];

    public $depends = [
        'yii\web\YiiAsset',//包含yii专用的jquery等
    ];
}
