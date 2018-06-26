<?php
/**
 * OA 人员试用期
 */
namespace Home\Controller;
class UserHrResignController extends BaseController
{

    public $check_access = true;
    private $table = 'UserHrTry';
    public $pagesize = 20;
    public function __construct(){
        parent::__construct();
    }
    
    /**
     * 试用期列表记录
     */
    public function indexAction(){
        $user_id = session('user_id');
        $map['is_del'] = 1;
        $where['leader_uid'] = $user_id;
        $where['hrg_uid'] = $user_id;
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        
        $name = trim(I('name'));
        if($name){
            $filter_uids = M("user")->where(array('real_name'=>array('like',"%$name%"), 'is_del'=>1))->getField("user_id",true);
            if($filter_uids){
                $map['user_id'] = array('in', $filter_uids);
            }else{
                $map['user_id'] = 0;
            }
        }
        
        $count = $this->model->where($map)->count();
        $Page = new \Think\Page($count,$this->pagesize);
        $show = $Page->show();
        $list = $this->model->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
        foreach ($list as &$row) {
            $user = M("user")->where(['user_id'=>$row['user_id']])->find();
            $depart = M("depart")->where(['depart_id'=>$user['depart_id']])->find();
            $user_hr = M("user_hr")->where(['uid'=>$row['user_id']])->find();
            $row['real_name'] = $user['real_name'];
            $row['depart_name'] = $depart['depart_name'];
            $row['job'] = $user_hr['job'];
        }
        
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->assign('head_title', '试用期信息');
        $this->display('index');
    }

    /**
     * 添加
     */
    public function addAction(){
        if(I('user_id')){
            $user_id = intval(I('user_id'));
        }else{
            $user_id = session('user_id');
        }
        $map['is_del'] = 1;
        $map['user_id'] = $user_id;
    
        $info = M('UserHrResign')->where($map)->find();
        if($info){
            $resign_leader = M("UserHrResignLeader")->where(['r_id'=>$info['id']])->find();
            $resign_hr = M("UserHrResignHr")->where(['r_id'=>$info['id']])->find();
            $this->assign('resign_leader', $resign_leader);
            $this->assign('resign_hr', $resign_hr);
            $this->assign('info', $info);
        }
        
        $user = M("user")->where(['user_id'=>$user_id])->find();
        $user_hr = M("user_hr")->where(['uid'=>$user_id])->find();
        $leader = M("user")->where(['user_id'=>$user['leader_uid']])->find();
        $depart = M("depart")->where(['depart_id'=>$user['depart_id']])->find();
        
        $this->assign('user', $user);
        $this->assign('user_hr', $user_hr);
        $this->assign('leader', $leader);
        $this->assign('depart', $depart);
        $this->assign('person_reson', C('RESIGN_PERSON_RESON'));
        $this->assign('company_reson', C('RESIGN_COMPANY_RESON'));
        $this->display('add');
    }
    
    /**
     * 领导添加
     */
    public function leader_addAction(){
        if(I('user_id')){
            $user_id = intval(I('user_id'));
            $map['is_del'] = 1;
            $map['user_id'] = $user_id;
            $resign = D('UserHrResign')->where($map)->find();
            if($resign['leader_uid'] != session('user_id')){
                redirect(U('Index/index'), 2, '您没有权限!');
            }
            $resign_leader = M("UserHrResignLeader")->where(['r_id'=>$resign['id']])->find();
            $this->assign('resign_leader', $resign_leader);
            
            $user = M("user")->where(['user_id'=>$user_id])->find();
            $user_hr = M("user_hr")->where(['uid'=>$user_id])->find();
            
            $this->assign('user', $user);
            $this->assign('user_hr', $user_hr);
            $this->assign('resign', $resign);
            $this->assign('person_reson', C('RESIGN_PERSON_RESON'));
            $this->assign('company_reson', C('RESIGN_COMPANY_RESON'));
            $this->assign('limit_item', C('RESIGN_LIMIT_ITEM'));
            $this->display('leader');
        }
    }
    
