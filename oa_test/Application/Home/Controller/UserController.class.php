<?php
/**
 * OA系统用户管理
 */
namespace Home\Controller;
use Think\Page;
class UserController extends BaseController{
    
    public $check_access = true;
    public $head_title = '用户库';
    
    public function indexAction(){
        $where = 'is_del = 1';
        $keyword = I('get.keyword','','addslashes');
        if($keyword){
            $where .= " and (real_name like '%$keyword%' or user_name like '%$keyword%')";
        }
        
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        if(!check_permission_byca('User', 'handle', $user_id, $lvl_id)){
            $where .= " and user_id=".$user_id;
        }else{
            $where .= " and company_id=".session('company_id');
            if($lvl_id!=1){
                $where .= " and lvl_id!=1";
            }
        }
        $user_model = new \Home\Model\UserModel();
        $count = $user_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $user_model->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        
        $depart_model = new \Home\Model\DepartModel();
        $level_model = new \Home\Model\LevelModel();
        foreach ($list as &$row) {
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            if($row['depart_id']){                          //部门名称
                $depart = $depart_model->get_one($row['depart_id']);
                if($depart){
                    $row['depart_name'] = $depart['depart_name'];
                }
            }
            if($row['lvl_id']){                            //级别名称
                $level = $level_model->get_one($row['lvl_id']);
                if($level){
                    $row['lvl_name'] = $level['lvl_name'];
                }
            }
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display('User:index');
    }
    
    /*  
     *  新增、修改用户页
     **/
    public function addAction(){
        $user_model = new \Home\Model\UserModel();
        $depart_model = new \Home\Model\DepartModel();
        
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $handle_permission = check_permission_byca('User', 'handle', $user_id, $lvl_id);
        if(IS_POST){
            if(!$user_model->create()){
                $this->ajaxReturn(array('status'=>0, 'msg'=>$user_model->getError()));
            }else{
                
                $data['user_name'] = I('post.user_name','');
                $data['phone'] = I('post.phone','');
                $data['birthday'] = I('post.birthday','');
                if(I('post.password')){
                    $data['password'] = encode_password(I('post.password'));
                }
                
                if($handle_permission){             //只有具备修改用户权限
                    $data['real_name'] = I('post.real_name');
                    $data['email'] = I('post.email');
                    $data['depart_id'] = I('post.depart_id');
                    $data['lvl_id'] = I('post.lvl_id');
                    $data['nx_vacation'] = floatval(I('post.nx_vacation'));
                    $depart = $depart_model->get_one($data['depart_id']);
                    $data['company_id'] = $depart['company_id'];
                    $data['leader_uid'] = I('post.leader_uid');
                }
                
                $id = I('post.user_id');
                $action = '';
                if(isset($id) && is_numeric($id)){
                    $id = intval($id);
                    $data['mod_user_id'] = $this->user_id;          //操作用户Id
                    $ret = $user_model->update_data($data, "user_id = $id");
                    $action = 'update';
                }else{
                    $data['crt_user_id'] = $this->user_id;
                    $ret = $user_model->insert_data($data);
                    $action = 'insert';
                }    
                if($ret){
                    logrecords($action, $user_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
        
        $id = I('get.user_id');
        if($id){
            $id = intval($id);
            $user = $user_model->get_one($id);
            if($user){
                
                //获取领导列表
                $leader_users = $this->get_leader($user['depart_id'], $user['lvl_id']);
                $this->assign('leader_users', $leader_users);
                $this->assign('user', $user);
            }
        }
        
        //获取部门列表
        $depart_model = new \Home\Model\DepartModel();
        $departs = $depart_model->get_use_list();
        
        //获取级别
        $level_model = new \Home\Model\LevelModel();
        $levels = $level_model->get_list("is_del=1");
        
        $this->assign('lvl_id', session('lvl_id'));
        $this->assign('departs', $departs);
        $this->assign('levels', $levels);
        $this->assign('handle_permission', $handle_permission);
        $this->display('User:add');
    }
    
    /*
     *  删除用户操作
     **/
    public function deleteAction(){
        if(IS_POST){
            $lvl_id = session('lvl_id');
            $user_id = $this->user_id;
            $handle_permission = check_permission_byca('User', 'handle', $user_id, $lvl_id);
            if(!$handle_permission){
                $this->ajaxReturn(array('status'=>0, 'msg'=>'你没有权限进行删除操作！'));
            }
            
            $id = I('post.user_id');
            $user_model = new \Home\Model\UserModel();
            $user = $user_model->get_one($id);
            if($user){
                $ret = $user_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "user_id = $id");
                if($ret){
                    logrecords('delete', $user_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'用户不存在！'));
        }
        
    }
    
    /*
     *  动态获取用户领导
     **/
    public function get_leaderAction(){
        $depart_id = I('post.depart_id',0);
        $lvl_id = I('post.lvl_id',0);
        
        $users = $this->get_leader($depart_id, $lvl_id);
        $this->ajaxReturn(array('status'=>1,'users'=>$users));
    }
    
    public function get_leader($depart_id,$lvl_id){
        if(!$depart_id || !$lvl_id) return false;
        $map1['company_id'] = session('company_id');
        $map1['is_del'] = 1;
        
        $depart = M('Depart')->where(array('depart_id'=>$depart_id,'is_del'=>1))->find();
        if($depart){
            $depart_id_arr = array($depart_id);
            if($depart['depart_par_id']) array_push($depart_id_arr, $depart['depart_par_id']);
            $map['depart_id'] = array("in", $depart_id_arr);
        }
        
        if($lvl_id){
            $map['lvl_id'] = array('lt', $lvl_id);
        }
        
        $map['_complex'] = $map1;
        $users = M('user')->where($map)->field('user_id,real_name')->order("lvl_id asc")->select();
        if(!$users){
            $map1['lvl_id'] = 2;
            $users = M('user')->where($map1)->field('user_id,real_name')->order("lvl_id asc")->select();
        }
        return $users;
    }
}