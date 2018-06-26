<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;" name="viewport" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>

    <title>Max|智源动力传播机构</title>

    <link rel="shortcut icon" href="/oa/oa_test/Public/common/img/max.ico"/>
    <link rel="stylesheet" type="text/css" href="/oa/oa_test/Public/common/css/bootstrap/bootstrap.min.css"/>
	<script src="/oa/oa_test/Public/common/js/demo-rtl.js"></script>
    <!-- <link rel="stylesheet" type="text/css" href="/oa/oa_test/Public/common/css/libs/font-awesome.css"/> -->
    <link rel="stylesheet" type="text/css" href="/oa/oa_test/Public/common/css/libs/nanoscroller.css"/>
    

    <link rel="stylesheet" type="text/css" href="/oa/oa_test/Public/common/css/compiled/all.css"/>
	<link rel="stylesheet" type="text/css" href="/oa/oa_test/Public/common/css/libs/nifty-component.css"/>
    <link rel="stylesheet" href="/oa/oa_test/Public/common/css/libs/bootstrap-datepicker3.min.css" type="text/css"/>
    <link rel="stylesheet" href="/oa/oa_test/Public/common/css/libs/select2.css" type="text/css"/>

    <!--[if lt IE 9]>
    <script src="/oa/oa_test/Public/common/js/html5shiv.js"></script>
    <script src="/oa/oa_test/Public/common/js/respond.min.js"></script>
    <![endif]-->

	<script src="/oa/oa_test/Public/common/js/jquery.js"></script>
	<script type="text/javascript" src="/oa/oa_test/Application/Home/Public/js/alert.js"></script>
<script src="/oa/oa_test/Public/common/js/bootstrap.js"></script>
<script src="/oa/oa_test/Public/common/js/bootstrap-datepicker.js"></script>
</head>
<body class="pace-done theme-turquoise">
<div id="loading" style="display:none;">
	<img src="/oa/oa_test/Application/Home/Public/images/loading.gif"/>
</div>
<div id="theme-wrapper">

    <header class="navbar" id="header-navbar">
        <div class="container">
            <a href="javascript:;" id="logo" class="navbar-brand">
                <img src="/oa/oa_test/Public/common/img/logo.png" alt="" class="normal-logo logo-white"/>
                <img src="/oa/oa_test/Public/common/img/logo-black.png" alt="" class="normal-logo logo-black"/>
                <img src="/oa/oa_test/Public/common/img/logo-small.png" alt="" class="small-logo hidden-xs hidden-sm hidden"/>
            </a>

            <div class="clearfix">
                <button class="navbar-toggle" data-target=".navbar-ex1-collapse" data-toggle="collapse" type="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="fa" id="ali_font">&#xe680;</span>
                </button>
                <div class="nav-no-collapse navbar-left pull-left hidden-sm hidden-xs">
                    <ul class="nav navbar-nav pull-left">
                        <li>
                            <a class="btn" id="make-small-nav">
                                <i class="fa" id="ali_font">&#xe680;</i>
                            </a>
                        </li>
                    </ul>
                </div>


                <div class="nav-no-collapse pull-right" id="header-nav">
                <ul class="nav navbar-nav pull-right">
                    <li class="profile-dropdown">
                        <a href="javascript:;">
                            <img src="/oa/oa_test/Public/common/img/samples/scarlet-159.png" alt=""/>
                            <span class="translateY"><?php if(!empty($user_depart_name)): echo ($user_depart_name); ?>-<?php endif; if(!empty($user_lvl_name)): echo ($user_lvl_name); ?>-<?php endif; echo ($real_name); ?></span>
                        </a>
                    </li>
                    <li class="hidden-xxs">
                        <a class="btn" href="<?php echo U('Login/logout');?>">
                            <i class="fa f-size20" id="ali_font">&#xe608;</i>
                        </a>
                    </li>
                </ul>
            </div>



            </div>
        </div>
    </header>
	<div id="page-wrapper" class="container">
        <div class="row">
            
