<?php
/**
 * @desc 日志方法
 * @param $action 操作,暂时定义三种：update,insert,delete，
 * @param $table 表名，无需加前缀,不区分大小写，
 * @param $status 状态，默认为0，不作状态记录，1表示操作成功， -1表示操作失败，
 * @author berry.qi
 * @date 2016-04-20
 */
function logrecords($action,$table,$status=0){
    $table = C('DB_PREFIX').strtolower($table);
    $log['table_name'] = $table;
    $log['action']     = $action;
    $log['status']     = $status;
    $log['action_user_id'] = session('user_id');//$_SESSION['uid'];//后期开放session
    $log['action_time'] = time();
    M('Log')->add($log);
}

/**
 * @desc 判断该用户是否在审批用户中
 */
function checkInApproverList($user_id, $aprv_no, $aprv_type){
    if(get_access_allvoucher($user_id)) return true;
    $where = " aprv_no='$aprv_no' and aprv_type=$aprv_type and (aprv_user_id1 = $user_id or aprv_user_id2 = $user_id or aprv_user_id3 = $user_id or aprv_user_id4 = $user_id or aprv_user_id5 = $user_id)";
    $app_model = new \Home\Model\ApproveModel();
    if($app_model->where($where)->find()){
        return true;
    }
    return false;
}

/**
 * @desc 判断该用户是否在项目中
 */
function checkInProjList($user_id, $lvl_id, $proj_id){
    if($lvl_id==1) return true;
    $depart_id = I('session.depart_id');
    if($depart_id==1) return true;               //财务部都可以查看
    $sql = "(select a.proj_id from max_project a INNER JOIN max_project_users b on b.proj_id=a.proj_id where b.user_id=$user_id and a.proj_id=$proj_id and a.is_del=1 and b.is_del=1) union (select a.proj_id from max_project a where a.proj_id=$proj_id and (a.proj_mgr=$user_id or a.crt_user_id=$user_id) and a.is_del=1)";
    $proj_model = new \Home\Model\ProjectModel();
    $list = $proj_model->query($sql);
    if(count($list)){
        return true;
    }
    return false;
}

/**
 * @desc 获取审批人列表
 * @param $approve 审批表对象
 * @param $apply_uid 申请该单据的用户Id
 * @param $user_id 当前登录的用户Id
 * @param $lvl_id 当前登录的用户的级别
 * @author maojingjing
 */
function getApproverList($apply_uid,$user_id,$lvl_id,$aprv_type,$crt_time,$approve=null){
    if(!is_numeric($apply_uid) || !is_numeric($user_id))return false;
    $user_model = new \Home\Model\UserModel();
    $appr_model = new \Home\Model\ApproveModel();
    $depart_id = session('depart_id');
    $depart_par_id = session('depart_par_id');
    $company_id = session('company_id');
    
    if($crt_time>get_c_date()){
        $where = "is_del=1";
        if($aprv_type == 4){        //还款单
            $where .= " and user_id=".get_cn_userid();
        }else{                      //报销单和变更报销单
            if($lvl_id>5){              //小于AD级别的
                $where .= " and lvl_id=5 and company_id = $company_id and (depart_id=$depart_id or depart_id=$depart_par_id)";
            }elseif($lvl_id == 5){            //AD
                $where .= " and lvl_id=2 and company_id = $company_id";
            }
        }
        
        return $user_model->get_list($where,"user_id,real_name");
    }else{
        if(!is_numeric($apply_uid) || !is_numeric($user_id))return false;
        $user_model = new \Home\Model\UserModel();
        $appr_model = new \Home\Model\ApproveModel();
        if($aprv_type == 4){          //还款单
            return $list = $user_model->get_list("is_del=1 and user_id=87 and user_id!=$user_id");              //出纳
        }
        $depart_id = session('depart_id');
        $depart_par_id = session('depart_par_id');
        
        if($approve['aprv_result1'] == 0){
            $list = $user_model->get_list("is_del=1 and company_id = $company_id and ((lvl_id>4 and lvl_id<9 and depart_id=$depart_id) or (lvl_id=5 and depart_id=$depart_par_id))");              //项目经理
        }else{
            $num = 0;
            for ($i = 1; $i < 6; $i++) {
                if($lvl_id!=1){                                          //非管理员时
                    if($approve['aprv_user_id'.$i] == $user_id){
                        $num = $i+1;
                        if($approve['aprv_user_id'.$num] == $user_id){
                            $num += 1;
                        }
                        break;
                    }
                }else{
                    if($approve['aprv_result'.$i] == 0){
                        $num = $i;
                        break;
                    }
                }
            }
        
            if(array_key_exists($num, $appr_model->_type_appr[$aprv_type])){
                $lvl_id = $appr_model->_type_appr[$aprv_type][$num];
                $where = "is_del=1 and company_id = $company_id and lvl_id=$lvl_id";
                if($num < 3){
                    $where .= " and depart_id=$depart_par_id";
                }
        
                $list = $user_model->get_list($where);
            }
        }
        
        return $list;
    }
}

/**
 * @desc 获取审批人列表
 * @param $approve 审批表对象
 * @param $apply_uid 申请该单据的用户Id
 * @param $user_id 当前登录的用户Id
 * @param $lvl_id 当前登录的用户的级别
 * @param $type 单据类型，2为借款，3为报销
 * @author maojingjing
 */
