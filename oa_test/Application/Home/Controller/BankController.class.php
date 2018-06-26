<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class BankController extends BaseController
{

    public $check_access = true;
    private $table = 'Bank';
    private $model;
    public function __construct()
    {
        parent::__construct();
        $this->model = D('Bank');
    }
    /**
     * berry.qi
     * 供应商列表
     * 2016-04-19
     */
    public function indexAction()
    {
        $map['is_del'] = 1;
        $bank_name = trim(I('bank_name'));
        if($bank_name){
            $map['bank_name'] = array('like',"%{$bank_name}%");
            $search['bank_name'] = $bank_name;
        }
        $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('post.');
        $list =  $this->model->get_lists($map,$Page->firstRow,$Page->listRows);
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
            $map['bank_id'] = $id;
            $info =  $this->model->get_onebywhere($map);
            $this->assign('info',$info);
        }
        $this->assign('head_title', '新增/编辑银行');
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
            if($data['bank_id']){
                //$data['mod_user_id'] = $this->uid;
                $where['bank_id'] = $data['bank_id'];
                $id = $this->model->update($data,$where);
                //写入日志
                logrecords('update',$this->table);
            }else{
                //$data['crt_user_id'] = $this->uid;
                $id =$this->model->insert($data);
                logrecords('insert',$this->table);
            }
            $arr['code'] = 1;
            $arr['msg'] = '操作成功！';
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
            $where['bank_id'] = $id;
            $data['is_del'] = -1;
            //$data['mod_user_id'] = $this->uid;
            $id = $this->model->update($data,$where);
            if($id){
                $arr['code'] = 1;
                $arr['msg'] = '删除成功';
                logrecords('delete',$this->table,1);
            }else{
                $arr['code'] = 0;
                $arr['msg'] = '删除失败';
                logrecords('delete',$this->table,-1);
            }
        }else{
            $arr['code'] = -1;
            $arr['msg'] = '参数有误';
        }
        $this->ajaxReturn($arr);
    }
}