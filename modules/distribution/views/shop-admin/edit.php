<?php
$this->title = $admin->getIsNewRecord() ? '帐号创建' : '帐号编辑';
$this->breadcrumbs = [
    ['label'=>'帐号列表' ,'url'=>['/admin/admin/list']],
    ['label'=>$this->title],
];
use app\base\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$form = ActiveForm::begin();
?>
<?=$form->field($admin, 'account', ['enableAjaxValidation'=>true])->textInput(['disabled'=>!$admin->getIsNewRecord()]); ?>
<?=$form->field($admin, 'shop_id')->dropDownList(ArrayHelper::map($shops, 'id', 'name'), ['prompt'=>'--选择商家--']); ?>
<?=$form->field($admin, 'newPassword')->passwordInput(); ?>
<?=$form->field($admin, 'repeatPassword')->passwordInput(); ?>
<?=$form->field($admin, 'phone'); ?>
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
