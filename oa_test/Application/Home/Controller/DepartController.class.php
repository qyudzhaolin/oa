<?php
/**
 * OA系统用户管理
 */
namespace Home\Controller;
use Think\Page;
class DepartController extends BaseController{
    
    public $check_access = true;
    public $head_title = '部门管理';
    
    /*
     *  部门列表页
     **/
    public function indexAction(){
        $where = 'is_del = 1 and company_id='.session('company_id');
        if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
            $where .= " and depart_name like '%{$_GET['keyword']}%'";
        }
        $depart_model = new \Home\Model\DepartModel();
        $count = $depart_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $depart_model->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        
        
        $company = $depart_model->get_company();
        foreach ($list as &$row) {
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            if($row['depart_par_id']){
                $par_depart = $depart_model->get_one($row['depart_par_id']);
                if($par_depart){
                    $row['par_depart_name'] = $par_depart['depart_name'];
                }
            }
            $row['company_name'] = $company[$row['company_id']];
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display('Depart:index');
    }
    
    /*  
     *  新增、修改部门页
     **/
    public function addAction(){
        $depart_model = new \Home\Model\DepartModel();
        if(IS_POST){
            if(!$depart_model->create()){
                $this->ajaxReturn(array('status'=>0, 'msg'=>$depart_model->getError()));
            }else{
                $data['depart_name'] = I('post.depart_name','');
                $data['depart_par_id'] = I('post.depart_par_id', 0);
                $data['company_id'] = I('post.company_id', 1);
                $data['hrg_uid'] = I('post.hrg_uid', 0);
                $id = I('post.depart_id');
                $action = '';
                if(isset($id) && is_numeric($id)){
                    $id = intval($id);
                    $data['mod_user_id'] = $this->user_id;          //操作用户Id
                    $ret = $depart_model->update_data($data, "depart_id = $id");
                    $action = 'update';
                }else{
                    $data['crt_user_id'] = $this->user_id;
                    $ret = $depart_model->insert_data($data);
                    $action = 'insert';
                }    
                if($ret){
                    logrecords($action, $depart_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
        
        $id = I('get.depart_id');
        $fwhere = "depart_par_id=0 and is_del=1";
        if($id){
            $id = intval($id);
            $depart = $depart_model->get_one($id);
            if($depart){
                $this->assign('depart', $depart);
                $fwhere .= " and depart_id!=$id";         //上级部门不包括自己
            }
        }
        
        //获取HR部门人员列表
        $hr_list = M('user')->where("depart_id=60 or depart_id=12")->select();
        
        //获取上级部门
        $fdeparts = $depart_model->get_list($fwhere);
        $this->assign('hr_list', $hr_list);
        $this->assign('company_id', session('company_id'));
        $this->assign('company', $depart_model->get_company());
        $this->assign('fdeparts', $fdeparts);        
        $this->display('Depart:add');
    }
    
    /*
     *  删除部门操作
     **/
    public function deleteAction(){
        if(IS_POST){
            $id = I('post.depart_id');
            $depart_model = new \Home\Model\DepartModel();
            $depart = $depart_model->get_one($id);
            if($depart){
                if($depart['depart_par_id'] == 0){
                    $is_par = $depart_model->get_onebywhere("depart_par_id=$id and is_del=1");
                    if($is_par){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'该部门下有子部分存在，请确认！'));
                    }
                }
                
                $ret = $depart_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "depart_id = $id");
                if($ret){
                    logrecords('delete', $depart_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'部门不存在！'));
        }
        
    }
    /*
     * Litter_7
     * 2017-6-23
     * 根据部门读取该部门下的员工
     */
    public function ajaxGetUsersAction(){
        $departModel = D('Depart');
        $depart_id = $_POST['depart_id'];
        $flow_uids = $_POST['flow_uids'];
        $uidsarr = array_unique(explode(',',$flow_uids));
//        dump($uidsarr);
        $return['status'] = 0;
        if(is_numeric($depart_id) && $depart_id>0){
            //是否是父级部门
            $company_id = session('company_id');
            $depart_ids = $departModel->where("is_del=1 and depart_par_id={$depart_id} and company_id={$company_id}")->getField('depart_id',true);
            $map['is_del'] = 1;
            $map['lvl_id'] = array('notin',array(11,10));//级别大于7
            if($depart_ids){
                array_push($depart_ids,$_POST['depart_id']);
                $map['depart_id'] = array('in',$depart_ids);
            }else{
                $map['depart_id'] = $depart_id;
            }
            //读取人员列表
            $list = D('User')->where($map)->order('user_id asc')->select();
            $html = "";
            if($list) {
                $return['status'] = 1;
                foreach ($list as $key => $val) {
                    $selected = '';
                    if(in_array($val['user_id'],$uidsarr)){
                        $selected = 'checked';
                    }
                    $html .= '<label class="checkbox-inline flowUserTab"><input type="checkbox" value="'.$val['user_id'].'" '.$selected.'>'.$val['real_name'].'</label>';
                }
                $return['html'] = $html;
            }
        }
        $this->ajaxReturn($return);
    }
}