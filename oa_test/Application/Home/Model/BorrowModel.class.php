<?php
namespace Home\Model;
use Think\Model;
class BorrowModel extends Model{
    
    protected $tableName = 'borrow';
    
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
        return $this->where("borrow_id = $id")->find();
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
     * 获取所属项目下的借款单
     * @param type $id
     * @return type
     */
    public function get_proj_borrows($proj_id, $user_id){
        if(empty($proj_id)||!is_numeric($proj_id))return false;
        return $this->field("borrow_id,borrow_no,tot_amt")->where("user_id=$user_id and proj_id=$proj_id and is_del=1 and result=1")->select();
    }
}