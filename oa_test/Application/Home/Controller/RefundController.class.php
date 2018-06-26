<?php
/**
 * OA系统--还款单
 */
namespace Home\Controller;
use Think\Page;
class RefundController extends BaseController{
    
    public $check_access = true;
    public $head_title = '还款单';
    
    public function indexAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $proj_model = new \Home\Model\ProjectModel();
        if(get_access_allvoucher($user_id)){                    
            $where = "is_del = 1";
            if($lvl_id!=2){
                //获取所属公司下属的项目相关预算单
                $where .= " and proj_id in (".$proj_model->get_wherebycompanyid(session('company_id')).")";
            }
        }else{
            $where = "is_del = 1 and (crt_user_id=$user_id || cur_approver_id=$user_id)";
        }
        
        //关键词检索
        $keyword = I('get.keyword','','addslashes');
        if($keyword){
            $plist = $proj_model->get_list("is_del = 1 and proj_name like '%$keyword%'","proj_id");          //检索项目列表
            $pwhere = "";
            foreach ($plist as $prow) {
                $pwhere .= $prow['proj_id'].",";
            }
            $pwhere = rtrim($pwhere, ",");
            if($pwhere){
                $where .= " and proj_id in ($pwhere)";
            }else{
                $where .= " and proj_id<0";
            }
        }
        
        $refund_model = new \Home\Model\RefundModel();
        $count = $refund_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $refund_model->where($where)->order("ref_id desc")->limit($page->firstRow.','.$page->listRows)->select();
        
        $depart_model = new \Home\Model\DepartModel();
        $user_model = new \Home\Model\UserModel();
        $borrow_model = new \Home\Model\BorrowModel();
        foreach ($list as &$row) {
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            
            $proj = $proj_model->get_one($row['proj_id']);               //项目
            $row['proj_name'] = $proj['proj_name'];
            
            $borrow = $borrow_model->get_one($row['borrow_id']);         //借款单
            $row['borrow_no'] = $borrow['borrow_no'];
            
            $user = $user_model->get_one($row['crt_user_id']);               //申请人员
            if($user){
                $row['real_name'] = $user['real_name'];
                if($user['depart_id']){                          //部门名称
                    $depart = $depart_model->get_one($user['depart_id']);
                    if($depart){
                        $row['depart_name'] = $depart['depart_name'];
                    }
                }
            }
            
            //获取审批状态
            $row['result_info'] = '待处理';
            if($row['result'] == 1){
                $row['result_info'] = '已处理';
            }elseif($row['result'] == -1){
                $row['result_info'] = '不同意';
            }
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('user_id', $user_id);
        $this->assign('lvl_id', $lvl_id);
        $this->display('Refund:index');
    }
    
