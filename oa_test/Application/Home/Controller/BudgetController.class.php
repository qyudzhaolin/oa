<?php
/**
 * OA系统项目预算单
 */
namespace Home\Controller;
use Think\Page;
use Think\Model;
class BudgetController extends BaseController{
    
    public $check_access = true;
    public $head_title = '项目预算单';
    
    public function indexAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\ApproveModel();
        $proj_model = new \Home\Model\ProjectModel();
       
        if($user_id == get_cw_userid() or $lvl_id==1){
            $export = I('get.export', 0);
        }
        
        $access_allvoucher = get_access_allvoucher($user_id);
        if($access_allvoucher){                    
            $where = "is_del = 1";
            if($lvl_id!=2){
                //获取所属公司下属的项目相关预算单
                $where .= " and proj_id in (".$proj_model->get_wherebycompanyid(session('company_id')).")";
            }
        }else{
            $where = "is_del = 1 and (crt_user_id = $user_id";
            
            //获取归该用户审批的预算单列表
            $awhere = $app_model->get_whereaprrove($user_id, 1);
            $where .= " or bud_no in ($awhere)";
            
            //获取该用户参与项目的相关预算单
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
            $plist = $proj_model->get_list("is_del = 1 and proj_no like '%$keyword%'","proj_id");          //检索项目列表
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
        
        $budget_model = new \Home\Model\BudgetModel();
        $count = $budget_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        if($export == 1){
            $list = $budget_model->where($where)->order('bud_id desc')->select();
        }else{
            $list = $budget_model->where($where)->order('bud_id desc')->limit($page->firstRow.','.$page->listRows)->select();
        }
        $user_model = new \Home\Model\UserModel();
        $depart_model = new \Home\Model\DepartModel();
        foreach ($list as &$row) {
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            $row['end_time'] = date('Y-m-d', $row['end_time']);
            
            $proj = $proj_model->get_one($row['proj_id']);               //项目
            $row['proj_name'] = $proj['proj_name'];
            $row['proj_no'] = $proj['proj_no'];
            
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
            $approve = $app_model->getApproveSchedule($row['bud_no'], 1);
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
            if(($lvl_id == 1 || $row['crt_user_id'] == $user_id) && $i==1 && $approve['aprv_result1']==0){
                $row['action'] = "add";
            }
            
            //变更操作
            $row['modify_action'] = false;
            /*if(($row['result'] == 1 && $lvl_id!=3 && $lvl_id!=4 && $lvl_id<10) || in_array($user_id, array(42))){
                $row['modify_action'] = true;
            }*/
            if(($row['result'] == 1 && $lvl_id!=3 && $lvl_id!=4 && $lvl_id<10) || in_array($user_id, array(367))){
                $row['modify_action'] = true;
            }
            
            if($access_allvoucher){
                $expenses_model = new \Home\Model\ExpensesModel();
                $expenses = $expenses_model->where("bud_id={$row['bud_id']} and is_del=1")->field("sum(budget_money) as total_budget,sum(usable_money) as total_usable")->find();
                $row['total_use_money'] = sprintf("%.2f",(doubleval($expenses['total_budget']) - doubleval($expenses["total_usable"])));
                $row['total_nuse_money'] = sprintf("%.2f",(doubleval($expenses['total_usable'])));
            }
        }
        
        $is_budget_add = true;
        if($lvl_id>8 && !in_array($user_id, array(40,42,45))){
            $is_budget_add = false;
        }
        
        $this->assign('is_mjsupplier_export', check_permission_left("Mjsupplier", "export_data"));
        $this->assign('access_allvoucher', $access_allvoucher);
        $this->assign('is_budget_add', $is_budget_add);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('user_id', $user_id);
        $this->assign('lvl_id', $lvl_id);
        $this->display('Budget:index');
    }
    
    /**
     * 导出信息
     */
    public function exportAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        
        if(!check_permission_left("financial_approve", "budget_export")){
            exit();
        }
        
        $app_model = new \Home\Model\ApproveModel();
        $proj_model = new \Home\Model\ProjectModel();
        $access_allvoucher = get_access_allvoucher($user_id);
        $where = "result =1 and is_del = 1 and proj_id in (".$proj_model->get_wherebycompanyid(session('company_id')).")";
        
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
            $plist = $proj_model->get_list("is_del = 1 and proj_no like '%$keyword%'","proj_id");          //检索项目列表
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
        
        $budget_model = new \Home\Model\BudgetModel();
        $list = $budget_model->where($where)->order('bud_id desc')->select();
        $user_model = new \Home\Model\UserModel();
        $depart_model = new \Home\Model\DepartModel();
        
        $header1 = array("预算单号","项目编号","项目名称","部门","申请人","项目创建时间","项目合同金额","项目预算金额","项目预算毛利率","已经审批报销金额","已经支付报销金额","项目决算费用金额","项目截止日期");
        $header = implode("\t",array_values($header1));
        $header .= "\t\n";
        $content .= $header;
        ob_end_clean();
        header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        header("X-DNS-Prefetch-Control: off");
        header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=budget.xls");
        $content=iconv("UTF-8","GBK//IGNORE",$content) ;
        echo $content;
        
        $rec_model = new \Home\Model\RecoupedModel();
        $bo_model = new \Home\Model\BorrowModel(); 
        $ref_model = new \Home\Model\RefundModel();
        $expenses_model = new \Home\Model\ExpensesModel();
        
