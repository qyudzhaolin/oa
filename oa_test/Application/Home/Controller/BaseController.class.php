<?php
namespace Home\Controller;
use Think\Controller;

//基础类
class BaseController extends Controller{
    
    public $check_access = true;
    public $head_title = '';
    public $pagesize = 20;
    public $user_id = 0;
    public $depart_id = 0;

    public function __construct(){
        parent::__construct();
        $user_id = I('session.user_id');
        if($this->check_access == true && !$user_id){
            $ret_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            session('ret_url', $ret_url);
            $this->redirect('/Home/Login', array() ,2, '请先登录');
        }
        $this->user_id = $user_id;
        $this->depart_id = session('depart_id');
        
        //判断权限
        /* $exist = check_permission_access($user_id, I('session.lvl_id'));
        if(!$exist){
            $this->redirect('/Home/index', array() ,2, '你无权限访问该页面');
        }
        
        //获取动态加载的左侧菜单
        $left_modules = get_left_modules($user_id, I('session.lvl_id'));  */
        
        //$this->assign('is_mj_creat', check_is_mj_creat());
        $this->assign('head_title', $this->head_title);
        $this->assign('controller_name', CONTROLLER_NAME);
        $this->assign('action_name', ACTION_NAME);
        $this->assign('real_name', I('session.real_name'));
        $this->assign('user_depart_name', I('session.depart_name'));
        $this->assign('user_lvl_name', I('session.lvl_name'));
        $this->assign('user_lvl_id', I('session.lvl_id'));
        $this->assign('user_id', $this->user_id);
        $this->assign('depart_id', I('session.depart_id'));
        $this->assign('depart_type', I('session.depart_type'));
        $this->assign('is_supplier_creat', I('session.is_supplier_creat'));
    }
}