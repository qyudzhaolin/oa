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
<script type="text/javascript" src="/oa/oa_test/Application/Home/Public/js/sp_assignment.js"></script>
<!-- S-页面主要部分 -->
<div class="mainContent">
	<ol class="breadcrumb">
	  <li class="active">审批任务</li>
	</ol>
	<!-- S-搜索框部分 -->
	<div class="search_main form-inline">
		<!-- 右 -->
		<div style="float:right;">			
			<div class="form-group">
				<label for="exampleInputName2">状态：</label>
		        <select name="" id="way_select" class="form-control">
					<option value="0">全部</option>
				   	<option value="1" <?php if($way == 1): ?>selected="selected"<?php endif; ?>>已审核</option>
				   	<option value="2" <?php if($way == 2): ?>selected="selected"<?php endif; ?>>未审核</option>
			   </select>
		    </div>
		    <div class="form-group">
				<label for="exampleInputName2">状态：</label>
		        <select name="" id="type_select" class="form-control">
					<option value="0">全部</option>
				   	<option value="1" <?php if($_GET['type']== 1): ?>selected="selected"<?php endif; ?>>预算单</option>
				   	<option value="2" <?php if($_GET['type']== 2): ?>selected="selected"<?php endif; ?>>借款单</option>
				   	<option value="3" <?php if($_GET['type']== 3): ?>selected="selected"<?php endif; ?>>项目报销单</option>
				   	<option value="4" <?php if($_GET['type']== 4): ?>selected="selected"<?php endif; ?>>还款单</option>
				   	<option value="5" <?php if($_GET['type']== 5): ?>selected="selected"<?php endif; ?>>预算变更单</option>
				   	<option value="6" <?php if($_GET['type']== 6): ?>selected="selected"<?php endif; ?>>个人报销单</option>
				   	<option value="7" <?php if($_GET['type']== 7): ?>selected="selected"<?php endif; ?>>TB报销单</option>
		  		</select>
		    </div>
		    <div class="form-group">
		    	<div class="input-group">
			      <input type="text" class="form-control" placeholder="单据编号/项目名称" id="keyword" value="">
			      <span class="input-group-btn">
			        <button class="btn btn-success" type="button" id="search">搜索</button>
			      </span>
			    </div>
		    </div>
		</div>
		<!-- 右 -->		
	</div>
	<!-- E-搜索框部分 -->

	<!-- S-表格部分 -->	
	<div class="table">
		<div class="min-width1000">
			<table class="table-striped table-bordered table-hover ">
				<colgroup>
					<col width="8%">
					<col width="10%">
					<col width="8%">
					<col width="15%">
					<col width="15%">
					<col width="10%">
					<col width="10%">
					<col width="5%">
				</colgroup>
				<thead>
					<tr id="table_head">
						<th>单据类型</th>
						<th>单据编号</th>
						<th>申请人</th>
						<th>所属项目</th>
					    <th>项目编号</th>
					    <th>审核状态</th>
					    <th>申请时间</th>
					    <th>操作</th>
					</tr>
				</thead>
				<tbody>  
					<?php if(is_array($list)): foreach($list as $k=>$vo): ?><tr class="table_bd active">
						<td><?php echo ($vo["aprv_type_name"]); ?></td>
						<?php if($is_mobile == 1): ?><td style="word-wrap:break-word;word-break:break-all;"><div style="width:100px"><?php echo ($vo["aprv_no"]); ?></div></td><?php else: ?><td><?php echo ($vo["aprv_no"]); ?></td><?php endif; ?>
						<td><?php echo ($vo["real_name"]); ?></td>
						<td><?php if(empty($vo["proj_name"])): ?>--<?php else: echo ($vo["proj_name"]); endif; ?></td>
						<td><?php if(empty($vo["proj_no"])): ?>--<?php else: echo ($vo["proj_no"]); endif; ?></td>
						<td><?php echo ($vo["result_info"]); ?></td>
						<td><?php echo ($vo["crt_time"]); ?></td>
						<td class="operate">
							<?php if($vo["aprv_type"] == '1'): ?><a href="<?php echo U('Budget/info');?>?id=<?php echo ($vo["obj_id"]); ?>" class="edit">详情</a>
							<?php elseif($vo["aprv_type"] == '2'): ?>
								<a href="<?php echo U('Borrow/info');?>?id=<?php echo ($vo["obj_id"]); ?>" class="edit">详情</a>
							<?php elseif($vo["aprv_type"] == '3'): ?>
								<a href="<?php echo U('Recouped/info');?>?id=<?php echo ($vo["obj_id"]); ?>" class="edit">详情</a>
							<?php elseif($vo["aprv_type"] == '4'): ?>
								<a href="<?php echo U('Refund/info');?>?id=<?php echo ($vo["obj_id"]); ?>" class="edit">详情</a>
							<?php elseif($vo["aprv_type"] == '5'): ?>
								<a href="<?php echo U('Modifybudget/info');?>?id=<?php echo ($vo["obj_id"]); ?>" class="edit">详情</a>
							<?php elseif($vo["aprv_type"] == '6'): ?>
								<a href="<?php echo U('Pfrecouped/info');?>?id=<?php echo ($vo["obj_id"]); ?>" class="edit">详情</a><?php endif; ?>
						</td>
					</tr><?php endforeach; endif; ?> 
				</tbody>
			</table>
		</div>
	</div>
	<div class="Pagination"><?php echo ($page); ?></div>
	<!-- E-表格部分 -->	
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
		var borrow_id = $(this).parent().attr('id');
		
		$.ajax({
			url:"<?php echo U('Borrow/delete');?>",
			type:"post",
			dataType:'json', 
			data:{'borrow_id':borrow_id},
			success:function(data){
				if(data.status == 1){
					alert("操作成功！", "<?php echo U('Borrow/index');?>");
				}else{
					alert(data.msg);
					return false;
				}
			}
		});
	});
	
	$("#search").click(function(){
		window.location.href="<?php echo U('Approve/index');?>?keyword="+$("#keyword").val()+"&way="+$("#way_select").val()+"&type="+$("#type_select").val();
	})
	
	$("#way_select").change(function(){
		window.location.href="<?php echo U('Approve/index');?>?way="+$(this).val()+"&type="+$("#type_select").val();
	})
	
	$("#type_select").change(function(){
		window.location.href="<?php echo U('Approve/index');?>?way="+$("#way_select").val()+"&type="+$(this).val();
	})
});
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