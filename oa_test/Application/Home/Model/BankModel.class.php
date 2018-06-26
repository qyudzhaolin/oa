<?php
namespace Home\Model;
use Think\Model;
use Think\Think;

class BankModel extends Model{
    protected  $pagesize = 10;
    protected $_validate = array(
        // array(验证字段2,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        array('bank_name','require','银行名称不能为空或重复',0,'unique',3),
    );
    public function insert($data){
        $data['is_del'] = 1;
       // $data['crt_time'] = time();
        return $this->add($data);
    }
    
    public function update($data, $where){
       // $data['mod_time'] = time();
        return $this->where($where)->save($data);
    }
    
    /**
     * 根据部门ID获取单条信息
     * @param type $id
     * @return type
     */
    public function get_one($id){
        if(intval($id)==0)
            return false;
        return $this->where("bank_id = $id")->find();
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
    public function get_lists($where,$firstRow,$listRow,$order=array('bank_id desc')){
       return $this->where($where)->limit($firstRow.','.$listRow)->order($order)->select();
    }
    /**
     * 根据条件获取总数
     * @param type $id
     * @return type
     */
    public function get_count($where){
        return $this->where($where)->count();
    }
    /**
     * 根据条件删除数据
     * @param type $id
     * @return type
     * 物理删除 一般情况下不调用
     */
    public function deleteone($where){
        //$this->where($where)->delete();
        if($where){
            return $this->where($where)->delete();
        }
        return false;
    }


}