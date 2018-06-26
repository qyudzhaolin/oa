<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class CustomerController extends BaseController
{

    public $check_access = true;
    private $model;
    private $table = 'Customer';
    public function __construct()
    {
        parent::__construct();
        if(session('lvl_id')>3){
            $this->redirect('/Home/Index', array() ,2, '您没有权限对客户库进行操作!');
        }
        $this->model = D('Customer');
    }
    /**
     * berry.qi
     * 供应商列表
     * 2016-04-19
     */
    public function indexAction()
    {
        $map['is_del'] = 1;
        /* if(session('lvl_id')!=1 && session('lvl_id')!=2){
            $map['depart_id'] = session('depart_par_id');
        } */
        $cust_full_name = trim(I('cust_full_name'));
        if($cust_full_name){
            $map['cust_full_name'] = array('like',"%{$cust_full_name}%");
            $search['cust_full_name'] = $cust_full_name;
        }
        $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('post.');
        $list =  $this->model->get_lists($map,$Page->firstRow,$Page->listRows);
        //关联字段设置
        foreach($list as $key=>&$value){
            if($value['bnk_id']>0){
                $value['bank_name'] = M('Bank')->where('bank_id='.$value['bnk_id'])->getField('bank_name');
            }
        }
//        echo $model->getLastSql();
        $show = $Page->show();
        $this->assign('search',$search);
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->assign('head_title', '用户管理-供应商');
        $this->display('index');
    }

    /**
     * berry.qi
     * 供应商添加
     * 2016-04-19
     */
    public function addAction()
    {
        $id = intval(I('id'));
        if($id>0){
            $map['is_del'] = 1;
            $map['cust_id'] = $id;
            $info =  $this->model->get_onebywhere($map);
            if($info['crt_user_id']>0){
                $info['user_name'] = M('User')->where('user_id='.$info['crt_user_id'])->getField('user_name');
            }
            $this->assign('info',$info);
        }
        $this->assign('head_title', '新增客户');
        $this->display('add');
    }

    /**
     * berry.qi
     * 供应商数据保存
     * 2016-04-19
     */
    public function saveAction()
    {
        if ($this->model->create()===false) {
            $arr['code'] = 0;
            $arr['msg'] = $this->model->getError();
        }else{
            $data = I('post.');
            if($data['cust_id']){
                $data['mod_user_id'] = $this->uid;
                $where['cust_id'] = $data['cust_id'];
                $id = $this->model->update($data,$where);
                $logtype = 'update';
            }else{
                $data['crt_user_id'] = $this->uid;
                $data['depart_id'] = session('depart_par_id');
                $id =$this->model->insert($data);
                $logtype = 'insert';
            }
            if($id){
                $arr['code'] = 1;
                $arr['msg'] = '操作成功！';
                $logstatus = 1;
            }else{
                $arr['code'] = 0;
                $arr['msg'] = '操作失败！';
                $logstatus = -1;
            }
            logrecords($logtype,$this->table,$logstatus);
        }
        $this->ajaxReturn($arr);
    }
    /**
     * berry.qi
     * 供应商数据保存
     * 2016-04-19
     */
    public function delAction(){
        $id = intval(I('id'));
        if($id){
            $where['cust_id'] = $id;
            $data['is_del'] = -1;
            $data['mod_user_id'] = $this->uid;
            $id = $this->model->update($data,$where);
            if($id){
                $arr['code'] = 1;
                $arr['msg'] = '删除成功';
                $logstatus = 1;
            }else{
                $arr['code'] = 0;
                $arr['msg'] = '删除失败';
                $logstatus = -1;
            }
            logrecords('delete',$this->table,$logstatus);
        }else{
            $arr['code'] = -1;
            $arr['msg'] = '参数有误';
        }
        $this->ajaxReturn($arr);
    }
}