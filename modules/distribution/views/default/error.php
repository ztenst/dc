<?php
$this->title = '错误信息';
use yii\helpers\Html;
\app\modules\admin\assets\MetronicErrorAsset::register($this);
 ?>
<div class="row">
    <div class="col-md-12 page-500">
        <div class=" number font-red"> <?=$exception->statusCode; ?> </div>
        <div class=" details">
            <h4>出错了：</h4>
            <p> <?= nl2br(Html::encode($exception->getMessage())) ?>
                <br/> </p>
            <p>
                <?=Html::a('返回', Yii::$app->request->referrer ?  : ['/admin'], ['class'=>'btn red btn-outline']); ?>
                <br> </p>
        </div>
    </div>
</div>
