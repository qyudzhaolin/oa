<?php
namespace Home\Controller;
use Think\Page;
class OvertimeController extends BaseController{
    
    public $check_access = true;
    public $head_title = '加班申请管理';
    public $o_status = array(1=>'待审批',2=>'已通过',3=>'未通过');
    public $o_types = array(1=>'1小时',2=>'2小时',3=>'3小时',4=>'4小时及以上');
    
    public function indexAction(){
        $lvl_id = session('lvl_id');
        if($lvl_id==2){
            $this->redirect("Overtime/approve");
        }
        
        $ov_model = new \Home\Model\OvertimeModel();
        $where = "is_del=1";
        $is_vo_power = check_permission_left("Overtime", "regret");
        if($is_vo_power){
            //关键词检索
            $real_name = I('get.keyword','','addslashes');
            if($real_name){
                $where .= " and real_name='$real_name'";
            }
            $where .= " and real_name in (select real_name from max_user where company_id=".session('company_id').")";
            
            if(I('get.status')){
                $where .= " and status=".intval(I('get.status'));
            }
            
        }else{
            $real_name = session('real_name');
            $where .= " and status!=2 and real_name='$real_name'";
        }
       
        if(I('get.o_type')){
            $where .= " and o_type=".intval(I('get.o_type'));
        }
        
        //日期检索
        $start_date = I('get.start_date','','addslashes');
        $end_date = I('get.end_date','','addslashes');
        if($start_date){
            $where .= " and o_date>='$start_date'";
        }
        if($end_date){
            $where .= " and o_date<='$end_date'";
        }
        
        $count = $ov_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $ov_model->where($where)->order("crt_time desc")->limit($page->firstRow.','.$page->listRows)->select();
        $user_model = new \Home\Model\UserModel();
        $app_model = new \Home\Model\OvApproveModel();
        foreach ($list as &$row) {
            if($is_vo_power && !$real_name){
                $user = $user_model->get_one($row['crt_user_id']);               //申请人员
                $row['real_name'] = $user['real_name'];
            }else{
                $row['real_name'] = $real_name;
            }
            $row['type_name'] = $row['o_type'] ? $this->o_types[$row['o_type']] : "";
            if($row['status']==1 && (($row['cur_approver_id'] == 92 && strtotime($row['o_date']." +1 day")<strtotime(date('Y-m-d'))) || ($row['cur_approver_id'] != 92 && strtotime($row['o_date'])<strtotime(date('Y-m-d'))))){
                $row['status_name'] = "已过期";
            }else{
                $row['status_name'] = $row['status'] ? $this->o_status[$row['status']] : "";
                $app_user = $user_model->get_one($row['cur_approver_id']);               //审批人员
                $row['status_name'] .= "（".$app_user['real_name']."）";
            }
            
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            if($row['status']==3){
                $approve = $app_model->getApproveSchedule($row['o_id']);
                $row['no_agree_reason'] = $approve['aprv_opinion1'];
            }
        }
        //审批人列表
        $approve_users = getOvApproverList();
        
        $this->assign('o_type_arr', $this->o_types);
        $this->assign('approve_users', $approve_users);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('is_vo_power', $is_vo_power);
        $this->assign('is_approver', $this->is_approver());
        $this->display('Overtime:index');
    }
    
    //新增申请
    public function ov_addAction(){
        $types = $this->att_types;
        if(IS_POST){
            $data['o_type'] = I('post.o_type',0);
            $data['reason'] = trim(I('post.reason'));
            $data['cur_approver_id'] = I('post.cur_approver_id');
            $data['o_date'] = I('post.o_date');
            $id = I("post.o_id");
            $ov_model = new \Home\Model\OvertimeModel();
            
            //基础数据验证
            if($data['reason']=="") $this->ajaxReturn(array('status'=>0, 'msg'=>'请填写原因！'));
            if(!$data['cur_approver_id'] && !$id) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            
            if(!$id){
                $ov_count = $ov_model->where("crt_user_id={$this->user_id} and o_date='{$data['o_date']}' and is_del=1 and status!=3")->count();
                if($ov_count){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'今天已经提交过申请！'));
                }
            }
            
