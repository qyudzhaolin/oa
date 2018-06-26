<?php
namespace Home\Model;
use Think\Model;
class PermissionsModel extends Model{
    
    protected $tableName = 'permissions';
    
    public function get_tablename(){
        return $this->tableName;
    }
    
    protected $_validate = array(
        array('per_name','require','请填写权限名称！'),
        array('per_name','','权限名称已经存在！',0,'unique'), // 在新增的时候验证name字段是否唯一
        array('controller','require','请填写控制器！'),
        array('action','require','请填写行为！')
    );
    
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
        return $this->where("per_id = $id")->find();
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
     */
    public function get_list($where,$feilds="*"){
        if(empty($where))return false;
        return $this->field($feilds)->where($where)->select();
    }
    
    /**
     * 根据控制器和行为名获取数据
     * @param type $controller_name
     * @param type $action_name
     * @return type
     */
    public function get_onebyname($controller_name, $action_name){
        $cache_key = $controller_name.'_'.$action_name;
        $permission = S($cache_key);
        if(!$permission){
            $permission = $this->get_onebywhere("is_del=1 and controller='".$controller_name."' and action='".$action_name."'");
            if($permission){
                S($cache_key, $permission);
            }
        }
        return $permission;
    }
    
    public function clean_cache($controller_name, $action_name){
        $cache_key = $controller_name.'_'.$action_name;
        S($cache_key,null);
    }
}