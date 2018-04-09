<?php
/**
 * 七牛前端资源包配套upload包
 */
namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author weibaqiu
 * @since 2017-05-15
 */
class PluploadAsset extends AssetBundle
{
    public $sourcePath = '@bower/plupload';

    public $css = [
    ];
    public $js = [
        'js/plupload.full.min.js',
    ];
    public $depends = [
    ];
}
