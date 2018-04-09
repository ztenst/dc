<?php
$this->title = '区域列表';

use yii\helpers\Html;
$controller = $this->context;
 ?>
<div class="row">
    <div class="col-md-12">
        <div class="table-toolbar">
            <div class="pull-right">
                <div class="btn-group">
                    <button class="btn blue dropdown-toggle" data-toggle="dropdown" aria-expanded="false">添加<i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li role="presentation"><?=Html::a('省份', ['edit','type'=>$controller::TYPE_PROVINCE]); ?></li>
                        <li role="presentation"><?=Html::a('城市', ['edit','type'=>$controller::TYPE_CITY]); ?></li>
                        <li role="presentation"><?=Html::a('行政区', ['edit','type'=>$controller::TYPE_DISTRICT]); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?=$this->render('_list'.ucfirst($type), [
    'type' => $type,
    'gridViewCaption' => $gridViewCaption,
    'dataProvider' => $dataProvider,
]); ?>
