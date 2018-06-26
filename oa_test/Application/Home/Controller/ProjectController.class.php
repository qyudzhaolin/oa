<?php
/**
 * OA系统项目管理
 */
namespace Home\Controller;
use Think\Page;
class ProjectController extends BaseController{
    
    public $check_access = true;
    public $head_title = '项目管理';
    
    /*
     *  部门列表页
     **/
    public function indexAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $where = 'is_del = 1';
        $keyword = I('get.keyword','','addslashes');
        if($keyword){
            $where .= " and (proj_no like '%$keyword%' or proj_name like '%$keyword%')";
        }
        $proj_model = new \Home\Model\ProjectModel();
        if($lvl_id>2){
            if($user_id!=33){           
                $proj_where = $proj_model->get_wherebyuserid($lvl_id,$user_id);
                if($proj_where){
                    $where .= " and $proj_where";
                }
            }
        }
        
        $count = $proj_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $proj_model->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        $pusers_model = new \Home\Model\ProjectUsersModel();
        $user_model = new \Home\Model\UserModel();
        $file_model = new \Home\Model\FileModel();
        foreach ($list as &$row) {
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            $users = $pusers_model->get_user_list($row['proj_id']);
            foreach ($users as $user) {
                $row['users'] .= $user['real_name']."/";
            }
            $row['users'] = rtrim($row['users'],"/");
            $row['proj_mgr'] = $user_model->get_one($row['proj_mgr']);
            $row['crt_user'] = $user_model->get_one($row['crt_user_id']);
            $file = array();
            if($row['file_id']){
                $file = $file_model->get_one($row['file_id']);
                if($file){
                    $row['file_name'] = $file['file_name'];
                    $row['file_url'] = C('IMG_DOMAIN').$row['file_name'];
                }
            }
            $other_file = array();
            if($row['other_file_id']){
                $other_file = $file_model->get_one($row['other_file_id']);
                if($other_file){
                    $row['other_file_name'] = $other_file['file_name'];
                    $row['other_file_url'] = C('IMG_DOMAIN').$other_file['file_name'];
                }
            }
            
        }
        
