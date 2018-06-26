<?php
namespace Home\Model;
use Think\Model;
class ProjectModel extends Model{
    
    protected $tableName = 'project';
    protected $_validate = array(
        array('cust_id','require','请选择客户！'),
        array('proj_no','require','请填写项目编号！'),
        array('cntr_val','require','请填写合同金额！'),
        array('proj_name','require','请填写项目名称！'),
        array('proj_mgr','require','请选择项目经理！')
    );
    
    public function get_tablename(){
        return $this->tableName;
    }
    
    public function insert_data($data){
        $data['is_del'] = 1;
        $data['crt_time'] = time();
        return $this->add($data);
    }
    
    public function update_data($data, $where){
        $data['mod_time'] = time();
        return $this->where($where)->save($data);
    }
    
    /**
     * 根据用户ID获取单条用户信息
     * @param type $id
     * @return type
     */
    public function get_one($id){
        if(empty($id)||!is_numeric($id))return false;
        return $this->where("proj_id = $id")->find();
    }
    
    /**
     * 根据条件获取单条信息
     * @param type $id
     * @return type
     */
    public function get_onebywhere($where){
        if(empty($where))return false;
        return $this->where($where)->find();
    }
    
    /**
     * 根据条件获取列表信息
     * @param type $id
     * @return type
     */
    public function get_list($where,$feilds="*"){
        if(empty($where))return false;
        return $this->field($feilds)->where($where)->select();
    }
    
    /**
     * 获取该用户参与到的项目列表
     * @return type
     */
    public function get_list_byuserid($lvl_id,$user_id,$cust_id=null){
        if(empty($lvl_id)||!is_numeric($lvl_id))return false;
        if(empty($user_id)||!is_numeric($user_id))return false;
        $company_id =session('company_id');
        $where = " and company_id=$company_id";
        if($cust_id && is_numeric($cust_id)) $where=" and a.cust_id=$cust_id";
        if($lvl_id < 5 ){
            $sql = "select a.* from max_project a where a.is_del=1 $where";
            $list = $this->query($sql); 
        }else{
            $sql = "(select a.proj_id,a.cust_id,a.proj_no,a.proj_name,a.cntr_val,a.proj_mgr,a.crt_user_id from max_project a INNER JOIN max_project_users b on b.proj_id=a.proj_id where b.user_id=$user_id $where and a.is_del=1 and b.is_del=1) union (select a.proj_id,a.cust_id,a.proj_no,a.proj_name,a.cntr_val,a.proj_mgr,a.crt_user_id from max_project a where (a.proj_mgr=$user_id or a.crt_user_id=$user_id) and a.is_del=1 $where)";
            $list = $this->query($sql);
        }
        return $list;
    }
    
    public function get_wherebyuserid($lvl_id,$user_id){
        $list = $this->get_list_byuserid($lvl_id, $user_id);
        $where = "";
        $awhere = "";
        foreach ($list as $key => $row) {
            $awhere .= "{$row['proj_id']},";
        }
        $awhere = rtrim($awhere, ',');
        if(strlen($awhere) > 0){
            $where .= " proj_id in ($awhere)";
        }else{
            $where .= " proj_id in (0)";
        }
        return $where;
    }
    
    /**
     * 获取该用户参与到的项目列表(排除已经被建预算表的项目)
     * @return type
     */
    public function get_budget_list($lvl_id,$user_id,$cust_id,$proj_id=null){
        if(empty($lvl_id)||!is_numeric($lvl_id))return false;
        if(empty($user_id)||!is_numeric($user_id))return false;
        if(empty($cust_id)||!is_numeric($cust_id))return false;
        $where = "";
        $budget_model = new BudgetModel();
        $hav_list = $budget_model->where('is_del=1 and cust_id='.$cust_id)->group('proj_id')->field('proj_id')->select();
        $awhere = "";
        foreach ($hav_list as $row) {
            if(!$proj_id || $row['proj_id'] != $proj_id){
                $awhere .= "{$row['proj_id']},";
            }
        }
        $awhere = rtrim($awhere, ',');
        if(strlen($awhere) > 0){
            $where .= " and a.proj_id not in ($awhere)";
        }
        
        if($cust_id && is_numeric($cust_id)) $where.=" and a.cust_id=$cust_id";
        if($lvl_id == 1){
            $sql = "select a.* from max_project a where a.is_del=1 $where";
            $list = $this->query($sql);
        }else{
            $sql = "(select a.proj_id,a.cust_id,a.proj_no,a.proj_name,a.cntr_val,a.proj_mgr,a.crt_user_id from max_project a INNER JOIN max_project_users b on b.proj_id=a.proj_id where b.user_id=$user_id $where and a.is_del=1 and b.is_del=1) union (select a.proj_id,a.cust_id,a.proj_no,a.proj_name,a.cntr_val,a.proj_mgr,a.crt_user_id from max_project a where (a.proj_mgr=$user_id or a.crt_user_id=$user_id) and a.is_del=1 $where)";
            $list = $this->query($sql);
        }
        return $list;
    }
    
    /**
     * 判断用户是否参与到这个项目中
     * @return type
     */
    public function check_in_proj($lvl_id,$user_id,$proj_id){
        if($lvl_id<4){
           return true; 
        }
        $projusers_model = new ProjectUsersModel();
        $count = $projusers_model->where("proj_id=$proj_id and user_id=$user_id and is_del=1")->count();
        if($count){
            return true;
        }
        $proj = $this->get_onebywhere("proj_mgr=$user_id or crt_user_id=$user_id");
        
        if($proj){
            return true;
        }
        return false;
    }
    
    public function get_wherebycompanyid($company_id){
        return "select proj_id from max_project where is_del=1 and company_id=$company_id";
    }

    public function get_wheresqlbyuserid($lvl_id,$user_id){
        $company_id =session('company_id');
        $where = " and a.company_id=$company_id";
        if($lvl_id < 5 ){
            $sql = "select a.proj_id from max_project a where a.is_del=1 $where";
        }else{
            $sql = "select b.proj_id from ((select a.proj_id,a.cust_id,a.proj_no,a.proj_name,a.cntr_val,a.proj_mgr,a.crt_user_id from max_project a INNER JOIN max_project_users b on b.proj_id=a.proj_id where b.user_id=$user_id $where and a.is_del=1 and b.is_del=1) union (select a.proj_id,a.cust_id,a.proj_no,a.proj_name,a.cntr_val,a.proj_mgr,a.crt_user_id from max_project a where (a.proj_mgr=$user_id or a.crt_user_id=$user_id) and a.is_del=1 $where)) as b";
        }
        return $sql;
    }
}