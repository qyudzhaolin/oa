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
<script type="text/javascript" src="/oa/oa_test/Application/Home/Public/js/recouped.js"></script>
<!-- S-页面主要部分 -->
<div class="mainContent">
	<ol class="breadcrumb">
	  <li class="active">用章申请</li>
	</ol>

	<!-- S-表格部分 -->	
	<div class="table">
		<div class="min-width1000">
		<!-- S-部门项目等 -->
			<table class="table-striped table-bordered">
				<tr>
					<td class="col-md-2 active">部门</td>
					<td class="col-md-4"><?php echo ($depart["depart_name"]); ?></td>
					<td class="col-md-2 active">姓名</td>
					<td class="col-md-4"><?php echo ($apply_user["real_name"]); ?></td>
				</tr>
				<tr>
					<td class="active">
						用章类型
					</td>
					<td>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="s_type" id="cash" value="1" <?php if($seal["s_type"] == '1' or $seal["s_type"] == ''): ?>checked="checked"<?php endif; ?>> 盖章申请
						</label>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="s_type" id="transfer" value="2" <?php if($seal["s_type"] == 2): ?>checked="checked"<?php endif; ?>> 借章申请
						</label>
					</td>
					<td class="active">所属公司</td>
					<td>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="c_type" value="1" <?php if($seal["c_type"] == '1' or $seal["c_type"] == ''): ?>checked="checked"<?php endif; ?>> 榕智
						</label>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="c_type" value="2" <?php if($seal["c_type"] == 2): ?>checked="checked"<?php endif; ?>> 炜旻
						</label>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="c_type" value="3" <?php if($seal["c_type"] == 3): ?>checked="checked"<?php endif; ?>> 络昕
						</label>
					</td>
				</tr>
				<tr>
					<td class="active">印章类型</td>
					<td>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="se_type" value="1" <?php if($seal["se_type"] == '1' or $seal["se_type"] == ''): ?>checked="checked"<?php endif; ?>> 公章
						</label>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="se_type" value="2" <?php if($seal["se_type"] == 2): ?>checked="checked"<?php endif; ?>> 财务章
						</label>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="se_type" value="3" <?php if($seal["se_type"] == 3): ?>checked="checked"<?php endif; ?>> 法人章
						</label>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="se_type" value="4" <?php if($seal["se_type"] == '4'): ?>checked="checked"<?php endif; ?>> 发票章
						</label>
						<label class="radio-inline">
						  <input disabled="disabled" type="radio" name="se_type" value="5" <?php if($seal["se_type"] == '5'): ?>checked="checked"<?php endif; ?>> 合同章
						</label>
					</td>
					<td class="active" id="change_td"><?php if($seal["s_type"] == '1' or $seal["s_type"] == ''): ?>印章次数<?php else: ?>日期<?php endif; ?></td>
					<td id="change_td_1">
						<?php if($seal["s_type"] == '1' or $seal["s_type"] == ''): echo ($seal["use_num"]); ?>
						<?php else: ?>
						<label class="radio-inline"><p style="float:left;margin-top: 6px;margin-right: 10px;">借出</p><input style="float:left;width: 70%;" type="text" class="form-control" placeholder="日期" id="out_date" name="out_date"  value="<?php echo ($seal["out_date"]); ?>" readonly="readonly"></label>
						<label class="radio-inline"><p style="float:left;margin-top: 6px;margin-right: 10px;">归还</p><input style="float:left;width: 70%;" type="text" class="form-control" placeholder="日期" id="back_date" name="back_date" value="<?php echo ($seal["back_date"]); ?>" readonly="readonly"></label><?php endif; ?>
					</td>
				</tr>
				<tr>
					<td class="active">使用原因</td>
					<td colspan="4">
						<?php echo ($seal["reason"]); ?>
					</td>
				</tr>
			</table>
			<!-- E-部门项目等 -->
		
			<!-- S-申请完操作 -->
			<table class="table-striped table-bordered">
				<tr>
					<td class="active">申请人</td>
					<?php if(is_array($approve_arr["approve_title_arr"])): foreach($approve_arr["approve_title_arr"] as $k=>$vo): ?><td class="active"><?php echo ($vo); ?></td><?php endforeach; endif; ?>
				</tr>
				<tr>
					<td><?php echo ($apply_user["real_name"]); ?></td>
					<?php if(is_array($approve_arr["approve_title_arr"])): foreach($approve_arr["approve_title_arr"] as $k=>$vo): ?><td>
	<?php if(empty($approve_arr["approve_user_arr"]["{$k}"])): ?>--
	<?php else: ?>
	<?php if(($approve_arr["approve_user_arr"]["{$k}"]["result"]) == "1"): if(($approve_arr["type"]) == "seal"): ?>同意
			<?php else: ?>
			<?php if(($approve_arr["approve_user_arr"]["{$k}"]["user_lvl_id"]) == "4"): ?>已打款<?php else: ?>同意<?php endif; endif; ?>
		
		
		（<?php echo ($approve_arr["approve_user_arr"]["$k"]["user_real_name"]); ?>）<?php if(!empty($approve_arr["approve_user_arr"]["{$k}"]["opinion"])): ?><br/>备注：<?php echo ($approve_arr["approve_user_arr"]["$k"]["opinion"]); endif; ?>
		<p><?php echo (date('Y-m-d H:i:s',$approve_arr["approve_user_arr"]["$k"]["time"])); ?></p>
	<?php else: ?>
		<?php if(($approve_arr["approve_user_arr"]["{$k}"]["result"]) == "-1"): ?>不同意（<?php echo ($approve_arr["approve_user_arr"]["$k"]["user_real_name"]); ?>）<?php if(!empty($approve_arr["approve_user_arr"]["{$k}"]["opinion"])): ?><br/>理由：<?php echo ($approve_arr["approve_user_arr"]["$k"]["opinion"]); endif; ?>
		<p><?php echo (date('Y-m-d H:i:s',$approve_arr["approve_user_arr"]["$k"]["time"])); ?></p>
		<?php else: ?>
			<?php if(($user_id) == $approve_arr["approve_user_arr"]["{$k}"]["user_id"]): if(($approve_arr["approve_user_arr"]["{$k}"]["user_lvl_id"]) == "4"): if(($approve_arr["type"]) == "seal"): ?><button type="button" class="btn btn-success btn-sm" value="1" val="<?php echo ($k+1); ?>">同意</button>
					<?php else: ?>
						<button type="button" class="btn btn-success btn-sm" value="1" val="<?php echo ($k+1); ?>">确认打款</button><?php endif; ?>
				<?php else: ?>
				<button type="button" class="btn btn-success btn-sm" value="1" val="<?php echo ($k+1); ?>">同意</button>
				<button type="button" class="btn btn-danger btn-sm" value="-1" val="<?php echo ($k+1); ?>">不同意</button><?php endif; ?>
			<?php else: ?>
			<?php if(($approve_arr["approve_user_arr"]["{$k}"]["user_lvl_id"]) == "4"): if(($approve_arr["type"]) == "seal"): ?>等待审批
				<?php else: ?>
					待打款<?php endif; ?>
			<?php else: ?>等待审批<?php endif; ?>
			（<?php echo ($approve_arr["approve_user_arr"]["$k"]["user_real_name"]); ?>）<?php endif; endif; endif; endif; ?>
	<?php $approve_user_row = $approve_arr['approve_user_arr'][$k]; if($approve_user_row['user_lvl_id']==2 && $approve_user_row['user_id']==$user_id && $approve_user_row['result']!=0 && (!$approve_arr['approve_user_arr'][$k+1] || $approve_arr['approve_user_arr'][$k+1]['result']==0)){ echo '<button type="button" class="btn btn-warning" value="1" val="3" style="padding: 5px 10px;">返回</button>'; } ?>
