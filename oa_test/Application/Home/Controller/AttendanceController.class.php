<?php
namespace Home\Controller;
use Think\Page;
class AttendanceController extends BaseController{
    
    public $check_access = true;
    public $head_title = '考勤管理';
    public $att_status = array(1=>'待审批',2=>'审核中',3=>'已通过',4=>'未通过');
    public $att_types = array(1=>'调休',2=>'出差',3=>'外出',4=>'忘签到',5=>'病假',6=>'事假',7=>'年假',8=>'郁闷假',9=>'亲子假',10=>'其他假');
    
    public function indexAction(){
            if(session('lvl_id')==2){
                $this->redirect("Attendance/approve");
            }
            $real_name = "";
            $is_vo_power = check_permission_left("Attendance", "approve_all");
            $where = "1=1";
            if($is_vo_power){
                //关键词检索
                $keyword = I('get.keyword','','addslashes');
                if($keyword){
                    $where = "real_name = '$keyword'";
                    $real_name = $keyword;
                }
                $where .= " and real_name in (select real_name from max_user where company_id=".session('company_id').")";
            }else{
                $real_name = session('real_name');
                $where = "real_name = '$real_name'";
            }
            
            //日期检索
            $start_date = I('get.start_date','','addslashes');
            $end_date = I('get.end_date','','addslashes');
            if($start_date){
                $where .= " and att_date>='$start_date'";
            }
            if($end_date){
                $where .= " and att_date<='$end_date'";
            }
            
            $obj = M('Attendance');
            $count = $obj->where($where)->count();
            $page = new Page($count, 20);
            $show = $page->show();
            $list = $obj->where($where)->order("att_date asc,att_num asc")->limit($page->firstRow.','.$page->listRows)->select();
            $user_model = new \Home\Model\UserModel();
            $depart_name = "";
            if(count($list)>0 && $real_name){
                $user = $user_model->get_onebywhere("real_name='$real_name'");
                if($user){
                    $depart_name = M('Depart')->where('depart_id='.$user['depart_id'])->getField('depart_name');
                }
            }
            
            foreach ($list as &$row) {
                if(!$depart_name){
                    $user = $user_model->get_onebywhere("real_name='{$row['real_name']}'");
                    if($user){
                        $row['depart_name'] = M('Depart')->where('depart_id='.$user['depart_id'])->getField('depart_name');
                    }
                }else{
                    $row['depart_name'] = $depart_name;
                }
            
                $date_limit = (strtotime(date('Y-m-d', time()))-strtotime($row['att_date']))/(24*3600);
            
                $att_start_time = floatval(str_replace(":", ".", $row['att_start_time']));
                if((!$row['att_start_time'] || $att_start_time>9.45) && $row['start_status']!=3 && $row['holiday']==0){
                    if($date_limit>5){
                        $row['start_color'] = "gray";
                    }else{
                        $row['start_color'] = "yellow";
                    }
                }
            
                $att_end_time = floatval(str_replace(":", ".", $row['att_end_time']));
                if((!$row['att_end_time'] || $att_end_time<18.30) && $row['end_status']!=3 && $row['over_time']=="" && $row['holiday']==0){
                    if($date_limit>5){
                        $row['end_color'] = "gray";
                    }else{
                        $row['end_color'] = "yellow";
                    }
                }
            
                if($row['start_status']){
                    $row['start_status_name'] = $this->att_types[$row['start_type']]."--".$this->att_status[$row['start_status']];
                }
                if($row['end_status']){
                    $row['end_status_name'] = $this->att_types[$row['end_type']]."--".$this->att_status[$row['end_status']];
                }
            }
            
            $v_model = D('Vacation');
            $v_type_arr = $v_model->v_type_arr;
            unset($v_type_arr[10]);
            
            //审批人列表
            $approve_users = getAttApproverList();
            
            $this->assign('approve_users', $approve_users);
            $this->assign('v_type_arr', $v_type_arr);
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->assign('is_vo_power', $is_vo_power);
            $this->assign('is_approver', $this->is_approver());
            $this->display('Attendance:index');
    }
    
    //导入打卡记录
    public function upload_dateAction(){
        if(IS_POST){
            setlocale(LC_ALL,'zh_CN');
            $obj = M('Attendance');
            $file = $_FILES['file']['tmp_name'];
            $filename = end(explode('.', $_FILES['file']['name']));
            if($filename == 'csv' || $filename == 'CSV'){
                $handle = fopen($file, 'r');
                while ($data = fgetcsv($handle)) {
                    $att_data = array();
                    $att_data['att_num'] = $data[0];
                    $att_data['real_name'] = mb_convert_encoding($data[2] ,'utf-8','GBK');
                    $att_data['att_date'] = $data[3];
                    $att_data['att_start_time'] = $data[4];
                    $att_data['att_end_time'] = $data[5];
                    $att_data['late_time'] = $data[6];
                    $att_data['over_time'] = $data[7];
                    $att_data['work_time'] = $this->count_work($att_data['att_start_time'],$att_data['att_end_time']);
                    $att_data['holiday'] = $this->check_holiday($att_data['att_date']);
                    
                    $att = $obj->where("att_num={$att_data['att_num']} and att_date='{$att_data['att_date']}'")->find();
                    if(!$att){
                        $obj->add($att_data);
                    }else{
                        $obj->where(array('att_id'=>$att['att_id']))->save($att_data);
                    }
                }
                $this->ajaxReturn(array('status'=>1));
            }
            $this->ajaxReturn(array('status'=>0,'msg'=>'只能上传csv后缀的文件'));
        }
        $this->display('Attendance:upload_date');
    }

