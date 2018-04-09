<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author weibaqiu
 * @version 2017-08-02
 */
class EchartsAsset extends AssetBundle
{
    public $sourcePath = '@bower/echarts';

    public $css = [
    ];
    public $js = [
        'dist/echarts.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',//包含yii专用的jquery等
    ];
}
