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
	<div class="row"><link rel="stylesheet" type="text/css" href="/oa/oa_test/Application/Home/Public/css/baoxiao.css">
<link rel="stylesheet" type="text/css" href="/oa/oa_test/Application/Home/Public/css/budget_add.css">
<link rel="stylesheet" type="text/css" href="/oa/oa_test/Application/Home/Public/css/budget_list.css">
<script src="https://cdn.bootcss.com/jquery.form/3.51/jquery.form.js"></script>
<script charset="utf-8" src="/oa/oa_test/Public/datetime/WdatePicker.js"></script>
<script type="text/javascript" src="/oa/oa_test/Application/Home/Public/js/budget_list.js"></script>
<!-- S-页面主要部分 -->
		<div class="mainContent">
			<ol class="breadcrumb">
			  <li><a href="index.html">项目预算单</a></li>
			  <li class="active">新增项目预算单</li>
			</ol>

			<!-- S-表格部分 -->	
			<div class="table main">
				<!-- S-新增按钮部分 -->
				<div class="text-left" id="add_btn">						
				   <button id="add_trilateral" class="btn btn-primary" type="button"><?php if(empty($third_party)): ?>新增第三方费用<?php else: ?>删除第三方费用<?php endif; ?></button>&nbsp;				
				   <button id="add_setup" class="btn btn-primary" type="button"><?php if(empty($system_party)): ?>新增智源体系费用<?php else: ?>删除智源体系费用<?php endif; ?></button>						
				</div>
				<!-- E-新增按钮部分 -->
				<div class="min-width1000">
				<table class="table table-bordered">
						<colgroup>
							<col width="20%">
							<col width="15%">
							<col width="15%">
							<col width="20%">
							<col width="15%">							
							<col width="15%">							
						</colgroup>
					<!-- S-上半部分 -->
					<tr>
						<td class="active1">客户</td>
						<td colspan="2">
							<select name="" id="customer_id" class="form-control input-sm">
								<option value="0">请选择客户</option>
								<?php if(is_array($customers)): foreach($customers as $k=>$vo): ?><option value="<?php echo ($vo["cust_id"]); ?>" <?php if(($budget["cust_id"]) == $vo["cust_id"]): ?>selected="selected"<?php endif; ?>><?php echo ($vo["cust_short_name"]); ?></option><?php endforeach; endif; ?>
							</select>
						</td>
						<td class="active1">项目</td>
						<td colspan="2">
							<select name="" id="proj_id" class="form-control input-sm">
								<option value="0">请选择项目</option>
								<?php if(is_array($projs)): foreach($projs as $k=>$vo): ?><option value="<?php echo ($vo["proj_id"]); ?>" <?php if(($budget["proj_id"]) == $vo["proj_id"]): ?>selected="selected"<?php endif; ?>><?php echo ($vo["proj_name"]); ?></option><?php endforeach; endif; ?>
							</select>
						</td>
					</tr>

					<tr>
						<td class="active1">项目编号</td>
						<td colspan="2" id="proj_no"><?php echo ($proj["proj_no"]); ?></td>
						
						<td class="active1">合同金额</td>
						<td colspan="2" id="cntr_val"><?php echo ($proj["cntr_val"]); ?></td>
					</tr>

					<tr>
						<td class="active1" >项目经理</td>
						<td colspan="2" id="proj_mgr"><?php echo ($proj["proj_mgr"]); ?></td>
						<td class="active1" >项目人员</td>
						<td colspan="2" id="proj_users"><?php echo ($proj["proj_users"]); ?></td>
					</tr>

					<tr>
						<td class="theme-bg">科目</td>
						<td class="theme-bg" colspan="3">预算</td>
						<td class="theme-bg" colspan="3">决算</td>
					</tr>

					<tr>
						<td class="active1" colspan="1">合同收入</td>
						<td colspan="2">
							<input class="form-control input-sm" type="text" id="budget_cntr_income" onkeyUp="addBudgetMoney()" onblur="toFixed(this);addBudgetMoney()" value="<?php echo ($budget["budget_cntr_income"]); ?>" maxlength="10">
						</td>
						<td></td>
						<td><input class="form-control input-sm" type="text" id="final_cntr_income"  value="<?php echo ($budget["final_cntr_income"]); ?>" maxlength="10" disabled="disabled"></td>
						<td></td>
					</tr>

					<tr>
						<td class="active1" colspan="1">税点</td>
						<td colspan="2" id="budget_point"><?php echo ($budget["budget_point"]); ?></td>
						<td>6.34%</td>
						<td id="final_point"><?php echo ($budget["final_point"]); ?></td>
						<td>6.34%</td>
					</tr>

					<tr>
						<td class="active1" colspan="1">项目利润</td>
						<td colspan="2" id="budget_proj_profit"><?php echo ($budget["budget_proj_profit"]); ?></td>
						<td><?php echo ($budget["profit_percent"]); ?></td>
						<td id="final_proj_profit"><?php echo ($budget["final_proj_profit"]); ?></td>
						<td></td>
					</tr>
					<tr>
						<td class="active1" colspan="1">项目截止日期</td>
						<td colspan="2" id="budget_proj_profit" >
							<input type="text" class="form-control" placeholder="日期" id="end_time" name="end_time" onclick="WdatePicker()" value="<?php if($budget["end_time"] != 0): echo (date('Y-m-d',$budget["end_time"])); endif; ?>">
						</td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<!-- E-上半部分 -->


				</table>
				
				<!-- S-第三方费用部分 -->
				<table class="table table-bordered trilateral" <?php if(!empty($third_party)): ?>style="display: table;"<?php endif; ?>>
					<colgroup>
						<col width="20%">
						<col width="15%">
						<col width="15%">
						<col width="20%">
						<col width="15%">							
						<col width="15%">							
					</colgroup>
					<tr>
						<td colspan="6" class="danger">第三方费用</td>
					</tr>
					<tr>
						<td class="theme-bg">科目</td>
						<td class="theme-bg">预算</td>
						<td class="theme-bg">说明（符号 <span style="color:red">; ^ &</span>不能使用）</td>
						<td class="theme-bg">说明文件（文件大小不超过10M）</td>
						<td class="theme-bg" colspan="2"></td>
					</tr>
					<?php if(!empty($third_party)): if(is_array($third_party)): foreach($third_party as $k=>$vo): ?><tr class="third_party">
						<td>
							<input type="text" class="form-control input-sm cost_id" readonly="readonly" data-cost="<?php echo ($vo["cost_id"]); ?>" value="<?php echo ($vo["money_name"]); ?>" data-type="1">
						</td>
						<td>
							<input class="form-control input-sm budget_money"  type="text" value="<?php echo ($vo["budget_money"]); ?>" onkeyUp="addDBudegtMoney(this)" onblur="toFixed(this);addDBudegtMoney(this)" maxlength="10">
						</td>
						<td><input class="form-control input-sm budget_info"  type="text" value="<?php echo ($vo["comm"]); ?>"  maxlength="100"></td>
						<td>
							<div class="file_input">
								<form action="" method="post" enctype="multipart/form-data">
									<input class="form-control input-sm" type="text" placeholder="点击上传文件" value="<?php echo ($vo["file_name"]); ?>">
									<input type="file" class="file_click" name="file">
									<input type="hidden" class="file_id" value="<?php echo ($vo["file_id"]); ?>" name="file_id"/>
								</form>
							</div>
						</td>
						<td><input class="form-control input-sm final_money"  type="text" value="<?php echo ($vo["final_money"]); ?>" maxlength="10" disabled="disabled"></td>
						<td><input class="btn btn-primary btn-sm delete_Btn" type="button" value="&nbsp;&nbsp;-删除&nbsp;&nbsp;"></td>
					</tr><?php endforeach; endif; ?>
					<?php else: ?>
					<tr class="third_party">
						<td>
							<input type="text" class="form-control input-sm cost_id" readonly="readonly" data-cost="" data-type="1">
						</td>
						<td>
							<input class="form-control input-sm budget_money"  type="text" value="" onkeyUp="addDBudegtMoney(this)" onblur="toFixed(this);addDBudegtMoney(this)" maxlength="10">
						</td>
						<td><input class="form-control input-sm budget_info"  type="text"  maxlength="100"></td>
						<td>
							<div class="file_input">
								<form action="" method="post" enctype="multipart/form-data">
									<input class="form-control input-sm" type="text" placeholder="点击上传文件">
									<input type="file" class="file_click" name="file">
								    <input type="hidden" class="file_id" value="" name="file_id"/>
								</form>
							</div>
						</td>
						<td><input class="form-control input-sm final_money"  type="text" maxlength="10" disabled="disabled"></td>
						<td><input class="btn btn-primary btn-sm delete_Btn" type="button" value="&nbsp;&nbsp;-删除&nbsp;&nbsp;"></td>
					</tr><?php endif; ?>

					<tr>
						<td>
							<input class="btn btn-primary btn-sm third_party_addBtn" type="button" value="&nbsp;&nbsp;+新增&nbsp;&nbsp;">
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>

					<tr>
						<td></td>
						<td class="d_budget_total">
							预算总计：<?php echo ($budget["d_budget_total"]); ?>
						</td>
						<td></td>
						<td></td>
						<td class="d_final_total">决算总计：<?php echo ($budget["d_final_total"]); ?></td>
						<td></td>
					</tr>
				</table>
				<!-- E-第三方费用部分 -->

				<!-- S-智源体系部分 -->
				<table class="table table-bordered setup" <?php if(!empty($system_party)): ?>style="display: table;"<?php endif; ?>>
					<colgroup>
						<col width="20%">
						<col width="15%">
						<col width="15%">
						<col width="20%">
						<col width="15%">							
						<col width="15%">							
					</colgroup>
					<tr>
						<td colspan="6" class="danger">智源体系</td>
					</tr>
					<tr>
						<td class="theme-bg">科目</td>
						<td class="theme-bg">预算</td>
						<td class="theme-bg">说明（符号 <span style="color:red">; ^ &</span>不能使用）</td>
						<td class="theme-bg">比例%</td>
						<td class="theme-bg" colspan="2">决算</td>
					</tr>
					<?php if(!empty($system_party)): if(is_array($system_party)): foreach($system_party as $k=>$vo): ?><tr class="system_party">
						<td>
							<input type="text" class="form-control input-sm cost_id" readonly="readonly" data-cost="<?php echo ($vo["cost_id"]); ?>" value="<?php echo ($vo["money_name"]); ?>" data-type="2">
						</td>
						<td>
							<input class="form-control input-sm budget_money"  type="text" value="<?php echo ($vo["budget_money"]); ?>" onkeyUp="addDBudegtMoney(this)" onblur="toFixed(this);addDBudegtMoney(this)" maxlength="10">
						</td>
						<td><input class="form-control input-sm budget_info"  type="text" value="<?php echo ($vo["comm"]); ?>"  maxlength="100"></td>
						<td></td>
						<td><input class="form-control input-sm final_money"  type="text" value="<?php echo ($vo["final_money"]); ?>" maxlength="10" disabled="disabled"></td>
						<td><input class="btn btn-primary btn-sm delete_Btn" type="button" value="&nbsp;&nbsp;-删除&nbsp;&nbsp;"></td>
						
					</tr><?php endforeach; endif; ?>
					<?php else: ?>
					<tr class="system_party">
						<td>
							<input type="text" class="form-control input-sm cost_id" readonly="readonly" data-cost="" data-type="2">
						</td>
						<td>
							<input class="form-control input-sm budget_money" type="text" value="" onkeyUp="addDBudegtMoney(this)" onblur="toFixed(this);addDBudegtMoney(this)" maxlength="10">
						</td>
						<td><input class="form-control input-sm budget_info"  type="text" maxlength="100"></td>
						<td></td>
						<td><input class="form-control input-sm final_money"  type="text" maxlength="10" disabled="disabled"></td>
						<td><input class="btn btn-primary btn-sm delete_Btn" type="button" value="&nbsp;&nbsp;-删除&nbsp;&nbsp;"></td>
					</tr><?php endif; ?>
					<tr>
						<td>
							<input class="btn btn-primary btn-sm" id="system_addBtn" type="button" value="&nbsp;&nbsp;+新增&nbsp;&nbsp;">
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td class="z_budget_total">预算总计：<?php echo ($budget["z_budget_total"]); ?></td>
						<td></td>
						<td></td>
						<td class="z_final_total">决算总计：<?php echo ($budget["z_final_total"]); ?></td>
						<td></td>
					</tr>
				</table>
				<!-- E-智源体系部分 -->

				<!-- S-申请完操作 -->
				<?php if(!empty($budget)): ?><table class="table table-striped table-bordered">
					<tr>
						<td class="active">申请人</td>
						<?php if(is_array($approve_arr["approve_title_arr"])): foreach($approve_arr["approve_title_arr"] as $k=>$vo): ?><td class="active"><?php echo ($vo); ?></td><?php endforeach; endif; ?>
					</tr>
					<tr>
						<td><?php echo ($app_real_name); ?></td>
						<?php if(is_array($approve_arr["approve_title_arr"])): foreach($approve_arr["approve_title_arr"] as $k=>$vo): ?><td>
							<?php if(empty($approve_arr["approve_user_arr"]["{$k}"])): ?>--
							<?php else: ?>
							<?php if(($approve_arr["approve_user_arr"]["{$k}"]["result"]) == "1"): ?>同意（<?php echo ($approve_arr["approve_user_arr"]["$k"]["user_real_name"]); ?>）
								<p><?php echo (date('Y-m-d H:i:s',$approve_arr["approve_user_arr"]["$k"]["time"])); ?></p>
							<?php else: ?>
								<?php if(($approve_arr["approve_user_arr"]["{$k}"]["result"]) == "-1"): ?>不同意（<?php echo ($approve_arr["approve_user_arr"]["$k"]["user_real_name"]); ?>）<?php if(!empty($approve_arr["approve_user_arr"]["{$k}"]["opinion"])): ?><br/>理由：<?php echo ($approve_arr["approve_user_arr"]["$k"]["opinion"]); endif; ?>
								<p><?php echo (date('Y-m-d H:i:s',$approve_arr["approve_user_arr"]["$k"]["time"])); ?></p>
								<?php else: ?>
									<?php if(($user_id) == $approve_arr["approve_user_arr"]["{$k}"]["user_id"]): ?><button type="button" class="btn btn-success btn-sm btn-operate" value="1" val="<?php echo ($k+1); ?>">同意</button>
										<button type="button" class="btn btn-danger btn-sm btn-operate" value="-1" val="<?php echo ($k+1); ?>">不同意</button>
									<?php else: ?>
									等待审批（<?php echo ($approve_arr["approve_user_arr"]["$k"]["user_real_name"]); ?>）<?php endif; endif; endif; endif; ?>
						</td><?php endforeach; endif; ?>
					</tr>
				</table><?php endif; ?>

				<!-- E-申请完操作 -->

				<!-- S-提交部分 -->
				<?php if($is_show_app == 1): ?><div class="edit text-center">
					<div class="col-md-4 col-md-offset-4">		
						<div class="col-md-6">							
							<select name="" id="cur_approver_id" class="form-control">
								<option value="0">请选择审批人</option>
								<?php if(is_array($approve_users)): foreach($approve_users as $key=>$vo): ?><option value="<?php echo ($vo["user_id"]); ?>" <?php if(($budget["cur_approver_id"]) == $vo["user_id"]): ?>selected="selected"<?php endif; ?>><?php echo ($vo["real_name"]); ?></option><?php endforeach; endif; ?>
							</select>
						</div>			
						<button type="button" class="btn btn-primary col-md-2" id="Submit">提交</button>
					</div>
				</div><?php endif; ?>
					
				<?php if($is_restart == 1): ?><div class="edit text-center">
						<button type="button" class="btn btn-primary" id="Restart">重新发启</button>
					</div><?php endif; ?>
				<!-- E-提交部分 -->
			</div>
			</div>
			<!-- E-表格部分 -->	
			
			<input type="hidden" value="<?php echo ($budget["bud_id"]); ?>" id="bud_id" />
			<input type="hidden" value="0" id="d_budget_total" />
			<input type="hidden" value="0" id="z_budget_total" />
		</div>
		<!-- E-页面主要部分 -->
		
		<!-- 款项弹出框 -->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h3 class="modal-title" id="myModalLabel">选择款项</h3>
	      </div>
	      <div class="modal-body">
		<!-- S-tab切换 -->
	      <div>
			  <ul class="nav nav-tabs" role="tablist">
			    <li role="presentation" class="active" id="li-cb"><a href="#c_b" aria-controls="home" role="tab" data-toggle="tab">传播</a></li>
			    <li role="presentation" id="li-hd"><a href="#h_d" aria-controls="profile" role="tab" data-toggle="tab">活动</a></li>
			  </ul>

			  <!-- Tab panes -->
			  <div class="tab-content">
			    <div role="tabpanel" class="tab-pane active" id="c_b">
			    	<div class="selecr_parent">					    		
				    	<select name="" id="" class="form-control input-sm first">
				    		<option value="0">请选择一级</option>
				    	</select>
			    	</div>
			    	<div class="selecr_parent">					    		
				    	<select name="" id="" class="form-control input-sm second">
				    		<option value="0">请选择二级</option>
				    	</select>
			    	</div>
			    	<div class="selecr_parent">					    		
				    	<select name="" id="" class="form-control input-sm third">
				    		<option value="0">请选择三级</option>
				    	</select>
			    	</div>
			    </div>
			    <div role="tabpanel" class="tab-pane" id="h_d">
			    	<div class="selecr_parent">					    		
				    	<select name="" id="" class="form-control input-sm first">
				    		<option value="0">请选择一级</option>
				    	</select>
			    	</div>
			    	<div class="selecr_parent">					    		
				    	<select name="" id="" class="form-control input-sm second">
				    		<option value="0">请选择二级</option>
				    	</select>
			    	</div>
			    	<div class="selecr_parent">					    		
				    	<select name="" id="" class="form-control input-sm third">
				    		<option value="0">请选择三级</option>
				    	</select>
			    	</div>
			    </div>
			  </div>
		
			</div>
		<!-- E-tab切换 -->

	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
	        <button type="button" class="btn btn-primary" id="confirm_btn">确定</button>
	      </div>
	    </div>
	  </div>
	</div>
	<!-- 款项弹出框 -->
	
	<!-- S-输入理由的弹出框 -->
	<!-- S-输入理由的弹出框 -->
 <div class="modal fade bs-example-modal-sm account noagree" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
     <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">请输入理由</h4>
      </div>
      <div class="modal-body">
          <div class="form-group">
		    <textarea name="" id="no_agree_opinion" cols="30" rows="10" class="form-control" placeholder="请输入理由" maxlength="100"></textarea>
		  </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary" id="no-agree">确定</button>
      </div>
    </div>
  </div>