function getApproverListByProj($proj_id,$apply_uid,$user_id,$lvl_id,$crt_time,$approve=null,$type=3){
    if(!is_numeric($proj_id) || !is_numeric($user_id))return false;
    $user_model = new \Home\Model\UserModel();
    $appr_model = new \Home\Model\ApproveModel();
    $depart_id = session('depart_id');
    $depart_par_id = session('depart_par_id');
    $company_id = session('company_id'); 
    
    if($crt_time>get_c_date()){
        $where = "is_del=1";
        if($lvl_id>5){              //小于AD级别的
            if($depart_id == 16){
                $where .= " and lvl_id=5 and (depart_id=$depart_id or depart_id=$depart_par_id)";
            }else{
                $project = M("Project")->where("is_del=1 and proj_id=$proj_id")->find();              //项目
                if(($project && $project['proj_mgr'] == $user_id)){            //如果申请人为项目经理或者aam以上，则提交给AD
                    $where .= " and lvl_id=5 and (depart_id=$depart_id or depart_id=$depart_par_id)";
                }else{
                    $str = " and user_id = ".$project['proj_mgr'];
                    if($type == 3 && $depart_id == 16 && $lvl_id>7){
                        $str = " and depart_id=$depart_id and lvl_id>5 and lvl_id<9";
                    }
                    $where .= $str;
                }
            }
        }elseif($lvl_id == 5){            //AD
            if($depart_id == 16 && $user_id!=$apply_uid && $crt_time>get_c_datea() && (!$approve || ($approve && $approve['aprv_user_id1']!=$approve['aprv_user_id2']))){
                $project = M("Project")->where("is_del=1 and proj_id=$proj_id")->find();              //项目
                $deaprt_ids = get_user_depart_ids($project['proj_mgr']);
                if($deaprt_ids){
                    $deaprt_ids_str = implode(",", $deaprt_ids);
                    $where .= " and lvl_id=5 and depart_id in ($deaprt_ids_str)";
                }
            }else{
                if($type == 3){         //报销单
                    $where .= " and user_id=".get_cw_userid();
                }elseif($type == 2){       //借款单
                    $where .= " and lvl_id=2 and company_id=$company_id";
                }
            }
        }elseif($user_id == get_cw_userid()){            //财务
            if($type == 3){         //报销单
                $where .= " and ((lvl_id=2 and company_id=$company_id) or user_id=".get_cn_userid().")";
            }elseif($type == 2){       //借款单
                $where .= " and user_id=".get_cn_userid();
            }
        }
        return $user_model->get_list($where,"user_id,real_name");
    }else{
        if(!is_numeric($proj_id) || !is_numeric($user_id))return false;
        $user_model = new \Home\Model\UserModel();
        $appr_model = new \Home\Model\ApproveModel();
        $depart_id = session('depart_id');
        $depart_par_id = session('depart_par_id');
        if(!$approve || $approve['aprv_result1'] == 0){
            $sql = "(select b.* from max_project a LEFT JOIN max_user b on b.user_id=a.proj_mgr where a.proj_id=$proj_id and a.is_del=1 and b.is_del=1 and lvl_id>4 and lvl_id<9)
            union (select  b.* from max_project_users a LEFT JOIN max_user b on b.user_id=a.user_id where a.proj_id=$proj_id and a.is_del=1 and b.is_del=1 and lvl_id>4 and lvl_id<9)";
            $list = $user_model->query($sql);              //项目经理
        }else{
            $aprv_type = $approve['aprv_type'];
            $num = 0;
            for ($i = 1; $i < 6; $i++) {
                if($lvl_id!=1){                                          //非管理员时
                    if($approve['aprv_user_id'.$i] == $user_id){
                        $num = $i+1;
                        if($approve['aprv_user_id'.$num] == $user_id){
                            $num += 1;
                        }
                        break;
                    }
                }else{
                    if($approve['aprv_result'.$i] == 0){
                        $num = $i;
                        break;
                    }
                }
            }
        
            if(array_key_exists($num, $appr_model->_type_appr[$aprv_type])){
                $lvl_id = $appr_model->_type_appr[$aprv_type][$num];
                if(is_array($lvl_id)){
                    $lvl_id_s = implode(",", $lvl_id);
                    $where = "is_del=1 and lvl_id in ($lvl_id_s)";
                }else{
                    $where = "is_del=1 and lvl_id=$lvl_id";
                }
        
                if($num < 3){
                    $where .= " and depart_id=$depart_par_id";
                }
        
                $where .= " and company_id=$company_id";
                $list = $user_model->get_list($where);
            }
        }
        
        return $list;
    }
}

/**
 * @desc 发送邮件
 * @param $to 接收方邮箱
 * @param $title 邮件标题
 * @param $content 邮件内容
 * @author maojingjing
 */
function send_email($to, $title, $content){
    include_once 'ThinkPHP/Library/Vendor/PHPMailer/PHPMailerAutoload.php';
    $mail = new PHPMailer();
    
    $mail = new PHPMailer(); //实例化
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = C('MAIL_USERNAME'); //发件人邮箱名
    $mail->Password = C('MAIL_PASSWORD') ; //163邮箱发件人授权密码
    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
    $mail->AddAddress($to,$to);
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject =$title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
    return($mail->Send());
}

/**
 * @desc 发送邮件
 * @param $toid 接收方id
 * @param $title 邮件标题
 * @param $content 邮件内容
 * @author maojingjing
 */
function send_email_bytoid($toid, $title, $content){
    $user_model = new \Home\Model\UserModel();
    $to = $user_model->get_one($toid);
    if($to && $to['email']){
        $ret = send_email($to['email'], $title, $content);
        return $ret;
    }
    return false;
}

function send_email_approve($toid,$title,$content,$url){
    if($toid != get_cw_userid()){
        $content = $content . "请点击：<a href='".'http://' . $_SERVER['HTTP_HOST'].$url."'>审批地址</a>";
        send_email_bytoid($toid,$title,$content);
    }
}

/**
 * @desc 判断对预算单的操作权限
 * @param $apply_userid 预算单申请人id
 * @param $userid 操作人id
 * @param $lvl_id 操作人级别id
 * @param $depart_id 操作人部门id
 * @param $depart_par_id 操作人部门的父部门id
 * @author maojingjing
 */
function check_acess_budget($apply_userid, $userid, $lvl_id, $depart_id, $depart_par_id){
    if($lvl_id < 5 || $apply_userid == $userid || $userid == 33){
        return true;
    }
    $user_model = new \Home\Model\UserModel();
    $apply_user = $user_model->get_one($apply_userid);
    
    $depart_model = new \Home\Model\DepartModel();
    $depart = $depart_model->get_one($apply_user['depart_id']);
    if(($apply_user['depart_id'] == $depart_id || $depart['depart_par_id'] == $depart_par_id) && $lvl_id < 9){
        return true;
    }
    return false;
}

