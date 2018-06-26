<?php
namespace Home\Model;
use Think\Model;

//model
class SupplierCategoryModel extends Model{
    protected $_validate = array(
        array('name','require','分类名称不能为空')
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
     * 根据ID获取单条信息
     * @param type $id
     * @return type
     */
    public function get_one($id){
        if(intval($id)==0)
            return false;
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
    public function get_lists($where,$firstRow,$listRow,$order=array('id desc'),$field="*"){
        return $this->where($where)->limit($firstRow.','.$listRow)->order($order)->field($field)->select();
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