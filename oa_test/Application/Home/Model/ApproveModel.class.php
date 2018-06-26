<?php
namespace Home\Model;
use Think\Model;
class ApproveModel extends Model{
    
    protected $tableName = 'approve';
    public $_type_appr = array(
        1=>array(1=>array(5,6,7,8), 2=>5, 3=>2, 4=>3),
        2=>array(1=>array(5,6,7,8), 2=>5, 3=>2, 4=>3, 5=>4),
        3=>array(1=>array(5,6,7,8), 2=>5, 3=>3, 4=>array(2,4), 5=>4),
        4=>array(1=>array(3)),
        5=>array(1=>array(6,7,8), 2=>5, 3=>2, 4=>3),
        6=>array(1=>5, 2=>3, 3=>array(2,4), 4=>4),
    ); 
    public $_aprv_type = array(
        1=>'预算单',
        2=>'借款单',
        3=>'项目报销单',
        4=>'还款单',
        5=>'预算变更单',
        6=>'个人报销单',
        7=>'TB报销单',
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
        $where = "(aprv_user_id1 = $userid or aprv_user_id2 = $userid or aprv_user_id3 = $userid or aprv_user_id4 = $userid or aprv_user_id5 = $userid)";
        if($aprv_type){
            $where .= " and aprv_type = $aprv_type";
        }
        return $this->get_list($where);
    }
    
    /**
     * @desc 获取该用户对应需要审批的单据单号列表
     * @param $userid 该用户userid
     * @param $aprv_type 单据类型
     * @author maojingjing
     */
    public function getApproveNos($userid, $aprv_type=null){
        if(empty($userid)||!is_numeric($userid))return false;
        $approves = $this->getApproveOrders($userid, $aprv_type);
        $arr = array();
        foreach ($approves as $row) {
            for ($i = 5; $i > 0; $i--) {
                $arr[$row['aprv_no']] = array('result'=>$row['aprv_result'.$i], 'user_id'=>$row['aprv_user_id'.$i]);
                break;
            }
        }
        return $arr;
    }
    
    /**
     * @desc 获取某个单的审批进度列表
     * @param $aprv_no 审批编号
     * @param $aprv_type 单据类型
     * @author maojingjing
     */
    public function getApproveSchedule($aprv_no, $aprv_type){
        if(empty($aprv_no))return false;
        $approve = $this->get_onebywhere("aprv_no='$aprv_no' and aprv_type=$aprv_type");
        return $approve;
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
    
    /**
     * @desc 更新审批人
     * @param $approve 审批对象
     * @param $cur_approver_id 审批人id
     * @param $userid 当前登录人id
     * @author maojingjing
     */
    public function updateApproved($approve, $cur_approver_id, $lvl_id, $userid){
        if(!$approve || !is_numeric($cur_approver_id))return false;
        $num = 1;
        if($userid){
            for ($i = 1; $i < 6; $i++) {
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
    
    /**
     * @desc 判断是否已经审批
     * @param $approve 审批对象
     * @param $userid 审批人id
     * @author maojingjing
     */
    public function checkIsApproved($approve, $userid){
        if(!$approve || !is_numeric($userid))return false;
        if($approve['aprv_result1'] == 0) return true;
        for ($i = 1; $i < 6; $i++) {
            if($approve['aprv_user_id'.$i] == $userid && $approve['aprv_result'.$i]==1){
                return true;
                break;
            }
        }
        return false;
    }
    
    public function get_whereaprrove($user_id,$aprv_type){
        return "select aprv_no from max_approve where is_del=1 and aprv_type = $aprv_type and (aprv_user_id1 = $user_id or aprv_user_id2 = $user_id or aprv_user_id3 = $user_id or aprv_user_id4 = $user_id or aprv_user_id5 = $user_id)";
    }
}