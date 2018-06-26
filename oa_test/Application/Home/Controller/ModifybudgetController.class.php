<?php
/**
 * OA系统项目预算变更单
 */
namespace Home\Controller;
use Think\Page;
class ModifybudgetController extends BaseController{
    
    public $check_access = true;
    public $head_title = '项目预算变更单';
    
    public function indexAction(){
        $lvl_id = session('lvl_id');
        $depart_id = session('depart_id');
        $depart_par_id = session('depart_par_id'); 
        $user_id = $this->user_id;
        $bud_id = I("get.bud_id");
        
        $budget_model = new \Home\Model\BudgetModel();
        $budget = $budget_model->get_one($bud_id);
        if($budget){
            //判断是否有权限进行编辑操作
            if(!check_acess_budget($budget['crt_user_id'], $user_id, $lvl_id, $depart_id, $depart_par_id)){
                redirect(U('Budget:index'), 2, '您没有权限对该预算单进行变更操作!');
            }
            
            $modify_model = new \Home\Model\ModifybudgetModel();
            $app_model = new \Home\Model\ApproveModel();
            $where = "is_del = 1 and bud_id=$bud_id";
            
            $count = $modify_model->where($where)->count();
            $page = new Page($count, 20);
            $show = $page->show();
            $list = $modify_model->where($where)->order('bud_id asc')->limit($page->firstRow.','.$page->listRows)->select();
            
            $user_model = new \Home\Model\UserModel();
            $depart_model = new \Home\Model\DepartModel();
            foreach ($list as &$row) {
                $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
                
                //获取申请人信息
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
                $approve = $app_model->getApproveSchedule($row['mod_no'], 5);
                for ($i = 1; $i < 5; $i++) {
                    if($approve['aprv_user_id'.$i] == $row['cur_approver_id']){
                        $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                        $row['result_info'] = '等待审批';
                        if($approve['aprv_result'.$i] == 1){
                            $row['result_info'] = '同意';
                        }elseif($approve['aprv_result'.$i] == -1){
                            $row['result_info'] = '不同意';
                        }
            
                        if(($i == 4 || $app_user['lvl_id']==3) && $approve['aprv_result'.$i] == 1){
                            $row['result_info'] = '流程已完成';
                        }else{
                            $row['result_info'] = $row['result_info']."（{$app_user['real_name']}）";
                        }
            
                        break;
                    }
                }
            
                //操作动作
                $row['action'] = "info";
                if($lvl_id == 1 || ($row['crt_user_id'] == $user_id && $i==1 && $approve['aprv_result1']==0)){
                    $row['action'] = "add";
                }
            }
            $this->assign('list',$list);
            $this->assign('page',$show);
            $this->assign('user_id', $user_id);
            $this->assign('lvl_id', $lvl_id);
            $this->assign('budget', $budget);
            $this->display('Modifybudget:index');
        }
        
    }
    
    /*  
     *  新增、修改用户页
     **/
    public function addAction(){
        $proj_model = new \Home\Model\ProjectModel();
        $budget_model = new \Home\Model\BudgetModel();
        $expenses_model = new \Home\Model\ExpensesModel();
        $modify_model = new \Home\Model\ModifybudgetModel();
        $modexpenses_model = new \Home\Model\ModifyexpensesModel();
        
        $lvl_id = session('lvl_id');
        $depart_id = session('depart_id');
        $depart_par_id = session('depart_par_id'); 
        $user_id = $this->user_id;
        
        if(IS_POST){
            $data['cust_id'] = I('post.customer_id',0);
            $data['proj_id'] = I('post.proj_id',0);
            $data['modify_cntr_income'] = I('post.modify_cntr_income');
            $data['cur_approver_id'] = I('post.cur_approver_id');
            $bud_id = $data['bud_id'] = I("post.bud_id");
            $id = I("post.mod_id");
            $d_str = I("post.d_str");
            $z_str = I("post.z_str");
        
            //基础数据验证
            if(!$data['cur_approver_id'] && !$id) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            
            //判断是否可以更新
            $app_model = new \Home\Model\ApproveModel();
            $approve = null;
            $modify = null;
            $action = '';
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $modify = $modify_model->get_one($id);
                $approve = $app_model->getApproveSchedule($modify['mod_no'], 5);
                if(session('lvl_id')!=1){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                    }
                }
            }
            
