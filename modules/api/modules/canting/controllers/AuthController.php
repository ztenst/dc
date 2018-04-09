<?php
namespace api\modules\canting\controllers;

use api\base\Controller;
use api\modules\canting\models\ShopShop;
use api\modules\canting\models\UserMember;
use app\base\ArCache;
use app\models\ext\ShopDesk;
use app\models\ext\UserActiveDesk;
use Yii;
use yii\web\BadRequestHttpException;

class AuthController extends Controller
{
    public function verbs()
    {
        return [
            'login' => ['post'],
            '*' => ['get']
        ];
    }

    /**
     * eg : {
     *      code
     *      encryptedData
     *      iv
     *      rawData
     *      signature
     *  }
     * 小程序登录接口
     * 通过code换取session_key 和 openid  生成自己的3rd_session 返回给客户端
     * @throws BadRequestHttpException
     * @return string
     */
    public function actionLogin()
    {
        $body = Yii::$app->request->getBodyParams();
        $result = Yii::$app->applet->getSession($body['code']);
        if(!$result || isset($result['errcode'])){
            throw new BadRequestHttpException($result['errmsg']);
        }
        //获取到session_key 和 openid
        $session_key = $result['session_key'];
        //数据签名校验
        $my_signature = sha1( $body['rawData'].$session_key);
        if($my_signature !=  $body['signature']){
            throw new BadRequestHttpException('用户信息校验失败.');
        }

        $data = Yii::$app->applet->decryptData($session_key,$body['encryptedData'], $body['iv']);
        if(!$data){
            throw new BadRequestHttpException('解密数据失败.');
        }
        //没有绑定微信开放平台没有unionId数据 先用openId代替unionId
        $attributes = [
            'unionid' => $data['unionId'],
            'nickname' => preg_replace('/\xEE[\x80-\xBF][\x80-\xBF]|\xEF[\x81-\x83][\x80-\xBF]/', '', $data['nickName']),
            'sex' => $data['gender'],
            'avatar' => $data['avatarUrl']
        ];
        $user_member = UserMember::findByUnionid($data['unionId']);
        if($user_member === null) {
            $user_member = new UserMember();
        }
        $user_member->attributes = $attributes;
        if(!$user_member->save()){
            throw new BadRequestHttpException('用户信息保存失败');
        }
        $result['user_id'] = $user_member->id;
        if($third_session = Yii::$app->user->add($result)){
            return [
                'third_session' => $third_session
            ];
        }else{
            throw new BadRequestHttpException('error');
        }
    }

    //扫一扫接口
    //eg : http://rms.app/api/canting/auth/scode?desk_id=1&shop_id=1
    public function actionScode()
    {
        /**
         * 验证店铺是否存在 验证桌号是否存在
         * 如果都存在
         * 判断这个桌号是否在点餐
         * 如果在点餐 进入菜单页面
         * 如果是空桌 进入选几人就餐页面
         * 并卓的情况
         * 扫码的这一桌如果是并卓返回的desk_id为merge_target_desk_id
         */

        $desk_id = Yii::$app->request->get('desk_id');
        $shop_id = Yii::$app->request->get('shop_id');

        $shop = ShopShop::find()->status()->whereId($shop_id)->one();
        if(is_null($shop)){
            throw new BadRequestHttpException('未能识别商家二维码');
        }
        $desk = ShopDesk::find()->shop($shop_id)->whereId($desk_id)->one();
        if(is_null($desk)){
            throw new BadRequestHttpException('未能识别商家二维码');
        }
        //判断餐桌是否为空桌
        $is_empty = $desk->status === ShopDesk::STATUS_EMPTY ? true : false;
        //判断是否为并卓
        if($desk->merge_target_desk_id){
            $merge_desk = ShopDesk::find()->shop($shop_id)->whereId($desk->merge_target_desk_id)->one();
            if(is_null($merge_desk)){
                throw new BadRequestHttpException('未能识别商家二维码');
            }
            $desk_id = $desk->merge_target_desk_id;
        }
        return [
            'desk_id' => $desk_id,
            'shop_id' => $shop_id,
            'desk_number' => $desk->number,
            'is_empty' => $is_empty,
        ];
    }
    
}