            if(session('lvl_id')!=1){
                if(strtotime($data['o_date'])<strtotime(date('Y-m-d'))){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'日期不能请小于今天！'));
                }
            }
            
            //判断是否可以更新
            $app_model = new \Home\Model\OvApproveModel();
            $approve = null;
            $overtime = null;
            $action = "";
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $overtime = $ov_model->get_one($id);
                $approve = $app_model->getApproveSchedule($overtime['o_id']);
                if(session('lvl_id')!=1 && $approve['aprv_result1']!=0){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                }
            }
        
            //新增、更新数据
            $ov_model->startTrans();     //开启事务
            if($id){
                if(session('lvl_id')==1){
                    $data['crt_time'] = strtotime($data['start_date']);
                }
                $ret = $ov_model->update_data($data, "o_id = $id");  
                if($ret && $overtime['cur_approver_id'] != $data['cur_approver_id']){
                    $ret1 = $app_model->updateApproved($approve, $data['cur_approver_id']);
                }else{
                    $ret1 = true;
                }
                $action = "update";
            }else{
                $data['crt_user_id'] = $this->user_id;
                $data['real_name'] = session('real_name');
                $data['status'] = 1;
                $ret = $ov_model->insert_data($data);                               
                if($ret){
                    $ret1 = $app_model->insert_data(array('o_id'=>$ret,'aprv_user_id1'=>$data['cur_approver_id'],'crt_user_id'=>$data['crt_user_id']));
                    $id = $ret;
                }
                $action = "insert";
            }
            
            if($ret && $ret1){
                if($ov_model->commit()){
                    logrecords($action, $ov_model->get_tablename());          //日志
                    send_email_approve($data['cur_approver_id'], '加班审批通知', $data['real_name']."的加班申请请审批！", U("Overtime/approve"));           //发邮件
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $ov_model->rollback();
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    }
    
    //删除用户操作
    public function ov_deleteAction(){
        if(IS_POST){
            $id = I('post.o_id');
            $ov_model = new \Home\Model\OvertimeModel();
            $overtime = $ov_model->get_one($id);
            if($overtime){
                $app_model = new \Home\Model\OvApproveModel();
                if(session('lvl_id')!=1 && $overtime['status']==2){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可删除！'));
                }
    
                $ret = $ov_model->update_data(array('is_del'=>-1), "o_id = $id");
                if($ret){
                    $ret1 = $app_model->update_data(array('is_del'=>-1), "o_id=$id");
                    logrecords('delete', $ov_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'加班申请不存在！'));
        }
    }
    
    //审批管理
    public function approveAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\OvApproveModel();
        $user_model = new \Home\Model\UserModel();
        $where = "is_del = 1";
        if($lvl_id != 1){
            $where .= " and aprv_user_id1 = {$this->user_id}";
        }else{
            $where .= " and aprv_user_id1 in (".$user_model->get_wherebycompanyid(session('company_id')).")";
        }
        
        //状态检索
        $way = I('get.way',2);
        if(is_numeric($way) && $way > 0){
            if($way == '1'){
                if($lvl_id == 1){
                    $where .= " and result=1";
                }else{
                    $where .= " and (aprv_user_id1 = {$this->user_id} and aprv_result1!=0)";
                }
            }else{
                if($lvl_id == 1){
                    $where .= " and result=0";
                }else{
                    $where .= " and (aprv_user_id1 = {$this->user_id} and aprv_result1=0)";
                }
            }
        }
        
        //日期检索
        $start_date = I('get.start_date','','addslashes');
        $end_date = I('get.end_date','','addslashes');
        if($start_date || $start_date){
            if($start_date){
                $where .= " and crt_time>=".strtotime($start_date);
            }
            if($end_date){
                $where .= " and crt_time<=".strtotime($end_date." 23:59:59");
            }
        }
        
        //关键词检索
        $ov_model = new \Home\Model\OvertimeModel();
        $keyword = I('get.keyword','','addslashes');
        if($keyword){
            $ids = $user_model->where("real_name like '%$keyword%'")->getField('user_id',true);
            if($ids){
                $where .= " and crt_user_id in (".implode(",", $ids).")";
            }else{
                $where .= " and 1!=1";
            }
        }
        
        $count = $app_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $app_model->where($where)->order('crt_time desc')->limit($page->firstRow.','.$page->listRows)->select();
        
        foreach ($list as &$row) {
            $overtime = $ov_model->get_one($row['o_id']);
            $row['o_type'] = $this->o_types[$overtime['o_type']];
            $row['reason'] = $overtime['reason'];
            $row['o_date'] = $overtime['o_date'];
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            
            $apply_userid = $row['crt_user_id'];
            $user = $user_model->get_one($apply_userid);               //申请人员
            $row['real_name'] = $user['real_name'];
            $row['apply_depart_id'] = $user['depart_id']; 
        
            if($user){
                $row['depart_name'] = M('Depart')->where('depart_id='.$user['depart_id'])->getField('depart_name');
            }
            
            $is_late = false;                                //是否过期
            if($row['result']==1){                                    //管理员
                $row['result_info'] = '已同意';
            }else{
                if($row['result'] == 0){
                    if(($overtime['cur_approver_id'] == 92 && strtotime($row['o_date']." +1 day")<strtotime(date('Y-m-d'))) || ($overtime['cur_approver_id'] != 92 && strtotime($row['o_date'])<strtotime(date('Y-m-d')))){
                        $row['result_info'] = '已过期';
                        $is_late = true;
                    }else{
                        $row['result_info'] = '等待审批';
                    }
                }elseif($row['result'] == 3){
                    $row['result_info'] = '不同意';
                    $row['no_agree_reason'] = $row['aprv_opinion1'];
                }
                $app_user = $user_model->get_one($row['aprv_user_id1']);               //审批人员
                $row['result_info'] = $row['result_info']."（".$app_user['real_name']."）";
            }
            $row['is_approve'] = true;
            if($app_user['user_id']!=$user_id || $row['result'] || $is_late){
                $row['is_approve'] = false;
            }
        }
        $this->assign('way',$way);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display('Overtime:approve');
    }
    
    //审批操作
    public function operateAction(){
        if(IS_POST){
            $aprv_id = I('post.aprv_id');
            $result = I('post.result');
            $reason = I('post.reason');
            
            $app_model = new \Home\Model\OvApproveModel();
            $ov_model = new \Home\Model\OvertimeModel();
            $user_model = new \Home\Model\UserModel();
            $approve = $app_model->get_one($aprv_id);
            if($approve){
                $user_id = $this->user_id;
                $lvl_id = session('lvl_id');
                $data['aprv_time1'] = time();
                
                //当不同意
                $overtime = $ov_model->get_one($approve['o_id']);
                $data['result'] = $data['aprv_result1'] = $result;
                if($result == 2){
                    $data['result'] = $data['aprv_result1'] = 3;
                    $data['aprv_opinion1'] = $reason;
                }
                
                $app_model->startTrans();
                $ret = $app_model->update_data($data, "aprv_id=$aprv_id");
                if($ret){
                    $o_data = array();
                    if($data['result']==1){
                        $o_data['status'] = 2;
                    }else{
                        $o_data['status'] = 3;
                    }
                    if($o_data){
                        $ret1 = $ov_model->update_data($o_data, "o_id=".$approve['o_id']);
                    }else{
                        $ret1 = true;
                    }
                    
                    //更新考勤记录表
                    if($ret1){
                        if($app_model->commit()){
                            logrecords("update", $app_model->get_tablename());          //日志
                            if($data['result'] == 2){                                    //不同意
                                send_email_approve($overtime['crt_user_id'], '加班审批未通过', "你的加班申请未获通过！", U("Overtime/index"));           //发邮件
                            }
                            $this->ajaxReturn(array('status'=>1));
                        }
                    }
                }
                $app_model->rollback();          //回滚
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    }
    
    //是否审批人
    public function is_approver(){
        $lvl_id = session('lvl_id');
        $user_id = session('user_id');
        if($lvl_id<9 && $lvl_id!=4){
            if($lvl_id==3 && $user_id!=get_cw_userid()){
                return false;
            }
            return true;
        }
    }

    //ajax获取va数据
    public function getovAction(){
        if(IS_POST){
            $id = I('post.o_id');
            $ov_model = new \Home\Model\OvertimeModel();
            $overtime = $ov_model->get_one($id);
            if($ov_model){
                $this->ajaxReturn(array('status'=>1,'overtime'=>$overtime));
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    }
    
    //通过过期
    public function passAction(){
        if(IS_POST){
            $o_id = I('post.o_id');
        
            $app_model = new \Home\Model\OvApproveModel();
            $ov_model = new \Home\Model\OvertimeModel();
            $user_model = new \Home\Model\UserModel();
            $overtime = $ov_model->get_one($o_id);
            if($overtime){
                $data['aprv_time1'] = time();
        
                //当不同意
                $approve = $app_model->get_onebywhere("o_id=$o_id and is_del=1");
                $data['result'] = $data['aprv_result1'] = 1;
                $app_model->startTrans();
                $ret = $app_model->update_data($data, "aprv_id=".$approve['aprv_id']);
                if($ret){
                    $ret1 = $ov_model->update_data(array('status'=>2,'is_pass'=>1), "o_id=".$approve['o_id']);
                    if($ret1){
                        if($app_model->commit()){
                            logrecords("update", $app_model->get_tablename());          //日志
                            $this->ajaxReturn(array('status'=>1));
                        }
                    }
                }
                $app_model->rollback();          //回滚
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    }
}