<?php
/**
 * OA系统用户权限
 */
namespace Home\Controller;
use Think\Page;
class PermissionsController extends BaseController{
    
    public $check_access = true;
    public $head_title = '权限管理';
    
    public function __construct(){
        parent::__construct();
        $lvl_id = session('lvl_id');
        if($lvl_id>2){
            $this->redirect('/Home/Index', array() ,2, '您没有权限进入该页面！');
        }
    }
    
    public function indexAction(){
        $per_model = new \Home\Model\PermissionsModel();
        $where['is_del'] = 1;
        if(I('get.keyword')){
            $where['per_name'] = array('like', "%".I('get.keyword')."%");
        }
        
        $count = $per_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $per_model->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        foreach ($list as &$row) {
            $row['p_per_name'] = "--";
            if($row['pid']){
                $p_per = $per_model->get_one($row['pid']);
                $row['p_per_name'] = $p_per['per_name'];
            }
        }
        
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display('Permissions:index');
    }
    
    public function addAction(){
        $per_model = new \Home\Model\PermissionsModel();
        if(IS_POST){
            if(!$per_model->create()){
                $this->ajaxReturn(array('status'=>0, 'msg'=>$per_model->getError()));
            }else{
                $data['per_name'] = I('post.per_name');
                $data['pid'] = I('post.pid',0);
                $data['controller'] = I('post.controller');
                $data['action'] = I('post.action');
                $data['is_module'] = I('post.is_module',0);
                $data['css'] = I('post.css');
                $data['is_2'] = I('post.is_2',0);
                $data['is_3'] = I('post.is_3',0);
                $data['is_4'] = I('post.is_4',0);
                $data['is_5'] = I('post.is_5',0);
                $data['is_6'] = I('post.is_6',0);
                $data['is_7'] = I('post.is_7',0);
                $data['is_8'] = I('post.is_8',0);
                $data['is_9'] = I('post.is_9',0);
                $data['is_10'] = I('post.is_10',0);
                $data['is_11'] = I('post.is_11',0);
                
                $per_id = I('post.per_id');
                
                if(isset($per_id) && is_numeric($per_id)){
                    $per = $per_model->get_one($per_id);
                    if($per){
                        $per_id = intval($per_id);
                        $ret = $per_model->update_data($data, "per_id = $per_id");
                        $per_model->clean_cache($per['controller'], $per['action']);
                    }
                }else{
                    $ret = $per_model->insert_data($data);
                }    
                if($ret){                                          //对项目人员进行操作
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $per_id = I('get.per_id');
        if($per_id){
            $per_id = intval($per_id);
            $per = $per_model->get_one($per_id);
            if($per){
                $this->assign('permissions', $per);
            }
        }
        
        $p_pers = $per_model->get_list("is_del=1 and pid=0");
        $this->assign('p_pers', $p_pers);
        $this->display('Permissions:add');
    }
    
    public function setAction(){
        $user_id = I("get.user_id");
        $user_model = new \Home\Model\UserModel();
        $user_per_model = new \Home\Model\UserPermissionsModel();
        $per_model = new \Home\Model\PermissionsModel();
    
        $user = $user_model->get_one($user_id);
        if(!$user || $user['company_id'] != session('company_id')){
            $this->redirect('/Home/Index', array() ,2, '您没有权限对该用户进行操作！');
        }
    
        $list = $per_model->where("is_del=1")->order("pid asc,per_id asc")->field("per_id,pid,per_name")->select();
        $permissions = array();
        foreach ($list as $row) {
            $permissions[$row['per_id']] = $row;
        }
        $pers = $user_per_model->get_list("user_id=$user_id");
        if($pers){
            foreach ($pers as $key => $row) {
                if($permissions[$row['per_id']]){
                    $permissions[$row['per_id']]['is_check'] = true;
                }
            }
        }else{
            //若未设置过权限，则根据用户级别显示级别默认权限
            foreach ($permissions as &$row) {
                if($row['is_'.$user['lvl_id']]){
                    $row['is_check'] = true;
                }
            }
        }
        $list = array();
        foreach ($permissions as $row1) {
            if($row1['pid']>0){
                array_push($list[$row1['pid']]['children'], $row1);
            }else{
                $list[$row1['per_id']]['children'] = array($row1);
            }
        }
    
        $this->assign('user', $user);
        $this->assign('permissions', $list);
        $this->display('Permissions:set');
    }
    
    public function deleteAction(){
        if(IS_POST){
            $id = I('post.per_id');
            $per_model = new \Home\Model\PermissionsModel();
            $per = $per_model->get_one($id);
            if($per){
                $ret = $per_model->update_data(array('is_del'=>-1), "per_id = $id or pid=$id");
                if($ret){
                    $per_model->clean_cache($per['controller'], $per['action']);
                    logrecords('delete', $per_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'权限不存在！'));
        }
    
    }
    
    public function adduAction(){
        if(IS_POST){
            $user_model = new \Home\Model\UserModel();
            $per_model = new \Home\Model\UserPermissionsModel();
            $data['user_id'] = I('post.user_id');
            $per_ids = trim(I('post.per_ids',''),",");
            if(strlen($per_ids) == 0){
                $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择相应权限选项！'));
            }
            
            $count = $per_model->where(array("user_id"=>$data['user_id']))->count();
            if($count>0){
                $ret = $per_model->delete_data("user_id = {$data['user_id']}");
            }else{
                $ret = true;
            }    
            if($ret){
                $per_arr = explode(",", $per_ids);
                foreach ($per_arr as $row) {
                    $data['per_id'] = $row;
                    $per_model->insert_data($data);
                }
                logrecords("update", $per_model->get_tablename());
                $this->ajaxReturn(array('status'=>1));
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    }
}