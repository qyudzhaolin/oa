<?php
namespace Home\Model;
use Think\Model;
class ContractModel extends Model{
    
    protected $tableName = 'contract';
    
    public function get_tablename(){
        return $this->tableName;
    }
    
    public function insert_data($data){
        $data['is_del'] = 1;
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
        return $this->where("ct_id = $id")->find();
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
    
    public function get_limit_row($proj_id){
        return $this->where("is_del=1 and proj_id=$proj_id")->max('rank');
    }
}