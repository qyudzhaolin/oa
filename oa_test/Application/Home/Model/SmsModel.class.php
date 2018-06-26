<?php
namespace Home\Model;
use Think\Model;
class SmsModel extends Model{
    /*
     * showApi发送
     * $content={}
     */
    public function showApiSend($phone,$content,$tnum){
        header("Content-Type:text/html;charset=UTF-8");
        date_default_timezone_set("PRC");
        $return['status'] = 0;
        $return['error'] = 'null';
        $config = C('SMS_SHOWAPI_CONFIG');
        $showapi_appid = $config['app_appid'];  //替换此值,在官网的"我的应用"中找到相关值
        $showapi_secret = $config['app_secret'];  //替换此值,在官网的"我的应用"中找到相关值
        $paramArr = array(
            'showapi_appid'=> $showapi_appid,
            'mobile'=> $phone,
            'content'=> $content,
            'tNum'=> $tnum,//模板id
            'big_msg'=> ""
            //添加其他参数
        );
        $param = $this->createParam($paramArr,$showapi_secret);
        $url = $config['request_url'].'?'.$param;
//        $url = 'http://route.showapi.com/892-3?'.$param;
        $result = file_get_contents($url);
        $result = json_decode($result);
        if($result && $result['showapi_res_code']==0){
            $return['status'] = 1;
            $return['error'] = 'OK';
        }else{
            $return['status'] = -1;
            $return['error'] = $result['showapi_res_error']?$result['showapi_res_error']:'发送失败';
        }
        return $return;
    }
    //阿里云短信接口发送
    public function sendAliApi($phone,$templateCode,$paramString,$signName){
        $config = C('SMS_ALI_CONFIG');
        $app_key = $config['app_key'];//"24537034";
        $app_secret = $config['app_secret'];//"b7e78f96c164083332aa87c828945deb";
        $data['ParamString'] = $paramString;
        $data['SignName'] = $signName;
        $data['TemplateCode'] = $templateCode;
        $data['RecNum'] = trim($phone);
        $request_host = "http://sms.market.alicloudapi.com";
        $request_uri = "/singleSendSms";
        $request_method = "GET";
        $info = "";
        $infos = $this->do_get($app_key, $app_secret, $request_host, $request_uri, $request_method, $data, $info);
        $result = json_decode($infos,true);
        if($result && $result['success']){
            $return['status'] = 1;
            $return['error'] = 'OK';
        }else{
            $return['status'] = -1;
            $return['error'] = $result['message']?$result['message']:'发送失败';
        }
        return $return;
    }
    function do_get($app_key, $app_secret, $request_host, $request_uri, $request_method, $request_paras, &$info) {
        ksort($request_paras);
        $request_header_accept = "application/json;charset=utf-8";
        $content_type = "";
        $headers = array(
            'X-Ca-Key' => $app_key,
            'Accept' => $request_header_accept
        );
        ksort($headers);
        $header_str = "";
        $header_ignore_list = array('X-CA-SIGNATURE', 'X-CA-SIGNATURE-HEADERS', 'ACCEPT', 'CONTENT-MD5', 'CONTENT-TYPE', 'DATE');
        $sig_header = array();
        foreach($headers as $k => $v) {
            if(in_array(strtoupper($k), $header_ignore_list)) {
                continue;
            }
            $header_str .= $k . ':' . $v . "\n";
            array_push($sig_header, $k);
        }
        $url_str = $request_uri;
        $para_array = array();
        foreach($request_paras as $k => $v) {
            array_push($para_array, $k .'='. $v);
        }
        if(!empty($para_array)) {
            $url_str .= '?' . join('&', $para_array);
        }
        $content_md5 = "";
        $date = "";
        $sign_str = "";
        $sign_str .= $request_method ."\n";
        $sign_str .= $request_header_accept."\n";
        $sign_str .= $content_md5."\n";
        $sign_str .= "\n";
        $sign_str .= $date."\n";
        $sign_str .= $header_str;
        $sign_str .= $url_str;

        $sign = base64_encode(hash_hmac('sha256', $sign_str, $app_secret, true));
        $headers['X-Ca-Signature'] = $sign;
        $headers['X-Ca-Signature-Headers'] = join(',', $sig_header);
        $request_header = array();
        foreach($headers as $k => $v) {
            array_push($request_header, $k .': ' . $v);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $request_host . $url_str);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $ret;
    }
}