<?php
/**
 * 七牛前端资源包
 */
namespace app\base\storage\qiniu;

use yii\web\AssetBundle;
use yii\helpers\ArrayHelper;

/**
 * @author weibaqiu
 * @since 2017-05-15
 */
class QiniuAsset extends AssetBundle
{
    public $sourcePath = '@bower/qiniu';

    public $css = [
    ];
    public $js = [
        'src/qiniu.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',//包含yii专用的jquery等
        'app\assets\PluploadAsset',
    ];
}
