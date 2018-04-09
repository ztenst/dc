<?php
namespace app\widgets;

use Yii;
use yii\base\Widget;

class ToastrNotification
{
    public $view;

    public $title = '';

    private $message = '';

    public $closeButton = true;

    public $debug = false;

    private $_timeOut = 3000;

    private $_type = 'success';

    private $_position = 'toast-top-center';

    /**
     * 内置显示位置
     * 共有8种
     * topCenter\topRight\topLeft\bottomRight\bottomLeft\
     * bottomCenter\topFullWidth\bottomFullWidth
     * @var array
     */
    public static $builtInPositionMap = [
        'topCenter' => 'toast-top-center',
        'topRight' => 'toast-top-right',
        'topLeft' => 'toast-top-left',
        'bottomRight' => 'toast-bottom-right',
        'bottomLeft' => 'toast-bottom-left',
        'bottomCenter' => 'toast-bottom-center',
        'topFullWidth' => 'toast-top-full-width',
        'bottomFullWidth' => 'toast-bottom-full-width',
    ];

    /**
     * 内置显示类型
     * 共4种
     * @var array
     */
    public static $builtInTypeMap = [
        'success',
        'info',
        'warning',
        'error'
    ];

    /**
     * position显示位置属性
     * @param string $value 显示的位置，
     */
    public function setPosition($value)
    {
        $this->_position = isset(self::$builtInPositionMap[$value])
        ? self::$builtInPositionMap[$value] : array_shift(self::$builtInPositionMap);
    }

    /**
     * 显示类别
     * @param string $value 显示类别，共4种
     * success\info\warning\error
     */
    public function setType($value)
    {
        $this->_type = in_array($value, self::$builtInTypeMap) ? $value
        : array_shift(self::$builtInTypeMap);
    }

    /**
     * 获取类别
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * 设置显示时间（单位：秒）
     * @param integer $value 显示时间（秒）
     */
    public function setTimeOut($value)
    {
        $this->_timeOut = intval($value) * 1000;
    }

    /**
     * 注册前端资源文件
     */
    public function registerAssets()
    {
        \app\assets\plugins\ToastrNotificationAsset::register($this->view);
    }

    /**
     * 注册js配置代码
     * @return void
     */
    public function registerJsScript()
    {
        $js = <<<EOT
        toastr.options = {
            "closeButton": $this->closeButton,
            "debug": false,
            "positionClass": "$this->_position",
            "showDuration": "1000",
            "hideDuration": "1000",
            "timeOut": "$this->_timeOut",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
EOT;
        $this->view->registerJs($js, \yii\web\View::POS_END);
    }

    public function doIt()
    {
        if($this->getFlash()) {
            $this->registerAssets();
            $this->registerJsScript();
            $js = 'toastr.'.$this->type.'("'.$this->message.'");';
            $this->view->registerJs($js, \yii\web\View::POS_END);
        }
    }

    /**
     * 设置flash消息
     */
    public function setFlash($type, $message)
    {
        Yii::$app->getSession()->setFlash($type, $message);
    }

    /**
     * 获取flash消息
     * @return boolean 获取到消息返回true，未获取到返回false
     */
    public function getFlash()
    {
        $session = Yii::$app->getSession();
        $types = array_keys($session->getAllFlashes());
        foreach($types as $type) {
            if(in_array($type, self::$builtInTypeMap) && is_string($session->getFlash($type))) {
                $this->type = $type;
                $this->message = $session->getFlash($type, '未知提示', true);
                return true;
            }
        }
        return false;
    }

    public function setMessage($msg)
    {
        $this->message = str_replace(["\r\n", "\r", "\n"],'',$msg);
    }
}
