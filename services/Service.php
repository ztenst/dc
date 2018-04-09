<?php
namespace app\services;

use Yii;

/**
 * service层基类
 * 业务逻辑的处理放在service层，controller负责调度service来显示数据
 */
class Service extends \yii\base\Component
{
    /**
     * 上下文对象，一般情况都是谁实例化了本类，$context就是谁
     */
    public $context;

    /**
     * 获取模型错误信息
     * @param \yii\db\ActiveRecord $model 模型对象
     * @param string $defaultMsg 默认错误信息
     * @return string
     */
    public function getModelError($model, $defaultMsg)
    {
        if($model->hasErrors()) {
            return current($model->getFirstErrors());
        } else {
            return $defaultMsg;
        }
    }
}
