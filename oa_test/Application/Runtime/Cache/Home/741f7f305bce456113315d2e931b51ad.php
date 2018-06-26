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
                    <li <?php if($controller_name == 'News'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo U('News/index');?>">
                            <i class="fa">
                                &#xe601;
                            </i>
                            <span class="icon_text">新闻管理</span>
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
	  <li class="active">加班管理</li>
	</ol>
	<!-- S-搜索框部分 -->

    <form action="" method="get">
	<div class="search_main form-inline">
		<div class="form-group">
	        <label for="exampleInputName2">起止时间：</label>
	        <input type="text" class="form-control" placeholder="开始日期" name="start_date" onclick="WdatePicker()" value="<?php echo ($_GET['start_date']); ?>">
	    </div>
		<div class="form-group">
	        <label for="exampleInputName2">至</label>
	        <input type="text" class="form-control" placeholder="结束日期" name="end_date" onclick="WdatePicker()" value="<?php echo ($_GET['end_date']); ?>">
	    </div>
	    <?php if($is_vo_power): ?><div class="form-group">
	        <label for="exampleInputName2">员工姓名：</label>
	        <input type="text" class="form-control" placeholder="员工姓名" name="keyword" value="<?php echo ($_GET['keyword']); ?>">
	    </div>
	    <div class="form-group">
	        <label for="exampleInputName2">是否通过:</label>
	        <select class="form-control" name="status">
				<option value="0">请选择</option>
		    	<option value="1" <?php if($_GET['status']== 1): ?>selected="selected"<?php endif; ?>>待审批</option>
		    	<option value="2" <?php if($_GET['status']== 2): ?>selected="selected"<?php endif; ?>>已通过</option>
		    	<option value="3" <?php if($_GET['status']== 3): ?>selected="selected"<?php endif; ?>>未通过</option>
	    	</select>
	    </div><?php endif; ?>
		<div class="form-group">
	        <label for="exampleInputName2">加班时长:</label>
	        <select class="form-control" name="v_type">
				<option value="0">加班时长</option>
		    	<?php if(is_array($o_type_arr)): foreach($o_type_arr as $k1=>$vo1): ?><option value="<?php echo ($k1); ?>" <?php if(($k1) == $_GET['o_type']): ?>selected="selected"<?php endif; ?>><?php echo ($vo1); ?></option><?php endforeach; endif; ?>
	    	</select>
	    </div>
	    <div class="form-group">	    	
			<button class="btn btn-primary" type="submit" id="search">查询</button>
	    </div>
	    <div class="form-group">	    	
			<a class="btn btn-primary add_att" href="#">新增</a>
			<?php if($is_approver): ?><a class="btn btn-primary" href="<?php echo U('Overtime/approve');?>">审批任务</a><?php endif; ?>
	    </div>
	</div>
	</form>
	<!-- E-搜索框部分 -->
	<div class="table">
		<div class="min-width1000">
			<!-- S-表格部分 -->	
			<table class="table-striped table-bordered table-hover ">
				<colgroup>
					<col width="5%">
					<?php if($is_vo_power): ?><col width="5%"><?php endif; ?>
					<col width="8%">
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
						<th>加班日期</th>
					    <th>加班时长</th>
					    <th>加班理由</th>
					    <th>状态</th>
					    <th>申请时间</th>
					    <th>操作</th>
					</tr>
				</thead>
				<tbody>  
					<?php if(is_array($list)): foreach($list as $k=>$vo): ?><tr class="table_bd active">
						<td><?php echo ($k+1); ?></td>
						<?php if($is_vo_power): ?><td><?php echo ($vo["real_name"]); ?></td><?php endif; ?>
						<td><?php echo ($vo["o_date"]); ?></td>
						<td><?php echo ($vo["type_name"]); ?></td>
						<td><?php echo ($vo["reason"]); ?></td>
						<td><?php echo ($vo["status_name"]); ?>
						<?php if(!empty($vo["no_agree_reason"])): ?><br/><font style="color:red"><?php echo ($vo["no_agree_reason"]); ?></font><?php endif; ?>
						</td>
						<td><?php echo ($vo["crt_time"]); ?></td>
						<td class="operate" id="<?php echo ($vo["o_id"]); ?>">
						    <?php if($is_vo_power or $vo["status"] == '1'): if($vo["status_name"] != '已过期'): ?><a href="javascript:;" class="edit" onclick="edit_v(<?php echo ($vo["o_id"]); ?>)">修改</a><?php endif; endif; ?>
							
							<?php if($is_vo_power and $vo["status_name"] == '已过期'): ?><a href="javascript:;" class="edit" onclick="pass(<?php echo ($vo["o_id"]); ?>)">通过</a><?php endif; ?>
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
	        <h3 class="modal-title" id="myModalLabel">加班申请</h3>
	      </div>
	      <!-- 起止时间 -->
	      <div class="modal-body">
			<div class="panel panel-success">
		      <div class="panel-heading">
		        <h3 class="panel-title">加班日期</h3>
		      </div>
		      <div class="panel-body" style="padding-left:0px">
			    <div class="col-md-5 col-sm-5 col-md-5">
			    	<input type="text" class="form-control" placeholder="开始日期" id="o_date" onclick="WdatePicker()" value="">
			    </div>
		      </div>
		    </div>
	      </div>
	      <!-- 起止时间 -->
	      
	      
	      
	      <!-- 请假类型 -->
	      <div class="modal-body">
			<div class="panel panel-success">
		      <div class="panel-heading">
		        <h3 class="panel-title">选择加班时长</h3>
		      </div>
		      <div class="panel-body">
			      	<div class="row no_border">
					    <div class="col-md-10 col-sm-10 col-md-10">
						    <select class="form-control" id="o_type">
							    <?php if(is_array($o_type_arr)): foreach($o_type_arr as $k1=>$vo1): ?><option value="<?php echo ($k1); ?>" <?php if(($k1) == $_GET['o_type']): ?>selected="selected"<?php endif; ?>><?php echo ($vo1); ?></option><?php endforeach; endif; ?>
					    	</select>
					    </div>
			      	</div>
		      </div>
		    </div>
	      </div>
	      <!-- 请假类型 -->
	      <!-- 理由 -->
	      <div class="modal-body">
			<div class="panel panel-success">
		      <div class="panel-heading">
		        <h3 class="panel-title">理由</h3>
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
	        <input type="hidden" value="" id="o_id" />
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
		var o_id = $(this).parent().attr('id');
		
		$.ajax({
			url:"<?php echo U('Overtime/ov_delete');?>",
			type:"post",
			dataType:'json', 
			data:{'o_id':o_id},
			success:function(data){
				if(data.status == 1){
					alert("操作成功！", window.location.href);
				}else{
					alert(data.msg);
					return false;
				}
			}
		});
	});
	
	$(".add_att").on("click",function(){
		$("#reason").val('');
		$("#o_type").find("option[value='0']").attr("selected",true);
		$("#cur_approver_id").find("option[value='0']").attr("selected",true);
		$("#o_id").val('');
		$("#o_date").val('');
		$('#leave').modal('show');
	});
	
	$('#confirm_btn').on("click",function(){
		var o_type = $("#o_type").val();
		var reason = $("#reason").val();
		var cur_approver_id = $("#cur_approver_id").val();
		var o_id = $("#o_id").val();
		var o_date = $("#o_date").val();
		if(o_date==""){
			alert("请选择加班日期！");
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
				url:"<?php echo U('Overtime/ov_add');?>",
				type:"post",
				dataType:'json', 
				data:{"o_date":o_date,"o_type":o_type,"reason":reason,"cur_approver_id":cur_approver_id,"o_id":o_id},
				success:function(data){
					$("#loading").hide();
					if(data.status == 1){
						alert("操作成功！","<?php echo U('Overtime/index');?>");
					}else{
						alert(data.msg);
						click = true;
						return false;
					}
				}
			});
		}
	});
});

function edit_v(o_id){
	$("#o_id").val(o_id);
	$.ajax({
		url:"<?php echo U('Overtime/getov');?>",
		type:"post",
		dataType:'json', 
		data:{'o_id':o_id},
		success:function(data){
			if(data.status == 1){
				var overtime=data.overtime;
				$("#o_date").val(overtime.o_date);
				$('#o_type option').each(function(){
					if($(this).val() == overtime.o_type){
						$(this).attr("selected","selected");
					}
				});
				
				$("#reason").val(overtime.reason);
				$('#cur_approver_id option').each(function(){
					if($(this).val() == overtime.cur_approver_id){
						$(this).attr("selected","selected");
					}
				});
			}
		}
	});
	
	$('#leave').modal('show');
}

function pass(o_id){
	$("#loading").show();
	$.ajax({
		url:"<?php echo U('Overtime/pass');?>",
		type:"post",
		dataType:'json', 
		data:{'o_id':o_id},
		success:function(data){
			$("#loading").hide();
			if(data.status == 1){
				alert("操作成功！",window.location.href);
			}else{
				alert(data.msg);
				click = true;
				return false;
			}
		}
	});
	
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