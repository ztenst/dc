<?php

namespace api;

use Yii;
/**
 * api module definition class
 * @version 2017-05-18 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'api\controllers';

    /**
    * @string 禁用视图
    */
    public $layout = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        //api子模块
        $this->modules = [
             'canting' => [
                 'class' => 'api\modules\canting\Module'
             ],
             'shop' => [
                 'class' => 'api\modules\shop\Module',
                 'layout' => $this->layout,
             ],
        ];
        Yii::configure(Yii::$app,require(__DIR__.'/config/config.php'));
    }
}
