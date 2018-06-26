<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class UserHrFlowController extends BaseController
{

    public $check_access = true;
    private $table = 'UserHrFlow';
    public $pagesize = 20;
    public function __construct()
    {
        parent::__construct();
        $this->model = D('UserHrFlow');
    }
    /**
     * Litter7
     * 办公用品在线申请
     * 2017-04-12
     */
    public function indexAction()
    {
        $access = check_permission_left('UserHr','index');
        if(!$access){
            $this->error('您没有该操作权限');exit;
        }
        $search = I('');
        $map = $this->mapFormat($search);
        $map['is_del'] = 1;
        $map['company_id'] = session('company_id');
        $catModel = M('OfficeSuppliesCategory');
        $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');
        $list = $this->model->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
        $fileModel = M('File');
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
            if($val['file_id']>0){
                $file_name = $fileModel->where('file_id='.$val['file_id'])->getField('file_name');
                $list[$key]['file_name'] = $file_name;
                $list[$key]['file_url'] = C('IMG_DOMAIN').$file_name;
            }
        }
//        dump($list);
        $show = $Page->show();
        $this->assign('search',I(''));
        $this->assign('page',$show);
        $this->assign('add',$access);
        $this->assign('list',$list);
        $this->assign('head_title', '招聘信息');
        $this->display('index');
    }
    protected function mapFormat($search=array()){
        $name = trim($search['name']);
        if($name){
            $map['name'] = array('like',"%$name%");
        }
        $phone = trim($search['phone']);
        if($phone){
            $map['phone'] = array('like',"%$phone%");
        }
        $job = trim($search['job']);
        if($job){
            $map['job'] = array('like',"%$job%");
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
        $access = check_permission_left('UserHr','add');
        if(!$access){
            $this->error('您没有该操作权限');exit;
        }
        $fileModel = M('File');
        $id = intval(I('id'));
        if($id>0){
            $map['is_del'] = 1;
            $map['id']=$id;
            $info = $this->model->get_onebywhere($map);
            if($info['file_id']>0){
                $file_name = $fileModel->where('file_id='.$info['file_id'])->getField('file_name');
                $info['file_name'] = $file_name;
//                $info['file_url'] = C('IMG_DOMAIN').$file_name;
            }
            $this->assign('info',$info);
        }
        //分类
        //部门信息
        $departments = D('Depart')->where('is_del=1 and company_id='.session('company_id'))->order('depart_id asc')->select();
        $departments = formatToTree($departments, $html = '--', $pid = 0, $level = 0,$pidname='depart_par_id',$idname = 'depart_id');
        $this->assign('head_title', '新增招聘信息');
        $this->assign('departments',$departments);
        $this->display('add');
    }

    /**
     * berry.qi
     * 供应商数据保存
     * 2016-04-19
     */
    public function saveAction()
    {
        $access = check_permission_left('UserHr','add');
        if(!$access){
            $arr['code'] = -1;
            $arr['msg'] = '无权限操作！';
            $this->ajaxReturn($arr);
        }
        $arr['code'] = 1;
        $arr['msg'] = '操作成功！';
        $data = $_POST;
        $itvModel = M('UserInterview');
        if ($this->model->create()===false) {
            $arr['code'] = 0;
            $arr['msg'] = $this->model->getError();
        }else{
//            dump($data);exit;
            $data['company_id'] = session('company_id');
            //保存审核信息
            $itvdata['salary'] = $data['salary'];
            $itvdata['subsidy'] = $data['subsidy'];
            $itvdata['advantage'] = $data['advantage'];
            $itvdata['disadvantage'] = $data['disadvantage'];
            $itvdata['result'] = $data['result'];
            $itvdata['status'] = $data['result_status'];
            unset($data['advantage']);
            unset($data['disadvantage']);
            if($data['id']){
                $hr_id = $data['id'];
                $data['mod_user_id'] = $this->user_id;
                $where['id'] = $data['id'];
                $id = $this->model->update($data,$where);
                $itvdata['mod_time'] = time();
                $itv_id = $itvModel->where("hr_id={$hr_id} and step=1")->save($itvdata);
//                echo $this->model->getLastSql();exit;
                $logtype = 'update';
            }else{
                $data['crt_user_id'] = $this->user_id;
                $id = $hr_id = $this->model->insert($data);
                $itvdata['hr_id'] = $id;
                $itvdata['step'] = 1;
                $itvdata['crt_time'] = time();
                $itv_id = $itvModel->add($itvdata);
                $logtype = 'insert';
            }
            if($id){
                $arr['code'] = 1;
                $arr['msg'] = '操作成功！';
                $logstatus = 1;
                //如果审核状态==1，则定义工作流
                if($itv_id>0 && $itvdata['status']==1){
                    $flowData['flow_uids'] = $data['user_ids'];
                    $flowData['depart_id'] = $data['depart_id'];
                }
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
        $access = check_permission_left('UserHr','del');
        if(!$access){
            $arr['code'] = -1;
            $arr['msg'] = '无权限操作！';
            $this->ajaxReturn($arr);exit;
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