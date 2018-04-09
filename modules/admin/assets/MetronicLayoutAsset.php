<?php
namespace app\modules\admin\assets;

use yii\web\AssetBundle;

class MetronicLayoutAsset extends AssetBundle
{
    public $basePath = '@webroot/web/metronic47';
    public $baseUrl = '@web/metronic47';

    public $css = [

        //BEGIN THEME LAYOUT STYLES
        'layouts/layout/css/layout.min.css',
        'layouts/layout/css/themes/grey.min.css',
        'layouts/layout/css/custom.min.css',
        //END THEME LAYOUT STYLES

    ];

    public $js = [
    ];

    public $depends = [
    ];
}
