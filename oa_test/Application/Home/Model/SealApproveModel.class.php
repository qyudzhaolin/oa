<?php
namespace Home\Model;
use Think\Model;
class SealApproveModel extends Model{
    
    protected $tableName = 'seal_approve';
    
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
        return $this->where("aprv_id = $id")->find();
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
     * @param $where 条件
     * @return type
     */
    public function get_list($where,$feilds="*"){
        if(empty($where))return false;
        return $this->field($feilds)->where($where)->select();
    }
    
    /**
     * @desc 获取该用户对应需要审批的单据列表
     * @param $userid 该用户userid
     * @param $type 单据类型
     * @author maojingjing
     */
    public function getApproveOrders($userid, $aprv_type=null){
        if(empty($userid)||!is_numeric($userid))return false;
        $where = "(aprv_user_id1 = $userid) and is_del=1";
        return $this->get_list($where);
    }
    
    /**
     * @desc 获取某个单的审批进度列表
     * @param $aprv_no 审批编号
     * @param $aprv_type 单据类型
     * @author maojingjing
     */
    public function getApproveSchedule($se_id){
        if(empty($se_id))return false;
        $approve = $this->get_onebywhere("se_id=$se_id and is_del=1");
        return $approve;
    }
    
    
    /**
     * @desc 更新审批人
     * @param $approve 审批对象
     * @param $cur_approver_id 审批人id
     * @author maojingjing
     */
    public function updateApproved($approve, $cur_approver_id, $lvl_id, $userid ){
        if(!$approve || !is_numeric($cur_approver_id))return false;
        $num = 1;
        if($userid){
            for ($i = 1; $i < 4; $i++) {
                if($lvl_id!=1){
                    if($approve['aprv_user_id'.$i] == $userid){
                        if($approve['aprv_user_id'.($i+1)] == $userid){
                            $num = $i+2;
                        }else{
                            $num = $i+1;
                        }
                        break;
                    }
                }else{
                    if($approve['aprv_result'.$i] == 0){
                        $num = $i;
                        break;
                    }
                }
            }
        }
        $ret = $this->update_data(array('aprv_user_id'.$num=>$cur_approver_id), "aprv_id=".$approve['aprv_id']);
        if($ret){
            return true;
        }
        return false;
    }
    
    public function get_whereaprrove($user_id){
        return "select se_id from max_seal_approve where is_del=1 and  (aprv_user_id1 = $user_id or aprv_user_id2 = $user_id or aprv_user_id3 = $user_id)";
    }
    
    /**
     * @desc 判断是已有审批记录
     * @param $approve 审批对象
     * @param $userid 审批人id
     * @author maojingjing
     */
    public function getIsApproved($approve, $userid = null){
        if(!$approve)return false;
        $num = 1;
        if($userid){
            /* for ($i = 1; $i < 6; $i++) {
             if($approve['aprv_user_id'.$i] == $userid){
             $num = $i+1;
             break;
             }
             }  */
            for ($i = 5; $i > 0; $i--) {
                if($approve['aprv_user_id'.$i] == $userid){
                    $num = $i+1;
                    break;
                }
            }
        }
        if($approve['aprv_result'.$num]!=0){
            return true;
        }
        return false;
    }
}