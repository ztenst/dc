<?php
namespace api\modules\canting\controllers;

use app\helpers\Storage;
use app\models\ext\SmsCode;
use Yii;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

class UserController extends Controller
{
    public function verbs()
    {
        return [
            '*' => ['get'],
            'send-code' => ['post']
        ];
    }

    /**
     * 验证码
     */
    public function actionSendCode()
    {
        $phone = Yii::$app->request->getBodyParam('phone');
        $phoneReg = '/(^(13\d|15[^4,\D]|17[13678]|18\d)\d{8}|170[^346,\D]\d{7})$/';
        //检测手机号
        if(!$phone && !preg_match($phoneReg,$phone)){
            throw new BadRequestHttpException('手机号格式不正确');
        }
        //判断短信是否在1分钟内已经发送过
        $cache = Yii::$app->cache;
        $smsCodeKey = 'rms_sms_code_'.$phone;
        $time = $cache->get($smsCodeKey);
        $expire = 60;
        if($time){
           throw new BadRequestHttpException('请一分钟过后再试');
        }
        $code = SmsCode::getCode();
        $msg = '您的验证码是'.$code.'，在5分钟内有效。如非本人操作请忽略本短信。';
        try{
            Yii::$app->sms->send($phone,$msg);
            $smsCode = new SmsCode();
            $attributes = [
                'phone' => $phone,
                'msg' => $msg,
                'code' => $code,
            ];
            $smsCode->attributes = $attributes;
            $smsCode->save();
            $cache->set($smsCodeKey,time(),$expire);
            return true;
        }catch (Exception $e){
            throw new BadRequestHttpException('发送失败');
        }
    }

    //个人中心
    public function actionCenter()
    {
        return $this->user;
    }
    
    //过往订单
    public function actionOrder()
    {
        
    }

    /**
     * 爱吃的菜
     * @return mixed
     */
    public function actionMenus()
    {
//        $limit = (int)Yii::$app->request->get('limit',5);
        $sql = "SELECT 
                  count('om.menu_id') AS order_times, 
                  om.`menu_name`, 
                  om.`menu_id`, 
                  om.`menu_attr_info`, 
                  om.`menu_price`, 
                  m.`image` AS menu_image,
                  m.`status` AS menu_status,
                  mu.`name` AS menu_unit 
                FROM `user_order_menu` AS om 
                INNER JOIN `shop_menu` AS m ON om.`menu_id` = m.`id` 
                INNER JOIN `shop_menu_unit` AS mu ON m.`unit_id` = mu.`id` 
                WHERE om.`user_id`=:user_id 
                GROUP BY om.`menu_id`
                ORDER BY order_times DESC 
                LIMIT 5";
        $connect = Yii::$app->db;
        $command = $connect->createCommand($sql);
        $command->bindValue(':user_id',$this->user->id);
        $data = $command->queryAll();
        foreach ($data as &$menu){
            $attrs = json_decode($menu['menu_attr_info'],true);
            $menu_specs = [];
            $menu_attrs = [];
            if($attrs && is_array($attrs)){
                foreach ($attrs as $attr){
                    if($attr['name'] == '尺寸'){
                        $menu_attrs[] = $attr;
                    }else{
                        $menu_specs[] = $attr;
                    }
                }
            }
            $menu['menu_specs'] = $menu_specs;
            $menu['menu_attrs'] = $menu_attrs;
            unset($menu['menu_attr_info']);
            $menu['menu_image'] = Storage::fixImageUrl($menu['menu_image']);
        }
        return $data;
    }
    
}
