<?php

namespace app\base\sms\providers;

use yii\base\Exception;

class ChuanglanProvider extends Provider
{
    CONST API_URL = 'http://222.73.117.156/msg/HttpBatchSendSM';

    public static $errorMsg = [
        '101' => '无此用户',
        '102' => '密码错误',
        '103' => '提交过快(提交速度超过流速限制)',
        '104' => '系统忙()',
        '105' => '权限受限',
        '106' => '流量控制受限',
        '107' => '扩展码权限错误',
        '108' => '内容长度过长',
        '109' => '内部数据库错误',
        '110' => '序列号状态错误',
        '111' => '服务器写文件失败',
        '112' => '没有权限',
        '113' => '禁止同时使用多个接口地址',
        '115' => '相同手机号，相同内容重复提交',
        '116' => 'IP鉴权失败',
        '117' => '缓存无此序列号信息',
        '118' => '序列号为空，参数错误',
        '119' => '序列号格式错误，参数错误',
        '120' => '密码为空,参数错误',
    ];

    public function send($to, $message, $config)
    {
        $params = [
            'account' => $config['account'],
            'pswd' => $config['pswd'],
            'msg' => $message,
            'mobile' => $to,
            'needstatus' => false,
        ];
        $result = $this->post(self::API_URL, $params);
        $result = preg_split("/[,\r\n]/",$result);
        if($result[1] != 0){
            throw new Exception(self::$errorMsg[$result[1]]);
        }
        return true;
    }



}
