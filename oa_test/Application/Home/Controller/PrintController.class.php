<?php
/**
 * OA系统--打印
 */
namespace Home\Controller;
class PrintController extends BaseController{
    
    public $check_access = false;
    public $head_title = '打印';
    
    /*
     *  借款单
     **/
    public function borrowAction(){
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
                $borrow['crt_time'] = date('Y-m-d H:i:s',$borrow['crt_time']);
                $borrow['print_time'] = date('Y-m-d H:i:s');
                
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
                $borrow['proj_name'] = $proj['proj_name']; 
                
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
                /* for ($i = 1; $i < 6; $i++) {
                    if($approve['aprv_user_id'.$i]>0){
                        $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                        if($app_user){
                            $approve['user_real_name'.$i] = $app_user['real_name'];
                        }
                    }else{
                        break;
                    }
                } */
                
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
                $this->assign('approve_arr', $approve_arr);
                $this->assign('user_id', $this->user_id);
                $this->display('Print:borrow');
            }
        }
    }

    public function mj_recoupedAction(){
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
                $recouped['crt_time'] = date('Y-m-d H:i:s',$recouped['crt_time']);
                $recouped['print_time'] = date('Y-m-d H:i:s');
        
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
        
                //审批人列表
                $approve_users = getApproverListByProj($recouped['proj_id'],$recouped['user_id'], $user_id, $lvl_id, $recouped['crt_time'], $approve);
        
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
            $this->assign('app_real_name', $app_real_name);
            $this->assign('schedule', $approve);
            $this->assign('depart', $depart);
            $this->assign('projs', $projs);
            $this->assign('approve_users', $approve_users);
            $this->assign('user_id', $user_id);
            $this->assign('apply_user', $apply_user);
            $this->display('Print:mj_recouped');
        }
    }
    
    public function recoupedAction(){
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
                $recouped['crt_time'] = date('Y-m-d H:i:s',$recouped['crt_time']);
                $recouped['print_time'] = date('Y-m-d H:i:s');
        
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
                $recouped['proj_name'] = $proj['proj_name'];
        
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
                
                /* for ($i = 1; $i < 6; $i++) {
                    if($approve['aprv_user_id'.$i]>0){
                        $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                        if($app_user){
                            $approve['user_real_name'.$i] = $app_user['real_name'];
                        }
                    }else{
                        break;
                    }
                } */
                //借款单列表
                if($recouped['borrow_id']){
                    $borrows = $borrow_model->get_one($recouped['borrow_id']);
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
        
                $this->assign('supplers', $supplers);
                $this->assign('recouped', $recouped);
                $this->assign('money_list', $money_list);
                $this->assign('apply_user', $apply_user);
                $this->assign('depart', $depart);
                $this->assign('projs', $projs);
                $this->assign('schedule', $approve);
                $this->assign('user_id', $this->user_id);
                $this->assign('lvl_id', $lvl_id);
                $this->assign('approve_arr', $approve_arr);
                $this->display('Print:recouped');
            }
        }
    }
    
    public function pfrecoupedAction(){
        $id = I('get.id');
        if($id){
            $id = intval($id);
            $recouped_model = new \Home\Model\PfrecoupedModel();
            $money_model = new \Home\Model\MoneyModel();
            $user_model = new \Home\Model\UserModel();
            $recouped = $recouped_model->get_one($id);
            $file_model = new \Home\Model\FileModel();
            if($recouped){
                $user_id = $this->user_id;
                $lvl_id = session('lvl_id');
                $recouped['crt_time'] = date('Y-m-d H:i:s',$recouped['crt_time']);
                $recouped['print_time'] = date('Y-m-d H:i:s');
    
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
                if($recouped['get_id']){
                    $supplers = $this->get_sup_list(1, "sup_id={$recouped['get_id']} and is_del=1", 0, 1);
                    $recouped['get_user'] = $supplers[0]['payee'];
                }elseif($recouped['borrow_way']==2 && !$recouped['get_user']){
                    $recouped['get_user'] = $apply_user['real_name'];
                }
    
                //申请人获取所在部门
                $depart_model = new \Home\Model\DepartModel();
                $depart = $depart_model->get_one($apply_user['depart_id']);
    
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($recouped['pf_no'], 6);
                $approve_arr = getApprovers($approve, $recouped['crt_user_id']);
                /* for ($i = 1; $i < 5; $i++) {
                    if($approve['aprv_user_id'.$i]>0){
                        $app_user = $user_model->get_one($approve['aprv_user_id'.$i]);
                        if($app_user){
                            $approve['user_real_name'.$i] = $app_user['real_name'];
                        }
                    }else{
                        break;
                    }
                } */
    
                $this->assign('recouped', $recouped);
                $this->assign('money_list', $money_list);
                $this->assign('apply_user', $apply_user);
                $this->assign('depart', $depart);
                $this->assign('schedule', $approve);
                $this->assign('user_id', $this->user_id);
                $this->assign('lvl_id', $lvl_id);
                $this->assign('approve_arr', $approve_arr);
                $this->display('Print:pfrecouped');
            }
        }
    }
    
    
    public function TbrecoupedAction(){
        $id = I('get.id');
        if($id){
            $id = intval($id);
            $recouped_model = new \Home\Model\TbrecoupedModel();
            $money_model = new \Home\Model\MoneyModel();
            $user_model = new \Home\Model\UserModel();
            $recouped = $recouped_model->get_one($id);
            $file_model = new \Home\Model\FileModel();
            if($recouped){
                $user_id = $this->user_id;
                $lvl_id = session('lvl_id');
                $recouped['crt_time'] = date('Y-m-d H:i:s',$recouped['crt_time']);
                $recouped['print_time'] = date('Y-m-d H:i:s');
    
                //获取款项列表
                if($recouped['result'] == -1){
                    $money_list = $money_model->get_list("obj_id=$id and obj_type=4 and is_cancel=1");
                }else{
                    $money_list = $money_model->get_list("obj_id=$id and obj_type=4 and is_del=1");
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
    
                //申请人获取所在部门
                $depart_model = new \Home\Model\DepartModel();
                $depart = $depart_model->get_one($apply_user['depart_id']);
    
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($recouped['tb_no'], 7);
                $approve_arr = getApprovers($approve, $recouped['crt_user_id']);
    
                $this->assign('recouped', $recouped);
                $this->assign('money_list', $money_list);
                $this->assign('apply_user', $apply_user);
                $this->assign('depart', $depart);
                $this->assign('schedule', $approve);
                $this->assign('user_id', $this->user_id);
                $this->assign('lvl_id', $lvl_id);
                $this->assign('approve_arr', $approve_arr);
                $this->display('Print:tbrecouped');
            }
        }
    }
    
    
    public function refundAction(){
        $id = I('get.id');
        if($id){
            $id = intval($id);
            $refund_model = new \Home\Model\RefundModel();
            $refund = $refund_model->get_one($id);
            
            if($refund){
                $user_id = $this->user_id;
                $proj_id = $refund['proj_id'];
                $lvl_id = session('lvl_id');
                
                $refund['crt_time'] = date('Y-m-d H:i:s',$refund['crt_time']);
                $refund['print_time'] = date('Y-m-d H:i:s');
            
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
                $this->display('Print:refund');
            }
        }
    }
    
    /*
     *  记录打印次数
     **/
    public function confirmprintAction(){
        $type = I('post.type');
        $obj = null;
        if($type == 1){
            $obj = M('Borrow');
            $sid = "borrow_id";
        }elseif($type == 2){
            $obj = M('Recouped');
            $sid = "rec_id";
        }elseif($type == 3){
            $obj = M('Refund');
            $sid = "ref_id";
        }else{
            
        }
        
        $id = I('post.id');
        if($id){
            $obj->where("$sid=$id")->setInc('print_num',1);
        }
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
}