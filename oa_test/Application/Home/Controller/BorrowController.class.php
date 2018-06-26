<?php
/**
 * OA系统--借款单
 */
namespace Home\Controller;
use Think\Page;
class BorrowController extends BaseController{
    
    public $check_access = true;
    public $head_title = '借款单';
    
    public function indexAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\ApproveModel();
        $proj_model = new \Home\Model\ProjectModel();
        if(get_access_allvoucher($user_id)){                    
            $where = "is_del = 1";
            if($lvl_id!=2){
                //获取所属公司下属的项目相关预算单
                $where .= " and proj_id in (".$proj_model->get_wherebycompanyid(session('company_id')).")";
            }
        }else{
            $where = "is_del = 1 and (user_id = $user_id";
            
            //获取归该用户审批的借款单列表
            $awhere = $app_model->get_whereaprrove($user_id, 2);
            $where .= " or borrow_no in ($awhere)";
            
            //获取该用户参与项目的相关借款单
            $proj_where = $proj_model->get_wheresqlbyuserid($lvl_id,$user_id);
            $where .= " or proj_id in ($proj_where)";
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
        
        $borrow_model = new \Home\Model\BorrowModel();
        $count = $borrow_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $borrow_model->where($where)->order("borrow_id desc")->limit($page->firstRow.','.$page->listRows)->select();
        
        $depart_model = new \Home\Model\DepartModel();
        $user_model = new \Home\Model\UserModel();
        foreach ($list as &$row) {
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            $row['borrow_way_name'] = $row['borrow_way'] == 1 ? "现金" : "转账" ;
            
            $proj = $proj_model->get_one($row['proj_id']);               //项目
            $row['proj_name'] = $proj['proj_name'];
            
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
            
            if($lvl_id<5 && $row['result'] == 1){
                $rec_model = new \Home\Model\RecoupedModel();
                $refund_model = new \Home\Model\RefundModel();
                $row['total_recouped_money'] = "0.00";
                $row['total_refund_money'] = "0.00";
                $rec = $rec_model->where("borrow_id={$row['borrow_id']} and is_del=1 and result=1")->field("sum(tot_amt) as tot_amt")->find();
                $refund = $refund_model->where("borrow_id={$row['borrow_id']} and is_del=1 and result=1")->field("sum(tot_amt) as tot_amt")->find();
                if($rec["tot_amt"]){
                    $row['total_recouped_money'] = $rec["tot_amt"];
                }
                if($refund["tot_amt"]){
                    $row['total_refund_money'] = $refund["tot_amt"];
                }
            }
            
            //获取审批状态
            $approve = $app_model->getApproveSchedule($row['borrow_no'], 2);
            for ($i = 1; $i < 6; $i++) {
                if($approve['aprv_user_id'.$i] == $row['cur_approver_id']){
                    $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                    $row['result_info'] = '等待审批';
                    if($approve['aprv_result'.$i] == 1){
                        $row['result_info'] = '同意';
                    }elseif($approve['aprv_result'.$i] == -1){
                        $row['result_info'] = '不同意';
                    }
                    
                    if(($i == 5  || $app_user['lvl_id']==4) && $approve['aprv_result'.$i] == 1){
                        $row['result_info'] = '流程已完成';
                    }else{
                        $row['result_info'] = $row['result_info']."（{$app_user['real_name']}）";
                    }
                    
                    break;
                }
            }
            
            //操作动作
            $row['action'] = "info";
            if($lvl_id == 1 || ($row['user_id'] == $user_id && $i==1 && $approve['aprv_result1']==0)){
                $row['action'] = "add";
            }
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('user_id', $user_id);
        $this->assign('lvl_id', $lvl_id);
        $this->display('Borrow:index');
    }
    
    /*  
     *  新增、修改用户页
     **/
    public function addAction(){
        $budget_model = new \Home\Model\BudgetModel();
        $borrow_model = new \Home\Model\BorrowModel();
        $bank_model = new \Home\Model\BankModel();
        $money_model = new \Home\Model\MoneyModel();
        $expenses_model = new \Home\Model\ExpensesModel();
        
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        
        if(IS_POST){
            $data['proj_id'] = I('post.proj_id');
            $data['borrow_way'] = I('post.borrow_way');
            $data['bnk_branch'] = I('post.bnk_branch');
            $data['bnk_acct'] = I('post.bnk_acct');
            $id = I("post.borrow_id");
            $fund_str = I("post.fund_str");
            $data['tot_amt'] = I('post.tot_amt');
            $data['tot_amt_d'] = I('post.tot_amt_d');
            $bud_id = $data['bud_id'] = I('post.bud_id');
            $cur_approver_id = $data['cur_approver_id'] = I('post.cur_approver_id');
            
            //项目是否截止
            $budget = $budget_model->get_one($bud_id);
            if($budget){
                $is_over = $budget_model->check_is_over($budget);
                if($is_over['status']==true){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>$is_over['msg']));
                }
            }
            
            //基础数据验证
            if(!$data['proj_id']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择项目！'));
            if($data['borrow_way'] != '1' && $data['borrow_way'] != '2') $this->ajaxReturn(array('status'=>0, 'msg'=>'借款方式不正确！'));
            if($data['borrow_way'] == '2'){
                if(!$data['bnk_branch']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请填写银行！'));
                if(!$data['bnk_acct']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请填写银行账号！'));
            }
            if(!$data['cur_approver_id'] && !$id) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            
            //判断是否可以更新
            $app_model = new \Home\Model\ApproveModel();
            $approve = null;
            $borrow = null;
            $action = '';
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $borrow = $borrow_model->get_one($id);
                $approve = $app_model->getApproveSchedule($borrow['borrow_no'], 2);
                if(session('lvl_id')!=1){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                    }
                }
            }
            
            //新增、更新数据
            $borrow_model->startTrans();     //开启事务
            if($id){
                $ret = $borrow_model->update_data($data, "borrow_id = $id");            //更新借款单表
                if($ret && $borrow['cur_approver_id'] != $data['cur_approver_id']){
                    $ret3 = $app_model->updateApproved($approve, $data['cur_approver_id'], $lvl_id, $user_id);
                }else{
                    $ret3 = true;
                }
                $action = 'update';
            }else{
                $data['user_id'] = $this->user_id;
                $data['crt_user_id'] = $this->user_id;
                $data['borrow_no'] = $this->getBorrowNo();
                $ret = $borrow_model->insert_data($data);                               //插入借款单表
                $ret3 = true;
                $action = 'insert';
            } 
            $msg = "保存失败！";
            if($ret && $ret3){     
                if($action == 'update'){
                    $moneys = $money_model->get_list("obj_id = $id and obj_type=1 and is_del=1");
                    if($moneys){
                        $ret2 = $money_model->update_data(array('is_del'=>-1), "obj_id = $id and obj_type=1");
                        if($ret2){
                            foreach ($moneys as $money) {
                                $ret_exp = $expenses_model->where("proj_id={$borrow['proj_id']} and cost_id={$money['cost_id']}")->setInc('usable_money', $money['money']);
                                if(!$ret_exp){
                                    $borrow_model->rollback();
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
                    $ret2 = $app_model->insert_data(array('aprv_no'=>$data['borrow_no'],'aprv_type'=>2,'aprv_user_id1'=>$data['cur_approver_id'],'proj_id'=>$data['proj_id']));
                }
                if($ret2){
                    $issubmit = true;
                    $fund_str = str_replace("&amp;", "&", $fund_str);
                    $fund_array = explode(";", $fund_str);
                    
                    //获取预算单中款项列表
                    if($bud_id){
                        $exps = $expenses_model->get_array_list($bud_id);
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
                                $usable_money = $exps[$f_arr[0]]['usable_money'] = $exps[$f_arr[0]]['usable_money'] - $f_arr[1];         //可用余额
                                if($usable_money < 0){
                                    $msg = '款项：'.$f_arr[3].'可用余额不足！';
                                    $issubmit = false;
                                    break;
                                }
                                $ret1 = $money_model->insert_data(array('obj_id'=>$ret,'obj_type'=>1,'cost_id'=>$f_arr[0], 'money'=>$f_arr[1], 'usable_money'=>$f_arr[1], 'comm'=>$f_arr[2]));
                                $ret4 = $expenses_model->where("exp_id=".$exps[$f_arr[0]]['exp_id'])->setDec('usable_money',$f_arr[1]);
                                if(!$ret1 || !$ret4){
                                    $issubmit = false;
                                    break;
                                }
                            }
                        }
                    }
                    
                    
                    if($issubmit){
                        logrecords($action, $borrow_model->get_tablename());          //日志
                        if($borrow_model->commit()){
                            if($action == 'insert' || ($action == 'update' && $borrow['cur_approver_id'] != $data['cur_approver_id'])){
                                send_email_approve($data['cur_approver_id'], '借款单审批通知', "您有一个借款单审批请求！",U("Borrow/info",array('id'=>$ret)));
                            }
                            $this->ajaxReturn(array('status'=>1));
                        }
                    }
                }
            }
            $borrow_model->rollback();        //回滚
            $this->ajaxReturn(array('status'=>0, 'msg'=>$msg));
        }
        
        $proj_model = new \Home\Model\ProjectModel();
        $id = I('get.id');
        $app_real_name = session('real_name');
        $depart_id = session('depart_id');
        if($id){
            $id = intval($id);
            $borrow = $borrow_model->get_one($id);
            if($borrow){
                //判断是否有权限进行编辑操作
                if($borrow['user_id'] != $this->user_id && session('lvl_id')!=1){
                    redirect(U('Borrow:index'), 2, '您没有权限对该借款单进行操作!');
                }
                
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($borrow['borrow_no'], 2);
                //审批已经开始必须进入详情页
                if($approve['aprv_result1']>0){
                    redirect(U("Borrow/info")."?id=".$id);
                }
                
                //获取款项列表
                if($borrow['result'] == -1){
                    $money_list = $money_model->get_list("obj_id=$id and obj_type=1 and is_cancel=1");
                }else{
                    $money_list = $money_model->get_list("obj_id=$id and obj_type=1 and is_del=1");
                }
                
                $costs = $expenses_model->get_expsandcost_list($borrow['bud_id']);
                $this->assign('costs', $costs);
                $this->assign('money_list', $money_list);
                
                //获取项目编号
                $proj = $proj_model->get_one($borrow['proj_id']);
                $borrow['proj_no'] = $proj['proj_no'];
                
                //申请人
                $user_model = new \Home\Model\UserModel();
                $apply_user = $user_model->get_one($borrow['user_id']);
                $app_real_name = $apply_user['real_name'];
                $depart_id = $apply_user['depart_id'];
                
                //是否显示选择审批人功能
                $is_show_app = false;
                if($approve['result'] != -1){
                    $is_show_app = check_is_show_app($borrow['user_id'], $user_id, $lvl_id, $approve, $borrow['crt_time']);
                }
                
                //审批人列表
                if($is_show_app == true){
                    $approve_users = getApproverListByProj($borrow['proj_id'],$borrow['user_id'], $user_id, $lvl_id, $borrow['crt_time'], $approve, 2);
                }
                
                //是否重新发启按钮
                $is_restart = false;
                if($approve['result'] == -1 && ($lvl_id==1 || $borrow['user_id']==$user_id)){
                    $is_restart = true;
                }
                
                //审批进度条内容
                $approve_arr = getApprovers($approve, $borrow['crt_user_id']);
                
                $this->assign('approve_arr', $approve_arr);
                $this->assign('borrow', $borrow);
            }
        }else{
            $is_show_app = true;
            $is_restart = false;
        }
        
        //获取所在部门
        $depart_model = new \Home\Model\DepartModel();
        $depart = $depart_model->get_one($depart_id);
        
        //获取银行列表
        $banks = $bank_model->get_lists("is_del=1", 0, 1000, array('bank_id asc'));
        
        //获取该用户涉及到的项目列表
        $budget_model = new \Home\Model\BudgetModel();
        $projs = $budget_model->get_list_over($lvl_id,$user_id);
        
        $this->assign('is_show_app', $is_show_app);
        $this->assign('is_restart', $is_restart);
        $this->assign('app_real_name', $app_real_name);
        $this->assign('depart', $depart);
        $this->assign('banks', $banks);
        $this->assign('projs', $projs);
        $this->assign('user_id', $user_id);
        $this->assign('approve_users', $approve_users);
        $this->display('Borrow:add');
    }
    
    /*
     *  信息页
     **/
    public function infoAction(){
        $id = I('get.id');
        if($id){
            $id = intval($id);
            $borrow_model = new \Home\Model\BorrowModel();
            $bank_model = new \Home\Model\BankModel();
            $money_model = new \Home\Model\MoneyModel();
            $proj_model = new \Home\Model\ProjectModel();
            $user_model = new \Home\Model\UserModel();
            $borrow = $borrow_model->get_one($id);
            if($borrow){
                $user_id = $this->user_id;
                $proj_id = $borrow['proj_id'];
                $lvl_id = session('lvl_id');
                
                if($borrow['crt_time']>get_c_date()){
                    //判断是否有权限进入
                    $is_projlist = checkInProjList($user_id,$lvl_id,$borrow['proj_id']);
                    if($borrow['user_id']!=$user_id && !checkInApproverList($user_id,$borrow['borrow_no'],2) && !$is_projlist){
                        redirect(U('Borrow:index'), 2, '您没有权限对该借款单进行操作!');
                    }
                    
                    //获取款项列表
                    if($borrow['result'] == -1){
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=1 and is_cancel=1");
                    }else{
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=1 and is_del=1");
                    }
                    $expenses_model = new \Home\Model\ExpensesModel();
                    $costs = $expenses_model->get_expsandcost_list($borrow['bud_id']);
                    foreach ($money_list as &$row) {
                        foreach ($costs as $c_row) {
                            if($row['cost_id'] == $c_row['id']){
                                $row['usable_money'] = $c_row['usable_money'];
                                break;
                            }
                        }
                    }
                    
                    //获取项目编号
                    $proj = $proj_model->get_one($proj_id);
                    $borrow['proj_no'] = $proj['proj_no'];
                    
                    //申请人
                    $apply_user = $user_model->get_one($borrow['user_id']);
                    
                    //申请人获取所在部门
                    $depart_model = new \Home\Model\DepartModel();
                    $depart = $depart_model->get_one($apply_user['depart_id']);
                    
                    //获取项目列表
                    $projs = $proj_model->get_list("proj_id={$borrow['proj_id']} and is_del=1","proj_id,proj_name");
                    
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($borrow['borrow_no'], 2);
                    $approve_arr = getApprovers($approve, $borrow['crt_user_id']);
                    
                    //是否显示选择审批人功能
                    $is_show_app = check_is_show_app($borrow['user_id'], $user_id, $lvl_id, $approve, $borrow['crt_time']);
                    
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getApproverListByProj($borrow['proj_id'],$apply_user['user_id'], $user_id, $lvl_id, $borrow['crt_time'], $approve, 2);
                        $this->assign('approve_users', $approve_users);
                    }
                    
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $borrow['user_id']==$user_id)){
                        $is_restart = true;
                    }
                    
                    //级别大于5的可以看的信息
                    if($lvl_id<5 && $borrow['result']==1){
                        $depart_model = new \Home\Model\DepartModel();
                        $rec_model = new \Home\Model\RecoupedModel();
                        $refund_model = new \Home\Model\RefundModel();
                        $recoupeds = $rec_model->get_list("borrow_id={$borrow['borrow_id']} and is_del=1");
                        $refunds = $refund_model->get_list("borrow_id={$borrow['borrow_id']} and is_del=1");
                        foreach ($recoupeds as &$row) {
                            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
                            $row['borrow_way_name'] = $row['borrow_way'] == 1 ? "现金" : "转账" ;
                    
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
                    
                            $row['result_info'] = '等待审批';
                            if($row['result'] == 1){
                                $row['result_info'] = '流程已完成';
                            }elseif($row['result'] == -1){
                                $row['result_info'] = '不同意';
                            }
                        }
                    
                        foreach ($refunds as &$row) {
                            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
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
                            $row['result_info'] = '等待审批';
                            if($row['result'] == 1){
                                $row['result_info'] = '流程已完成';
                            }elseif($row['result'] == -1){
                                $row['result_info'] = '不同意';
                            }
                        }
                    
                        $this->assign('recoupeds', $recoupeds);
                        $this->assign('refunds', $refunds);
                    }
                    
                    $this->assign('lvl_id', $lvl_id);
                    $this->assign('borrow', $borrow);
                    $this->assign('money_list', $money_list);
                    $this->assign('apply_user', $apply_user);
                    $this->assign('depart', $depart);
                    $this->assign('projs', $projs);
                    $this->assign('approve_arr', $approve_arr);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('user_id', $this->user_id);
                    $this->display('Borrow:info');
                }else{
                    //判断是否有权限进入
                    $is_projlist = checkInProjList($user_id,$lvl_id,$borrow['proj_id']);
                    if($borrow['user_id']!=$user_id && !checkInApproverList($user_id,$borrow['borrow_no'],2) && !$is_projlist){         
                        redirect(U('Borrow:index'), 2, '您没有权限对该借款单进行操作!');
                    }
                    
                    //获取款项列表
                    if($borrow['result'] == -1){
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=1 and is_cancel=1");
                    }else{
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=1 and is_del=1");
                    }
                    $expenses_model = new \Home\Model\ExpensesModel();
                    $costs = $expenses_model->get_expsandcost_list($borrow['bud_id']);
                    foreach ($money_list as &$row) {
                        foreach ($costs as $c_row) {
                            if($row['cost_id'] == $c_row['id']){
                                $row['usable_money'] = $c_row['usable_money'];
                                break;
                            }
                        }
                    }
            
                    //获取项目编号
                    $proj = $proj_model->get_one($proj_id);
                    $borrow['proj_no'] = $proj['proj_no'];
                    
                    //申请人
                    $apply_user = $user_model->get_one($borrow['user_id']);
                    
                    //申请人获取所在部门
                    $depart_model = new \Home\Model\DepartModel();
                    $depart = $depart_model->get_one($apply_user['depart_id']);
                    
                    //获取项目列表
                    $projs = $proj_model->get_list("proj_id={$borrow['proj_id']} and is_del=1","proj_id,proj_name");
                    
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($borrow['borrow_no'], 2);
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
                    
                    //是否显示选择审批人功能
                    $is_show_app = check_is_show_app($borrow['user_id'], $user_id, $lvl_id, $approve, $borrow['crt_time']);
                    
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getApproverListByProj($borrow['proj_id'],$apply_user['user_id'], $user_id, $lvl_id, $borrow['crt_time'], $approve, 2);
                        $this->assign('approve_users', $approve_users);
                    }
                    
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $borrow['user_id']==$user_id)){
                        $is_restart = true;
                    }
                    
