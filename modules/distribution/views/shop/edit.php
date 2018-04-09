<?php
$this->title = '商家编辑';
$this->breadcrumbs = [
    ['label'=>'商家列表','url'=>['list']],
    ['label'=>$this->title]
];
use app\base\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->storage->widgetOptions = [
    'uptoken_url' => Url::toRoute(['/distribution/api/get-uptoken']),
    'width' => 400,
    'height' => 300,
];

$form = ActiveForm::begin();
 ?>

 <?=$form->field($shop, 'city[name]')->staticControl(); ?>
 <?=$form->field($shop, 'name'); ?>
 <?=$form->field($shopInfo, 'logo')->fileInput(); ?>
 <?=$form->field($shop, 'status')->radioList($shop::getStatusList()); ?>
 <?=$form->field($shopInfo, 'address')->textInput(); ?>
 <?=$form->field($shopInfo, 'description')->textArea(); ?>

 <div class="form-actions">
     <div class="row">
         <div class="col-md-offset-3 col-md-9">
             <?=Html::submitButton('提交', ['class'=>'btn green']); ?>
             <?=Html::submitButton('提交并继续添加', ['class'=>'btn blue','name'=>'next']); ?>
             <?=Html::a('返回', ['list'], ['class'=>'btn default']); ?>
             <!-- <button type="button" class="btn default">返回</button> -->
         </div>
     </div>
 </div>

 <?php ActiveForm::end(); ?>