    /*  
     *  新增、修改用户页
     **/
    public function addAction(){
        $borrow_model = new \Home\Model\BorrowModel();
        $refund_model = new \Home\Model\RefundModel();
        $money_model = new \Home\Model\MoneyModel();
        $rm_model = new \Home\Model\RefundMoneyModel();
        
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        
        if(IS_POST){
            $data['proj_id'] = I('post.proj_id');
            $data['borrow_id'] = I('post.borrow_id');
            $id = I("post.ref_id");
            $fund_str = I("post.fund_str");
            $data['tot_amt'] = I('post.tot_amt');
            $data['tot_amt_d'] = I('post.tot_amt_d');
            $cur_approver_id = $data['cur_approver_id'] = I('post.cur_approver_id');
            
            //基础数据验证
            if(!$data['proj_id']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择项目！'));
            if(!$data['borrow_id']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择借款单！'));
            if(!$data['cur_approver_id'] && !$id) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            
            //判断是否可以更新
            $app_model = new \Home\Model\ApproveModel();
            $approve = null;
            $refund = null;
            $action = '';
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $refund = $refund_model->get_one($id);
                $approve = $app_model->getApproveSchedule($refund['ref_no'], 4);
                if(session('lvl_id')!=1){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                    }
                }
            }
            
            //新增、更新数据
            $refund_model->startTrans();     //开启事务
            if($id){
                $ret = $refund_model->update_data($data, "ref_id = $id");            //更新还款单表
                if($ret && $refund['cur_approver_id'] != $data['cur_approver_id']){
                    $ret3 = $app_model->updateApproved($approve, $data['cur_approver_id'], $lvl_id, $user_id);
                }else{
                    $ret3 = true;
                }
                $action = 'update';
            }else{
                $data['user_id'] = $this->user_id;
                $data['crt_user_id'] = $this->user_id;
                $data['ref_no'] = $this->getBorrowNo();
                $ret = $refund_model->insert_data($data);                               //插入还款单表
                $ret3 = true;
                $action = 'insert';
            } 
            $msg = "保存失败！";
            if($ret && $ret3){     
                if($action == 'update'){
                    $r_moneys = $rm_model->get_list("ref_id = $id and is_del=1");
                    if($r_moneys){
                        $ret2 = $rm_model->update_data(array('is_del'=>-1), "ref_id = $id");
                        if($ret2){
                            //更新新数据前，先把老的报销金额返还给原处
                            foreach ($r_moneys as $r_money) {
                                $ret_exp = $money_model->where("obj_id={$refund['borrow_id']} and obj_type=1 and cost_id={$r_money['cost_id']} and is_del=1")->setInc('usable_money', $r_money['money']);
                                if(!$ret_exp){
                                    $refund_model->rollback();
                                    $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                                    break;
                                }
                            }
                        }
                    }else{
                        $ret2 = true;
                    }
                    $ret = $id;
                }else{
                    //提交审批表
                    $ret2 = $app_model->insert_data(array('aprv_no'=>$data['ref_no'],'aprv_type'=>4,'aprv_user_id1'=>$data['cur_approver_id'],'proj_id'=>$data['proj_id']));
                }
                if($ret2){
                    $issubmit = true;
                    $fund_str = str_replace("&amp;", "&", $fund_str);
                    $fund_array = explode(";", $fund_str);
                    $exps = $money_model->get_array_list($data['borrow_id']);
                
                    foreach ($fund_array as $f_row) {
                        if($f_row && $f_arr = explode("^", $f_row)){
                            if($f_arr[1] == "0.00"){
                                continue;
                            }
                            if(!array_key_exists($f_arr[0], $exps)){
                                $msg = '款项：'.$f_arr[3].'不存在！';
                                $issubmit = false;
                                break;
                            }
                            $usable_money = $exps[$f_arr[0]]['usable_money'] - $f_arr[1];         //可用余额
                            if($usable_money < 0){
                                $msg = '款项：'.$f_arr[3].'可用余额不足！';
                                $issubmit = false;
                                break;
                            }
                            $ret1 = $rm_model->insert_data(array('ref_id'=>$ret,'cost_id'=>$f_arr[0], 'money'=>$f_arr[1], 'comm'=>$f_arr[2]));
                            $ret4 = $money_model->where("money_id=".$exps[$f_arr[0]]['money_id'])->setDec('usable_money',$f_arr[1]);
                
                            if(!$ret1 || !$ret4){
                                $issubmit = false;
                                break;
                            }
                        }
                    }
                
                    if($issubmit){
                        logrecords($action, $refund_model->get_tablename());          //日志
                        if($refund_model->commit()){
                            if($action == 'insert' || ($action == 'update' && $refund['cur_approver_id'] != $data['cur_approver_id'])){
                                send_email_approve($data['cur_approver_id'], '还款单审批通知', "您有一个还款单审批请求！",U("Refund/info",array('id'=>$ret)));   //发邮件
                            }
                            $this->ajaxReturn(array('status'=>1));
                        }
                    }
                }
            }
            $refund_model->rollback();        //回滚
            $this->ajaxReturn(array('status'=>0, 'msg'=>$msg));
        }
        
        $proj_model = new \Home\Model\ProjectModel();
        $id = I('get.id');
        $app_real_name = session('real_name');
        $depart_id = session('depart_id');
        if($id){
            $id = intval($id);
            $refund = $refund_model->get_one($id);
            if($refund){
                //判断是否有权限进行编辑操作
                if($refund['crt_user_id'] != $this->user_id && session('lvl_id')!=1){
                    redirect(U('Refund:index'), 2, '您没有权限对该借款单进行操作!');
                }
                
                //获取款项列表
                if($refund['result'] == -1){
                    $money_list = $rm_model->get_list("ref_id=$id and is_cancel=1");
                }else{
                    $money_list = $rm_model->get_list("ref_id=$id and is_del=1");
                }
                
                $costs = $money_model->get_moneyandcost_list($refund['borrow_id']);
                $this->assign('costs', $costs);
                $this->assign('money_list', $money_list);
                
                //获取项目编号
                $proj = $proj_model->get_one($refund['proj_id']);
                $refund['proj_no'] = $proj['proj_no'];
                
                $borrows = $borrow_model->get_proj_borrows($refund['proj_id'], $refund['crt_user_id']);
                
                //申请人
                $user_model = new \Home\Model\UserModel();
                $apply_user = $user_model->get_one($refund['crt_user_id']);
                $app_real_name = $apply_user['real_name'];
                $depart_id = $apply_user['depart_id'];
                
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($refund['ref_no'], 4);
                if($approve['aprv_user_id1']>0){
                    $app_user = $user_model->get_one($approve['aprv_user_id1']);
                    if($app_user){
                        $approve['user_real_name1'] = $app_user['real_name'];
                    }
                }
                
                //是否显示选择审批人功能
                $is_show_app = false;
                if($approve['result'] != -1){
                    if($lvl_id == 1 || ($refund['crt_user_id'] == $user_id  && $approve['aprv_result1'] == 0)){   //管理员或者发起者
                        $is_show_app = true;
                    }
                }
                
                //审批人列表
                if($is_show_app == true){
                    $approve_users = getApproverList($refund['crt_user_id'], $user_id, $lvl_id, 4, $refund['crt_time']);
                }
                
                //是否重新发启按钮
                $is_restart = false;
                if($approve['result'] == -1 && ($lvl_id==1 || $refund['crt_user_id']==$user_id)){
                    $is_restart = true;
                }
                
                $this->assign('schedule', $approve);
                $this->assign('borrows', $borrows);
                $this->assign('refund', $refund);
            }
        }else{
            $is_show_app = true;
            $is_restart = false;
            //审批人列表
            $approve_users = getApproverList($user_id, $user_id, $lvl_id, 4, time());
        }
        
        //获取所在部门
        $depart_model = new \Home\Model\DepartModel();
        $depart = $depart_model->get_one($depart_id);
        
        
        //获取该用户涉及到的项目列表
        $budget_model = new \Home\Model\BudgetModel();
        $projs = $budget_model->get_list_over($lvl_id,$user_id);
        
        $this->assign('is_show_app', $is_show_app);
        $this->assign('is_restart', $is_restart);
        $this->assign('app_real_name', $app_real_name);
        $this->assign('depart', $depart);
        $this->assign('projs', $projs);
        $this->assign('approve_users', $approve_users);
        $this->display('Refund:add');
    }
    