/**  
 * @desc 选择下个审批人
 * @param $id 对象id
 * @param $obj_type 单据类型
 * @param $approver_id 审批表id
 * @param $lvl_id 用户级别
 * @param $user_id 用户id
 */
function choose_approval_user($id,$obj_type,$approver_id,$lvl_id,$user_id){
    $obj_model = new \Home\Model\BudgetModel();  //预算单
    $obj_str = "预算单";
    $obj_no = "bud_no";
    $obj_id = "bud_id";
    $obj_url = "Budget/info";
    if($obj_type == 2){                //借款单
        $obj_model = new \Home\Model\BorrowModel();
        $obj_str = "借款单";
        $obj_no = "borrow_no";
        $obj_id = "borrow_id";
        $obj_url = "Borrow/info";
    }elseif($obj_type == 3){                //报销单
        $obj_model = new \Home\Model\RecoupedModel();
        $obj_str = "项目报销单";
        $obj_no = "rec_no";
        $obj_id = "rec_id";
        $obj_url = "Recouped/info";
    }elseif($obj_type == 5){                //预算变更单
        $obj_model = new \Home\Model\ModifybudgetModel();
        $obj_str = "预算变更单";
        $obj_no = "mod_no";
        $obj_id = "mod_id";
        $obj_url = "Modifybudget/info";
    }elseif($obj_type == 6){                //平台报销单
        $obj_model = new \Home\Model\PfrecoupedModel();
        $obj_str = "个人报销单";
        $obj_no = "pf_no";
        $obj_id = "pf_id";
        $obj_url = "Pfrecouped/info";
    }elseif($obj_type == 7){                //平台报销单
        $obj_model = new \Home\Model\TbrecoupedModel();
        $obj_str = "TB报销单";
        $obj_no = "tb_no";
        $obj_id = "tb_id";
        $obj_url = "Tbrecouped/info";
    }
    $obj = $obj_model->get_one($id);
    if($obj){
        $app_model = new \Home\Model\ApproveModel();
        $approve = $app_model->getApproveSchedule($obj[$obj_no], $obj_type);
        
        //更新recouped表当前审批人
        $obj_model->startTrans();     //开启事务
        $ret = $obj_model->update_data(array('cur_approver_id'=>$approver_id), "$obj_id=$id");
        if($ret){
            $ret1 = $app_model->updateApproved($approve, $approver_id, $lvl_id, $user_id);
            if($ret && $ret1){
                logrecords("update", $app_model->get_tablename());          //日志
                $obj_model->commit();
                send_email_approve($approver_id, $obj_str."审批通知", "您有一个".$obj_str."审批请求！",U($obj_url,array('id'=>$id)));  //发邮件
                return true;
            }else{
                $obj_model->rollback();
            }
        }
    }
    return false;
}

/**
 * @desc 判断是否有查看全部单据的权限
 * @param $user_id 登录用户id
 */
function get_access_allvoucher($userid){
    $user = D('User')->get_one($userid);
    if($user['is_allvoucher']==1){
        return true;
    }
    return false;
}

/**
 * @desc 判断是否有查看媒介信息权限
 * @param $user_id 登录用户id
 */
function check_is_mj_show($userid){
    $user = D('User')->get_one($userid);
    if($user['is_mj_show']==1){
        return true;
    }
    return false;
}

/**
 * @desc 判断是否有创建编辑媒介信息权限
 * @param $user_id 登录用户id
 */
function check_is_mj_creat(){
    $user_id = session('user_id');
    $lvl_id = session('lvl_id');
    $depart_id = session('depart_id');
    if($lvl_id==2 || $depart_id==16 || $user_id==56){
        return true;
    }
    return false;
}

/**
 * @desc 判断是否显示选择审批人功能
 * @param $crt_user_id 创建人id
 * @param $user_id 审批人id
 * @param $lvl_id 审批人级别id
 * @param $approve 审批内容
 * @author maojingjing
 */
function check_is_show_app($crt_user_id, $user_id, $lvl_id, $approve, $crt_time){
    if($crt_time>get_c_date() || $approve['aprv_type'] == 6){
        $is_show_app = true;
        if($lvl_id == 2 || (($approve['aprv_type'] == 3 || $approve['aprv_type'] == 2 || $approve['aprv_type'] == 6 || $approve['aprv_type'] == 7 || $approve['se_id']) && $lvl_id == 4)){                       //总经理不需要提交审批人，自动提交
            return false;
        }
        return $is_show_app;
    }else{
        $is_show_app = false;
        if($lvl_id == 2 || $approve['result']==1){                       //总经理不需要提交审批人，自动提交
            return $is_show_app;
        }
        if($lvl_id == 1 || ($crt_user_id == $user_id  && $approve['aprv_result1'] == 0)){   //管理员或者发起者
            $is_show_app = true;
        }else{
            for ($i = 1; $i < 6; $i++) {
                if($approve['aprv_user_id'.$i]>0){
                    if($approve['aprv_user_id'.$i] == $user_id && $approve['aprv_result'.$i] == 1 && $approve['aprv_result'.($i+1)] == 0){
                        $is_show_app = true;
                        break;
                    }
                }
            }
        }
        return $is_show_app;
    }
}

/**
 * @desc 获取考勤审批人列表
 * @param $user_id 当前登录的用户Id
 * @param $lvl_id 当前登录的用户的级别
 * @author maojingjing
 */
function getAttApproverList($approve=null){
    $user_id = session('user_id');
    $lvl_id = session('lvl_id');
    $depart_id = session('depart_id');
    $depart_par_id = session('depart_par_id');
    $company_id = session('company_id');
    if(!$user_id || !$lvl_id) return false;
    $user_model = new \Home\Model\UserModel();
    
    $mcn_user_ids = array(531, 532, 533, 534, 535, 536);
    $where = "is_del=1";
    if(in_array($user_id, $mcn_user_ids)){         //福建考勤特殊功能
        if($user_id==531){
            $where .= " and user_id=24";          //至陈瑶
        }else{
            $where .= " and user_id=531";          //至陈瑶
        }
       
    }else{
        if($lvl_id>8){  //小于AM级别的先提交给AM至SAM之间的人
            $where .= " and lvl_id>4 and lvl_id<9 and (depart_id=$depart_id or depart_id=$depart_par_id)";
        }elseif($lvl_id>5 && $lvl_id<9){         //AM至SAM级别
            $where .= " and lvl_id=5 and (depart_id=$depart_id or depart_id=$depart_par_id)";
        }elseif($lvl_id == 5 || $user_id == get_cw_userid()){            //AD和财务部peter
            $where .= " and lvl_id=2 and company_id=$company_id";
        }elseif(($lvl_id == 3 && $user_id != get_cw_userid())|| $lvl_id == 4 ){
            $where .= " and user_id=".get_cw_userid();
        }
    }
    
    
    return $user_model->get_list($where,"user_id,real_name");
}

