<?php
namespace Home\Model;
use Think\Model;
use Think\Think;

class CustomerModel extends Model{
    protected  $pagesize = 10;
    protected $_validate = array(
        // array(验证字段2,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        array('brand_name','require','新闻标题不能为空'),
        array('cust_short_name','require','新闻来源不能为空'),
        array('cust_full_name','require','新闻内容不能为空'),
        /* array('contact','require','联系人不能为空'),
        array('contact_number','require','联系电话不能为空'),
        array('sales_man','require','专营业务员不能为空'),
        array('department','require','分管部门不能为空'), */
    );
    public function insert($data){
        $data['is_del'] = 1;
        $data['crt_time'] = time();
        return $this->add($data);
    }
    
    public function update($data, $where){
        $data['mod_time'] = time();
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
        return $this->where("cust_id = $id")->find();
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
    public function get_lists($where,$firstRow,$listRow,$order=array('cust_id desc')){
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
     */
    public function deleteone($where){
        //$this->where($where)->delete();
        if($where){
            return $this->where($where)->delete();
        }
        return false;
    }


}