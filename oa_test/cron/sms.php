<?php
 function sub_curl($url,$data=array(),$is_post=0){
        $ch = curl_init();
        if(!$is_post)//get 请求
        {
            $url =  $url.'?'.http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        if($is_post)
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $info = curl_exec($ch);
        $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $info;
 }
$info = sub_curl('http://oa.max-digital.cn/index.php/Home/SqlTask/birthdaySms');
$info1 = sub_curl('http://api.max-digital.cn/Home/SqlTask/monthBill');