    //报表管理页
    public function reportingAction(){
        if(check_permission_left('Attendance', 'reporting')){
            $real_name = "";
        
            $where = "1=1";
            //关键词检索
            $keyword = I('get.keyword','','addslashes');
            if($keyword){
                $where = "real_name = '$keyword'";
                $real_name = $keyword;
            }
        
            //日期检索
            $month = I('get.month','','addslashes');
            if($month){
                $where .= " and month='$month'";
            }
            
            //是否导出
            $export = I('get.type');
        
            $obj = M('Att_reporting');
            
            if($export == 'export'){ //导出
                $list = $obj->where($where)->order('id asc')->select();
            }else{
                $count = $obj->where($where)->count();
                $page = new Page($count, 20);
                $show = $page->show();
                $list = $obj->where($where)->order('id asc')->limit($page->firstRow.','.$page->listRows)->select();
            }
        
            $user_model = new \Home\Model\UserModel();
            $depart_name = "";
            if(count($list)>0 && $real_name){
                $user = $user_model->get_onebywhere("real_name='$real_name'");
                if($user){
                    $depart_name = M('Depart')->where('depart_id='.$user['depart_id'])->getField('depart_name');
                }
            }
        
            foreach ($list as &$row) {
                if(!$depart_name){
                    $user = $user_model->get_onebywhere("real_name='{$row['real_name']}'");
                    if($user){
                        $row['depart_name'] = M('Depart')->where('depart_id='.$user['depart_id'])->getField('depart_name');
                    }
                }else{
                    $row['depart_name'] = $depart_name;
                }
            }
            
            if($export == 'export'){
                $this->export($list);exit;
            }
        
            $is_count = check_permission_left('Attendance', 'count_reporting');
            $this->assign('is_count', $is_count);
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->display('Attendance:reporting');
        }
    }
    
    //导出月度统计
    public function export($list){
        $header1 = array("月份","姓名","加班","调休","迟到","事假","病假","年休假","旷工","忘签到","其他假","实际出勤天","差值","备注");
        $header = implode("\",\"",array_values($header1));
        $header = "\"" .$header;
        $header .= "\"\r\n";
        $content .= $header;
        ob_end_clean();
        header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        header("X-DNS-Prefetch-Control: off");
        header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=orderlist.csv");
        $content=iconv("UTF-8","GBK//IGNORE",$content) ;
        echo $content;
    
        foreach($list as $row)
        {
            $data['real_name'] = $row['depart_name']."--".$row['real_name'];
            $data['month'] = $row['month'];
            $data['over_time'] = $row['over_time'];
            $data['rest_time'] = $row['rest_time'];
            $data['late_time'] = $row['late_time'];
            $data['s_vacation'] = $row['s_vacation'];
            $data['b_vacation'] = $row['b_vacation'];
            $data['nx_vacation'] = $row['nx_vacation'];
            $data['kg_time'] = $row['kg_time'];
            $data['forget_signin'] = $row['forget_signin'];
            $data['other'] = $row['other'];
            $data['work_date'] = $row['work_date'];
            $data['differ_time'] = $row['differ_time'];
            $data['mark'] = $row['mark'];
            $new_arr = array();
            $content = "";
            foreach ($data as $key => $value)
            {
                array_push($new_arr, preg_replace("/\"/","\"\"","\t".$value));
            }
    
            $line = implode("\",\"",$new_arr);
            $line = "\"" .$line;
            $line .= "\"\r\n";
            $content .= $line;
            $content=@iconv("UTF-8","GBK//IGNORE",$content) ;
            echo $content;
        }
    }
    
    //修改报表  
    public function report_editAction(){
        if(IS_POST){
            $data['over_time'] = I('post.over_time');
            $data['rest_time'] = I('post.rest_time');
            $data['late_time'] = I('post.late_time');
            $data['s_vacation'] = I('post.s_vacation');
            $data['b_vacation'] = I('post.b_vacation');
            $data['nx_vacation'] = I('post.nx_vacation');
            $data['kg_time'] = I('post.kg_time');
            $data['forget_signin'] = I('post.forget_signin');
            $data['work_date'] = I('post.work_date');
            $data['differ_time'] = I('post.differ_time');
            $data['mark'] = I('post.mark');
            $data['mod_time'] = time();
            $id = I("post.rid");
            if($id){
                $ret = M('Att_reporting')->where('id='.$id)->save($data);
                if($ret){
                    $this->ajaxReturn(array('status'=>1));
                }
            }
        }
        $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
    }
    
