<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class CardRecordController extends BaseController
{

    public $check_access = true;
    private $table = 'CardRecord';
    public $pagesize = 20;
    public function __construct()
    {
        parent::__construct();
        $this->model = D('CardRecord');
    }
    /**
     * Litter7
     * 办公用品在线申请
     * 2017-04-12
     */
    public function indexAction()
    {
//        dump($this->user_id);
        $search = I('');
        $map = $this->mapFormat($search);
        $map['is_del'] = 1;
        $map['company_id'] = session('company_id');
        $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');
        $list = $this->model->get_lists($map,$Page->firstRow.','.$Page->listRows);
//echo $this->model->getLastSql();
        $UserModel = D('User');
        foreach($list as $key=>$val){
            if($val['deal_uid']){
                $list[$key]['deal_name'] = $UserModel->where('user_id='.$val['deal_uid'])->getField('user_name');
            }
        }
        $show = $Page->show();
        $this->assign('search',I(''));
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->assign('is_admin',is_admin_power());
        $this->assign('head_title', '名片申请');
        $this->display('index');
    }
    protected function mapFormat($search=array()){
        $name = trim($search['name']);
        $is_admin = is_admin_power();
        if($is_admin || $this->user_id==172 || $this->user_id==173 || $this->depart_id==42 || $this->user_id==353){
            if($name){
                $map['_string'] = "(name like '%".$name."%' OR en_name like '%".$name."%')";
            }
        }else{
//            $real_name = session('real_name');
            $map['_string'] = "(name like '%".$name."%' OR en_name like '%".$name."%')";
            $map['crt_user_id'] = $this->user_id;
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
        if(is_numeric($search['get_status'])){
           $map['get_status'] = $search['get_status'];
        }
        return $map;
    }

    /**
     * berry.qi
     * 添加
     * 2017-04-12
     */
    public function addAction()
    {

        $id = intval(I('id'));
        if($id>0){
            $map['is_del'] = 1;
            $map['id']=$id;
            $info = $this->model->get_onebywhere($map);
            if($info['address']){
                $info['address'] = explode(',',$info['address']);
            }
            $this->assign('info',$info);
        }
        //分类
        $cmap['is_del'] = 1;
        $category = D('OfficeSuppliesCategory')->get_lists($cmap);
        $this->assign('head_title', '新增名片申请');
        $this->assign('category', $category);
        $this->display('add');
    }

    /**
     * berry.qi
     * 供应商数据保存
     * 2016-04-19
     */
    public function saveAction()
    {
        $arr['code'] = 1;
        $arr['msg'] = '操作成功！';
        $data = $_POST;
        if ($this->model->create()===false) {
            $arr['code'] = 0;
            $arr['msg'] = $this->model->getError();
        }else{
            $data['company_id'] = session('company_id');
            if($data['address']){
                $data['address'] = implode(',',$data['address']);
            }
            if($data['id']){
                $data['mod_user_id'] = $this->user_id;
                $where['id'] = $data['id'];
                $id = $this->model->update($data,$where);
                $logtype = 'update';
            }else{
                $data['crt_user_id'] = $this->user_id;
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
            $where['id'] = $id;
            $where['company_id'] = session('company_id');
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
    public function changestatusAction(){
        $is_admin = is_admin_power();
        if(!$is_admin && $this->user_id!=172 && $this->user_id!=173 && $this->depart_id!=42 && $this->user_id!=353){
            $arr['code'] = -1;
            $arr['msg'] = '无权限操作！';
            $this->ajaxReturn($arr);
        }
        $id = intval(I('post.id'));
        $arr['code'] = 0;
        $arr['msg'] = '更新失败';
        if($id){
            $map['id'] = $id;
            $map['company_id'] = session('company_id');
            $res = $this->model->update(array('get_status'=>I('post.get_status'),'deal_uid'=>$this->user_id),$map);
            if($res){
                $arr['code'] = 1;
                $arr['msg'] = '更新成功';
            }
        }
        $this->ajaxReturn($arr);
    }
}