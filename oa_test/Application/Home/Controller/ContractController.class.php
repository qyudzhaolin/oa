<?php
/**
 * OA系统合同管理
 */
namespace Home\Controller;
use Think\Page;
class ContractController extends BaseController{
    
    public $check_access = true;
    public $head_title = '合同管理';
    
    public function __construct()
    {
        parent::__construct();
        $this->model = D('Contract');
    }
    
    /*
     *  合同列表页
     **/
    public function indexAction(){
        $proj_model = new \Home\Model\ProjectModel();
        $file_model = new \Home\Model\FileModel();
        $proj_id = I('get.proj_id',0);
        //判断是否有权限进行查看操作
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        if($lvl_id >5 && !$proj_model->check_in_proj($lvl_id, $user_id, $proj_id)){
            redirect(U('Index/index'), 2, '您没有权限查看该项目下的合同!');
        }
        if($lvl_id == 5 && $user_id!=33){               //部门领导但不包括张佳璐
            $depart_model = new \Home\Model\DepartModel();
            $depart_id = session('depart_id');
            $depart_ids = $depart_model->where("depart_par_id=$depart_id or depart_id=$depart_id")->getField('depart_id',true);
            $where_data['depart_id'] = array('in', $depart_ids); 
        }
        
        $where_data['is_del'] = 1;
        if($proj_id){
            $where_data['proj_id'] = $proj_id;
        }
        $keyword = I('get.keyword','','addslashes'); 
        if($keyword){
            $where_data['ct_no'] = array('like',"%$keyword%");
        }
        
        $count = $this->model->where($where_data)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $this->model->where($where_data)->order('ct_limit_date asc,ct_id asc')->limit($page->firstRow.','.$page->listRows)->select();
        
        foreach ($list as &$row) {
            $row['ct_limit_date'] = date('Y-m-d', $row['ct_limit_date']);
            $file = $file_model->get_one($row['file_id']);
            if($file){
                $row['file_name'] = $file['file_name'];
                $row['file_url'] = C('IMG_DOMAIN').$row['file_name'];
            }
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display('Contract:index');
    }
    public function listAction(){
        $proj_model = new \Home\Model\ProjectModel();
        $file_model = new \Home\Model\FileModel();
        $proj_id = I('get.proj_id',0);

        //判断是否有权限进行查看操作
        //admin & Ruby=33
        $is_admin = is_admin_power();
        $user_id = $this->user_id;
        if(!$is_admin && $this->user_id!=33){
            redirect(U('Index/index'), 2, '您没有权限!');
        }
        $search = I('');
        $map = $this->mapFormat($search);
        $map['is_del'] = 1;
        $count = $this->model->where($map)->count();
        $page = new Page($count, $this->pagesize);
        $show = $page->show();
        $list = $this->model->where($map)->order('ct_limit_date asc,ct_id asc')->limit($page->firstRow.','.$page->listRows)->select();
        
        foreach ($list as &$row) {
            $row['ct_limit_date'] = date('Y-m-d', $row['ct_limit_date']);
            $file = $file_model->get_one($row['file_id']);
            if($file){
                $row['file_name'] = $file['file_name'];
                $row['file_url'] = C('IMG_DOMAIN').$row['file_name'];
            }
            if($row['proj_id']){
                $row['project_name'] = $proj_model->where('proj_id='.$row['proj_id'])->getField('proj_name');
            }
            if($row['sup_id']){
                $row['sup_name'] = M('Supplier')->where('sup_id='.$row['sup_id'])->getField('sup_full_name');
            }
        }
        $this->assign('is_admin',$is_admin);
        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('page',$show);
        $this->display('Contract:list');
    }
    protected function mapFormat($search=array()){
        $ct_no = trim($search['ct_no']);
        if($ct_no){
            $map['ct_no'] = array('like',"%$ct_no%");
        }
        //项目和供应商检索
        $prej_name = trim($search['proj_name']);
        if($prej_name){
            $prej_ids = M('Project')->where("proj_name like '%".$prej_name."%'")->getField('proj_id',true);
            if($prej_ids){
                $map['proj_id'] = array('in',$prej_ids);
            }else{
                $map['proj_id'] = -99;
            }
        }
        $sup_name = trim($search['sup_name']);
        if($sup_name){
            $sup_ids = M('Supplier')->where("sup_full_name like '%".$sup_name."%'")->getField('sup_id',true);
            if($sup_ids) {
                $map['sup_id'] = array('in', $sup_ids);
            }else{
                $map['sup_id'] = -99;
            }
        }
        $is_admin = is_admin_power();
      /*  if($is_admin || $this->depart_id==33){
            //todo
        }else{

        }*/
        $supplier_id = intval($search['supplier_id']);
        if($supplier_id){
            $map['supplier_id'] = $supplier_id;
        }
        $sdate = strtotime($search['start_date']);
        $edate = strtotime($search['end_date']);
        if($sdate && $edate){
            $edate = $edate + 86400;
            $map['crt_time'] = array('between',array($sdate,$edate));
        }elseif($sdate){
            $map['crt_time'] = array('gt',$sdate);
        }elseif($edate){
            $edate = $edate + 86400;
            $map['crt_time'] = array('lt',$edate);
        }
        return $map;
    }

    /*
     *  合同新增页
     **/
    public function addAction(){
        $id = intval(I('get.id'));
        $proj_id = intval(I('get.proj_id'));
        $proj_model = new \Home\Model\ProjectModel();
        $supplierModel = D('Supplier');
        $user_id = $this->user_id;
        $admin_edit = 0;
        if($proj_id){
            $project = $proj_model->get_one($proj_id);
            if($project){
                //判断是否有权限进行编辑操作
                if($id>0){
                    $admin_edit = 1;
                    $map['is_del'] = 1;
                    $map['ct_id'] = $id;
                    $info = $this->model->get_onebywhere($map);
                    $is_admin = is_admin_power();
                    if($info['crt_user_id'] != $user_id && !$is_admin && $this->user_id!=33){
                        redirect(U('Index/index'), 2, '您没有权限对合同进行编辑操作!');
                    }elseif($is_admin || $this->user_id==33){
                        $admin_edit = 2;
                    }
                    $info['ct_limit_date'] = $info['ct_limit_date'] ? date('Y-m-d',$info['ct_limit_date']) : '';
                    $file_model = new \Home\Model\FileModel();
                    if($info['file_id']){
                        $file = $file_model->get_one($info['file_id']);
                        if($file){
                            $info['file_name'] = $file['file_name'];
                        }
                    }if($info['sup_id']){
                        $file = $supplierModel->get_one($info['sup_id']);
                        if($file){
                            $info['sup_name'] = $file['sup_full_name'];
                        }
                    }

                    
                    $this->assign('info',$info);
                }
                //供应商
                $supplierlist = $supplierModel->where('is_del=1')->order('level1_cid asc,sup_id desc')->select();
                $this->assign('supplierlist', $supplierlist);
                $this->assign('admin_edit', $admin_edit);
                $this->assign('project', $project);
                $this->assign('edit_type', I('get.edit_type'));
                $this->display('add');
            }
        }
    }
    
    public function saveAction()
    {
        if(IS_POST){
            $data = I('post.');
            $ct_data['proj_id'] = $data['proj_id'];
            $ct_data['ct_limit_date'] = strtotime($data['ct_limit_date']);
            $ct_data['ct_money'] = $data['ct_money'];
            $ct_data['file_id'] = $data['file_id'];
            $ct_data['depart_id'] = session('depart_id');
            $ct_data['ct_no'] = $data['ct_no'];
            $ct_data['sup_id'] = $data['sup_id'];
            $proj_model = new \Home\Model\ProjectModel();
            $proj = $proj_model->get_one($data['proj_id']);
            if(!$data['id']){
                $ct_data['crt_user_id'] = $this->user_id;
                $ret = $this->model->insert_data($ct_data);
            }else{
                $contract = $this->model->get_onebywhere("ct_id='{$data['id']}' and is_del=1");
                $ret = $this->model->update_data($ct_data, "ct_id=".$data['id']);
            }
            
            if($ret){
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("status"=>0,"msg"=>"操作失败，请稍后再试"));
        }
    }

    /*
     *  删除项目操作
     **/
    public function deleteAction(){
        if(IS_POST){
            $id = I('post.ct_id');
            $contract = $this->model->get_one($id);
            if($contract){
                //判断是否生成预算单
                $model = M('Recouped');
                if($model->where("ct_id=$id and is_del=1")->count()){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'该合同已被报销单采用，不能删除！'));
                }
    
                $ret = $this->model->update_data(array('is_del'=>-1), "ct_id = $id");
                if($ret){
                    logrecords('delete', $this->model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'合同不存在！'));
        }
    
    }
}