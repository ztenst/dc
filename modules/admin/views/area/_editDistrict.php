<?php
use app\models\ext\AreaCity;
use yii\helpers\ArrayHelper;
$cities = ArrayHelper::map(AreaCity::find()->all(), 'id', 'name');
?>
<?=$form->field($model, 'city_id')->dropDownList($cities, ['prompt' => '--选择城市--']); ?>
