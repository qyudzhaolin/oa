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
	<div class="row"><link rel="stylesheet" type="text/css" href="/oa/oa_test/Application/Home/Public/css/borrow_money.css">
<script type="text/javascript" src="/oa/oa_test/Application/Home/Public/js/refund.js"></script>
<!-- S-页面主要部分 -->
<div class="mainContent">
	<ol class="breadcrumb">
	  <li class="active">还款单</li>
	</ol>

	<!-- S-表格部分 -->	
	<div class="table">
		<div class="min-width1000">
			<!-- S-部门项目等 -->
			<table class="table-striped table-bordered">
				<tr>
					<td class="col-md-2 active">部门</td>
					<td class="col-md-4">
						<input class="form-control input-sm" type="text" readonly="readonly" value="<?php echo ($depart["depart_name"]); ?>">
					</td>
					<td class="col-md-2 active">姓名</td>
					<td class="col-md-4">
						<input class="form-control input-sm" type="text" readonly="readonly" value="<?php echo ($app_real_name); ?>">
					</td>
				</tr>
				<tr>
					<td class="col-md-2 active">项目</td>
					<td class="col-md-4">
						<select name="" id="proj_id" class="form-control input-sm">
							<option value="0">请选择项目</option>
							<?php if(is_array($projs)): foreach($projs as $k=>$vo): ?><option value="<?php echo ($vo["proj_id"]); ?>" <?php if(($refund["proj_id"]) == $vo["proj_id"]): ?>selected="selected"<?php endif; ?>><?php echo ($vo["proj_name"]); ?></option><?php endforeach; endif; ?>
						</select>
					</td>
					<td class="col-md-2 active">项目编号</td>
					<td class="col-md-4">
						<input class="form-control input-sm" type="text" readonly="readonly" value="<?php echo ($refund["proj_no"]); ?>" id="proj_no">
					</td>
				</tr>
				<tr>
					<td class="col-md-2 active">借款单</td>
					<td class="col-md-4">
						<select name="" id="borrow_id" class="form-control input-sm">
							<option value="0">请选择借款单</option>
							<?php if(is_array($borrows)): foreach($borrows as $k=>$vo): ?><option value="<?php echo ($vo["borrow_id"]); ?>" <?php if(($refund["borrow_id"]) == $vo["borrow_id"]): ?>selected="selected"<?php endif; ?> data-price="<?php echo ($vo["tot_amt"]); ?>"><?php echo ($vo["borrow_no"]); ?></option><?php endforeach; endif; ?>
						</select>
					</td>
				</tr>
				<tr id="no_border">
					<td colspan="4">.</td>
				</tr>
			</table>
			<!-- E-部门项目等 -->
			<!-- S-填写款项金额等 -->
			<table class="table-striped table-bordered">
				<tr id="th">
					<td class="col-md-1 active">款项</td>
					<td class="col-md-1 active">可用余额</td>
					<td class="col-md-1 active">还款金额</td>
					<td class="col-md-9 active">备注说明</td>
				</tr>
				<?php if(!$money_list): ?><tr class="fund">
					<td class="col-md-1">
						<select name="" class="form-control input-sm cost_id">
							<option value="0">请选择款项</option>
						</select>
					</td>
					<td class="col-md-1">
						<input class="form-control input-sm usable_money" type="text" disabled="disabled">
					</td>
					<td class="col-md-1">
						<input class="form-control input-sm money" type="text"  maxlength="10" onkeyUp="addMoney(this)" onblur="toFixed(this)">
					</td>
					<td class="col-md-9">
						<input class="form-control input-sm comm" type="text" maxlength="100">
					</td>
				</tr>
				<?php else: ?>
				<?php if(is_array($money_list)): foreach($money_list as $k=>$vo): ?><tr class="fund">
					<td class="col-md-1">
						<select name="" class="form-control input-sm cost_id">
							<option value="0">请选择款项</option>
							<?php if(is_array($costs)): foreach($costs as $k1=>$vo1): ?><option value="<?php echo ($vo1["id"]); ?>" <?php if(($vo["cost_id"]) == $vo1["id"]): ?>selected="selected"<?php endif; ?> data-price="<?php echo ($vo1["usable_money"]); ?>"><?php echo ($vo1["costname"]); ?></option><?php endforeach; endif; ?>
						</select>
					</td>
					<td class="col-md-1">
						<input class="form-control input-sm usable_money" type="text" disabled="disabled" value="<?php echo ($vo["usable_money"]); ?>">
					</td>
					<td class="col-md-1">
						<input class="form-control input-sm money" type="text" maxlength="10" onkeyUp="addMoney(this)" onblur="toFixed(this);addMoney(this)" value="<?php echo ($vo["money"]); ?>">
					</td>
					<td class="col-md-9">
						<input class="form-control input-sm comm" type="text" maxlength="100" value="<?php echo ($vo["comm"]); ?>">
					</td>
				</tr><?php endforeach; endif; endif; ?>
				<tr>
					<td colspan="4">
						<button type="button" class="btn btn-primary btn-sm add_btn">&nbsp;&nbsp;+添加&nbsp;&nbsp;</button>
					</td>
				</tr>
				<tr>
					<td colspan="1" class="active">金额总计（小写）</td>
					<td colspan="3">
						<div class="col-xs-5">
							<input class="form-control input-sm" type="text" id="tot_amt" readonly="readonly" value="<?php echo ($refund["tot_amt"]); ?>">
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="1" class="active">金额总计（大写）</td>
					<td colspan="3">
						<div class="col-xs-5">
							<input class="form-control input-sm" type="text" id="tot_amt_d" readonly="readonly" value="<?php echo ($refund["tot_amt_d"]); ?>">
						</div>
					</td>
				</tr>
			</table>
			<!-- E-填写款项金额等 -->
			
			<!-- S-申请完操作 -->
			<?php if(!empty($refund)): ?><table class="table-striped table-bordered">
				<tr>
					<td class="active">申请人</td>
					<td class="active">财务</td>
				</tr>
				<tr>
					<td><?php echo ($app_real_name); ?></td>
					<td>
						<?php if($schedule["aprv_result1"] == '1'): ?>已处理（<?php echo ($schedule["user_real_name1"]); ?>）
						<p><?php echo (date('Y-m-d H:i:s',$schedule["aprv_time1"])); ?></p>
						<?php elseif($schedule["aprv_result1"] == '-1'): ?>
						不同意（<?php echo ($schedule["user_real_name1"]); ?>）<?php if(!empty($schedule["aprv_opinion1"])): ?><br/>理由：<?php echo ($schedule["aprv_opinion1"]); endif; ?>
						<p><?php echo (date('Y-m-d H:i:s',$schedule["aprv_time1"])); ?></p>
						<?php else: ?>
						<?php if(($user_id) == $schedule["aprv_user_id1"]): ?><button type="button" class="btn btn-success btn-sm btn-operate" value="1" val="1">确认处理</button>
						<?php else: ?>
							<?php if($schedule["aprv_user_id1"] != '0'): ?>待处理（<?php echo ($schedule["user_real_name1"]); ?>）
							<?php else: ?>
							--<?php endif; endif; endif; ?>
					</td>
				</tr>
			</table><?php endif; ?>
			<!-- E-申请完操作 -->
			
			<!-- S-提交部分 -->
			<?php if($is_show_app == 1): ?><div class="edit text-center">
				<select name="" id="cur_approver_id">
					<option value="0">请选择审批人</option>
					<?php if(is_array($approve_users)): foreach($approve_users as $key=>$vo): ?><option value="<?php echo ($vo["user_id"]); ?>" <?php if(($refund["cur_approver_id"]) == $vo["user_id"]): ?>selected="selected"<?php endif; ?>><?php echo ($vo["real_name"]); ?></option><?php endforeach; endif; ?>
				</select>&nbsp;
				<button type="button" class="btn btn-primary" id="Submit">提交</button>
			</div><?php endif; ?>
			<?php if($is_restart == 1): ?><div class="edit text-center">
				<button type="button" class="btn btn-primary" id="Restart">重新发启</button>
			</div><?php endif; ?>
			<!-- E-提交部分 -->
		</div>
		
	</div>
	<!-- E-表格部分 -->	
	<input type="hidden" value="<?php echo ($refund["ref_id"]); ?>" id="ref_id" />
	<input type="hidden" value="<?php echo ($budget["bud_id"]); ?>" id="bud_id" />