/**
 * @desc 获取个人报销审批人列表
 * @param $user_id 当前登录的用户Id
 * @param $lvl_id 当前登录的用户的级别
 * @author maojingjing
 */
function getPfApproverList($crt_time,$approve=null,$is_tb=0){
    $user_model = new \Home\Model\UserModel();
    $appr_model = new \Home\Model\ApproveModel();
    $user_id = session('user_id');
    $lvl_id = session('lvl_id');
    $depart_id = session('depart_id');
    $depart_par_id = session('depart_par_id');
    $company_id = session('company_id');
    if($crt_time>get_c_datef()){
        $where = "is_del=1";
        if($lvl_id>5){                    //小于AD级别的
            if($depart_id == 61 && $lvl_id>7){
                $where .= " and lvl_id=7 and depart_id=$depart_id";
            }else{
                $where .= " and lvl_id=5 and (depart_id=$depart_id or depart_id=$depart_par_id)";
            }
        }elseif($lvl_id == 5){            //AD
            $hrd = get_permission_users("HRD", "index");
            if($is_tb == 1 && $user_id!=$hrd){
                $where .= " and user_id=".get_permission_users("HRD", "index");
            }else{
                $where .= " and user_id=".get_cw_userid();
            }
        }elseif($lvl_id<5 && $lvl_id>2 ){            //财务
            if($user_id == get_cw_userid()){
                $where .= " and ((lvl_id=2 and company_id=$company_id) or user_id=".get_cn_userid().")";
            }else{
                $where .= " and user_id=".get_cw_userid();
            }
        }
        return $user_model->get_list($where,"user_id,real_name");
    }else{
        $aprv_type = 6;
        if(!$approve){
            $num = 1;
        }else{
            $num = 0;
            for ($i = 1; $i < 5; $i++) {
                if($approve['aprv_result'.$i] == 0){
                    if($approve['aprv_user_id'.$i] == $user_id){
                        $num = $i+1;
                    }else{
                        $num = $i;
                    }
                    break;
                }
            }
        }
        if(array_key_exists($num, $appr_model->_type_appr[$aprv_type])){
            if($num == 1 && $depart_id == 1){
                $where = "is_del=1 and lvl_id=3";
            }else{
                $lvl_id = $appr_model->_type_appr[$aprv_type][$num];
                if(is_array($lvl_id)){
                    $lvl_id_s = implode(",", $lvl_id);
                    $where = "is_del=1 and lvl_id in ($lvl_id_s)";
                }else{
                    $where = "is_del=1 and lvl_id=$lvl_id";
                }
                
                if($num==1){
                    $where .= " and (depart_id=$depart_id or depart_id=$depart_par_id)";
                }
            }
            $where .= " and company_id=$company_id";
            $list = $user_model->get_list($where);
            return $list;
        }
    }
}

/**
 * @desc 获取个人报销审批人列表
 * @param $user_id 当前登录的用户Id
 * @param $lvl_id 当前登录的用户的级别
 * @author maojingjing
 */
function getTbApproverList($approve=null){
    $user_model = new \Home\Model\UserModel();
    $appr_model = new \Home\Model\TbApproveModel();
    $user_id = session('user_id');
    $lvl_id = session('lvl_id');
    $depart_id = session('depart_id');
    $depart_par_id = session('depart_par_id');
    $company_id = session('company_id');
    $where = "is_del=1";
    if($lvl_id>5){                    //小于AD级别的
        if($depart_id == 61 && $lvl_id>7){
            $where .= " and lvl_id=7 and depart_id=$depart_id";
        }else{
            $where .= " and lvl_id=5 and (depart_id=$depart_id or depart_id=$depart_par_id)";
        }
    }elseif($lvl_id == 5 || $user_id == get_cw_userid()){            //AD或者财务
        $hrd = get_permission_users("HRD", "index");
        if($user_id!=$hrd){
            $where .= " and user_id=".get_permission_users("HRD", "index");
        }else{
            $where .= " and user_id=".get_zjl_userid();
        }
    }elseif($lvl_id==3 ){            //财务
        $where .= " and user_id=".get_cw_userid();
    }
    return $user_model->get_list($where,"user_id,real_name");
}

/**
 * @desc 获取个人报销审批人列表
 * @param $user_id 当前登录的用户Id
 * @param $lvl_id 当前登录的用户的级别
 * @author maojingjing
 */
function getTbRecApproverList($crt_user_id,$approve=null){
    $user_model = new \Home\Model\UserModel();
    $appr_model = new \Home\Model\TbApproveModel();
    $user_id = session('user_id');
    $lvl_id = session('lvl_id');
    $depart_id = session('depart_id');
    $depart_par_id = session('depart_par_id');
    $company_id = session('company_id');
    $where = "is_del=1";
    
    if($lvl_id>5){                    //小于AD级别的
        if($depart_id == 61 && $lvl_id>7){
            $where .= " and lvl_id=7 and depart_id=$depart_id";
        }else{
            $where .= " and lvl_id=5 and (depart_id=$depart_id or depart_id=$depart_par_id)";
        }
    }elseif($lvl_id == 5 || ($crt_user_id == get_cw_userid() && ($approve['aprv_user_id1']!=get_cw_userid() && $approve['aprv_user_id2']!=get_cw_userid()))){            //AD
        $hrd = get_permission_users("HRD", "index");
        if($user_id!=$hrd){
            $where .= " and user_id=".$hrd;
        }else{
            $where .= " and user_id=".get_cw_userid();
        }
    }elseif($lvl_id<5 && $lvl_id>2 ){            //财务
        if($user_id == get_cw_userid()){
            $where .= " and ((lvl_id=2 and company_id=$company_id) or user_id=".get_cn_userid().")";
        }else{
            $where .= " and user_id=".get_cw_userid();
        }
    }
    return $user_model->get_list($where,"user_id,real_name");
}


