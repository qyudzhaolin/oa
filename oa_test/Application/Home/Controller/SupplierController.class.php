<?php
/**
 * OA系统用户管理

 */
namespace Home\Controller;
class SupplierController extends BaseController
{

    public $check_access = true;
    private $table = 'Supplier';
    public function __construct()
    {
        parent::__construct();
        if(session('is_supplier_creat')!=1 && session('is_supplier_show')!=1){
            redirect(U('Index/index'), 2, '您没有权限对该供应商进行操作!');
        }
        $this->model = D('Supplier');
    }
    /**
     * berry.qi
     * 供应商列表
     * 2016-04-19
     */
    public function indexAction()
    {
        $search = I('');
        $map = $this->mapFormat($search);
        $map['is_del'] = 1;
        /* if(session('lvl_id')!=1 && session('lvl_id')!=2){
            $map['depart_id'] = session('depart_par_id');
        } */
        $categoryModel = D('SupplierCategory');
        if($search['level1_cid']>0){
//            $search['level2_catname'] = $categoryModel->where('id='.$search['level2_cid'])->getField('name');
            $level2_list = $categoryModel->where('pid='.$search['level1_cid'])->select();
//            dump($level2_list);
            $this->assign('level2_list',$level2_list);
        }
        $count = $this->model->get_count($map);
        $Page = new \Think\Page($count,$this->pagesize);
        $Page->parameter = I('post.');
        $list = $this->model->get_lists($map,$Page->firstRow.','.$Page->listRows);

        $pay_method = C('PAY_METHOD');
        foreach ($list as &$row) {
            $row['pay_method'] = $row['pay_method'] ? $pay_method[$row['pay_method']] : '';
            if($row['level1_cid']){
                $row['level1_catname'] = $categoryModel->where('is_del=1 and id='.$row['level1_cid'])->getField('name');
            }
            if($row['level2_cid']){
                $row['level2_catname'] = $categoryModel->where('is_del=1 and id='.$row['level2_cid'])->getField('name');
            }
        }
        
        $show = $Page->show();
        //分类信息
        $category = $categoryModel->where('is_del=1 and pid=0')->select();
        $this->assign('category',$category);
//        dump($category);
        $this->assign('search',$search);
        $this->assign('is_supplier_creat',session('is_supplier_creat'));
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->assign('head_title', '用户管理-供应商');
        $this->display('index');
    }
    protected function mapFormat($search=array()){
        $name = trim($search['sup_full_name']);
        if($name){
            $map['_string'] = "sup_full_name like '%".$name."%' or sup_short_name like '%".$name."%'";
        }
        $area = trim($search['area']);
        if($area){
            $map['area'] = array('like',"%$area%");
        }
        /*$is_admin = is_admin_power();
        if($is_admin || $this->depart_id==34){
            //todo
        }else{
            $map['inventory'] = array('gt',0);//只显示有库存的物品
        }*/
        $level1_cid = intval($search['level1_cid']);
        if($level1_cid>0){
            $map['level1_cid'] = $level1_cid;
        }
        $level2_cid = intval($search['level2_cid']);
        if($level2_cid>0){
            $map['level2_cid'] = $level2_cid;
        }

        return $map;
    }

    /**
     * berry.qi
     * 供应商添加
     * 2016-04-19
     */
    public function addAction()
    {
        $categoryModel = D('SupplierCategory');
        if(session('is_supplier_creat')!=1){
            redirect(U('Index/index'), 2, '您没有权限对供应商进行编辑操作!');
        }
        $id = intval(I('id'));
        if($id>0){
            $map['is_del'] = 1;
            $map['sup_id']=$id;
            $info = $this->model->get_onebywhere($map);
            if($info['crt_user_id']>0){
                $info['user_name'] = M('User')->where('user_id='.$info['crt_user_id'])->getField('user_name');
            }
            if($info['level2_cid']>0){
                $info['level2_catname'] = $categoryModel->where('is_del=1 and id='.$info['level2_cid'])->getField('name');
            }
            $this->assign('info',$info);
        }
        //分类信息
        $category = $categoryModel->where('is_del=1 and pid=0')->select();
        $this->assign('category',$category);
//        dump($category);
        $banks = M('Bank')->where('is_del=1')->select();
        $pay_method = C('PAY_METHOD');
        $this->assign('banks',$banks);
        $this->assign('methods',$pay_method);
        $this->assign('head_title', '新增供应商');
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
            if($data['pay_method'] == 1){
                $data['other'] = '';
            }elseif($data['pay_method'] == 2){
                $data['bnk_acct'] = '';
                $data['bnk_branch'] = '';
                $data['other'] = '';
            }elseif($data['pay_method'] == 3){
                $data['bnk_acct'] = '';
                $data['bnk_branch'] = '';
            }
            
            if($data['sup_id']){
                $data['mod_user_id'] = $this->uid;
                $where['sup_id'] = $data['sup_id'];
                $id = $this->model->update($data,$where);
                $logtype = 'update';
            }else{
                $data['crt_user_id'] = $this->uid;
                $data['depart_id'] = session('depart_par_id');
                $id = $this->model->insert($data);
                $logtype = 'insert';
            }
            if($id){
                $arr['code'] = 1;
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
            $where['sup_id'] = $id;
            $data['is_del'] = -1;
            $data['mod_user_id'] = $this->uid;
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
}