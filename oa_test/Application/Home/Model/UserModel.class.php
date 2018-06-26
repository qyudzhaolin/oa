<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{
    
    protected $tableName = 'user';
    protected $_validate = array(
        array('user_name','require','请填写用户名！'),
        array('user_name','','用户名已存在！',0,'unique',self::MODEL_BOTH),
        array('password','check_password','请正确填写密码和确认密码！',0, 'callback'),
        array('real_name','require','请填写真实姓名！'),
        array('email','require','请填写邮箱！'),
        array('depart_id','check_depart','请正确选择所在部门！',0, 'callback'),
        array('lvl_id','check_level','请正确选择用户级别！',0, 'callback')
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
        return $this->where("user_id = $id")->find();
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
    
    
    public function get_mgr_users($depart_id = 0){
        $sql = "select a.user_id,a.real_name,b.depart_name from max_user a LEFT JOIN max_depart b on a.depart_id = b.depart_id where a.is_del=1 and a.lvl_id in (3,4,5,6,7,8)";
        if(!is_admin_power() && $depart_id){
            $depart_model = new DepartModel();
            $ids = $depart_model->where("depart_par_id=$depart_id")->field("depart_id")->select();
            $str="($depart_id";
            foreach ($ids as $row) {
                $str .= ",".$row['depart_id'];
            }
            $str .= ")";
            $sql .= " and a.depart_id in $str";
        }
        
        return $this->query($sql);
    }
    
    /**
     * @desc 判断审批人列表
     * @param $lvl_id 审批对象级别id
     * @author maojingjing
     */
    public function getApproveList($lvl_id, $apply_user_id, $approve){
        if($lvl_id == 1){
            $approve_users = $this->get_list("is_del=1 and lvl_id<8 and user_id!=$apply_user_id",'user_id,real_name');
        }else{
            $approve_users = $this->get_list("is_del=1 and lvl_id<$lvl_id",'user_id,real_name');
        }
        return $approve_users;
    }
    
    public function get_wherebycompanyid($company_id){
        return "select user_id from ".$this->trueTableName." where is_del=1 and company_id=$company_id";
    }
}