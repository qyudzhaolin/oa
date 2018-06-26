<?php
/**
 * OA系统--平台报销单
 */
namespace Home\Controller;
use Think\Page;
use Think\Model;
class PfrecoupedController extends BaseController{
    
    public $check_access = true;
    public $head_title = '个人报销单';
    
    public function indexAction(){
        /* if(session('depart_type')!=2){
            redirect(U('Recouped:index'), 2, '您没有权限进入平台报销单!');
        } */
        
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\ApproveModel();
        $user_model = new \Home\Model\UserModel();
        $recouped_model = new \Home\Model\PfrecoupedModel();
        if(get_access_allvoucher($user_id)){
            $where = "is_del = 1";
            //获取所属公司下属的项目相关预算单
            if($lvl_id!=2){
                $where .= " and user_id in (".$user_model->get_wherebycompanyid(session('company_id')).")";
            }
        }else{
            $where = "is_del = 1 and (user_id = $user_id";
            
            //获取归该用户审批的报销单列表
            $awhere = $app_model->get_whereaprrove($user_id, 6);
            $where .= " or pf_no in ($awhere)";
            $where .= ")";
        }
        
        //状态检索
        $way = I('get.way',0);
        if($way==1){
            $where .= " and (result=1 or result=-1)";
        }elseif($way==2){
            $where .= " and result=0";
        }
        
        //日期检索
        $start_date = I('get.start_date','','addslashes');
        $end_date = I('get.end_date','','addslashes');
        if($start_date){
            $start_date = strtotime($start_date);
            $where .= " and crt_time>='$start_date'";
        }
        if($end_date){
            $end_date = strtotime($end_date." 23:59:59");
            $where .= " and crt_time<='$end_date'";
        }
        
        //关键词检索
        $keyword = I('get.keyword','','addslashes');
        if($keyword){
            $ulist = $user_model->get_list("is_del = 1 and real_name like '%$keyword%'","user_id");          //检索项目列表
            if($ulist){
                foreach ($ulist as $urow) {
                    $pwhere .= $urow['user_id'].",";
                }
                $pwhere = rtrim($pwhere, ",");
                if($pwhere){
                    $where .= " and user_id in ($pwhere)";
                }
            }
        }
        $count = $recouped_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $recouped_model->where($where)->order("pf_id desc")->limit($page->firstRow.','.$page->listRows)->select();
        $depart_model = new \Home\Model\DepartModel();
        
        foreach ($list as &$row) {
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            $row['borrow_way_name'] = $row['borrow_way'] == 1 ? "现金" : "转账" ;
            if($row['borrow_way']==1){
                $row['borrow_way_name'] = "现金";
            }elseif($row['borrow_way']==2){
                $row['borrow_way_name'] = "转账";
            }else{
                $row['borrow_way_name'] = "其他";
            }
            
            $user = $user_model->get_one($row['user_id']);               //申请人员
            if($user){
                $row['real_name'] = $user['real_name'];
                if($user['depart_id']){                          //部门名称
                    $depart = $depart_model->get_one($user['depart_id']);
                    if($depart){
                        $row['depart_name'] = $depart['depart_name'];
                    }
                }
            }
            
            if($row['result'] == 1){
                $row['result_info'] = '流程已完成';
            }else{
                //获取审批状态
                $approve = $app_model->getApproveSchedule($row['pf_no'], 6);
                for ($i = 1; $i < 6; $i++) {
                    if($approve['aprv_user_id'.$i] == $row['cur_approver_id']){
                        $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                        $row['result_info'] = '等待审批';
                        if($approve['aprv_result'.$i] == 1){
                            $row['result_info'] = '同意';
                        }elseif($approve['aprv_result'.$i] == -1){
                            $row['result_info'] = '不同意';
                        }
                        
                        $row['result_info'] = $row['result_info']."（{$app_user['real_name']}）";
                        
                        break;
                    }
                }
            }
            
            
            //操作动作
            $row['action'] = "info";
            if($lvl_id == 1 || ($row['user_id'] == $user_id && $i==1 && $approve['aprv_result1']!=1)){
                $row['action'] = "add";
            }
        }
        $export = check_permission_left('Pfrecouped', 'export');
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('user_id', $user_id);
        $this->assign('lvl_id', $lvl_id);
        $this->assign('export', $export);
        $this->display('Pfrecouped:index');
    }
    
