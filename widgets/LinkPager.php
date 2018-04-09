<?php
namespace app\widgets;

class LinkPager extends \yii\widgets\LinkPager
{
    /**
     * @var array HTML attributes for the pager container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'pagination pull-right'];
}