                    //级别大于5的可以看的信息
                    if($lvl_id<5 && $borrow['result']==1){
                        $depart_model = new \Home\Model\DepartModel();
                        $rec_model = new \Home\Model\RecoupedModel();
                        $refund_model = new \Home\Model\RefundModel();
                        $recoupeds = $rec_model->get_list("borrow_id={$borrow['borrow_id']} and is_del=1");
                        $refunds = $refund_model->get_list("borrow_id={$borrow['borrow_id']} and is_del=1");
                        foreach ($recoupeds as &$row) {
                            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
                            $row['borrow_way_name'] = $row['borrow_way'] == 1 ? "现金" : "转账" ;
                    
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
                    
                            $row['result_info'] = '等待审批';
                            if($row['result'] == 1){
                                $row['result_info'] = '流程已完成';
                            }elseif($row['result'] == -1){
                                $row['result_info'] = '不同意';
                            }
                        }
                        
                        foreach ($refunds as &$row) {
                            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
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
                            $row['result_info'] = '等待审批';
                            if($row['result'] == 1){
                                $row['result_info'] = '流程已完成';
                            }elseif($row['result'] == -1){
                                $row['result_info'] = '不同意';
                            }
                        }
                    
                        $this->assign('recoupeds', $recoupeds);
                        $this->assign('refunds', $refunds);
                    }
                    