            $budget = $budget_model->get_one($bud_id);
            if($budget){
                $is_over = $budget_model->check_is_over($budget);
                if($is_over['status']==true){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>$is_over['msg']));
                }
            }
            
            //判断该项目是否有未被审批完成的变更单
            $have_modify = $modify_model->get_onebywhere("is_del=1 and result=0 and bud_id=$bud_id");
            if($have_modify && (!$id || $id!=$have_modify['mod_id'])){
                $this->ajaxReturn(array('status'=>0, 'msg'=>'有未被审批完成的变更单！'));
            }
            
            //判断是否有变更数据
            if(!$id){
               if($data['modify_cntr_income'] == ""){
                   $is_modify = $this->check_is_modify($d_str, $z_str);
                   if($is_modify == false){
                       $this->ajaxReturn(array('status'=>0, 'msg'=>'没有变更的数据，所以无法建立变更单！'));
                   }
               } 
            }
            
            //是否超出款项余额的变化
            $is_out = $this->check_is_out($d_str, $z_str);
            if($is_out['is_out'] == true){
                $this->ajaxReturn(array('status'=>0, 'msg'=>$is_out['costname'].'变更的数据超过款项余额，请确认！'));
            }
            //exit;
            //新增、更新数据
            $modify_model->startTrans();     //开启事务
            if($id){
                $ret = $modify_model->update_data($data, "mod_id = $id");            //更新变更单表
                if($ret && $modify['cur_approver_id'] != $data['cur_approver_id']){
                    $ret3 = $app_model->updateApproved($approve, $data['cur_approver_id'], $lvl_id, $user_id);
                }else{
                    $ret3 = true;
                }
                $action = 'update';
            }else{
                $data['budget_cntr_income'] = $budget['budget_cntr_income'];
                $data['budget_proj_profit'] = $budget['budget_proj_profit']; 
                $data['crt_user_id'] = $this->user_id;
                $data['mod_no'] = $this->getBorrowNo();
                $ret = $modify_model->insert_data($data);                               //插入变更单表
                $ret3 = true;
                $action = 'insert';
            }
            $modify_proj_profit = ($data['modify_cntr_income'] ? $data['modify_cntr_income'] : $budget['budget_cntr_income']) * (1-0.0634);   //利润=收入减去税点
            
            if($ret && ret3){
                if($action == 'update'){
                    if($modexpenses_model->get_onebywhere("mod_id=$id and is_del=1")){
                        $ret2 = $modexpenses_model->update_data(array('is_del'=>-1), "mod_id = $id");
                    }else{
                        $ret2 = true;
                    }
                    $ret = $id;
                }else{
                    //提交审批表
                    $ret2 = $app_model->insert_data(array('aprv_no'=>$data['mod_no'],'aprv_type'=>5,'aprv_user_id1'=>$data['cur_approver_id'],'proj_id'=>$data['proj_id']));
                }
                if($ret2){
                    $issubmit = true;
                    $z_str = str_replace("&amp;", "&", $z_str);
                    $d_str = str_replace("&amp;", "&", $d_str);
                    $d_array = explode(";", $d_str);
                    foreach ($d_array as $d_row) {
                        if($d_row && $d_arr = explode("^", $d_row)){
                            $d_arr[2] = $d_arr[2]=='none' ? '' : $d_arr[2];
                            $ret1 = $modexpenses_model->insert_data(array('mod_id'=>$ret,'bud_id'=>$bud_id,'exp_id'=>$d_arr[3],'cost_id'=>$d_arr[0],'type'=>1,'budget_money'=>$d_arr[1], 'comm'=>$d_arr[5], 'modify_money'=>$d_arr[2], 'mark'=>$d_arr[4]));
                            if(!$ret1){
                                $issubmit = false;
                                $modify_model->rollback();        //回滚
                                break;
                            }
                            $modify_proj_profit = $modify_proj_profit - ($d_arr[2]?$d_arr[2]:$d_arr[1]);
                        }
                    }
                    
                    if($issubmit == true){
                        $z_array = explode(";", $z_str);
                        foreach ($z_array as $z_row) {
                            if($z_row && $z_arr = explode("^", $z_row)){
                                $z_arr[2] = $z_arr[2]=='none' ? '' : $z_arr[2];
                                $ret2 = $modexpenses_model->insert_data(array('mod_id'=>$ret,'bud_id'=>$bud_id,'exp_id'=>$z_arr[3],'cost_id'=>$z_arr[0],'type'=>2,'budget_money'=>$z_arr[1], 'comm'=>$z_arr[5], 'modify_money'=>$z_arr[2], 'mark'=>$z_arr[4]));
                                if(!$ret2){
                                    $issubmit = false;
                                    $modify_model->rollback();        //回滚
                                    break;
                                }
                                $modify_proj_profit = $modify_proj_profit - ($z_arr[2]?$z_arr[2]:$z_arr[1]);
                            }
                        }
                    }
                    
                    if($issubmit){
                        $ret4 = $modify_model->where(array('mod_id'=>$ret))->save(array('modify_proj_profit'=>round($modify_proj_profit,2),'mod_time'=>time()+1));
                        if($ret4){
                            logrecords($action, $modify_model->get_tablename());          //日志
                            if($modify_model->commit()){
                                if($action == 'insert' || ($action == 'update' && $budget['cur_approver_id'] != $data['cur_approver_id'])){
                                    send_email_approve($data['cur_approver_id'], '预算变更单审批通知', "您有一个预算变更单审批请求！",U("Modifybudget/info",array('id'=>$ret)));
                                }
                                $this->ajaxReturn(array('status'=>1));
                            }
                        }
                    }
                }
            }
            $modify_model->rollback();        //回滚
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
        
        $bud_id = I("get.bud_id");
        $budget_model = new \Home\Model\BudgetModel();
        $budget = $budget_model->get_one($bud_id);
        if($budget){
            //判断是否有权限进行编辑操作
            if(!check_acess_budget($budget['crt_user_id'], $user_id, $lvl_id, $depart_id, $depart_par_id)){
                redirect(U('Modifybudget:index')."?bud_id=$bud_id", 2, '您没有权限对该预算单进行变更操作!');
            }
            
            $id = I('get.id');
            $have_where = "is_del=1 and result=0 and bud_id=$bud_id";
            if($id){
                $have_where .= " and mod_id!=$id";
            }
            //判断是否有未完成流程的变更单
            if($modify_model->where($have_where)->count()){
                redirect(U('Modifybudget:index')."?bud_id=$bud_id", 2, '有未走审批完成的预算变更单!');
            }
            
            $user_model = new \Home\Model\UserModel();
            if($id){
                $id = intval($id);
                $modify = $modify_model->get_one($id);
                if($modify){
                    
                    if($modify['budget_proj_profit']){
                        $modify['budget_profit_percent'] = round(($modify['budget_proj_profit']/$modify['budget_cntr_income'])*100,2)."%";
                        $modify['modify_profit_percent'] = round(($modify['modify_proj_profit']/($modify['modify_cntr_income']?$modify['modify_cntr_income']:$modify['budget_cntr_income']))*100,2)."%";
                    }
                      
                    //申请人
                    $apply_user = $user_model->get_one($modify['crt_user_id']);
                    $app_real_name = $apply_user['real_name'];
                    
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($modify['mod_no'], 5);
                    //审批已经开始必须进入详情页
                    if($approve['aprv_result1']>0){
                        redirect(U("Modifybudget/info")."?id=".$id);
                    }
                    for ($i = 1; $i < 5; $i++) {
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
                    $is_show_app = check_is_show_app($modify['crt_user_id'], $user_id, $lvl_id, $approve, $modify['crt_time']);
                    
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getApproverList($modify['crt_user_id'], $user_id, $lvl_id, 5, $modify['crt_time'], $approve);
                    }
                    
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $modify['crt_user_id']==$user_id)){
                        $is_restart = true;
                    }
                    
                    //审批进度条内容
                    $approve_arr = getApprovers($approve, $modify['crt_user_id']);
                    
                    $this->assign('approve_arr', $approve_arr);
                    $this->assign('app_real_name', $app_real_name);
                    $this->assign('modify', $modify);
                    $third_party_arr = $modexpenses_model->final_list("mod_id=$id and type=1 and is_del=1");
                    $system_party_arr = $modexpenses_model->final_list("mod_id=$id and type=2 and is_del=1");
                }
            }else{
                $is_show_app = true;
                $is_restart = false;
                //审批人列表
                $approve_users = getApproverList($user_id, $user_id, $lvl_id, 5, time());
                $third_party_arr = $expenses_model->final_list("bud_id=$bud_id and type=1 and is_del=1");
                $system_party_arr = $expenses_model->final_list("bud_id=$bud_id and type=2 and is_del=1");
            }
            
            //获取第三方费用列表
            $third_party = $third_party_arr['list'];
            $budget['d_final_total'] = $third_party_arr['final_total'];
            
            //获取智源体系费用列表
            $system_party = $system_party_arr['list'];
            $budget['z_final_total'] = $system_party_arr['final_total'];
            
            //客户列表
            $customer_model = new \Home\Model\CustomerModel();
            $customers = $customer_model->get_one($budget['cust_id']);
            
            //获取项目编号
            $proj = $proj_model->get_one($budget['proj_id']);
            $proj = $this->getProjUsers($proj);
            $this->assign('proj', $proj);
            
            $this->assign('budget', $budget);
            $this->assign('third_party', $third_party);
            $this->assign('system_party', $system_party);
            $this->assign('user_id', $user_id);
            $this->assign('customers', $customers);
            $this->assign('approve_users', $approve_users);
            $this->assign('is_show_app', $is_show_app);
            $this->assign('is_restart', $is_restart);
            $this->display('Modifybudget:add');
        }
    }
    
    /*
     *  信息页
     **/
    public function infoAction(){
        $proj_model = new \Home\Model\ProjectModel();
        $budget_model = new \Home\Model\BudgetModel();
        $expenses_model = new \Home\Model\ExpensesModel();
        $modify_model = new \Home\Model\ModifybudgetModel();
        $modexpenses_model = new \Home\Model\ModifyexpensesModel();
        
        $lvl_id = session('lvl_id');
        $depart_id = session('depart_id');
        $depart_par_id = session('depart_par_id'); 
        $user_id = $this->user_id;
        $mod_id = I("get.id");
        
        if($mod_id){
            $mod_id = intval($mod_id);
            $modify = $modify_model->get_one($mod_id);
            if($modify['crt_time']>get_c_date()){
                if($modify){
                    $bud_id = $modify['bud_id'];
                    $budget_model = new \Home\Model\BudgetModel();
                    $budget = $budget_model->get_one($bud_id);
                
                    //判断是否有权限进行编辑操作
                    if(!check_acess_budget($modify['crt_user_id'], $user_id, $lvl_id, $depart_id, $depart_par_id)){
                        redirect(U('Modifybudget:index')."?bud_id=$bud_id", 2, '您没有权限对该预算单进行变更操作!');
                    }
                
                    if($modify['budget_proj_profit']){
                        $modify['budget_profit_percent'] = round(($modify['budget_proj_profit']/$modify['budget_cntr_income'])*100,2)."%";
                        $modify['modify_profit_percent'] = round(($modify['modify_proj_profit']/($modify['modify_cntr_income']?$modify['modify_cntr_income']:$modify['budget_cntr_income']))*100,2)."%";
                    }
                    $user_model = new \Home\Model\UserModel();
                    //申请人
                    $apply_user = $user_model->get_one($modify['crt_user_id']);
                    $app_real_name = $apply_user['real_name'];
                
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($modify['mod_no'], 5);
                    $approve_arr = getApprovers($approve, $modify['crt_user_id']);
                
                    //是否显示选择审批人功能
                    $is_show_app = false;
                    if($approve['result'] != -1){
                        $is_show_app = check_is_show_app($modify['crt_user_id'], $user_id, $lvl_id, $approve, $modify['crt_time']);
                    }
                
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getApproverList($modify['crt_user_id'], $user_id, $lvl_id, 5, $modify['crt_time'], $approve);
                    }
                
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $modify['crt_user_id']==$user_id)){
                        $is_restart = true;
                    }
                
                    //获取第三方费用列表
                    $third_party_arr = $modexpenses_model->final_list("mod_id=$mod_id and type=1 and is_del=1");
                    $third_party = $third_party_arr['list'];
                    $budget['d_final_total'] = $third_party_arr['final_total'];
                
                    //获取智源体系费用列表
                    $system_party_arr = $modexpenses_model->final_list("mod_id=$mod_id and type=2 and is_del=1");
                    $system_party = $system_party_arr['list'];
                    $budget['z_final_total'] = $system_party_arr['final_total'];
                
                    //客户列表
                    $customer_model = new \Home\Model\CustomerModel();
                    $customers = $customer_model->get_one($budget['cust_id']);
                
                    //获取项目编号
                    $proj = $proj_model->get_one($budget['proj_id']);
                    $proj = $this->getProjUsers($proj);
                    $this->assign('proj', $proj);
                
                    $this->assign('app_real_name', $app_real_name);
                    $this->assign('approve_arr', $approve_arr);
                    $this->assign('modify', $modify);
                    $this->assign('budget', $budget);
                    $this->assign('third_party', $third_party);
                    $this->assign('system_party', $system_party);
                    $this->assign('user_id', $user_id);
                    $this->assign('customers', $customers);
                    $this->assign('approve_users', $approve_users);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('lvl_id', $lvl_id);
                    $this->display('Modifybudget:info');
                }
            }else{
                if($modify){
                    $bud_id = $modify['bud_id'];
                    $budget_model = new \Home\Model\BudgetModel();
                    $budget = $budget_model->get_one($bud_id);
                
                    //判断是否有权限进行编辑操作
                    if(!check_acess_budget($modify['crt_user_id'], $user_id, $lvl_id, $depart_id, $depart_par_id)){
                        redirect(U('Modifybudget:index')."?bud_id=$bud_id", 2, '您没有权限对该预算单进行变更操作!');
                    }
                
                    if($modify['budget_proj_profit']){
                        $modify['budget_profit_percent'] = round(($modify['budget_proj_profit']/$modify['budget_cntr_income'])*100,2)."%";
                        $modify['modify_profit_percent'] = round(($modify['modify_proj_profit']/($modify['modify_cntr_income']?$modify['modify_cntr_income']:$modify['budget_cntr_income']))*100,2)."%";
                    }
                    $user_model = new \Home\Model\UserModel();
                    //申请人
                    $apply_user = $user_model->get_one($modify['crt_user_id']);
                    $app_real_name = $apply_user['real_name'];
                
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($modify['mod_no'], 5);
                    for ($i = 1; $i < 5; $i++) {
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
                    $is_show_app = false;
                    if($approve['result'] != -1){
                        $is_show_app = check_is_show_app($modify['crt_user_id'], $user_id, $lvl_id, $approve, $modify['crt_time']);
                    }
                
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getApproverList($modify['crt_user_id'], $user_id, $lvl_id, 5, $modify['crt_time'], $approve);
                    }
                
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $modify['crt_user_id']==$user_id)){
                        $is_restart = true;
                    }
                
                    //获取第三方费用列表
                    $third_party_arr = $modexpenses_model->final_list("mod_id=$mod_id and type=1 and is_del=1");
                    $third_party = $third_party_arr['list'];
                    $budget['d_final_total'] = $third_party_arr['final_total'];
                
                    //获取智源体系费用列表
                    $system_party_arr = $modexpenses_model->final_list("mod_id=$mod_id and type=2 and is_del=1");
                    $system_party = $system_party_arr['list'];
                    $budget['z_final_total'] = $system_party_arr['final_total'];
                
                    //客户列表
                    $customer_model = new \Home\Model\CustomerModel();
                    $customers = $customer_model->get_one($budget['cust_id']);
                
                    //获取项目编号
                    $proj = $proj_model->get_one($budget['proj_id']);
                    $proj = $this->getProjUsers($proj);
                    $this->assign('proj', $proj);
                
                    $this->assign('app_real_name', $app_real_name);
                    $this->assign('schedule', $approve);
                    $this->assign('modify', $modify);
                    $this->assign('budget', $budget);
                    $this->assign('third_party', $third_party);
                    $this->assign('system_party', $system_party);
                    $this->assign('user_id', $user_id);
                    $this->assign('customers', $customers);
                    $this->assign('approve_users', $approve_users);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('lvl_id', $lvl_id);
                    $this->display('Modifybudget:info_old');
                }
            }
        }
    }
    
    /*
     *  审批结果提交
     **/
    public function operateAction(){
        $id = I("post.mod_id");
        $num = I("post.num");
        $result = I("post.result");
        $modify_model = new \Home\Model\ModifybudgetModel();
        $modify = $modify_model->get_one($id);
        $budget_model = new \Home\Model\BudgetModel();
        $budget = $budget_model->get_one($modify['bud_id']);
        
        if(check_is_over($budget['proj_id']) && $result==1){
            $this->ajaxReturn(array('status'=>0, 'msg'=>'该项目已结束，只能进行不同意操作！'));
        }
        
        if($modify['crt_time']>get_c_date()){
            $cur_approver_id = I("post.cur_approver_id");
            $lvl_id = session('lvl_id');
            if($result == 1 && $lvl_id != 2 && $lvl_id != 3 && $cur_approver_id==0){          //同意操作，并且不是总经理和出纳，需要判断是否选择下一步审批人
                $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            }
            
            $msg = "保存失败！";
            if($modify){
                $app_model = new \Home\Model\ApproveModel();
                if($app_model->getApproveSchedule($modify['mod_no'] ,5)){
                    $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                    if($lvl_id == 3 || $result == -1){
                        $data['result'] = $result;
                    }
                    if(I("post.option")) $data['aprv_opinion'.$num] = I("post.option");
                    $modify_model->startTrans();           //开启事务
                    $ret = $app_model->update_data($data, "aprv_no='{$modify['mod_no']}' and aprv_type=5");
                    if($ret){
                        $is_true = true;
                        $user_id = $this->user_id;
                        if($lvl_id != 3 && $result == 1){
                            if($lvl_id == 2){
                                choose_approval_user($id,5,get_cw_userid(),$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给出纳
                            }else{
                                choose_approval_user($id,5,$cur_approver_id,$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给peter
                            }
                        }
                        if($lvl_id == 3 || $result == -1){
                            $ret1 = $modify_model->update_data(array('result'=>$result), "mod_id = $id");
                            if($ret1){
                                //把变更的数据提交到预算单中去
                                if($lvl_id == 3 && $result!=-1){
                                    $modexpenses_model = new \Home\Model\ModifyexpensesModel();
                                    $expenses_model = new \Home\Model\ExpensesModel();
            
                                    if($is_true == true){
                                        $list = $modexpenses_model->get_list("mod_id=$id and is_del=1");
                                        $d_budget_total = 0;
                                        $z_budget_total = 0;
                                        foreach ($list as $row) {
                                            if($row['modify_money']!="" && $row['exp_id']>0){                        //修改了款项
                                                $modify_money = doubleval($row['modify_money']);
                                                $budget_money = doubleval($row['budget_money']);
                                                $margin_money = $budget_money-$modify_money;
                                                $usable_money = 0;
                                                $expenses = $expenses_model->get_one($row['exp_id']);
                                                $old_usable_money = doubleval($expenses['usable_money']);
                                                if($margin_money>0){
                                                    if($old_usable_money<$margin_money){
                                                        $is_true = false;
                                                        $msg = "变更后金额为".$modify_money."的款项余额不足！";
                                                        break;
                                                    }
            
                                                }
                                                $usable_money = $old_usable_money-$margin_money;
                                                $ret3 = $expenses_model->update_data(array("budget_money"=>$modify_money,'usable_money'=>$usable_money), "exp_id=".$row['exp_id']);
                                            }elseif($row['exp_id'] == 0){              //新增的款项
                                                $data['bud_id'] = $budget['bud_id'];
                                                $data['proj_id'] = $budget['proj_id'];
                                                $data['type'] = $row['type'];
                                                $data['cost_id'] = $row['cost_id'];
                                                $data['budget_money'] = $row['budget_money'];
                                                $data['usable_money'] = $row['budget_money'];
                                                $data['comm'] = $row['comm'];
                                                $ret3 = $expenses_model->insert_data($data);
                                            }else{
                                                $ret3 = true;
                                            }
                                            if(!$ret3){
                                                $is_true = false;
                                                break;
                                            }
            
                                            if($row['type'] == 1){
                                                if($row['exp_id']>0 && $row['modify_money']!=""){
                                                    $d_budget_total += doubleval($row['modify_money']);
                                                }else{
                                                    $d_budget_total += doubleval($row['budget_money']);
                                                }
                                            }elseif($row['type'] == 2){
                                                if($row['exp_id']>0 && $row['modify_money']!=""){
                                                    $z_budget_total += doubleval($row['modify_money']);
                                                }else{
                                                    $z_budget_total += doubleval($row['budget_money']);
                                                }
                                            }
                                        }
            
                                        //重新计算项目利润和税点
                                        if($is_true == true){
                                            //预算合同金额的改变
                                            $cntr_income = 0;
                                            if($modify['modify_cntr_income']!=""){
                                                $modify_cntr_income = doubleval($modify['modify_cntr_income']);
                                                $cntr_income = $modify_cntr_income;
                                            }else{
                                                $cntr_income = doubleval($budget['budget_cntr_income']);
                                            }
            
                                            $budget_point = round($cntr_income*0.0634,2);           //税点
                                            $budget_proj_profit = $cntr_income-$budget_point-$z_budget_total-$d_budget_total;           //项目利润
            
                                            $ret2 = $budget_model->update_data(array('budget_cntr_income'=>$cntr_income,'budget_point'=>$budget_point,'budget_proj_profit'=>$budget_proj_profit,'d_budget_total'=>$d_budget_total,'z_budget_total'=>$z_budget_total), "bud_id=".$modify['bud_id']);
                                            if(!$ret2){
                                                $is_true = false;
                                            }
                                        }
                                    }
                                }
                                
                                $content = "您的一个预算变更单审批已通过，请注意查看。";
                                if($result == -1){
                                    $content = "您的一个预算变更单审批未获通过，请注意查看。";
                                }
                                send_email_approve($modify['crt_user_id'], '预算变更单审批通知', $content."单号：{$modify['mod_no']}。", U("Modifybudget/info",array('id'=>$id)));           //发邮件
                            }else{
                                $is_true = false;
                            }
                        }
                        if($is_true){
                            $modify_model->commit();
                            logrecords("update", $app_model->get_tablename());          //日志
                            $this->ajaxReturn(array('status'=>1));
                        }
                    }
                    $modify_model->rollback();            //回滚
                }
            }
        }else{
            $msg = "保存失败！";
            if($modify){
                $app_model = new \Home\Model\ApproveModel();
                if($app_model->getApproveSchedule($modify['mod_no'] ,5)){
                    $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                    if($num == 4 || $result == -1){
                        $data['result'] = $result;
                        if($result == -1){
                            $data['aprv_opinion'.$num] = I("post.option");
                        }
                    }
                    
                    $modify_model->startTrans();           //开启事务
                    $ret = $app_model->update_data($data, "aprv_no='{$modify['mod_no']}' and aprv_type=5");
                    if($ret){
                        $is_true = true;
                        $user_id = $this->user_id;
                        $lvl_id = session('lvl_id');
                        if($lvl_id == 2 && $result == 1){
                            choose_approval_user($id,5,get_cw_userid(),$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给peter
                        }
                        if($num == 4 || $result == -1){
                            $ret1 = $modify_model->update_data(array('result'=>$result), "mod_id = $id");
                            if($ret1){
                                //把变更的数据提交到预算单中去
                                if($num == 4 && $result!=-1){
                                    $modexpenses_model = new \Home\Model\ModifyexpensesModel();
                                    $expenses_model = new \Home\Model\ExpensesModel();
                                    $budget_model = new \Home\Model\BudgetModel();
                                    $budget = $budget_model->get_one($modify['bud_id']);
                                
                                    if($is_true == true){
                                        $list = $modexpenses_model->get_list("mod_id=$id and is_del=1");
                                        $d_budget_total = 0;
                                        $z_budget_total = 0;
                                        foreach ($list as $row) {
                                            if($row['modify_money']!="" && $row['exp_id']>0){                        //修改了款项
                                                $modify_money = doubleval($row['modify_money']);
                                                $budget_money = doubleval($row['budget_money']);
                                                $margin_money = $budget_money-$modify_money;
                                                $usable_money = 0;
                                                $expenses = $expenses_model->get_one($row['exp_id']);
                                                $old_usable_money = doubleval($expenses['usable_money']);
                                                if($margin_money>0){
                                                    if($old_usable_money<$margin_money){
                                                        $is_true = false;
                                                        $msg = "变更后金额为".$modify_money."的款项余额不足！";
                                                        break;
                                                    }
                                                    
                                                }
                                                $usable_money = $old_usable_money-$margin_money;
                                                $ret3 = $expenses_model->update_data(array("budget_money"=>$modify_money,'usable_money'=>$usable_money), "exp_id=".$row['exp_id']);
                                            }elseif($row['exp_id'] == 0){              //新增的款项
                                                $data['bud_id'] = $budget['bud_id'];
                                                $data['proj_id'] = $budget['proj_id'];
                                                $data['type'] = $row['type'];
                                                $data['cost_id'] = $row['cost_id'];
                                                $data['budget_money'] = $row['budget_money'];
                                                $data['usable_money'] = $row['budget_money'];
                                                $data['comm'] = $row['comm'];
                                                $ret3 = $expenses_model->insert_data($data);
                                            }else{
                                                $ret3 = true;
                                            }
                                            if(!$ret3){
                                                $is_true = false;
                                                break;
                                            }
                                            
                                            if($row['type'] == 1){
                                                if($row['exp_id']>0 && $row['modify_money']!=""){
                                                    $d_budget_total += doubleval($row['modify_money']);
                                                }else{
                                                    $d_budget_total += doubleval($row['budget_money']);
                                                }
                                            }elseif($row['type'] == 2){
                                                if($row['exp_id']>0 && $row['modify_money']!=""){
                                                    $z_budget_total += doubleval($row['modify_money']);
                                                }else{
                                                    $z_budget_total += doubleval($row['budget_money']);
                                                }
                                            }
                                        }
                                        
                                        //重新计算项目利润和税点
                                        if($is_true == true){
                                            //预算合同金额的改变
                                            $cntr_income = 0;
                                            if($modify['modify_cntr_income']!=""){
                                                $modify_cntr_income = doubleval($modify['modify_cntr_income']);
                                                $cntr_income = $modify_cntr_income;
                                            }else{
                                                $cntr_income = doubleval($budget['budget_cntr_income']);
                                            }
                                            
                                            $budget_point = round($cntr_income*0.0634,2);           //税点
                                            $budget_proj_profit = $cntr_income-$budget_point-$z_budget_total-$d_budget_total;           //项目利润
                                            
                                            $ret2 = $budget_model->update_data(array('budget_cntr_income'=>$cntr_income,'budget_point'=>$budget_point,'budget_proj_profit'=>$budget_proj_profit,'d_budget_total'=>$d_budget_total,'z_budget_total'=>$z_budget_total), "bud_id=".$modify['bud_id']);
                                            if(!$ret2){
                                                $is_true = false;
                                            }
                                        }
                                    }
                                }
                            }else{
                                $is_true = false;
                            }
                        }
                        if($is_true){
                            $modify_model->commit();
                            logrecords("update", $app_model->get_tablename());          //日志
                            $content = "您的一个预算变更单审批已通过，请注意查看。";
                            if($result == -1){
                                $content = "您的一个预算变更单审批未获通过，请注意查看。";
                            }
                            send_email_approve($modify['crt_user_id'], '预算变更单审批通知', $content."单号：{$modify['mod_no']}。", U("Modifybudget/info",array('id'=>$id)));           //发邮件
                            
                            $this->ajaxReturn(array('status'=>1));
                        }
                    }
                    $modify_model->rollback();            //回滚
                }
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>$msg));
    }
    
    /*
     *  返回操作
     **/
    public function backAction(){
        if(IS_POST){
            $lvl_id = session('lvl_id');
            $user_id = $this->user_id;
            if($lvl_id==2){
                $app_model = new \Home\Model\ApproveModel();
                $obj_id = I('post.obj_id');
    
                //借款单
                $obj_model = new \Home\Model\ModifybudgetModel();
                $where = "mod_id = '$obj_id'";
                $obj = $obj_model->get_onebywhere($where);
                if($obj){
                    $aprv_no = $obj['mod_no'];
                }
                $approve = $app_model->getApproveSchedule($aprv_no, 5);
                $obj_model->startTrans();
                $ret = $obj_model->update_data(array('cur_approver_id'=>$user_id,'result'=>0), $where);
                for ($i = 1; $i < 6; $i++) {
                    if($approve["aprv_user_id".$i] == get_zjl_userid()){
                        break;
                    }
                }
                $ret1 = $app_model->update_data(array('aprv_user_id'.($i+1) => 0, 'aprv_result'.$i => 0, 'result'=>0), "aprv_no='$aprv_no' and aprv_type=5");
                if($ret && $ret1){
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
        $id = I("post.mod_id");
        $cur_approver_id = I("post.cur_approver_id");
        $modify_model = new \Home\Model\ModifybudgetModel();
        $modify = $modify_model->get_one($id);
        if($modify){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($modify['mod_no'], 5);
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
            $modify_model->startTrans();     //开启事务
            $ret = $modify_model->update_data(array('cur_approver_id'=>$cur_approver_id), "mod_id=".$id);
            if($ret){
                $ret1 = $app_model->updateApproved($approve, $cur_approver_id, $lvl_id, $user_id);
                if($ret && $ret1){
                    logrecords("update", $app_model->get_tablename());          //日志
                    $modify_model->commit();
                    send_email_approve($cur_approver_id, '预算变更单审批通知', "您有一个预算变更单审批请求！",U("Modifybudget/info",array('id'=>$id)));  //发邮件
                    $this->ajaxReturn(array('status'=>1));
                }else{
                    $modify_model->rollback();
                }
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    /*
     *  重新发启审核
     **/
    public function restartAction(){
        $id = I('post.mod_id');
        $modify_model = new \Home\Model\ModifybudgetModel();
        $modify = $modify_model->get_one($id);
        if($modify){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($modify['mod_no'], 5);
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
                $ret = $app_model->update_data($data, "aprv_id={$approve['aprv_id']}");
                if($ret){
                    $modify_model->update_data(array('cur_approver_id'=>0,'result'=>0), "mod_id=$id");
                    logrecords("update", $app_model->get_tablename());          //日志
                    $this->ajaxReturn(array('status'=>1));
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
            $id = I('post.mod_id');
            $modify_model = new \Home\Model\ModifybudgetModel();
            $modify = $modify_model->get_one($id);
            if($modify){
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($modify['mod_no'], 5);
                if(!check_permission_left('financial_approve', 'finance')){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可删除！'));
                    }
                }
        
                $ret = $modify_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "mod_id = $id");
                if($ret){
                    $ret1 = $app_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "aprv_no='{$modify['mod_no']}' and aprv_type=5");
                    logrecords('delete', $modify_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'预算变更单不存在！'));
        }
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
        return "MSD".date('YmdHis').sprintf("%04d",rand(0, 1000));
    }

    public function getProjUsers($proj){
        $pusers_model = new \Home\Model\ProjectUsersModel();
        $user_model = new \Home\Model\UserModel();
        $users = $pusers_model->get_user_list($proj['proj_id']);
        $proj['proj_users'] = '';
        foreach ($users as $user) {
            $proj['proj_users'] .= $user['real_name']."/";
        }
        $proj['proj_users'] = rtrim($proj['proj_users'],"/");
        $proj_mgr = $user_model->get_one($proj['proj_mgr']);
        $proj['proj_mgr'] = $proj_mgr['real_name'];
        return $proj;
    }

    public function check_is_out($d_str, $z_str){
        $expenses_model = new \Home\Model\ExpensesModel();
        $d_array = explode(";", $d_str);
        $is_out = false;
        $cost_id = 0;
        $ret = array();
        foreach ($d_array as $d_row) {
            if($d_row && $d_arr = explode("^", $d_row)){
                $modify_money = doubleval($d_arr[2]);
                $budget_money = doubleval($d_arr[1]);
                if($d_arr[3] && mb_strlen($d_arr[2])>0 && $budget_money>$modify_money){
                    $expenses = $expenses_model->get_one($d_arr[3]);
                    if(($budget_money-$modify_money)>$expenses['usable_money']){
                        $cost_id = $expenses['cost_id'];
                        $is_out = true;
                        break;
                    }
                }
            }
        }
        if($is_out == false){
            $z_array = explode(";", $z_str);
            foreach ($z_array as $z_row) {
                if($z_row && $z_arr = explode("^", $z_row)){
                    $modify_money = doubleval($z_arr[2]);
                    $budget_money = doubleval($z_arr[1]);
                    if($z_arr[3] && mb_strlen($z_arr[2])>0 && $budget_money>$modify_money){
                        $expenses = $expenses_model->get_one($z_arr[3]);
                        if(($budget_money-$modify_money)>$expenses['usable_money']){
                            $cost_id = $expenses['cost_id'];
                            $is_out = true;
                            break;
                        }
                    }
                }
            }
        }
        $ret['is_out'] = $is_out;
        if($is_out && $cost_id>0){
            $cost = M('Cost_type')->where(array('id'=>$cost_id))->find();
            $ret['costname'] = $cost['costname'];
        }
        
        return $ret;
    }
    
    public function check_is_modify($d_str, $z_str){
        $is_modify = false;
        $d_array = explode(";", $d_str);
        foreach ($d_array as $d_row) {
            if($d_row && $d_arr = explode("^", $d_row)){
                $modify_money = doubleval($d_arr[2]);
                $budget_money = doubleval($d_arr[1]);
                if((mb_strlen($d_arr[2])>0 && $modify_money!=$budget_money) || $d_arr[2]=="none"){
                    $is_modify = true;
                    break;
                }
            }
        }
        if($is_modify == false){
            $z_array = explode(";", $z_str);
            foreach ($z_array as $z_row) {
                if($z_row && $z_arr = explode("^", $z_row)){
                    $modify_money = doubleval($z_arr[2]);
                    $budget_money = doubleval($z_arr[1]);
                    if((mb_strlen($z_arr[2])>0 && $modify_money!=$budget_money) || $z_arr[2]=="none"){
                        $is_modify = true;
                        break;
                    }
                }
            }
        }
        return $is_modify;
    }
}