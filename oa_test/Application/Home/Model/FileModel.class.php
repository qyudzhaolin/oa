<?php
namespace Home\Model;
use Think\Model;
class FileModel extends Model{
    
    protected $tableName = 'file';
    
    public function get_tablename(){
        return $this->tableName;
    }
    
    public function insert_data($data){
        $data['crt_time'] = time();
        return $this->add($data);
    }
    
    /**
     * 根据用户ID获取单条用户信息
     * @param type $id
     * @return type
     */
    public function get_one($id){
        if(empty($id)||!is_numeric($id))return false;
        return $this->where("file_id = $id")->find();
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
}