    /*
     *  信息页
     **/
    public function infoAction(){
        $id = I('get.id');
        if($id){
            $id = intval($id);
            $refund_model = new \Home\Model\RefundModel();
            $refund = $refund_model->get_one($id);
            
            if($refund){
                $user_id = $this->user_id;
                $proj_id = $refund['proj_id'];
                $lvl_id = session('lvl_id');
                
                //判断是否有权限进入
                if($refund['crt_user_id'] != $user_id && $refund['cur_approver_id'] != $user_id && !get_access_allvoucher($user_id)){         
                    redirect(U('Refund:index'), 2, '您没有权限对该借款单进行操作!');
                }
                
                $borrow_model = new \Home\Model\BorrowModel();
                $money_model = new \Home\Model\MoneyModel();
                $proj_model = new \Home\Model\ProjectModel();
                $user_model = new \Home\Model\UserModel();
                $rm_model = new \Home\Model\RefundMoneyModel();
                
                //获取款项列表
                if($refund['result'] == -1){
                    $money_list = $rm_model->get_list("ref_id=$id and is_cancel=1");
                }else{
                    $money_list = $rm_model->get_list("ref_id=$id and is_del=1");
                }
                $costs = $money_model->get_moneyandcost_list($refund['borrow_id']);
                foreach ($money_list as &$row) {
                    foreach ($costs as $c_row) {
                        if($row['cost_id'] == $c_row['id']){
                            $row['usable_money'] = $c_row['usable_money'];
                            break;
                        }
                    }
                }
                $this->assign('costs', $costs);
                $this->assign('money_list', $money_list);
                
                //获取项目编号
                $proj = $proj_model->get_one($refund['proj_id']);
                $refund['proj_no'] = $proj['proj_no'];
                
                //获取借款单
                $borrows = $borrow_model->get_list("borrow_id={$refund['borrow_id']} and is_del=1");
                
                //申请人
                $apply_user = $user_model->get_one($refund['crt_user_id']);
                
                //申请人获取所在部门
                $depart_model = new \Home\Model\DepartModel();
                $depart = $depart_model->get_one($apply_user['depart_id']);
                
                //获取项目列表
                $projs = $proj_model->get_list("proj_id={$refund['proj_id']} and is_del=1","proj_id,proj_name");
                
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($refund['borrow_no'], 4);
                for ($i = 1; $i < 6; $i++) {
                    if($approve['aprv_user_id'.$i]>0){
                        $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                        if($app_user){
                            $approve['user_real_name'.$i] = $app_user['real_name'];
                        }
                    }else{
                        break;
                    }
                }
                
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($refund['ref_no'], 4);
                if($approve['aprv_user_id1']>0){
                    $app_user = $user_model->get_one($approve['aprv_user_id1']);
                    if($app_user){
                        $approve['user_real_name1'] = $app_user['real_name'];
                    }
                }
                
                
                //是否显示选择审批人功能
                $is_show_app = false;
                if($approve['result'] != -1){
                    if($lvl_id == 1 || ($refund['crt_user_id'] == $user_id  && $approve['aprv_result1'] == 0)){   //管理员或者发起者
                        $is_show_app = true;
                    }
                }
                
                //审批人列表
                if($is_show_app == true){
                    $approve_users = getApproverList($refund['crt_user_id'], $user_id, $lvl_id, 4, $refund['crt_time']);
                }
                
                //是否重新发启按钮
                $is_restart = false;
                if($approve['result'] == -1 && ($lvl_id==1 || $refund['crt_user_id']==$user_id)){
                    $is_restart = true;
                }
                
                $this->assign('borrows', $borrows);
                $this->assign('refund', $refund);
                $this->assign('money_list', $money_list);
                $this->assign('apply_user', $apply_user);
                $this->assign('depart', $depart);
                $this->assign('projs', $projs);
                $this->assign('schedule', $approve);
                $this->assign('is_show_app', $is_show_app);
                $this->assign('is_restart', $is_restart);
                $this->assign('user_id', $this->user_id);
                $this->display('Refund:info');
            }
        }
    }
    
