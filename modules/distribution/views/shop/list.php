<?php
$this->title = '商家列表';

use app\base\grid\GridView;
use app\base\grid\ActionColumn;
use yii\grid\DataColumn;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="row">
    <div class="col-md-12">
        <div class="table-toolbar">
            <div class="btn-group pull-left">
            </div>
            <div class="pull-right">
                <?=Html::a('添加商家', ['edit'], ['class'=>'btn green']); ?>
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
                [
                    'attribute' => 'id',
                    'headerOptions' => ['class'=>'col-md-1']
                ],
                [
                    'label' => 'Logo',
                    'content' => function ($model, $key, $index, $column) {
                        if($img = $model->shopInfo->getLogo(40,30)) {
                            return Html::img($img);
                        }
                    },
                    'headerOptions' => ['class'=>'col-md-1']
                ],
                'name',
                [
                    'attribute' => 'status',
                    'value' => 'statusText',
                    'filter' => $model::getStatusList(),
                    'filterInputOptions' => ['class'=>'form-control', 'prompt'=>'全部'],
                    'headerOptions' => ['class'=>'col-md-2'],
                ],
                [
                    'class' => ActionColumn::className(),
                    'header' => '操作',
                    'template' => '{adminList} {edit} {delete}',
                    'buttons' => [
                        'edit'=>function($url, $model, $key){
                            return Html::a('编辑', $url, ['class'=>'btn blue btn-xs']);
                        },
                        'adminList' => function($url, $model, $key) {
                            return Html::a('商家帐号', $url, ['class'=>'btn green btn-xs']);
                        }
                    ],
                    'urlCreator' => function($action, $model, $key, $index, $column) {
                        switch($action) {
                            case 'edit': return Url::toRoute(['edit','id'=>$key]);
                            case 'delete': return Url::toRoute(['delete', 'id'=>$key]);
                            case 'adminList': return Url::toRoute(['shop-admin/list', 'ShopAdminSearch[shop_id]'=>$key]);
                        }
                    },
                    'headerOptions' => ['class'=>'col-md-3']
                ],
            ]
        ]);
        ?>
    </div>
</div>