//是否具有管理员权限
function is_admin_power(){
    $lvl_id = session('lvl_id');
    if($lvl_id>0 && $lvl_id<3){
        return true;
    }
    return false;
}

//获取总经理助理的userid
function get_aide_userid(){
    $company_id = session('company_id');
    if($company_id == 1){
        return 132;
    }else{
        return 322;
    }
}

//是否具有考勤管理和加班管理查看的权限
function get_vo_power(){
    $user_id = session('user_id');
    if(is_admin_power() || $user_id==132 || $user_id==172 || $user_id==322){
        return true;
    }
    return false;
};

//获取出纳的userid
function get_cn_userid(){
    $company_id = session('company_id');
    $cn_userid = S('cache_cn'.$company_id);
    if(!$cn_userid){
        $per_model = new \Home\Model\PermissionsModel();
        $permission = $per_model->get_onebyname('financial_approve', 'cashier');
        if($permission){
            $list = M('User_permissions')->where(array("per_id"=>$permission['per_id']))->getField('user_id',true);
            foreach ($list as $value) {
                $user = M("User")->where(array('company_id'=>$company_id, 'user_id'=>$value))->find();
                if($user){
                    $cn_userid = $value;
                    S('cache_cn'.$company_id, $cn_userid);
                    break;
                }
            }
        }
    }
    return $cn_userid;
}

//获取总经理的userid
function get_zjl_userid(){
    $company_id = session('company_id');
    $zjl_userid = S('cache_zjl'.$company_id);
    if(!$zjl_userid){
        $user_id = M('User')->cache('cache_zjl'.$company_id)->where("lvl_id=2 and company_id=$company_id and is_del=1")->getField('user_id');
        $zjl_userid = $user_id;
    }
    return $zjl_userid;
}

//获取财务的userid
function get_cw_userid(){
    $company_id = session('company_id');
    $cw_userid = S('cache_cw'.$company_id);
    if(!$cw_userid){
        $per_model = new \Home\Model\PermissionsModel();
        $permission = $per_model->get_onebyname('financial_approve', 'finance');
        if($permission){
            $list = M('User_permissions')->where(array("per_id"=>$permission['per_id']))->getField('user_id',true);
            foreach ($list as $value) {
                $user = M("User")->where(array('company_id'=>$company_id, 'user_id'=>$value))->find();
                if($user){
                    $cw_userid = $value;
                    S('cache_cw'.$company_id, $cw_userid);
                }
            }
        }
    }
    return $cw_userid;
}

//获取出纳的userid
function get_seal_cn_userid(){
    $company_id = session('company_id');
    $per_model = new \Home\Model\PermissionsModel();
    $permission = $per_model->get_onebyname('financial_approve', 'seal');
    if($permission){
        $list = M('User_permissions')->where(array("per_id"=>$permission['per_id']))->getField('user_id',true);
        foreach ($list as $value) {
            $user = M("User")->where(array('company_id'=>$company_id, 'user_id'=>$value))->find();
            if($user){
                $cn_userid = $value;
            }
        }
    }
    return $cn_userid;
}

/**
 * @desc 获取审批人列表和title列表
 * @param $approve 当前审批表对象
 * @param $crt_user_id 创建该项审批的userid
 * @author maojingjing
 */
