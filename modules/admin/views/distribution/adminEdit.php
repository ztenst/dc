<?php
$this->title = $admin->getIsNewRecord() ? '帐号创建' : '帐号编辑';
$this->breadcrumbs = [
    ['label'=>'帐号列表' ,'url'=>['/admin/distribution/admin-list']],
    ['label'=>$this->title],
];
use app\base\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin();
?>
<?=$form->field($admin, 'city_id')->dropDownList($cities, ['prompt'=>'--选择城市--']); ?>
<?=$form->field($admin, 'account', ['enableAjaxValidation'=>true])->textInput(['disabled'=>!$admin->getIsNewRecord()]); ?>
<?=$form->field($admin, 'newPassword')->passwordInput(); ?>
<?=$form->field($admin, 'repeatPassword')->passwordInput(); ?>
<?=$form->field($admin, 'username'); ?>
<?=$form->field($admin, 'phone'); ?>
<?=$form->field($admin, 'status')->radioList($admin::getStatusList()); ?>

<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <button type="submit" class="btn green">提交</button>
            <?=Html::a('返回', ['admin-list'], ['class'=>'btn default']); ?>
            <!-- <button type="button" class="btn default">返回</button> -->
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
