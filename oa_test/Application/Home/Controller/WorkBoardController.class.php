<?php
namespace Home\Controller;

class WorkBoardController extends BaseController{
    
    public function indexAction(){
        //待入职列表
        //待入职列表
        $userHrModel = M('UserHr');
        $departModel = M('Depart');
        $maphr['flow_type'] = 5;
        $is_admin = is_admin_power();
        if($is_admin){
            $maphr['_string'] = "hr_deal_id=0 OR xz_deal_id=0";
        }else{
            if($this->depart_id==60){
                //人事
                $maphr['hr_deal_id'] = 0;
            }elseif($this->depart_id==61){
                //行政
                $maphr['xz_deal_id'] = 0;
            }else{
                //禁止查看列表
                $maphr['id'] = array('lt',0);
            }

        }
        $list = $userHrModel->where($maphr)->order('id desc')->limit(10)->select();
        if($list){
            foreach($list as $key=>$val){
                //按分类重组数组
                if($val['file_id']>0){
                    $list[$key]['depart'] = $departModel->where('depart_id='.$val['depart_id'])->getField('depart_name');
                }
            }
        }
        
        //试用期到期列表
        $tryList = M('UserHrTry')->where("is_del=1 and (l_status=0 or h_status=0)")->order('id desc')->limit(10)->select();
        foreach ($tryList as &$row) {
            $user = M("user")->where(['user_id'=>$row['user_id']])->find();
            $depart = M("depart")->where(['depart_id'=>$user['depart_id']])->find();
            $user_hr = M("user_hr")->where(['uid'=>$row['user_id']])->find();
            $row['real_name'] = $user['real_name'];
            $row['depart_name'] = $depart['depart_name'];
            $row['job'] = $user_hr['job'];
            $row['try_over_date'] = $user_hr['try_over_date'];
            $row['status_name'] = '待直线主管处理';
            if($row['h_status'] == 0){
                $row['status_name'] = '待HRG处理';
            }
        }
        
        //合同到期列表
        $expireList = M('UserHrExpire')->where("is_del=1 and (l_status=0 or h_status=0 or p_status=0)")->order('id desc')->limit(10)->select();
        foreach ($expireList as &$row1) {
            $user = M("user")->where(['user_id'=>$row1['user_id']])->find();
            $depart = M("depart")->where(['depart_id'=>$user['depart_id']])->find();
            $user_hr = M("user_hr")->where(['uid'=>$row1['user_id']])->find();
            $row1['real_name'] = $user['real_name'];
            $row1['depart_name'] = $depart['depart_name'];
            $row1['job'] = $user_hr['job'];
            $row1['contract_over_date'] = $user_hr['contract_over_date'];
            $row1['status_name'] = '待直线主管处理';
            if($row1['p_status'] == 0){
                $row1['status_name'] = '待部门负责人处理';
            }elseif($row1['h_status'] == 0){
                $row1['status_name'] = '待HRG处理';
            }
        }
        
        $this->assign('expireList',$expireList);
        $this->assign('tryList',$tryList);
        $this->assign('list', $list);
        $this->assign('head_title', '工作台');
        $this->display();
    }
    public function listAction(){
        //待入职列表
        //待入职列表
        $userHrModel = M('UserHr');
        $departModel = M('Depart');
        $maphr['flow_type'] = 5;
        $is_admin = is_admin_power();
        if($is_admin){
            $maphr['_string'] = "hr_deal_id=0 OR xz_deal_id=0";
        }else{
            if($this->depart_id==60){
                //人事
                $maphr['hr_deal_id'] = 0;
            }elseif($this->depart_id==61){
                //行政
                $maphr['xz_deal_id'] = 0;
            }else{
                //禁止查看列表
                $maphr['id'] = array('lt',0);
            }

        }
        $count = $userHrModel->where($maphr)->count();
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');
        $list = $userHrModel->where($maphr)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        if($list){
            foreach($list as $key=>$val){
                //按分类重组数组
                if($val['file_id']>0){
                    $list[$key]['depart'] = $departModel->where('depart_id='.$val['depart_id'])->getField('depart_name');
                }
            }
        }
        $show = $Page->show();
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->assign('head_title', '工作台');
        $this->display();
    }
    public function hrdealAction(){
        $data['hr_id'] = $hr_id = I('post.hr_id');
        $data['remark'] = I('post.remark');
        $data['status'] = I('post.status');
        $data['crt_time'] = time();
        $data['deal_uid'] = $this->user_id;
        $data['type'] = $type = I('post.type');
        $return['code'] = 0;
        $return['msg'] = '上传失败';
        if($hr_id){
            $dealModel = M('userHrDeal');
            $id = $dealModel->add($data);
            if($id){
                //更新hr表的deal_id
                if($type==1 && $this->depart_id==60){
                    //人事
                    $field = 'hr_deal_id';
                }
                if($type==2 && $this->depart_id==61){
                    //行政
                    $field = 'xz_deal_id';
                }
                $res = M('UserHr')->where('id='.$hr_id)->setField($field,$id);
                if($res){
                    $return['code'] = 1;
                    $return['msg'] = '上传成功';
                }

            }
        }
        $this->ajaxReturn($return);
    }

}