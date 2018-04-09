<?php
namespace app\widgets;

class Breadcrumbs extends \yii\widgets\Breadcrumbs
{
    public $itemTemplate = "<li>{link}<i class='fa fa-angle-right'></i></li>\n";
}
