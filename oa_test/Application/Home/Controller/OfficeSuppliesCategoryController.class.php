<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class OfficeSuppliesCategoryController extends BaseController
{

    public $check_access = true;
    private $table = 'OfficeSuppliesCategory';
    public function __construct()
    {
        parent::__construct();
        $this->model = D('OfficeSuppliesCategory');
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
        $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('');
        $list = $this->model->get_lists($map,$Page->firstRow.','.$Page->listRows);
        $show = $Page->show();
        $this->assign('search',I(''));
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->assign('head_title', '办公用品');
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
        $this->assign('head_title', '新增办公用品申请');
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
    
    public function getsupplerlistAction(){
        $list = M("Recouped")->where("get_type=1 and is_del=1")->select();
        $arr = array();
        foreach ($list as $key => $row) {
            $suppler = M("Supplier")->where("sup_id=".$row['get_id'])->find();
            if($suppler){
                $arr[$key]['sup_full_name'] = $suppler['sup_full_name'];
            }
            $proj = M("Project")->where("proj_id=".$row['proj_id'])->find();
            if($proj){
                $arr[$key]['proj_no'] = $proj['proj_no'];
            }
            $sql = "select b.depart_name,a.real_name from max_user a inner join max_depart b on b.depart_id = a.depart_id where a.user_id=".$row['crt_user_id'];
            $info = M("User")->query($sql);
            if($info){
                $arr[$key]['depart_name'] = $info[0]['depart_name'];
            }
            $arr[$key]['rec_no'] = $row['rec_no'];
            $arr[$key]['tot_amt'] = $row['tot_amt'];
        }
    
        $headtitle = array(
            'sup_full_name'=>'客户',
            'proj_no'=>'项目编号',
            'depart_name'=>'部门名称',
            'rec_no'=>'报销单号',
            'tot_amt'=>'金额',
        );
    
        /* $header = implode("\",\"",array_values($headtitle));
         $header = "\"" .$header;
         $header .= "\"\r\n";
         $content .= $header;
         $filename = date('YmdHis').'.csv';
         ob_end_clean();
         header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
         header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
         header("X-DNS-Prefetch-Control: off");
         header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
         header("Pragma: no-cache");
         header("Content-Type: application/octet-stream");
         header("Content-Type: application/force-download");
         header("Content-Disposition: attachment; filename=".$filename);
         $content=iconv("UTF-8","GBK//IGNORE",$content) ;
         echo $content;
         foreach($arr as $row)
         {
         $new_arr = array();
         $content = "";
         foreach ($headtitle as $key1 => $value)
         {
         array_push($new_arr, preg_replace("/\"/","\"\"","\t".$row[$key1]));
         }
         $line = implode("\",\"",$new_arr);
         $line = "\"" .$line;
         $line .= "\"\r\n";
         $content .= $line;
         $content=@iconv("UTF-8","GBK//IGNORE",$content) ;
         echo $content;
        } */
    }
    public function saveCategoryAction(){
        $name = I('post.name');
    }
}