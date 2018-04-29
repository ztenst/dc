<?php
namespace api\modules\canting\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
/**
 * 微信支付类
 */
class WxPay extends Component {
	public $appid;
        // $apps = '48d79f6b24890a88ef5b53a5e5119f5a';
        
    // $appid=SiteExt::getAttr('qjpz','appid');
    public $mch_id;
    public $body;
    public $out_trade_no;
    public $nonce_str;
    public $notify_url;
    public $spbill_create_ip;
    public $mch_key;

    public function init()
    {
    	# code...
    }

    public function __construct()
    {
    	$this->appid = 'wx67ff4c94e4ba7507';
    	$this->mch_id = '1501462231';
    	$this->mch_key = 'yycy2018yycy2018yycy2018yycy2018';
    	$this->out_trade_no = 'wxpay'.time();
    	$this->nonce_str = $this->createNoncestr(20);
    	$this->notify_url = Yii::$app->request->getHostInfo().'/api/canting/home/payRecall';
    	$this->spbill_create_ip = $_SERVER["REMOTE_ADDR"];
    }

	public function setPay($body='',$price='',$openid='')
	{
		if(!$body || !$price || !$openid) {
			return false;
		}
		$data = [
            'appid'=>$this->appid,
            'mch_id'=>$this->mch_id,
            'body'=>$body,
            'out_trade_no'=>$this->out_trade_no,
            'nonce_str'=>$this->nonce_str,
            // 'sign'=>$sign,
            'total_fee'=>(int)($price*100),
            'spbill_create_ip'=>$this->spbill_create_ip,
            'trade_type'=>'JSAPI',
            'notify_url'=>$this->notify_url,
            'openid'=>$openid,
        ];
        $data['sign'] = $this->getSign($data);
        // var_dump($data);
         // $this->frame['data'] = $dataxml;
        // $res = $this->post('https://api.mch.weixin.qq.com/pay/unifiedorder',$dataxml);
        $xmlData = $this->arrayToXml($data);
        $return = $this->xmlToArray($this->postXmlCurl($xmlData, 'https://api.mch.weixin.qq.com/pay/unifiedorder', 60));
        if(isset($return['return_code']) && isset($return['return_msg'])){
            throw new Exception($return['return_msg']);
        }
        $parameters = array(
            'appId' => $this->appid, //小程序ID
            'timeStamp' => '' . time() . '', //时间戳
            'nonceStr' => $this->createNoncestr(20), //随机串
            'package' => 'prepay_id=' . $return['prepay_id'], //数据包
            'signType' => 'MD5'//签名方式
        );
        //签名
        $parameters['paySign'] = $this->getSign($parameters);
        return $parameters;
	}

    // 生成签名
    private function getSign($Obj) {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=".$this->mch_key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    // 作用：格式化参数，签名过程需要使用
    private function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    //数组转换成xml
    private function arrayToXml($arr) {
       if(!is_array($arr) || count($arr) == 0) return '';

            $xml = "<xml>";
            foreach ($arr as $key=>$val)
            {
                    if (is_numeric($val)){
                            $xml.="<".$key.">".$val."</".$key.">";
                    }else{
                            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
                    }
            }
            $xml.="</xml>";
            return $xml; 
    }

    //xml转换成数组
    private function xmlToArray($xml) {


        //禁止引用外部xml实体 


        libxml_disable_entity_loader(true);


        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);


        $val = json_decode(json_encode($xmlstring), true);


        return $val;
    }

    private static function postXmlCurl($xml, $url, $second = 30) 
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);


        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);


        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }

    private static function postXmlCurlByCert($xml, $url, $second = 30) 
    {
        $ch = curl_init();
        $params[CURLOPT_URL] = $url;    //请求url地址
        $params[CURLOPT_HEADER] = false; //是否返回响应头信息
        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
        $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
        $params[CURLOPT_POST] = true;
        $params[CURLOPT_POSTFIELDS] = $xml;
        $params[CURLOPT_SSL_VERIFYPEER] = false;
        $params[CURLOPT_SSL_VERIFYHOST] = false;
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,Yii::app()->basePath.'/cert/apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY,Yii::app()->basePath.'/cert/apiclient_key.pem');


        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        curl_setopt_array($ch, $params); 
        set_time_limit(0);


        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            var_dump($error);exit;
            throw new WxPayException("curl出错，错误码:$error");
        }
    }
    public function createNoncestr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function getRsaKey()
    {
        $signarr = [
            'appid'=>$this->appid,
            'mch_id'=>$this->mch_id,
            'nonce_str'=>$this->nonce_str,
            // 'body'=>'明悦心空商户支付到银行卡',
            // 'out_trade_no'=>'wxpay'.time(),
            // 'total_fee'=>1,
            // 'spbill_create_ip'=>$this->spbill_create_ip,
            //  'notify_url'=>$this->notify_url,
            //  'trade_type'=>'JSAPI',
        ];
        $sign = $this->getSign($signarr);
        $arr = [
            'mch_id'=>$this->mch_id,
            'nonce_str'=>$this->nonce_str,
            'sign'=>$sign,
            'sign_type'=>'MD5',
        ];
        $xmlData = $this->arrayToXml($arr);
        // var_dump($this->mch_key);exit;
        $res = $this->xmlToArray($this->postXmlCurlByCert($xmlData,'https://fraud.mch.weixin.qq.com/risk/getpublickey',60));
        var_dump($res);exit;
        // if($res[''])
    }


}