    /*  
     *  新增、修改用户页
     **/
    public function addAction(){
        $recouped_model = new \Home\Model\PfrecoupedModel();
        $money_model = new \Home\Model\MoneyModel();
        
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        $depart_id = session('depart_id');
        
        if(IS_POST){
            //$data['get_user'] = I('post.get_user');
            $data['get_type'] = I('post.get_type');
            $data['borrow_way'] = I('post.borrow_way');
            $data['bnk_branch'] = I('post.bnk_branch');
            $data['bnk_acct'] = I('post.bnk_acct');
            $id = I("post.pf_id");
            $fund_str = I("post.fund_str");
            $data['tot_amt'] = I('post.tot_amt');
            $data['tot_amt_d'] = I('post.tot_amt_d');
            $cur_approver_id = $data['cur_approver_id'] = I('post.cur_approver_id');
            $data['is_tb'] =I('post.is_tb'); 
            
            //基础数据验证
            if($data['get_type'] == 1){
                $data['get_id'] = I('post.get_id');
                if(!$data['get_id']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请填写收款人！'));
            }
            if($data['borrow_way'] != '1' && $data['borrow_way'] != '2' && $data['borrow_way'] != '3') $this->ajaxReturn(array('status'=>0, 'msg'=>'借款方式不正确！'));
            if($data['borrow_way'] == '2'){
                if(!$data['bnk_branch']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请填写银行！'));
                if(!$data['bnk_acct']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请填写银行账号！'));
            }elseif($data['borrow_way'] == '3'){
                $data['other'] = I('post.other');
                $data['bnk_branch'] = '';
                $data['bnk_acct'] = '';
                if(!$data['other']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请填写其他的支付信息！'));
            }
            if(!$data['cur_approver_id'] && !$id) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            
            //判断是否可以更新
            $app_model = new \Home\Model\ApproveModel();
            $approve = null;
            $recouped = null;
            $action = '';
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $recouped = $recouped_model->get_one($id);
                if(!$recouped){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'个人报销单不存在！'));
                }
                $approve = $app_model->getApproveSchedule($recouped['pf_no'], 6);
                if(session('lvl_id')!=1){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                    }
                }
            }
            
            //新增、更新数据
            $recouped_model->startTrans();     //开启事务
            if($id){
                $ret = $recouped_model->update_data($data, "pf_id = $id");            //更新报销单表
                if($ret && $recouped['cur_approver_id'] != $data['cur_approver_id']){
                    $ret3 = $app_model->updateApproved($approve, $data['cur_approver_id'], $lvl_id, $user_id);
                }else{
                    $ret3 = true;
                }
                $action = 'update';
            }else{
                $data['user_id'] = $this->user_id;
                $data['crt_user_id'] = $this->user_id;
                $data['pf_no'] = $this->getBorrowNo();
                $ret = $recouped_model->insert_data($data);                               //插入报销单表
                $ret3 = true;
                $action = 'insert';
            }  
            $msg = "保存失败！";                                      //未完成
            if($ret && $ret3){  
                if($action == 'update'){
                    $moneys = $money_model->get_list("obj_id = $id and obj_type=3 and is_del=1");
                    $ret2 = $money_model->update_data(array('is_del'=>-1), "obj_id = $id and obj_type=3");
                    $ret = $id;
                }else{
                    //提交审批表
                    $ret2 = $app_model->insert_data(array('aprv_no'=>$data['pf_no'],'aprv_type'=>6,'aprv_user_id1'=>$data['cur_approver_id']));
                }
                if($ret2){
                    $issubmit = true;
                    $fund_str = str_replace("&amp;", "&", $fund_str);
                    $fund_array = explode(";", $fund_str);
                              
                    foreach ($fund_array as $f_row) {
                        if($f_row && $f_arr = explode("^", $f_row)){
                            if($f_arr[1] == "0.00"){
                                continue;
                            }
                            $ret1 = $money_model->add(array('obj_id'=>$ret,'obj_type'=>3, 'money'=>$f_arr[0], 'usable_money'=>$f_arr[0], 'comm'=>$f_arr[1], 'file_id'=>$f_arr[2], 'is_del'=>1));
                            if(!$ret1){
                                $issubmit = false;
                                break;
                            }
                        }
                    }
                    
                    if($issubmit){
                        logrecords($action, $recouped_model->get_tablename());          //日志
                        if($recouped_model->commit()){
                            if($action == 'insert' || ($action == 'update' && $recouped['cur_approver_id'] != $data['cur_approver_id'])){
                                send_email_approve($data['cur_approver_id'], '个人报销单审批通知', "您有一个个人报销单审批请求！",U("Pfrecouped/info",array('id'=>$ret)));   //发邮件
                            }
                            $this->ajaxReturn(array('status'=>1));
                        }
                    }
                }
            }
            $recouped_model->rollback();
            $this->ajaxReturn(array('status'=>0, 'msg'=>$msg));
        }
        
        $id = I('get.id');
        $app_real_name = session('real_name');
        if($id){
            $id = intval($id);
            $recouped = $recouped_model->get_one($id);
            if($recouped){
                //判断是否有权限进行编辑操作
                if($recouped['user_id'] != $this->user_id && session('lvl_id')!=1){
                    redirect(U('Recouped:index'), 2, '您没有权限对该个人报销单进行操作!');
                }
                
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($recouped['pf_no'], 6);
                //审批已经开始必须进入详情页
                if($approve['aprv_result1']>0){
                    redirect(U("Pfrecouped/info")."?id=".$id);
                }
                
                //获取款项列表
                if($recouped['result'] == -1){
                    $money_list = $money_model->get_list("obj_id=$id and obj_type=3 and is_cancel=1");
                }else{
                    $money_list = $money_model->get_list("obj_id=$id and obj_type=3 and is_del=1");
                }
                
                $file_model = new \Home\Model\FileModel();
                foreach ($money_list as &$row) {
                    if($row['file_id']){
                        $file = $file_model->get_one($row['file_id']);
                        if($file){
                            $row['file_name'] = $file['file_name'];
                        }
                    }
                }
                
                $this->assign('money_list', $money_list);
                
                //申请人
                $user_model = new \Home\Model\UserModel();
                $apply_user = $user_model->get_one($recouped['user_id']);
                $app_real_name = $apply_user['real_name'];
                $depart_id = $apply_user['depart_id'];
                
                //判断前期未填收款人
                if($recouped['borrow_way']==2 && !$recouped['get_user']){
                    $recouped['get_user'] = $apply_user['real_name'];
                }
                
                for ($i = 1; $i < 6; $i++) {
                    if($approve['aprv_user_id'.$i]>0){
                        $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                        if($app_user){
                            $approve['user_real_name'.$i] = $app_user['real_name'];
                            $approve['user_lvl_id'.$i] = $app_user['lvl_id'];
                        }
                    }else{
                        break;
                    }
                }
                
                //供应商列表
                if($recouped['get_type'] == 1){
                    $suppler = M('Supplier')->where("sup_id={$recouped['get_id']} and is_del=1")->find();
                    $this->assign('suppler', $suppler);
                }
                
                //是否显示选择审批人功能
                $is_show_app = false;
                if($approve['result'] != -1){
                    $is_show_app = check_is_show_app($recouped['user_id'], $user_id, $lvl_id, $approve, $recouped['crt_time']);
                }
                
                //审批人列表
                if($is_show_app == true){
                    $approve_users = getPfApproverList($recouped['crt_time'],$approve, $recouped['is_tb']);
                }
                
                //是否重新发启按钮
                $is_restart = false;
                if($approve['result'] == -1 && ($lvl_id==1 || $recouped['user_id']==$user_id)){
                    $is_restart = true;
                }
                
                //审批进度条内容
                $approve_arr = getApprovers($approve, $recouped['crt_user_id']);
                $this->assign('approve_arr', $approve_arr);
                $this->assign('recouped', $recouped);
            }
        }else{
            $is_show_app = true;
            $is_restart = false;
            $approve_users = getPfApproverList(time());
        }
        
        //获取所在部门
        $depart_model = new \Home\Model\DepartModel();
        $depart = $depart_model->get_one($depart_id);
        
        //获取该用户涉及到的项目列表
        $this->assign('is_show_app', $is_show_app);
        $this->assign('is_restart', $is_restart);
        $this->assign('app_real_name', $app_real_name);
        $this->assign('depart', $depart);
        $this->assign('approve_users', $approve_users);
        $this->assign('user_id', $user_id);
        $this->display('Pfrecouped:add');
    }
    