</div>
<!-- E-页面主要部分 -->
<script>
jQuery(document).ready(function($) {
	<?php if($refund): ?>$(".fund").each(function(){
		var usable_money = $(this).find(".cost_id").find("option:selected").attr("data-price");
		$(this).find(".usable_money").val(usable_money);
	})<?php endif; ?>
	
	
	var click=true;
	$("#Submit").click(function(event) {
		var a=/^[0-9]*(\.[0-9]{1,2})?$/;
		
		var ref_id = $("#ref_id").val();
		var borrow_id = $("#borrow_id").val();
		var proj_id = $("#proj_id").val();
		var cur_approver_id = $("#cur_approver_id").val();
		if(proj_id==0){
			alert("请选择项目！");
			return false;
		}
		
		var fund_str = "";
		for(var i=0;i<$(".fund").length;i++){
			var cost_id = $(".fund").eq(i).find(".cost_id").val();
			var money = $(".fund").eq(i).find(".money").val();
			var comm = $(".fund").eq(i).find(".comm").val();
			var usable_money = $(".fund").eq(i).find(".usable_money").val();
			var cost_name = $(".fund").eq(i).find(".cost_id").find("option:selected").text();
			if(cost_id.length>0 || money.length>0 || comm.length>0){
				if(cost_id == 0){
					alert("请选择款项！");
					return false;
				}
				if(money.length == 0){
					alert("请填写金额！");
					return false;
				}
				if(!a.test(money)){
					alert("金额格式不正确！");
					return false;
				}
				if(parseFloat(money)<=0){
					alert("款项："+cost_name+"的金额必须大于零！");
					return false;
				}
				if(cost_name=="F&B"){
					cost_name = "FB";
				}
				/* if(usable_money<money){
					alert("款项："+cost_name+"可用余额不足！");
					return false;
				} */
			}
			fund_str += cost_id+"^"+money+"^"+comm+"^"+cost_name+";"
		}
		
		<?php if(!$refund): ?>if (cur_approver_id==0) {
			alert("请选择审批人！");
			return false;
		}<?php endif; ?>
		
		var tot_amt = $("#tot_amt").val();
		if(tot_amt.length == 0){
			alert("请至少填写一条款项信息！");
			return false;
		}
		
		var tot_amt_d = $("#tot_amt_d").val();
		var bud_id = $("#bud_id").val();
		if(click){
			click = false;
			$("#loading").show();
			$.ajax({
				url:"<?php echo U('Refund/add');?>",
				type:"post",
				dataType:'json', 
				data:{'ref_id':ref_id,'borrow_id':borrow_id,"proj_id":proj_id,"cur_approver_id":cur_approver_id,"fund_str":fund_str,"tot_amt":tot_amt,"tot_amt_d":tot_amt_d},
				success:function(data){
					$("#loading").hide();
					if(data.status == 1){
						alert("操作成功！","<?php echo U('Refund/index');?>");
					}else{
						alert(data.msg);
						click = true;
						return false;
					}
				}
			});
		}
	});
	
	var click2 = true;
	$("#Restart").click(function(){
		if(click2){
			click2 = false;
			if(confirm('确定要重新发启？')){
				var ref_id = $("#ref_id").val();
				$.ajax({
					url:"<?php echo U('Refund/restart');?>",
					type:"post",
					dataType:'json', 
					data:{'ref_id':ref_id},
					success:function(data){
						if(data.status == 1){
							alert("操作成功！","<?php echo U('Refund/index');?>");
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
	
	
	$("#proj_id").change(function(){
		var proj_id = $(this).val();
		if(proj_id > 0){
			$.ajax({
				url:"<?php echo U('Refund/getprojno');?>",
				type:"post",
				dataType:'json', 
				data:{'proj_id':proj_id},
				success:function(data){
					if(data.status == 1){
						$("#proj_no").val(data.proj_no);
						var borrows = eval(data.borrows);
						if(borrows.length > 0){
							var str = '<option value="0">请选择借款单</option>';
							for(var i=0;i<borrows.length;i++){
								str += '<option value="'+borrows[i]['borrow_id']+'">'+borrows[i]['borrow_no']+'</option>';
							}
							$("#borrow_id").html(str);
						}
					}else{
						alert(data.msg);
						return false;
					}
				}
			});
		}
	})
	
	
	$("#borrow_id").on("change",function(){
		$("#borrow_tot_amt").text($(this).find("option:selected").attr("data-price"));
		if($(this).val() > 0){
			$.ajax({
				url:"<?php echo U('Refund/getborrowexps');?>",
				type:"post",
				dataType:'json', 
				data:{'borrow_id':$(this).val()},
				success:function(data){
					if(data.status == 1){
						var costs = eval(data.costs);
						if(costs.length > 0){
							var str = '<option value="0">请选择款项</option>';
							for(var i=0;i<costs.length;i++){
								str += '<option value="'+costs[i]['id']+'" data-price="'+costs[i]['usable_money']+'">'+costs[i]['costname']+'</option>';
							}
							$(".cost_id").html(str);
						}
					}
				}
			});
		}
	})
	
	$("body").on("change",".fund .cost_id",function(event) {
		$(this).parents(".fund").find(".usable_money").val($(this).find("option:selected").attr("data-price"));
	})
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