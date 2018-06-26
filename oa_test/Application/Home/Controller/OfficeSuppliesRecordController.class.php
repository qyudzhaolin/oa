<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class OfficeSuppliesRecordController extends BaseController
{

    public $check_access = true;
    private $table = 'OfficeSuppliesRecord';
    public $pagesize = 20;
    public function __construct()
    {
        parent::__construct();
        $this->model = D('OfficeSuppliesRecord');
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

        $departModel = M('Depart');
        $mainModel = M('OfficeSuppliesMain');
        $officeSuppliesModel = M('OfficeSupplies');
       /* $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');*/
        $sum = array();
        $list = $this->model->where($map)->select();
        foreach($list as $key=>$val){
            $list[$key]['depart_name'] = $departModel->where('depart_id='.$val['depart_id'])->getField('depart_name');
            $goodsinfo = $officeSuppliesModel->where('is_del=1 and id='.$val['sid'])->find();
            $list[$key]['goods_name'] = $goodsinfo['name'];
            $maininfo = $mainModel->find($val['mid']);
            $list[$key]['main_no'] = $maininfo['no'];
            //计算总计
           /* $sum['page_count_num'] += $val['num'];
            $sum['page_count_price'] += $val['cur_price'];*/
        }
//        $sum['count_num'] = $this->model->where($map)->order('id DESC')->sum('num');
//        $sum['count_price'] = $this->model->where($map)->order('id DESC')->sum('cur_price');
//        echo $this->model->getLastSql();
//        $show = $Page->show();
        //部门信息
        $departments = $departModel->where('is_del=1')->order('depart_id asc')->select();
        $departments = formatToTree($departments, $html = '--', $pid = 0, $level = 0,$pidname='depart_par_id',$idname = 'depart_id');
//        dump($departments);
        $mid = intval(I('mid'));
        if($mid>0){
            $main = $mainModel->find($mid);
            $this->assign('mid',$mid);
        }
        $this->assign('search',$search);
        $this->assign('page',$show);
        $this->assign('maininfo',$main);
        $this->assign('list',$list);
        $this->assign('sum',$sum);
        $this->assign('is_admin',is_admin_power());
        $this->assign('departments',$departments);
        $this->assign('head_title', '办公用品申领记录');
        $this->display('index');
    }
    protected function mapFormat($search=array()){
        $real_name = trim($search['real_name']);
        $is_admin = is_admin_power();
        if($is_admin || $this->user_id==172 || $this->user_id==173 || $this->depart_id==42 || $this->user_id==353){
            if($real_name){
                $map['real_name'] = array('like',"%$real_name%");
            }
        }else{
            $map['real_name'] = array('like',"%$real_name%");
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
        if(is_numeric($search['depart_id']) && $search['depart_id']>0){
            //是否是父级部门
            $depart_ids = M('Depart')->where('is_del=1 and depart_par_id='.$search['depart_id'])->getField('depart_id',true);
            if($depart_ids){
                $map['depart_id'] = array('in',$depart_ids);
            }else{
                $map['depart_id'] = $search['depart_id'];
            }
        }
        $mid = $search['mid'];
        if($mid){
            $map['mid'] = $mid;
        }else{
            $map['company_id'] = session('company_id');
        }
        return $map;
    }

 

    /**
     * berry.qi
     * 记录数据保存
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
     * 记录数据保存
     * 2016-04-19
     */
    public function saveByMainAction()
    {
        $data = I('post.data');
        $arr['code'] = 0;
        $arr['msg'] = '操作失败！';
        $officeSuppliesModel = M('OfficeSupplies');
        //生成申领记录
        $i = 0;
        $sumprice = 0;
        $ids = array();
        if($data){
            //计算总价
            foreach($data as $key=>$value){
                $goods = $officeSuppliesModel->find($value['id']);
                if($goods['inventory']>=$value['num']) {
                    $sumprice += $goods['price'] * $value['num'];
                }
            }
//            echo $officeSuppliesModel->getLastSql();exit;
            //main表数据
            $maindata['real_name'] = session('real_name');
            $maindata['company_id'] = session('company_id');
            $maindata['all_price'] = $sumprice;
            $maindata['depart_id'] = session('depart_id');
            $maindata['crt_user_id'] = $this->user_id;
            $maindata['crt_time'] = time();
            $maindata['no'] = $orderno = 'BGP'.date("YmdHis");
            $mid = M('OfficeSuppliesMain')->add($maindata);
            if($mid){
                foreach($data as $key=>$value){
                    //生成记录&减掉库存
                    $goods = $officeSuppliesModel->find($value['id']);
                    if($goods['inventory']>=$value['num']){
                        $savedata['mid'] = $mid;
                        $savedata['crt_user_id'] = $this->user_id;
                        $savedata['company_id'] = session('company_id');
                        $savedata['crt_time'] = time();
                        $savedata['sid'] = $value['id'];
                        $savedata['num'] = $value['num'];
                        $savedata['real_name'] = session('real_name');
                        $savedata['cur_price'] = $goods['price'];
                        $savedata['depart_id'] = session('depart_id');
                        $id = $this->model->insert($savedata);
                        if($id){
                            //减库存
                            $officeSuppliesModel->where('id='.$value['id'])->setDec('inventory',$value['num']);
                            $i++;
                        }
                    }
                }
                if($i>0){
                    $arr['code'] = 1;
                    $arr['msg'] = '操作成功！';
                    $arr['mid'] = $mid;
                    $logstatus = 1;
                    logrecords('insert','OfficeSuppliesMain',$logstatus);
                }
            }

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
    

    public function changestatusAction(){
        $is_admin = is_admin_power();
        if(!$is_admin  && $this->user_id!=172 && $this->user_id!=173 && $this->depart_id!=42 && $this->user_id!=353){
            $arr['code'] = -1;
            $arr['msg'] = '无权限操作！';
            $this->ajaxReturn($arr);
        }
        $id = intval(I('post.id'));
        $arr['code'] = 0;
        $arr['msg'] = '设置失败';
        if($id){
            $map['id'] = $id;
            $map['company_id'] = session('company_id');
            $res = $this->model->update(array('get_status'=>1,'deal_uid'=>$this->user_id),$map);
            if($res){
                $arr['code'] = 1;
                $arr['msg'] = '设置成功';
            }
        }
        $this->ajaxReturn($arr);
    }
}