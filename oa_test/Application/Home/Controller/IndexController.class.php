<?php
namespace Home\Controller;

class IndexController extends BaseController{
    
    public function indexAction(){
        $notice_model = M('Notice');
        $map['company_id'] = session('company_id');
        $notice = $notice_model->where($map)->find();
        $notice['description'] = htmlspecialchars_decode($notice['description']);
       
        $this->assign('notice', $notice);
        $this->assign('head_title', '智源动力OA系统');
        $this->display();
    }
    
    public function testAction(){
        echo "hello1";
    }

}