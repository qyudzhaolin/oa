<?php
namespace Home\Model;
use Think\Model;
class BudgetModel extends Model{
    
    protected $tableName = 'budget';
    /* protected $_validate = array(
        array('cust_id','require','请选择客户！'),
        array('proj_id','require','请选择项目编号！'),
        array('cntr_income','require','请输入合同收入！'),
        array('user_name','','用户名已存在！',0,'unique',self::MODEL_BOTH),
        array('password','check_password','请正确填写密码和确认密码！',0, 'callback'),
        array('real_name','require','请填写真实姓名！'),
        array('depart_id','check_depart','请正确选择所在部门！',0, 'callback'),
        array('lvl_id','check_level','请正确选择用户级别！',0, 'callback')
    ); */
    
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
    
    public function delete_data($where){
        $data['is_del'] = -1;
        return $this->update_data($data, $where);
    }
    
    /**
     * 根据用户ID获取单条用户信息
     * @param type $id
     * @return type
     */
    public function get_one($id){
        if(empty($id)||!is_numeric($id))return false;
        return $this->where("bud_id = $id")->find();
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
     * 检查用户级别
     * @return $msg
     */
    function check_level($lvl_id){
        if($lvl_id==0 || !is_numeric($lvl_id))return false;
        $level_model = new LevelModel();
        $level = $level_model->get_one($lvl_id);
        if($level && $level['is_del'] == 1){
            return true;
        }
        return false;
    }
    
    /**
     * 检查用户级别
     * @return $msg
     */
    function check_depart($depart_id){
        if($depart_id==0 || !is_numeric($depart_id))return false;
        $depart_model = new DepartModel();
        $depart = $depart_model->get_one($depart_id);
        if($depart && $depart['is_del'] == 1){
            return true;
        }
        return false;
    }
    
    /**
     * 检查密码
     * @return $msg
     */
    public function check_password($password){
        if(!I('post.user_id')){
            if(I('post.password')=='' || I('post.repassword')==''){
                return false;
            }elseif(I('post.password') != I('post.repassword')){
                return false;
            }
        }else{
            if(I('post.password') != I('post.repassword')){
                return false;
            }
        }
        return true;
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
     * 获取已经审批完成的项目
     * @param type $id
     * @return type
     */
    public function get_list_over($lvl_id,$user_id){
        $proj_model = new ProjectModel();
        $projs = $proj_model->get_list_byuserid($lvl_id,$user_id);
        
        $awhere = "";
        foreach ($projs as $row) {
            $awhere .= "{$row['proj_id']},";
        }
        $awhere = rtrim($awhere, ',');
        if(strlen($awhere) > 0){
            $where .= " and a.proj_id in ($awhere)";
        }
        
        if(!$where){
            return false;
        }
        
        $sql = "select b.* from max_budget a INNER JOIN max_project b on b.proj_id=a.proj_id where a.result=1 and a.is_del=1 $where";
        return $this->query($sql);
    }
    
    public function check_is_over($data){
        $is_over = check_is_over($data['proj_id']);
        if($is_over){
            return array('status'=>true,'msg'=>'该项目已结束');
        }
        
        if($data['end_time']>0){
            $over_time = $data['end_time']+31*86400;            //30天为一个月，加上当天的时间为31
            if($over_time<time()){
                return array('status'=>true,'msg'=>'该项目已过截止日期');
            }
        }
        return array('status'=>false);
    }
}