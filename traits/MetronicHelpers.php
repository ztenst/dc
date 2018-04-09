<?php
namespace app\traits;

use Yii;

trait MetronicHelpers
{
    private $_notification;
    /**
     * 获得ToastrNotification类
     */
    private function getNotification()
    {
        if($this->_notification===null) {
            $this->_notification = new \app\widgets\ToastrNotification;
        }
        return $this->_notification;
    }

    /**
     * 设置操作后的提示消息
     * @param string $msg    提示信息文案
     * @param string $type   提示类型
     * @param mixed $return 当该参数值为null时，不做任何操作；当为true时，跳转回来源页；
     * 当该值为数组时，默认作为redirect的参数跳转到对应route；当为字符串时，当作为网址进行跳转
     * @param Response $return 返回Response
     */
    public function setMessage($msg, $type='success', $return=null)
    {
        $this->getNotification()->setFlash($type, $msg);
        if($return!==null) {
            if(is_array($return) || is_string($return)) {
                $this->redirect($return)->send();
            } elseif($return===true) {
                $this->goBack(Yii::$app->request->getReferrer())->send();
            }
            Yii::$app->end();
        }
    }

    /**
     * 在模板中把flash信息输出
     */
    public function notify($view)
    {
        $notification = $this->getNotification();
        $notification->view = $view;
        $notification->doIt();
    }
}
