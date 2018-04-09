<?php
namespace app\base\storage\qiniu\widgets;

use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class Single extends Widget
{
    /**
     * @var ActiveField
     */
    public $field;
    /**
     * @var Storage
     */
    public $storage;

    /**
     * 小物件配置项，包含js配置项（释义见七牛前端注释）：
     * - uptoken_url
     * - browse_button
     * - domain
     */
    public $options = [];

    /**
     * 替换js代码中的占位符
     * js代码可配置项（这些配置项可通过ActiveField::$storageJsOptions传入）:
     * - uptoken_url
     * - browse_button
     * - domain
     * @return string 替换了占位符后的js代码
     */
    public function replaceJsPlaceholder($jsCode)
    {
        $options = $this->options;
        $uptokenUrl = ArrayHelper::remove($options, 'uptoken_url');
        if(!$uptokenUrl) {
            throw new \Exception('uptoken_url必须设置');
        }
        $buttonId = ArrayHelper::getValue($options, 'browse_button', $this->getFieldId());
        $domain = ArrayHelper::remove($options, 'domain', $this->storage->domain);

        $replacement = [
            '{uptoken_url}' => $uptokenUrl,
            '{browse_button}' => $buttonId,
            '{domain}' => $domain,
            '{FileUploaded}' => $this->getFileUploadedCallback(),
        ];
        return strtr($jsCode, $replacement);
    }

    /**
     * 获取字段值
     */
    private function getFieldValue()
    {
        return Html::getAttributeValue($this->field->model, $this->field->attribute);
    }

    /**
     * 获取字段input框id
     */
    private function getFieldId()
    {
        return Html::getInputId($this->field->model, $this->field->attribute);
    }

    public function getWidth()
    {
        return ArrayHelper::getValue($this->options, 'width', 0);
    }

    public function getHeight()
    {
        return ArrayHelper::getValue($this->options, 'height', 0);
    }

    /**
     * 获取js中FileUploaded回调函数
     * @return string 回调函数代码字符串
     */
    public function getFileUploadedCallback()
    {
        $hiddenInput = Html::activeHiddenInput($this->field->model, $this->field->attribute, ['id'=>'hideval']);
        $js = <<<EOT
        function(up, file, info) {
               var domain = up.getOption('domain');
               var res = $.parseJSON(info);
               key = res.key;

               sourceLink = domain + key; //获取上传成功后的文件的Url
               console.log(sourceLink);

               if({$this->width} + {$this->height}) {
                   imgLink = Qiniu.imageView2({
                       mode: 3,
                       w: {$this->width},
                       h: {$this->height},
                       q: 100
                   }, key);
               } else {
                   imgLink = sourceLink;
               }

               $('#single{$this->getId()}').html('').append('<img src=\'\'/>').find('img').attr('src', imgLink).show();
               $('#single{$this->getId()}').append('{$hiddenInput}');
               $('#hideval').val(key);
        }
EOT;
        return ArrayHelper::getValue($this->options, 'FileUploaded', $js);
    }



    public function run()
    {
        return $this->render('single',[

        ]);
    }
}
