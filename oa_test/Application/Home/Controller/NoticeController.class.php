<?php
/**
 * OA系统公告发布
 */
namespace Home\Controller;
class NoticeController extends BaseController{
    
    public $check_access = true;
    public $head_title = '公告发布';
    
    public function addAction(){
        $notice_model = M('Notice');
        $map['company_id'] = session('company_id');
        if(IS_POST){
            $data['description'] = I('post.description');
            $ret = $notice_model->where($map)->save($data);
            if($ret){
                $this->ajaxReturn(array('status'=>1));
            }
        }
        
        $notice = $notice_model->where($map)->find();
        $this->assign('notice', $notice);
        $this->display('Notice:add');
    }
}