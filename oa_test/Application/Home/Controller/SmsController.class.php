<?php
/**
 * OA系统用户管理
 * 短信发送模块（Aliyun）
 */
namespace Home\Controller;
use Home\Model\AliSmsModel;
use Think\Validate;

class SmsController extends BaseController
{

    public $check_access = true;
    private $table = 'Sms';
    public $pagesize = 20;
    public function __construct()
    {
        parent::__construct();
        $this->model = D('Sms');
    }
    /**
     * Litter7
     * 短信发送列表
     * 2017-07-12
     */
    public function indexAction()
    {
        $access = check_permission_left('Sms','index');
        if(!$access){
            $this->error('您没有该操作权限');exit;
        }
        $search = I('');
        $map = $this->mapFormat($search);
        $map['status'] = array('in',array(0,1,2));
        $map['is_del'] = 1;
//        $map['company_id'] = session('company_id');
        $count = $this->model->where($map)->count();
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');
        $list = $this->model->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();

//        dump($list);
        $show = $Page->show();
        $this->assign('search',I(''));
        $this->assign('page',$show);
        $this->assign('add',$access);
        $this->assign('list',$list);
        $this->assign('head_title', '招聘信息');
        $this->display('index');
    }
    /*
     * showApi发送
     * $content={}
     */
    public function showApiSend($phone,$content,$tnum){
        header("Content-Type:text/html;charset=UTF-8");
        date_default_timezone_set("PRC");
        $return['status'] = 0;
        $return['error'] = 'null';
        $config = C('SMS_SHOWAPI_CONFIG');
        $showapi_appid = $config['app_appid'];  //替换此值,在官网的"我的应用"中找到相关值
        $showapi_secret = $config['app_secret'];  //替换此值,在官网的"我的应用"中找到相关值
        $paramArr = array(
            'showapi_appid'=> $showapi_appid,
            'mobile'=> $phone,
            'content'=> $content,
            'tNum'=> $tnum,//模板id
            'big_msg'=> ""
            //添加其他参数
        );
        $param = $this->createParam($paramArr,$showapi_secret);
        $url = $config['request_url'].'?'.$param;
//        $url = 'http://route.showapi.com/892-3?'.$param;
        $result = file_get_contents($url);
        $result = json_decode($result);
        if($result && $result['showapi_res_code']==0){
            $return['status'] = 1;
            $return['error'] = 'OK';
        }else{
            $return['status'] = -1;
            $return['error'] = $result['showapi_res_error']?$result['showapi_res_error']:'发送失败';
        }
        return $return;
    }

