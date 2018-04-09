<?php
use app\base\grid\GridView;
use app\base\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
 ?>
<div class="row">
    <div class="col-md-12">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'caption' => $gridViewCaption,
            'columns' => [
                'id',
                'name',
                'statusText',
                [
                    'class' => ActionColumn::className(),
                    'header' => '操作',
                    'template' => '{edit} {delete}',
                    'buttons' => [
                        'edit'=>function($url, $model, $key) use($type){
                            return Html::a('编辑', $url, ['class'=>'btn blue btn-xs']);
                        }
                    ],
                    'urlCreator' => function($action, $model, $key, $index, $column) use($type){
                        switch($action) {
                            case 'edit': return Url::toRoute(['edit','id'=>$key,'type'=>$type]);
                            case 'delete': return Url::toRoute(['delete', 'id'=>$key,'type'=>$type]);
                        }
                    },
                    'headerOptions' => ['class'=>'col-md-2']
                ],
            ]
        ]);
        ?>
    </div>
</div>
