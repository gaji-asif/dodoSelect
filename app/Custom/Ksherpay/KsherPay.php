<?php

namespace App\Custom\Ksherpay;

class KsherPay {
    public $time;
    public $appid; //ksher appid
    public $privatekey;// 私钥
    public $pubkey;//ksher公钥
    public $version;//SDK版本
    public $pay_domain;
    public $gateway_domain;

    public function __construct($appid='', $privatekey='', $version='3.0.0'){
        $this->time = date("YmdHis", time());
        $this->appid = $appid;
        $this->privatekey = $privatekey;
        $this->version = $version;
        $this->pay_domain = 'https://api.mch.ksher.net/KsherPay';
        $this->gateway_domain = 'https://gateway.ksher.com/api';

		$this->pubkey = <<<EOD
-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAL7955OCuN4I8eYNL/mixZWIXIgCvIVE
ivlxqdpiHPcOLdQ2RPSx/pORpsUu/E9wz0mYS2PY7hNc2mBgBOQT+wUCAwEAAQ==
-----END PUBLIC KEY-----
EOD;

    }

    /**
     * 生成随机数
     */
    public function generate_nonce_str($len=16) {
        $nonce_str = "";
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        for ( $i = 0; $i < $len; $i++ ) {
            $nonce_str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $nonce_str;
    }
    /**
     * 生成sign
     * @param $data
     * @param $private_key_content
     */
    public function ksher_sign($data){
        $message = self::paramData( $data );
        $private_key = openssl_get_privatekey($this->privatekey);
        openssl_sign($message, $encoded_sign, $private_key,OPENSSL_ALGO_MD5);
        openssl_free_key($private_key);
        $encoded_sign = bin2hex($encoded_sign);
        return $encoded_sign;
    }
    /**
     * 验证签名
     */
    public function verify_ksher_sign( $data, $sign){
        $sign = pack("H*",$sign);
        $message = self::paramData( $data );
        $res = openssl_get_publickey($this->pubkey);
        $result = openssl_verify($message, $sign, $res,OPENSSL_ALGO_MD5);
        openssl_free_key($res);
        return $result;
    }
    /**
     * 处理待加密的数据
     */
    private static function paramData($data){
        ksort($data);
        $message = '';
        foreach ($data as $key => $value) {
            $message .= $key . "=" . $value;
        }
        $message = mb_convert_encoding($message, "UTF-8");
        return $message;
    }
    /**
     * @access get方式请求数据
     * @params url //请求地址
     * @params data //请求的数据，数组格式
     * */
    public function _request($url, $data=array()){
        try {
            if(!empty($data) && is_array($data)){
                $params = '';
                $data['sign'] = $this->ksher_sign($data);
                foreach($data as $temp_key =>$temp_value){
                    $params .= ($temp_key."=".urlencode($temp_value)."&");
                }
                if(strpos($url, '?') === false){
                    $url .= "?";
                }
                $url .= "&".$params;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $output = curl_exec($ch);

            if($output !== false){
                $response_array = json_decode($output, true);
                if($response_array['code'] == 0){
                    if(!$this->verify_ksher_sign($response_array['data'], $response_array['sign'])){
                        $temp = array(
                            "code"=> 0,
                            "data"=> array(
                                    "err_code"=> "VERIFY_KSHER_SIGN_FAIL",
                                    "err_msg"=> "verify signature failed",
                                    "result"=> "FAIL"),
                            "msg"=> "ok",
                            "sign"=> "",
                            "status_code"=> "",
                            "status_msg"=> "",
                            "time_stamp"=> $this->time,
                            "version"=> $this->version
                        );
                        return json_encode($temp);
                    }
                }
            }
            curl_close($ch);
            return $output;

        } catch (\Exception $e) {
            echo 'curl error';
            return false;
        }
    }
    /**
     * B扫C支付
     * 必传参数
     *    mch_order_no
     *    total_fee
     *    fee_type
     *    auth_code
     *    channel
     * 选传参数
     *    operator_id
     */
    public function quick_pay($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/quick_pay', $data);
        return $response;
    }

    /**
     * C扫B支付
     * 必传参数
     *     mch_order_no
     *     total_fee
     *     fee_type
     *     channel
     *     notify_url
     * 选传参数
     *     redirect_url
     *     paypage_title
     *     operator_id
     */
    public function jsapi_pay($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/jsapi_pay', $data);
        return $response;
    }

    /**
     * 动态码支付
     * 必传参数
     *     mch_order_no
     *     total_fee
     *     fee_type
     *     channel
     *     notify_url
     * 选传参数
     *     redirect_url
     *     paypage_title
     *     product
     *     attach
     *     operator_id
     *     device_id
     *     img_type
     */
    public function native_pay($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/native_pay', $data);
        return $response;
    }

    /**
     * 小程序支付
     * 必传参数
     *      mch_order_no
     *      local_total_fee
     *      fee_type
     *      channel
     *      sub_openid
     *      channel_sub_appid
     * 选传参数
     *     redirect_url
     *     notify_url
     *     paypage_title
     *     product
     *     operator_id
     */

    public function minipro_pay($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/mini_program_pay', $data);
        return $response;
    }
    /**
     * app支付
     * 必传参数
     *     mch_order_no
     *     total_fee
     *     fee_type
     *     channel
     *     sub_openid
     *     channel_sub_appid
     * 选传参数
     *     redirect_url
     *     notify_url
     *     paypage_title
     *     product
     *     attach
     *     operator_id
     *     refer_url 仅当channel为alipay时需要
     */
    public function app_pay($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/app_pay', $data);
        return $response;
    }
    /**
     * H5支付，仅支持channel=alipay
     *
     * 必传参数
     *     mch_order_no
     *     local_total_fee
     *     fee_type
     *     channel
     *     refer_url
     * 选传参数
     *     redirect_url
     *     notify_url
     *     paypage_title
     *     product
     *     attach
     *     operator_id
     *     device_id
     */
    public function wap_pay($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/wap_pay', $data);
        return $response;
    }
    /**
     * PC网站支付，仅支持channel=alipay
     * 必传参数
     *     mch_order_no
     *     local_total_fee
     *     fee_type
     *     channel
     *     refer_url
     * 选传参数
     *     redirect_url
     *     notify_url
     *     paypage_title
     *     product
     *     attach
     *     operator_id
     *     device_id
     */
    public function web_pay($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/web_pay', $data);
        return $response;
    }
    /**
     * 订单查询
     * 必传参数
     *     mch_order_no、ksher_order_no、channel_order_no三选一
     */
    public function order_query($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/order_query', $data);
        return $response;
    }
    /**
     * 订单关闭
     * 必传参数
     *     mch_order_no、ksher_order_no、channel_order_no三选一
     * 选传参数
     *     operator_id
     */
    public function order_close($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/order_close', $data);
        return $response;
    }
    /**
     * 订单撤销
     * 必传参数
     *     mch_order_no、ksher_order_no、channel_order_no三选一
     * 选传参数
     *     operator_id
     */
    public function order_reverse($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/order_reverse', $data);
        return $response;
    }
    /**
     * 订单退款
     * 必传参数
     *     total_fee
     *     fee_type
     *     refund_fee
     *     mch_refund_no
     *     mch_order_no、ksher_order_no、channel_order_no三选一
     * 选传参数
     *     operator_id
     */
    public function order_refund($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $data['version'] = $this->version;
        $response = $this->_request($this->pay_domain.'/order_refund', $data);
        return $response;
    }
    /**
     * 退款查询
     * 必传参数
     *     mch_refund_no、ksher_refund_no、channel_refund_no三选一
     *     mch_order_no、ksher_order_no、channel_order_no三选一
     */
    public function refund_query($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/refund_query', $data);
        return $response;
    }
    /**
     * 汇率查询
     * 必传参数
     *     channel
     *     fee_type
     *     date
     */
    public function rate_query($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->pay_domain.'/rate_query', $data);
        return $response;
    }
    /**
     *聚合支付商户查询订单支付状态
     * 必传参数
     * mch_order_no
     */
    public function gateway_order_query($data){
        //$data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->gateway_domain.'/gateway_order_query', $data);
        return $response;
    }
    /**
     *
     * 聚合支付商户通过API提交数据
     * :param kwargs:
     * 必传参数
     *   mch_order_no: 商户订单号 str
     *   total_fee: 金额(分) int
     *   fee_type: 货币种类 st
     *   channel_list: 支付通道 str
     *   mch_code: 商户订单code str
     *   mch_redirect_url: 商户通知url str
     *   mch_redirect_url_fail: 失败回调网址 str
     *   product_name: 商品描述 str
     *   refer_url: 商家refer str
     *   device: 设备名称(PC or H5) str
     * 选传参数
     *   color: 横幅颜色 str
     *   background: 横幅背景图片 str
     *   payment_color: 支付按钮颜色 str
     *   ksher_explain: 最下方文案 str
     *   hide_explain: 是否显示最下方文案(1显示 0不显示) int
     *   expire_time: 订单过期时间(min) int
     *   hide_exp_time: 是否显示过期时间(1显示 0不显示) int
     *   logo: 横幅logo str
     *   lang: 语言(en,cn,th) str
     *   shop_name: logo旁文案 str
     *   attach: 商户附加信息 str
     * :return:
     *   {'pay_content': 'https://gateway.ksher.com/mindex?order_uuid=订单uuid'}
     */
    public function gateway_pay($data){
        $data['appid'] = $this->appid;
        $data['nonce_str'] = $this->generate_nonce_str();
        $data['time_stamp'] = $this->time;
        $response = $this->_request($this->gateway_domain.'/gateway_pay', $data);
        return $response;
    }
}