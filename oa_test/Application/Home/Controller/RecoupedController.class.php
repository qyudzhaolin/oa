<?php
/**
 * OA系统--报销单
 */
namespace Home\Controller;
use Think\Page;
use Think\Model;
class RecoupedController extends BaseController{
    
    public $check_access = true;
    public $head_title = '报销单';
    
    public function indexAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\ApproveModel();
        $proj_model = new \Home\Model\ProjectModel();
        $user_model = new \Home\Model\UserModel();
        $recouped_model = new \Home\Model\RecoupedModel();
        
        if(get_access_allvoucher($user_id) || check_permission_left('financial_approve', 'recouped_export')){                    
            $where = "is_del = 1";
            if($lvl_id!=2){
                //获取所属公司下属的项目相关预算单
                $where .= " and proj_id in (".$proj_model->get_wherebycompanyid(session('company_id')).")";
            }
        }else{
            $where = "is_del = 1 and (user_id = $user_id";
            
            //获取归该用户审批的报销单列表
            $awhere = $app_model->get_whereaprrove($user_id, 3);
            $where .= " or rec_no in ($awhere)";
            
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
            if($plist){
                foreach ($plist as $prow) {
                    $pwhere .= $prow['proj_id'].",";
                }
                $pwhere = rtrim($pwhere, ",");
                if($pwhere){
                    $where .= " and proj_id in ($pwhere)";
                }else{
                    $where .= " and proj_id<0";
                }
            }else{
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
        }
        
        $borrow_model = new \Home\Model\BorrowModel();
        $count = $recouped_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $recouped_model->where($where)->order("rec_id desc")->limit($page->firstRow.','.$page->listRows)->select();
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
            
            $row['borrow_no'] = "--";
            if($row['borrow_id']){
                $borrow = $borrow_model->get_one($row['borrow_id']);
                $row['borrow_no'] = $borrow['borrow_no'];
            }
            
            $row['result_info'] = '审核中';
            if($row['result'] == 1){
                $row['result_info'] = '通过';
            }elseif($row['result'] == -1){
                $row['result_info'] = '未通过';
            }
            
            
            //获取审批状态
            $approve = $app_model->getApproveSchedule($row['rec_no'], 3);
            for ($i = 1; $i < 7; $i++) {
                if($approve['aprv_user_id'.$i] == $row['cur_approver_id']){
                    $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                    $row['result_info'] = '等待审批';
                    if($approve['aprv_result'.$i] == 1){
                        $row['result_info'] = '同意';
                    }elseif($approve['aprv_result'.$i] == -1){
                        $row['result_info'] = '不同意';
                    }
                    if($app_user['lvl_id']==4 && $approve['aprv_result'.$i] == 1){
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
        $this->assign("is_use_mj", check_permission_byca('Mjsupplier', 'use', $user_id, $lvl_id));
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('user_id', $user_id);
        $this->assign('lvl_id', $lvl_id);
        $this->display('Recouped:index');
    }
    
    /*  
     *  新增、修改用户页
     **/
    public function addAction(){
        $recouped_model = new \Home\Model\RecoupedModel();
        $borrow_model = new \Home\Model\BorrowModel();
        $bank_model = new \Home\Model\BankModel();
        $money_model = new \Home\Model\MoneyModel();
        $suppler_model = new \Home\Model\SupplierModel();
        $mjsuppler_model = new \Home\Model\MjsupplierModel();
        $expenses_model = new \Home\Model\ExpensesModel();
        $ct_model = new \Home\Model\ContractModel();
        $proj_model = new \Home\Model\ProjectModel();
        
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        $depart_id = session('depart_id');
        
        if(IS_POST){
            $data['borrow_id'] = I('post.borrow_id');
            $data['proj_id'] = I('post.proj_id');
            $data['get_type'] = I('post.get_type');
            $data['borrow_way'] = I('post.borrow_way');
            $data['bnk_branch'] = I('post.bnk_branch');
            $data['bnk_acct'] = I('post.bnk_acct');
            $id = I("post.rec_id");
            $fund_str = I("post.fund_str");
            $data['tot_amt'] = I('post.tot_amt');
            $data['tot_amt_d'] = I('post.tot_amt_d');
            //$data['borrow_tot_amt'] = I('post.borrow_tot_amt');
            $cur_approver_id = $data['cur_approver_id'] = I('post.cur_approver_id');
            $bud_id = $data['bud_id'] = I('post.bud_id');
            $data['ct_id'] = I('post.ct_id');
            
            //项目是否截止
            $budget_model = new \Home\Model\BudgetModel();
            $budget = $budget_model->get_one($bud_id);
            if($budget){
                $is_over = $budget_model->check_is_over($budget);
                if($is_over['status']==true){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>$is_over['msg']));
                }
            }
            
            //基础数据验证
            if(!$data['proj_id']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择项目！'));
            //if($data['get_type'] != '1' && $data['get_type'] != '2') $this->ajaxReturn(array('status'=>0, 'msg'=>'收款人类型不正确！'));
            if($data['get_type'] == 1 || $data['get_type'] == 3){
                $sup = $this->check_sup($data['get_type']);
                if($sup['status'] == 0){
                    $this->ajaxReturn($sup);
                }
                $data['get_id'] = $sup['get_id'];
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
            $borrow_id = 0;           //未编辑前，引用的借款单id
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $recouped = $recouped_model->get_one($id);
                if(!$recouped){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'报销单不存在！'));
                }
                $borrow_id = $recouped['borrow_id'];             //给引用的借款单id赋值
                $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
                if(session('lvl_id')!=1){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                    }
                }
            }
            
            //新增、更新数据
            $recouped_model->startTrans();     //开启事务
            if($id){
                $ret = $recouped_model->update_data($data, "rec_id = $id");            //更新报销单表
                if($ret && $recouped['cur_approver_id'] != $data['cur_approver_id']){
                    $ret3 = $app_model->updateApproved($approve, $data['cur_approver_id'], $lvl_id, $user_id);
                }else{
                    $ret3 = true;
                }
                $action = 'update';
            }else{
                $data['user_id'] = $this->user_id;
                $data['crt_user_id'] = $this->user_id;
                $data['rec_no'] = $this->getBorrowNo();
                $ret = $recouped_model->insert_data($data);                               //插入报销单表
                $ret3 = true;
                $action = 'insert';
            }  
            $msg = "保存失败！";                                      //未完成
            if($ret && $ret3){  
                if($action == 'update'){
                    $moneys = $money_model->get_list("obj_id = $id and obj_type=2 and is_del=1");
                    if($moneys){
                        $ret2 = $money_model->update_data(array('is_del'=>-1), "obj_id = $id and obj_type=2");
                        if($ret2){
                            //更新新数据前，先把老的报销金额返还给原处
                            foreach ($moneys as $money) {
                                if($borrow_id > 0){               //是否是使用借款单进行报销
                                    if($money['to_money_id']>0){
                                        $ret_exp = $money_model->where("money_id={$money['to_money_id']} and is_del=1")->setInc('usable_money', $money['money']);
                                    }else{
                                        $ret_exp = $money_model->where("obj_id=$borrow_id and obj_type=1 and cost_id={$money['cost_id']} and is_del=1")->setInc('usable_money', $money['money']);
                                    }
                                }else{
                                    $ret_exp = $expenses_model->where("proj_id={$recouped['proj_id']} and cost_id={$money['cost_id']}")->setInc('usable_money', $money['money']);
                                }
                                if(!$ret_exp){
                                    $recouped_model->rollback();
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
                    $ret2 = $app_model->insert_data(array('aprv_no'=>$data['rec_no'],'aprv_type'=>3,'aprv_user_id1'=>$data['cur_approver_id'],'proj_id'=>$data['proj_id']));
                }
                if($ret2){
                    $issubmit = true;
                    $fund_array = explode(";", $fund_str);
                    
                    if($data['borrow_id'] > 0){
                        $exps = $money_model->get_array_list($data['borrow_id']);
                    }else{
                        //获取预算单中款项列表
                        $exps = $expenses_model->get_array_list($bud_id);
                    }
                              
                    foreach ($fund_array as $f_row) {
                        if($f_row && $f_arr = explode("^", $f_row)){
                            if($f_arr[1] == "0.00"){
                                continue;
                            }
                            if(!array_key_exists($f_arr[0], $exps)){
                                //$msg = '款项：'.$f_arr[3].'不存在！';
                                $msg = '说明文字不得包含 ; ^ &等符号';
                                $issubmit = false;
                                break;
                            }
                            
                            if($f_arr[0] == 90){
                                if($depart_id!=16){
                                    $msg = '款项：'.$f_arr[3].'必须媒介部门使用！';
                                    $issubmit = false;
                                    break;
                                }
                                if($data['get_type']!=3){
                                    $msg = '款项：'.$f_arr[3].'必须使用媒介供应商！';
                                    $issubmit = false;
                                    break;
                                }
                            }
                            
                            $usable_money = 0;
                            if($f_arr[6]){
                                $borrow_money = $money_model->get_one($f_arr[6]);
                                $usable_money = $exps[$f_arr[0]]['usable_money'] = $borrow_money['usable_money'] - $f_arr[1];         //可用余额
                            }else{
                                $usable_money = $exps[$f_arr[0]]['usable_money'] = $exps[$f_arr[0]]['usable_money'] - $f_arr[1];         //可用余额
                            }
                            if($usable_money < 0){
                                $msg = '款项：'.$f_arr[3].'可用余额不足！';
                                $issubmit = false;
                                break;
                            }
                            
                            $add_data = array('obj_id'=>$ret,'obj_type'=>2,'cost_id'=>$f_arr[0], 'money'=>$f_arr[1], 'usable_money'=>$f_arr[1], 'comm'=>$f_arr[2], 'file_id'=>$f_arr[4], 'is_del'=>1, 'remit_date'=>$f_arr[5]);
                            if($f_arr[6]){
                                $add_data['to_money_id'] = $f_arr[6];
                            }
                            
                            $ret1 = $money_model->add($add_data);
                            if($data['borrow_id'] > 0){
                                $ret4 = $money_model->where("money_id=".$f_arr[6])->setDec('usable_money',$f_arr[1]);
                            }else{
                                $ret4 = $expenses_model->where("exp_id=".$exps[$f_arr[0]]['exp_id'])->setDec('usable_money',$f_arr[1]);
                            }
                            
                            if(!$ret1 || !$ret4){
                                $issubmit = false;
                                break;
                            }
                        }
                    }
                    
                    if($issubmit){
                        logrecords($action, $recouped_model->get_tablename());          //日志
                        if($recouped_model->commit()){
                            if($action == 'insert' || ($action == 'update' && $recouped['cur_approver_id'] != $data['cur_approver_id'])){
                                send_email_approve($data['cur_approver_id'], '报销单审批通知', "您有一个报销单审批请求！",U("Recouped/info",array('id'=>$ret)));   //发邮件
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
                    redirect(U('Recouped:index'), 2, '您没有权限对该报销单进行操作!');
                }
                
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
                //审批已经开始必须进入详情页
                if($approve['aprv_result1']>0){
                    redirect(U("Recouped/info")."?id=".$id);
                }
                
                //获取款项列表
                if($recouped['result'] == -1){
                    $money_list = $money_model->get_list("obj_id=$id and obj_type=2 and is_cancel=1");
                }else{
                    $money_list = $money_model->get_list("obj_id=$id and obj_type=2 and is_del=1");
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
                
                if($recouped['borrow_id']>0){
                    $costs = $money_model->get_moneyandcost_list($recouped['borrow_id']);
                }else{
                    $costs = $expenses_model->get_expsandcost_list($recouped['bud_id']);
                }
                $this->assign('costs', $costs);
                $this->assign('money_list', $money_list);
                
                //获取合同列表
                $contract = $ct_model->get_onebywhere("ct_id={$recouped['ct_id']}");
                if($contract){
                    $contract['ct_limit_date'] = $contract['ct_limit_date'] ? date('Y-m-d',$contract['ct_limit_date']) : "";
                }
                $ct_list = $ct_model->get_list("proj_id={$recouped['proj_id']} and is_del=1");
                $this->assign('contract', $contract);
                $this->assign('ct_list', $ct_list);
                
                //获取项目编号
                $proj = $proj_model->get_one($recouped['proj_id']);
                $recouped['proj_no'] = $proj['proj_no'];
                
                //申请人
                $user_model = new \Home\Model\UserModel();
                $apply_user = $user_model->get_one($recouped['user_id']);
                $app_real_name = $apply_user['real_name'];
                $depart_id = $apply_user['depart_id'];
                
                //借款单列表--所属项目下的借款单
                if($recouped['proj_id']){
                    $borrows = $borrow_model->get_proj_borrows($recouped['proj_id'], $user_id);
                    $this->assign('borrows', $borrows);
                }
                
                for ($i = 1; $i < 7; $i++) {
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
                $is_show_app = false;
                if($approve['result'] != -1){
                    $is_show_app = check_is_show_app($recouped['user_id'], $user_id, $lvl_id, $approve, $recouped['crt_time']);
                }
                
                //审批人列表
                if($is_show_app == true){
                    $approve_users = getApproverListByProj($recouped['proj_id'],$recouped['user_id'], $user_id, $lvl_id, $recouped['crt_time'], $approve);
                }
                
                //是否重新发启按钮
                $is_restart = false;
                if($approve['result'] == -1 && ($lvl_id==1 || $recouped['user_id']==$user_id)){
                    $is_restart = true;
                }
                
                //供应商列表
                if($recouped['get_type'] == 1 || $recouped['get_type'] == 3){
                    $get_type = $recouped['get_type'];
                    if($get_type==1 && $depart_id==16){
                        $get_type = 3;
                    }
                    $supplers = $this->get_sup_list($get_type, "sup_id={$recouped['get_id']} and is_del=1", 0, 1);
                    $this->assign('supplers', $supplers);
                }
                
                //审批进度条内容
                $approve_arr = getApprovers($approve, $recouped['crt_user_id']);

                $this->assign('approve_arr', $approve_arr);
                $this->assign('recouped', $recouped);
            }
        }else{
            $is_show_app = true;
            $is_restart = false;
        }
        
        //获取所在部门
        $depart_model = new \Home\Model\DepartModel();
        $depart = $depart_model->get_one($depart_id);
        
        //是否有使用媒介供应商的权限
        $is_use_mj = check_permission_byca('Mjsupplier', 'use', $user_id, $lvl_id);
        
        //获取该用户涉及到的项目列表
        $budget_model = new \Home\Model\BudgetModel();
        $projs = $budget_model->get_list_over($lvl_id,$user_id);
        $this->assign('is_show_app', $is_show_app);
        $this->assign('is_restart', $is_restart);
        $this->assign('app_real_name', $app_real_name);
        $this->assign('depart', $depart);
        $this->assign('projs', $projs);
        $this->assign('supplers', $supplers);
        $this->assign('approve_users', $approve_users);
        $this->assign('user_id', $user_id);
        $this->assign('is_use_mj',$is_use_mj);
        $this->display('Recouped:add');
    }
    
    public function mj_addAction(){
        $recouped_model = new \Home\Model\RecoupedModel();
        $borrow_model = new \Home\Model\BorrowModel();
        $money_model = new \Home\Model\MoneyModel();
        $expenses_model = new \Home\Model\ExpensesModel();
        $proj_model = new \Home\Model\ProjectModel();
    
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        $depart_id = session('depart_id');
        $cost_id = 90;       //固定为媒介采买
    
        if(IS_POST){
            $data['borrow_id'] = I('post.borrow_id');
            $data['proj_id'] = I('post.proj_id');
            $data['get_type'] = 3;
            $data['get_id'] = I('post.sup_id');
            $data['tot_amt'] = I('post.payment');
            $data['tot_amt_d'] = $this->num2rmb(I('post.payment'));
            $data['borrow_way'] = I('post.borrow_way');
            $data['bnk_branch'] = I('post.bnk_branch');
            $data['bnk_acct'] = I('post.bnk_acct');
            $data['mj_ex_id'] = I('post.mj_ex_id');
            $id = I("post.rec_id");
            //$data['borrow_tot_amt'] = I('post.borrow_tot_amt');
            $cur_approver_id = $data['cur_approver_id'] = I('post.cur_approver_id');
            $bud_id = $data['bud_id'] = I('post.bud_id');
    
            //项目是否截止
            $budget_model = new \Home\Model\BudgetModel();
            $budget = $budget_model->get_one($bud_id);
            if($budget){
                $is_over = $budget_model->check_is_over($budget);
                if($is_over['status']==true){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>$is_over['msg']));
                }
            }
    
            //基础数据验证
            if(!$data['proj_id']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择项目！'));
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
            $borrow_id = 0;           //未编辑前，引用的借款单id
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $recouped = $recouped_model->get_one($id);
                if(!$recouped){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'报销单不存在！'));
                }
                $borrow_id = $recouped['borrow_id'];             //给引用的借款单id赋值
                $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
                if(session('lvl_id')!=1){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                    }
                }
            }
    
            //新增、更新数据
            $recouped_model->startTrans();     //开启事务
            if($id){
                $ret = $recouped_model->update_data($data, "rec_id = $id");            //更新报销单表
                M("recouped_mj_expenses")->where("id=".$recouped['mj_ex_id'])->save(array("is_use"=>0));
                if($ret && $recouped['cur_approver_id'] != $data['cur_approver_id']){
                    $ret3 = $app_model->updateApproved($approve, $data['cur_approver_id'], $lvl_id, $user_id);
                }else{
                    $ret3 = true;
                }
                $action = 'update';
            }else{
                $data['user_id'] = $this->user_id;
                $data['crt_user_id'] = $this->user_id;
                $data['rec_no'] = $this->getBorrowNo();
                $ret = $recouped_model->insert_data($data);                               //插入报销单表
                $ret3 = true;
                $action = 'insert';
            }
            $msg = "保存失败！";                                      //未完成
            if($ret && $ret3){
                if($action == 'update'){
                    $moneys = $money_model->get_list("obj_id = $id and obj_type=2 and is_del=1");
                    if($moneys){
                        $ret2 = $money_model->update_data(array('is_del'=>-1), "obj_id = $id and obj_type=2");
                        if($ret2){
                            //更新新数据前，先把老的报销金额返还给原处
                            foreach ($moneys as $money) {
                                if($borrow_id > 0){               //是否是使用借款单进行报销
                                    if($money['to_money_id']>0){
                                        $ret_exp = $money_model->where("money_id={$money['to_money_id']} and is_del=1")->setInc('usable_money', $money['money']);
                                    }else{
                                        $ret_exp = $money_model->where("obj_id=$borrow_id and obj_type=1 and cost_id=$cost_id and is_del=1")->setInc('usable_money', $money['money']);
                                    }
                                }else{
                                    $ret_exp = $expenses_model->where("proj_id={$recouped['proj_id']} and cost_id=$cost_id")->setInc('usable_money', $money['money']);
                                }
                                if(!$ret_exp){
                                    $recouped_model->rollback();
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
                    $ret2 = $app_model->insert_data(array('aprv_no'=>$data['rec_no'],'aprv_type'=>3,'aprv_user_id1'=>$data['cur_approver_id'],'proj_id'=>$data['proj_id']));
                }
                if($ret2){
    
                    if($data['borrow_id'] > 0){
                        $money = $money_model->where("obj_id={$data['borrow_id']} and obj_type=1 and cost_id=$cost_id and is_del=1")->find();
                    }else{
                        //获取预算单中款项列表
                        $money = $expenses_model->where("bud_id=$bud_id and cost_id=$cost_id and is_del=1")->find();
                    }
                    
                    $usable_money = $money['usable_money'] - $data['tot_amt'];         //可用余额
                    if($usable_money<0){
                        $msg = '款项：达人合作可用余额不足！';
                    }else{
                        $add_data = array('obj_id'=>$ret,'obj_type'=>2,'cost_id'=>$cost_id, 'money'=>$data['tot_amt'], 'usable_money'=>$data['tot_amt'], 'is_del'=>1);
                        $add_data['file_id'] = I('post.file_id');
                        $add_data['comm'] = I('post.comm');
                        if($data['borrow_id'] > 0){
                            $add_data['to_money_id'] = $money["money_id"];
                        }
                        
                        $ret1 = $money_model->add($add_data);
                        if($data['borrow_id'] > 0){
                            $ret4 = $money_model->where("money_id=".$money["money_id"])->setDec('usable_money',$data['tot_amt']);
                        }else{
                            $ret4 = $expenses_model->where("exp_id=".$money['exp_id'])->setDec('usable_money',$data['tot_amt']);
                        }
                        
                        $ret5 = M("recouped_mj_expenses")->where("id=".$data['mj_ex_id'])->save(array("is_use"=>1));
                        
                        if($ret1 && $ret4){
                            logrecords($action, $recouped_model->get_tablename());          //日志
                            if($recouped_model->commit()){
                                if($action == 'insert' || ($action == 'update' && $recouped['cur_approver_id'] != $data['cur_approver_id'])){
                                    send_email_approve($data['cur_approver_id'], '报销单审批通知', "您有一个报销单审批请求！",U("Recouped/info",array('id'=>$ret)));   //发邮件
                                }
                                $this->ajaxReturn(array('status'=>1));
                            }
                        }
                    }
                }
            }
            $recouped_model->rollback();
            $this->ajaxReturn(array('status'=>0, 'msg'=>$msg));
        }
    
        //是否有使用媒介供应商的权限
        if(!check_permission_byca('Mjsupplier', 'use', $user_id, $lvl_id)){
            redirect(U('Recouped:index'), 2, '您没有权限对媒介报销单进行操作!');
        }
        
        $id = I('get.id');
        $app_real_name = session('real_name');
        if($id){
            $id = intval($id);
            $recouped = $recouped_model->get_one($id);
            if($recouped){
                //判断是否有权限进行编辑操作
                if($recouped['user_id'] != $this->user_id && session('lvl_id')!=1){
                    redirect(U('Recouped:index'), 2, '您没有权限对该报销单进行操作!');
                }
    
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
                //审批已经开始必须进入详情页
                if($approve['aprv_result1']>0){
                    redirect(U("Recouped/info")."?id=".$id);
                }
    
                //获取项目编号
                $proj = $proj_model->get_one($recouped['proj_id']);
                $recouped['proj_no'] = $proj['proj_no'];
    
                //申请人
                $user_model = new \Home\Model\UserModel();
                $apply_user = $user_model->get_one($recouped['user_id']);
                $app_real_name = $apply_user['real_name'];
                $depart_id = $apply_user['depart_id'];
    
                //借款单列表--所属项目下的借款单
                if($recouped['proj_id']){
                    $borrows = $borrow_model->get_proj_borrows($recouped['proj_id'], $user_id);
                    $this->assign('borrows', $borrows);
                }
    
                for ($i = 1; $i < 7; $i++) {
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
                $is_show_app = false;
                if($approve['result'] != -1){
                    $is_show_app = check_is_show_app($recouped['user_id'], $user_id, $lvl_id, $approve, $recouped['crt_time']);
                }
    
                //审批人列表
                if($is_show_app == true){
                    $approve_users = getApproverListByProj($recouped['proj_id'],$recouped['user_id'], $user_id, $lvl_id, $recouped['crt_time'], $approve);
                }
    
                //是否重新发启按钮
                $is_restart = false;
                if($approve['result'] == -1 && ($lvl_id==1 || $recouped['user_id']==$user_id)){
                    $is_restart = true;
                }
    
                $mj_expenses = M("recouped_mj_expenses")->find($recouped['mj_ex_id']); 
                
                //获取款项列表
                if($recouped['result'] == -1){
                    $money = $money_model->where("obj_id=$id and cost_id=$cost_id and obj_type=2 and is_cancel=1")->find();
                }else{
                    $money = $money_model->where("obj_id=$id and cost_id=$cost_id and obj_type=2 and is_del=1")->find();
                }
                
                $file_model = new \Home\Model\FileModel();
                if($money['file_id']){
                    $file = $file_model->get_one($money['file_id']);
                    if($file){
                        $money['file_name'] = $file['file_name'];
                    }
                }
                
                $expenses = M("recouped_mj_expenses")->where("id=".$recouped['mj_ex_id'])->find();
                $mjsupplier = M("mjsupplier")->where(array('sup_short_name'=>$expenses['mj_name'],'is_del'=>1))->select();
                
                $this->assign('mjsupplier', $mjsupplier);
                $this->assign('mj_expenses', $mj_expenses);
                $this->assign('money', $money);
    
                //审批进度条内容
                $approve_arr = getApprovers($approve, $recouped['crt_user_id']);
                
                
                $this->assign('approve_arr', $approve_arr);
                $this->assign('recouped', $recouped);
            }
        }else{
            $is_show_app = true;
            $is_restart = false;
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
        $this->assign('user_id', $user_id);
        $this->display('Recouped:mj_add');
    }
    
    
    
    
    /*
     *  信息页
     **/
    public function infoAction(){
        $id = I('get.id');
        if($id){
            $id = intval($id);
            $recouped_model = new \Home\Model\RecoupedModel();
            $borrow_model = new \Home\Model\BorrowModel();
            $bank_model = new \Home\Model\BankModel();
            $money_model = new \Home\Model\MoneyModel();
            $proj_model = new \Home\Model\ProjectModel();
            $user_model = new \Home\Model\UserModel();
            $recouped = $recouped_model->get_one($id);
            $suppler_model = new \Home\Model\SupplierModel();
            $file_model = new \Home\Model\FileModel();
            $ct_model = new \Home\Model\ContractModel();
            if($recouped){
                $user_id = $this->user_id;
                $proj_id = $recouped['proj_id'];
                $lvl_id = session('lvl_id');
                
                
                if($recouped['crt_time']>get_c_date()){
                    //判断是否有权限进入
                    $is_projlist = checkInProjList($user_id,$lvl_id,$recouped['proj_id']);
                    if($recouped['user_id']!=$user_id && !checkInApproverList($user_id,$recouped['rec_no'],3) && !$is_projlist){
                        redirect(U('Recouped:index'), 2, '您没有权限对该报销单进行操作!');
                    }
                    
                    //获取款项列表
                    if($recouped['result'] == -1){
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=2 and is_cancel=1");
                    }else{
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=2 and is_del=1");
                    }
                    
                    if($recouped['borrow_id']>0){
                        $costs = $money_model->get_moneyandcost_list($recouped['borrow_id']);
                    }else{
                        $expenses_model = new \Home\Model\ExpensesModel();
                        $costs = $expenses_model->get_expsandcost_list($recouped['bud_id']);
                    }
                    foreach ($money_list as &$row) {
                        foreach ($costs as $c_row) {
                            if($row['to_money_id']){
                                if($row['to_money_id'] == $c_row['money_id']){
                                    $row['usable_money'] = $c_row['usable_money'];
                                    break;
                                }
                            }else{
                                if($row['cost_id'] == $c_row['id']){
                                    $row['usable_money'] = $c_row['usable_money'];
                                    break;
                                }
                            }
                        }
                        if($row['file_id']){
                            $file = $file_model->get_one($row['file_id']);
                            if($file){
                                $row['file_name'] = $file['file_name'];
                                $row['file_url'] = C('IMG_DOMAIN').$row['file_name'];
                            }
                        }
                    }
                    
                    //获取合同列表
                    $contract = $ct_model->get_onebywhere("ct_id={$recouped['ct_id']}");
                    if($contract){
                        $contract['ct_limit_date'] = $contract['ct_limit_date'] ? date('Y-m-d',$contract['ct_limit_date']) : "";
                        if($contract['file_id']){
                            $file = $file_model->get_one($contract['file_id']);
                            if($file){
                                $contract['file_name'] = $file['file_name'];
                                $contract['file_url'] = C('IMG_DOMAIN').$contract['file_name'];
                            }
                        }
                    }
                    $this->assign('contract', $contract);
                    
                    //获取项目编号
                    $proj = $proj_model->get_one($proj_id);
                    $recouped['proj_no'] = $proj['proj_no'];
                    
                    //申请人
                    $apply_user = $user_model->get_one($recouped['user_id']);
                    
                    //申请人获取所在部门
                    $depart_model = new \Home\Model\DepartModel();
                    $depart = $depart_model->get_one($apply_user['depart_id']);
                    
                    //获取项目列表
                    $projs = $proj_model->get_list("proj_id={$recouped['proj_id']} and is_del=1","proj_id,proj_name");
                    
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
                    $approve_arr = getApprovers($approve, $recouped['crt_user_id']);
                    //是否显示选择审批人功能
                    $is_show_app = check_is_show_app($recouped['user_id'], $user_id, $lvl_id, $approve, $recouped['crt_time']);
                    
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getApproverListByProj($recouped['proj_id'],$apply_user['user_id'], $user_id, $lvl_id, $recouped['crt_time'], $approve);
                        $this->assign('approve_users', $approve_users);
                    }
                    
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $recouped['user_id']==$user_id)){
                        $is_restart = true;
                    }
                    
                    //借款单列表
                    if($recouped['borrow_id']){
                        $borrows = $borrow_model->get_list("borrow_id = ".$recouped['borrow_id']);
                        $this->assign('borrows', $borrows);
                    }
                    
                    //供应商列表
                    if($recouped['get_type'] == 1 || $recouped['get_type'] == 3){
                        $get_type = $recouped['get_type'];
                        if($get_type==1 && $apply_user['depart_id']==16){
                            $get_type = 3;
                        }
                        $supplers = $this->get_sup_list($get_type, "sup_id={$recouped['get_id']} and is_del=1", 0, 1);
                        $this->assign('supplers', $supplers);
                    }
                    
                    $is_mj_show = true;
                    //判断是否媒介供应商，若是只有媒介组和审批人可以看
                    if($recouped['get_type'] == 3 || ($recouped['get_type'] == 1 && $apply_user['depart_id'] == 16)){
                        //是否有使用媒介供应商的权限
                        $is_use_mj = check_permission_byca('Mjsupplier', 'use', $user_id, $lvl_id);
                        if(!$is_use_mj){
                            $is_mj_show = false;
                        }
                    }
                    
                    $this->assign('supplers', $supplers);
                    $this->assign('recouped', $recouped);
                    $this->assign('money_list', $money_list);
                    $this->assign('apply_user', $apply_user);
                    $this->assign('depart', $depart);
                    $this->assign('projs', $projs);
                    $this->assign('approve_arr', $approve_arr);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('is_mj_show', $is_mj_show);
                    $this->assign('user_id', $this->user_id);
                    $this->assign('lvl_id', $lvl_id);
                    $this->display('Recouped:info');
                }else{
                    //判断是否有权限进入
                    $is_projlist = checkInProjList($user_id,$lvl_id,$recouped['proj_id']);
                    if($recouped['user_id']!=$user_id && !checkInApproverList($user_id,$recouped['rec_no'],3) && !$is_projlist){
                        redirect(U('Recouped:index'), 2, '您没有权限对该报销单进行操作!');
                    }
                    
                    //获取款项列表
                    if($recouped['result'] == -1){
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=2 and is_cancel=1");
                    }else{
                        $money_list = $money_model->get_list("obj_id=$id and obj_type=2 and is_del=1");
                    }
                    
                    if($recouped['borrow_id']>0){
                        $costs = $money_model->get_moneyandcost_list($recouped['borrow_id']);
                    }else{
                        $expenses_model = new \Home\Model\ExpensesModel();
                        $costs = $expenses_model->get_expsandcost_list($recouped['bud_id']);
                    }
                    foreach ($money_list as &$row) {
                        foreach ($costs as $c_row) {
                            if($row['cost_id'] == $c_row['id']){
                                $row['usable_money'] = $c_row['usable_money'];
                                break;
                            }
                        }
                        if($row['file_id']){
                            $file = $file_model->get_one($row['file_id']);
                            if($file){
                                $row['file_name'] = $file['file_name'];
                                $row['file_url'] = C('IMG_DOMAIN').$row['file_name'];
                            }
                        }
                    }
                    
                    //获取合同列表
                    $contract = $ct_model->get_onebywhere("ct_id={$recouped['ct_id']}");
                    if($contract){
                        $contract['ct_limit_date'] = $contract['ct_limit_date'] ? date('Y-m-d',$contract['ct_limit_date']) : "";
                        if($contract['file_id']){
                            $file = $file_model->get_one($contract['file_id']);
                            if($file){
                                $contract['file_name'] = $file['file_name'];
                                $contract['file_url'] = C('IMG_DOMAIN').$contract['file_name'];
                            }
                        }
                    }
                    $this->assign('contract', $contract);
                    
                    //获取项目编号
                    $proj = $proj_model->get_one($proj_id);
                    $recouped['proj_no'] = $proj['proj_no'];
                    
                    //申请人
                    $apply_user = $user_model->get_one($recouped['user_id']);
                    
                    //申请人获取所在部门
                    $depart_model = new \Home\Model\DepartModel();
                    $depart = $depart_model->get_one($apply_user['depart_id']);
                    
                    //获取项目列表
                    $projs = $proj_model->get_list("proj_id={$recouped['proj_id']} and is_del=1","proj_id,proj_name");
                    
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
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
                    
                    //是否显示选择审批人功能
                    $is_show_app = check_is_show_app($recouped['user_id'], $user_id, $lvl_id, $approve, $recouped['crt_time']);
                    
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getApproverListByProj($recouped['proj_id'],$apply_user['user_id'], $user_id, $lvl_id, $recouped['crt_time'], $approve);
                        $this->assign('approve_users', $approve_users);
                    }
                    
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $recouped['user_id']==$user_id)){
                        $is_restart = true;
                    }
                    
                    //借款单列表
                    if($recouped['borrow_id']){
                        $borrows = $borrow_model->get_list("borrow_id = ".$recouped['borrow_id']);
                        $this->assign('borrows', $borrows);
                    }
                    
                    //供应商列表
                    if($recouped['get_type'] == 1 || $recouped['get_type'] == 3){
                        $get_type = $recouped['get_type'];
                        if($get_type==1 && $apply_user['depart_id']==16){
                            $get_type = 3;
                        }
                        $supplers = $this->get_sup_list($get_type, "sup_id={$recouped['get_id']} and is_del=1", 0, 1);
                        $this->assign('supplers', $supplers);
                    }
                    
                    $is_mj_show = true;
                    //判断是否媒介供应商，若是只有媒介组和审批人可以看
                    if($recouped['get_type'] == 3 || ($recouped['get_type'] == 1 && $apply_user['depart_id'] == 16)){
                        if(session('depart_id')!=16 && !check_is_mj_show($user_id)){
                            $is_mj_show = false;
                        }
                    }
                    
                    $this->assign('supplers', $supplers);
                    $this->assign('recouped', $recouped);
                    $this->assign('money_list', $money_list);
                    $this->assign('apply_user', $apply_user);
                    $this->assign('depart', $depart);
                    $this->assign('projs', $projs);
                    $this->assign('schedule', $approve);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('is_mj_show', $is_mj_show);
                    $this->assign('user_id', $this->user_id);
                    $this->assign('lvl_id', $lvl_id);
                    $this->display('Recouped:info_old');
                }
            }
        }
    }
    
    public function mj_infoAction(){
        $id = I('get.id');
        if($id){
            $cost_id = 90;
            $recouped_model = new \Home\Model\RecoupedModel();
            $borrow_model = new \Home\Model\BorrowModel();
            $bank_model = new \Home\Model\BankModel();
            $money_model = new \Home\Model\MoneyModel();
            $proj_model = new \Home\Model\ProjectModel();
            $user_model = new \Home\Model\UserModel();
            $recouped = $recouped_model->get_one($id);
            $suppler_model = new \Home\Model\SupplierModel();
            $file_model = new \Home\Model\FileModel();
            $ct_model = new \Home\Model\ContractModel();
            $id = intval($id);
            $recouped = $recouped_model->get_one($id);
            if($recouped){
                $user_id = $this->user_id;
                $proj_id = $recouped['proj_id'];
                $lvl_id = session('lvl_id');
                //判断是否有权限进入
                $is_projlist = checkInProjList($user_id,$lvl_id,$recouped['proj_id']);
                if($recouped['user_id']!=$user_id && !checkInApproverList($user_id,$recouped['rec_no'],3) && !$is_projlist){
                    redirect(U('Recouped:index'), 2, '您没有权限对该报销单进行操作!');
                }
        
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
        
                //获取项目编号
                $proj = $proj_model->get_one($recouped['proj_id']);
                $recouped['proj_no'] = $proj['proj_no'];
        
                //申请人
                $user_model = new \Home\Model\UserModel();
                $apply_user = $user_model->get_one($recouped['user_id']);
                $app_real_name = $apply_user['real_name'];
                $depart_id = $apply_user['depart_id'];
        
                //借款单列表--所属项目下的借款单
                if($recouped['borrow_id']){
                    $borrows = $borrow_model->get_list("borrow_id = ".$recouped['borrow_id']);
                    $this->assign('borrows', $borrows);
                }
        
                for ($i = 1; $i < 7; $i++) {
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
                $is_show_app = false;
                if($approve['result'] != -1){
                    $is_show_app = check_is_show_app($recouped['user_id'], $user_id, $lvl_id, $approve, $recouped['crt_time']);
                }
        
                //审批人列表
                if($is_show_app == true){
                    $approve_users = getApproverListByProj($recouped['proj_id'],$recouped['user_id'], $user_id, $lvl_id, $recouped['crt_time'], $approve);
                }
        
                //是否重新发启按钮
                $is_restart = false;
                if($approve['result'] == -1 && ($lvl_id==1 || $recouped['user_id']==$user_id)){
                    $is_restart = true;
                }
        
                $mj_expenses = M("recouped_mj_expenses")->find($recouped['mj_ex_id']);
        
                //获取款项列表
                if($recouped['result'] == -1){
                    $money = $money_model->where("obj_id=$id and cost_id=$cost_id and obj_type=2 and is_cancel=1")->find();
                }else{
                    $money = $money_model->where("obj_id=$id and cost_id=$cost_id and obj_type=2 and is_del=1")->find();
                }
        
                $file_model = new \Home\Model\FileModel();
                if($money['file_id']){
                    $file = $file_model->get_one($money['file_id']);
                    if($file){
                        $money['file_name'] = $file['file_name'];
                        $money['file_url'] = C('IMG_DOMAIN').$file['file_name'];
                    }
                }
        
                $expenses = M("recouped_mj_expenses")->where("id=".$recouped['mj_ex_id'])->find();
                $mjsupplier = M("mjsupplier")->where(array('sup_short_name'=>$expenses['mj_name'],'is_del'=>1))->select();
                
                $this->assign('mjsupplier', $mjsupplier);
                $this->assign('mj_expenses', $mj_expenses);
                $this->assign('money', $money);
        
                //审批进度条内容
                $approve_arr = getApprovers($approve, $recouped['crt_user_id']);
                $this->assign('approve_arr', $approve_arr);
                $this->assign('recouped', $recouped);
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
            $this->assign('user_id', $user_id);
            $this->assign('apply_user', $apply_user);
            $this->display('Recouped:mj_info');
        }
    }
    
    /*
     *  审批结果提交
     **/
    public function operateAction(){
        $id = I("post.rec_id");
        $num = I("post.num");
        $result = I("post.result");
        
        $recouped_model = new \Home\Model\RecoupedModel();
        $recouped = $recouped_model->get_one($id);
        
        if(check_is_over($recouped['proj_id']) && $result==1){
            $this->ajaxReturn(array('status'=>0, 'msg'=>'该项目已结束，只能进行不同意操作！'));
        }
        
        
        if($recouped['crt_time']>get_c_date()){
            $cur_approver_id = I("post.cur_approver_id");
            $lvl_id = session('lvl_id');
            if($result == 1 && $lvl_id != 4 && $lvl_id != 2 && $cur_approver_id==0){          //同意操作，并且不是总经理和出纳，需要判断是否选择下一步审批人
                $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            }
            if($recouped){
                $user_id = $this->user_id;
                $app_model = new \Home\Model\ApproveModel();
                if($app_model->getApproveSchedule($recouped['rec_no'] ,3)){
                    $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                    if($lvl_id == 4 || $num == 6 || $result == -1){
                        $data['result'] = $result;
                    }
                    
                    if(I("post.option")) $data['aprv_opinion'.$num] = I("post.option");
                    
                    $recouped_model->startTrans();           //开启事务
                    $ret = $app_model->update_data($data, "aprv_no='{$recouped['rec_no']}' and aprv_type=3");
                    if($ret){
                        if($lvl_id != 4 && $result == 1){
                            if($lvl_id == 2){
                                choose_approval_user($id,3,get_cn_userid(),$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给出纳
                            }else{
                                choose_approval_user($id,3,$cur_approver_id,$lvl_id,$user_id);                    //非总经理的人员提交下个审批人的操作
                            }
                        }
                        if($lvl_id == 4 || $num == 6 || $result == -1){
                            $recouped_model->update_data(array('result'=>$result), "rec_id = {$recouped['rec_id']}");
                            $content = "您的一个报销单审批已通过，请注意查看。";
                            if($result == -1){
                                //不同意操作，把费用重新加回预算单余额
                                $money_model = new \Home\Model\MoneyModel();
                                $expenses_model = new \Home\Model\ExpensesModel();
                                $moneys = $money_model->get_list("obj_id = $id and obj_type=2 and is_del=1");
                                if($moneys){
                                    foreach ($moneys as $money) {
                                        $ret2 = $money_model->update_data(array('is_del'=>-1,'is_cancel'=>1), "money_id=".$money['money_id']);
                                        if($recouped['borrow_id'] > 0){               //是否是使用借款单进行报销
                                            $ret3 = $money_model->where("obj_id={$recouped['borrow_id']} and obj_type=1 and cost_id={$money['cost_id']} and is_del=1")->setInc('usable_money', $money['money']);
                                        }else{
                                            $ret3 = $expenses_model->where("proj_id={$recouped['proj_id']} and cost_id={$money['cost_id']}")->setInc('usable_money', $money['money']);
                                        }
                                        
                                        if(!$ret2 || !$ret3){
                                            $recouped_model->rollback();
                                            $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                                            break;
                                        }
                                    }
                                }else{
                                    $recouped_model->rollback();
                                    $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败2！"));
                                }
                                
                                if($recouped['mj_ex_id']>0){
                                    M("recouped_mj_expenses")->where("id=".$recouped['mj_ex_id'])->save(array("is_use"=>0));
                                }
                                
                                $content = "您的一个报销单审批未获通过，请注意查看。";
                            }
                            send_email_approve($recouped['user_id'], '报销单审批通知', $content."单号：{$recouped['rec_no']}。", U("Recouped/info",array('id'=>$id)));           //发邮件
                        }
                        $recouped_model->commit();          
                        logrecords("update", $app_model->get_tablename());          //日志
                        $this->ajaxReturn(array('status'=>1));
                    }
                    $recouped_model->rollback();           //回滚
                }
            }
        }else{
            if($recouped){
                $user_id = $this->user_id;
                $lvl_id = session('lvl_id');
                $app_model = new \Home\Model\ApproveModel();
                if($app_model->getApproveSchedule($recouped['rec_no'] ,3)){
                    $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                    if($lvl_id == 4 || $num == 5 || $result == -1){
                        $data['result'] = $result;
                        if($result == -1){
                            $data['aprv_opinion'.$num] = I("post.option");
                        }
                    }
            
                    $recouped_model->startTrans();           //开启事务
                    $ret = $app_model->update_data($data, "aprv_no='{$recouped['rec_no']}' and aprv_type=3");
                    if($ret){
                        if($lvl_id == 2 && $result == 1){
                            choose_approval_user($id,3,87,$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给peter
                        }
                        if($lvl_id == 4 || $num == 5 || $result == -1){
                            $recouped_model->update_data(array('result'=>$result), "rec_id = {$recouped['rec_id']}");
                            $content = "您的一个报销单审批已通过，请注意查看。";
                            if($result == -1){
                                //不同意操作，把费用重新加回预算单余额
                                $money_model = new \Home\Model\MoneyModel();
                                $expenses_model = new \Home\Model\ExpensesModel();
                                $moneys = $money_model->get_list("obj_id = $id and obj_type=2 and is_del=1");
                                if($moneys){
                                    foreach ($moneys as $money) {
                                        $ret2 = $money_model->update_data(array('is_del'=>-1,'is_cancel'=>1), "money_id=".$money['money_id']);
                                        if($recouped['borrow_id'] > 0){               //是否是使用借款单进行报销
                                            $ret3 = $money_model->where("obj_id={$recouped['borrow_id']} and obj_type=1 and cost_id={$money['cost_id']} and is_del=1")->setInc('usable_money', $money['money']);
                                        }else{
                                            $ret3 = $expenses_model->where("proj_id={$recouped['proj_id']} and cost_id={$money['cost_id']}")->setInc('usable_money', $money['money']);
                                        }
            
                                        if(!$ret2 || !$ret3){
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
                            send_email_approve($recouped['user_id'], '报销单审批通知', $content."单号：{$recouped['rec_no']}。", U("Recouped/info",array('id'=>$id)));           //发邮件
                        }
                        $recouped_model->commit();
                        logrecords("update", $app_model->get_tablename());          //日志
                        $this->ajaxReturn(array('status'=>1));
                    }
                    $recouped_model->rollback();           //回滚
                }
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    
    /*  */
    public function backAction(){
        if(IS_POST){
            $lvl_id = session('lvl_id');
            $user_id = $this->user_id;
            if($lvl_id==2){
                $app_model = new \Home\Model\ApproveModel();
                $obj_id = I('post.obj_id');
    
                //借款单
                $obj_model = new \Home\Model\RecoupedModel();
                $where = "rec_id = '$obj_id'";
                $obj = $obj_model->get_onebywhere($where);
                if($obj){
                    $aprv_no = $obj['rec_no'];
                }
                $approve = $app_model->getApproveSchedule($aprv_no, 3);
                $obj_model->startTrans();
                $ret = $obj_model->update_data(array('cur_approver_id'=>$user_id,'result'=>0), $where);
                
                for ($i = 1; $i < 6; $i++) {
                    if($approve["aprv_user_id".$i] == get_zjl_userid()){
                        break;
                    }
                }
                
                $ret1 = $app_model->update_data(array('aprv_user_id'.($i+1) => 0, 'aprv_result'.$i => 0, 'result'=>0), "aprv_no='$aprv_no' and aprv_type=3");
                if($ret && $ret1){
                    if($obj['result'] == -1){
                        //不同意操作，把费用重新加回预算单余额
                        $money_model = new \Home\Model\MoneyModel();
                        $expenses_model = new \Home\Model\ExpensesModel();
                        $moneys = $money_model->get_list("obj_id={$obj['rec_id']} and obj_type=2 and is_del=-1 and is_cancel=1");
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
        $id = I("post.rec_id");
        $cur_approver_id = I("post.cur_approver_id");
        $recouped_model = new \Home\Model\RecoupedModel();
        $recouped = $recouped_model->get_one($id);
        if($recouped){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
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
            $ret = $recouped_model->update_data(array('cur_approver_id'=>$cur_approver_id), "rec_id=".$id);
            if($ret){
                $ret1 = $app_model->updateApproved($approve, $cur_approver_id, $lvl_id, $user_id);
                if($ret && $ret1){
                    logrecords("update", $app_model->get_tablename());          //日志
                    $recouped_model->commit();
                    send_email_approve($cur_approver_id, '报销单审批通知', "您有一个报销单审批请求！",U("Recouped/info",array('id'=>$id)));   //发邮件
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
            $id = I('post.rec_id');
            $recouped_model = new \Home\Model\RecoupedModel();
            $recouped = $recouped_model->get_one($id);
            if($recouped){
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
                if(!check_permission_left('financial_approve', 'finance')){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可删除！'));
                    }
                }
                
                $recouped_model->startTrans();                 //开启事务
                $ret = $recouped_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "rec_id = $id");
                $ret1 = $app_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "aprv_no='{$recouped['rec_no']}' and aprv_type=3");
                if($ret && $ret1){
                    $money_model = new \Home\Model\MoneyModel();
                    $expenses_model = new \Home\Model\ExpensesModel();
                    $moneys = $money_model->get_list("obj_id = $id and obj_type=2 and is_del=1");
                    if($moneys){
                        $ret2 = $money_model->update_data(array('is_del'=>-1), "obj_id = $id and obj_type=2");
                        if($ret2){
                            foreach ($moneys as $money) {
                                if($recouped['borrow_id'] > 0){               //是否是使用借款单进行报销
                                    if($money['to_money_id']>0){
                                        $ret_exp = $money_model->where("money_id={$money['to_money_id']} and is_del=1")->setInc('usable_money', $money['money']);
                                    }else{
                                        $ret_exp = $money_model->where("obj_id={$recouped['borrow_id']} and obj_type=1 and cost_id={$money['cost_id']} and is_del=1")->setInc('usable_money', $money['money']);
                                    }
                                }else{
                                    $ret_exp = $expenses_model->where("proj_id={$recouped['proj_id']} and cost_id={$money['cost_id']}")->setInc('usable_money', $money['money']);
                                }
                                if(!$ret_exp){
                                    $recouped_model->rollback();
                                    $this->ajaxReturn(array('status'=>0, 'msg'=>"保存失败1！"));
                                    break;
                                }
                            }
                        }
                    }
                    if($recouped['mj_ex_id']>0){
                        M("recouped_mj_expenses")->where("id=".$recouped['mj_ex_id'])->save(array("is_use"=>0));
                    }
                    
                    $recouped_model->commit();
                    logrecords('delete', $recouped_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
                
                $recouped_model->rollback();          //回滚
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'报销单不存在！'));
        }
        
    }
    
    /*
     *  重新发启审核
     **/
    public function restartAction(){
        $id = I('post.rec_id');
        $recouped_model = new \Home\Model\RecoupedModel();
        $recouped = $recouped_model->get_one($id);
        
        if($recouped){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
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
                $ret1 = $recouped_model->update_data(array('cur_approver_id'=>0,'result'=>0), "rec_id = $id");
                if($ret && $ret1){
                    $money_model = new \Home\Model\MoneyModel();
                    $expenses_model = new \Home\Model\ExpensesModel();
                    $moneys = $money_model->get_list("obj_id=$id and obj_type=2 and is_cancel=1");
                    
                    if($recouped['borrow_id'] > 0){
                        $exps = $money_model->get_array_list($recouped['borrow_id']);
                    }else{
                        $exps = $expenses_model->get_array_list(0,$recouped['proj_id']);
                    }
                    
                    foreach ($moneys as $money) {
                        $usable_money = $exps[$money['cost_id']]['usable_money'] - $money['money'];         //可用余额
                        if($usable_money < 0){
                            $recouped_model->rollback();
                            $this->ajaxReturn(array('status'=>0, 'msg'=>"可用余额不足！"));
                            break;
                        }
                        //重启操作，重新减去预算单相关费用余额
                        $ret2 = $money_model->update_data(array('is_cancel'=>0,'is_del'=>1), 'money_id='.$money['money_id']);
                        if($recouped['borrow_id'] > 0){
                            $ret3 = $money_model->where("money_id=".$exps[$money['cost_id']]['money_id'])->setDec('usable_money',$money['money']);
                        }else{
                            $ret3 = $expenses_model->where("exp_id=".$exps[$money['cost_id']]['exp_id'])->setDec('usable_money',$money['money']);
                        }
                        if(!$ret2 || !$ret3){
                            $recouped_model->rollback();
                            $this->ajaxReturn(array('status'=>0, 'msg'=>"操作失败1！"));
                            break;
                        }
                    }
                    if($recouped['mj_ex_id']>0){
                        M("recouped_mj_expenses")->where("id=".$recouped['mj_ex_id'])->save(array("is_use"=>1));
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
     *  获取借款单和预算单的款项列表
     **/
    public function getborrowsAction(){
        $user_id = $this->user_id;
        $proj_id = I('post.proj_id');
        $borrow_model = new \Home\Model\BorrowModel();
        $borrows = $borrow_model->get_proj_borrows($proj_id,$user_id);
        
        $budget_model = new \Home\Model\BudgetModel();
        $budget = $budget_model->get_onebywhere("proj_id=$proj_id and is_del=1");
        if($budget){
            $expenses_model = new \Home\Model\ExpensesModel();
            $costs = $expenses_model->get_expsandcost_list($budget['bud_id']);
        }
        if($borrows || $costs){
            $this->ajaxReturn(array('status'=>1, 'borrows'=>$borrows, 'costs'=>$costs, 'bud_id'=>$budget['bud_id']));
        }
        
        $this->ajaxReturn(array('status'=>0, 'msg'=>'借款单不存在'));
    }
    
    /*
     *  获取项目信息
     **/
    
    public function getprojnoAction(){
        $id = I('post.proj_id');
        $proj_model = new \Home\Model\ProjectModel();
        $proj = $proj_model->get_one($id);
        if($proj){
            $this->ajaxReturn(array('status'=>1, 'proj_no'=>$proj['proj_no']));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'项目不存在'));
    }
    
    /*
     *  根据id获取供应商的信息
     **/
    public function getsupplierinfoAction(){
        $sup_id = I('post.sup_id');
        $get_type = I('post.get_type');
        if($get_type == 3){
            $sup_model = new \Home\Model\MjsupplierModel();
        }else{
            $sup_model = new \Home\Model\SupplierModel();
        }
        
        $sup = $sup_model->get_one($sup_id);
        if($sup){
            $this->ajaxReturn(array('status'=>1, 'sup'=>$sup));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'供应商不存在！'));
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
        return "BXD".date('YmdHis').sprintf("%04d",rand(0, 1000));
    }
    
    public function getsuplistAction(){
        $get_type = I('post.get_type');
        $sup_name = I('post.keyword','','stripslashes');
        //供应商列表
        $supplers = $this->get_sup_list($get_type, "is_del=1 and sup_full_name like '%$sup_name%'", 0, 20, array('sup_id asc'), "sup_id,sup_full_name,bnk_branch,bnk_acct,other,pay_method");
        if($supplers){
            $this->ajaxReturn(array('status'=>1, 'list'=>$supplers));
        }
        $this->ajaxReturn(array('status'=>0));
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

    /*
     *  获取供应商列表
     **/
    public function get_sup_list($get_type, $where,$firstRow,$listRow,$order=array('sup_id desc'),$field="*"){
        if($get_type == 3){
            $obj = new \Home\Model\MjsupplierModel();
        }else{
            $obj = new \Home\Model\SupplierModel();
        }
        
        $supplers = $obj->get_lists($where, $firstRow, $listRow, $order, $field);
        return $supplers;
    }

    public function getapproversAction(){
        $proj_id = I('post.proj_id');
        if(is_numeric($proj_id)){
            $proj_id = intval($proj_id);
        }
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        $approve_users = getApproverListByProj($proj_id, $user_id, $user_id, $lvl_id, time());
        if($approve_users){
            $this->ajaxReturn(array('status'=>1, 'approve_users'=>$approve_users));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'审批人没有'));
    }
    
    public function getcontractsAction(){
        $proj_id = I('post.proj_id');
        if(is_numeric($proj_id)){
            $proj_id = intval($proj_id);
        }
        $ct_model = new \Home\Model\ContractModel();
        $ct_list = $ct_model->get_list("proj_id=$proj_id and is_del=1");
        if($ct_list){
            $this->ajaxReturn(array('status'=>1, 'ct_list'=>$ct_list));
        }
        $this->ajaxReturn(array('status'=>0));
    }
    /**
     * ++++++++++++++++++++++++litter_7+++++++++++
     */
    public function exportAction(){
        /*$recouped_model = new \Home\Model\PfrecoupedModel();
        $search = I('');
        $map = $this->mapFormat($search);
        $list = $recouped_model->where($map)->order("pf_id desc")->select();*/
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\ApproveModel();
        $proj_model = new \Home\Model\ProjectModel();
        $user_model = new \Home\Model\UserModel();
        $recouped_model = new \Home\Model\RecoupedModel();
        if(get_access_allvoucher($user_id)){
            $where = "is_del = 1";
            if($lvl_id!=2){
                //获取所属公司下属的项目相关预算单
                $where .= " and proj_id in (".$proj_model->get_wherebycompanyid(session('company_id')).")";
            }
        }else{
            $where = "is_del = 1 and (user_id = $user_id";

            //获取归该用户审批的报销单列表
            $awhere = $app_model->get_whereaprrove($user_id, 3);
            $where .= " or rec_no in ($awhere)";

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
            if($plist){
                foreach ($plist as $prow) {
                    $pwhere .= $prow['proj_id'].",";
                }
                $pwhere = rtrim($pwhere, ",");
                if($pwhere){
                    $where .= " and proj_id in ($pwhere)";
                }else{
                    $where .= " and proj_id<0";
                }
            }else{
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
        }
        $list = $recouped_model->where($where)->order("rec_id desc")->select();
//        echo $recouped_model->getLastSql();
        $headtitle = array(
            'rec_no'=>'报销单号',
            'borrow_id'=>'借款单号',
            'proj_id'=>'项目名称',
            'proj_no'=>'项目编号',
            'user_id'=>'申请人',
            'borrow_way'=>'收款方式',
            'result'=>'状态',
            'tot_amt'=>'金额总计',
            'get_type'=>'收款人类型',
//            'depart_id'=>'部门',
            'get_user'=>'收款人',
            'bnk_branch'=>'银行',
            'bnk_acct'=>'银行账号',
            'crt_time'=>'创建时间',
            'remark'=>'备注说明',
            );
        $this->outPut($list,$headtitle);
    }
    protected function outPut($list=array(),$headtitle=array()){
        $app_model = new \Home\Model\ApproveModel();
        $proj_model = new \Home\Model\ProjectModel();
        $user_model = new \Home\Model\UserModel();
        $borrow_model = new \Home\Model\BorrowModel();
        $depart_model = new \Home\Model\DepartModel();
        $header = implode("\t",array_values($headtitle));
        $header .= "\t\n";
        $content .= $header;
        $filename = '项目报销单'.date('YmdHis').'.xls';
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
            //$row['borrow_way'] = $row['borrow_way'] == 1 ? "现金" : "转账" ;
            if($row['borrow_way']==3){
                $row['borrow_way'] = "其他";
            }elseif($row['borrow_way']==2){
//                $row['borrow_way'] = "转账".$row['user_id'];
                $row['borrow_way'] = "转账";
            }else{
                $row['borrow_way'] = "现金";
            }
            if($row['proj_id']){
                $proj = $proj_model->get_one($row['proj_id']);               //项目
                $row['proj_id'] = $proj['proj_name'];
                $row['proj_no'] = $proj['proj_no'];
            }

            $user = $user_model->get_one($row['user_id']);               //申请人员
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
//                $row['get_type'] = '供应商'.$user['depart_id'];
                $row['get_type'] = '供应商';
                $row['get_user'] = M('Supplier')->where("sup_id={$row['get_id']} and is_del=1")->getField('sup_full_name');
            }
            $row['borrow_id'] = "--";
            if($row['borrow_id']){
                $borrow = $borrow_model->get_one($row['borrow_id']);
                $row['borrow_id'] = $borrow['borrow_no'];
            }
            if($headtitle['remark']){
                //获取款项列表
                $money_model = new \Home\Model\MoneyModel();
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

            $row['result'] = '审核中';
            if($row['result'] == 1){
                $row['result'] = '通过';
            }elseif($row['result'] == -1){
                $row['result'] = '未通过';
            }


            //获取审批状态
            $approve = $app_model->getApproveSchedule($row['rec_no'], 3);
            for ($i = 1; $i < 7; $i++) {
                if($approve['aprv_user_id'.$i] == $row['cur_approver_id']){
                    $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                    $row['result'] = '等待审批';
                    if($approve['aprv_result'.$i] == 1){
                        $row['result'] = '同意';
                    }elseif($approve['aprv_result'.$i] == -1){
                        $row['result'] = '不同意';
                    }
                    if(($i == 5 || $app_user['lvl_id']==4) && $approve['aprv_result'.$i] == 1){
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
    
    public function get_mj_expenses_nameAction(){
        $proj_id = I("post.proj_id");
        $platform = I("post.platform");
        $mj_ex_id = I("post.mj_ex_id");
        
        $where = "proj_id=$proj_id and platform='$platform' and is_del=1 and is_use=0";
        if($mj_ex_id){
            $where = "proj_id=$proj_id and platform='$platform' and is_del=1 and (id=$mj_ex_id or is_use=0)";
        }
        $list = M("recouped_mj_expenses")->field("mj_name")->where($where)->group("mj_name")->select();
        $this->ajaxReturn(array('status'=>1, 'list'=>$list));
    }
    
    public function get_mj_expenses_payment_stageAction(){
        $proj_id = I("post.proj_id");
        $platform = I("post.platform");
        $mj_ex_name = I("post.mj_ex_name");
        $mj_ex_id = I("post.mj_ex_id");
        
        $where = "proj_id=$proj_id and platform='$platform' and mj_name='$mj_ex_name' and is_del=1 and is_use=0";
        if($mj_ex_id){
            $where = "proj_id=$proj_id and platform='$platform' and mj_name='$mj_ex_name' and is_del=1 and (id=$mj_ex_id or is_use=0)";
        }
        $list = M("recouped_mj_expenses")->field("id,payment_stage")->where($where)->select();
        $this->ajaxReturn(array('status'=>1, 'list'=>$list));
    }
    
    public function get_mj_expensesAction(){
        $id = I("post.mj_id");
    
        $expenses = M("recouped_mj_expenses")->where("id=".$id)->find();
        $mjsupplier = M("mjsupplier")->where(array('sup_short_name'=>$expenses['mj_name'],'is_del'=>1))->select();
        
        $this->ajaxReturn(array('status'=>1, 'expenses'=>$expenses, 'mjsuppliers'=>$mjsupplier));
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
}