    /*
     *  信息页
     **/
    public function infoAction(){
        $id = I('get.id');
        if($id){
            $id = intval($id);
            $recouped_model = new \Home\Model\PfrecoupedModel();
            $money_model = new \Home\Model\MoneyModel();
            $user_model = new \Home\Model\UserModel();
            $recouped = $recouped_model->get_one($id);
            $file_model = new \Home\Model\FileModel();
            if($recouped){
                if($recouped['crt_time']>get_c_datef()){
                    $user_id = $this->user_id;
                    $lvl_id = session('lvl_id');
                    $depart_id = session('depart_id');
                    
                    //判断是否有权限进入
                    if($recouped['user_id']!=$user_id && !checkInApproverList($user_id,$recouped['pf_no'],6) && $depart_id!=1){         
                        redirect(U('Pfrecouped:index'), 2, '您没有权限对该个人报销单进行操作!');
                    }
                    
                    //获取款项列表
                    if($recouped['result'] == -1){
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=3 and is_cancel=1");
                    }else{
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=3 and is_del=1");
                    }
                    foreach ($money_list as &$row) {
                        if($row['file_id']){
                            $file = $file_model->get_one($row['file_id']);
                            if($file){
                                $row['file_name'] = $file['file_name'];
                                $row['file_url'] = C('IMG_DOMAIN').$row['file_name'];
                            }
                        }
                    }
                    
                    //供应商列表
                    if($recouped['get_type'] == 1){
                        $suppler = M('Supplier')->where("sup_id={$recouped['get_id']} and is_del=1")->find();
                        $this->assign('suppler', $suppler);
                    }
                    
                    //申请人
                    $apply_user = $user_model->get_one($recouped['user_id']);
                    
                    //判断前期未填收款人
                    if($recouped['borrow_way']==2 && !$recouped['get_user'] && $recouped['get_type']==2){
                        $recouped['get_user'] = $apply_user['real_name'];
                    }
                    
                    //申请人获取所在部门
                    $depart_model = new \Home\Model\DepartModel();
                    $depart = $depart_model->get_one($apply_user['depart_id']);
                    
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($recouped['pf_no'], 6);
                    $approve_arr = getApprovers($approve, $recouped['crt_user_id']);
                    
                    //是否显示选择审批人功能
                    $is_show_app = check_is_show_app($recouped['user_id'], $user_id, $lvl_id, $approve, $recouped['crt_time']);
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getPfApproverList($recouped['crt_time'],$approve, $recouped['is_tb']);
                        $this->assign('approve_users', $approve_users);
                    }
                    
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $recouped['user_id']==$user_id)){
                        $is_restart = true;
                    }
                    
                    $this->assign('recouped', $recouped);
                    $this->assign('money_list', $money_list);
                    $this->assign('apply_user', $apply_user);
                    $this->assign('depart', $depart);
                    $this->assign('approve_arr', $approve_arr);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('user_id', $this->user_id);
                    $this->assign('lvl_id', $lvl_id);
                    $this->display('Pfrecouped:info');
                }else{
                    $user_id = $this->user_id;
                    $lvl_id = session('lvl_id');
                    $depart_id = session('depart_id');
                    
                    //判断是否有权限进入
                    if($recouped['user_id']!=$user_id && !checkInApproverList($user_id,$recouped['pf_no'],6) && $depart_id!=1){
                        redirect(U('Pfrecouped:index'), 2, '您没有权限对该个人报销单进行操作!');
                    }
                    
                    //获取款项列表
                    if($recouped['result'] == -1){
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=3 and is_cancel=1");
                    }else{
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=3 and is_del=1");
                    }
                    foreach ($money_list as &$row) {
                        if($row['file_id']){
                            $file = $file_model->get_one($row['file_id']);
                            if($file){
                                $row['file_name'] = $file['file_name'];
                                $row['file_url'] = C('IMG_DOMAIN').$row['file_name'];
                            }
                        }
                    }
                    
                    //申请人
                    $apply_user = $user_model->get_one($recouped['user_id']);
                    
                    //判断前期未填收款人
                    if($recouped['borrow_way']==2 && !$recouped['get_user']){
                        $recouped['get_user'] = $apply_user['real_name'];
                    }
                    
                    //申请人获取所在部门
                    $depart_model = new \Home\Model\DepartModel();
                    $depart = $depart_model->get_one($apply_user['depart_id']);
                    
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($recouped['pf_no'], 6);
                    for ($i = 1; $i < 5; $i++) {
                        if($approve['aprv_user_id'.$i]>0){
                            $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                            if($app_user){
                                $approve['user_real_name'.$i] = $app_user['real_name'];
                                $approve['user_lvl_id'.$i] = $app_user['lvl_id'];
                            }
                        }else{
                            break;
                        }
                    }
                    
                    //是否显示选择审批人功能
                    $is_show_app = check_is_show_app($recouped['user_id'], $user_id, $lvl_id, $approve, $recouped['crt_time']);
                    
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getPfApproverList($recouped['crt_time'],$approve);
                        $this->assign('approve_users', $approve_users);
                    }
                    
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $recouped['user_id']==$user_id)){
                        $is_restart = true;
                    }
                    
                    $this->assign('recouped', $recouped);
                    $this->assign('money_list', $money_list);
                    $this->assign('apply_user', $apply_user);
                    $this->assign('depart', $depart);
                    $this->assign('schedule', $approve);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('user_id', $this->user_id);
                    $this->assign('lvl_id', $lvl_id);
                    $this->display('Pfrecouped:info_old');
                }
            }
        }
    }
    
    /*
     *  审批结果提交
     **/
    public function operateAction(){
        $id = I("post.pf_id");
        $num = I("post.num");
        $result = I("post.result");
        $cur_approver_id = I("post.cur_approver_id");
        $recouped_model = new \Home\Model\PfrecoupedModel();
        $recouped = $recouped_model->get_one($id);
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        
        if($result == 1 && $lvl_id != 4 && $lvl_id != 2 && $cur_approver_id==0){          //同意操作，并且不是总经理和出纳，需要判断是否选择下一步审批人
            $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
        }
        if($recouped){
            $app_model = new \Home\Model\ApproveModel();
            if($app_model->getApproveSchedule($recouped['pf_no'] ,6)){
                $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                if($lvl_id == 4 || $result == -1){
                    $data['result'] = $result;
                }
                
                if(I("post.option")) $data['aprv_opinion'.$num] = I("post.option");
                
                $recouped_model->startTrans();           //开启事务
                $ret = $app_model->update_data($data, "aprv_no='{$recouped['pf_no']}' and aprv_type=6");
                if($ret){
                    $depart_id = session('depart_id');
                    if($lvl_id != 4 && $result == 1){
                        if($lvl_id == 2){
                            choose_approval_user($id,6,get_cn_userid(),$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给peter
                        }else{
                            choose_approval_user($id,6,$cur_approver_id,$lvl_id,$user_id);                    //非总经理的人员提交下个审批人的操作
                        }
                    }
                    
                    if($lvl_id == 4 || $result == -1){
                        $recouped_model->update_data(array('result'=>$result), "pf_id = {$recouped['pf_id']}");
                        $content = "您的一个个人报销单审批已通过，请注意查看。";
                        if($result == -1){
                            //不同意操作，把费用重新加回预算单余额
                            $money_model = new \Home\Model\MoneyModel();
                            $moneys = $money_model->get_list("obj_id = $id and obj_type=3 and is_del=1");
                            if($moneys){
                                foreach ($moneys as $money) {
                                    $ret2 = $money_model->update_data(array('is_del'=>-1,'is_cancel'=>1), "money_id=".$money['money_id']);
                                    if(!$ret2){
                                        $recouped_model->rollback();
                                        $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                                        break;
                                    }
                                }
                            }else{
                                $recouped_model->rollback();
                                $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败2！"));
                            }
                            $content = "您的一个报销单审批未获通过，请注意查看。";
                        }
                        send_email_approve($recouped['user_id'], '报销单审批通知', $content."单号：{$recouped['pf_no']}。", U("Pfrecouped/info",array('id'=>$id)));           //发邮件
                    }
                    $recouped_model->commit();          
                    logrecords("update", $app_model->get_tablename());          //日志
                    $this->ajaxReturn(array('status'=>1));
                }
                $recouped_model->rollback();           //回滚
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    public function backAction(){
        if(IS_POST){
            $lvl_id = session('lvl_id');
            $user_id = $this->user_id;
            if($lvl_id==2){
                $app_model = new \Home\Model\ApproveModel();
                $obj_id = I('post.obj_id');
    
                //借款单
                $obj_model = new \Home\Model\PfrecoupedModel();
                $where = "pf_id = '$obj_id'";
                $obj = $obj_model->get_onebywhere($where);
                if($obj){
                    $aprv_no = $obj['pf_no'];
                }
                $approve = $app_model->getApproveSchedule($aprv_no, 6);
                $obj_model->startTrans();
                $ret = $obj_model->update_data(array('cur_approver_id'=>$user_id,'result'=>0), $where);
                for ($i = 1; $i < 6; $i++) {
                    if($approve["aprv_user_id".$i] == get_zjl_userid()){
                        break;
                    }
                }
                $ret1 = $app_model->update_data(array('aprv_user_id'.($i+1) => 0, 'aprv_result'.$i => 0, 'result'=>0), "aprv_no='$aprv_no' and aprv_type=6");
                if($ret && $ret1){
                    if($obj['result'] == -1){
                        //不同意操作，把费用重新加回预算单余额
                        $money_model = new \Home\Model\MoneyModel();
                        $moneys = $money_model->get_list("obj_id={$obj['pf_id']} and obj_type=3 and is_del=-1 and is_cancel=1");
                        foreach ($moneys as $money) {
                            $ret2 = $money_model->update_data(array('is_cancel'=>0,'is_del'=>1), 'money_id='.$money['money_id']);
                            if(!$ret2){
                                $obj_model->rollback();
                                $this->ajaxReturn(array('status'=>0, 'msg'=>"操作失败1！"));
                                break;
                            }
                        }
                    }
                    $obj_model->commit();
                    $this->ajaxReturn(array('status'=>1));
                }
                $obj_model->rollback();
                $this->ajaxReturn(array('status'=>0,'操作失败！'));
            }
        }
    }
    
    /*
     *  选择审批人提交
     **/
    public function chooseAction(){
        $id = I("post.pf_id");
        $cur_approver_id = I("post.cur_approver_id");
        $recouped_model = new \Home\Model\PfrecoupedModel();
        $recouped = $recouped_model->get_one($id);
        if($recouped){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($recouped['pf_no'], 6);
            $user_id = $this->user_id;
            $lvl_id = session('lvl_id');
            
            //判断该用户是否审批，审批后才能提交下个审批人
            if($lvl_id!=1 && !($app_model->checkIsApproved($approve, $user_id))){
                $this->ajaxReturn(array('status'=>0, 'msg'=>'未审批或者未通过，所以不给提交下个审批人！'));
            }
            
            //判断是否可以编辑审批人
            if($lvl_id!=1 && $app_model->getIsApproved($approve, $user_id)){
                $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
            }              
            
            //更新recouped表当前审批人
            $recouped_model->startTrans();     //开启事务
            $ret = $recouped_model->update_data(array('cur_approver_id'=>$cur_approver_id), "pf_id=".$id);
            if($ret){
                $ret1 = $app_model->updateApproved($approve, $cur_approver_id, $lvl_id, $user_id);
                if($ret && $ret1){
                    logrecords("update", $app_model->get_tablename());          //日志
                    $recouped_model->commit();
                    send_email_approve($cur_approver_id, '个人报销单审批通知', "您有一个个人报销单审批请求！",U("Pfrecouped/info",array('id'=>$id)));   //发邮件
                    $this->ajaxReturn(array('status'=>1));
                }else{
                    $recouped_model->rollback();
                }
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    /*
     *  删除用户操作
     **/
    public function deleteAction(){
        if(IS_POST){
            $id = I('post.pf_id');
            $recouped_model = new \Home\Model\PfrecoupedModel();
            $recouped = $recouped_model->get_one($id);
            if($recouped){
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($recouped['pf_no'], 6);
                if(!check_permission_left('financial_approve', 'finance')){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可删除！'));
                    }
                }
                
                $recouped_model->startTrans();                 //开启事务
                $ret = $recouped_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "pf_id = $id");
                $ret1 = $app_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "aprv_no='{$recouped['pf_no']}' and aprv_type=6");
                if($ret && $ret1){
                    $money_model = new \Home\Model\MoneyModel();
                    $moneys = $money_model->get_list("obj_id = $id and obj_type=3 and is_del=1");
                    if($moneys){
                        $ret2 = $money_model->update_data(array('is_del'=>-1), "obj_id = $id and obj_type=3");
                        if($ret2){
                            $recouped_model->commit();
                            logrecords('delete', $recouped_model->get_tablename());
                            $this->ajaxReturn(array('status'=>1));
                        }
                    }
                }
                
                $recouped_model->rollback();          //回滚
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'个人报销单不存在！'));
        }
        
    }
    
    /*
     *  重新发启审核
     **/
    public function restartAction(){
        $id = I('post.pf_id');
        $recouped_model = new \Home\Model\PfrecoupedModel();
        $recouped = $recouped_model->get_one($id);
        
        if($recouped){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($recouped['pf_no'], 6);
            if($approve && $approve['result'] == -1){
                $data['aprv_user_id1'] = 0;
                $data['aprv_time1'] = 0;
                $data['aprv_opinion1'] = '';
                $data['aprv_result1'] = 0;
                $data['aprv_user_id2'] = 0;
                $data['aprv_time2'] = 0;
                $data['aprv_opinion2'] = '';
                $data['aprv_result2'] = 0;
                $data['aprv_user_id3'] = 0;
                $data['aprv_time3'] = 0;
                $data['aprv_opinion3'] = '';
                $data['aprv_result3'] = 0;
                $data['aprv_user_id4'] = 0;
                $data['aprv_time4'] = 0;
                $data['aprv_opinion4'] = '';
                $data['aprv_result4'] = 0;
                $data['aprv_user_id5'] = 0;
                $data['aprv_time5'] = 0;
                $data['aprv_opinion5'] = '';
                $data['aprv_result5'] = 0;
                $data['result'] = 0;
                $recouped_model->startTrans();
                $ret = $app_model->update_data($data, "aprv_id={$approve['aprv_id']}");
                $ret1 = $recouped_model->update_data(array('cur_approver_id'=>0,'result'=>0), "pf_id = $id");
                if($ret && $ret1){
                    $money_model = new \Home\Model\MoneyModel();
                    $moneys = $money_model->get_list("obj_id=$id and obj_type=3 and is_cancel=1");
                    
                    foreach ($moneys as $money) {
                        //重启操作，重新减去预算单相关费用余额
                        $ret2 = $money_model->update_data(array('is_cancel'=>0,'is_del'=>1), 'money_id='.$money['money_id']);
                        if(!$ret2){
                            $recouped_model->rollback();
                            $this->ajaxReturn(array('status'=>0, 'msg'=>"操作失败1！"));
                            break;
                        }
                    }
                    $recouped_model->commit();
                    logrecords("update", $app_model->get_tablename());          //日志
                    $this->ajaxReturn(array('status'=>1));
                }
                $recouped_model->rollback();
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    /*
     *  获取借款单号
     **/
    public function getBorrowNo(){
        return "PFBXD".date('YmdHis').sprintf("%04d",rand(0, 1000));
    }

    public function getapproversAction(){
        $is_tb = I('post.is_tb');
        $approve_users = getPfApproverList(time(), null, $is_tb);
        if($approve_users){
            $this->ajaxReturn(array('status'=>1, 'approve_users'=>$approve_users));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'审批人没有'));
    }
    
    public function check_sup($get_type){
        $get_id = 0;
        if(I('post.sup_id')){
            $get_id = I('post.sup_id');
        }else{
            $sup_name=I('post.sup_name');
            if($sup_name){
                $supplers = $this->get_sup_list($get_type, "sup_full_name='$sup_name'", 0, 1);
                if($supplers){
                    $get_id = $supplers[0]['sup_id'];
                }
            }
        }
    
        if(!$get_id){
            return array('status'=>0, 'msg'=>'供应商不存在！');
        }
        return array('status'=>1, 'get_id'=>$get_id);
    }
    /**
     * ++++++++++++++++Litter_7
     */
    public function mapFormat($search=array()){
        $map = array();
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        /*if(empty($search)){
            return false;
        }*/
        $map['is_del'] = 1;
        if(get_access_allvoucher($user_id)){
            //获取所属公司下属的项目相关预算单
            if($lvl_id!=2){
                $user_model = new \Home\Model\UserModel();
                $company_id = session('company_id') ;
                $uids = $user_model->where("is_del=1 and company_id=$company_id")->getField('user_id', true);
                $map['user_id'] = array('in',$uids);
            }
        }else{
            //获取归该用户审批的报销单列表
            $app_model = new \Home\Model\ApproveModel();
            $awhere = $app_model->get_whereaprrove($user_id, 6);
            $map['_string']= "(user_id = $user_id OR pf_no in ($awhere))";
        }

        //状态检索
        $way = $search['way'];
        if($way==1){
            $map['result'] = array('in',array(1,-1));//" and (result=1 or result=-1)";
        }elseif($way==2){
            $map['result'] = 0;
        }

        //日期检索
        $start_date = $search['start_date'];
        $end_date = $search['end_date'];
        if($start_date && $end_date){
            $start_time = strtotime($start_date);
            $end_time = strtotime($start_date) + 86399;//
            $map['crt_time'] = array('between',array($start_time,$end_time));
        }elseif($start_date){
            $start_time = strtotime($start_date);
            $map['crt_time'] = array('gt',$start_time);
        }elseif($end_date){
            $end_time = strtotime($start_date) + 86399;//
            $map['cdate'] = array('lt',$end_time);
        }

        //关键词检索
//        $keyword = I('keyword','','addslashes');
        $keyword = trim($search['keyword']);
        if($keyword){
            $user_ids = $user_model->where("is_del = 1 and real_name like '%$keyword%'","user_id")->getField('user_id',true);          //检索项目列表
            if($user_ids){
                $map['user_id'] = array('in',$user_ids);
            }
        }
        return $map;
    }
    public function exportAction(){
        /*$recouped_model = new \Home\Model\PfrecoupedModel();
        $search = I('');
        $map = $this->mapFormat($search);
        $list = $recouped_model->where($map)->order("pf_id desc")->select();*/
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\ApproveModel();
        $user_model = new \Home\Model\UserModel();
        $recouped_model = new \Home\Model\PfrecoupedModel();
        if(get_access_allvoucher($user_id)){
            $where = "is_del = 1";
            //获取所属公司下属的项目相关预算单
            if($lvl_id!=2){
                $where .= " and user_id in (".$user_model->get_wherebycompanyid(session('company_id')).")";
            }
        }else{
            $where = "is_del = 1 and (user_id = $user_id";

            //获取归该用户审批的报销单列表
            $awhere = $app_model->get_whereaprrove($user_id, 6);
            $where .= " or pf_no in ($awhere)";
            $where .= ")";
        }

        //状态检索
        $way = I('get.way',0);
        if($way==1){
            $where .= " and (result=1 or result=-1)";
        }elseif($way==2){
            $where .= " and result=0";
        }

        //日期检索
        $start_date = I('get.start_date','','addslashes');
        $end_date = I('get.end_date','','addslashes');
        if($start_date){
            $start_date = strtotime($start_date);
            $where .= " and crt_time>='$start_date'";
        }
        if($end_date){
            $end_date = strtotime($end_date." 23:59:59");
            $where .= " and crt_time<='$end_date'";
        }

        //关键词检索
        $keyword = I('get.keyword','','addslashes');
        if($keyword){
            $ulist = $user_model->get_list("is_del = 1 and real_name like '%$keyword%'","user_id");          //检索项目列表
            if($ulist){
                foreach ($ulist as $urow) {
                    $pwhere .= $urow['user_id'].",";
                }
                $pwhere = rtrim($pwhere, ",");
                if($pwhere){
                    $where .= " and user_id in ($pwhere)";
                }
            }
        }
        $list = $recouped_model->where($where)->order("pf_id desc")->select();
//        echo $recouped_model->getLastSql();


        $headtitle = array(
            'pf_no'=>'报销单号',
            'user_id'=>'申请人',
            'tot_amt'=>'金额总计',
            'result'=>'状态',
            'get_type'=>'收款人类型',
            'get_user'=>'收款人',
            'borrow_way'=>'收款方式',
            'bnk_branch'=>'银行',
            'bnk_acct'=>'银行账号',
            'crt_time'=>'创建时间',
            'remark'=>'备注说明',
            );
        $this->outPut($list,$headtitle);
    }
    protected function outPut($list=array(),$headtitle=array()){
        $app_model = new \Home\Model\ApproveModel();
        $user_model = new \Home\Model\UserModel();
        $recouped_model = new \Home\Model\PfrecoupedModel();
        $depart_model = new \Home\Model\DepartModel();
        $header = implode("\t",array_values($headtitle));
        $header .= "\t\n";
        $content .= $header;
        $filename = '个人报销单'.date('YmdHis').'.xls';
        ob_end_clean();
        header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        header("X-DNS-Prefetch-Control: off");
        header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=".$filename);
        $content=iconv("UTF-8","GBK//IGNORE",$content) ;
        echo $content;
        foreach($list as &$row)
        {
            //id字段转换
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            if($row['borrow_way']==3){
                $row['borrow_way'] = "其他";
            }elseif($row['borrow_way']==2){
                $row['borrow_way'] = "转账";
            }else{
                $row['borrow_way'] = "现金";
            }
            $user = $user_model->get_one($row['crt_user_id']);               //申请人员
            if($user){
                $row['user_id'] = $user['real_name'];
                if($user['depart_id']){                          //部门名称
                    $depart = $depart_model->get_one($user['depart_id']);
                    if($depart){
                        $row['user_id'] = $depart['depart_name'].'--'.$user['real_name'];
                    }
                }
            }
            $get_type = $row['get_type'];
            if($get_type==2){
                $row['get_type'] = '个人';
                $row['get_user'] = $user['real_name'];
            }elseif($get_type==3 || ($get_type==1 && $user['depart_id']==16)){
                $row['get_type'] = '媒介供应商';
                $row['get_user'] = M('Mjsupplier')->where("sup_id={$row['get_id']} and is_del=1")->getField('sup_full_name');
            }else{
                $row['get_type'] = '供应商';
                $row['get_user'] = M('Supplier')->where("sup_id={$row['get_id']} and is_del=1")->getField('sup_full_name');
            }
            if($headtitle['remark']){
                //获取款项列表
                $money_model = new \Home\Model\MoneyModel();
                if($row['rec_id']){
                    if($row['result'] == -1){
                        $money_list = $money_model->get_list("obj_id={$row['rec_id']} and obj_type=2 and is_cancel=1");
                    }else{
                        $money_list = $money_model->get_list("obj_id={$row['rec_id']} and obj_type=2 and is_del=1");
                    }
                    if($money_list){
                        $moneyremark = '';
                        foreach($money_list as  $money){
                            $moneyremark .= $money['comm'].'('.$money['money'].') &';
                        }
                        $row['remark'] = substr($moneyremark,0,-1);
                    }
                }
            }

            if($row['result'] == 1){
                $row['result'] = '通过';
            }elseif($row['result'] == -1){
                $row['result'] = '未通过';
            }else{
                $row['result'] = '审核中';
            }
            //获取审批状态
            $approve = $app_model->getApproveSchedule($row['pf_no'], 6);
            for ($i = 1; $i < 5; $i++) {
                if($approve['aprv_user_id'.$i] == $row['cur_approver_id']){
                    $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                    $row['result'] = '等待审批';
                    if($approve['aprv_result'.$i] == 1){
                        $row['result'] = '同意';
                    }elseif($approve['aprv_result'.$i] == -1){
                        $row['result'] = '不同意';
                    }
                    if(($i == 4 || $app_user['lvl_id']==4)&& $approve['aprv_result'.$i] == 1){
                        $row['result'] = '流程已完成';
                    }else{
                        $row['result'] = $row['result']."（{$app_user['real_name']}）";
                    }
                    break;
                }
            }
            $new_arr = array();
            $content = "";
            foreach ($headtitle as $key1 => $value)
            {
                array_push($new_arr, trim($row[$key1]));
            }
            $line = implode("\t",$new_arr);
            //$line = "\"" .$line;
            $line .= "\t\n";
            $content .= $line;
            $content=@iconv("UTF-8","GBK//IGNORE",$content) ;
            echo $content;
        }
    }
}