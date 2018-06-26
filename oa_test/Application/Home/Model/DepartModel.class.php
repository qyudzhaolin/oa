<?php
namespace Home\Model;
use Think\Model;
class DepartModel extends Model{
    
    protected $tableName = 'depart';
    
    protected $_company = array('1'=>'上海公司',2=>'福州公司',3=>'北京公司');
    
    protected $_validate = array(
        array('depart_name','require','请填写部门名称！')
    );
    
    public function get_tablename(){
        return $this->tableName;
    }
    
    public function get_company(){
        return $this->_company;
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
     * 根据部门ID获取单条信息
     * @param type $id
     * @return type
     */
    public function get_one($id){
        if(empty($id)||!is_numeric($id))return false;
        return $this->where("depart_id = $id")->find();
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
    public function get_list($where){
        return $this->where($where)->select();
    }
    
    /**
     * 获取子级或者无子级的部门列表
     */
    public function get_use_list(){
        $company_id = session('company_id');            //获取所属公司的部门列表
        $sql = "select depart_id,depart_name from max_depart where is_del=1 and company_id=$company_id";
        return $this->query($sql);
    }
    
    
    /**
     * 获取项目所对应的部门列表信息
     * @param $proj_id 项目Id 默认为Null
     * @return type
     */
    public function get_project_list($proj_id = null){
        $list = $this->get_use_list();
        
        $ret = array();
        $du_array = array();
        $users_model = new UserModel();
        if(!empty($proj_id)){
            $pusers_model = new ProjectUsersModel();
            $users = $pusers_model->get_list("is_del=1 and proj_id=$proj_id");                    //获取该项目下的所有人员
            foreach ($users as $urow) {
                if(!array_key_exists($urow['depart_id'], $du_array)){
                    $dusers = $users_model->get_list("depart_id={$urow['depart_id']} and is_del=1");          //获取该部门下的所有人员
                    foreach ($dusers as $durow) {
                        $du_array[$urow['depart_id']][$durow['user_id']]['real_name'] = $durow['real_name'];      
                        $du_array[$urow['depart_id']][$durow['user_id']]['is_check'] = false;
                    }
                }
                $du_array[$urow['depart_id']][$urow['user_id']]['is_check'] = true;
            }
        }
        
        foreach ($list as $row) {
            $count = $users_model->where("depart_id={$row['depart_id']} and is_del=1")->count();          //获取该部门下的所有人员
            if($count == 0){
                continue;
            }
        
            $ret[$row['depart_id']]['depart_name'] = $row['depart_name'];
            if(array_key_exists($row['depart_id'], $du_array)){
                $ret[$row['depart_id']]['is_check'] = true;
                $ret[$row['depart_id']]['users'] = $du_array[$row['depart_id']];
            }else{
                $ret[$row['depart_id']]['is_check'] = false;
                $ret[$row['depart_id']]['users'] = array();
            }
        }
        
        return $ret;
    }
    
    /**
     * 获取部门所对应的用户列表信息
     * @param $proj_id 项目Id 默认为Null
     * @return type
     */
    public function get_depart_users($depart_id, $proj_id = null){
        if(empty($depart_id)||!is_numeric($depart_id))return false;
        $users_model = new UserModel();
        $dusers = $users_model->get_list("depart_id=$depart_id and is_del=1","user_id,real_name");          //获取该部门下的所有人员
        
        /* if(!empty($proj_id)){
            $pusers_model = new ProjectUsersModel();
            $users = $pusers_model->get_list("is_del=1 and proj_id=$proj_id and depart_id=$depart_id","user_id");                    //获取该项目、部门下的所有人员
            foreach ($dusers as &$durow) {
                $durow['is_check'] = false;
                foreach ($users as $key => $row) {
                    if($durow['user_id'] == $row['user_id']){
                        $durow['is_check'] = true;
                        unset($users[$key]);                    //若找到对应信息，删除该数据并跳出内层循环
                        break;
                    }
                }
            }
        } */
        
        return $dusers;
    }
}