function getApprovers($approve, $crt_user_id){
    $user_model = new \Home\Model\UserModel();
    $proj_model = new \Home\Model\ProjectModel();
    
    $is_execute = false;
    if($approve['crt_time']>get_c_date()){
        $is_execute = true; 
    }
    
    $crt_user = $user_model->get_one($crt_user_id);
    if($approve['aprv_type'] == 3){             //报销单
        $approve_title_arr = array(1=>'项目经理', 2=>'部门领导', 3=>'财务', 4=>'总经理', 5=>'出纳');
        $approve_arr = array();
        if($is_execute){
            if($crt_user['depart_id'] == 16){
                if($approve['crt_time']>get_c_datea()){
                    if($crt_user['lvl_id']==5){
                        unset($approve_title_arr[1]);
                        unset($approve_title_arr[2]);
                    }else{
                        $approve_title_arr[1] = "媒介主管";
                        $approve_title_arr[2] = "项目部门领导";
                    }
                }else{
                    $approve_title_arr = array(1=>'媒介主管',2=>'项目经理', 3=>'部门领导', 4=>'财务', 5=>'总经理', 6=>'出纳');
                    if($crt_user['lvl_id']<8){              //小于媒介主管级别的
                        unset($approve_title_arr[1]);
                    }
                }
            }else{
                if($crt_user['lvl_id']>5){              //小于AD级别的
                    $project = $proj_model->where("is_del=1 and proj_id={$approve['proj_id']}")->find();              //项目
                    if($project && $project['proj_mgr'] == $crt_user_id){            //如果申请人为项目经理，则提交给AD
                        unset($approve_title_arr[1]);
                    }
                }elseif($crt_user['lvl_id'] == 5){            //AD
                    unset($approve_title_arr[1]);
                    unset($approve_title_arr[2]);
                }elseif($crt_user_id == get_cw_userid()){            //财务
                    unset($approve_title_arr[1]);
                    unset($approve_title_arr[2]);
                    unset($approve_title_arr[3]);
                }
            }
        }
    }elseif($approve['aprv_type'] == 1 || $approve['aprv_type'] == 5){      //预算单、预算变更单
        $approve_title_arr = array(1=>'项目经理', 2=>'部门领导', 3=>'总经理', 4=>'财务');
        $approve_arr = array();
        if($is_execute){
            unset($approve_title_arr[1]);
            if($crt_user['lvl_id'] == 5){            //AD
                unset($approve_title_arr[2]);
            }
        }
    }elseif($approve['aprv_type'] == 2){      //借款单
        $approve_title_arr = array(1=>'项目经理', 2=>'部门领导', 3=>'总经理', 4=>'财务', 5=>'出纳');
        $approve_arr = array();
        if($is_execute){
            if($crt_user['depart_id'] == 16 && $approve['crt_time']>get_c_datea()){
                if($crt_user['lvl_id']==5){
                    unset($approve_title_arr[1]);
                    unset($approve_title_arr[2]);
                }else{
                    $approve_title_arr[1] = "媒介主管";
                    $approve_title_arr[2] = "项目部门领导";
                }
            }else{
                if($crt_user['lvl_id']>5){              //小于AD级别的
                    $project = $proj_model->where("is_del=1 and proj_id={$approve['proj_id']}")->find();              //项目
                    if($project && $project['proj_mgr'] == $crt_user_id){            //如果申请人为项目经理，则提交给AD
                        unset($approve_title_arr[1]);
                    }
                }elseif($crt_user['lvl_id'] == 5){            //AD
                    unset($approve_title_arr[1]);
                    unset($approve_title_arr[2]);
                }
            }
        }
    }elseif($approve['aprv_type'] == 6){      //个人报销单
        $approve_title_arr = array(1=>'部门领导', 2=>'财务', 3=>'总经理', 4=>'出纳');
        $approve_arr = array();
        $pf = M("platform_recouped")->where(array("pf_no"=>$approve['aprv_no']))->find();
        
        if($pf['is_tb']){
            $hrd = $pf['user_id']!=get_permission_users('HRD', 'index');
            if($pf['user_id']!=$hrd && $approve['aprv_user_id1']!=$hrd){
                $approve_title_arr = array(1=>'部门领导', 2=>'HRD', 3=>'财务', 4=>'总经理', 5=>'出纳');
            }
        }
        if($is_execute){
            if($crt_user['depart_id'] == 61 && $approve['crt_time']>1500346265){         //行政部特别功能
                $approve_title_arr = array(1=>'部门主管', 2=>'部门领导', 3=>'财务', 4=>'总经理', 5=>'出纳');
                if($crt_user['lvl_id']<8){              //小于AM级别的
                    unset($approve_title_arr[1]);
                }
            }else{
                if($crt_user['lvl_id'] == 5){            //AD
                    unset($approve_title_arr[1]);
                }elseif($crt_user['depart_id'] == 1 || $crt_user['depart_id'] == 44 || $crt_user['depart_id'] == 47){            //财务部
                    if($crt_user_id == get_cw_userid()){
                        unset($approve_title_arr[1]);
                    }
                    unset($approve_title_arr[2]);
                }
            }
        }
    }elseif($approve['aprv_type'] == 7){      //TB报销单
        $approve_title_arr = array(1=>'部门领导', 2=>'人事总监', 3=>'财务', 4=>'总经理', 5=>'出纳');
        $approve_arr = array();
        $hrd = get_permission_users('HRD', 'index');
        if($crt_user['lvl_id'] == 5 || $approve['aprv_user_id1']==$hrd){            //AD
            unset($approve_title_arr[1]);
            if($crt_user_id==$hrd){
                unset($approve_title_arr[2]);
            }
        }elseif($crt_user['depart_id'] == 1 || $crt_user['depart_id'] == 44 || $crt_user['depart_id'] == 47){            //财务部
            if($crt_user_id == get_cw_userid()){
                unset($approve_title_arr[1]);
            }
            unset($approve_title_arr[2]);
        }
    }
    
    $is_zjl = true;
    for ($i = 1; $i < 7; $i++) {
        if($approve['aprv_user_id'.$i]>0){
            $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
            if($app_user){
                $approve_arr[$i]['user_real_name'] = $app_user['real_name'];
                $approve_arr[$i]['user_lvl_id'] = $app_user['lvl_id'];
                $approve_arr[$i]['user_id'] = $app_user['user_id'];
                $approve_arr[$i]['result'] = $approve['aprv_result'.$i];
                $approve_arr[$i]['time'] = $approve['aprv_time'.$i];
                $approve_arr[$i]['opinion'] = $approve['aprv_opinion'.$i];
                if($app_user['lvl_id'] == 4 && $approve['aprv_user_id'.($i-1)]!=get_zjl_userid()){
                    $is_zjl = false;
                }
                
            }
        }else{
            break;
        }
    }
    
    if($is_zjl == false){
        if($approve['aprv_type'] == 3){
            if($crt_user['depart_id'] == 16){
                unset($approve_title_arr[5]);
            }else{
                unset($approve_title_arr[4]);
            }
        }elseif($approve['aprv_type'] == 6){
            unset($approve_title_arr[3]);
        }elseif($approve['aprv_type'] == 7){
            unset($approve_title_arr[$i-1]);
        }
    }
    
    //平台报销单或者借款单，并且项目经理是领导级别
    if($approve['aprv_type'] == 3 or $approve['aprv_type'] == 2){
        if($crt_user['depart_id'] == 16){
            if($approve['crt_time']<get_c_datea()){
                if($approve_arr[1]['user_lvl_id'] == 5){
                    unset($approve_title_arr[3]);
                }elseif($approve_arr[2]['user_lvl_id'] == 5){
                    unset($approve_title_arr[1]);
                }
            }
        }else{
            if($approve_arr[1]['user_lvl_id'] == 5 && isset($approve_title_arr[1])){
                unset($approve_title_arr[2]);
            }
        }
    }
    $list['approve_title_arr'] = array_values($approve_title_arr);
    $list['approve_user_arr'] = array_values($approve_arr);
    return $list;
}

function get_c_date(){
    return 1490675400;
}

function get_c_datef(){
    return 1490752800;
}

function get_c_datea(){
    return 1508212200;
}

/**
 * @desc 获取加班审批人列表
 * @author maojingjing
 */