        $is_proj_add = true;
        if($lvl_id>8 && !in_array($user_id, array(40,42,45))){
            $is_proj_add = false;
        }
        $this->assign('is_proj_add', $is_proj_add);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('user_id', $user_id);
        $this->assign('lvl_id', $lvl_id);
        $this->display('Project:index');
    }
    
    /*  
     *  新增、修改项目
     **/
    public function addAction(){
        $depart_par_id = session('depart_par_id');
        $proj_model = new \Home\Model\ProjectModel();
        $depart_model = new \Home\Model\DepartModel();
        $pusers_model = new \Home\Model\ProjectUsersModel();
        $user_model = new \Home\Model\UserModel();
        if(IS_POST){
            if(!$proj_model->create()){
                $this->ajaxReturn(array('status'=>0, 'msg'=>$proj_model->getError()));
            }else{
                $data['proj_name'] = I('post.proj_name','');
                $data['proj_mgr'] = I('post.proj_mgr',0);
                $data['cntr_val'] = I('post.cntr_val',0);
                $data['cust_id'] = I('post.cust_id',0);
                $data['file_id'] = I('post.file_id',0);
                $data['other_file_id'] = I('post.other_file_id',0);
                $proj_id = I('post.proj_id');
                $action = '';
                $user_ids = I('post.user_ids');
                $ids = explode(",", rtrim($user_ids,","));
                
                //验证项目编号
                $poj = $proj_model->where("proj_no='".I('post.proj_no')."' and is_del=1")->find();
                if($poj && $proj_id!=$poj['proj_id']){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'项目编号已存在！'));
                }
                
                if(isset($proj_id) && is_numeric($proj_id)){
                    $proj_id = intval($proj_id);
                    $data['mod_user_id'] = $this->user_id;          //操作用户Id
                    $ret = $proj_model->update_data($data, "proj_id = $proj_id");
                    $action = 'update';
                }else{
                    $data['proj_no'] = I('post.proj_no','');
                    $data['crt_user_id'] = $this->user_id;
                    $data['company_id'] = session('company_id');
                    $ret = $proj_model->insert_data($data);
                    $action = 'insert';
                }    
                if($ret){                                          //对项目人员进行操作
                    if($action == 'update'){
                        $pusers_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "proj_id = $proj_id");
                        $ret = $proj_id;
                    }
                    foreach ($ids as $id) {
                        $user = $user_model->get_one($id);
                        if($user){
                            $pusers_model->insert_data(array('proj_id'=>$ret, 'depart_id'=>$user['depart_id'], 'user_id'=>$id, 'crt_user_id'=>$this->user_id));
                        }
                    }
                    
                    logrecords($action, $proj_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $id = I('get.proj_id');
        if($id){
            $id = intval($id);
            $project = $proj_model->get_one($id);
            if($project){
                //判断是否有权限进行编辑操作
                if($project['crt_user_id'] != $this->user_id && $project['proj_mgr'] != $this->user_id && $lvl_id!=1 && $lvl_id!=2){
                    redirect(U('Project:index'), 2, '您没有权限对项目进行操作!');
                }
                
                $file_model = new \Home\Model\FileModel();
                if($project['file_id']){
                    $file = $file_model->get_one($project['file_id']);
                    if($file){
                        $project['file_name'] = $file['file_name'];
                    }
                }
                if($project['other_file_id']){
                    $other_file = $file_model->get_one($project['other_file_id']);
                    if($other_file){
                        $project['other_file_name'] = $other_file['file_name'];
                    }
                }
                
                $this->assign('project', $project);
            }
        }else{
            if($lvl_id>8 && !in_array($user_id, array(40,42,45))){
                redirect(U('Project:index'), 2, '您没有权限对项目进行操作!');
            }
        }
        
        //客户列表
        $customer_model = new \Home\Model\CustomerModel();
        $customers = $customer_model->get_lists("is_del=1", 0, 1000);
        
        //获取上级部门
        $departs = $depart_model->get_project_list($id);
        
        //项目经理人员列表
        $mgr_users = $user_model->get_mgr_users($depart_par_id);
        
        $this->assign('customers', $customers);
        $this->assign('departs', $departs);
        $this->assign('mgr_users', $mgr_users);        
        $this->display('Project:add');
    }
    
    public function infoAction(){
        $id = I('get.proj_id');
        if($id){
            $proj_model = new \Home\Model\ProjectModel();
            $depart_model = new \Home\Model\DepartModel();
            $pusers_model = new \Home\Model\ProjectUsersModel();
            $user_model = new \Home\Model\UserModel();
            $id = intval($id);
            $project = $proj_model->get_one($id);
            if($project){
                $lvl_id = session('lvl_id');
                $user_id = $this->user_id;
                
                //判断是否有权限进行查看操作
                if(!$proj_model->check_in_proj($lvl_id, $user_id, $id)){
                    redirect(U('Project:index'), 2, '您没有权限对项目进行操作!');
                }
                
                //客户列表
                $customer_model = new \Home\Model\CustomerModel();
                $customers = $customer_model->get_lists("is_del=1", 0, 1000);
                
                //获取上级部门
                $departs = $depart_model->get_project_list($id);
                
                //项目经理人员列表
                $mgr_users = $user_model->get_mgr_users();
                
                $this->assign('customers', $customers);
                $this->assign('departs', $departs);
                $this->assign('mgr_users', $mgr_users);
                $this->assign('project', $project);
                $this->display('Project:info');
            }
        }
    }
    
    /*
     *  删除项目操作
     **/
    public function deleteAction(){
        if(IS_POST){
            $id = I('post.proj_id');
            $proj_model = new \Home\Model\ProjectModel();
            $depart = $proj_model->get_one($id);
            if($depart){
                if($depart['depart_par_id'] == 0){
                    //判断是否生成预算单
                    $budget_model = M('Budget');
                    if($budget_model->where("proj_id=$id and is_del=1")->count()){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'该项目下已生成项目预算单，请确认！'));
                    }
                    
                    //判断是否生成借款单
                    $borrow_model = M('Borrow');
                    if($borrow_model->where("proj_id=$id and is_del=1")->count()){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'该项目下已生成借款单，请确认！'));
                    }
                    
                    //判断是否生成预算单
                    $recouped_model = M('Recouped');
                    if($recouped_model->where("proj_id=$id and is_del=1")->count()){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'该项目下已生成报销单，请确认！'));
                    }
                }
                
                $ret = $proj_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "proj_id = $id");
                if($ret){
                    logrecords('delete', $proj_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'项目不存在！'));
        }
        
    }
    
    
    /*
     *  结束项目操作
     **/
    public function overAction(){
        if(IS_POST){
            $id = I('post.proj_id');
            $proj_model = new \Home\Model\ProjectModel();
            $proj = $proj_model->get_one($id);
            if($proj){
                $ret = $proj_model->update_data(array('mod_user_id'=>$this->user_id, 'is_over'=>1), "proj_id = $id");
                if($ret){
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'项目不存在！'));
        }
    
    }

    /*
     *  获取部门下的人员
     **/
    public function getusersAction(){
        $depart_id = I("post.depart_id");
        $proj_id = I("post.proj_id",null);
        
        $depart_model = new \Home\Model\DepartModel();
        $users = $depart_model->get_depart_users($depart_id, $proj_id);
        
        $this->ajaxReturn(array('status'=>1, 'users'=>$users));
    }
}