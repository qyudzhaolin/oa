<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class SupplierCategoryController extends BaseController
{

    public $check_access = true;
    private $table = 'SupplierCategory';
    public function __construct()
    {
        parent::__construct();
        $this->model = D('SupplierCategory');
    }
    /**
     * Litter7
     * 办公用品在线申请
     * 2017-04-12
     */
    public function indexAction()
    {
        $map['is_del'] = 1;
        $name = trim(I('name'));
        if($name){
            $map['name'] = array('like',"%{$name}%");
        }
        $map['is_del'] = 1;
        $map['pid'] = 0;
        $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');
        $list = $this->model->where($map)->select();
        foreach($list as $key=>$val){
            $list[$key]['list'] = $this->model->where('is_del=1 and pid='.$val['id'])->select();

        }
        $show = $Page->show();
        $this->assign('search',I(''));
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->assign('head_title', '供应商分类');
        $this->display('index');
    }

    /**
     * berry.qi
     * 添加
     * 2017-04-12
     */
    public function addAction()
    {
        $id = intval(I('id'));
        if($id>0){
            $map['is_del'] = 1;
            $map['id']=$id;
            $info = $this->model->get_onebywhere($map);
            if($info['crt_user_id']>0){
                $info['user_name'] = M('User')->where('user_id='.$info['crt_user_id'])->getField('user_name');
            }
            $this->assign('info',$info);
        }
        $category = $this->model->where('is_del=1 and pid=0')->select();
        $this->assign('category',$category);
        $this->assign('head_title', '新增供应商分类');
        $this->display('add');
    }

    /**
     * berry.qi
     * 供应商数据保存
     * 2016-04-19
     */
    public function saveAction()
    {
        if ($this->model->create()===false) {
            $arr['code'] = 0;
            $arr['msg'] = $this->model->getError();
        }else{
            $data = I('post.');
//dump($data);exit;
            
            if($data['id']){
                $data['mod_user_id'] = $this->user_id;
                $where['id'] = $data['id'];
                $id = $this->model->update($data,$where);
                $logtype = 'update';
            }else{
                $data['crt_user_id'] = $this->user_id;
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
        }
        $this->ajaxReturn($arr);
    }
    /**
     * berry.qi
     * 供应商数据保存
     * 2016-04-19
     */
    public function delAction(){
        $id = intval(I('id'));
        if($id){
            $where['id'] = $id;
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
    public function ajaxGetcateAction(){
        $cid = I('post.cid');
        $arr['code'] = 1;
        $html = '<option value="0">请选择分类</option>';
        $arr['list'] = $html;
        $arr['required'] = 0;
        if($cid){
            $category = M('SupplierCategory')->where('is_del=1 and pid='.$cid)->select();
            if($category){
                foreach($category as $key=>$val){
                    $html .= '<option value="'.$val['id'].'">'.$val['name'].'</option>';
                }
                $arr['code'] = 1;
                $arr['required'] = 1;
                $arr['list'] = $html;
            }
        }
        $this->ajaxReturn($arr);
    }
}