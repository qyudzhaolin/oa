<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class UserHrController extends BaseController
{

    public $check_access = true;
    private $table = 'UserHr';
    public $pagesize = 20;
    public $host = "http://oatest.max-digital.cn/index.php";
    public function __construct()
    {
        parent::__construct();
        $this->model = D('UserHr');
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
        //hr可以看到自己创建的所有记录，审核者可以看到流转到自己的记录
        if($this->depart_id==12 || $this->depart_id==60){
            $map['crt_user_id'] = $this->user_id;
        }else{
            $map['flow_current_uid'] = $this->user_id;
        }
        $map['is_del'] = 1;
        $map['company_id'] = session('company_id');
        $map['flow_type'] = array('in',array(0,1));
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
    public function offerAction()
    {
        $access = check_permission_left('UserHr','offer');
        if(!$access){
            $this->error('您没有该操作权限');exit;
        }
        $search = I('');
        $map = $this->mapFormat($search);
        if($this->depart_id==12 || $this->depart_id==60){
//            $map['crt_user_id'] = $this->user_id;
            $map['_string'] = "crt_user_id ={$this->user_id} or flow_current_uid={$this->user_id}";
        }else{
            $map['flow_current_uid'] = $this->user_id;
        }
        $map['is_del'] = 1;
        $map['company_id'] = session('company_id');
        $map['flow_type'] = array('in',array(2,3,4,5));
        $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');
        $list = $this->model->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
//        echo $this->model->getLastSql();
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
        $this->display('offerlist');
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
            $where['hr_id'] = $id;
            $where['step'] = 1;
            $itvinfo = M('UserInterview')->where($where)->find();
            $flowinfo = M('UserHrFlow')->where($where)->find();
        }
        //初试信息

//        echo M('UserHrFlow')->getLastSql();
//        dump($flowinfo);
        //部门信息
        $departments = D('Depart')->where("is_del=1 and hrg_uid={$this->user_id} and company_id=".session('company_id'))->order('depart_id asc')->select();
        $departments = formatToTree($departments, $html = '--', $pid = 0, $level = 0,$pidname='depart_par_id',$idname = 'depart_id');
        $this->assign('head_title', '新增招聘信息');
        $this->assign('departments',$departments);
        $this->assign('info',$info);
        $this->assign('itvinfo',$itvinfo);
        $this->assign('flowinfo',$flowinfo);
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
            $data['company_id'] = session('company_id');
            //保存审核信息
            $itvdata['salary'] = $data['salary'];
            $itvdata['subsidy'] = $data['subsidy'];
            $itvdata['advantage'] = $data['advantage'];
            $itvdata['disadvantage'] = $data['disadvantage'];
            $itvdata['result'] = $data['result'];
            $itvdata['status'] = $data['result_status'];
            $itvdata['itv_uid'] = $this->user_id;
            $itvdata['itv_depart_id'] = $this->depart_id;
            $itvdata['next_date'] = $data['next_date'];
            unset($data['advantage']);
            unset($data['disadvantage']);
            $data['flow_current_uid'] = $data['user_ids'];
            if($data['id']){
//                dump($data);
                $hr_id = $data['id'];
                $data['mod_user_id'] = $this->user_id;
                $where['id'] = $data['id'];
                $id = $this->model->update($data,$where);
                $itvdata['mod_time'] = time();
                $itvdata['hr_id'] = $hr_id;
                $itvupmap['hr_id'] = $hr_id;
                $itvupmap['step'] = 1;
                $itvupmap['itv_uid'] = $this->user_id;//只能修改自己的记录
                $intinfo = $itvModel->where($itvupmap)->find();
                if($intinfo){
                    $itv_id = $intinfo['id'];
                    $itvdata['mod_user_id'] = $this->user_id;
                    $itvdata['mod_time'] = time();
                    $result_itv = $itvModel->where($intinfo)->save($itvdata);
                }else{
                    $itvdata['crt_user_id'] = $this->user_id;
                    $itvdata['crt_time'] = time();
                    $itv_id = $result_itv = $itvModel->add($itvdata);
                }
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
                    $flowModel = D('UserHrFlow');
                    //是否定义过该阶段工作流
                    $wheref['itv_id'] = $itv_id;
                    $wheref['hr_id'] = $hr_id;
                    $wheref['step'] = 1;
                    $flowinfo = $flowModel->where($wheref)->find();
                   // $arr['sql'] = $flowModel->getLastSql();
                    $flowData['next_uids'] = $data['user_ids'];
                    $flowData['next_depart_id'] = $data['depart_id'];
                    $flowData['step'] = 1;
                    $flowData['hr_id'] = $hr_id;
                    $flowData['itv_id'] = $itv_id;
                    if($flowinfo){
                        $flowModel->update($flowData,$wheref);
                    }else{
                        $flowModel->insert($flowData);
                    }
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
    public function detailAction(){
        $access = check_permission_left('UserHr','detail');
        if(!$access){
            $this->error('您没有该操作权限');exit;
        }
        $departModel = D('Depart');
        $userModel = D('User');
        $flowModel = D('UserHrFlow');
        $fileModel = M('File');
        $id = intval(I('id'));
        $type = intval(I('type'));
        $map['id'] = $id;
        $map['is_del'] = 1;
        $info = $this->model->where($map)->find();
//        $this->model->getLastSql();
        if(empty($info) || empty($id)){
            $this->error('信息有误');exit;
        }
        $auditAccess = false;
        if($info['file_id']>0){
            $file_name = $fileModel->where('file_id='.$info['file_id'])->getField('file_name');
            $info['file_name'] = $file_name;
            $info['file_url'] = C('IMG_DOMAIN').$file_name;
        }
        $this->assign('info',$info);
        //检索之前的面试信息
        //根据已经建立的工作流读取
        $itvmap['hr_id'] = $info['id'];
        $itvmap['type'] = $type;
        $list = M('UserInterview')->where($itvmap)->order('id asc')->select();
        foreach($list as $key=>$val){
            if($val['itv_depart_id']){
                $list[$key]['department'] = $departModel->where('depart_id='.$val['itv_depart_id'])->getField('depart_name');
            }
        }

        //已经审核和没有权限 不可以审核

        $currenflow = $flowModel->where('is_del=1 and hr_id='.$id)->order('id desc')->find();
        $currenuids = explode(',',$currenflow['next_uids']);
        $access_audit = check_permission_left('UserHr','saveInterview');

       /* if(in_array($this->user_id,$currenuids) && $access_audit){
            $auditAccess = true;
        }*/
        if($this->user_id==$info['flow_current_uid'] && $access_audit){
            $auditAccess = true;
        }
        if($auditAccess){
            //读取当前用户的上一级领导 人事部可以看到所有部门 审核者直接推送到上一级
            if($this->depart_id==12 || $this->depart_id==60){
                $dptmap['is_del'] = 1;
                $dptmap['company_id'] = session('company_id');
                if($type==1){
                    $dptmap['hrg_uid'] = $this->user_id;
                }else{
                    $dptmap['depart_id'] = array('in',array(12,35,60));//人事 总办（lester）
                }
                $departments = $departModel->where($dptmap)->order('depart_id asc')->select();
                $departments = formatToTree($departments, $html = '--', $pid = 0, $level = 0,$pidname='depart_par_id',$idname = 'depart_id');
            }else{
                $userinfo = $userModel->where('user_id='.$this->user_id)->find();
                $leader_uid = $userinfo['leader_uid'];
                if($leader_uid){
                    $join = "left join max_depart as d on d.depart_id = u.depart_id";
                    $departments = $userModel->alias('u')->join($join)->field('u.user_id,u.user_name,u.real_name,d.depart_name,d.depart_id')->where("u.user_id={$leader_uid}")->find();
                }
                $departments['is_only_one'] = true;

            }
//            dump($departments);
            $this->assign('departments',$departments);
        }
        $this->assign('head_title', '招聘详情');
//        dump($list);
        $this->assign('list',$list);
        $this->assign('lastitvinfo',$list[count($list)-1]);
//        dump($info);
        $this->assign('auditAccess',$auditAccess);
        if($type==2){
            $this->display('offerdetail');
        }else{
            $this->display('detail');
        }
    }
    public function saveInterviewAction(){
        $arr['code'] = 0;
        $arr['msg'] = '操作失败';
        $itvModel = D('UserInterview');
        $flowModel = D('UserHrFlow');
        $userhrModel = D('UserHr');
        $data = I('post.');
        $hr_id = $data['hr_id'];
        //最后审核步骤的状态
        $map['hr_id'] = $hr_id;
        $lastinfo = $itvModel->where($map)->order('id desc')->find();
        $step = $lastinfo['step'];
        $data['status'] = $data['result_status'];
        $data['itv_uid'] = $this->user_id;
        $data['itv_depart_id'] = $this->depart_id;
        $data['step'] = $nextstep =  $step+1;
        $data['crt_time'] = time();
        unset($data['id']);
        $itv_id = $itvModel->add($data);
        $userhrInfo = $userhrModel->where('id='.$hr_id)->find();
        if($itv_id){
            $type = 1;//默认的为1 面试流程
            $flow_current_uid = $userhrInfo['flow_current_uid'];
            $arr['code'] = 1;
            $arr['msg'] = '操作成功';
            if($data['status']==1){
                //推送至下一步
                $flowData['next_uids'] = $data['user_ids'];
                $flowData['next_depart_id'] = $data['depart_id'];
                $flowData['step'] = $nextstep;
                $flowData['hr_id'] = $hr_id;
                $flowData['itv_id'] = $itv_id;
                $flowData['type'] = $data['type'];
                $flow_current_uid = $data['user_ids'];
                $flowModel->insert($flowData);
                //邮件内容
                $to_uid = $data['user_ids'];
                $email_content = "HR为您物色了一个{$userhrInfo['job']}岗位的新成员，请至OA-招聘管理填写面试信息~";
            }elseif($data['status']==6){
                $email_content = "您创建的{$userhrInfo['name']}{$userhrInfo['job']}岗位面试已经完成面试，请至OA-招聘管理模块查看具体信息~";
                //触发流程终结操作进入offer流程&更新主表的flow_type状态
                $type = 2;
                $to_uid = $userhrInfo['crt_user_id'];
                $flow_current_uid = $userhrInfo['crt_user_id'];
//                $userhrModel->where('id='.$hr_id)->setField('flow_type',$type);
            }elseif($data['status']==-1){
                $to_uid = $userhrInfo['crt_user_id'];
                $flow_current_uid = $userhrInfo['crt_user_id'];
                $email_content = "您创建的{$userhrInfo['name']}{$userhrInfo['job']}岗位面试因{$data['result']}被退回，请至OA-招聘管理模块查看具体信息~";
            }
            //发送邮件
            if($to_uid){
//                send_email_bytoid($to_uid,'面试通知',$email_content);
                send_email_bytoid(138,'面试通知',$email_content);
            }
            //如果是审批流程 则更新主表的薪资信息
            $userhrData['mod_time'] = time();
            $userhrData['flow_current_uid'] = $flow_current_uid;
            $userhrData['flow_type'] = $type;
            $userhrModel->where('id='.$data['hr_id'])->save($userhrData);
        }
        $this->ajaxReturn($arr);
    }
    //offer审批
    public function saveOfferInterviewAction(){
        $arr['code'] = 0;
        $arr['msg'] = '操作失败';
        $itvModel = D('UserInterview');
        $flowModel = D('UserHrFlow');
        $userhrModel = D('UserHr');
        $data = I('post.');
        $hr_id = $data['hr_id'];
        //最后审核步骤的状态
        $map['hr_id'] = $hr_id;
        $lastinfo = $itvModel->where($map)->order('id desc')->find();
        $step = $lastinfo['step'];
        $data['status'] = $data['result_status'];
        $data['itv_uid'] = $this->user_id;
        $data['itv_depart_id'] = $this->depart_id;
        $data['step'] = $nextstep =  $step+1;
        $data['crt_time'] = time();
        unset($data['id']);
        $itv_id = $itvModel->add($data);
        $userhrInfo = $userhrModel->where('id='.$hr_id)->find();
        if($itv_id){
            $type = 2;//默认的为2 审批流程
            $flow_current_uid = $userhrInfo['flow_current_uid'];
            $arr['code'] = 1;
            $arr['msg'] = '操作成功';
            if($data['status']==1){
                //推送至下一步
                $flowData['next_uids'] = $data['user_ids'];
                $flowData['next_depart_id'] = $data['depart_id'];
                $flowData['step'] = $nextstep;
                $flowData['hr_id'] = $hr_id;
                $flowData['itv_id'] = $itv_id;
                $flowData['type'] = $data['type'];
                $flow_current_uid = $data['user_ids'];
                $flowModel->insert($flowData);
                //邮件内容
                $to_uid = $data['user_ids'];
                $email_content = "您的OA系统有待审批的offer，请尽快登陆OA-招聘管理进行处理~";
            }elseif($data['status']==6){
                $email_content = "您创建的{$userhrInfo['name']}{$data['job']}的Offer审批已经完成，请至OA-招聘管理模块查看具体信息~";
                //触发流程终结操作进入offer流程&更新主表的flow_type状态
                $to_uid = $userhrInfo['crt_user_id'];
                $flow_current_uid = $userhrInfo['crt_user_id'];
                $type = 3;//更新状态为已完成审批
            }elseif($data['status']==-1){
                $to_uid = $userhrInfo['crt_user_id'];
                $flow_current_uid = $userhrInfo['crt_user_id'];
                $email_content = "您创建的{$userhrInfo['name']}{$userhrInfo['job']}的Offer审批因{$data['result']}被退回，请至OA-招聘管理模块查看具体信息~";
            }
            if($to_uid){
//                send_email_bytoid($to_uid,'Offer审批通知',$email_content);
                send_email_bytoid(138,'Offer审批通知',$email_content);
            }
            //发送邮件
            //如果是审批流程 则更新主表的薪资信息
            $userhrData['mod_time'] = time();
            $userhrData['flow_type'] = $type;
            $userhrData['salary'] = $data['salary'];
            $userhrData['pre_salary'] = $data['pre_salary'];
            $userhrData['pre_salary_num'] = $data['pre_salary_num'];
            $userhrData['salary_num'] = $data['salary_num'];
            $userhrData['pre_year_bonus'] = $data['pre_year_bonus'];
            $userhrData['pre_subsidy'] = $data['pre_subsidy'];
            $userhrData['subsidy'] = $data['subsidy'];
            $userhrData['mod_user_id'] = $this->user_id;
            $userhrData['other_benefits'] = $data['other_benefits'];
            $userhrData['flow_current_uid'] = $flow_current_uid;
            $userhrModel->where('id='.$data['hr_id'])->save($userhrData);
        }
        $this->ajaxReturn($arr);
    }

    public function sendOfferAction(){
        $data = I('post.');
        $hr_id = $data['id'];
        //
        $return['code'] =0;
        $return['msg'] = '操作失败';
        if($hr_id){
            $userhrInfo = $this->model->where('id='.$hr_id)->find();
            if($userhrInfo && $userhrInfo['email']){
                $email = $userhrInfo['email'];
                $data['udate'] = time();
                $data['flow_type'] = 4;//已发送邮件
                $res = $this->model->where('id='.$hr_id)->save($data);
                if($res){
                    $return['code'] = 1;
                    $return['msg'] = '操作成功';
                    $content = "恭喜你通过我们公司的面试，现邀请您于{$data['entry_date']} 9:30到徐汇区长乐路989号3102公司总部办理入职手续，在此之前，务必请配合我们填写以下入职表格：{$this->host}/Home/outweb/acceptOffer?id={$hr_id}";
                    send_email($email,'入职通知',$content);
                }
            }
        }
        $this->ajaxReturn($return);
    }


}