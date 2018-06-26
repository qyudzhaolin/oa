<?php
namespace Home\Model;
use Think\Model;
class MoneyModel extends Model{
    
    protected $tableName = 'money';
    
    public function get_tablename(){
        return $this->tableName;
    }
    
    public function insert_data($data){
        $data['is_del'] = 1;
        return $this->add($data);
    }
    
    public function update_data($data, $where){
        return $this->where($where)->save($data);
    }
    
    /**
     * 根据用户ID获取单条用户信息
     * @param type $id
     * @return type
     */
    public function get_one($id){
        if(empty($id)||!is_numeric($id))return false;
        return $this->where("money_id = $id")->find();
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
    public function get_list($where,$feilds="*",$order=""){
        if(empty($where))return false;
        $list = $this->field($feilds)->where($where)->order($order)->select();
        $cost_model = new CosttypeModel();
        foreach ($list as &$row) {
            $cost = $cost_model->get_one($row['cost_id']);
            $row['money_name'] = $cost['costname'];
        }
        return $list;
    }
    
    public function get_moneyandcost_list($obj_id, $obj_type=1){
        if(empty($obj_id)||!is_numeric($obj_id))return false;
        $sql = "select b.*,a.usable_money,a.money_id from max_money a inner join max_cost_type b on b.id=a.cost_id where a.obj_id=$obj_id and obj_type=$obj_type and a.is_del=1";
        $costs = $this->query($sql);
        return $costs;
    }
    
    public function get_array_list($obj_id, $obj_type=1){
        if(empty($obj_id)||!is_numeric($obj_id))return false;
        $moneys = $this->get_list("obj_id=$obj_id and obj_type=$obj_type and is_del=1","*","usable_money asc");  
        $arr = null;
        foreach ($moneys as $row) {
            $arr[$row['cost_id']] = $row;
        }
        return $arr;
    }
}