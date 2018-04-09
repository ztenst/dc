<?php
namespace app\base\grid;

class GridView extends \yii\grid\GridView
{
    /**
     * @var array the HTML attributes for the grid table element.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $tableOptions = ['class' => 'table table-bordered table-striped table-condensed flip-content table-hover'];
    /**
     * @var string the layout that determines how different sections of the list view should be organized.
     * The following tokens will be replaced with the corresponding section contents:
     *
     * - `{summary}`: the summary section. See [[renderSummary()]].
     * - `{errors}`: the filter model error summary. See [[renderErrors()]].
     * - `{items}`: the list items. See [[renderItems()]].
     * - `{sorter}`: the sorter. See [[renderSorter()]].
     * - `{pager}`: the pager. See [[renderPager()]].
     */
    public $layout = "{items}</div>\n<div class='row'>{summary}\n<div class='col-md-7'>{pager}</div></div>";
    /**
     * @var array the configuration for the pager widget. By default, [[LinkPager]] will be
     * used to render the pager. You can use a different widget class by configuring the "class" element.
     * Note that the widget must support the `pagination` property which will be populated with the
     * [[\yii\data\BaseDataProvider::pagination|pagination]] value of the [[dataProvider]].
     */
    public $pager = ['class'=>'app\widgets\LinkPager'];

    /**
     * 重写该函数，直接返回字符串即可，配置$summary的定制化程度太低
     */
    public function renderSummary()
    {
        $pager = $this->dataProvider->pagination;
        return '<div class="col-md-5" style="padding-top:10px">共'.$pager->pageCount.'页'.$this->dataProvider->count.'条记录;每页展示'.$pager->pageSize.'条</div>';
    }
}