    //月度报表计算
    public function count_dataAction(){
        //echo $this->count_over('00:43');die();
        ini_set('max_execution_time',864000);
        $month = trim(I('post.month'));
        if(!$month){
            $month = date('Y-m');
        }else{
            $month = date('Y-m',strtotime("$month +1 month"));
        }
        $y_month = date('Y-m',strtotime("$month -1 month"));
        $y_y_month = date('Y-m',strtotime("$month -2 month"));
        
        $keyword = trim(I('post.keyword'));
        $map['is_del'] = 1;
        $map['company_id'] = 1;
        if($keyword){
            $map['real_name'] = $keyword;
        }
        
        $user_list = M('User')->where($map)->select();
        foreach ($user_list as $urow) {
            $att_list = M('Attendance')->where("real_name='{$urow['real_name']}' and date_format(att_date,'%Y-%m') = '$y_month'")->select();
            $va_list =  M('Vacation')->where("is_del=1 and status=3 and real_name='{$urow['real_name']}' and (date_format(start_date,'%Y-%m') = '$y_month' or date_format(end_date,'%Y-%m') = '$y_month')")->select();
            $over_time = 0;
            $late_time = 0;
            $rest_time = 0;
            $work_date = 0;
            $s_vacation = 0;
            $b_vacation = 0;
            $nx_vacation = 0;
            $forget_signin = 0;
            $other = 0;
            $differ_time = 0;
            $kg_time = 0;
            $go_out = 0;
            $holiday_over_time = 0;
            
            foreach ($att_list as $arow) {
                if($arow['holiday']!=0){       //节假日
                    if($arow['att_start_time'] && $arow['att_end_time']){
                        $o_time = 0;
                        $o_time = $this->count_over($arow['work_time'],$arow['att_date'],$urow['real_name']);           //加班时间，非工作日工作时间等于加班时间
                        $over_time += $o_time;
                        $holiday_over_time += $o_time;
                    }
                }else{
                    //加班时间
                    $over_time += $this->count_over($arow['over_time'],$arow['att_date'],$urow['real_name']);
                    
                    //迟到
                    if($arow['late_time']){
                        $va_lates =  M('Vacation')->where("is_del=1 and status=3 and real_name='{$urow['real_name']}' and (date_format(start_date,'%Y-%m-%d') = '{$arow['att_date']}' or date_format(end_date,'%Y-%m-%d') = '{$arow['att_date']}')")->select();
                        if($va_lates){
                            foreach ($va_lates as $row1) {
                                if($row1['v_type']==1 && strtotime($row1['end_date'])<strtotime($arow['att_date']." ".$arow['att_start_time'])){
                                    $cle = strtotime($arow['att_date']." ".$arow['att_start_time'])-strtotime($row1['end_date']);
                                    $late_time += floor(($cle%(3600*24))/3600)*60+floor(($cle%(3600*24))%3600/60);
                                }
                            }
                        }else{
                            $late_time += $this->handle_hour($arow['late_time']);
                        }
                    }
                    
                    //实际出勤天
                    if($arow['att_start_time'] ||  $arow['att_end_time']){                       //实际出勤天数指工作日有打卡记录就算出勤一天
                        $work_date++;                                                            
                    }
                    
                    //忘签到
                    if((!$arow['att_start_time'] && $arow['att_end_time']) || ($arow['att_start_time'] && !$arow['att_end_time'])){
                        $forget_signin++;
                    }
                    
                    if(!$arow['att_start_time'] && !$arow['att_end_time']){
                        $a_start_date = $arow['att_date']." 09:30:00";
                        $a_end_date = $arow['att_date']." 18:30:00";
                        
                        $va_count =  M('Vacation')->where("is_del=1 and status=3 and real_name='{$urow['real_name']}' and start_date<='$a_start_date' and end_date>='$a_end_date'")->count();
                        if($va_count==0){
                            $kg_time++;
                        }
                    }
                }
            }
            
            foreach ($va_list as $vrow) {
                if(date('Y-m', strtotime($vrow['start_date'])) != $y_month){
                    $vrow['start_date'] = $y_month."-01 09:30:00";
                }
                if(date('Y-m', strtotime($vrow['end_date'])) != $y_month){
                    $first_day = $month."-01";
                    $vrow['end_date'] = date('Y-m-d', strtotime("$first_day -1 day"))." 18:30:00";
                }
                
                if($vrow['v_type'] == 1){                         //调休
                    $rest_time += $this->count_rest($vrow['start_date'],$vrow['end_date']);
                }elseif($vrow['v_type'] == 5){                    //病假
                    $b_vacation += $this->count_day($vrow['start_date'],$vrow['end_date']);
                }elseif($vrow['v_type'] == 6){                    //事假
                    $s_vacation += $this->count_day($vrow['start_date'],$vrow['end_date']);
                }elseif($vrow['v_type'] == 7){                    //年休假
                    $nx_vacation += $this->count_day($vrow['start_date'],$vrow['end_date']);
                }elseif($vrow['v_type'] > 7){                     //其他假
                    $other += $this->count_day($vrow['start_date'],$vrow['end_date']);
                }elseif($vrow['v_type'] == 3){                     //外出
                    $go_out += $this->count_day($vrow['start_date'],$vrow['end_date']);
                }
            }
            
            //$differ_time = $over_time-$rest_time;
            $differ_time = $over_time;
            $report_y = M('Att_reporting')->where(array('month'=>$y_y_month,'user_id'=>$urow['user_id']))->find();
            if($report_y){
                $differ_time += $report_y['differ_time'];
                if(date('n')!=1){
                    $holiday_over_time += $report_y['holiday_over_time'];
                }
            }
            
            //每年3月份清空上年12月的数据     
            if(date('n')==3 && $differ_time!=0){
                $y_year =  date('Y',strtotime("-1 year"));
                $month_12 = $y_year."-12";
                $report_12 = M('Att_reporting')->where("user_id={$urow['user_id']} and month = '$month_12'")->find();
                if($report_12){
                    $month_01 = date('Y')."-01";
                    $report_01 = M('Att_reporting')->where("user_id={$urow['user_id']} and month = '$month_01'")->find();
                    if($report_12['differ_time']>0){
                        $new_differ_time = $report_12['differ_time']-$report_01['rest_time']-$rest_time;
                        if($new_differ_time>0){
                            $differ_time = $report_01['over_time']+$over_time;
                        }
                    }else{
                        $new_differ_time = $report_01['over_time']-$report_01['rest_time']+$over_time-$rest_time;
                        $differ_time_1 = $new_differ_time+$report_12['differ_time'];
                        if($differ_time_1>=0){
                            $differ_time = $differ_time_1;
                        }else{
                            $differ_time = $new_differ_time;
                        }
                    }
                }
            }
            
            $is_count_holiday = I("post.is_count_holiday");
            if(!$is_count_holiday){
                $data['user_id'] = $urow['user_id'];
                $data['real_name'] = $urow['real_name'];
                $data['month'] = $y_month;
                $data['over_time'] = $over_time;
                $data['rest_time'] = $rest_time;
                $data['late_time'] = $late_time;
                $data['s_vacation'] = $s_vacation;
                $data['b_vacation'] = $b_vacation;
                $data['nx_vacation'] = $nx_vacation;
                $data['forget_signin'] = $forget_signin;
                $data['other'] = $other;
                $data['work_date'] = $work_date;
                $data['differ_time'] = $differ_time;
                $data['kg_time'] = $kg_time;
                $data['go_out'] = $go_out;
            }
            $data['holiday_over_time'] = $holiday_over_time;
            //插入
            $report = M('Att_reporting')->where(array('month'=>$y_month,'user_id'=>$urow['user_id']))->find();
            if(!$report){
                $ret = M('Att_reporting')->add($data);
            }else{
                $data['mod_time'] = time();
                $ret = M('Att_reporting')->where('id='.$report['id'])->save($data);
            }
        }
        
        $this->ajaxReturn(array('status'=>1));
    }
    
