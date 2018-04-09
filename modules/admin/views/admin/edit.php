<?php
$this->title = $admin->getIsNewRecord() ? '帐号创建' : '帐号编辑';
$this->breadcrumbs = [
    ['label'=>'帐号列表' ,'url'=>['/admin/admin/list']],
    ['label'=>$this->title],
];
use app\base\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin([
    'options' => ['class'=>'form-horizontal'],
    // 'validationUrl' => ['/admin/admin/editValid'],
    'fieldConfig' => [
        'hintOptions' => ['class'=>'help-inline'],
        'inputOptions' => ['class'=>'form-control input-inline input-xlarge'],
    ],
    // 'enableClientScript' => false,
]);
?>
<?=$form->field($admin, 'account', ['enableAjaxValidation'=>true])->textInput(['disabled'=>!$admin->getIsNewRecord()]); ?>
<?=$form->field($admin, 'newPassword')->passwordInput(); ?>
<?=$form->field($admin, 'repeatPassword')->passwordInput(); ?>
<?=$form->field($admin, 'username'); ?>
<?=$form->field($admin, 'status')->radioList($admin::getStatusList()); ?>

<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <button type="submit" class="btn green">提交</button>
            <?=Html::a('返回', ['list'], ['class'=>'btn default']); ?>
            <!-- <button type="button" class="btn default">返回</button> -->
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
