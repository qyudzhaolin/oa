<?php
/**
 * OA系统用户管理
 */
namespace Home\Controller;
use Think\Page;
class BookController extends BaseController{
    
    public $check_access = true;
    public $head_title = '用户库';
    
    public function indexAction(){
        $where = 'is_del = 1';
        $keyword = I('get.keyword','','addslashes');
        if($keyword){
            $where .= " and (real_name like '%$keyword%')";
        }
        
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        if($lvl_id > 2){
            $where .= " and lvl_id!=1 and company_id=".session('company_id');
        }
        $user_model = new \Home\Model\UserModel();
        $count = $user_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $user_model->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        
        $depart_model = new \Home\Model\DepartModel();
        $level_model = new \Home\Model\LevelModel();
        
        $company_arr = $depart_model->get_company();
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
            $row['company_name'] = $company_arr[$row['company_id']];
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('phone_watch', check_permission_left('Book', 'phone_watch'));
        $this->display('Book:index');
    }
}