    //请假申请
    public function vacationAction(){
        $va_model = new \Home\Model\VacationModel();
        
        $where = "is_del=1";
        $is_vo_power = get_vo_power();
        if($is_vo_power){
            //关键词检索
            $real_name = I('get.keyword','','addslashes');
            if($real_name){
                $where .= " and real_name='$real_name'";
            }
            $where .= " and real_name in (select real_name from max_user where company_id=".session('company_id').")";
        }else{
            $real_name = session('real_name');
            $where .= " and real_name='$real_name'";
        }
        
        if(I('get.v_type')){
            $where .= " and v_type=".intval(I('get.v_type'));
        }
        
        //日期检索
        $start_date = I('get.start_date','','addslashes');
        $end_date = I('get.end_date','','addslashes')." 23:59:59";
        if($start_date || $start_date){
            if($start_date){
                $where .= " and start_date>='$start_date'";
            }
            if($end_date){
                $where .= " and end_date<='$end_date'";
            }
        }
        
        $count = $va_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $va_model->where($where)->order("crt_time desc")->limit($page->firstRow.','.$page->listRows)->select();
        $user_model = new \Home\Model\UserModel();
        $app_model = new \Home\Model\AttApproveModel();
        $file_model = new \Home\Model\FileModel();
        foreach ($list as &$row) {
            if($is_vo_power && !$real_name){
                $user = $user_model->get_one($row['crt_user_id']);               //申请人员
                $row['real_name'] = $user['real_name'];
            }else{
                $row['real_name'] = $real_name;
            }
            $row['type_name'] = $row['v_type'] ? $this->att_types[$row['v_type']] : "";
            $row['status_name'] = $row['status'] ? $this->att_status[$row['status']] : ""; 
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            if($row['status']!=3){
                $app_user = $user_model->get_one($row['cur_approver_id']);               //审批人员
                $row['status_name'] .= "（".$app_user['real_name']."）";
            }
            if($row['status']==4){
                $approve = $app_model->getApproveSchedule($row['v_id']);
                for ($i = 1; $i < 5; $i++) {
                    if($approve['aprv_result'.$i] == 4){
                        $row['no_agree_reason'] = $approve['aprv_opinion'.$i];
                        break;
                    }
                }
            }
            if($row['file_id']){
                $file = $file_model->get_one($row['file_id']);
                if($file){
                    $row['file_name'] = $file['file_name'];
                    $row['file_url'] = C('IMG_DOMAIN').$row['file_name'];
                } 
            }
        }
        //审批人列表
        $approve_users = getAttApproverList();
        $user_n = $user_model->get_one($this->user_id);
        //获取上个月所剩多少的调休时间
        $month = date('Y-m');
        $y_month = date('Y-m',strtotime("$month -1 month"));
        $report_data = M('Att_reporting')->where(array('month'=>$y_month,'real_name'=>$user_n['real_name']))->find();
        
        $v_type_arr = $va_model->v_type_arr;
        $this->assign('s_type_arr', $v_type_arr);
        unset($v_type_arr[4]);
        $this->assign('v_type_arr', $v_type_arr);
        $this->assign('approve_users', $approve_users);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('is_vo_power', $is_vo_power);
        $this->assign('is_approver', $this->is_approver());
        $this->assign('user', $user_n);
        $this->assign('report_data', $report_data);
        $this->display('Attendance:vacation');
    }
    
