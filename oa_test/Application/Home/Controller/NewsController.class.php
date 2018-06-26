<?php
/**
 * OA系统公告发布
 */
namespace Home\Controller;
class NewsController extends BaseController{
    public $check_access = true;
    private $table = 'News';
    public $pagesize = 20;
    public function __construct()
    {
        parent::__construct();
        $this->model = D('News');
    }
   
    public function indexAction(){
         
          $cust_full_name = trim(I('cust_full_name'));
      
        if($cust_full_name){
            $map['titile'] = array('like',"%{$cust_full_name}%");
        }
        $count = $this->model->where($map)->count();
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');
        $list = $this->model->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        $show = $Page->show();
        $this->assign('search',I(''));
        $this->assign('page',$show);
        $this->assign('list',$list);


        $this->display('News:index');
    }

    public function addAction()
    {
        $id = intval(I('id'));
        if($id>0){
            $map['is_del'] = 1;
            $map['cust_id'] = $id;
            $info =  $this->model->get_onebywhere($map);
            if($info['crt_user_id']>0){
                $info['user_name'] = M('User')->where('user_id='.$info['crt_user_id'])->getField('user_name');
            }
            $this->assign('info',$info);
        }
        $this->assign('head_title', '新增客户');
        $this->display('add');
    }

    public function saveAction()
    {
          
         
            $data = I('post.');
            
            
            if($data['id']){
                
                $where['id'] = $data['id'];
                $id = $this->model->update($data,$where);
                $logtype = 'update';
            }else{
                $id = $this->model->insert($data);
                $logtype = 'insert';
            }
            if($id){
                $arr['code'] = 1;
                $arr['id'] = $id;
                $arr['msg'] = '操作成功！';
                $logstatus = 1;
            }else{
                $arr['code'] = 0;
                $arr['msg'] = '操作失败！';
                $logstatus = -1;
            }
            logrecords($logtype,$this->table,$logstatus);
       
        
        $this->ajaxReturn($arr);
        
     }
        
}