</div>
<input type="hidden" value="" id="no_agree_num" />
<!-- E-输入理由的弹出框 -->
	<!-- E-输入理由的弹出框 -->
		
<script>
jQuery(document).ready(function($) {
	var click=true;
	$("#Submit").click(function(event) {
		calculate();
		
		var a=/^[0-9]*(\.[0-9]{1,2})?$/;
		
		var bud_id = $("#bud_id").val();
		var customer_id = $("#customer_id").val();
		var proj_id = $("#proj_id").val();
		var budget_cntr_income = $("#budget_cntr_income").val();
		var budget_point = $("#budget_point").text();
		var budget_proj_profit = $("#budget_proj_profit").text();
		var cur_approver_id = $("#cur_approver_id").val();
		var d_budget_total = $("#d_budget_total").val();
		var z_budget_total = $("#z_budget_total").val();
		var end_time = $("#end_time").val();
		if(customer_id==0){
			alert("请选择客户！");
			return false;
		}
		if(proj_id==0){
			alert("请选择项目！");
			return false;
		}
		if(budget_cntr_income.length == 0){
			alert("请填写预算合同收入！");
			return false;
		}
		if(!a.test(budget_cntr_income)){
			alert("预算合同收入格式不正确！");
			return false;
		}
		if(end_time==''){
			alert("项目截止日期不能为空！");
			return false;
		}

		<?php if(!$budegt): ?>if (cur_approver_id==0) {
			alert("请选择审批人！");
			return false;
		}<?php endif; ?>
		
		var d_str = "";
		for(var i=0;i<$(".third_party").length;i++){
			var cost_id = $(".third_party").eq(i).find(".cost_id").attr("data-cost");
			var budget_money = $(".third_party").eq(i).find(".budget_money").val();
			var budget_info = $(".third_party").eq(i).find(".budget_info").val();
			var file_id = $(".third_party").eq(i).find(".file_id").val();
			if(cost_id.length>0 || budget_money.length>0 || budget_info.length>0){
				if(cost_id.length == 0){
					alert("请选择第三方科目！");
					return false;
				}
				if(budget_money.length == 0){
					alert("请填写第三方预算金额！");
					return false;
				}
				if(!a.test(budget_money)){
					alert("第三方预算金额格式不正确！");
					return false;
				}
				if(cost_id.length>0){
					if(d_str.indexOf(cost_id+"^")==0 || d_str.indexOf(";"+cost_id+"^")>0){
						alert("第三方科目不能重复！");
						return false;
					}
				}
				if(budget_info.length == 0){
					alert("请填写说明！");
					return false;
				}
				if(file_id.length <= 1){
					alert("请上传说明文件！");
					return false;
				}
				d_str += cost_id+"^"+budget_money+"^"+budget_info+"^"+file_id+";"
			}
		}
		
		var z_str = "";
		for(var i=0;i<$(".system_party").length;i++){
			var cost_id = $(".system_party").eq(i).find(".cost_id").attr("data-cost");
			var budget_money = $(".system_party").eq(i).find(".budget_money").val();
			var budget_info = $(".system_party").eq(i).find(".budget_info").val();
			if(cost_id.length>0 || budget_money.length>0 || budget_info.length>0){
				if(cost_id.length == 0){
					alert("请选择智源体系科目！");
					return false;
				}
				if(budget_money.length == 0){
					alert("请填写智源体系预算金额！");
					return false;
				}
				if(!a.test(budget_money)){
					alert("智源体系预算金额格式不正确！");
					return false;
				}
				if(cost_id.length>0){
					if(z_str.indexOf(cost_id+"^")==0 || z_str.indexOf(";"+cost_id+"^")>0){
						alert("智源体系科目不能重复！");
						return false;
					}
				}
				z_str += cost_id+"^"+budget_money+"^"+budget_info+";"
			}
		}
		
		if(click){
			click = false;
			$("#loading").show();
			$.ajax({
				url:"<?php echo U('Budget/add');?>",
				type:"post",
				dataType:'json', 
				data:{'bud_id':bud_id,"customer_id":customer_id, "proj_id":proj_id, "budget_cntr_income":budget_cntr_income,
					"budget_point":budget_point,"budget_proj_profit":budget_proj_profit,'cur_approver_id':cur_approver_id,
					'd_budget_total':d_budget_total,"z_budget_total":z_budget_total,'z_str':z_str,'d_str':d_str,'end_time':end_time},
				success:function(data){
					$("#loading").hide();
					if(data.status == 1){
						alert("操作成功！","<?php echo U('Budget/index');?>");
					}else{
						alert(data.msg);
						click = true;
						return false;
					}
				}
			});
		}
	});
	
	
	$("#customer_id").change(function(){
		var cust_id = $(this).val();
		var proj_id = '<?php echo ($budget["proj_id"]); ?>';
		if(cust_id > 0){
			$.ajax({
				url:"<?php echo U('Budget/getprojs');?>",
				type:"post",
				dataType:'json', 
				data:{'cust_id':cust_id,'proj_id':proj_id},
				success:function(data){
					var str = '<option value="0">请选择项目</option>';
					if(data.status == 1){
						var projs = eval(data.projs);
						for(var i=0;i<projs.length;i++){
							str += '<option value="'+projs[i]['proj_id']+'">'+projs[i]['proj_name']+'</option>';
						}
						$("#proj_id").html(str);
					}else{
						$("#proj_id").html(str);
						alert(data.msg);
						return false;
					}
				}
			});
		}
	});
	
	$("#proj_id").change(function(){
		var proj_id = $(this).val();
		if(proj_id > 0){
			$.ajax({
				url:"<?php echo U('Budget/getprojinfo');?>",
				type:"post",
				dataType:'json', 
				data:{'proj_id':proj_id},
				success:function(data){
					if(data.status == 1){
						var proj = eval(data.proj);
						$("#proj_no").html(proj.proj_no);
						$("#cntr_val").html(proj.cntr_val);
						$("#proj_mgr").html(proj.proj_mgr);
						$("#proj_users").html(proj.proj_users);
					}else{
						alert(data.msg);
						return false;
					}
				}
			});
		}
		$("#proj_no").html('');
		$("#cntr_val").html('');
		$("#proj_mgr").html('');
		$("#proj_users").html('');
	})
	
	var click2 = true;
	$("#Restart").click(function(){
		if(click2){
			click2 = false;
			if(confirm('确定要重新发启？')){
				var bud_id = $("#bud_id").val();
				$.ajax({
					url:"<?php echo U('Budget/restart');?>",
					type:"post",
					dataType:'json', 
					data:{'bud_id':bud_id},
					success:function(data){
						if(data.status == 1){
							alert("操作成功！","<?php echo U('Budget/index');?>");
						}else{
							alert(data.msg);
							click2 = true;
							return false;
						}
					}
				});
			}else{
				click2 = true;
			}
		}
	})
	
	var click3=true; 
	$(".btn-operate").click(function(){
		var val = $(this).attr("value");
		var num = $(this).attr("val");
		var bud_id = $("#bud_id").val();
		if(val==-1){
			$("#no_agree_num").val(num);
			$(".account").modal('show');
		}else{
			if(click3){
				click3 = false;
				if(confirm('确定要执行同意操作吗？')){
					$.ajax({
						url:"<?php echo U('Budget/operate');?>",
						type:"post",
						dataType:'json', 
						data:{'bud_id':bud_id,'num':num,'result':val},
						success:function(data){
							if(data.status == 1){
								alert("操作成功！","<?php echo U('Budget/index');?>");
							}else{
								alert(data.msg);
								click3 = true;
								return false;
							}
						}
					});
				}else{
					click3 = true;	
				}
			}
		}
	});
	
	var click4 = true;
	$("#no-agree").click(function(){
		var num = $("#no_agree_num").val();
		var bud_id = $("#bud_id").val();
		if(click4){
			click4 = false;
			if(confirm('确定要执行不同意操作吗？')){
				$.ajax({
					url:"<?php echo U('Budget/operate');?>",
					type:"post",
					dataType:'json', 
					data:{'bud_id':bud_id,'num':num,'result':-1,'option':$("#no_agree_opinion").val()},
					success:function(data){
						if(data.status == 1){
							alert("操作成功！","<?php echo U('Budget/index');?>");
						}else{
							alert(data.msg);
							click4 = true;
							return false;
						}
					}
				});
			}else{
				click4 = true;	
			}
		}
	});
});
</script>
</div>
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