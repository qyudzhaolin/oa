<?php
/**
 * 外部单页面模式
 * 无需登录OA 访问OA页面
 */
namespace Home\Controller;
use Think\Validate;

class OutwebController extends BaseController
{
    public $check_access = false;
    /*
     * offer接收页面，供用户信息反馈
     * Litter_7
     */
    public function acceptOfferAction(){
        layout(false);
        //读取后台已经设置的信息
        $id = I('get.id');
        if($id){
            $info = M('UserHr')->where('id='.$id)->find();
            if($info){
                if($info['depart_id']){
                    $info['depart'] = M('Depart')->where('depart_id='.$info['depart_id'])->getField('depart_name');
                }
                $otherinfo = M('UserOther')->where('hr_id='.$info['id'])->find();
                $otherinfo['family'] = unserialize($otherinfo['family_list']);
                $otherinfo['work'] = unserialize($otherinfo['work_list']);
                $otherinfo['other'] = unserialize($otherinfo['other']);
                $otherinfo['education'] = unserialize($otherinfo['study_list']);
                $otherinfo['language'] = unserialize($otherinfo['language_list']);
            }
        }else{
            $this->error('参数错误，请联系相关人员');
        }
        $this->assign('nowtime',time());
        $this->assign('entry_time',strtotime($info['entry_date']));
        $this->assign('info',$info);
        $this->assign('otherinfo',$otherinfo);
        $this->display('Userinfo:userinfo');
    }
    public function updateOfferAction(){
        $data = $_POST;
        $hr_id = $data['id'];
        $userHrModel = M('UserHr');
        $userOtherModel = M('UserOther');
        //信息表更新
        $base = $data['base'];
        $base['flow_type'] = 5;//已接受offer
        $userHrModel->where('id='.$hr_id)->save($base);
        //其他信息入库other表 字段均已serialize格式保存
        $otherinfo = $userOtherModel->where('hr_id='.$hr_id)->find();
        $otherdata['sign_name'] = $data['sign_name'];
        $otherdata['hr_id'] = $hr_id;
        $otherdata['study_list'] = serialize($data['education']);
        $otherdata['work_list'] = serialize($data['work']);
        $otherdata['family_list'] = serialize($data['family']);
        $otherdata['language_list'] = serialize($data['language']);
        $otherdata['other'] = serialize($data['other']);
        $otherdata['urgency_contact'] = $data['urgency_contact'];
        $otherdata['urgency_relation'] = $data['urgency_relation'];
        $otherdata['urgency_phone'] = $data['urgency_phone'];
        $otherdata['driving_licence'] = $data['driving_licence'];
        $otherdata['professional_cert'] = $data['professional_cert'];
        if($otherinfo){
            $otherdata['udate'] = time();
            $res = $userOtherModel->where('id='.$otherinfo['id'])->save($otherdata);
        }else{
            $otherdata['cdate'] = time();
            $res = $userOtherModel->add($otherdata);
        }
        if($res){
            $this->success('信息提交成功',U('/Home/Outweb/acceptOffer',array('id'=>$hr_id)));
        }else{
            $this->error('操作失败');
        }

    }

}
