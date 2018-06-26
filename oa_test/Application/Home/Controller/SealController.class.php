<?php
namespace Home\Controller;
use Think\Page;
use Think\Model;
class SealController extends BaseController{
    
    public $check_access = true;
    public $head_title = '用章申请';
    
    public function indexAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\SealApproveModel();
        $user_model = new \Home\Model\UserModel();
        $seal_model = new \Home\Model\SealModel();
        if($lvl_id<3){
            $where = "is_del = 1 and company_id=".session('company_id');
        }else{
            $where = "is_del = 1 and (crt_user_id = $user_id";
            
            //获取归该用户审批的报销单列表
            $awhere = $app_model->get_whereaprrove($user_id);
            $where .= " or se_id in ($awhere)";
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
                    $where .= " and crt_user_id in ($pwhere)";
                }
            }
        }
        $count = $seal_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $seal_model->where($where)->order("se_id desc")->limit($page->firstRow.','.$page->listRows)->select();
        $depart_model = new \Home\Model\DepartModel();
        
        foreach ($list as &$row) {
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
            
            if($row['result'] == 1){
                $row['result_info'] = '流程已完成';
                $row['action'] = "info";
            }else{
                //获取审批状态
                $approve = $app_model->getApproveSchedule($row['se_id']);
                for ($i = 1; $i < 4; $i++) {
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
                if($lvl_id == 1 || ($row['crt_user_id'] == $user_id && $i==1 && $approve['aprv_result1']!=1)){
                    $row['action'] = "add";
                }
            }
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('user_id', $user_id);
        $this->display('Seal:index');
    }
    
    /*  
     *  新增、修改用户页
     **/
    public function addAction(){
        $app_model = new \Home\Model\SealApproveModel();
        $user_model = new \Home\Model\UserModel();
        $seal_model = new \Home\Model\SealModel();
        
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        $depart_id = session('depart_id');
        
        if(IS_POST){
            $id = I("post.se_id");
            $data['s_type'] = I('post.s_type');
            $data['c_type'] = I('post.c_type');
            $data['se_type'] = I('post.se_type');
            $data['use_num'] = I('post.use_num');
            $data['out_date'] = I('post.out_date');
            $data['back_date'] = I('post.back_date');
            $data['reason'] = I('post.reason');
            $cur_approver_id = $data['cur_approver_id'] = I('post.cur_approver_id');
            
            //基础数据验证
            if(!$data['s_type']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择用章类型！'));
            if(!$data['c_type']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择所属公司！'));
            if(!$data['se_type']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择印章类型！'));
            if(!$data['reason']) $this->ajaxReturn(array('status'=>0, 'msg'=>'请填写使用原因！'));
            if(!$data['cur_approver_id'] && !$id) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            
            if($data['s_type'] == 1){
                $data['out_date'] == '';
                $data['back_date'] == '';
            }elseif($data['s_type'] == 2){
                $data['use_num'] == 0;
            }
            
            //判断是否可以更新
            $approve = null;
            $seal = null;
            $action = '';
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $seal = $seal_model->get_one($id);
                if(!$seal) $this->ajaxReturn(array('status'=>0, 'msg'=>'用章申请不存在！'));
                $approve = $app_model->getApproveSchedule($seal['se_id']);
                if(session('lvl_id')!=1){
                    if($app_model->getIsApproved($approve)){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                    }
                }
            }
            if($id){
                $ret = $seal_model->update_data($data, "se_id = $id");            
                if($ret){
                    if($seal['cur_approver_id'] != $data['cur_approver_id']){
                        $ret1 = $app_model->updateApproved($approve, $data['cur_approver_id'], $lvl_id, $user_id);
                    }else{
                        $ret1 = true;
                    }
                }
            }else{
                $data['crt_user_id'] = $this->user_id;
                $data['company_id'] = session("company_id");
                $ret = $seal_model->insert_data($data); 
                $ret1 = $app_model->insert_data(array('se_id'=>$ret,'aprv_user_id1'=>$data['cur_approver_id']));
            }  
            
            $msg = "保存失败！";                                      
            if($ret && $ret1){  
                $this->ajaxReturn(array('status'=>1));
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>$msg));
        }
        
        $id = I('get.id');
        $app_real_name = session('real_name');
        if($id){
            $id = intval($id);
            $seal = $seal_model->get_one($id);
            if($seal){
                //判断是否有权限进行编辑操作
                if($seal['crt_user_id'] != $this->user_id && session('lvl_id')!=1){
                    redirect(U('Seal:index'), 2, '您没有权限对该用章申请进行操作!');
                }
                
                //审批信息
                $approve = $app_model->getApproveSchedule($id);
                //审批已经开始必须进入详情页
                if($approve['aprv_result1']!=0){
                    redirect(U("Seal/info")."?id=".$id);
                }
                
                //申请人
                $user_model = new \Home\Model\UserModel();
                $apply_user = $user_model->get_one($seal['crt_user_id']);
                $app_real_name = $apply_user['real_name'];
                $depart_id = $apply_user['depart_id'];
                
                for ($i = 1; $i < 4; $i++) {
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
                    $is_show_app = check_is_show_app($seal['crt_user_id'], $user_id, $lvl_id, $approve, $seal['crt_time']);
                }
                
                
                //审批人列表
                if($is_show_app == true){
                    $approve_users = getSealApproverList($approve);
                }
                
                //审批进度条内容
                $approve_arr = getSealApprovers($approve, $seal['crt_user_id']);
                $this->assign('approve_arr', $approve_arr);
                $this->assign('seal', $seal);
            }
        }else{
            $is_show_app = true;
            $is_restart = false;
            $approve_users = getSealApproverList();
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
        $this->display('Seal:add');
    }
    
    /*
     *  信息页
     **/
    public function infoAction(){
        $id = I('get.id');
        if($id){
            $id = intval($id);
            $app_model = new \Home\Model\SealApproveModel();
            $user_model = new \Home\Model\UserModel();
            $seal_model = new \Home\Model\SealModel();
            $seal = $seal_model->get_one($id);
            if($seal){
                $user_id = $this->user_id;
                $lvl_id = session('lvl_id');
                $depart_id = session('depart_id');
                
                //判断是否有权限进入
                if($seal['crt_user_id']!=$user_id && !checkInSealApproverList($user_id)){         
                    redirect(U('Seal:index'), 2, '您没有权限对该用章申请进行操作!');
                }
                
                //申请人
                $apply_user = $user_model->get_one($seal['crt_user_id']);
                
                //申请人获取所在部门
                $depart_model = new \Home\Model\DepartModel();
                $depart = $depart_model->get_one($apply_user['depart_id']);
                
                //审批信息
                $approve = $app_model->getApproveSchedule($seal['se_id']);
                $approve_arr = getSealApprovers($approve, $seal['crt_user_id']);
                
                //是否显示选择审批人功能
                $is_show_app = check_is_show_app($seal['crt_user_id'], $user_id, $lvl_id, $approve, $seal['crt_time']);
                //审批人列表 大于申请人级别
                if($is_show_app == true){
                    $approve_users = getSealApproverList($approve);
                    $this->assign('approve_users', $approve_users);
                }
                
                //是否重新发启按钮
                $is_restart = false;
                if($approve['result'] == -1 && ($lvl_id==1 || $seal['crt_user_id']==$user_id)){
                    $is_restart = true;
                }
                
                $this->assign('seal', $seal);
                $this->assign('apply_user', $apply_user);
                $this->assign('depart', $depart);
                $this->assign('approve_arr', $approve_arr);
                $this->assign('is_show_app', $is_show_app);
                $this->assign('is_restart', $is_restart);
                $this->assign('user_id', $this->user_id);
                $this->assign('lvl_id', $lvl_id);
                $this->display('Seal:info');
            }
        }
    }
    
    /*
     *  审批结果提交
     **/
    public function operateAction(){
        $id = I("post.se_id");
        $num = I("post.num");
        $result = I("post.result");
        $cur_approver_id = I("post.cur_approver_id");
        $seal_model = new \Home\Model\SealModel();
        $seal = $seal_model->get_one($id);
        $user_id = $this->user_id;
        $lvl_id = session('lvl_id');
        
        if($result == 1 && $lvl_id != 2 &&  $lvl_id != 4 && $cur_approver_id==0){          
            $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
        }
        if($seal){
            $app_model = new \Home\Model\SealApproveModel();
            if($app_model->getApproveSchedule($id)){
                $data = array('aprv_result'.$num=>$result, 'aprv_time'.$num=>time());
                if($lvl_id == 4 || $result == -1){
                    $data['result'] = $result;
                }
                
                if(I("post.option")) $data['aprv_opinion'.$num] = I("post.option");
                
                $ret = $app_model->update_data($data, "se_id=$id");
                if($ret){
                    if($lvl_id != 4 && $result == 1){
                        if($lvl_id == 2){
                            $cur_approver_id = get_seal_cn_userid();
                        }
                        $ret1 = $seal_model->update_data(array("cur_approver_id"=>$cur_approver_id), "se_id=$id");
                        $approve = $app_model->getApproveSchedule($id);
                        $ret2 = $app_model->updateApproved($approve, $cur_approver_id, $lvl_id, $user_id);
                    }else{
                        $ret1 = $seal_model->update_data(array('result'=>$result), "se_id=$id");
                        $ret2 = true;
                    }
                    
                    if($ret1 && $ret2){
                        send_email_approve($seal['crt_user_id'], '用章申请审批通知', "你有一个用章申请需要审批", U("Seal/info",array('id'=>$id)));           //发邮件
                        $this->ajaxReturn(array('status'=>1));
                    }
                }
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    public function backAction(){
        if(IS_POST){
            $lvl_id = session('lvl_id');
            $user_id = $this->user_id;
            if($lvl_id==2){
                $app_model = new \Home\Model\SealApproveModel();
                $se_id = I('post.se_id');
    
                //借款单
                $obj_model = new \Home\Model\SealModel();
                $obj = $obj_model->get_one($se_id);
                $approve = $app_model->getApproveSchedule($se_id);
                $ret = $obj_model->update_data(array('cur_approver_id'=>$user_id,'result'=>0), "se_id=$se_id");
                for ($i = 1; $i < 5; $i++) {
                    if($approve["aprv_user_id".$i] == get_zjl_userid()){
                        break;
                    }
                }
                $ret1 = $app_model->update_data(array('aprv_user_id'.($i+1) => 0, 'aprv_result'.$i => 0, 'result'=>0), "se_id=$se_id");
                if($ret && $ret1){
                    $this->ajaxReturn(array('status'=>1));
                }
                $this->ajaxReturn(array('status'=>0,'操作失败！'));
            }
        }
    }
    
    /*
     *  删除用户操作
     **/
    public function deleteAction(){
        if(IS_POST){
            $id = I('post.se_id');
            $seal_model = new \Home\Model\SealModel();
            $seal = $seal_model->get_one($id);
            if($seal){
                $app_model = new \Home\Model\SealApproveModel();
                
                $ret = $seal_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "se_id=$id");
                $ret1 = $app_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "se_id=$id");
                if($ret && $ret1){
                    $this->ajaxReturn(array('status'=>1));
                }
                
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'用章申请不存在！'));
        }
        
    }
    
    /*
     *  重新发启审核
     **/
    public function restartAction(){
        $id = I('post.t_id');
        $seal_model = new \Home\Model\TbModel();
        $seal = $seal_model->get_one($id);
        
        if($seal){
            $app_model = new \Home\Model\TbApproveModel();
            $approve = $app_model->getApproveSchedule($id);
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
                $data['result'] = 0;
                $ret = $app_model->update_data($data, "aprv_id={$approve['aprv_id']}");
                $ret1 = $seal_model->update_data(array('cur_approver_id'=>0,'result'=>0), "t_id = $id");
                if($ret && $ret1){
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