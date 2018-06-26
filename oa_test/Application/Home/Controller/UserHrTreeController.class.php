<?php
/**
 * OA 人员树形结构
 */
namespace Home\Controller;
class UserHrTreeController extends BaseController
{

    public $check_access = true;
    private $table = 'UserHrTree';
    public $pagesize = 20;
    public function __construct(){
        parent::__construct();
    }
    
    public function indexAction(){
        /* $company_arr = array(1=>'上海公司',2=>'福州公司',3=>'北京公司');
        $company_id = session('company_id');
        $user_id = $this->user_id;
        
        $user = M('User')->where(['user_id'=>$user_id])->field("user_id,leader_uid,user_name,real_name,depart_id,lvl_id")->find();
        $depart_arr = M('Depart')->where(array('company_id'=>$company_id,'is_del'=>1))->getField("depart_id,depart_name,depart_par_id");
        $childs = $this->getChild($company_id,$user_id);
        
        foreach ($childs as &$row) {
            $row['depart_name'] = $depart_arr[$row['depart_id']]['depart_name'];
            if($this->check_is_child($company_id,$row['user_id'])){
                $row['type'] = 'group';
            }else{
                $row['type'] = 'list';
            }
        }
        
        $this->assign('user',$user);
        $this->assign('childs',$childs); */
        $this->display('index');
    }
    
    public function ajax_get_childAction(){
        $company_id = session('company_id');
        $depart_arr = M('Depart')->where(array('company_id'=>$company_id,'is_del'=>1))->getField("depart_id,depart_name,depart_par_id");
        if(is_numeric(I('get.id'))){
            $user_id = intval(I('get.id'));
            $childs = $this->getChild($company_id,$user_id);
        }else{
            $childs = M('User')->where(['user_id'=>$this->user_id])->field("user_id,leader_uid,user_name,real_name,depart_id,lvl_id")->select();
        }
        
        $str_html = "";
        foreach ($childs as &$row) {
            $row['depart_name'] = $depart_arr[$row['depart_id']]['depart_name'];
            
            $str_html .= '<ul>';
            if($this->check_is_child($company_id,$row['user_id'])){
                $str_html .= '<li class="jstree-closed" id="'.$row['user_id'].'">'.$row['real_name'].'（'.$row['depart_name'].'）'.'</li>';
            }else{
                $str_html .= '<li class="group">'.$row['real_name'].'</li>';
            }
            $str_html .= '</ul>';
        }
        echo $str_html;exit;
    }
    
    public function getChild($company_id,$user_id){
        $user_arr = M('User')->where(array('company_id'=>$company_id,'is_del'=>1,'leader_uid'=>$user_id))->field("user_id,leader_uid,user_name,real_name,depart_id,lvl_id")->select();
        return $user_arr;
    }
    
    public function check_is_child($company_id,$user_id){
        $count = M('User')->where(array('company_id'=>$company_id,'is_del'=>1,'leader_uid'=>$user_id))->count();
        return $count;
    }

    
}