    //新增申请
    public function va_addAction(){
        $types = $this->att_types;
        if(IS_POST){
            $start_date = I('post.start_date');
            $start_time = I('post.start_time')?I('post.start_time'):'09:30';
            $end_date = I('post.end_date');
            $end_time = I('post.end_time')?I('post.end_time'):'18:30';
            $data['v_type'] = I('post.v_type',0);
            $data['reason'] = trim(I('post.reason'));
            $data['cur_approver_id'] = I('post.cur_approver_id');
            if(I('post.att_type')) $data['att_type'] = I('post.att_type');
            $data['att_id'] = I('post.att_id');
            $data['file_id'] = I('post.file_id');
            $id = I("post.v_id");
            $va_model = new \Home\Model\VacationModel();
            
            //基础数据验证
            if(!$start_date || !$start_time || !$end_date || !$end_time) $this->ajaxReturn(array('status'=>0, 'msg'=>'起止时间请填写完整！'));
            if($data['v_type']==0) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择请假类型！'));
            if($data['reason']=="") $this->ajaxReturn(array('status'=>0, 'msg'=>'请填写原因！'));
            if(!$data['cur_approver_id'] && !$id) $this->ajaxReturn(array('status'=>0, 'msg'=>'请选择审批人！'));
            
            $data['start_date'] = $start_date." ".$start_time;
            $data['end_date'] = $end_date." ".$end_time;
            if(session('lvl_id')!=1){
                if(strtotime($data['start_date'])>=strtotime($data['end_date'])){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'结束时间不能小于开始时间！'));
                }
                
                if($data['v_type'] != 5 && strtotime($start_date)<strtotime(date('Y-m-d'))){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'开始日期不能请小于今天！'));
                }
            }
            
            if($data['v_type'] == 1){          //计算调休时间
                if($this->check101()){
                    $rest_time = $this->count_rest($data['start_date'], $data['end_date']);
                    //获取上个月所剩多少的调休时间
                    $month = date('Y-m');
                    $y_month = date('Y-m',strtotime("$month -1 month"));
                    $report = M('Att_reporting')->where(array('month'=>$y_month,'user_id'=>$this->user_id))->find();
                    
                    if(!$report || floatval($report['differ_time'])<$rest_time){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'你没有足够可调休时间！'));
                    }
                }
            }
            
            $day = $this->count_day($data['start_date'], $data['end_date']);
            $user_model = new \Home\Model\UserModel();
            if($data['v_type'] == 7 && time()>1488297600){          //
                $user_n = $user_model->get_one($this->user_id);
                if($day>$user_n['nx_vacation']){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'所剩年假天数不足！'));
                }
            }
            
            if($data['v_type'] == 8 || $data['v_type'] == 9){
                if($day>1){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'郁闷假或亲子假每季度只能请一天！'));
                }
                
                //判断该季度是否已经请过郁闷假或者亲子假
                $season = ceil((date('n'))/3);//当月是第几季度
                $quarter_start = date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y')));
                $quarter_end = date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y')));
                if($id){
                    $va_quarter_count = $va_model->where("crt_user_id={$this->user_id} and start_date>'$quarter_start' and end_date<'$quarter_end' and v_type={$data['v_type']} and is_del=1 and v_id<>$id and status!=4")->count();
                }else{
                    $va_quarter_count = $va_model->where("crt_user_id={$this->user_id} and start_date>'$quarter_start' and end_date<'$quarter_end' and v_type={$data['v_type']} and is_del=1 and status!=4")->count();
                }
                
                if($va_quarter_count>0){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'本季度只能请一天！'));
                }
            }
            
            
            if($data['att_id']){
                $attendace = M('Attendance')->where('att_id='.$data['att_id'])->find();
                if($data['att_type'] == 1){
                    if(!$attendace['att_start_time']) $attendace['att_start_time'] = "09:30";
                    if(strtotime($attendace['att_date']." ".$attendace['att_start_time'])>strtotime($data['end_date'])){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'请假结束时间不能小于签到时间！'));
                    }
                }elseif($data['att_type'] == 2){
                    if(!$attendace['att_end_time']) $attendace['att_start_time'] = "18:30";
                    if(strtotime($attendace['att_date']." ".$attendace['att_end_time'])<strtotime($data['start_date'])){
                        $this->ajaxReturn(array('status'=>0, 'msg'=>'请假开始时间不能到于签退时间！'));
                    }
                }
            }
            
            //判断是否可以更新
            $app_model = new \Home\Model\AttApproveModel();
            $approve = null;
            $vacation = null;
            $action = "";
            if(isset($id) && is_numeric($id)){
                $id = intval($id);
                $vacation = $va_model->get_one($id);
                $approve = $app_model->getApproveSchedule($vacation['v_id']);
                if(session('lvl_id')!=1 && $approve['aprv_result1']!=0){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可编辑！'));
                }
            }
        
            //新增、更新数据
            $va_model->startTrans();     //开启事务
            if($id){
                if(session('lvl_id')==1){
                    $data['crt_time'] = strtotime($data['start_date']);
                }
                
                $ret = $va_model->update_data($data, "v_id = $id");  
                if($ret && $vacation['cur_approver_id'] != $data['cur_approver_id']){
                    $ret1 = $app_model->updateApproved($approve, $data['cur_approver_id']);
                }else{
                    $ret1 = true;
                }
                $action = "update";
            }else{
                $data['crt_user_id'] = $this->user_id;
                $data['real_name'] = session('real_name');
                $data['status'] = 1;
                $ret = $va_model->insert_data($data);                               //插入预算单表
                if($ret){
                    $ret1 = $app_model->insert_data(array('v_id'=>$ret,'aprv_user_id1'=>$data['cur_approver_id'],'crt_user_id'=>$data['crt_user_id']));
                    $id = $ret;
                }
                $action = "insert";
            }
            
            if($ret && $ret1){
                if($data['v_type'] == 7 || $vacation['v_type'] == 7){      
                    if($vacation['v_type'] == 7 && $data['v_type'] != 7){             //如果一开始是年假修改成其他假
                        $day_v = $this->count_day($vacation['start_date'], $vacation['end_date']);
                        $ret2 = $user_model->where('user_id='.$this->user_id)->setInc('nx_vacation',$day_v);
                    }elseif($vacation['v_type'] == 7 && $data['v_type'] == 7){            //如果修改成年假修
                        $day_v = $this->count_day($vacation['start_date'], $vacation['end_date']);
                        $day_c = abs($day_v-$day);
                        if($day_v>$day){
                            $ret2 = $user_model->where('user_id='.$this->user_id)->setInc('nx_vacation',$day_c);
                        }elseif($day_v<$day){
                            $ret2 = $user_model->where('user_id='.$this->user_id)->setDec('nx_vacation',$day_c);
                        }else{
                            $ret2 = true;
                        }
                    }elseif((!$vacation || $vacation['v_type'] != 7) && $data['v_type'] == 7){            //如果修改成年假修
                        $ret2 = $user_model->where('user_id='.$this->user_id)->setDec('nx_vacation',$day);
                    }
                }else{
                    if($data['v_type'] == 1){          //计算调休时间
                        if($this->check101()){
                            $differ_time = floatval($report['differ_time']);
                            $differ_time = $differ_time-$rest_time;
                            $ret2 = M('Att_reporting')->where(array('id'=>$report['id']))->save(array('differ_time'=>$differ_time));
                        }else{
                            $ret2 = true;
                        }
                    }else{
                        $ret2 = true;
                    }
                }
                
                if($ret2){
                    if($va_model->commit()){
                        logrecords($action, $va_model->get_tablename());          //日志
                        $this->ajaxReturn(array('status'=>1));
                    }
                }
            }
            $va_model->rollback();
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    }
    
    //删除用户操作
    public function va_deleteAction(){
        if(IS_POST){
            $id = I('post.v_id');
            $va_model = new \Home\Model\VacationModel();
            $vacation = $va_model->get_one($id);
            if($vacation){
                $app_model = new \Home\Model\AttApproveModel();
                if(!check_permission_left("Attendance", "vacation_del") && ($vacation['status']==2 || $vacation['status']==3)){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'已进入审批流程，不可删除！'));
                }
    
                $ret = $va_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "v_id = $id");
                if($ret){
                    $ret1 = $app_model->update_data(array('mod_user_id'=>$this->user_id, 'is_del'=>-1), "v_id=$id");
                    /* if($vacation['att_type'] == 1){
                        $data['start_type'] = 0;
                        $data['start_status'] = 0;
                    }else{
                        $data['end_type'] = 0;
                        $data['end_status'] = 0;
                    }
                    $ret2 = M('Attendance')->where('att_id='.$vacation['att_id'])->save($data); */
                    
                    if($vacation['v_type'] == 7){             //如果一开始是年假修改成其他假
                        $day_v = $this->count_day($vacation['start_date'], $vacation['end_date']);
                        $ret2 = M('User')->where("real_name='{$vacation['real_name']}'")->setInc('nx_vacation',$day_v);
                    }elseif($vacation['v_type'] == 1){
                        if($this->check101()){
                            $ret2 = $this->back_rest($vacation);
                        }
                    }
                    
                    logrecords('delete', $va_model->get_tablename());
                    $this->ajaxReturn(array('status'=>1));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'请假申请不存在！'));
        }
    }
    
    //审批管理
    public function approveAction(){
        $lvl_id = session('lvl_id');
        $user_id = $this->user_id;
        $app_model = new \Home\Model\AttApproveModel();
        $is_vo_power = check_permission_left("Attendance", "approve_all");
        if($is_vo_power){
            $where = "is_del = 1";
            $where .= " and crt_user_id in (select user_id from max_user where company_id=".session('company_id').")";
        }else{
            $where = "is_del = 1 and (aprv_user_id1 = {$this->user_id} or aprv_user_id2 = {$this->user_id} or aprv_user_id3 = {$this->user_id}  or aprv_user_id4 = {$this->user_id})";
        }
        
        //状态检索
        $way = I('get.way',2);
        if(is_numeric($way) && $way > 0){
            if($way == '1'){
                if($is_vo_power){
                    $where .= " and result=1";
                }else{
                    $where .= " and ((aprv_user_id1 = {$this->user_id} and aprv_result1!=0) or (aprv_user_id2 = {$this->user_id} and aprv_result2!=0) or (aprv_user_id3 = {$this->user_id} and aprv_result3!=0) or (aprv_user_id4 = {$this->user_id} and aprv_result4!=0))";
                }
            }else{
                if($is_vo_power){
                    $where .= " and result=0";
                }else{
                    $where .= " and ((aprv_user_id1 = {$this->user_id} and aprv_result1=0) or (aprv_user_id2 = {$this->user_id} and aprv_result2=0) or (aprv_user_id3 = {$this->user_id} and aprv_result3=0) or (aprv_user_id4 = {$this->user_id} and aprv_result4=0))";
                }
            }
        }
        
        //日期检索
        $start_date = I('get.start_date','','addslashes');
        $end_date = I('get.end_date','','addslashes');
        if($start_date || $start_date){
            if($is_vo_power){
                $where1 = "is_del=1";
                if($start_date){
                    $where1 .= " and start_date>='$start_date'";
                }
                if($end_date){
                    $where1 .= " and end_date<='$end_date'";
                }
                $v_ids = M("vacation")->where($where1)->getField('v_id',true);
                if($v_ids){
                    $where .= " and v_id in (".implode(",", $v_ids).")";
                }else{
                    $where .= " and 1!=1";
                }
            }else{
                if($start_date){
                    $where .= " and crt_time>=".strtotime($start_date);
                }
                if($end_date){
                    $where .= " and crt_time<=".strtotime($end_date." 23:59:59");
                }
            }
        }
        
        //关键词检索
        $user_model = new \Home\Model\UserModel();
        $va_model = new \Home\Model\VacationModel();
        $file_model = new \Home\Model\FileModel();
        $keyword = I('get.keyword','','addslashes');
        if($keyword){
            $ids = $user_model->where("real_name like '%$keyword%'")->getField('user_id',true);
            if($ids){
                $where .= " and crt_user_id in (".implode(",", $ids).")";
            }else{
                $where .= " and 1!=1";
            }
        }
        
        $count = $app_model->where($where)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $list = $app_model->where($where)->order('crt_time desc')->limit($page->firstRow.','.$page->listRows)->select();
        
        foreach ($list as &$row) {
            $vacation = $va_model->get_one($row['v_id']);
            $row['v_type'] = $this->att_types[$vacation['v_type']];
            $row['start_date'] = $vacation['start_date'];
            $row['end_date'] = $vacation['end_date'];
            $row['reason'] = $vacation['reason'];
            $row['crt_time'] = date('Y-m-d H:i:s', $row['crt_time']);
            
            $apply_userid = $row['crt_user_id'];
            $user = $user_model->get_one($apply_userid);               //申请人员
            $row['real_name'] = $user['real_name'];
            $row['apply_depart_id'] = $user['depart_id']; 
        
            if($user){
                $row['depart_name'] = M('Depart')->where('depart_id='.$user['depart_id'])->getField('depart_name');
            }
            
            
            if($row['result']==1){                                    //管理员
                $row['result_info'] = '流程已完成';
            }else{
                for ($i = 1; $i < 5; $i++) {
                    if($row['aprv_result'.$i] == 0 && $row['aprv_user_id'.$i]!=0){
                        $row['result_info'] = '等待审批';
                        break;
                    }elseif($row['aprv_result'.$i] != 0 && $row['aprv_user_id'.($i+1)]==0){
                        if($row['aprv_result'.$i]==1){
                            $row['result_info'] = '同意';
                        }elseif($row['aprv_result'.$i]==4){
                            $row['result_info'] = '不同意';
                        }
                        break;
                    }
                }
                $app_user = $user_model->get_one($row['aprv_user_id'.$i]);               //审批人员
                $row['result_info'] = $row['result_info']."（".$app_user['real_name']."）";
            }
            $row['is_approve'] = true;
            if($app_user['user_id']!=$user_id || $row['result']){
                $row['is_approve'] = false;
            }
            if($vacation['file_id']){
                $file = $file_model->get_one($vacation['file_id']);
                if($file){
                    $row['file_name'] = $file['file_name'];
                    $row['file_url'] = C('IMG_DOMAIN').$row['file_name'];
                }
            }
        }
        $this->assign('way',$way);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('aide_userid', get_aide_userid());
        $this->assign('zjl_userid', get_zjl_userid());
        $this->display('Attendance:approve');
    }
    
    //审批操作
    public function operateAction(){
        if(IS_POST){
            $aprv_id = I('post.aprv_id');
            $result = I('post.result');
            $reason = I('post.reason');
            
            $app_model = new \Home\Model\AttApproveModel();
            $va_model = new \Home\Model\VacationModel();
            $user_model = new \Home\Model\UserModel();
            $approve = $app_model->get_one($aprv_id);
            if($approve){
                $user_id = $this->user_id;
                $lvl_id = session('lvl_id');
                for ($i = 1; $i < 5; $i++) {
                    if($approve['aprv_user_id'.$i] == $user_id && $approve['aprv_result'.$i] == 0){
                        break;
                    }
                }
                $data['aprv_time'.$i] = time();
                $data['aprv_result'.$i] = $result;
                
                //当不同意
                $vacation = $va_model->get_one($approve['v_id']);
                if($result == 2){
                    $data['result'] = $data['aprv_result'.$i] = 4;
                    $data['aprv_opinion'.$i] = $reason;
                }else{
                    //审批人是总经理
                    if($lvl_id == 2){
                        $data['result'] = $data['aprv_result'.$i];
                    }else{
                        $aid_userid = get_aide_userid();
                        if(($lvl_id==5 && $user_id!=$aid_userid)|| $user_id==get_cw_userid()){              //审批人为部门领导或者财务领导Peter
                            //判断是否要提交给总经理
                            $isto_aid = $this->check_isto_aid($vacation);
                            if($isto_aid){
                                $data['aprv_user_id'.($i+1)] = $aid_userid;  //申请日期为3天以上的提交给总经办
                                $data['mark'] = "连续请假超过3天";
                            }else{
                                $data['result'] = $data['aprv_result'.$i];
                            }
                        }elseif($user_id == $aid_userid){                    //coco审批
                            if($result!=3){
                                $data['result'] = $data['aprv_result'.$i];
                            }else{
                                $data['aprv_user_id'.($i+1)] = get_zjl_userid();
                                $data['aprv_result'.$i] = 1;
                                $data['aprv_opinion'.$i] = $reason;
                            }
                        }else{                                       //审批人为AM以上级别
                            $depart_id = session('depart_id');
                            $depart_par_id = session('depart_par_id');
                            
                            if($user_id==531){
                                $data['aprv_user_id'.($i+1)] = 24;
                            }else{
                                $leader = $user_model->get_onebywhere("is_del=1 and lvl_id=5 and (depart_id=$depart_id or depart_id=$depart_par_id)");
                                $data['aprv_user_id'.($i+1)] = $leader['user_id'];
                            }
                        }
                    }
                }
                
                $app_model->startTrans();
                $ret = $app_model->update_data($data, "aprv_id=$aprv_id");
                if($ret){
                    $v_data = array();
                    if($data['aprv_user_id'.($i+1)]){
                        $v_data['cur_approver_id'] = $data['aprv_user_id'.($i+1)];
                    }
                    if($data['result']==1){
                        $v_data['status'] = 3;
                    }elseif($data['result']==4){
                        $v_data['status'] = 4;
                    }else{
                        //$v_data['status'] = 2;
                    }
                    if($v_data){
                        $ret1 = $va_model->update_data($v_data, "v_id=".$approve['v_id']);
                    }else{
                        $ret1 = true;
                    }
                    
                    //更新考勤记录表
                    if($ret1){
                        if($vacation['v_type'] == 7 && $result == 2){         
                            $day_v = $this->count_day($vacation['start_date'], $vacation['end_date']);
                            $ret2 = $user_model->where('user_id='.$vacation['crt_user_id'])->setInc('nx_vacation',$day_v);
                        }else{
                            if($vacation['v_type'] == 1 && $result == 2 && $this->check101()){
                                $ret2 = $this->back_rest($vacation);
                            }else{
                                $ret2 = true;
                            }
                        }
                        
                        if($ret2){
                            if($app_model->commit()){
                                logrecords("update", $app_model->get_tablename());          //日志
                                $this->ajaxReturn(array('status'=>1));
                            }
                        }
                    }
                }
                $app_model->rollback();          //回滚
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    }
    
    //ajax获取va数据
    public function getvaAction(){
        if(IS_POST){
            $id = I('post.v_id');
            $va_model = new \Home\Model\VacationModel();
            $vacation = $va_model->get_one($id);
            if($vacation){
                $vacation['start_date_d'] = date('Y-m-d',strtotime($vacation['start_date']));
                $vacation['start_date_t'] = date('H:i',strtotime($vacation['start_date']));
                $vacation['end_date_d'] = date('Y-m-d',strtotime($vacation['end_date']));
                $vacation['end_date_t'] = date('H:i',strtotime($vacation['end_date']));
                
                $this->ajaxReturn(array('status'=>1,'vacation'=>$vacation));
            }
            $this->ajaxReturn(array('status'=>0, 'msg'=>'保存失败！'));
        }
    }
    
    //是否审批人
    public function is_approver(){
        $lvl_id = session('lvl_id');
        $user_id = session('user_id');
        if($lvl_id<9 && $lvl_id!=4){
            if($lvl_id==3 && $user_id!=get_cw_userid()){
                return false;
            }
            return true;
        }
    }

    //计算加班时间
    function count_over($time2, $date, $real_name){
        if($time2 && $time2_arr = explode(":", $time2)){
            $overtime = M("Overtime")->where(array('real_name'=>$real_name, "o_date"=>$date, 'status'=>2))->find();
            if($overtime){             //加班申请并通过的，计算加班时间
                $hour2 = intval($time2_arr[0]);
                $minute2 = intval($time2_arr[1]);
                
                if($minute2>30){
                    $minute2 = 0.5;
                }else{
                    $minute2 = 0;
                }
                return $hour2+$minute2;
            }
        }
        return 0;
    }
    
    //计算调休时间
    function count_rest($att_start_time, $att_end_time){
        $days = 0;
        $start_arr = explode(" ", $att_start_time);
        $end_arr = explode(" ", $att_end_time);
        $start_date = $start_arr[0];
        $end_date = $end_arr[0];
        $start_time = $start_arr[1];
        $end_time = $end_arr[1];
        if(floatval(str_replace(":", ".", $start_time))<9.3){
            $start_time = "09:30";
        }
        if(floatval(str_replace(":", ".", $end_time))>18.3){
            $end_time = "18:30";
        }
        $att_start_time = $start_date." ".$start_time;
        $att_end_time = $end_date." ".$end_time;
        
        $one = strtotime($att_start_time);         //开始时间 时间戳
        $tow = strtotime($att_end_time);           //结束时间 时间戳
        $cle = $tow - $one;                        //得出时间戳差值
        $day = floor(($cle/(3600*24)));
        $hour = floor(($cle%(3600*24))/3600);
        $minute = floor(($cle%(3600*24))%3600/60);
        
        if($day>0){
            $max = $day+1;
            for ($i = 0; $i < $max; $i++) {
                $date = date('Y-m-d', strtotime("$start_date +$i day"));
                $holiday = $this->check_holiday($date);
                if($holiday>0){
                    $day = $day-1;
                }
            }
        }
        
        if($minute>30){
            $hour += 1;
        }elseif($minute>0){
            $hour += 0.5;
        }
        
        if($hour>8){
            $hour = 8;
        }
        
        return $hour+$day*8;
    }
    
    //处理时间点
    function count_hour($att_start_time, $att_end_time){
        $one = strtotime($att_start_time);         //开始时间 时间戳
        $tow = strtotime($att_end_time);           //结束时间 时间戳
        $cle = $tow - $one;                        //得出时间戳差值
        
        $hour = sprintf("%02d",floor(($cle%(3600*24))/3600));  
        $minute = sprintf("%02d",floor(($cle%(3600*24))%3600/60));
        return $hour.":".$minute;
    }
    
    //处理时间点（返回数字）
    function count_hour_double($att_start_time, $att_end_time){
        $one = strtotime($att_start_time);         //开始时间 时间戳
        $tow = strtotime($att_end_time);           //结束时间 时间戳
        $cle = $tow - $one;                        //得出时间戳差值
    
        $hour = floor(($cle%(3600*24))/3600);
        $minute = floor(($cle%(3600*24))%3600/60);
        return $hour.".".$minute;
    }
    
    //把时间转换成数字
    function handle_hour($time){
        if($time && $time_arr = explode(":", $time)){
            $hour = intval($time_arr[0]);
            $minute = intval($time_arr[1]);
            return $hour*60+$minute;
        }
        return 0;
    }
    
    //计算天数
    function count_day($att_start_time, $att_end_time){
        $days = 0;
        $start_arr = explode(" ", $att_start_time);
        $end_arr = explode(" ", $att_end_time);
        $start_date = $start_arr[0];
        $end_date = $end_arr[0];
        $start_time = $start_arr[1];
        $end_time = $end_arr[1];
        if(floatval(str_replace(":", ".", $start_time))<9.3){
            $start_time = "09:30";
        }
        if(floatval(str_replace(":", ".", $end_time))>18.3){
            $end_time = "18:30";
        }
        $att_start_time = $start_date." ".$start_time;
        $att_end_time = $end_date." ".$end_time;
        
        $one = strtotime($att_start_time);         //开始时间 时间戳
        $tow = strtotime($att_end_time);           //结束时间 时间戳
        $cle = $tow - $one;                        //得出时间戳差值
        $day = floor(($cle/(3600*24))); 
        
        if($day>0){
            $hour = $this->count_hour_double($start_time, $end_time);
            $max = $day+1;
            for ($i = 0; $i < $max; $i++) {
                $date = date('Y-m-d', strtotime("$start_date +$i day"));
                $holiday = $this->check_holiday($date);
                if($holiday>0){
                    $day = $day-1;
                }
            }
        }else{
            $hour = $cle%(3600*24)/3600;
        }
        if($hour>=5){
            $days = 1;
        }else{
            $days = 0.5;
        }
        $days = $days+$day;
        return $days;
    }

    function count_work($att_start_time, $att_end_time){
        $att_date = "2016-08-22";
        if($att_start_time || $att_end_time){
            if(!$att_start_time){                    //没有上班打卡时间，默认9:30
                $att_start_time = "09:30";
            }
            if(!$att_end_time){                      //没有下班打卡时间，默认18:30
                $att_end_time = "18:30";
            }
            $att_start_time = $att_date." ".$att_start_time;
            $end_arr = explode(":", $att_end_time);
            $end_hour = ltrim($end_arr[0],"0");
            if($end_hour < 8 || !$end_hour){
                $att_date = "2016-08-23";
            }
            $att_end_time = $att_date." ".$att_end_time;
            return $this->count_hour($att_start_time, $att_end_time);
        }
        return "";
    }
    
    //查询
    function check_holiday($date){
        $obj = M('AttHoliday');
        $date = date('Y-m-d',strtotime($date));
        $holiday = $obj->where("att_date='$date'")->find();
        if($holiday){
            $ret = $holiday['type'];
        }else{
            $date_d = date('Ymd',strtotime($date));
            $ch = curl_init();
            $url = "http://www.easybots.cn/api/holiday.php?d==$date_d";
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // 执行HTTP请求
            curl_setopt($ch , CURLOPT_URL , $url);
            $res = curl_exec($ch);
            $return = json_decode($res,true);
            $return = $return[$date_d];
            
            if($return==0 || $return==1 || $return==2){
                $obj->add(array('att_date'=>$date,'type'=>$return));
                $ret = $return;
            }
        }
        
        return $ret;
    }

    //检查是否提交给总办
    function check_isto_aid($vacation){
        $obj = $vacation;
        if(!$obj){
            return false;
        }
        
        $limit_day_y = 3;
        $day_n = 3;
        $va_model = new \Home\Model\VacationModel();
        for ($i = 0; $i < 15; $i++) {
            if($limit_day_y<1){
                return false;
            }
            $day_v = $this->count_day($obj['start_date'], $obj['end_date']);
            if($day_v>=$day_n){
                return true;
            }
            $day_n -= $day_v;
            
            $start_arr = explode(" ", $obj['start_date']);
            $start_date = $start_arr[0];
            $start_time = $start_arr[1];
            
            $start_time_num = floatval(str_replace(":", ".", $start_time));
            $where = "";
            if($start_time_num<=9.3){
                $y_date = "";
                for ($j = 1; $j < 15; $j++) {
                    $y_date = date('Y-m-d',strtotime("$start_date -$j day"));
                    $holiday = $this->check_holiday($y_date);
                    
                    if($holiday==0){
                        break;
                    }
                }
                
                $where = " and end_date>='".$y_date." 18:30' and end_date<'".$start_date." 09:30'";
            }/* elseif($start_time_num>=12.3 && $start_time_num<13.3){
                $where = " and end_date<='".$start_date." 13:30'";
            }else */{
                $where = " and end_date>='".$start_date."' and end_date<='".$start_date." ".$start_time."'";
            }
            $row = $va_model->where("is_del=1 and status!=4 and crt_user_id={$obj['crt_user_id']} $where")->order("end_date desc")->limit(0)->select();
            if(!$row){
                break;
            }
            $obj = $row[0];
            $limit_day_y--;
        }
        
        //如果日期向前的连续天数未满3天，则检查是否有日期向后的数据
        $limit_day_n = 3;
        $obj1 = $vacation;
        $data = $va_model->where("is_del=1 and status!=4 and crt_user_id={$obj1['crt_user_id']} and start_date>'{$obj1['end_date']}'")->select();
        if($data){
            for ($k = 0; $k < 15; $k++) {
                if($limit_day_n<1){
                    return false;
                }
                $end_arr = explode(" ", $obj1['end_date']);
                $end_date = $end_arr[0];
                $end_time = $end_arr[1];
                
                $end_time_num = floatval(str_replace(":", ".", $end_time));
                $where = "";
                if($end_time_num>=18.3){
                    $n_date = "";
                    for ($g = 1; $g < 15; $g++) {
                        $n_date = date('Y-m-d',strtotime("$end_date +$g day"));
                        $holiday = $this->check_holiday($n_date);
                        if($holiday==0){
                            break;
                        }
                    }
                
                    $where = " and start_date>='".$n_date."' and start_date<='".$n_date." 09:31:00'";
                }else{
                    $where = " and start_date>='".$end_date." ".$end_time."' and start_date<'".$end_date." 23:59:59'";
                }
                $row1 = $va_model->where("is_del=1 and status!=4 and crt_user_id={$obj['crt_user_id']} $where")->order("start_date asc")->limit(0)->select();
                if(!$row1){
                    return false;
                }
                $obj1 = $row1[0];
                $day_v = $this->count_day($obj1['start_date'], $obj1['end_date']);
                if($day_v>=$day_n){
                    return true;
                }
                $day_n -= $day_v;
                $limit_day_n--;
            }
        }
        
        return false;
    }

    public function back_rest($vacation){
        //if($vacation['v_id']) return false;
        $rest_time = $this->count_rest($vacation['start_date'], $vacation['end_date']);
        $month = date('Y-m');
        $y_month = date('Y-m',strtotime("$month -1 month"));
        $report = M('Att_reporting')->where(array('month'=>$y_month,'user_id'=>$vacation['crt_user_id']))->find();
        if($report){
            $differ_time = floatval($report['differ_time']);
            $differ_time = $differ_time+$rest_time;
            $ret = M('Att_reporting')->where(array('id'=>$report['id']))->save(array('differ_time'=>$differ_time));
            return $ret;
        }
        return false;
    }
    
    public function check101(){
        if(time()>1506787200 && session('company_id')==1){
            return true;
        }
        return false;
    }
}