    /**
     * hr添加
     */
    public function hr_addAction(){
        if(I('user_id')){
            $user_id = intval(I('user_id'));
            $map['is_del'] = 1;
            $map['user_id'] = $user_id;
            $resign = D('UserHrResign')->where($map)->find();
            if($resign['hrg_uid'] != session('user_id')){
                redirect(U('Index/index'), 2, '您没有权限!');
            }
            $resign_hr = M("UserHrResignHr")->where(['r_id'=>$resign['id']])->find();
            $this->assign('resign_hr', $resign_hr);
    
            $user = M("user")->where(['user_id'=>$user_id])->find();
            $user_hr = M("user_hr")->where(['uid'=>$user_id])->find();
    
            $this->assign('user', $user);
            $this->assign('user_hr', $user_hr);
            $this->assign('resign', $resign);
            $this->assign('person_reson', C('RESIGN_PERSON_RESON'));
            $this->assign('company_reson', C('RESIGN_COMPANY_RESON'));
            $this->display('hr');
        }
    }

    /**
     * 部门负责人添加
     */
    public function principal_addAction(){
        if(I('user_id')){
            $user_id = intval(I('user_id'));
            $map['is_del'] = 1;
            $map['user_id'] = $user_id;
            $resign = D('UserHrResign')->where($map)->find();
            $user = M("user")->where(['user_id'=>$user_id])->find();
            $principal = get_depart_principal($user['depart_id']);
            if($principal['user_id'] != session('user_id')){
                redirect(U('Index/index'), 2, '您没有权限!');
            }
            $resign_leader = M("UserHrResignLeader")->where(['r_id'=>$resign['id']])->find();
            $resign_hr = M("UserHrResignHr")->where(['r_id'=>$resign['id']])->find();
    
            $user = M("user")->where(['user_id'=>$user_id])->find();
            $user_hr = M("user_hr")->where(['uid'=>$user_id])->find();
    
            $this->assign('user', $user);
            $this->assign('user_hr', $user_hr);
            $this->assign('resign', $resign);
            $this->assign('resign_leader', $resign_leader);
            $this->assign('resign_hr', $resign_hr);
            $this->assign('person_reson', C('RESIGN_PERSON_RESON'));
            $this->assign('company_reson', C('RESIGN_COMPANY_RESON'));
            $this->assign('limit_item', C('RESIGN_LIMIT_ITEM'));
            $this->display('principal');
        }
    }
    
    public function saveAction(){
        $data_p = I('post.');
        
        $user_id = intval($data_p['user_id']);
        if(!$user_id) $this->ajaxReturn(array('status'=>0,'msg'=>'参数不对'));
        $map['is_del'] = 1;
        $map['user_id'] = $user_id;
        
        $info = M('UserHrResign')->where($map)->find();
        
        //上传验证
        if(!$data_p['apply_date']) $this->ajaxReturn(array('status'=>0,'msg'=>'离职日期不能为空'));
        if(!$data_p['campay_reson']) $this->ajaxReturn(array('status'=>0,'msg'=>'公司原因至少选择一个'));
        if(!$data_p['person_reson']) $this->ajaxReturn(array('status'=>0,'msg'=>'个人原因至少选择一个'));
        
        $data_p['campay_reson'] = implode(",", $data_p['campay_reson']);
        $data_p['person_reson'] = implode(",", $data_p['person_reson']);
        
        if($info){
            $data_p['udate'] = date('Y-m-d H:i:s');
            $id = M('UserHrResign')->where($map)->save($data_p);
        }else{
            if($user_id != $this->user_id){
                $data_p['type'] = 2;                   //被动离职
            }
            $data_p['cdate'] = date('Y-m-d H:i:s');
            $data_p['user_id'] = $user_id;
            
            $user = M("user")->where(['user_id'=>$user_id])->find();
            $leader = M("user")->where(['user_id'=>$user['leader_uid']])->find();
            $hrg = get_hrg($user['depart_id']);
            if($hrg) $data_p['hrg_uid'] = $hrg['user_id'];
            if($leader) $data_p['leader_uid'] = $leader['user_id'];
            
            $id = M('UserHrResign')->where($map)->add($data_p);
            if($id){
                if($user_id != $this->user_id){
                    $content = $leader['real_name'].'对其下属'.$user['real_name']."作出了离职处理，申请离职日期为：".$data_p['apply_date'];                   //被动离职
                    send_email_approve($hrg['user_id'], $user['real_name'].'的离职处理', $content, U("UserHrResign/hr_add",array('user_id'=>$user_id)));
                }else{
                    $content = $user['real_name']."申请离职，申请离职日期为：".$data_p['apply_date'];                   //主动离职
                    send_email_approve($hrg['user_id'], $user['real_name'].'的离职申请', $content, U("UserHrResign/hr_add",array('user_id'=>$user_id)));
                    send_email_approve($leader['user_id'], $user['real_name'].'的离职申请', $content, U("UserHrResign/leader_add",array('user_id'=>$user_id)));
                }
            }
        }
        if($id){
            $this->ajaxReturn(array('status'=>1));
        }
        $this->ajaxReturn(array('status'=>0,'msg'=>'操作失败，请重新提交'));
    }
    
