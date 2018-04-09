<?php
use app\models\ext\AreaProvince;
use yii\helpers\ArrayHelper;
$provinces = ArrayHelper::map(AreaProvince::find()->all(), 'id', 'name');
?>
<?=$form->field($model, 'province_id')->dropDownList($provinces, ['prompt' => '--选择省份--']); ?>