    public function sendAction(){
        //校验
        $access = check_permission_left('Sms','send');
        if(!$access){
            $this->error('您没有该操作权限');exit;
        }
        $postdata = I('post.');
        $phoneData = trim($postdata['phone']);
        $templateCode = trim($postdata['template_code']);
        $signName = C('SMS_ALI_CONFIG.sign_name');
        $validate = new Validate();
        if($postdata['target']=='mianshi'){
            $paramdata['name'] = $postdata['name'];
            $paramdata['name2'] = $postdata['hrname'].':'.$postdata['hrphone'];
            $paramdata['m'] = $postdata['date_m'];
            $paramdata['d'] = $postdata['date_d'];
            $paramdata['w'] = $postdata['date_w'];
            $paramdata['h'] = $postdata['date_h'];
//            $paramdata['hrphone'] = $postdata['hrphone'];
//            $paramdata['date'] = $postdata['date'];
        }elseif($postdata['target']=='onlyname'){
            $paramdata['name'] = $postdata['staff_name'];
        }elseif($postdata['target']=='verifycode'){
            $paramdata['code'] = $postdata['code'];
        }else{
            $paramdata['code'] = '1111';//默认code,不起任何作用
        }

        //先保存本次发送的信息
        if($phoneData){
            $postdata['crt_time'] = time();
            $postdata['crt_user_id'] = $this->user_id;
            $sms_id = $this->model->add($postdata);
        }
        $paramString = json_encode($paramdata);//'{"name":"XXXX"}','{"code":"12345"}';//
        $faillist = array();
        $i = 0;
        $AliSms = new \Home\Model\SmsModel();
//        dump($infos);
        if(trim($phoneData)){
            $multi_phone = explode(',',$phoneData);
            foreach($multi_phone as $phone){
                $fail = array();
                if($validate->is_mobile(trim($phone))){
                    $data['RecNum'] = trim($phone);
                    $send_result = $AliSms->sendAliApi($phone,$templateCode,$paramString,$signName);
                    if($send_result && $send_result['status']==1){
                        //发送成功后保存到日志
                        $log['sms_id'] = $sms_id;
                        $log['crt_time'] = time();
                        $log['phone'] = $phone;
                        M('SmsLog')->add($log);
                    }else{
                        $fail['phone'] = $phone;
                        $fail['error'] = $send_result['error'];
                        $i++;
                        $faillist[] = $fail;
                    }
                }else{
                    $i++;
                    $fail['phone'] = $phone;
                    $fail['error'] = '手机号格式错误';
                    $faillist[] = $fail;
                }
            }
        }else{
            $return['status'] = -1;
            $return['error'] = '请填写手机号';
        }
        if($i>0){
            $return['status'] = 0;
            $return['failList'] = $faillist;
        }else{
            $return['status'] = 1;
            $return['error'] = 'OK';
        }
        $this->ajaxReturn($return);
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
        $template_code = trim($search['template_code']);
        if($template_code){
            $map['template_code'] = $template_code;
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
        $access = check_permission_left('Sms','send');
        if(!$access){
            $this->error('您没有该操作权限');exit;
        }
        /*$fileModel = M('File');
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
        //初试信息
        $where['hr_id'] = $id;
        $where['step'] = 1;
        $itvinfo = M('UserInterview')->where($where)->find();
        $flowinfo = M('UserHrFlow')->where($where)->find();
//        echo M('UserHrFlow')->getLastSql();
//        dump($flowinfo);
        //部门信息
        $departments = D('Depart')->where('is_del=1 and company_id='.session('company_id'))->order('depart_id asc')->select();
        $departments = formatToTree($departments, $html = '--', $pid = 0, $level = 0,$pidname='depart_par_id',$idname = 'depart_id');*/
        $this->assign('head_title', '短信发送');
       /* $this->assign('departments',$departments);
        $this->assign('itvinfo',$itvinfo);
        $this->assign('flowinfo',$flowinfo);*/
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
        $arr['code'] = 0;
        $arr['msg'] = '操作成功！';
        $data = $_POST;
        $itvModel = M('UserInterview');
        $arr['error'] = $this->model->getError();
        if ($this->model->create()==false) {
            $arr['code'] = 0;
            $arr['error'] = 2;
            $arr['msg'] = $this->model->getError();
        }else{
//            dump($data);exit;
            $data['company_id'] = session('company_id');
            $data['status'] = $data['result_status'];
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
            if($data['id']){
                $hr_id = $data['id'];
                $data['mod_user_id'] = $this->user_id;
                $where['id'] = $data['id'];
                $id = $this->model->update($data,$where);
                $itvdata['mod_time'] = time();
                $intinfo = $itvModel->where("hr_id={$hr_id} and step=1")->find();
                if($intinfo){
                    $itv_id = $intinfo['id'];
                    $result_itv = $itvModel->where("hr_id={$hr_id} and step=1")->save($itvdata);
                }else{
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
                    //$arr['sql'] = $flowModel->getLastSql();
                    $flowData['flow_uids'] = $data['user_ids'];
                    $flowData['flow_depart_id'] = $data['depart_id'];
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
        $flowModel = D('UserHrFlow');
        $fileModel = M('File');
        $id = intval(I('id'));
        $map['id'] = $id;
        $map['is_del'] = 1;
        $info = $this->model->where($map)->find();
//        $this->model->getLastSql();
        if(empty($info) || empty($id)){
            $this->error('信息有误');exit;
        }
        if($info['file_id']>0){
            $file_name = $fileModel->where('file_id='.$info['file_id'])->getField('file_name');
            $info['file_name'] = $file_name;
            $info['file_url'] = C('IMG_DOMAIN').$file_name;
        }
        $this->assign('info',$info);
        //检索之前的面试信息
        //根据已经建立的工作流读取
        $itvmap['hr_id'] = $info['id'];
        $list = M('UserInterview')->where($itvmap)->order('id asc')->select();
        foreach($list as $key=>$val){
            if($val['itv_depart_id']){
                $list[$key]['department'] = $departModel->where('depart_id='.$val['itv_depart_id'])->getField('depart_name');
            }
        }
        $departments = D('Depart')->where('is_del=1 and company_id='.session('company_id'))->order('depart_id asc')->select();
        $departments = formatToTree($departments, $html = '--', $pid = 0, $level = 0,$pidname='depart_par_id',$idname = 'depart_id');
        $this->assign('head_title', '招聘详情');
        $this->assign('departments',$departments);
//        dump(count($list));
        $this->assign('list',$list);
        $this->assign('lastitvinfo',$list[count($list)-1]);
        //已经审核和没有权限 不可以审核
        $auditAccess = false;
        $currenflow = $flowModel->where('is_del=1 and hr_id='.$id)->order('id desc')->find();
        $currenuids = explode(',',$currenflow['flow_uids']);
        $access_audit = check_permission_left('UserHr','saveInterview');

        if(in_array($this->user_id,$currenuids) && $access_audit){
            $auditAccess = true;
        }
//        dump($info);
        $this->assign('auditAccess',$auditAccess);
        $this->display('detail');
    }
    public function saveInterviewAction(){
        $arr['code'] = 0;
        $arr['msg'] = '操作失败';
        $itvModel = D('UserInterview');
        $flowModel = D('UserHrFlow');
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
        $hrdata['pre_salary'] = $data['pre_salary'];
        $hrdata['pre_subsidy'] = $data['pre_subsidy'];
        $hrdata['pre_salary_num'] = $data['pre_salary_num'];
        $hrdata['pre_year_bonus'] = $data['pre_year_bonus'];
        $hrdata['salary'] = $data['salary'];
        $hrdata['subsidy'] = $data['subsidy'];
        $hrdata['salary_num'] = $data['salary_num'];
        $hrdata['remark'] = $data['remark'];
        $itv_id = $itvModel->add($data);
        if($itv_id){
            //更新主表的状态
            $this->model->where('id='.$hr_id)->save($hrdata);
            $arr['code'] = 1;
            if($data['status']==1){
                //推送至下一步
                $flowData['flow_uids'] = $data['user_ids'];
                $flowData['flow_depart_id'] = $data['depart_id'];
                $flowData['step'] = $nextstep;
                $flowData['hr_id'] = $hr_id;
                $flowData['itv_id'] = $itv_id;
                $flowModel->insert($flowData);
            }
        }
        $this->ajaxReturn($arr);
    }

    /**
     * 已完成面试可发offer的列表
     */
    public function offerlistAction()
    {
        $access = check_permission_left('UserHr','offerlist');
        if(!$access){
            $this->error('您没有该操作权限');exit;
        }
        $search = I('');
        $map = $this->mapFormat($search);
        $map['status'] = 3;//已完成面试，offer审核过程
        $map['is_del'] = 1;
        $map['company_id'] = session('company_id');
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
        $this->assign('head_title', 'Offer人员信息');
        $this->display('offerlist');
    }

}