                    $this->assign('lvl_id', $lvl_id);
                    $this->assign('borrow', $borrow);
                    $this->assign('money_list', $money_list);
                    $this->assign('apply_user', $apply_user);
                    $this->assign('depart', $depart);
                    $this->assign('projs', $projs);
                    $this->assign('schedule', $approve);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('user_id', $this->user_id);
                    $this->display('Borrow:info_old');
                }
            }
        }
    }
    
    /*
     *  审批结果提交
     **/
    public function operateAction(){
        $id = I("post.borrow_id");
        $num = I("post.num");
        $result = I("post.result");
        $borrow_model = new \Home\Model\BorrowModel();
        $borrow = $borrow_model->get_one($id);
        
        if(check_is_over($borrow['proj_id']) && $result==1){
            $this->ajaxReturn(array('status'=>0, 'msg'=>'该项目已结束，只能进行不同意操作！'));
        }
        
        if($borrow['crt_time']>get_c_date()){
            $cur_approver_id = I("post.cur_approver_id");
            $lvl_id = session('lvl_id');
            if($result == 1 && $lvl_id != 4 && $lvl_id != 2 && $cur_approver_id==0){          //同意操作，并且不是总经理和出纳，需要判断是否选择下一步审批人
                $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            }
            $app_model = new \Home\Model\ApproveModel();
            if($app_model->getApproveSchedule($borrow['borrow_no'] ,2)){
                $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                if($lvl_id == 4 || $result == -1){
                    $data['result'] = $result;
                }
                
                if(I("post.option")) $data['aprv_opinion'.$num] = I("post.option");
                
                $borrow_model->startTrans();           //开启事务
                $ret = $app_model->update_data($data, "aprv_no='{$borrow['borrow_no']}' and aprv_type=2");
                if($ret){
                    $user_id = $this->user_id;
                    if($lvl_id != 4 && $result == 1){
                        if($lvl_id == 2){
                            choose_approval_user($id,2,get_cw_userid(),$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给出纳
                        }else{
                            choose_approval_user($id,2,$cur_approver_id,$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给peter
                        }
                    }
                    
                    if($lvl_id == 4 || $result == -1){
                        $borrow_model->update_data(array('result'=>$result), "borrow_id = {$borrow['borrow_id']}");
                        $content = "您的一个借款单审批已通过，请注意查看。";
                        if($result == -1){
                            //不同意操作，把费用重新加回预算单余额
                            $money_model = new \Home\Model\MoneyModel();
                            $expenses_model = new \Home\Model\ExpensesModel();
                            $moneys = $money_model->get_list("obj_id = $id and obj_type=1 and is_del=1");
                            if($moneys){
                                foreach ($moneys as $money) {
                                    $ret2 = $money_model->update_data(array('is_del'=>-1,'is_cancel'=>1), "money_id=".$money['money_id']);
                                    $ret3 = $expenses_model->where("proj_id={$borrow['proj_id']} and cost_id={$money['cost_id']}")->setInc('usable_money', $money['money']);
                                    if(!$ret2 || !$ret3){
                                        $borrow_model->rollback();
                                        $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                                        break;
                                    }
                                }
                            }else{
                                $borrow_model->rollback();
                                $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败2！"));
                            }
                            $content = "您的一个借款单审批未获通过，请注意查看。";
                        }
                        send_email_approve($borrow['user_id'], '借款单审批通知', $content."单号：{$borrow['borrow_no']}。", U("Borrow/info",array('id'=>$id)));           //发邮件
                    }
                    $borrow_model->commit();
                    logrecords("update", $app_model->get_tablename());          //日志
                    $this->ajaxReturn(array('status'=>1));
                }
                $borrow_model->rollback();           //回滚
            }
        }else{
            $app_model = new \Home\Model\ApproveModel();
            if($app_model->getApproveSchedule($borrow['borrow_no'] ,2)){
                $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                if($num == 5 || $result == -1){
                    $data['result'] = $result;
                    if($result == -1){
                        $data['aprv_opinion'.$num] = I("post.option");
                    }
                }
                $borrow_model->startTrans();           //开启事务
                $ret = $app_model->update_data($data, "aprv_no='{$borrow['borrow_no']}' and aprv_type=2");
                if($ret){
                    $user_id = $this->user_id;
                    $lvl_id = session('lvl_id');
                    if($lvl_id == 2 && $result == 1){
                        choose_approval_user($id,2,get_cw_userid(),$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给peter
                    }
                    if($num == 5 || $result == -1){
                        $borrow_model->update_data(array('result'=>$result), "borrow_id = {$borrow['borrow_id']}");
                        $content = "您的一个借款单审批已通过，请注意查看。";
                        if($result == -1){
                            //不同意操作，把费用重新加回预算单余额
                            $money_model = new \Home\Model\MoneyModel();
                            $expenses_model = new \Home\Model\ExpensesModel();
                            $moneys = $money_model->get_list("obj_id = $id and obj_type=1 and is_del=1");
                            if($moneys){
                                foreach ($moneys as $money) {
                                    $ret2 = $money_model->update_data(array('is_del'=>-1,'is_cancel'=>1), "money_id=".$money['money_id']);
                                    $ret3 = $expenses_model->where("proj_id={$borrow['proj_id']} and cost_id={$money['cost_id']}")->setInc('usable_money', $money['money']);
                                    if(!$ret2 || !$ret3){
                                        $borrow_model->rollback();
                                        $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                                        break;
                                    }
                                }
                            }else{
                                $borrow_model->rollback();
                                $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败2！"));
                            }
                            $content = "您的一个借款单审批未获通过，请注意查看。";
                        }
                        send_email_approve($borrow['user_id'], '借款单审批通知', $content."单号：{$borrow['borrow_no']}。", U("Borrow/info",array('id'=>$id)));           //发邮件
                    }
                    $borrow_model->commit();
                    logrecords("update", $app_model->get_tablename());          //日志
                    $this->ajaxReturn(array('status'=>1));
                }
                $borrow_model->rollback();           //回滚
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    //返回操作
    public function backAction(){
        if(IS_POST){
            $lvl_id = session('lvl_id');
            $user_id = $this->user_id;
            if($lvl_id==2){
                $app_model = new \Home\Model\ApproveModel();
                $obj_id = I('post.obj_id');
                
                //借款单
                $obj_model = new \Home\Model\BorrowModel();
                $where = "borrow_id = '$obj_id'";
                $obj = $obj_model->get_onebywhere($where);
                if($obj){
                    $aprv_no = $obj['borrow_no'];
                }
                
                $approve = $app_model->getApproveSchedule($aprv_no, 2);
                
                $obj_model->startTrans();
                $ret = $obj_model->update_data(array('cur_approver_id'=>$user_id,'result'=>0), $where);
                for ($i = 1; $i < 6; $i++) {
                    if($approve["aprv_user_id".$i] == get_zjl_userid()){
                        break;
                    }
                }
                
                $ret1 = $app_model->update_data(array('aprv_user_id'.($i+1) => 0, 'aprv_result'.$i => 0, 'result'=>0), "aprv_no='$aprv_no' and aprv_type=2");
                if($ret && $ret1){
                    if($obj['result'] == -1){
                        //不同意操作，把费用重新加回预算单余额
                        $money_model = new \Home\Model\MoneyModel();
                        $expenses_model = new \Home\Model\ExpensesModel();
                        $moneys = $money_model->get_list("obj_id={$obj['borrow_id']} and obj_type=1 and is_del=-1 and is_cancel=1");
                        foreach ($moneys as $money) {
                            $ret2 = $money_model->update_data(array('is_cancel'=>0,'is_del'=>1), 'money_id='.$money['money_id']);
                            $ret3 = $expenses_model->where("proj_id={$obj['proj_id']} and cost_id={$money['cost_id']}")->setDec('usable_money', $money['money']);
                            if(!$ret2 || !$ret3){
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
                    send_email_approve($cur_approver_id, '借款单审批通知', "您有一个借款单审批请求！",U("Borrow/info",array('id'=>$id)));   //发邮件
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
            $id = I('post.borrow_id');
            $borrow_model = new \Home\Model\BorrowModel();
            $borrow = $borrow_model->get_one($id);
            if($borrow){
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($borrow['borrow_no'], 2);
                if(!check_permission_left('financial_approve', 'finance')){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可删除！'));
                    }
                }
                
                $borrow_model->startTrans();                 //开启事务
                $ret = $borrow_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "borrow_id = $id");
                $ret1 = $app_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "aprv_no='{$borrow['borrow_no']}' and aprv_type=2");
                if($ret && $ret1){
                    $money_model = new \Home\Model\MoneyModel();
                    $expenses_model = new \Home\Model\ExpensesModel();
                    $moneys = $money_model->get_list("obj_id = $id and obj_type=1 and is_del=1");
                    if($moneys){
                        $ret2 = $money_model->update_data(array('is_del'=>-1), "obj_id = $id and obj_type=1");
                        if($ret2){
                            foreach ($moneys as $money) {
                                $ret_exp = $expenses_model->where("proj_id={$borrow['proj_id']} and cost_id={$money['cost_id']}")->setInc('usable_money', $money['money']);
                                if(!$ret_exp){
                                    $borrow_model->rollback();
                                    $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                                    break;
                                }
                            }
                        }
                    }
                    $borrow_model->commit();
                    logrecords('delete', $borrow_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
                $borrow_model->rollback();          //回滚
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'借款单不存在！'));
        }
        
    }
    
    /*
     *  重新发启审核
     **/
    public function restartAction(){
        $id = I("post.borrow_id");
        $borrow_model = new \Home\Model\BorrowModel();
        $borrow = $borrow_model->get_one($id);
        if($borrow){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($borrow['borrow_no'] ,2);
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
                
                $borrow_model->startTrans();
                $ret = $app_model->update_data($data, "aprv_id={$approve['aprv_id']}");
                $ret1 = $borrow_model->update_data(array('cur_approver_id'=>0,'result'=>0), "borrow_id = $id");
                if($ret && $ret1){
                    //重启操作，重新减去预算单相关费用余额
                    $money_model = new \Home\Model\MoneyModel();
                    $expenses_model = new \Home\Model\ExpensesModel();
                    $moneys = $money_model->get_list("obj_id=$id and obj_type=1 and is_cancel=1");
                    foreach ($moneys as $money) {
                        $ret2 = $money_model->update_data(array('is_cancel'=>0,'is_del'=>1), 'money_id='.$money['money_id']);
                        $ret3 = $expenses_model->where("proj_id={$borrow['proj_id']} and cost_id={$money['cost_id']}")->setDec('usable_money', $money['money']);
                        if(!$ret2 || !$ret3){
                            $borrow_model->rollback();
                            $this->ajaxReturn(array('status'=>0, 'msg'=>"操作失败1！"));
                            break;
                        }
                    }
                    $borrow_model->commit();
                    logrecords("update", $app_model->get_tablename());          //日志
                    $this->ajaxReturn(array('status'=>1));
                }
                $borrow_model->rollback();
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    
    public function getprojnoAction(){
        $id = I('post.proj_id');
        $proj_model = new \Home\Model\ProjectModel();
        $proj = $proj_model->get_one($id);
        if($proj){
            $budget_model = new \Home\Model\BudgetModel();
            $budget = $budget_model->get_onebywhere("proj_id=$id and is_del=1");
            if($budget){
                $expenses_model = new \Home\Model\ExpensesModel();
                $costs = $expenses_model->get_expsandcost_list($budget['bud_id']);
            }
            
            $this->ajaxReturn(array('status'=>1, 'proj_no'=>$proj['proj_no'], 'costs'=>$costs, 'bud_id'=>$budget['bud_id']));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'项目不存在'));
    }
    
    /*
     *  根据父级id获取款项列表
     **/
    public function getcostAction(){
        $pid = I("post.pid");
        $cost_model = new \Home\Model\CosttypeModel();
    
        $costs = $cost_model->get_cost_bypid($pid);
        if($costs){
            $this->ajaxReturn(array('status'=>1, 'costs'=>$costs));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'父级不存在！'));
    }
    
    /*
     *  根据子级id获取款项列表
     **/
    public function getpcostAction(){
        $cid = I("post.cid");
        $cost_model = new \Home\Model\CosttypeModel();
    
        $costs = $cost_model->get_cost_bycid($cid);
        if($costs){
            $this->ajaxReturn(array('status'=>1, 'costs'=>$costs));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'子级不存在！'));
    }
    
    /*
     *  获取借款单号
     **/
    public function getBorrowNo(){
        return "JKD".date('YmdHis').sprintf("%04d",rand(0, 1000));
    }
    
    /*
     *  获取审批人
     **/
    public function getapproversAction(){
        $proj_id = I('post.proj_id');
        if(is_numeric($proj_id)){
            $proj_id = intval($proj_id);
        }
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        $approve_users = getApproverListByProj($proj_id, $user_id, $user_id, $lvl_id, time(), null, 2);
        if($approve_users){
            $this->ajaxReturn(array('status'=>1, 'approve_users'=>$approve_users));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'审批人没有'));
    }
    /***
     * ++++++++++++++++++++litter_7+++++++++++
     */
    public function exportAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\ApproveModel();
        $proj_model = new \Home\Model\ProjectModel();
        if(get_access_allvoucher($user_id)){
            $where = "is_del = 1";
            if($lvl_id!=2){
                //获取所属公司下属的项目相关预算单
                $where .= " and proj_id in (".$proj_model->get_wherebycompanyid(session('company_id')).")";
            }
        }else{
            $where = "is_del = 1 and (user_id = $user_id";

            //获取归该用户审批的借款单列表
            $awhere = $app_model->get_whereaprrove($user_id, 2);
            $where .= " or borrow_no in ($awhere)";

            //获取该用户参与项目的相关借款单
            $proj_where = $proj_model->get_wheresqlbyuserid($lvl_id,$user_id);
            $where .= " or proj_id in ($proj_where)";
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
        $borrow_model = new \Home\Model\BorrowModel();
        $list = $borrow_model->where($where)->order("borrow_id desc")->select();
        $headtitle = array(
            'borrow_no'=>'借款单号',
            'proj_id'=>'项目名称',
            'user_id'=>'申请人',
            'borrow_way'=>'借款方式',
            'tot_amt'=>'金额总计',
            'total_recouped_money'=>'已报销金额',
            'total_refund_money'=>'已还款金额',
            'result'=>'状态',
            'crt_time'=>'创建时间');
        $this->outPut($list,$headtitle);

    }
    protected function outPut($list,$headtitle){
        $lvl_id = session('lvl_id');
        $app_model = new \Home\Model\ApproveModel();
        $proj_model = new \Home\Model\ProjectModel();
        $user_model = new \Home\Model\UserModel();
        $header = implode("\t",array_values($headtitle));
        $header .= "\t\n";
        $content .= $header;
        $filename = '借款单'.date('YmdHis').'.xls';
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
        foreach ($list as &$row) {
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            $row['borrow_way'] = $row['borrow_way'] == 1 ? "现金" : "转账" ;
            $proj = $proj_model->get_one($row['proj_id']);               //项目
            $row['proj_id'] = $proj['proj_name'];
            $user = $user_model->get_one($row['user_id']);               //申请人员
            if($user){
                $row['user_id'] = $user['real_name'];
               /* if($user['depart_id']){                          //部门名称
                    $depart = $depart_model->get_one($user['depart_id']);
                    if($depart){
                        $row['depart_name'] = $depart['depart_name'];
                    }
                }*/
            }

           // if($lvl_id<5 && $row['result'] == 1){
                $rec_model = new \Home\Model\RecoupedModel();
                $refund_model = new \Home\Model\RefundModel();
                $row['total_recouped_money'] = "0.00";
                $row['total_refund_money'] = "0.00";
                $rec = $rec_model->where("borrow_id={$row['borrow_id']} and is_del=1 and result=1")->field("sum(tot_amt) as tot_amt")->find();
                $refund = $refund_model->where("borrow_id={$row['borrow_id']} and is_del=1 and result=1")->field("sum(tot_amt) as tot_amt")->find();
                if($rec["tot_amt"]){
                    $row['total_recouped_money'] = $rec["tot_amt"];
                }else{
                    $row['total_recouped_money'] = 0;
                }
                if($refund["tot_amt"]){
                    $row['total_refund_money'] = $refund["tot_amt"];
                }else{
                    $row['total_refund_money'] = 0;
                }
           // }

            //获取审批状态
            $approve = $app_model->getApproveSchedule($row['borrow_no'], 2);
            for ($i = 1; $i < 6; $i++) {
                if($approve['aprv_user_id'.$i] == $row['cur_approver_id']){
                    $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                    $row['result'] = '等待审批';
                    if($approve['aprv_result'.$i] == 1){
                        $row['result'] = '同意';
                    }elseif($approve['aprv_result'.$i] == -1){
                        $row['result'] = '不同意';
                    }

                    if(($i == 5  || $app_user['lvl_id']==4) && $approve['aprv_result'.$i] == 1){
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
            $line .= "\t\n";
            $content .= $line;
            $content=@iconv("UTF-8","GBK//IGNORE",$content) ;
            echo $content;
        }
    }
    
}