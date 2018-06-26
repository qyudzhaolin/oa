<?php
namespace Home\Model;
use Think\Model;
class VacationModel extends Model{
    
    protected $tableName = 'vacation';
    public $v_type_arr = array(1=>'调休',2=>'出差',3=>'外出',4=>'忘签到',5=>'病假',6=>'事假',7=>'年假',8=>'郁闷假',9=>'亲子假',10=>'其他假');
    
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
        return $this->where("v_id = $id")->find();
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
    
    public function get_idsbywhere($where){
        
    }
}