<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class OfficeSuppliesController extends BaseController
{

    public $check_access = true;
    private $table = 'OfficeSupplies';
    public $pagesize = 20;
    public function __construct()
    {
        parent::__construct();
        $this->model = D('OfficeSupplies');
    }
    /**
     * Litter7
     * 办公用品在线申请
     * 2017-04-12
     */
    public function indexAction()
    {
        $search = I('');
        $map = $this->mapFormat($search);
        $map['is_del'] = 1;
        $map['company_id'] = session('company_id');
        $catModel = M('OfficeSuppliesCategory');
       /* $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');*/
        $list = $this->model->where($map)->select();
        /*foreach($list as $key=>$val){
            //按分类重组数组
            if($val['cid']>0){
                $val['cat_name'] = $catModel->where('id='.$val['cid'])->getField('name');
                $newarr[$val['cid']]['cid'] = $val['cid'];
                $newarr[$val['cid']]['list'][] = $val;
            }
        }*/
        foreach($list as $key=>$val){
            //按分类重组数组
            if($val['cid']>0){
                $list[$key]['cat_name'] = $catModel->where('id='.$val['cid'])->getField('name');
            }
        }
//        dump($list);
        $category = $catModel->where('is_del=1')->order('id asc')->select();
//        $show = $Page->show();
        $this->assign('search',I(''));
//        $this->assign('page',$show);
        $this->assign('category',$category);
        $this->assign('is_admin',is_admin_power());
//        $this->assign('data',$newarr);
        $this->assign('list',$list);
        $this->assign('head_title', '办公用品');
        $this->display('index');
    }
    protected function mapFormat($search=array()){
        $name = trim($search['name']);
        if($name){
            $map['name'] = array('like',"%$name%");
        }
        $is_admin = is_admin_power();
        if($is_admin || $this->user_id==172 || $this->user_id==173  || $this->depart_id==42 || $this->user_id==353){
            //todo
        }else{
            $map['inventory'] = array('gt',0);//只显示有库存的物品
        }
        $cid = intval($search['cid']);
        if($cid){
            $map['cid'] = $cid;
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

    /**
     * berry.qi
     * 添加
     * 2017-04-12
     */
    public function addAction()
    {
        $is_admin = is_admin_power();
        if(!$is_admin && $this->user_id!=172 && $this->user_id!=173 && $this->depart_id!=42 && $this->user_id!=353){
            $this->error('无权限操作');
        }
        $id = intval(I('id'));
        if($id>0){
            $map['is_del'] = 1;
            $map['id']=$id;
            $info = $this->model->get_onebywhere($map);
            $this->assign('info',$info);
        }
        //分类
        $cmap['is_del'] = 1;
        $category = D('OfficeSuppliesCategory')->get_lists($cmap);
        $this->assign('head_title', '新增办公用品申请');
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
        $is_admin = is_admin_power();
        if(!$is_admin && $this->user_id!=172 && $this->user_id!=173 && $this->depart_id!=42 && $this->user_id!=353){
            $arr['code'] = -1;
            $arr['msg'] = '无权限操作！';
            $this->ajaxReturn($arr);
        }
        $arr['code'] = 1;
        $arr['msg'] = '操作成功！';
        $data = $_POST;
        if ($this->model->create()===false) {
            $arr['code'] = 0;
            $arr['msg'] = $this->model->getError();
        }else{
            $data['company_id'] = session('company_id');
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
        $is_admin = is_admin_power();
        if(!$is_admin && $this->user_id!=172 && $this->user_id!=173 && $this->depart_id!=42 && $this->user_id!=353){
            $arr['code'] = -1;
            $arr['msg'] = '无权限操作！';
            $this->ajaxReturn($arr);
        }
        $id = intval(I('id'));
        if($id){
            $where['id'] = $id;
            $where['company_id'] = session('company_id');
            $data['is_del'] = -1;
            $data['mod_user_id'] = $this->user_id;
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