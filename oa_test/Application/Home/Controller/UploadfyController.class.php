<?php
namespace Home\Controller;
use Think\Controller;
use Think\Upload;
class UploadfyController extends Controller {
    
    public function kindeditorAction(){
        $md5 = md5(time());
        $arr[1]=substr($md5, 0, 2);
        $arr[2]=substr($md5, 2, 4);
        $upload = new Upload();
        $upload->maxSize   = 3145728 ;// 设置附件上传大小
        $upload->exts      = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath  = "/{$arr[1]}/{$arr[2]}/"; // 设置附件上传目录    // 上传文件
        $upload->subName   = '';
        $upload->rootPath  = C('UPLOAD_DOMAIN');
        $info=$upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            $pic = M('File');
            $data['file_name'] = $info['imgFile']['savepath'].$info['imgFile']['savename'];
            $data['crt_time'] = NOW_TIME;
            $id = $pic->add($data);
            if($id){
                $data['url'] = C('IMG_DOMAIN') . $data['file_name'];
                $this->ajaxReturn(array('error'=>0, 'url'=>$data['url']));
            }
        }
    }

    public function fundfileAction(){
        $exts = array('pdf','gif','jpg','png','xlsx','xls','txt','doc','docx');
        if(I('post.type') && in_array(I('post.type'),$exts)){
            $exts = array(I('post.type'));
        }
        
        $md5 = md5(time());
        $arr_path[1]=substr($md5, 0, 2);
        $arr_path[2]=substr($md5, 2, 4);
        $upload = new \Think\Upload();
        $upload->maxSize  = 10240000;
        $upload->exts     = $exts;
        $upload->savePath  = "/{$arr_path[1]}/{$arr_path[2]}/"; // 设置附件上传目录
        $upload->subName = '';
        $upload->rootPath = C('UPLOAD_DOMAIN');
        $info = $upload->upload($_FILES);
        if($info){
            $pic = M('File');
            $data['file_name'] = $info['file']['savepath'].$info['file']['savename'];
            $data['crt_time'] = NOW_TIME;
            $ret = $pic->add($data);
            if($ret){
                $arr['status'] = 1;
                $arr['file_id'] = $ret;
                $arr['file_name'] = $info['file']['savename'];
            }else{
                $arr['status'] = 0;
                $arr['msg'] = '上传失败';
            }
        }else{
            $arr['status'] = 0;
            $arr['msg'] = $upload->getError();
        }
        $this->ajaxReturn($arr);
    }
}