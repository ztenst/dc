<?php
namespace app\gii\generators\module;

use yii\gii\CodeFile;
use yii\helpers\Html;
use Yii;
use yii\helpers\StringHelper;

class Generator extends \yii\gii\generators\module\Generator
{
    public function getName()
    {
        return '模块生成器';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return '该模块生成器不是Yii自带的，若需要修改直接编辑' . __FILE__;
    }

    public function attributeLabels()
    {
        return [
            'moduleID' => '模块id（会自动生成）',
            'moduleClass' => '模块类名（命名空间形式）',
        ];
    }

    public function getBaseNamespace()
    {
        return substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\')) . '\base';
    }

    public function generate()
    {
        $files = parent::generate();

        $modulePath = $this->getModulePath();

        //模块的基类控制器
        $files[] = new CodeFile(
            $modulePath . '/base/Controller.php',
            $this->render("base/controller.php")
        );

        return $files;
    }
}
