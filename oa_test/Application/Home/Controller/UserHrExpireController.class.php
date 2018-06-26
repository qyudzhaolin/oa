<?php
/**
 * OA 人员合同到期
 */
namespace Home\Controller;
class UserHrExpireController extends BaseController
{

    public $check_access = true;
    private $table = 'UserHrTry';
    public $pagesize = 20;
    public function __construct(){
        parent::__construct();
        $this->model = D('UserHrExpire');
    }
    
    /**
     * 试用期列表记录
     */
    public function indexAction(){
        $user_id = session('user_id');
        $map['is_del'] = 1;
        $where['l_uid'] = $user_id;
        $where['h_uid'] = $user_id;
        $where['p_uid'] = $user_id;
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
        $t_user_id = intval(I('user_id'));
        $user_id = session('user_id');
        $map['is_del'] = 1;
        $map['user_id'] = $t_user_id;
        
        $info = $this->model->where($map)->find();
        if($info && ($info['l_uid']==$user_id || $info['h_uid']==$user_id || $info['p_uid']==$user_id)){
            $user = M("user")->where(['user_id'=>$t_user_id])->find();
            $user_hr = M("user_hr")->where(['uid'=>$t_user_id])->find();
            
            $this->assign('info', $info);
            $this->assign("user_hr",$user_hr);
            $this->assign("user",$user);
            $this->display('add');
        }
    }

    public function saveAction()
    {
        $arr['status'] = 1;
        $arr['msg'] = '操作成功！';
        $data_p = I('post.');
        $user_id = $data_p['user_id'];
        $map['is_del'] = 1;
        $map['user_id'] = $user_id;
        
        $info = $this->model->where($map)->find();
        if($info){
            $data_p['udate'] = date('Y-m-d H:i:s');
            $id = $this->model->where($map)->save($data_p);
            if(!$id){
                $arr['status'] = 0;
                $arr['msg'] = '操作失败，请重新提交！';
            }else{
                if($data_p['contract_over_date']){
                    M("user_hr")->where(['uid'=>$user_id])->save(array('contract_over_date'=>$data_p['contract_over_date']));
                }
                $expire = $this->model->where($map)->find();
                $user = M("user")->where(['user_id'=>$user_id])->find();
                if($data_p['l_status']==2){
                    send_email_approve($expire['h_uid'], $user['real_name'].'合同到期审批不通过', $user['real_name']."合同到期审批不通过", U("UserHrExpire/add",array('user_id'=>$user_id)));
                }elseif($data_p['p_status']==2){
                    if($expire['l_uid']){
                        send_email_approve($expire['l_uid'], $user['real_name'].'合同到期审批不通过', $user['real_name']."合同到期审批不通过", U("UserHrExpire/add",array('user_id'=>$user_id)));
                    }
                    send_email_approve($expire['h_uid'], $user['real_name'].'合同到期审批不通过', $user['real_name']."合同到期审批不通过", U("UserHrExpire/add",array('user_id'=>$user_id)));
                }elseif($data_p['h_status']==2){
                    if($expire['l_uid']){
                        send_email_approve($expire['l_uid'], $user['real_name'].'合同到期审批不通过', $user['real_name']."合同到期审批不通过", U("UserHrExpire/add",array('user_id'=>$user_id)));
                    }
                    send_email_approve($expire['p_uid'], $user['real_name'].'合同到期审批不通过', $user['real_name']."合同到期审批不通过", U("UserHrExpire/add",array('user_id'=>$user_id)));
                }
            }
        }
            
        $this->ajaxReturn($arr);
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