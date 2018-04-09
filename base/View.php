<?php
namespace app\base;

class View extends \yii\web\View
{
    /**
     * 面包屑
     * 该属性值的具体格式需要符合[\yii\widgets\Breadcrumbs::links]的格式
     * 默认值为空数组
     * @var array
     */
    public $breadcrumbs = [];
}
