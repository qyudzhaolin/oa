<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class MjsupplierController extends BaseController
{

    public $check_access = true;
    private $table = 'Mjsupplier';
    public function __construct()
    {
        parent::__construct();
        if(!check_permission_left('Mjsupplier', 'index')){
            redirect(U('Index/index'), 2, '您没有权限对该供应商进行操作!');
        }
        $this->model = D('Mjsupplier');
    }
    /**
     * berry.qi
     * 媒介供应商列表
     * 2016-04-19
     */
    public function indexAction()
    {
        /* if(session('lvl_id')!=1 && session('lvl_id')!=2){
            $map['depart_id'] = session('depart_par_id');
        } */
        $sup_full_name = trim(I('sup_full_name'));
        if($sup_full_name){
            $where['sup_full_name'] = array('like',"%{$sup_full_name}%");
            $where['sup_short_name'] = array('like',"%{$sup_full_name}%");
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
            $search['sup_full_name'] = $sup_full_name;
        }
        $map['is_del'] = 1;
        $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('post.');
        $list = $this->model->get_lists($map,$Page->firstRow.','.$Page->listRows);
        
        $pay_method = C('PAY_METHOD');
        foreach ($list as &$row) {
            $row['pay_method'] = $row['pay_method'] ? $pay_method[$row['pay_method']] : '';
        }
        
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
            $map['sup_id']=$id;
            $info = $this->model->get_onebywhere($map);
            if($info['crt_user_id']>0){
                $info['user_name'] = M('User')->where('user_id='.$info['crt_user_id'])->getField('user_name');
            }
            $this->assign('info',$info);
        }
        $banks = M('Bank')->where('is_del=1')->select();
        $pay_method = C('PAY_METHOD');
        $this->assign('banks',$banks);
        $this->assign('methods',$pay_method);
        $this->assign('head_title', '新增供应商');
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
            if($data['pay_method'] == 1){
                $data['other'] = '';
            }elseif($data['pay_method'] == 2){
                $data['bnk_acct'] = '';
                $data['bnk_branch'] = '';
                $data['other'] = '';
            }elseif($data['pay_method'] == 3){
                $data['bnk_acct'] = '';
                $data['bnk_branch'] = '';
            }
            
            if($data['sup_id']){
                $data['mod_user_id'] = $this->uid;
                $where['sup_id'] = $data['sup_id'];
                $id = $this->model->update($data,$where);
                $logtype = 'update';
            }else{
                $data['crt_user_id'] = $this->uid;
                $data['depart_id'] = session('depart_par_id');
                $id = $this->model->insert($data);
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
            $where['sup_id'] = $id;
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