<?php
$this->title = '商家帐号列表';

use app\base\grid\GridView;
use app\base\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
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
            'filterModel' => $model,
            'columns' => [
                'id',
                [
                    'attribute' => 'shop_id',
                    'value' => 'shopName',
                    'headerOptions' => ['class'=>'col-md-1'],
                    'filter' => ArrayHelper::map($shops, 'id', 'name'),
                    'filterInputOptions' => ['prompt'=>'全部','class'=>'form-control'],
                ],
                'account',
                [
                    'attribute' => 'status',
                    'value' => 'statusText',
                    'filter' => $model::getStatusList(),
                    'filterInputOptions' => ['prompt'=>'全部','class'=>'form-control'],
                    'headerOptions' => ['class'=>'col-md-2']
                ],
                [
                    'attribute' => 'phone',
                    'headerOptions' => ['class'=>'col-md-2']
                ],
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
