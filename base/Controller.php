<?php
namespace app\base;

use Yii;

class Controller extends \yii\web\Controller
{
    /**
     * @var 是否开启csrf验证，默认改为关闭
     */
    public $enableCsrfValidation = false;
}
