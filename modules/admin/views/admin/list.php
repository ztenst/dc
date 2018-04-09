<?php
$this->title = '帐号列表';
$this->breadcrumbs[] = $this->title;

use app\base\grid\GridView;
use app\base\grid\ActionColumn;
use yii\helpers\Html;
?>

<div class="row">
    <div class="col-md-12">
        <div class="table-toolbar">
            <div class="btn-group pull-left">
            </div>
            <div class="pull-right">
                <?=Html::a('添加帐号', ['edit'], ['class'=>'btn green']); ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'account',
                'statusText',
                [
                    'class' => ActionColumn::className(),
                    'header' => '操作',
                    'template' => '{edit} {delete}',
                    'buttons' => [
                        'edit'=>function($url, $model, $key){
                            return Html::a('编辑', $url, ['class'=>'btn blue btn-xs']);
                        },
                    ],
                    'headerOptions' => ['class'=>'col-md-2']
                ],
            ]
        ]);
        ?>
    </div>
</div>