function getOvApproverList($approve=null){
    $user_id = session('user_id');
    $lvl_id = session('lvl_id');
    $depart_id = session('depart_id');
    $depart_par_id = session('depart_par_id');
    $company_id = session('company_id');
    if(!$user_id || !$lvl_id) return false;
    $user_model = new \Home\Model\UserModel();

    $mcn_user_ids = array(531, 532, 533, 534, 535, 536);
    $where = "is_del=1";
    if(in_array($user_id, $mcn_user_ids)){         //福建考勤特殊功能
        if($user_id==531){
            $where .= " and user_id=24";          //至陈瑶
        }else{
            $where .= " and user_id=531";          //至陈瑶
        }
         
    }else{
        if($lvl_id>8){  //小于AM级别的先提交给AM至SAM之间的人
            $where .= " and lvl_id>4 and lvl_id<9 and (depart_id=$depart_id or depart_id=$depart_par_id)";
        }elseif($lvl_id>5 && $lvl_id<9){         //AM至SAM级别
            $where .= " and lvl_id>4 and lvl_id<$lvl_id and (depart_id=$depart_id or depart_id=$depart_par_id)";
        }elseif($lvl_id == 5 || $user_id == get_cw_userid()){            //AD和财务部peter
            $where .= " and lvl_id=2 and company_id=$company_id";
        }elseif(($lvl_id == 3 && $user_id != get_cw_userid())){
            $where .= " and user_id=".get_cw_userid();
        }elseif($lvl_id == 4){
            $where .= " and lvl_id=3 and company_id=$company_id";
        }
    }

    return $user_model->get_list($where,"user_id,real_name");
}

/**
 * @desc 判断是否有权限访问该页面
 * @author maojingjing
 */
function check_permission_access($user_id, $lvl_id){
    return check_permission_byca(CONTROLLER_NAME, ACTION_NAME, $user_id, $lvl_id);
}

/**
 * @desc 判断是否有权限显示该左侧菜单
 * @author maojingjing
 */
function check_permission_left($controller_name, $action_name){
    $user_id = session('user_id');
    $lvl_id = session('lvl_id');
    return check_permission_byca($controller_name, $action_name, $user_id, $lvl_id);
}

/**
 * @desc 判断某个人对应某个操作是否有权限
 * @author maojingjing
 */
function check_permission_byca($controller_name, $action_name, $user_id, $lvl_id){
    if($lvl_id == 1) return true;                 //管理员有全部权限
    $per_model = new \Home\Model\PermissionsModel();
    $permission = $per_model->get_onebyname($controller_name, $action_name);
    if($permission){
        $user_per_model = new \Home\Model\UserPermissionsModel();
        $pers = $user_per_model->get_list("user_id=$user_id");
        if($pers){
            foreach ($pers as $row) {
                if($row['per_id'] == $permission['per_id']){
                    return true;
                }
            }
        }else{
            //若未设置过权限，则根据用户级别显示级别默认权限
            if($permission['is_'.$lvl_id]){
                return true;
            }
        }
        return false;
    }else{
        return true;
    }
}

function get_left_modules($user_id, $lvl_id){
    $per_model = new \Home\Model\PermissionsModel();
    $user_per_model = new \Home\Model\UserPermissionsModel();
    $user_per_count = $user_per_model->where("user_id=$user_id")->count();
    if($user_per_count){
        $permissions = $per_model->get_list("is_del=1 and pid=0 and is_module=1");
        foreach ($permissions as $key => $row) {
            $per = $user_per_model->get_onebywhere("user_id=$user_id and per_id={$row['per_id']}");
            if(!$per){
                unset($permissions[$key]);
            }
        }
    }else{
        //若未设置过权限，则根据用户级别显示级别默认权限
        $where = "is_del=1 and pid=0 and is_module=1";
        if($lvl_id!=1){
            $where .= " and is_$lvl_id=1";
        }
        $permissions = $per_model->get_list($where);
    }
    
    foreach ($permissions as &$row1) {
        $row1['css'] = html_entity_decode($row1['css']);
    }
    
    return $permissions;
}

function check_is_over($proj_id){
    $proj = M('Project')->where(array('proj_id'=>$proj_id))->find();
    if($proj && $proj['is_over']==1){
        return true;
    }
    return false;
}

function get_user_depart_ids($user_id){
    $user = M("User")->where(array("user_id"=>$user_id))->find();
    $depart = M("Depart")->where(array("depart_id"=>$user['depart_id']))->find();
    $deaprt_id_arr = array($user['depart_id']);
    if($depart && $depart['depart_par_id']){
        array_push($deaprt_id_arr, $depart['depart_par_id']);
    }
    return $deaprt_id_arr;
}

function get_permission_users($controller_name, $action_name){
    $company_id = session('company_id');
    $per_model = new \Home\Model\PermissionsModel();
    $permission = $per_model->get_onebyname($controller_name, $action_name);
    $user_id = 0;
    if($permission){
        $user_per_model = new \Home\Model\UserPermissionsModel();
        $list = $user_per_model->where("per_id=".$permission['per_id'])->getField("user_id", true);
        foreach ($list as $value) {
            $user = M("User")->where(array('company_id'=>$company_id, 'user_id'=>$value))->find();
            if($user){
                $user_id = $value;
            }
        }
    }
    return $user_id;
}

/**
 * @desc 判断该用户是否在审批用户中
 */
function checkInTbApproverList($user_id){
    if(get_access_allvoucher($user_id)) return true;
    $where = " aprv_user_id1 = $user_id or aprv_user_id2 = $user_id or aprv_user_id3 = $user_id";
    $app_model = new \Home\Model\TbApproveModel();
    if($app_model->where($where)->find()){
        return true;
    }
    return false;
}

function getTbApprovers($approve, $crt_user_id){
    $user_model = new \Home\Model\UserModel();
    $proj_model = new \Home\Model\ProjectModel();

    $crt_user = $user_model->get_one($crt_user_id);
    $approve_title_arr = array(1=>'部门领导', 2=>'人事总监', 3=>'总经理');
    $approve_arr = array();
    if($crt_user['depart_id'] == 60){
        unset($approve_title_arr[2]);
    }
    if($crt_user['lvl_id'] == 5 || $crt_user['user_id'] == get_cw_userid()){
        unset($approve_title_arr[1]);
    }

    for ($i = 1; $i < 4; $i++) {
        if($approve['aprv_user_id'.$i]>0){
            $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
            if($app_user){
                $approve_arr[$i]['user_real_name'] = $app_user['real_name'];
                $approve_arr[$i]['user_lvl_id'] = $app_user['lvl_id'];
                $approve_arr[$i]['user_id'] = $app_user['user_id'];
                $approve_arr[$i]['result'] = $approve['aprv_result'.$i];
                $approve_arr[$i]['time'] = $approve['aprv_time'.$i];
                $approve_arr[$i]['opinion'] = $approve['aprv_opinion'.$i];
            }
        }else{
            break;
        }
    }   
    $list['approve_title_arr'] = array_values($approve_title_arr);
    $list['approve_user_arr'] = array_values($approve_arr);
    return $list;
}

