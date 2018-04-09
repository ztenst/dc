<?php
namespace app\base;

use app\base\storage\Storage;
use Yii;
use yii\helpers\Html;

/**
 * 适配metronic4.7版本
 * 部分代码参考\yii\yii2-bootstrap\ActiveField
 * @author weibaqiu
 * @version 2016-12-16
 */
class ActiveField extends \yii\bootstrap\ActiveField
{
    /**
     * @var boolean whether to render [[checkboxList()]] and [[radioList()]] inline.
     */
    public $inline = true;
    /**
     * @var string the template that is used to arrange the label, the input field, the error message and the hint text.
     * The following tokens will be replaced when [[render()]] is called: `{label}`, `{input}`, `{error}` and `{hint}`.
     */
    public $template = "{label}<div class='col-md-6'>{input}{hint}{error}</div>";
    /**
     * @var string the template for inline radioLists
     */
    public $inlineRadioListTemplate = "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}\n{hint}";
    /**
     * @var array the default options for the label tags. The parameter passed to [[label()]] will be
     * merged with this property when rendering the label tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $labelOptions = ['class' => 'col-md-2 control-label'];
    /**
     * @var array the default options for the input tags. The parameter passed to individual input methods
     * (e.g. [[textInput()]]) will be merged with this property when rendering the input tag.
     *
     * If you set a custom `id` for the input element, you may need to adjust the [[$selectors]] accordingly.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $inputOptions = ['class' => 'form-control input-inline input-xlarge'];
    /**
     * @var array options for the wrapper tag, used in the `{beginWrapper}` placeholder
     */
    public $wrapperOptions = ['class'=>'col-md-6'];
    /**
     * @var 前端存储上传相关的配置项，具体配置项查看storage\qiniu(sso)\widgets\Widgets::replaceJsPlaceholder()注释
     * 额外提供以下配置项：
     * - width: 图片展示宽度
     * - height: 图片展示高度
     */
    public $storageJsOptions = [];

    /**
     * @inheritdoc
     */
    public function radioList($items, $options = [])
    {
        if ($this->inline) {
            if (!isset($options['template'])) {
                $this->template = $this->inlineRadioListTemplate;
            } else {
                $this->template = $options['template'];
                unset($options['template']);
            }
            if (!isset($options['itemOptions'])) {
                $options['itemOptions'] = [
                    'labelOptions' => ['class' => 'radio-inline'],
                ];
            }
        }  elseif (!isset($options['item'])) {
            $itemOptions = isset($options['itemOptions']) ? $options['itemOptions'] : [];
            $options['item'] = function ($index, $label, $name, $checked, $value) use ($itemOptions) {
                $options = array_merge(['label' => $label, 'value' => $value], $itemOptions);
                return '<div class="radio">' . Html::radio($name, $checked, $options) . '</div>';
            };
        }
        parent::radioList($items, $options);
        return $this;
    }

    /**
     * 上传文件按钮，只负责生成按钮
     * @param array $options 按钮选项，除了基本的html配置项，还提供以下配置项：
     * - width: 图片文件展示的宽度
     * - height: 图片文件展示的高度
     */
    public function fileInput($options = [])
    {
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        $options = array_merge([
            'class' => 'btn grey-mint input-xlarge'
        ], $options);

        $model = $this->model;
        $attribute = $this->attribute;

        if (!array_key_exists('id', $options)) {
            $options['id'] = Html::getInputId($model, $attribute);
        }
        $this->parts['{input}'] = Html::tag('div',Html::a('选择文件', '#', $options),['id'=>'container']);
        $this->template = strtr($this->template, ['{input}'=>'{input}{storage}']);

        return $this;
    }

    /**
     * 渲染绑定前端上传js小物件
     * 直接调用[[fileInput()]]函数即可
     */
    private function storageWidget()
    {
        $this->parts['{storage}'] = $this->storage->bindFormField($this);
    }

    public function render($content = null)
    {
        if(strpos($this->template,'{storage}')!==false && !isset($this->parts['{storage}'])) {
            $this->storageWidget();
        }
        return parent::render($content);
    }

    private $_storage;

    public function getStorage()
    {
        if($this->_storage===null) {
            $this->_storage = Yii::$app->storage;
        }
        return $this->_storage;
    }

    public function setStorage(Storage $storage)
    {
        $this->_storate = $storage;
    }
}
