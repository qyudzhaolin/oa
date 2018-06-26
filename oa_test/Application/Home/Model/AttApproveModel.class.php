<?php
namespace Home\Model;
use Think\Model;
class AttApproveModel extends Model{
    
    protected $tableName = 'att_approve';
    
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
        $where = "(aprv_user_id1 = $userid or aprv_user_id2 = $userid or aprv_user_id3 = $userid) and is_del=1";
        return $this->get_list($where);
    }
    
    /**
     * @desc 获取某个单的审批进度列表
     * @param $aprv_no 审批编号
     * @param $aprv_type 单据类型
     * @author maojingjing
     */
    public function getApproveSchedule($v_id){
        if(empty($v_id))return false;
        $approve = $this->get_onebywhere("v_id=$v_id and is_del=1");
        return $approve;
    }
    
    
    /**
     * @desc 更新审批人
     * @param $approve 审批对象
     * @param $cur_approver_id 审批人id
     * @author maojingjing
     */
    public function updateApproved($approve, $cur_approver_id){
        if(!$approve || !is_numeric($cur_approver_id))return false;
        $num = 1;
        for ($i = 1; $i < 5; $i++) {
            if($approve['aprv_result'.$i] != 0){
                $num = $i+1;
                break;
            }
        }
        
        $ret = $this->update_data(array('aprv_user_id'.$num=>$cur_approver_id), "aprv_id=".$approve['aprv_id']);
        if($ret){
            return true;
        }
        return false;
    }
}