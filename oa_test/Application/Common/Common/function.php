<?php
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}
//Litter_7
function formatToTree($cate, $html = '--', $pid = 0, $level = 0,$pidname='pid',$idname = 'id')
{
    $arr = array();
    foreach ($cate as $k => $v) {
        if ($v[$pidname] == $pid) {
            $v['level'] = $level + 1;
            if ($pid > 0) {
                $v['html'] = str_repeat('&nbsp;', $level) . '|' . $html;
            }
            $arr[] = $v;
            $arr = array_merge($arr, formatToTree($cate, $html, $v[$idname], $level + 1,$pidname,$idname));
        }
    }
    return $arr;
}

function get_user_name($user_id){
	if(intval($user_id)){
		return M('User')->where('user_id='.$user_id)->getField('real_name');
	}
}