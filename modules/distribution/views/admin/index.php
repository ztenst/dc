<?php
    $this->title = '账号设置';
    $this->breadcrumbs = [
        ['label'=>$this->title]
    ];
use app\base\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin();
?>
<?=$form->field($model, 'newPassword')->passwordInput(); ?>
<?=$form->field($model, 'repeatPassword')->passwordInput(); ?>
<?=$form->field($model, 'username'); ?>
<?=$form->field($model, 'phone'); ?>

<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-2 col-md-6">
            <button type="submit" class="btn green input-xlarge">提交</button>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
