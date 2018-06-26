<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    
    /**  
     *  登录 
     **/
    public function indexAction(){
        if(IS_POST){
            $data = array();
            $username = I('post.username', '');
            $password = I('post.password', '');
            
            $user_model = M('user');
            $user = $user_model->where("user_name = '$username'")->find();
            if($user){
                if($user['password'] == encode_password($password)){
                    $depart_model = new \Home\Model\DepartModel();
                    $level_model = new \Home\Model\LevelModel();
                    $depart = $depart_model->get_one($user['depart_id']);
                    if($depart){
                        $user['depart_name'] = $depart['depart_name'];
                        if(!$depart['depart_par_id']){
                            $user['depart_par_id'] = $user['depart_id'];
                        }else{
                            $user['depart_par_id'] = $depart['depart_par_id'];
                        }
                        $user['depart_type'] = $depart['type'];
                    }
                    $level = $level_model->get_one($user['lvl_id']);
                    if($level){
                        $user['lvl_name'] = $level['lvl_name'];
                    }
                    foreach ($user as $key => $row) {
                        session($key, $row);
                    }
                    
                    if(session('ret_url')){
                        $data['ret_url'] = session('ret_url');
                        session('ret_url',null);
                    }
                    $data['status'] = 1;
                }else{
                    $data['status'] = 0;
                    $data['msg'] = '密码错误！';
                }
            }else{
                $data['status'] = 0;
                $data['msg'] = '用户名不存在！';
            }
            $this->ajaxReturn($data);
        }
        $this->display("Login:index");
    }
    
    /**
     *  后台退出登录
     **/
    public function logoutAction(){
        session('user_id', null); 
        session('real_name', null);
        $this->redirect('index');
    }
}