<div id="nav-col">
<?php $is_admin = is_admin_power(); ?>
    <section id="col-left" class="col-left-nano">
        <div id="col-left-inner" class="col-left-nano-content">
            <!-- <div id="user-left-box" class="clearfix hidden-sm hidden-xs">
                <img alt="" src="/oa/oa_test/Public/common/img/samples/scarlet-159.png"/>
            
                <div class="user-box">
                <span class="name">
                Welcome
                </span>
                <span class="status">
                <i class="fa fa-circle"></i> Online
                </span>
                </div>
            </div> -->
            <div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">
				<!---后台管理者-->
                <ul class="nav nav-pills nav-stacked">
				
					<!-- 二级菜单li>
                        <a href="#" class="dropdown-toggle">
                            <i class="fa fa-group"></i>
                            <span>部门管理</span>
                            <i class="fa fa-chevron-circle-right drop-icon"></i>
                        </a>
                        <ul class="submenu">
                            <li >
                                <a href="{:U('Department/index')}" >
                                    所有部门
                                </a>
                            </li>
                            <li >
                                <a href="{:U('Department/add')}" >
                                    新增部门
                                </a>
                            </li>
                        </ul>
                    </li-->
					
                    <li <?php if($controller_name == 'Project'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Project/index');?>">
                            <i class="fa">
                                &#xe601;
                            </i>
                            <span class="icon_text">项目管理</span>
                        </a>
                    </li>
                    <?php if($user_id == 33 or $is_admin == 1): ?><li <?php if($controller_name == 'Contract'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Contract/list');?>">
                            <i class="fa">&#xe602;</i>
                            <span class="icon_text">合同管理</span>
                        </a>
                    </li><?php endif; ?>
                    <li <?php if($controller_name == 'Budget'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Budget/index');?>">
                            <i class="fa">&#xe603;</i>
                            <span class="icon_text">项目预算单</span>
                        </a>
                    </li>
                    <li <?php if($controller_name == 'Borrow'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Borrow/index');?>">
                            <i class="fa">&#xe654;</i>
                            <span class="icon_text">借款单</span>
                        </a>
                    </li>
                    <li <?php if($controller_name == 'Pfrecouped'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Pfrecouped/index');?>">
                            <i class="fa">&#xe678;</i>
                            <span class="icon_text">个人报销单</span>
                        </a>
                    </li>
                    <?php if(check_permission_left('Teambuild', 'add')){ ?>
                    <li <?php if($controller_name == 'Teambuild' OR $controller_name == 'Tbrecouped'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Teambuild/index');?>" >
                            <i class="fa">&#xe6b0;</i>
                            <span class="icon_text">TB管理</span>
                        </a>
                    </li>
                    <?php } ?>
                    <li <?php if($controller_name == 'Recouped'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Recouped/index');?>">
                            <i class="fa">&#xe622;</i>
                            <span class="icon_text">项目报销单</span>
                        </a>
                    </li>
                    <li <?php if($controller_name == 'Refund'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Refund/index');?>">
                            <i class="fa">&#xe807;</i>
                            <span class="icon_text">还款单</span>
                        </a>
                    </li>
                    <li <?php if($controller_name == 'Approve'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Approve/index');?>">
                            <i class="fa">&#xe63c;</i>
                            <span class="icon_text">审批任务</span>
                        </a>
                    </li>
                    <?php if($user_lvl_id < 4): ?><li <?php if($controller_name == 'Customer'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Customer/index');?>" >
                            <i class="fa">&#xe627;</i>
                            <span class="icon_text">客户库</span>
                        </a>
                    </li><?php endif; ?>
                    <?php if($_SESSION['is_supplier_creat']== 1 or $_SESSION['is_supplier_show']== 1): ?><li <?php if($controller_name == 'Supplier'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Supplier/index');?>">
                            <i class="fa">&#xe801;</i>
                            <span class="icon_text">供应商库</span>
                        </a>
                    </li><?php endif; ?>
                    
                    <?php if(check_permission_left('Mjsupplier', 'index')){ ?>
                    <li <?php if($controller_name == 'Mjsupplier'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Mjsupplier/index');?>">
                            <i class="fa">&#xe687;</i>
                            <span class="icon_text">媒介供应商库</span>
                        </a>
                    </li>
                    <?php } ?>
                    
                    <li <?php if($controller_name == 'User' or $controller_name == 'Depart'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('User/index','','');?>">
                            <i class="fa">&#xe600;</i>
                            <span class="icon_text">用户库</span>
                        </a>
                    </li>
                    
                    <li <?php if($controller_name == 'Book'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Book/index');?>" >
                            <i class="fa">&#xe6f5;</i>
                            <span class="icon_text">通讯录</span>
                        </a>
                    </li>
                    
                    <?php if(check_permission_left('Notice', 'add')){ ?>
                    <li <?php if($controller_name == 'Notice'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Notice/add');?>" >
                            <i class="fa">&#xe7ac;</i>
                            <span class="icon_text">公告设置</span>
                        </a>
                    </li>
                    <?php } ?>
                    
                    <?php if($user_lvl_id < 5 and $user_lvl_id > 1): ?><li <?php if($controller_name == 'Contract'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Contract/index');?>" >
                            <i class="fa">&#xe602;</i>
                            <span class="icon_text">合同管理</span>
                        </a>
                    </li><?php endif; ?>
                    <li <?php if($controller_name == 'Attendance'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Attendance/vacation');?>" >
                            <i class="fa">&#xe749;</i>
                            <span class="icon_text">考勤管理</span>
                        </a>
                    </li>
                    <li <?php if($controller_name == 'Overtime'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Overtime/index');?>" >
                            <i class="fa">&#xe6b0;</i>
                            <span class="icon_text">加班管理</span>
                        </a>
                    </li>
                    <li <?php if($controller_name == 'OfficeSupplies' OR $controller_name == 'OfficeSuppliesMain' OR $controller_name == 'OfficeSuppliesRecord'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('officeSupplies/index');?>" >
                        <i class="fa">&#xe605;</i>
                        <span class="icon_text">办公用品</span>
                        </a>
                    </li>
                    <li <?php if($controller_name == 'CardRecord'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('CardRecord/index');?>" >
                        <i class="fa">&#xe63f;</i>
                        <span class="icon_text">名片申请</span>
                        </a>
                    </li> 
					<?php if($depart_id == 12 OR $depart_id == 60 OR $is_admin == 1): ?><li <?php if($controller_name == 'Sms'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Sms/index');?>" >
                        <i class="fa">&#xe63f;</i>
                        <span class="icon_text">短信设置</span>
                        </a>
                    </li><?php endif; ?>	
					<li <?php if($controller_name == 'UserHr' OR $controller_name == 'Offer'): ?>class="active"<?php endif; ?>>
                        <a href="#" class="dropdown-toggle">
                            <i class="fa">&#xe801;</i>
                            <span>招聘管理</span>
                            <i class="fa fa-chevron-circle-right drop-icon"></i>
                        </a>
                        <ul class="submenu">
                            <li <?php if($controller_name == 'UserHr' and $action_name == 'index'): ?>class="active"<?php endif; ?>>
                                <a href="<?php echo U('UserHr/index');?>" >
                                    面试管理
                                </a>
                            </li>
                            <li <?php if($controller_name == 'UserHr' and $action_name == 'offer'): ?>class="active"<?php endif; ?>>
                                <a href="<?php echo U('UserHr/offer');?>" >
                                    offer管理
                                </a>
                            </li>
                        </ul>
                    </li>
					<?php if($depart_id == 60 OR $depart_id == 61 OR $is_admin == 1): ?><li <?php if($controller_name == 'WorkBoard'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('WorkBoard/index');?>">
                            <i class="fa">&#xe63c;</i>
                            <span>工作台</span>
                        </a>
                    </li><?php endif; ?>
					
					<li <?php if($controller_name == 'Seal'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('Seal/index');?>">
                            <i class="fa">&#xe63c;</i>
                            <span>用章申请</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</div>
<div id="content-wrapper">
	<div class="row"><link rel="stylesheet" type="text/css" href="/oa/oa_test/Application/Home/Public/css/sp_assignment.css">
<link rel="stylesheet" type="text/css" href="/oa/oa_test/Public/css/page.css">
<script charset="utf-8" src="/oa/oa_test/Public/datetime/WdatePicker.js"></script>
<script src="https://cdn.bootcss.com/jquery.form/3.51/jquery.form.js"></script>
<style>
.file_click {width: 100%;height: 100%;position: absolute;top: 0;left: 0;z-index: 1;opacity: 0;cursor: pointer;}
.modal-body {padding: 5px 15px;}
.panel {margin-bottom: 5px}
</style>
<!-- S-页面主要部分 -->
<div class="mainContent">
	<ol class="breadcrumb">
	  <li class="active">请假记录</li>
	</ol>
	<!-- S-搜索框部分 -->
	<form action="" method="get">
	<div class="search_main form-inline">
		<div class="form-group">
	        <label for="exampleInputName2">起止时间：</label>
	        <input type="text" class="form-control" placeholder="开始日期" name="start_date" id="start_date" onclick="WdatePicker()" value="<?php echo ($_GET['start_date']); ?>">
	    </div>
		<div class="form-group">
	        <label for="exampleInputName2">至</label>
	        <input type="text" class="form-control" placeholder="结束日期" name="end_date" id="end_date" onclick="WdatePicker()" value="<?php echo ($_GET['end_date']); ?>">
	    </div>
		<div class="form-group">
	        <label for="exampleInputName2">员工姓名：</label>
	        <input type="text" class="form-control" placeholder="员工姓名" name="keyword" value="<?php echo ($_GET['keyword']); ?>">
	    </div>
		<div class="form-group">
	       <select class="form-control" name="v_type">
				<option value="0">请假类型</option>
		    	<?php if(is_array($s_type_arr)): foreach($s_type_arr as $k1=>$vo1): ?><option value="<?php echo ($k1); ?>" <?php if(($k1) == $_GET['v_type']): ?>selected="selected"<?php endif; ?>><?php echo ($vo1); ?></option><?php endforeach; endif; ?>
	    	</select>
			<button class="btn btn-primary" type="submit" id="search">查询</button>
	    </div>
	    <div class="form-group">
	    	<?php if($user_lvl_id > 2): ?><a class="btn btn-primary add_att" href="#">新增</a><?php endif; ?>
			<?php if($is_approver or $is_vo_power): ?><a class="btn btn-primary" href="<?php echo U('Attendance/approve');?>">审批任务</a><?php endif; ?>
			<?php if($is_vo_power): ?><a class="btn btn-primary" href="<?php echo U('Attendance/upload_date');?>">更新考勤</a><?php endif; ?>
			<?php if(check_permission_left('Attendance', 'reporting')){ ?>
			<a class="btn btn-primary" href="<?php echo U('Attendance/reporting');?>">月度报表</a>
			<?php } ?>
			<a class="btn btn-primary" href="<?php echo U('Attendance/index');?>">考勤记录</a>
	    </div>

	</div>
	</form>
	<!-- E-搜索框部分 -->

	<!-- S-表格部分 -->	
	<div class="table">
		<div class="min-width1000">
			<table class="table-striped table-bordered table-hover ">
				<colgroup>
					<col width="5%">
					<?php if($is_vo_power): ?><col width="5%"><?php endif; ?>
					<col width="8%">
					<col width="15%">
					<col width="12%">
					<col width="8%">
					<col width="10%">
					<col width="8%">
					<col width="8%">
				</colgroup>
				<thead>
					<tr id="table_head">
						<th>编号</th>
						<?php if($is_vo_power): ?><th>姓名</th><?php endif; ?>
						<th>请假类型</th>
					    <th>假期时间</th>
					    <th>备注(原因)</th>
					    <th>附件</th>
					    <th>状态</th>
					    <th>申请时间</th>
					    <th>操作</th>
					</tr>
				</thead>
				<tbody>  
					<?php if(is_array($list)): foreach($list as $k=>$vo): ?><tr class="table_bd active">
						<td><?php echo ($k+1); ?></td>
						<?php if($is_vo_power): ?><td><?php echo ($vo["real_name"]); ?></td><?php endif; ?>
						<td><?php echo ($vo["type_name"]); ?></td>
						<td><?php echo ($vo["start_date"]); ?>--<?php echo ($vo["end_date"]); ?></td>
						<td><?php echo ($vo["reason"]); ?></td>
						<td><a href="<?php echo ($vo["file_url"]); ?>" target="_blank"><?php echo ($vo["file_name"]); ?></a></td>
						<td><?php echo ($vo["status_name"]); ?>
						<?php if(!empty($vo["no_agree_reason"])): ?><br/><font style="color:red"><?php echo ($vo["no_agree_reason"]); ?></font><?php endif; ?>
						</td>
						<td><?php echo ($vo["crt_time"]); ?></td>
						<td class="operate" id="<?php echo ($vo["v_id"]); ?>">
						    <?php if(($user_id == $vo['crt_user_id'] && $vo['status'] == 1 ) || $user_lvl_id == 1){ ?>
						    <a href="javascript:;" class="edit" onclick="edit_v(<?php echo ($vo["v_id"]); ?>,<?php echo ($vo["att_id"]); ?>)">修改</a>
							<a href="javascript:;" class="deleat">删除</a>
							<?php } ?>
						</td>
					</tr><?php endforeach; endif; ?> 
				</tbody>
			</table>
		</div>
	</div>
	<div class="Pagination"><?php echo ($page); ?></div>
	<!-- E-表格部分 -->	

	<!-- 请假弹框 -->
	<div class="modal fade" id="leave" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h3 class="modal-title" id="myModalLabel">请假选择</h3>
	      </div>
	      <!-- 起止时间 -->
	      <div class="modal-body">
			<div class="panel panel-success">
		      <div class="panel-heading">
		        <h3 class="panel-title">起止时间</h3>
		      </div>
		      <div class="panel-body">
			      	<div class="row">
			      		<div class="col-md-2 col-sm-2 col-md-2 text-center" style="line-height: 30px;">从</div>
					    <div class="col-md-5 col-sm-5 col-md-5">
					    	<input type="text" class="form-control" placeholder="开始日期" id="start_date_1" onclick="WdatePicker()" value="">
					    </div>
					    <div class="col-md-5 col-sm-5 col-md-5">
					    	<input type="text" class="form-control" placeholder="时间" id="start_time" onclick="WdatePicker({dateFmt:'HH:mm'})" value="">
					    </div>
			      	</div>
			      	<div class="row no_border">
			      		<div class="col-md-2 col-sm-2 col-md-2 text-center" style="line-height: 30px;">到</div>
					    <div class="col-md-5 col-sm-5 col-md-5">
					    	<input type="text" class="form-control" placeholder="开始日期" id="end_date_1" onclick="WdatePicker()" value="" >
					    </div>
					    <div class="col-md-5 col-sm-5 col-md-5">
					    	<input class="form-control" type="text" placeholder="时间" id="end_time" onclick="WdatePicker({dateFmt:'HH:mm'})" value="">
					    </div>
			      	</div>
		      </div>
		    </div>
	      </div>
	      <!-- 起止时间 -->
	      <!-- 请假类型 -->
	      <div class="modal-body">
			<div class="panel panel-success">
		      <div class="panel-heading">
		        <h3 class="panel-title">选择请假的类型</h3>
		      </div>
		      <div class="panel-body">
			      	<div class="row no_border">
					    <div class="col-md-10 col-sm-10 col-md-10">
						    <select class="form-control" id="v_type">
							    <option value="1">调休</option>
							    <option value="2">出差</option>
							    <option value="3">外出</option>
							    <option value="4">忘签到</option>
						    	<option value="5">病假</option>
								<option value="6">事假</option>
								<option value="7">年假</option>
								<!-- <option value="8">郁闷假</option> -->
								<option value="9">亲子假</option>
								<option value="10">其他假</option>
					    	</select>
					    </div>
			      	</div>
			      	<div style="color:red" id="v_type_msg"><!-- 提示：截止上月底，剩余调休时间<?php if($report_data["user_id"] == 62): ?>--<?php else: echo ($report_data["differ_time"]); ?>小时<?php endif; ?> --></div>					   
		      </div>
		    </div>
	      </div>
	      <!-- 请假类型 -->
	      <!-- 理由 -->
	      <div class="modal-body">
			<div class="panel panel-success">
		      <div class="panel-heading">
		        <h3 class="panel-title">原因</h3>
		      </div>
		      <div class="panel-body">
			      	<div class="row no_border">
					    <div class="col-md-10 col-sm-10 col-md-10">
					    	<textarea class="form-control" style="resize:none;" id="reason"></textarea>
					    </div>
			      	</div>					   
		      </div>
		    </div>
	      </div>
	      
	      <div class="modal-body" id="div_upload_file">
			<div class="panel panel-success">
		      <div class="panel-heading">
		        <h3 class="panel-title">上传附件</h3>
		      </div>
		      <div class="panel-body">
			      <form action="" method="post" enctype="multipart/form-data">
					<input class="form-control input-sm" type="text" placeholder="点击上传文件">
					<input type="file" class="file_click" name="file">
				    <input type="hidden" class="file_id" value="" name="file_id"/>
				</form>			   
		      </div>
		    </div>
	      </div>
	      
	      <div class="modal-body">
	      	<select class="form-control" id="cur_approver_id" style="width:40%;margin-left: 30%">
	      		<option value="0">请选择审批人</option>
		    	<?php if(is_array($approve_users)): foreach($approve_users as $k2=>$vo2): ?><option value="<?php echo ($vo2["user_id"]); ?>" ><?php echo ($vo2["real_name"]); ?></option><?php endforeach; endif; ?>
	    	</select>
	      </div>
	      
	      <!-- 理由 -->
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
	        <button type="button" class="btn btn-primary" id="confirm_btn">确定</button>
	        <input type="hidden" value="" id="att_type" />
	        <input type="hidden" value="" id="att_id" />
	        <input type="hidden" value="" id="v_id" />
	      </div>
	    </div>
	  </div>
	</div>
	<!-- 请假弹框 -->

</div>
<!-- E-页面主要部分 -->

<script>
jQuery(document).ready(function($) {
	//删除
	$("body").on("click",".table_bd .operate .deleat",function(){
		//当点击确定时 返回 true 
		if(!window.confirm("确定要删除吗?")){
			return false;
		}
		var v_id = $(this).parent().attr('id');
		
		$.ajax({
			url:"<?php echo U('Attendance/va_delete');?>",
			type:"post",
			dataType:'json', 
			data:{'v_id':v_id},
			success:function(data){
				if(data.status == 1){
					alert("操作成功！", "<?php echo U('Attendance/vacation');?>");
				}else{
					alert(data.msg);
					return false;
				}
			}
		});
	});
	
	$(".add_att").on("click",function(){
		//$("#v_type").html('<option value="1">调休</option><option value="2">病假</option><option value="3">外出</option><option value="5">病假</option><option value="6">事假</option><option value="7">年假</option><option value="8">郁闷假</option><option value="9">亲子假</option><option value="10">其他假</option>');
		
		$("#start_date_1").val('');
		$("#start_time").val('');
		$("#end_date_1").val('');
		$("#end_time").val('');
		$("#reason").val('');
		$("#cur_approver_id").find("option[value='0']").attr("selected",true);
		$("#att_type").val('');
		$("#att_id").val('')
		$(".file_id").val('');
		$("#v_id").val('');
		$('#leave').modal('show');
	});
	
	$('#confirm_btn').on("click",function(){
		var start_date = $("#start_date_1").val();
		var start_time = $("#start_time").val();
		var end_date = $("#end_date_1").val();
		var end_time = $("#end_time").val();
		var v_type = $("#v_type").val();
		var reason = $("#reason").val();
		var cur_approver_id = $("#cur_approver_id").val();
		var att_type = $("#att_type").val();
		var att_id = $("#att_id").val();
		var file_id = $(".file_id").val();
		var v_id = $("#v_id").val();
		if(start_date==""  || end_date=="" ){
			alert("起止时间请填写完整！");
			return false;
		}
		if(reason.length==0 ){
			alert("请填写原因！");
			return false;
		}
		if(cur_approver_id==0) {
			alert("请选择审批人！");
			return false;
		}
		var click= true;
		if(click){
			click = false;
			$("#loading").show();
			$.ajax({
				url:"<?php echo U('Attendance/va_add');?>",
				type:"post",
				dataType:'json', 
				data:{'start_date':start_date,"start_time":start_time, "end_date":end_date, "end_time":end_time,"v_type":v_type,"reason":reason,"cur_approver_id":cur_approver_id,"att_type":att_type,"att_id":att_id,"file_id":file_id,"v_id":v_id},
				success:function(data){
					$("#loading").hide();
					if(data.status == 1){
						alert("操作成功！","<?php echo U('Attendance/vacation');?>");
					}else{
						alert(data.msg);
						click = true;
						return false;
					}
				}
			});
		}
	});
	
	$("#v_type").change(function(){
		if($(this).val() == '7'){
			$("#v_type_msg").html("提示：年假须提前15天请，剩余年假<?php echo ($user["nx_vacation"]); ?>天。请假以半天为最小计算单位，超过5小时按照1天计算。");
			$("#v_type_msg").show();
		}else if($(this).val() == '1'){
			$("#v_type_msg").hide();
			/* $("#v_type_msg").html("提示：截止上月底，剩余调休时间<?php echo ($report_data["differ_time"]); ?>小时");
			$("#v_type_msg").show(); */
		}else if($(this).val() == '5' || $(this).val() == '6'){
			$("#v_type_msg").html("提示：请假以半天为最小计算单位，超过5小时按照1天计算。");
			$("#v_type_msg").show();
		}else{
			$("#v_type_msg").hide();
		}
	})
	
	$("body").on('change', '.file_click', function(event) {
		event.preventDefault();
		var obj = $(this);
		obj.siblings('.input-sm').attr("placeholder","上传中请稍后....");
		var prospectus=obj.val();
		if(prospectus.length>0){
			obj.parent("form").ajaxSubmit({
	          url: "/index.php/Home/Uploadfy/fundfile",
	          type:'post',
	          dataType: 'json',
	          contentType: "application/json; charset=utf-8",
	          success:function(data){
	            if(data.status == 1){
	            	obj.siblings('.input-sm').val(data.file_name);
	            	obj.siblings('.file_id').val(data.file_id);
	                alert("上传成功");
	            }else{
	              alert(data.msg);
	              obj.siblings('.input-sm').attr("placeholder","点击上传文件");
	            }            
	          },error:function(){
	            alert("上传失败");
	            obj.siblings('.input-sm').attr("placeholder","点击上传文件");
	          }
	        })
		}else{
			obj.next(".file_id").val(0);
		}
	});
});

function edit_v(v_id,att_id){
	if(att_id!=0){
		$("#div_upload_file").hide();
		//$("#v_type").html('<option value="1">调休</option><option value="2">出差</option><option value="3">外出</option><option value="4">忘签到</option><option value="5">病假</option><option value="6">事假</option><option value="7">年假</option><option value="8">郁闷假</option><option value="9">亲子假</option>');
	}
	
	$("#v_id").val(v_id);
	$("#att_id").val(att_id);
	if(att_id>0){
		$("#div_upload_file").hide();
	}else{
		$("#div_upload_file").show();
	}
	
	$.ajax({
		url:"<?php echo U('Attendance/getva');?>",
		type:"post",
		dataType:'json', 
		data:{'v_id':v_id},
		success:function(data){
			if(data.status == 1){
				var vacation=data.vacation;
				$("#start_date").val(vacation.start_date_d);
				$("#start_time").val(vacation.start_date_t);
				$("#end_date").val(vacation.end_date_d);
				$("#end_time").val(vacation.end_date_t);
				
				$('#v_type option').each(function(){
					if($(this).val() == vacation.v_type){
						$(this).attr("selected","selected");
					}
				});
				
				if(vacation.v_type == '7'){
					$("#v_type_msg").html("提示：年假须提前15天请，剩余年假<?php echo ($user["nx_vacation"]); ?>天");
					$("#v_type_msg").show();
				}else if(vacation.v_type == '1'){
					/* $("#v_type_msg").html("提示：截止上月底，剩余调休时间<?php echo ($report_data["differ_time"]); ?>小时");
					$("#v_type_msg").show(); */
					$("#v_type_msg").hide();
				}else{
					$("#v_type_msg").hide();
				}
				
				$("#reason").val(vacation.reason);
				$("#att_type").val(vacation.att_type);
				$('#cur_approver_id option').each(function(){
					if($(this).val() == vacation.cur_approver_id){
						$(this).attr("selected","selected");
					}
				});
			}
		}
	});
	
	$('#leave').modal('show');
}
</script></div>
</div>
</div>
</div>
</div>
<div class="md-overlay"></div> 
<script src="/oa/oa_test/Public/common/js/demo-skin-changer.js"></script>

<script src="/oa/oa_test/Public/common/js/jquery.nanoscroller.min.js"></script>
<script src="/oa/oa_test/Public/common/js/demo.js"></script>

<script src="/oa/oa_test/Public/common/js/modernizr.custom.js"></script>

<script src="/oa/oa_test/Public/common/js/classie.js"></script>
<script src="/oa/oa_test/Public/common/js/modalEffects.js"></script>
<script src="/oa/oa_test/Public/common/js/scripts.js"></script>
<script src="/oa/oa_test/Public/common/js/pace.min.js"></script>


</body>
</html>