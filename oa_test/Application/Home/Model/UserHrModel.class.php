<?php
namespace Home\Model;
use Think\Model;

//model
class UserHrModel extends Model{
    protected $_validate = array(
        // array(验证字段2,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        /* array('name','require','姓名不能为空'),
        array('tel','require','电话不能为空'),
        array('phone','require','手机号不能为空'),
        array('email','require','邮箱不能为空'), */
        //array('address','require','地址不能为空'),
        //array('bnk_acct','/^\d{6,18}$/','银行账号不正确（请输入6~18位数字）',0,'regex',3),
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