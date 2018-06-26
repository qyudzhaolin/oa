<?php
/**
 * OA系统用户管理
 * 短信发送模块（Aliyun）
 */
namespace Home\Controller;

class SqlTaskController extends BaseController
{
    public $check_access = false;
    /*
     * 生日短信定时任务触发
     */
    public function birthdaySmsAction(){
        //读出当天过生日的用户列表
        //逐条调用阿里短信生日模板发送短信
        //发送成功的记录，存入日志表，后期用
        $nowday = date('m-d');
        $map['is_del'] = 1;
        $map['birthday'] = array('like','%'.$nowday);
        $model = M('User');
        $list = $model->where($map)->order('user_id asc')->select();
        $signName = C('SMS_ALI_CONFIG.sign_name');
        $template_code = 'SMS_77050042';//生日的模板
        if($list){
            $i = 0;
            foreach($list as $key=>$val) {
                $i++;
                $paramString = json_encode(array('name'=>$val['real_name']));
                $AliSms = new \Home\Model\SmsModel();
                $info = $AliSms->sendAliApi($val['phone'], $template_code, $paramString, $signName);
//                $info = $AliSms->sendAliApi('13764604663', $template_code, $paramString, $signName);
                $log['crt_time'] = time();
                $log['phone'] = $val['phone'];
                $log['type'] = 2;
                $log['template_code'] = $template_code;
                M('SmsLog')->add($log);
            }
            $this->ajaxReturn('Already send: ' .$i);
        }
        return false;
    }

    /*
     * 试用期转正邮件提醒，提前15天
     */
    public function userTryRemindAction(){
        $over_date = date('Y-m-d',strtotime(date('Y-m-d')." +15day"));
        $list = M("user_hr")->where("try_over_date='$over_date'")->select();
        foreach ($list as $row) {
            send_email_approve($row['uid'], '试用期转正提醒', "请进入试用期转正申请页面填写试用期小结", U("UserHrTry/add"));
        }
    }
    
    /*
     * 合同到期邮件提醒，提前45天
     */
    public function userExpireAction(){
        $over_date = date('Y-m-d',strtotime(date('Y-m-d')." +45day"));
        $list = M("user_hr")->where("contract_over_date='$over_date'")->select();
        foreach ($list as $row) {
            $user = M("user")->where(['user_id'=>$row['uid']])->find();
            $hrg = get_hrg($user['depart_id']);
            $principal = get_depart_principal($user['depart_id']);
            if($principal){         //部门负责人就是自己
                if($row['uid'] == $principal['user_id'] || $user['leader_uid'] == $principal['user_id']){
                    $l_uid = 0;
                    $p_uid = $user['leader_uid'];
                }else{
                    $l_uid = $user['leader_uid'];
                    $p_uid = $principal['user_id'];
                }
            }
            $ret = M('UserHrExpire')->add(array('user_id'=>$row['uid'],'l_uid'=>$l_uid,'p_uid'=>$p_uid,'h_uid'=>$hrg['user_id'],'cdate'=>date('Y-m-d H:i:s')));
            if($ret){
                if($l_uid==0){
                    send_email_approve($p_uid, $user['real_name'].'合同到期提醒', $user['real_name']."合同于45天后到期，请进行相关处理", U("UserHrExpire/add",array('user_id'=>$row['uid'])));
                }else{
                    send_email_approve($l_uid, $user['real_name'].'合同到期提醒', $user['real_name']."合同于45天后到期，请进行相关处理", U("UserHrExpire/add",array('user_id'=>$row['uid'])));
                }
                send_email_approve($hrg['user_id'], $user['real_name'].'合同到期提醒', $user['real_name']."合同于45天后到期，请进行相关处理", U("UserHrExpire/add",array('user_id'=>$row['uid'])));
            }
        }
    }

    /**
     * 商户号流水刷单提醒（模板消息）
     */
    public function monthBill(){
        $userarr = array('oBCVwwmzQDpRce-AB9stE2tmSIAg','');
        foreach($userarr as $openid){
            $configParams = array();
            $configParams['touser'] = 'oBCVwwmzQDpRce-AB9stE2tmSIAg';
            $configParams['template_id'] = 'gida-IK7j-BeAOSGvb8PKGj62J-W4zLx_QDDSw57fIE';
            $configParams['url'] = 'http://www.baidu.com';
            $configParams['data'] = array(
                'result' => array(
                    'value'=>'我们为您准备了一个分享红包，详细如下：',
                    'color'=>'#173177',
                ),
                'withdrawMoney' => array(
                    'value'=>'6.6元',
                    'color'=>'#173177',
                ),
                'withdrawTime' => array(
                    'value'=> date('Y-m-d H:i:s'),
                    'color'=>'#173177',
                ),
                'cardInfo' => array(
                    'value'=> '微信红包',
                    'color'=>'#173177',
                ),
                'arrivedTime' => array(
                    'value'=> date('Y-m-d H:i:s',time()+86500),
                    'color'=>'#173177',
                ),
                'remark' => array(
                    'value'=> '感谢您对我们的关注~~',
                    'color'=>'#173177',
                )
            );
            $postdata['signid'] = 4;
            $postdata['config'] = json_encode($configParams);
            $request = "http://api.max-digital.cn/Api/index/sendTemplateMsg";
            $return = $this->sub_curl($request,$postdata,1);
        }

        dump(json_decode($return,true));
    }
}