function num2rmb($number = 0, $int_unit = '元', $is_round = TRUE, $is_extra_zero = FALSE)
{
    // 将数字切分成两段
    $parts = explode('.', $number, 2);
    $int = isset($parts[0]) ? strval($parts[0]) : '0';
    $dec = isset($parts[1]) ? strval($parts[1]) : '';

    // 如果小数点后多于2位，不四舍五入就直接截，否则就处理
    $dec_len = strlen($dec);
    if (isset($parts[1]) && $dec_len > 2)
    {
        $dec = $is_round
        ? substr(strrchr(strval(round(floatval("0.".$dec), 2)), '.'), 1)
        : substr($parts[1], 0, 2);
    }

    // 当number为0.001时，小数点后的金额为0元
    if(empty($int) && empty($dec))
    {
        return '零';
    }

    // 定义
    $chs = array('0','壹','贰','叁','肆','伍','陆','柒','捌','玖');
    $uni = array('','拾','佰','仟');
    $dec_uni = array('角', '分');
    $exp = array('', '万');
    $res = '';

    // 整数部分从右向左找
    for($i = strlen($int) - 1, $k = 0; $i >= 0; $k++)
    {
        $str = '';
        // 按照中文读写习惯，每4个字为一段进行转化，i一直在减
        for($j = 0; $j < 4 && $i >= 0; $j++, $i--)
        {
            $u = $int{$i} > 0 ? $uni[$j] : ''; // 非0的数字后面添加单位
            $str = $chs[$int{$i}] . $u . $str;
        }
        //echo $str."|".($k - 2)."<br>";
        $str = rtrim($str, '0');// 去掉末尾的0
        $str = preg_replace("/0+/", "零", $str); // 替换多个连续的0
        if(!isset($exp[$k]))
        {
            $exp[$k] = $exp[$k - 2] . '亿'; // 构建单位
        }
        $u2 = $str != '' ? $exp[$k] : '';
        $res = $str . $u2 . $res;
    }

    // 如果小数部分处理完之后是00，需要处理下
    $dec = rtrim($dec, '0');

    // 小数部分从左向右找
    if(!empty($dec))
    {
        $res .= $int_unit;

        // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求
        if ($is_extra_zero)
        {
            if (substr($int, -1) === '0')
            {
                $res.= '零';
            }
        }

        for($i = 0, $cnt = strlen($dec); $i < $cnt; $i++)
        {
            $u = $dec{$i} > 0 ? $dec_uni[$i] : ''; // 非0的数字后面添加单位
            $res .= $chs[$dec{$i}] . $u;
        }
        $res = rtrim($res, '0');// 去掉末尾的0
        $res = preg_replace("/0+/", "零", $res); // 替换多个连续的0
    }
    else
    {
        $res .= $int_unit . '整';
    }
    return $res;
}

/**
 * @desc 获取个人报销审批人列表
 * @param $user_id 当前登录的用户Id
 * @param $lvl_id 当前登录的用户的级别
 * @author maojingjing
 */
function getSealApproverList($approve=null){
    $user_model = new \Home\Model\UserModel();
    $appr_model = new \Home\Model\TbApproveModel();
    $user_id = session('user_id');
    $lvl_id = session('lvl_id');
    $depart_id = session('depart_id');
    $depart_par_id = session('depart_par_id');
    $company_id = session('company_id');
    $where = "is_del=1";
    if($lvl_id>5){                    //小于AD级别的
        $where .= " and lvl_id=5 and (depart_id=$depart_id or depart_id=$depart_par_id)";
    }elseif($lvl_id == 5){            //AD或者财务
        $where .= " and user_id=".get_cw_userid();
    }elseif($user_id == get_cw_userid()){            //财务
        $where .= " and user_id=".get_zjl_userid();
        //$where .= " and user_id=".get_seal_cn_userid();
    }
    return $user_model->get_list($where,"user_id,real_name");
}

function getSealApprovers($approve, $crt_user_id){
    $user_model = new \Home\Model\UserModel();
    $proj_model = new \Home\Model\ProjectModel();

    $crt_user = $user_model->get_one($crt_user_id);
    $approve_title_arr = array(1=>'部门领导', 2=>'财务总监', 3=>'总经理', 4=>'出纳');
    $approve_arr = array();
    if($crt_user['lvl_id'] == 5 || $crt_user['user_id'] == get_cw_userid()){
        unset($approve_title_arr[1]);
    }

    for ($i = 1; $i < 5; $i++) {
        if($approve['aprv_user_id'.$i]>0){
            $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
            if($app_user){
                $approve_arr[$i]['user_real_name'] = $app_user['real_name'];
                $approve_arr[$i]['user_lvl_id'] = $app_user['lvl_id'];
                $approve_arr[$i]['user_id'] = $app_user['user_id'];
                $approve_arr[$i]['result'] = $approve['aprv_result'.$i];
                $approve_arr[$i]['time'] = $approve['aprv_time'.$i];
                $approve_arr[$i]['opinion'] = $approve['aprv_opinion'.$i];
            }
        }else{
            break;
        }
    }
    $list['approve_title_arr'] = array_values($approve_title_arr);
    $list['approve_user_arr'] = array_values($approve_arr);
    $list['type'] = 'seal';
    return $list;
}
/**
 * @desc 判断该用户是否在审批用户中
 */
function checkInSealApproverList($user_id){
    if(get_access_allvoucher($user_id)) return true;
    $where = " aprv_user_id1 = $user_id or aprv_user_id2 = $user_id or aprv_user_id3 = $user_id";
    $app_model = new \Home\Model\SealApproveModel();
    if($app_model->where($where)->find()){
        return true;
    }
    return false;
}
?>