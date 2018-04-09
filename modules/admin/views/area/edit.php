<?php
$this->title = $typeName . $operateName;
$this->breadcrumbs = [
    ['label'=>'区域列表' ,'url'=>['/admin/area/list']],
    ['label'=>$this->title],
];
use app\base\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin();
?>
<?=$this->render('_edit'.ucfirst($type), [
    'form' => $form,
    'model' => $model,
]); ?>
<?=$form->field($model, 'name'); ?>
<?=$form->field($model, 'status')->radioList($model::getStatusList()); ?>

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
