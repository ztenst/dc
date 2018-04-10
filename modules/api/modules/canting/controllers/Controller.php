<?php
namespace api\modules\canting\controllers;

use api\modules\canting\models\UserMember;
use app\base\ArCache;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class Controller extends \api\base\Controller
{
    protected $user;

    protected $arCache;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        /* 验证用户是否登录
         * wx.request() 带上third_session 访问 这边默认通过get方式传递
         */
        $third_session = Yii::$app->request->get('third_session');

        if(!Yii::$app->user->exist($third_session)){
            throw new UnauthorizedHttpException('未登录或登录已过期');
        }

        $session = Yii::$app->user->get($third_session);

        $user = UserMember::findOne($session['user_id']);
        if(is_null($user)){
            throw new BadRequestHttpException('用户不存在');
        }

        $this->user = $user;
        return true;
    }

    public function getArCache()
    {
        if(!$this->arCache){
            $this->arCache = new ArCache('rms_shop_arcache_');
        }
        return $this->arCache;
    }
}
