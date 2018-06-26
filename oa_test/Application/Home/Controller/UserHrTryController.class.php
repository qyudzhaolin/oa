<?php
/**
 * OA 人员试用期
 */
namespace Home\Controller;
class UserHrTryController extends BaseController
{

    public $check_access = true;
    private $table = 'UserHrTry';
    public $pagesize = 20;
    public function __construct(){
        parent::__construct();
        $this->model = D('UserHrTry');
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
        $id = intval(I('id'));
        $user_id = session('user_id');
        $map['is_del'] = 1;
        if($id>0){
            $map['id'] = $id;
        }else{
            $map['user_id'] = $user_id;
        }
        
        $info = $this->model->where($map)->find();
        if($info){
            if($info['user_id'] != $user_id){
                $user_id = $info['user_id'];
            }
            
            $leader = M("user")->where(['user_id'=>$info['leader_uid']])->find();
            $hrg = M("user")->where(['user_id'=>$info['hrg_uid']])->find();
            $this->assign('leader', $leader);
            $this->assign('hrg', $hrg);
        }
        
        $user_hr = M("user_hr")->where(['uid'=>$user_id])->find();
        $is_experience = false;
        if($user_hr && strtotime($user_hr['try_over_date']." -15days")<=strtotime(date('Y-m-d')) && strtotime(date('Y-m-d'))<=strtotime($user_hr['try_over_date'])){
            $is_experience = true;
        }
        
        $this->assign('info', $info);
        $this->assign("user_hr",$user_hr);
        $this->assign('is_experience', $is_experience);
        $this->display('add');
    }

    public function saveAction()
    {
        $arr['status'] = 1;
        $arr['msg'] = '操作成功！';
        $data_p = I('post.');
        $user_id = session('user_id');
        $map['is_del'] = 1;
        if($data_p['id']>0){
            $map['id'] = $data_p['id'];
        }else{
            $map['user_id'] = $user_id;
        }
        
        $info = $this->model->where($map)->find();
        if ($data_p['action'] == 'add') {
            if($info){
                $data['experience'] = $data_p['experience'];
                $id = $this->model->update($data,$map);
            }else{
                $data['experience'] = $data_p['experience'];
                $data['user_id'] = $this->user_id;
                
                $user = M("user")->where(['user_id'=>$user_id])->find();
                $leader = M("user")->where(['user_id'=>$user['leader_uid']])->find();
                $hrg = get_hrg($user['depart_id']);
                if($hrg) $data['hrg_uid'] = $hrg['user_id'];
                if($leader) $data['leader_uid'] = $leader['user_id'];
                
                $id = $this->model->insert($data);
                if($id){
                    send_email_approve($hrg['user_id'], $user['real_name'].'的试用期小结', $data['experience'], U("UserHrTry/add",array('id'=>$id)));
                    send_email_approve($leader['user_id'], $user['real_name'].'的试用期小结', $data['experience'], U("UserHrTry/add",array('id'=>$id)));
                }else{
                    $arr['status'] = 0;
                    $arr['msg'] = '操作失败，请重新提交！';
                }
            }
            
        }elseif($data_p['action'] == 'save'){
            if($data_p['leader_opinion']) $data['leader_opinion'] = $data_p['leader_opinion'];
            if($data_p['hrg_opinion']) $data['hrg_opinion'] = $data_p['hrg_opinion'];
            if($data_p['l_status']) $data['l_status'] = $data_p['l_status'];
            if($data_p['h_status']) $data['h_status'] = $data_p['h_status'];
            $id = $this->model->update($data,$map);
            if(!$id){
                $arr['status'] = 0;
                $arr['msg'] = '操作失败，请重新提交！';
            }else{
                if($data_p['leader_opinion']){
                    send_email_approve($info['user_id'], '你的主管对你的试用期进行了审核', $data_p['leader_opinion'], U("UserHrTry/add"));
                }elseif($data_p['hrg_opinion']){
                    send_email_approve($info['user_id'], 'HR对你的试用期进行了审核', $data_p['hrg_opinion'], U("UserHrTry/add"));
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

    public function testAction(){
        $this->display('UserHrResign:dimission');
    }
    
    public function test1Action(){
        $this->display('UserHrResign:leader');
    }
}