    /*
     *  审批结果提交
     **/
    public function operateAction(){
        $id = I("post.ref_id");
        $num = I("post.num");
        $result = I("post.result");
        $refund_model = new \Home\Model\RefundModel();
        $refund = $refund_model->get_one($id);
        if($refund){
            $app_model = new \Home\Model\ApproveModel();
            $rm_model = new \Home\Model\RefundMoneyModel();
            if($app_model->getApproveSchedule($refund['ref_no'] ,4)){              //判断是否有审批记录
                $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                $data['result'] = $result;
                if($result == -1){
                    $data['aprv_opinion1'] = I("post.option");
                }
                
                $refund_model->startTrans();           //开启事务
                $ret = $app_model->update_data($data, "aprv_no='{$refund['ref_no']}' and aprv_type=4");
                $ret1 = $refund_model->update_data(array('result'=>$result), "ref_id = $id");
                if($ret && $ret1){
                    $money_model = new \Home\Model\MoneyModel();
                    $expenses_model = new \Home\Model\ExpensesModel();
                    
                    $r_moneys = $rm_model->get_list("ref_id = $id and is_del=1");
                    if($r_moneys){
                        if($result == -1){
                            //不同意操作，把费用重新加回借款单余额
                            $ret2 = $rm_model->update_data(array('is_del'=>-1,'is_cancel'=>1), "ref_id = $id");
                            foreach ($r_moneys as $r_money) {
                                $ret3 = $money_model->where("obj_id={$refund['borrow_id']} and obj_type=1 and cost_id={$r_money['cost_id']} and is_del=1")->setInc('usable_money', $r_money['money']);
                                if(!$ret2 || !$ret3){
                                    $refund_model->rollback();
                                    $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                                    break;
                                }
                            }
                            $content = "您的一个还款单审批未获通过，请注意查看。";
                        }else{
                            //同意操作，把费用返回到预算单余额
                            foreach ($r_moneys as $r_money) {
                                $ret3 = $expenses_model->where("proj_id={$refund['proj_id']} and cost_id={$r_money['cost_id']}")->setInc('usable_money', $r_money['money']);
                                if(!$ret3){
                                    $refund_model->rollback();
                                    $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                                    break;
                                }
                            }
                            $content = "您的一个还款单审批已通过，请注意查看。";
                        }
                        $refund_model->commit();
                        send_email_approve($refund['crt_user_id'], '借款单审批通知', $content."单号：{$refund['ref_no']}。", U("Refund/info",array('id'=>$id)));           //发邮件
                        logrecords("update", $app_model->get_tablename());          //日志
                        $this->ajaxReturn(array('status'=>1));
                    }
                }
                $refund_model->rollback();   //回滚
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    }
    
    /*
     *  选择审批人提交
     **/
    public function chooseAction(){
        $id = I("post.borrow_id");
        $cur_approver_id = I("post.cur_approver_id");
        $borrow_model = new \Home\Model\BorrowModel();
        $borrow = $borrow_model->get_one($id);
        if($borrow){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($borrow['borrow_no'], 2);
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
            
            //更新borrow表当前审批人
            $borrow_model->startTrans();     //开启事务
            $ret = $borrow_model->update_data(array('cur_approver_id'=>$cur_approver_id), "borrow_id=".$id);
            if($ret){
                $ret1 = $app_model->updateApproved($approve, $cur_approver_id, $lvl_id, $user_id);
                if($ret && $ret1){
                    logrecords("update", $app_model->get_tablename());          //日志
                    $borrow_model->commit();
                    send_email_approve($cur_approver_id, '还款单审批通知', "您有一个还款单审批请求！",U("Refund/info",array('id'=>$id)));   //发邮件
                    $this->ajaxReturn(array('status'=>1));
                }else{
                    $borrow_model->rollback();
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
            $id = I('post.ref_id');
            $refund_model = new \Home\Model\RefundModel();
            $refund = $refund_model->get_one($id);
            
            if($refund){
                $app_model = new \Home\Model\ApproveModel();
                $money_model = new \Home\Model\MoneyModel();
                $rm_model = new \Home\Model\RefundMoneyModel();
                $approve = $app_model->getApproveSchedule($refund['ref_no'] ,4);
                if(session('lvl_id')!=1 && $refund['result']!=0){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可删除！'));
                }
                
                $refund_model->startTrans();                 //开启事务
                $r_moneys = $rm_model->get_list("ref_id = $id and is_del=1");              //还款单涉及到的款项列表
                $ret = $refund_model->update_data(array('is_del'=>-1), "ref_id = $id");
                $ret1 = $rm_model->update_data(array('is_del'=>-1), "ref_id = $id");
                $ret3 = $app_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "aprv_no='{$refund['ref_no']}' and aprv_type=4");
                if($ret && $ret1 && $ret3){
                    foreach ($r_moneys as $r_money) {
                        $ret2 = $money_model->where("obj_id={$refund['borrow_id']} and obj_type=1 and cost_id={$r_money['cost_id']} and is_del=1")->setInc('usable_money', $r_money['money']);
                        if(!$ret2){
                            $refund_model->rollback();
                            $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                            break;
                        }
                    }
                    $refund_model->commit();
                    logrecords('delete', $refund_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
                $refund_model->rollback();          //回滚
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'借款单不存在！'));
        }
        
    }
    
    /*
     *  重新发启审核
     **/
    public function restartAction(){
        $id = I("post.ref_id");
        $refund_model = new \Home\Model\RefundModel();
        $refund = $refund_model->get_one($id);
        if($refund){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($refund['ref_no'] ,4);
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
                
                $refund_model->startTrans();
                $ret = $app_model->update_data($data, "aprv_id={$approve['aprv_id']}");
                $ret1 = $refund_model->update_data(array('cur_approver_id'=>0,'result'=>0), "ref_id = $id");
                if($ret && $ret1){
                    //重启操作，重新减去报销单相关费用余额
                    $money_model = new \Home\Model\MoneyModel();
                    $rm_model = new \Home\Model\RefundMoneyModel();
                    $r_moneys = $rm_model->get_list("ref_id = $id and is_cancel=1");
                    foreach ($r_moneys as $r_money) {
                        $ret2 = $rm_model->update_data(array('is_cancel'=>0,'is_del'=>1), 'rm_id='.$r_money['rm_id']);
                        //报销单减去相关费用余额
                        $ret3 = $money_model->where("obj_id={$refund['borrow_id']} and obj_type=1 and cost_id={$r_money['cost_id']} and is_del=1")->setDec('usable_money', $r_money['money']);
                        if(!$ret2 || !$ret3){
                            $refund_model->rollback();
                            $this->ajaxReturn(array('status'=>0, 'msg'=>"操作失败1！"));
                            break;
                        }
                    }
                    $refund_model->commit();
                    logrecords("update", $app_model->get_tablename());          //日志
                    $this->ajaxReturn(array('status'=>1));
                }
                $refund_model->rollback();
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    /*
     *  获取项目信息
     **/
    public function getprojnoAction(){
        $id = I('post.proj_id');
        $proj_model = new \Home\Model\ProjectModel();
        $proj = $proj_model->get_one($id);
        if($proj){
            $user_id = $this->user_id;
            $proj_id = I('post.proj_id');
            $borrow_model = new \Home\Model\BorrowModel();
            $borrows = $borrow_model->get_proj_borrows($proj_id,$user_id);
            
            $this->ajaxReturn(array('status'=>1, 'proj_no'=>$proj['proj_no'], 'borrows'=>$borrows));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'项目不存在'));
    }
    
    /*
     *  获取借款单的款项列表
     **/
    public function getborrowexpsAction(){
        $borrow_id = I('post.borrow_id');
        $money_model = new \Home\Model\MoneyModel();
        $costs = $money_model->get_moneyandcost_list($borrow_id);
        if($costs){
            $this->ajaxReturn(array('status'=>1, 'costs'=>$costs));
        }
    }    
    
    /*
     *  获取借款单号
     **/
    public function getBorrowNo(){
        return "HKD".date('YmdHis').sprintf("%04d",rand(0, 1000));
    }
    
}