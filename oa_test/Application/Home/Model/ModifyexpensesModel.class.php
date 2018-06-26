<?php
namespace Home\Model;
use Think\Model;
class ModifyexpensesModel extends Model{
    
    protected $tableName = 'budget_modify_expenses';
    
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
    public function get_list($where,$feilds="*"){
        if(empty($where))return false;
        $list = $this->field($feilds)->where($where)->select();
        $cost_model = new CosttypeModel();
        foreach ($list as &$row) {
            $cost = $cost_model->get_one($row['cost_id']);
            $row['money_name'] = $cost['costname'];
            $row['final_money'] = sprintf("%.2f",($row['budget_money']-$row['usable_money']));
        }
        return $list;
    }
    
    public function final_list($where,$feilds="*"){
        if(empty($where))return false;
        $list = $this->field($feilds)->where($where)->select();
        $cost_model = new CosttypeModel();
        $arr['final_total'] = 0.00;
        foreach ($list as &$row) {
            $cost = $cost_model->get_one($row['cost_id']);
            $row['money_name'] = $cost['costname'];
            $row['final_money'] = sprintf("%.2f",($row['budget_money']-$row['usable_money']));
            $arr['final_total'] += sprintf("%.2f",($row['final_money']));
        }
        
        $arr['list'] = $list;
        return $arr;
    }
    
    public function get_array_list($bud_id, $proj_id=0){
        if($bud_id){
            $exps = $this->get_list("bud_id=$bud_id and is_del=1");
        }elseif($proj_id){
            $exps = $this->get_list("proj_id=$proj_id and is_del=1");
        }
        $arr = null;
        foreach ($exps as $row) {
            $arr[$row['cost_id']] = $row;
        }
        return $arr;
    } 
    
    public function get_expsandcost_list($bud_id){
        if(empty($bud_id)||!is_numeric($bud_id))return false;
        $sql = "select b.*,a.usable_money from max_expenses a inner join max_cost_type b on b.id=a.cost_id where a.bud_id=$bud_id and a.is_del=1";
        $costs = $this->query($sql);
        return $costs;
    }
}