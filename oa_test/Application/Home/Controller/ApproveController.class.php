<?php
/**
 * OA系统--审批任务
 */
namespace Home\Controller;
use Think\Page;
class ApproveController extends BaseController{
    
    public $check_access = true;
    public $head_title = '审批任务';
    
    public function indexAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\ApproveModel();
        $proj_model = new \Home\Model\ProjectModel();
        $user_model = new \Home\Model\UserModel();
        if(get_access_allvoucher($user_id)){                    //查看全部单据
            $where = "is_del = 1";
            if($lvl_id!=2){
                //获取所属公司下属的项目相关预算单
                $where .= " and aprv_user_id1 in (".$user_model->get_wherebycompanyid(session('company_id')).")";
            }
        }else{
            $where = "is_del = 1 and (aprv_user_id1 = {$this->user_id} or aprv_user_id2 = {$this->user_id} or aprv_user_id3 = {$this->user_id} or aprv_user_id4 = {$this->user_id} or aprv_user_id5 = {$this->user_id})";
        }
        
        //状态检索
        $way = I('get.way',2);
        if(is_numeric($way) && $way > 0){
            if($way == '1'){
                if($lvl_id == 1){
                    $where .= " and result=1";
                }else{
                    $where .= " and ((aprv_user_id1 = {$this->user_id} and aprv_result1!=0) or (aprv_user_id2 = {$this->user_id} and aprv_result2!=0) or (aprv_user_id3 = {$this->user_id} and aprv_result3!=0) or
                    (aprv_user_id4 = {$this->user_id} and aprv_result4!=0) or (aprv_user_id5 = {$this->user_id} and aprv_result5!=0))";
                }
            }else{
                if($lvl_id == 1){
                    $where .= " and result=0";
                }else{
                    $where .= " and ((aprv_user_id1 = {$this->user_id} and aprv_result1=0) or (aprv_user_id2 = {$this->user_id} and aprv_result2=0) or (aprv_user_id3 = {$this->user_id} and aprv_result3=0) or
                    (aprv_user_id4 = {$this->user_id} and aprv_result4=0) or (aprv_user_id5 = {$this->user_id} and aprv_result5=0))";
                }
            }
        }
        
        //类型检索
        $type = I('get.type',0);
        if(is_numeric($type) && $type > 0){
            $where .= " and aprv_type = $type";
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
            }
            if($pwhere){
                $where .= " and (aprv_no like '%$keyword%' or proj_id in ($pwhere))"; 
            }else{
                $where .= " and aprv_no like '%$keyword%'";
            }
        }
        
        $count = $app_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $app_model->where($where)->order('crt_time asc')->limit($page->firstRow.','.$page->listRows)->select();
        
        $borrow_model = new \Home\Model\BorrowModel();
        $proj_model = new \Home\Model\ProjectModel();
        $budget_model = new \Home\Model\BudgetModel();
        $refund_model = new \Home\Model\RefundModel();
        $pf_model = new \Home\Model\PfrecoupedModel();
        $recouped_model = new \Home\Model\RecoupedModel();
        $modify_model = new \Home\Model\ModifybudgetModel();
        foreach ($list as &$row) {
            $row['aprv_type_name'] = array_key_exists($row['aprv_type'], $app_model->_aprv_type) ? $app_model->_aprv_type[$row['aprv_type']] : '--';
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            $apply_userid = 0;
            $proj_id = 0;
            $obj_id = 0;
            if($row['aprv_type'] == 1){                     //预算单
                $budget = $budget_model->get_onebywhere("bud_no = '{$row['aprv_no']}'");
                $apply_userid = $budget['crt_user_id'];
                $proj_id = $budget['proj_id'];
                $obj_id = $budget['bud_id'];
            }elseif($row['aprv_type'] == 2){                //借款单
                $borrow = $borrow_model->get_onebywhere("borrow_no = '{$row['aprv_no']}'");
                $apply_userid = $borrow['user_id'];
                $proj_id = $borrow['proj_id'];
                $obj_id = $borrow['borrow_id'];
            }elseif($row['aprv_type'] == 3){                //报销单
                $recouped = $recouped_model->get_onebywhere("rec_no = '{$row['aprv_no']}'");
                $apply_userid = $recouped['user_id'];
                $proj_id = $recouped['proj_id'];
                $obj_id = $recouped['rec_id'];
            }elseif($row['aprv_type'] == 4){                //还款单
                $refund = $refund_model->get_onebywhere("ref_no = '{$row['aprv_no']}'");
                $apply_userid = $refund['crt_user_id'];
                $proj_id = $refund['proj_id'];
                $obj_id = $refund['ref_id'];
            }elseif($row['aprv_type'] == 5){                //预算变更单
                $modify = $modify_model->get_onebywhere("mod_no = '{$row['aprv_no']}'");
                $apply_userid = $modify['crt_user_id'];
                $budget =$budget_model->get_one($modify['bud_id']);
                $proj_id = $budget['proj_id'];
                $obj_id = $modify['mod_id'];
            }elseif($row['aprv_type'] == 6){                //预算变更单
                $recouped = $pf_model->get_onebywhere("pf_no = '{$row['aprv_no']}'");
                $apply_userid = $recouped['crt_user_id'];
                $obj_id = $recouped['pf_id'];
            }
            
            $proj = $proj_model->get_one($proj_id);               //项目
            $row['proj_name'] = $proj['proj_name'];
            $row['proj_no'] = $proj['proj_no'];
            
            $user = $user_model->get_one($apply_userid);               //申请人员
            $row['real_name'] = $user['real_name'];
            
            if($row['result']==1){                                    //管理员
                $row['result_info'] = '流程已完成';
            }else{
                for ($i = 1; $i < 6; $i++) {
                    if($row['aprv_result'.$i] == 0 && $row['aprv_user_id'.$i]!=0){
                        $row['result_info'] = '等待审批';
                        break;
                    }elseif($row['aprv_result'.$i] != 0 && $row['aprv_user_id'.($i+1)]==0){
                        if($row['aprv_result'.$i]==1){
                            $row['result_info'] = '同意';
                        }elseif($row['aprv_result'.$i]==-1){
                            $row['result_info'] = '不同意';
                        }
                        break;
                    }
                }
                $app_user = $user_model->get_one($row['aprv_user_id'.$i]);               //申请人员
                $row['result_info'] = $row['result_info']."（".$app_user['real_name']."）";
                
            }
            $row['obj_id'] = $obj_id;
        }
        $this->assign('way',$way);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('user_id', $this->user_id);
        $this->assign('lvl_id', $lvl_id);
        $this->assign('is_mobile',is_mobile());
        $this->display('Approve:index');
    }

    
    public function handleAction(){
        $borrow_model = new \Home\Model\BorrowModel();
        $proj_model = new \Home\Model\ProjectModel();
        $budget_model = new \Home\Model\BudgetModel();
        $refund_model = new \Home\Model\RefundModel();
        $app_model = new \Home\Model\ApproveModel();
        $list = $app_model->where("is_del=1 and proj_id=0")->order('crt_time asc')->limit('0,3000')->select();
        foreach ($list as $row) {
            if($row['aprv_type'] == 1){                     //预算单
                $budget = $budget_model->get_onebywhere("bud_no = '{$row['aprv_no']}'");
                $proj_id = $budget['proj_id'];
            }elseif($row['aprv_type'] == 2){                //借款单
                $borrow = $borrow_model->get_onebywhere("borrow_no = '{$row['aprv_no']}'");
                $proj_id = $borrow['proj_id'];
            }elseif($row['aprv_type'] == 3){                //报销单
                $recouped_model = new \Home\Model\RecoupedModel();
                $recouped = $recouped_model->get_onebywhere("rec_no = '{$row['aprv_no']}'");
                $proj_id = $recouped['proj_id'];
            }elseif($row['aprv_type'] == 4){                //还款单
                $refund = $refund_model->get_onebywhere("ref_no = '{$row['aprv_no']}'");
                $proj_id = $refund['proj_id'];
            }elseif($row['aprv_type'] == 5){                //预算变更单
                $modify_model = new \Home\Model\ModifybudgetModel();
                $modify = $modify_model->get_onebywhere("mod_no = '{$row['aprv_no']}'");
                $budget =$budget_model->get_one($modify['bud_id']);
                $proj_id = $budget['proj_id'];
            }
            
            if($proj_id){
                $app_model->where("aprv_id=".$row['aprv_id'])->save(array('proj_id'=>$proj_id));
            }
        }
    }
}