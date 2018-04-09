<?php
namespace app\base;

class ActiveForm extends \yii\bootstrap\ActiveForm
{
    public $fieldClass = 'app\base\ActiveField';

    public $options = ['class'=>'form-horizontal'];

    public $fieldConfig =  [
        'hintOptions' => ['class'=>'help-inline'],
        'inputOptions' => ['class'=>'form-control input-inline input-xlarge'],
    ];
}