</td><?php endforeach; endif; ?>
				</tr>
			</table>
			<!-- E-申请完操作 -->
		
			<!-- S-提交部分 -->
			<?php if($is_restart == 1): ?><div class="edit text-center">
				<button type="button" class="btn btn-primary" id="Restart">重新发启</button>
			</div><?php endif; ?>
			<!-- E-提交部分 -->
		
		
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
		
			<!-- S-选择审批人的弹出框 -->
			<!-- S-输入理由的弹出框 -->
 <div class="modal fade bs-example-modal-sm approve_users" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
     <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">同意</h4>
      </div>
      <?php if(check_permission_left('Agree_approve', 'index')){ ?>
      <div class="modal-body">
          <div class="form-group">
		    <textarea name="" id="agree_opinion" cols="30" rows="10" class="form-control" placeholder="请输入备注" maxlength="100"></textarea>
		  </div>
      </div>
      <?php }else{ ?>
      <input type="hidden" value="" id="agree_opinion" />
      <?php } ?>
      <div class="modal-body">
          <div class="form-group">
		    <select name="" id="cur_approver_id" class="form-control">
				<option value="0">请选择审批人</option>
				<?php if(is_array($approve_users)): foreach($approve_users as $key=>$vo): ?><option value="<?php echo ($vo["user_id"]); ?>" <?php if(($recouped["cur_approver_id"]) == $vo["user_id"]): ?>selected="selected"<?php endif; ?>><?php echo ($vo["real_name"]); ?></option><?php endforeach; endif; ?>
			</select>&nbsp;
		  </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary" id="btn-success">确定</button>
      </div>
    </div>
  </div>
