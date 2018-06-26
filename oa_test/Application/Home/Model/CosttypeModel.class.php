<?php
namespace Home\Model;
use Think\Model;
class CosttypeModel extends Model{
    
    protected $tableName = 'cost_type';
    
    /**
     * 根据级别ID获取单条信息
     * @param type $id
     * @return type
     */
    public function get_one($id){
        if(empty($id)||!is_numeric($id))return false;
        return $this->where("id = $id")->find();
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
     * 根据父级id获取子级列表信息
     * @param type $id
     * @return type
     */    
    public function get_cost_bypid($pid){
        if(empty($pid)||!is_numeric($pid))return false;
        //媒介部规定，传播推广选项的子级，只能是媒介采买
        if($pid == 7){
            return $this->get_list("id=90");
        }else{
            return $this->get_list("parentId=$pid");
        }
    }
    
    /**
     * 根据子级id获取父级列表信息
     * @param type $id
     * @return type
     */
    public function get_cost_bycid($cid){
        if(empty($cid)||!is_numeric($cid))return false;
        $cost = $this->get_one($cid);
        $arr = array();
        if($cost){
            $child_1 = $this->get_cost_bypid($cost['parentid']);
            $parent_1 = $this->get_one($cost['parentid']);
            if($parent_1){
                $child_2 = $this->get_cost_bypid($parent_1['parentid']);
                $parent_2 = $this->get_one($parent_1['parentid']);
                if($parent_2){
                    $child_3 = $this->get_cost_bypid($parent_2['parentid']);
                    $parent_3 = $this->get_one($parent_2['parentid']);
                    if($parent_3){
                        $arr = array(
                            'ish'=>$parent_3['id'],
                            'child'=>array(
                                'ish'=>$parent_2['id'],
                                'row'=>$child_3,
                                'child'=>array(
                                    'ish'=>$parent_1['id'],
                                    'row'=>$child_2,
                                    'child'=>array(
                                        'ish'=>$cid,
                                        'row'=>$child_1,
                                    )
                                )
                            )
                        );
                    }
                }
            }
        }
        return $arr;
    }
}