    public function leader_saveAction(){
        $data_p = I('post.');
        $r_id = intval($data_p['r_id']);
        if(!$r_id) $this->ajaxReturn(array('status'=>0,'msg'=>'参数不对'));
        $map['r_id'] = $r_id;
        $info = M('UserHrResignLeader')->where($map)->find();
    
        if($data_p['campay_reson']) {
            $data_p['campay_reson'] = implode(",", $data_p['campay_reson']);
        }
        if($data_p['person_reson']) {
            $data_p['person_reson'] = implode(",", $data_p['person_reson']);
        }
        
        
        if($data_p['is_limit'] == 1){
            if($data_p['limit_reson']) {
                $data_p['limit_reson'] = implode(",", $data_p['limit_reson']);
            }
            $data_p['no_limit_reson'] = "";
        }else{
            $data_p['limit_reson'] = "";
            $data_p['other'] = "";
        }
        
        if($info){
            $data_p['udate'] = date('Y-m-d H:i:s');
            $id = M('UserHrResignLeader')->where($map)->save($data_p);
        }else{
            $data_p['cdate'] = date('Y-m-d H:i:s');
            $id = M('UserHrResignLeader')->where($map)->add($data_p);
        }
        
        if($id){
            $this->ajaxReturn(array('status'=>1));
        }
        
        $this->ajaxReturn(array('status'=>0,'msg'=>'操作失败，请重新提交'));
    }

    public function hr_saveAction(){
        $data_p = I('post.');
        $r_id = intval($data_p['r_id']);
        if(!$r_id) $this->ajaxReturn(array('status'=>0,'msg'=>'参数不对'));
        $map['r_id'] = $r_id;
        $info = M('UserHrResignHr')->where($map)->find();
    
        if($data_p['campay_reson']) {
            $data_p['campay_reson'] = implode(",", $data_p['campay_reson']);
        }
        if($data_p['person_reson']) {
            $data_p['person_reson'] = implode(",", $data_p['person_reson']);
        }
    
        if($info){
            $data_p['udate'] = date('Y-m-d H:i:s');
            $id = M('UserHrResignHr')->where($map)->save($data_p);
        }else{
            $data_p['cdate'] = date('Y-m-d H:i:s');
            $id = M('UserHrResignHr')->where($map)->add($data_p);
        }
    
        if($id){
            $info = M('UserHrResign')->where($map)->find();
            $user = M("user")->where(['user_id'=>$info['user_id']])->find();
            $principal = get_depart_principal($user['depart_id']);
            send_email_approve($principal['user_id'], $user['real_name'].'的离职处理', $user['real_name'].'的离职处理', U("UserHrResign/principal_add",array('user_id'=>$info['user_id'])));
            $this->ajaxReturn(array('status'=>1));
        }
    
        $this->ajaxReturn(array('status'=>0,'msg'=>'操作失败，请重新提交'));
    }
    
    public function principal_saveAction(){
        $data_p = I('post.');
        $r_id = intval($data_p['r_id']);
        if(!$r_id) $this->ajaxReturn(array('status'=>0,'msg'=>'参数不对'));
        $map['id'] = $r_id;
        $info = M('UserHrResign')->where($map)->find();
    
        if($info){
            $data_p['udate'] = date('Y-m-d H:i:s');
            $id = M('UserHrResign')->where($map)->save($data_p);
            if($id){
                //$user = M("user")->where(['user_id'=>$info['user_id']])->find();
                //send_email_approve($principal['user_id'], $user['real_name'].'的离职处理', $user['real_name'].'的离职处理', U("UserHrResign/principal_add",array('user_id'=>$info['user_id'])));
                $this->ajaxReturn(array('status'=>1));
            }
        }
    
        $this->ajaxReturn(array('status'=>0,'msg'=>'操作失败，请重新提交'));
    }
    
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