        foreach ($list as $row) {
            $tmp_array["bud_no"] = $row['bud_no'];
            $proj = $proj_model->get_one($row['proj_id']);               //项目
            $tmp_array["proj_no"] = $proj['proj_no'];
            $tmp_array["proj_name"] = $proj['proj_name'];
            
            //获取申请人信息
            $user = $user_model->get_one($row['crt_user_id']);               //申请人员
            if($user){
                if($user['depart_id']){                          //部门名称
                    $depart = $depart_model->get_one($user['depart_id']);
                    if($depart){
                        $tmp_array["depart_name"] = $depart['depart_name'];
                    }
                }
                $tmp_array["real_name"] = $user['real_name'];
            }
            $tmp_array["crt_time"] = date('Y-m-d H:i:s', $row['crt_time']);
            $tmp_array['cntr_val'] = $proj['cntr_val'];
            $tmp_array['budget_cntr_income'] = $row['budget_cntr_income'];
            $tmp_array['profit_percent'] = round(($row['budget_proj_profit']/$row['budget_cntr_income'])*100,2)."%";
            
            //正在审批报销金额=借款单+无借款报销的总金额
            $rec_w_total = $rec_model->where("bud_id={$row['bud_id']} and is_del=1 and result=0 and borrow_id=0")->field("sum(tot_amt) as total_money")->find();
            $bo_w_total = $bo_model->where("bud_id={$row['bud_id']} and is_del=1 and result=0")->field("sum(tot_amt) as total_money")->find();
            $tmp_array["final_w_total"] = sprintf("%.2f",(doubleval($rec_w_total['total_money']) + doubleval($bo_w_total['total_money'])));
            
            //已经支付的金额
            $rec_y_total = $rec_model->where("bud_id={$row['bud_id']} and is_del=1 and result=1 and borrow_id=0")->field("sum(tot_amt) as total_money")->find();
            $bo_y_total = $bo_model->where("bud_id={$row['bud_id']} and is_del=1 and result=1")->field("sum(tot_amt) as total_money")->find();
            $ref_y_total = $ref_model->where("proj_id={$row['proj_id']} and is_del=1 and result=1")->field("sum(tot_amt) as total_money")->find();
            $tmp_array["final_y_total"] = sprintf("%.2f",(doubleval($rec_y_total['total_money']) + doubleval($bo_y_total['total_money']) - doubleval($ref_y_total['total_money'])));
            
            $third_party_arr = $expenses_model->final_list("bud_id={$row['bud_id']} and type=1 and is_del=1");
            $third_party = $third_party_arr['list'];
            $d_final_total = $third_party_arr['final_total'];
            
            $system_party_arr = $expenses_model->final_list("bud_id={$row['bud_id']} and type=2 and is_del=1");
            $system_party = $system_party_arr['list'];
            $z_final_total = $system_party_arr['final_total'];
            
            $tmp_array["final_total"] = sprintf("%.2f",(doubleval($d_final_total) + doubleval($z_final_total)));
            $tmp_array['end_time'] = date("Y-m-d", $row['end_time']);
            $new_arr = array();
            $content = "";
            foreach ($tmp_array as $key => $value)
            {
                array_push($new_arr, trim($value));
            }
            
            $line = implode("\t",$new_arr);
            $line .= "\t\n";
            $content .= $line;
            
            $content=@iconv("UTF-8","GBK//IGNORE",$content) ;
            echo $content;
        }
    }
    
    
    /*  
     *  新增、修改用户页
     **/
    public function addAction(){
        $proj_model = new \Home\Model\ProjectModel();
        $budget_model = new \Home\Model\BudgetModel();
        $expenses_model = new \Home\Model\ExpensesModel();
        
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        
        if(IS_POST){
            $data['cust_id'] = I('post.customer_id',0);
            $data['proj_id'] = I('post.proj_id',0);
            $data['budget_cntr_income'] = I('post.budget_cntr_income');
            $data['budget_point'] = I('post.budget_point');
            $data['budget_proj_profit'] = I('post.budget_proj_profit');
            $data['d_budget_total'] = I('post.d_budget_total');
            $data['z_budget_total'] = I('post.z_budget_total');
            $id = I("post.bud_id");
            $d_str = I("post.d_str");
            $z_str = I("post.z_str");
            $data['cur_approver_id'] = I('post.cur_approver_id');
            $data['end_time'] = strtotime(I('post.end_time'));
        
            //基础数据验证
            if($data['cust_id']==0) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择客户！'));
            if($data['proj_id']==0) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择项目！'));
            if(!$data['cur_approver_id'] && !$id) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            
            //判断是否可以更新
            $app_model = new \Home\Model\ApproveModel();
            $approve = null;
            $budget = null;
            $action = '';
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $budget = $budget_model->get_one($id);
                $approve = $app_model->getApproveSchedule($budget['bud_no'], 1);
                if(session('lvl_id')!=1){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                    }
                }
            }
            
            //判断该项目是否已经新建预算单
            $have_budget = $budget_model->get_onebywhere("is_del=1 and proj_id={$data['proj_id']}");
            if($have_budget && (!$id || $id!=$have_budget['bud_id'])){
                $this->ajaxReturn(array('status'=>0, 'msg'=>'该项目已新建预算单！'));
            }
        
            //新增、更新数据
            $budget_model->startTrans();     //开启事务
            if($id){
                $ret = $budget_model->update_data($data, "bud_id = $id");            //更新预算单表
                if($ret && $budget['cur_approver_id'] != $data['cur_approver_id']){
                    $ret3 = $app_model->updateApproved($approve, $data['cur_approver_id'], $lvl_id, $user_id);
                }else{
                    $ret3 = true;
                }
                $action = 'update';
            }else{
                $data['crt_user_id'] = $this->user_id;
                $data['bud_no'] = $this->getBorrowNo();
                $ret = $budget_model->insert_data($data);                               //插入预算单表
                $ret3 = true;
                $action = 'insert';
            }
            if($ret && ret3){
                if($action == 'update'){
                    if($expenses_model->get_onebywhere("bud_id=$id and is_del=1")){
                        $ret2 = $expenses_model->update_data(array('is_del'=>-1), "bud_id = $id");
                    }else{
                        $ret2 = true;
                    }
                    $ret = $id;
                }else{
                    //提交审批表
                    $ret2 = $app_model->insert_data(array('aprv_no'=>$data['bud_no'],'aprv_type'=>1,'aprv_user_id1'=>$data['cur_approver_id'],'proj_id'=>$data['proj_id']));
                }
                if($ret2){
                    $issubmit = true;
                    $d_str = str_replace("&amp;", "&", $d_str);
                    $d_array = explode(";", $d_str);
                    foreach ($d_array as $d_row) {
                        if($d_row && $d_arr = explode("^", $d_row)){
                            $ret1 = $expenses_model->insert_data(array('bud_id'=>$ret,'proj_id'=>$data['proj_id'],'type'=>1,'cost_id'=>$d_arr[0], 'budget_money'=>$d_arr[1], 'comm'=>$d_arr[2], 'usable_money'=>$d_arr[1], 'file_id'=>$d_arr[3]));
                            if(!$ret1){
                                $issubmit = false;
                                //$budget_model->rollback();        //回滚
                                break;
                            }
                        }
                    }
                    
                    if($issubmit == true){
                        $z_str = str_replace("&amp;", "&", $z_str);
                        $z_array = explode(";", $z_str);
                        foreach ($z_array as $z_row) {
                            if($z_row && $z_arr = explode("^", $z_row)){
                                $ret2 = $expenses_model->insert_data(array('bud_id'=>$ret,'proj_id'=>$data['proj_id'],'type'=>2,'cost_id'=>$z_arr[0], 'budget_money'=>$z_arr[1], 'comm'=>$z_arr[2], 'usable_money'=>$z_arr[1]));
                                if(!$ret2){
                                    $issubmit = false;
                                    //$budget_model->rollback();        //回滚
                                    break;
                                }
                            }
                        }
                    }
                    
                    if($issubmit){
                        logrecords($action, $budget_model->get_tablename());          //日志
                        if($budget_model->commit()){
                            if($action == 'insert' || ($action == 'update' && $budget['cur_approver_id'] != $data['cur_approver_id'])){
                                send_email_approve($data['cur_approver_id'], '预算单审批通知', "您有一个预算单审批请求！",U("Budget/info",array('id'=>$ret)));
                            }
                            $this->ajaxReturn(array('status'=>1));
                        }
                    }
                }
            }
            $budget_model->rollback();        //回滚
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
        
        $id = I('get.id');
        $app_real_name = session('real_name');
        if($id){
            $id = intval($id);
            $budget = $budget_model->get_one($id);
            if($budget){
                //判断是否有权限进行编辑操作
                if($budget['crt_user_id'] != $user_id && $lvl_id!=1 && $lvl_id!=2){
                    redirect(U('Budget:index'), 2, '您没有权限对该预算单进行操作!');
                }
                
                //审批信息
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($budget['bud_no'], 1);
                //审批已经开始必须进入详情页
                if($approve['aprv_result1']>0){
                    redirect(U("Budget/info")."?id=".$id);
                }
                
                //项目利润率
                $budget['profit_percent'] = round(($budget['budget_proj_profit']/$budget['budget_cntr_income'])*100,2)."%";
        
                //获取第三方费用列表
                $third_party_arr = $expenses_model->final_list("bud_id=$id and type=1 and is_del=1");
                $third_party = $third_party_arr['list'];
                $budget['d_final_total'] = $third_party_arr['final_total'];
                
                //获取智源体系费用列表
                $system_party_arr = $expenses_model->final_list("bud_id=$id and type=2 and is_del=1");
                $system_party = $system_party_arr['list'];
                $budget['z_final_total'] = $system_party_arr['final_total'];
                
                //获取项目编号
                $proj = $proj_model->get_one($budget['proj_id']);
                if($proj){
                    $proj = $this->getProjUsers($proj);
                    $this->assign('proj', $proj);
                }
                
                //申请人
                $user_model = new \Home\Model\UserModel();
                $apply_user = $user_model->get_one($budget['crt_user_id']);
                $app_real_name = $apply_user['real_name'];
                
                //获取该用户涉及到的项目列表
                $projs = $proj_model->get_budget_list($lvl_id,$user_id,$budget['cust_id'],$budget['proj_id']);
                
                //是否显示选择审批人功能
                $is_show_app = check_is_show_app($budget['crt_user_id'], $user_id, $lvl_id, $approve, $budget['crt_time']);
                
                //审批人列表 大于申请人级别
                if($is_show_app == true){
                    $approve_users = getApproverList($budget['crt_user_id'], $user_id, $lvl_id, 1, $budget['crt_time'], $approve);
                }
                
                //是否重新发启按钮
                $is_restart = false;
                if($approve['result'] == -1 && ($lvl_id==1 || $budget['crt_user_id']==$user_id)){
                    $is_restart = true;
                }
                
                //审批进度条内容
                $approve_arr = getApprovers($approve, $budget['crt_user_id']);
                $this->assign('approve_arr', $approve_arr);
                $this->assign('budget', $budget);
                $this->assign('third_party', $third_party);
                $this->assign('system_party', $system_party);
                $this->assign('projs', $projs);
            }
        }else{
            if($lvl_id>8 && !in_array($user_id, array(40,42,45))){
                redirect(U('Budget:index'), 2, '您没有权限对该预算单进行操作!');
            }
            $is_show_app = true;
            $is_restart = false;
            //审批人列表
            $approve_users = getApproverList($user_id, $user_id, $lvl_id, 1, time());
        }
        
        //客户列表
        $customer_model = new \Home\Model\CustomerModel();
        $customers = $customer_model->get_lists("is_del=1", 0, 1000);
        
        $this->assign('user_id', $user_id);
        $this->assign('is_show_app', $is_show_app);
        $this->assign('is_restart', $is_restart);
        $this->assign('customers', $customers);
        $this->assign('app_real_name', $app_real_name);
        $this->assign('approve_users', $approve_users);
        $this->display('Budget:add');
    }
    
    /*
     *  信息页
     **/
    public function infoAction(){
        $proj_model = new \Home\Model\ProjectModel();
        $budget_model = new \Home\Model\BudgetModel();
        $expenses_model = new \Home\Model\ExpensesModel();
        $user_model = new \Home\Model\UserModel();
        $id = I('get.id');
        $user_id = $this->user_id;
        $real_name = session('real_name');
        $lvl_id = session('lvl_id');
        if($id){
            $id = intval($id);
            $budget = $budget_model->get_one($id);
            if($budget){
                if($budget['crt_time']>get_c_date()){
                    //判断是否有权限进入
                    $is_projlist = checkInProjList($user_id,$lvl_id,$budget['proj_id']);
                    if($budget['crt_user_id'] != $user_id && !checkInApproverList($user_id,$budget['bud_no'],1) && !$is_projlist){
                        redirect(U('Budget:index'), 2, '您没有权限对该预算单进行操作!');
                    }
                    
                    //项目利润率
                    $budget['profit_percent'] = round(($budget['budget_proj_profit']/$budget['budget_cntr_income'])*100,2)."%";
            
                    //获取第三方费用列表
                    $third_party_arr = $expenses_model->final_list("bud_id=$id and type=1 and is_del=1");
                    $third_party = $third_party_arr['list'];
                    $budget['d_final_total'] = $third_party_arr['final_total'];
                    
                    //获取智源体系费用列表
                    $system_party_arr = $expenses_model->final_list("bud_id=$id and type=2 and is_del=1");
                    $system_party = $system_party_arr['list'];
                    $budget['z_final_total'] = $system_party_arr['final_total'];
            
                    //获取项目信息
                    $proj = $proj_model->get_one($budget['proj_id']);
                    if($proj){
                        $proj_mgr_id = $proj['proj_mgr'];
                        $proj = $this->getProjUsers($proj);
                        $this->assign('proj', $proj);
                    }
            
                    //获取该用户涉及到的项目列表
                    $projs = $proj_model->get_list_byuserid($lvl_id,$user_id,$budget['cust_id']);
                    
                    //客户列表
                    $customer_model = new \Home\Model\CustomerModel();
                    $customers = $customer_model->get_lists("is_del=1", 0, 1000);
                    
                    //申请人
                    $apply_user = $user_model->get_one($budget['crt_user_id']);
                    
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($budget['bud_no'], 1);
                    $approve_arr = getApprovers($approve, $budget['crt_user_id']);
                    
                    
                    //是否显示选择审批人功能
                    $is_show_app = check_is_show_app($budget['crt_user_id'], $user_id, $lvl_id, $approve, $budget['crt_time']);
                    
                    //审批人列表 大于申请人级别
                    if($is_show_app == true && !check_permission_left('Agree_approve', 'index')){
                        $approve_users = getApproverList($apply_user['user_id'], $user_id, $lvl_id, 1, $budget['crt_time'], $approve);
                        $this->assign('approve_users', $approve_users);
                    }
                    
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $budget['crt_user_id']==$user_id)){
                        $is_restart = true;
                    }
                    
                    //是否提交决算信息按钮
                    $is_final = false;
                    if($approve['result'] == 1 && ($lvl_id==3 || $lvl_id==5 || $proj_mgr_id==$user_id)){
                        $is_final = true;
                    }
                    
                    //级别大于5的可以看的信息
                    if($lvl_id<5 && $budget['result']==1){
                        $borrow_model = new \Home\Model\BorrowModel();
                        $rec_model = new \Home\Model\RecoupedModel();
                        $refund_model = new \Home\Model\RefundModel();
                        $borrows = $borrow_model->get_list("bud_id={$budget['bud_id']} and is_del=1");
                        $depart_model = new \Home\Model\DepartModel();
                        foreach ($borrows as &$row) {
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
                        
                        $recoupeds = $rec_model->get_list("bud_id={$budget['bud_id']} and is_del=1");
                        foreach ($recoupeds as &$row) {
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
                            
                            $row['borrow_no'] = "--";
                            if($row['borrow_id']){
                                $borrow = $borrow_model->get_one($row['borrow_id']);
                                $row['borrow_no'] = $borrow['borrow_no'];
                            }
                            
                            $row['result_info'] = '等待审批';
                            if($row['result'] == 1){
                                $row['result_info'] = '流程已完成';
                            }elseif($row['result'] == -1){
                                $row['result_info'] = '不同意';
                            }
                        }
                        
                        $this->assign('borrows', $borrows);
                        $this->assign('recoupeds', $recoupeds);
                    }
                    
                    $this->assign('lvl_id', $lvl_id);
                    $this->assign('is_final', $is_final);
                    $this->assign('approve_arr', $approve_arr);
                    $this->assign('apply_user', $apply_user);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('budget', $budget);
                    $this->assign('third_party', $third_party);
                    $this->assign('system_party', $system_party);
                    $this->assign('projs', $projs);
                    $this->assign('customers', $customers);
                    $this->assign('real_name', $real_name);
                    $this->assign('user_id', $user_id);
                    $this->display('Budget:info');
                }else{
                    //判断是否有权限进入
                    $is_projlist = checkInProjList($user_id,$lvl_id,$budget['proj_id']);
                    if($budget['crt_user_id'] != $user_id && !checkInApproverList($user_id,$budget['bud_no'],1) && !$is_projlist){
                        redirect(U('Budget:index'), 2, '您没有权限对该预算单进行操作!');
                    }
                    
                    //项目利润率
                    $budget['profit_percent'] = round(($budget['budget_proj_profit']/$budget['budget_cntr_income'])*100,2)."%";
                    
                    //获取第三方费用列表
                    $third_party_arr = $expenses_model->final_list("bud_id=$id and type=1 and is_del=1");
                    $third_party = $third_party_arr['list'];
                    $budget['d_final_total'] = $third_party_arr['final_total'];
                    
                    //获取智源体系费用列表
                    $system_party_arr = $expenses_model->final_list("bud_id=$id and type=2 and is_del=1");
                    $system_party = $system_party_arr['list'];
                    $budget['z_final_total'] = $system_party_arr['final_total'];
                    
                    //获取项目信息
                    $proj = $proj_model->get_one($budget['proj_id']);
                    if($proj){
                        $proj_mgr_id = $proj['proj_mgr'];
                        $proj = $this->getProjUsers($proj);
                        $this->assign('proj', $proj);
                    }
                    
                    //获取该用户涉及到的项目列表
                    $projs = $proj_model->get_list_byuserid($lvl_id,$user_id,$budget['cust_id']);
                    
                    //客户列表
                    $customer_model = new \Home\Model\CustomerModel();
                    $customers = $customer_model->get_lists("is_del=1", 0, 1000);
                    
                    //申请人
                    $apply_user = $user_model->get_one($budget['crt_user_id']);
                    
                    //审批信息
                    $app_model = new \Home\Model\ApproveModel();
                    $approve = $app_model->getApproveSchedule($budget['bud_no'], 1);
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
                    $is_show_app = check_is_show_app($budget['crt_user_id'], $user_id, $lvl_id, $approve, $budget['crt_time']);
                    
                    //审批人列表 大于申请人级别
                    if($is_show_app == true){
                        $approve_users = getApproverList($apply_user['user_id'], $user_id, $lvl_id, 1, $budget['crt_time'], $approve);
                        $this->assign('approve_users', $approve_users);
                    }
                    
                    //是否重新发启按钮
                    $is_restart = false;
                    if($approve['result'] == -1 && ($lvl_id==1 || $budget['crt_user_id']==$user_id)){
                        $is_restart = true;
                    }
                    
                    //是否提交决算信息按钮
                    $is_final = false;
                    if($approve['result'] == 1 && ($lvl_id==3 || $lvl_id==5 || $proj_mgr_id==$user_id)){
                        $is_final = true;
                    }
                    
                    //级别大于5的可以看的信息
                    if($lvl_id<5 && $budget['result']==1){
                        $borrow_model = new \Home\Model\BorrowModel();
                        $rec_model = new \Home\Model\RecoupedModel();
                        $refund_model = new \Home\Model\RefundModel();
                        $borrows = $borrow_model->get_list("bud_id={$budget['bud_id']} and is_del=1");
                        $depart_model = new \Home\Model\DepartModel();
                        foreach ($borrows as &$row) {
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
                    
                        $recoupeds = $rec_model->get_list("bud_id={$budget['bud_id']} and is_del=1");
                        foreach ($recoupeds as &$row) {
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
                    
                            $row['borrow_no'] = "--";
                            if($row['borrow_id']){
                                $borrow = $borrow_model->get_one($row['borrow_id']);
                                $row['borrow_no'] = $borrow['borrow_no'];
                            }
                    
                            $row['result_info'] = '等待审批';
                            if($row['result'] == 1){
                                $row['result_info'] = '流程已完成';
                            }elseif($row['result'] == -1){
                                $row['result_info'] = '不同意';
                            }
                        }
                    
                        $this->assign('borrows', $borrows);
                        $this->assign('recoupeds', $recoupeds);
                    }
                    
                    $this->assign('lvl_id', $lvl_id);
                    $this->assign('is_final', $is_final);
                    $this->assign('schedule', $approve);
                    $this->assign('apply_user', $apply_user);
                    $this->assign('is_show_app', $is_show_app);
                    $this->assign('is_restart', $is_restart);
                    $this->assign('budget', $budget);
                    $this->assign('third_party', $third_party);
                    $this->assign('system_party', $system_party);
                    $this->assign('projs', $projs);
                    $this->assign('customers', $customers);
                    $this->assign('real_name', $real_name);
                    $this->assign('user_id', $user_id);
                    $this->display('Budget:info_old');
                }
            }
        }
    }
    
    /*
     *  决算提交
     **/
    public function finaleditAction(){
        if(IS_POST){
            $id = I("post.bud_id");
            $data['final_cntr_income'] = I('post.final_cntr_income');
            $data['final_point'] = !I('post.final_point') ? '0' : I('post.final_point');
            //$data['final_proj_profit'] = I('post.final_proj_profit');
            $data['d_final_total'] = I('post.d_final_total');
            $data['z_final_total'] = I('post.z_final_total');
            
            $data['final_proj_profit'] = sprintf("%.2f",(doubleval($data['final_cntr_income']*(1-0.0634))-doubleval($data['d_final_total'])-doubleval($data['z_final_total'])));
            
            $budget_model = new \Home\Model\BudgetModel();
            $id = intval($id);
            $budget = $budget_model->get_one($id);
            if($budget && $budget['result'] == 1){
                $ret = $budget_model->update_data($data, "bud_id = $id");            //更新预算单表
                if($ret){
                    $this->ajaxReturn(array('status'=>1));
                }
                $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
            }
        }
        
    }
    
    /*
     *  审批结果提交
     **/
    public function operateAction(){
        $id = I("post.bud_id");
        $num = I("post.num");
        $result = I("post.result");
        
        $budget_model = new \Home\Model\BudgetModel();
        $budget = $budget_model->get_one($id);
        if($budget['crt_time']>get_c_date()){
            $cur_approver_id = I("post.cur_approver_id");
            $lvl_id = session('lvl_id');
            if($result == 1 && $lvl_id != 2 && $lvl_id != 3 && $cur_approver_id==0){          //同意操作，并且不是总经理和出纳，需要判断是否选择下一步审批人
                $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            }
            
            if($budget){
                $app_model = new \Home\Model\ApproveModel();
                if($app_model->getApproveSchedule($budget['bud_no'] ,1)){
                    $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                    if($lvl_id == 3 || $result == -1){
                        $data['result'] = $result;
                    }
                    
                    if(I("post.option")) $data['aprv_opinion'.$num] = I("post.option");
                    
                    $ret = $app_model->update_data($data, "aprv_no='{$budget['bud_no']}' and aprv_type=1");
                    if($ret){
                        $user_id = $this->user_id;
                        $lvl_id = session('lvl_id');
                        if($lvl_id != 3 && $result == 1){
                            if($lvl_id == 2){
                                choose_approval_user($id,1,get_cw_userid(),$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给出纳
                            }else{
                                choose_approval_user($id,1,$cur_approver_id,$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给peter
                            }
                        }
                        
                        if($lvl_id == 3 || $result == -1){
                            $budget_model->update_data(array('result'=>$result), "bud_id = {$budget['bud_id']}");
                            $content = "您的一个预算单审批已通过，请注意查看。";
                            if($result == -1){
                                $content = "您的一个预算单审批未获通过，请注意查看。";
                            }
                            send_email_approve($budget['crt_user_id'], '预算单审批通知', $content."单号：{$budget['bud_no']}。", U("Budget/info",array('id'=>$id)));           //发邮件
                        }
                        logrecords("update", $app_model->get_tablename());          //日志
                        $this->ajaxReturn(array('status'=>1));
                    }
                }
            }
        }else{
            if($budget){
                $app_model = new \Home\Model\ApproveModel();
                if($app_model->getApproveSchedule($budget['bud_no'] ,1)){
                    $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                    if($num == 4 || $result == -1){
                        $data['result'] = $result;
                        if($result == -1){
                            $data['aprv_opinion'.$num] = I("post.option");
                        }
                    }
                    $ret = $app_model->update_data($data, "aprv_no='{$budget['bud_no']}' and aprv_type=1");
                    if($ret){
                        $user_id = $this->user_id;
                        $lvl_id = session('lvl_id');
                        if($lvl_id == 2 && $result == 1){
                            choose_approval_user($id,1,get_cw_userid(),$lvl_id,$user_id);                    //总经理直接进行提交下个审批人的操作，给peter
                        }
                        if($num == 4 || $result == -1){
                            $budget_model->update_data(array('result'=>$result), "bud_id = {$budget['bud_id']}");
                            $content = "您的一个预算单审批已通过，请注意查看。";
                            if($result == -1){
                                $content = "您的一个预算单审批未获通过，请注意查看。";
                            }
                            send_email_approve($budget['crt_user_id'], '预算单审批通知', $content."单号：{$budget['bud_no']}。", U("Budget/info",array('id'=>$id)));           //发邮件
                        }
                        logrecords("update", $app_model->get_tablename());          //日志
                        $this->ajaxReturn(array('status'=>1));
                    }
                }
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    /*
     *  重新发启审核
     **/
    public function backAction(){
        if(IS_POST){
            $lvl_id = session('lvl_id');
            $user_id = $this->user_id;
            if($lvl_id==2){
                $app_model = new \Home\Model\ApproveModel();
                $obj_id = I('post.obj_id');
                
                //借款单
                $obj_model = new \Home\Model\BudgetModel();
                $where = "bud_id = '$obj_id'";
                $obj = $obj_model->get_onebywhere($where);
                if($obj){
                    $aprv_no = $obj['bud_no'];
                }
                $approve = $app_model->getApproveSchedule($aprv_no, 1);
                
                $obj_model->startTrans();
                $ret = $obj_model->update_data(array('cur_approver_id'=>$user_id,'result'=>0), $where);
                for ($i = 1; $i < 6; $i++) {
                    if($approve["aprv_user_id".$i] == get_zjl_userid()){
                        break;
                    }
                }
                
                $ret1 = $app_model->update_data(array('aprv_user_id'.($i+1) => 0, 'aprv_result'.$i => 0, 'result'=>0), "aprv_no='$aprv_no' and aprv_type=1");
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
        $id = I("post.bud_id");
        $cur_approver_id = I("post.cur_approver_id");
        $budget_model = new \Home\Model\BudgetModel();
        $budget = $budget_model->get_one($id);
        if($budget){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($budget['bud_no'], 1);
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
            $budget_model->startTrans();     //开启事务
            $ret = $budget_model->update_data(array('cur_approver_id'=>$cur_approver_id), "bud_id=".$id);
            if($ret){
                $ret1 = $app_model->updateApproved($approve, $cur_approver_id, $lvl_id, $user_id);
                if($ret && $ret1){
                    logrecords("update", $app_model->get_tablename());          //日志
                    $budget_model->commit();
                    send_email_approve($cur_approver_id, '预算单审批通知', "您有一个预算单审批请求！",U("Budget/info",array('id'=>$id)));  //发邮件
                    $this->ajaxReturn(array('status'=>1));
                }else{
                    $budget_model->rollback();
                }
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    /*
     *  重新发启审核
     **/
    public function restartAction(){
        $id = I('post.bud_id');
        $budget_model = new \Home\Model\BudgetModel();
        $budget = $budget_model->get_one($id);
        if($budget){
            $app_model = new \Home\Model\ApproveModel();
            $approve = $app_model->getApproveSchedule($budget['bud_no'], 1);
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
                    $budget_model->update_data(array('cur_approver_id'=>0,'result'=>0), "bud_id=$id");
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
            $id = I('post.bud_id');
            $budget_model = new \Home\Model\BudgetModel();
            $budget = $budget_model->get_one($id);
            if($budget){
                $app_model = new \Home\Model\ApproveModel();
                $approve = $app_model->getApproveSchedule($budget['bud_no'], 1);
                if(!check_permission_left('financial_approve', 'finance')){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可删除！'));
                    }
                }
        
                $ret = $budget_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "bud_id = $id");
                if($ret){
                    $ret1 = $app_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "aprv_no='{$budget['bud_no']}' and aprv_type=1");
                    logrecords('delete', $budget_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'预算单不存在！'));
        }
    }
    
    /*
     *  开票统计
     **/
    public function add_poAction(){
        $proj_model = new \Home\Model\ProjectModel();
        $budget_model = new \Home\Model\BudgetModel();
        $po_model = new \Home\Model\BudgetpoModel();
    
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
    
        if(IS_POST){
            $bud_id = I("post.bud_id");
            $d_str = I("post.d_str");
    
            //判断该项目是否已经新建预算单
            $budget = $budget_model->get_one($bud_id);
            if(!$budget || $budget['result']!=1){
                $this->ajaxReturn(array('status'=>0, 'msg'=>'该预算单不存在或者未审批完成！'));
            }
    
            //新增、更新数据
            $budget_model->startTrans();     //开启事务
            if($po_model->get_onebywhere("bud_id=$bud_id and is_del=1")){
                $ret = $po_model->update_data(array('is_del'=>-1), "bud_id = $bud_id");
            }else{
                $ret = true;
            }
            if($ret){
                $d_str = str_replace("&amp;", "&", $d_str);
                $d_array = explode(";", $d_str);
                $po_money = 0;
                $kp_money = 0;
                $back_money = 0;
                $issubmit = true;
                foreach ($d_array as $d_row) {
                    $d_arr = array();
                    $data = array();
                    if($d_row && $d_arr = explode("^", $d_row)){
                        $data['bud_id'] = $bud_id;
                        if($d_arr[0]) $data['po_no'] = $d_arr[0];
                        if($d_arr[1]) $data['po_money'] = $d_arr[1];
                        if($d_arr[2]) $data['express_no'] = $d_arr[2];
                        if($d_arr[3]) $data['kp_money'] = $d_arr[3];
                        if($d_arr[4]) $data['kp_date'] = $d_arr[4];
                        if($d_arr[5]) $data['back_money'] = $d_arr[5];
                        if($d_arr[6]) $data['back_date'] = $d_arr[6];
                        $po_money += round($data['po_money'],2);
                        $kp_money += round($data['kp_money'],2);
                        $back_money += round($data['back_money'],2);
                        $ret1 = $po_model->insert_data($data);
                        if(!$ret1){
                            $issubmit = false;
                            break;
                        }
                    }
                }
                if($issubmit == true){
                    $ret2 = $budget_model->update_data(array('po_money'=>$po_money, 'kp_money'=>$kp_money, 'back_money'=>$back_money), "bud_id=$bud_id");
                    if($ret2){
                        $budget_model->commit();
                        $this->ajaxReturn(array('status'=>1));
                    }
                }
            }
            $budget_model->rollback();        //回滚
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    
        $bud_id = I('get.bud_id');
        if($bud_id){
            $id = intval($bud_id);
            $budget = $budget_model->get_one($id);
            if($budget){
                //判断是否有权限进行编辑操作
                if($budget['crt_user_id'] != $user_id && $lvl_id>4){
                    redirect(U('Budget:index'), 2, '您没有权限对该预算单进行操作!');
                }
    
                //获取po列表
                $po_list = $po_model->get_list("bud_id=$id and is_del=1");
    
                $role = 0;             //用户权限1为修改po单号、po金额和快递单号，2为修改其他,3为全部可以修改
                if($budget['crt_user_id'] == $user_id){
                    $role = 1;
                }elseif($lvl_id==3){
                    $role = 2;
                }elseif($lvl_id<3){
                    $role = 3;
                }
                
                $this->assign('role', $role);
                $this->assign('budget', $budget);
                $this->assign('po_list', $po_list);
                $this->display('Budget:add_po');
            }
        }
    }
    
    public function mj_importAction(){
        if(IS_POST){
            setlocale(LC_ALL,'zh_CN');
            $file = $_FILES['file']['tmp_name'];
            $file = iconv("utf-8", "gb2312", $file);   //转码
            if(empty($file) OR !file_exists($file)) {
                die('file not exists!');
            }
            $proj_id = I("post.proj_id");
            if(!$proj_id) $this->ajaxReturn(array("status"=>0, 'msg'=>"请选择项目"));
            
            $expenses = M("expenses")->where(array("proj_id"=>$proj_id, "cost_id"=>90, "is_del"=>1))->find();
            if(!$expenses) $this->ajaxReturn(array("status"=>0, 'msg'=>"该项目预算单中没有媒介采买的款项"));
            
            vendor("PHPExcel.PHPExcel");
            $objRead = new \PHPExcel_Reader_Excel2007();  //建立reader对象
             
            if(!$objRead->canRead($file)){
                $objRead = new PHPExcel_Reader_Excel5();
                if(!$objRead->canRead($file)){
                    die('No Excel!');
                }
            }
            
            $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N');
            $key = array('B'=>'platform', 'C'=>'mj_type', 'D'=>'mj_name', 'E'=>'intro', 'F'=>'trench', 'G'=>'cooperation', 'H'=>'execute_time', 'I'=>'payment_time', 'J'=>'payment_stage', 'K'=>'payment', 'L'=>'contract_type', 'M'=>'invoice');
            
            $obj = $objRead->load($file);  //建立excel对象
            $currSheet = $obj->getSheet(0);   //获取指定的sheet表
            $columnH = $currSheet->getHighestColumn();   //取得最大的列号
            $columnCnt = array_search($columnH, $cellName);
            $rowCnt = $currSheet->getHighestRow();   //获取总行数
            
            $data = array();
            for($_row=2; $_row<=$rowCnt; $_row++){  //读取内容
                for($_column=0; $_column<=$columnCnt; $_column++){
                    $cellId = $cellName[$_column].$_row;
                    $cellValue = $currSheet->getCell($cellId)->getValue();
                    //$cellValue = $currSheet->getCell($cellId)->getCalculatedValue();  #获取公式计算的值
                    if($cellValue instanceof PHPExcel_RichText){   //富文本转换字符串
                        $cellValue = $cellValue->__toString();
                    }
            
                    if($cellName[$_column] == "H" || $cellName[$_column] == "I"){
                        $cellValue = gmdate("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($cellValue));
                    }
            
                    if(array_key_exists($cellName[$_column], $key)){
                        if($key[$cellName[$_column]] == "mj_name"){
                            $mjsupplier = M("mjsupplier")->where(array('sup_short_name'=>$cellValue,'is_del'=>1))->find();
                            if(!$mjsupplier){
                                $this->ajaxReturn(array("status"=>0,'msg'=>$cellValue."不在媒介库中"));
                            }
                        }
                        $data[$_row][$key[$cellName[$_column]]] = $cellValue;
                    }
                }
            }
            
            foreach ($data as $row) {
                $data_i = $row;
                $data_i['proj_id'] = $proj_id;
                $data_i['cdate'] = time();
                M('recouped_mj_expenses')->add($data_i);
            }
            $this->ajaxReturn(array("status"=>1));
        }
        
        
        
        $proj_id = I("get.proj_id");
        $where = "proj_id=".$proj_id." and is_del=1";
        //日期检索
        $start_date = I('get.start_date','','addslashes');
        $end_date = I('get.end_date','','addslashes');
        if($start_date){
            $where .= " and payment_time>='$start_date'";
        }
        if($end_date){
            $where .= " and payment_time<='$end_date'";
        }
        $list = M('recouped_mj_expenses')->where($where)->select();
        $app_model = new \Home\Model\ApproveModel();
        $user_model = new \Home\Model\UserModel();
        foreach ($list as &$row) {
            if($row['is_use']){
                $recouped = M("recouped")->where("mj_ex_id=".$row['id'])->find();
                $row['result_info'] = '审核中';
                if($recouped['result'] == 1){
                    $row['result_info'] = '通过';
                }elseif($recouped['result'] == -1){
                    $row['result_info'] = '未通过';
                }
                
                //获取审批状态
                $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
                for ($i = 1; $i < 7; $i++) {
                    if($approve['aprv_user_id'.$i] == $recouped['cur_approver_id']){
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
            }else{
                $row['result_info'] = '未使用';
            }
        }
        
        if(!check_permission_left("Mjsupplier", "export_data")){
            redirect(U('Budget:index'), 2, '您没有权限进行操作!');
        }
        
        $this->assign("list", $list);
        $this->assign("proj_id", $proj_id);
        $this->display("mj_import");
    }
    
    /**
     * 导出信息
     */
    public function mj_exportAction(){
        if(!check_permission_left("Mjsupplier", "export_data")){
            redirect(U('Budget:index'), 2, '您没有权限进行操作!');
        }
        $proj_id = I("get.proj_id");
        $where = "proj_id=".$proj_id." and is_del=1";
        //日期检索
        $start_date = I('get.start_date','','addslashes');
        $end_date = I('get.end_date','','addslashes');
        if($start_date){
            $where .= " and payment_time>='$start_date'";
        }
        if($end_date){
            $where .= " and payment_time<='$end_date'";
        }
        $list = M('recouped_mj_expenses')->where($where)->select();
        $app_model = new \Home\Model\ApproveModel();
        $user_model = new \Home\Model\UserModel();
    
        $header1 = array("合作平台","媒介分类","媒介名称","简介","来源","合作内容","执行时间","付款时间","付款阶段","付款金额","签约方式","税票","OA进度");
        $header = implode("\t",array_values($header1));
        $header .= "\t\n";
        $content .= $header;
        ob_end_clean();
        header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        header("X-DNS-Prefetch-Control: off");
        header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=budget.xls");
        $content=iconv("UTF-8","GBK//IGNORE",$content) ;
        echo $content;
        
        foreach ($list as &$row) {
            $tmp_array = array();
            $tmp_array["platform"] = $row['platform'];
            $tmp_array["mj_type"] = $row['mj_type'];
            $tmp_array["mj_name"] = $row['mj_name'];
            $tmp_array["intro"] = $row['intro'];
            $tmp_array["trench"] = $row['trench'];
            $tmp_array["cooperation"] = $row['cooperation'];
            
            $tmp_array["execute_time"] = $row['execute_time'];
            $tmp_array["payment_time"] = $row['payment_time'];
            $tmp_array["payment_stage"] = $row['payment_stage'];
            $tmp_array["payment"] = $row['payment'];
            $tmp_array["contract_type"] = $row['contract_type'];
            $tmp_array["invoice"] = $row['invoice'];
            
            if($row['is_use']){
                $recouped = M("recouped")->where("mj_ex_id=".$row['id'])->find();
                $row['result_info'] = '审核中';
                if($recouped['result'] == 1){
                    $row['result_info'] = '通过';
                }elseif($recouped['result'] == -1){
                    $row['result_info'] = '未通过';
                }
        
                //获取审批状态
                $approve = $app_model->getApproveSchedule($recouped['rec_no'], 3);
                for ($i = 1; $i < 7; $i++) {
                    if($approve['aprv_user_id'.$i] == $recouped['cur_approver_id']){
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
            }else{
                $row['result_info'] = '未使用';
            }
            $tmp_array["result_info"] = $row['result_info'];
            
            $new_arr = array();
            $content = "";
            foreach ($tmp_array as $key => $value)
            {
                array_push($new_arr, trim($value));
            }
            
            $line = implode("\t",$new_arr);
            $line .= "\t\n";
            $content .= $line;
            
            $content=@iconv("UTF-8","GBK//IGNORE",$content) ;
            echo $content;
        }
    }
    
    public function mj_deleteAction(){
        $id = I('post.id');
        $mj_expenses = M("recouped_mj_expenses")->where(array('id'=>$id))->find();
        if($mj_expenses){
            if($mj_expenses['is_use'] == 1){
                $this->ajaxReturn(array('status'=>0, 'msg'=>'已被使用，不可删除！'));
            }
        
            $ret = M("recouped_mj_expenses")->where(array('id'=>$id))->save(array('is_del'=>-1));
            if($ret){
                $this->ajaxReturn(array('status'=>1));
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'单据不存在！'));
    }
    
    public function getprojsAction(){
        $cust_id = I('post.cust_id');
        $proj_id = I('post.proj_id');
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        $proj_model = new \Home\Model\ProjectModel();
        $projs = $proj_model->get_budget_list($lvl_id,$user_id, $cust_id, $proj_id);
        if($projs){
            $this->ajaxReturn(array('status'=>1, 'projs'=>$projs));
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'项目不存在'));
    }
    
    public function getprojinfoAction(){
        $id = I('post.proj_id');
        $proj_model = new \Home\Model\ProjectModel();
        $proj = $proj_model->get_one($id);
        if($proj){
            $proj = $this->getProjUsers($proj);
            $this->ajaxReturn(array('status'=>1, 'proj'=>$proj));
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
    
    //财务修改预算相关信息
    public function budget_editAction(){
        if(IS_POST && check_permission_left('financial_approve', 'finance')){
            $data['end_time'] = I('post.end_time');
            if($data['end_time']){
                $data['end_time'] = strtotime($data['end_time']);
            }
            $id = I("post.bud_id");
            if($id){
                $ret = D('Budget')->update_data($data, "bud_id = $id");            //更新预算单表
                if($ret){
                    $this->ajaxReturn(array('status'=>1));
                }
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    /*
     *  获取借款单号
     **/
    public function getBorrowNo(){
        return "YSD".date('YmdHis').sprintf("%04d",rand(0, 1000));
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
}