</div>
<input type="hidden" value="" id="agree_num" />
<!-- E-输入理由的弹出框 -->
			<!-- E-选择审批人的弹出框 -->
		
		</div>
	</div>
	
	
	<!-- E-表格部分 -->	
	<input type="hidden" value="<?php echo ($seal["se_id"]); ?>" id="se_id" />
</div>
<!-- E-页面主要部分 -->
<script>
jQuery(document).ready(function($) {
	var click=true;
	$("#btn-success").click(function(){
		if(click){
			var se_id = $("#se_id").val();
			var num = $("#agree_num").val();
			var cur_approver_id = $("#cur_approver_id").val();
			if(confirm('确定要执行同意操作吗？')){
				click = false;
				$(".approve_users").modal('hide');
				$("#loading").show();
				$.ajax({
					url:"<?php echo U('Seal/operate');?>",
					type:"post",
					dataType:'json', 
					data:{'se_id':se_id,'num':num,'result':1,'cur_approver_id':cur_approver_id,'option':$("#agree_opinion").val()},
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
			}else{
				click = true;	
			}
		}
	})
	
	
	var click1=true; 
	$(".btn-sm").click(function(){
		var val = $(this).attr("value");
		var t_id = $("#t_id").val();
		var num = $(this).attr("val");
		if(val==-1){
			$("#no_agree_num").val(num);
			$(".noagree").modal('show');
		}else{
			$("#agree_num").val(num);
			<?php if($is_show_app == 1): ?>$(".approve_users").modal('show');
			<?php else: ?>
			$("#btn-success").click();<?php endif; ?>
		}
	});
	
	
	var click3 = true;
	$("#no-agree").click(function(){
		var num = $("#no_agree_num").val();
		var se_id = $("#se_id").val();
		if(click3){
			if(confirm('确定要执行不同意操作吗？')){
				click3 = false;
				$(".account").modal('hide');
				$("#loading").show();
				$.ajax({
					url:"<?php echo U('Seal/operate');?>",
					type:"post",
					dataType:'json', 
					data:{'se_id':se_id,'num':num,'result':-1,'option':$("#no_agree_opinion").val()},
					success:function(data){
						$("#loading").hide();
						if(data.status == 1){
							alert("操作成功！",window.location.href);
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
	});
	
	var click2 = true;
	$("#Restart").click(function(){
		if(click2){
			click2 = false;
			if(confirm('确定要重新发启？')){
				var se_id = $("#se_id").val();
				$.ajax({
					url:"<?php echo U('Seal/restart');?>",
					type:"post",
					dataType:'json', 
					data:{'se_id':se_id},
					success:function(data){
						if(data.status == 1){
							alert("操作成功！","<?php echo U('Seal/index');?>");
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
	});
	
	var click4 = true;
	$(".btn-warning").click(function(){
		if(click4){
			click4 = false;
			if(confirm('确定要返回操作吗？')){
				var se_id = $("#se_id").val();
				$.ajax({
					url:"<?php echo U('Seal/back');?>",
					type:"post",
					dataType:'json', 
					data:{'se_id':se_id},
					success:function(data){
						if(data.status == 1){
							alert("操作成功！",window.location.href);
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