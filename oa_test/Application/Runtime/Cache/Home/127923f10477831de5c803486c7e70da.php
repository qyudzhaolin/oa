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
	<div class="row"><link rel="stylesheet" type="text/css" href="/oa/oa_test/Application/Home/Public/css/budget_add.css">
<script src="https://cdn.bootcss.com/jquery.form/3.51/jquery.form.js"></script>
<script type="text/javascript" src="/oa/oa_test/Application/Home/Public/js/budget_add.js"></script>
<style>
.file_click {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
    opacity: 0;
    cursor: pointer;
}
</style>
<!-- S-页面主要部分 -->
	<div class="mainContent">
		<ol class="breadcrumb">
		  <li><a href="<?php echo U('Project/index');?>">项目管理</a></li>
		  <li class="active">新增项目</li>
		</ol>

		<!-- S-填写部分 -->
		<table class="table table-condensed">
			<colgroup>
				<col width="20%">
				<col width="80%">
			</colgroup>
			<tr>
				<td class="left">客户&nbsp;&nbsp;</td>
				<td class="right">
					<div class="col-xs-6 col-sm-4">
				    	<select name="" id="cust_id" class="form-control">
  	  					<option value="0">请选择客户</option>
  	  					<?php if(is_array($customers)): foreach($customers as $k=>$vo): ?><option value="<?php echo ($vo["cust_id"]); ?>" <?php if(($project["cust_id"]) == $vo["cust_id"]): ?>selected="selected"<?php endif; ?>><?php echo ($vo["cust_short_name"]); ?></option><?php endforeach; endif; ?>
						</select>
				    </div>
				</td>
			</tr>
			<tr>
				<td class="left">项目编号&nbsp;&nbsp;</td>
				<td class="right">
					<div class="col-xs-6 col-sm-4">
				    	<input type="text" class="form-control" id="proj_no"  maxlength="30" id="proj_no" placeholder="请填写项目编号" value="<?php echo ($project["proj_no"]); ?>" <?php if($_GET['proj_id']!= ''): ?>disabled="disabled"<?php endif; ?>>
				    </div>
				</td>
			</tr>
			<tr>
				<td class="left">项目名称&nbsp;&nbsp;</td>
				<td class="right">
					<div class="col-xs-6 col-sm-4">
				    	<input type="text" class="form-control" id="proj_name"  maxlength="100" id="proj_name" placeholder="请填写项目名称" value="<?php echo ($project["proj_name"]); ?>">
				    </div>
				</td>
			</tr>
			<tr>
				<td class="left">合同金额&nbsp;&nbsp;</td>
				<td class="right">
					<div class="col-xs-6 col-sm-4">
				    	<input type="text" class="form-control" id="cntr_val"  maxlength="20" id="proj_name" placeholder="请填写合同金额" value="<?php echo ($project["cntr_val"]); ?>">
				    </div>
				</td>
			</tr>
			<tr>
				<td class="left">项目经理&nbsp;&nbsp;</td>
				<td class="right">
					<div class="col-xs-6 col-sm-4">
				    	<select name="" id="proj_mgr" class="form-control">
  	  					<option value="0">请选择项目经理</option>
  	  					<?php if(is_array($mgr_users)): foreach($mgr_users as $k=>$vo): ?><option value="<?php echo ($vo["user_id"]); ?>" <?php if(($project["proj_mgr"]) == $vo["user_id"]): ?>selected="selected"<?php endif; ?>><?php echo ($vo["depart_name"]); ?>--<?php echo ($vo["real_name"]); ?></option><?php endforeach; endif; ?>
 					</select>
				    </div>
				</td>
			</tr>
			
			<tr>
				<td class="left">项目组&nbsp;&nbsp;</td>
				<td class="right">
					<div class="checkbox col-xs-11 group">
					   <?php if(is_array($departs)): foreach($departs as $k=>$vo): ?><label style="margin-right: 10px;">
				         <input type="checkbox" value="<?php echo ($k); ?>" name="<?php echo ($vo["depart_name"]); ?>" <?php if($vo["is_check"] == '1'): ?>checked="checked"<?php endif; ?>><?php echo ($vo["depart_name"]); ?>
				       </label><?php endforeach; endif; ?>
				    </div>
				</td>
			</tr>
			<tr id="group_people">
				<td class="left">项目人员&nbsp;&nbsp;</td>
				<td class="right">
					<div class="checkbox col-xs-11">
						<div class="group_people">
							<?php if(is_array($departs)): foreach($departs as $k=>$vo): if($vo["is_check"] == '1'): ?><div class="gr_num">									
								<strong id="<?php echo ($k); ?>"><?php echo ($vo["depart_name"]); ?>：</strong>
								<?php if(is_array($vo["users"])): foreach($vo["users"] as $ks=>$vos): ?><label style="margin-right: 10px;">
						         	<input type="checkbox" value="<?php echo ($ks); ?>" <?php if($vos["is_check"] == '1'): ?>checked="checked"<?php endif; ?>><?php echo ($vos["real_name"]); ?>
						       	</label><?php endforeach; endif; ?>
							</div><?php endif; endforeach; endif; ?>							
						</div>

				    </div>
				</td>
			</tr>
			<tr>
				<td class="left">客户合同&nbsp;&nbsp;</td>
				<td class="right">
					<div class="col-xs-6 col-sm-4">
						<form id="formfile" action="" method="post" enctype="multipart/form-data">
							<input class="form-control input-sm" type="text" placeholder="点击上传pdf文件" value="<?php echo ($project["file_name"]); ?>"  >
							<input type="file" class="file_click" name="file">
						    <input type="hidden" class="file_id" value="<?php echo ($project["file_id"]); ?>" id="file_id"/>
						</form>
				    </div>
				</td>
			</tr>
			<tr>
				<td class="left">其他项目文件&nbsp;&nbsp;</td>
				<td class="right">
					<div class="col-xs-6 col-sm-4">
						<form id="formfile1" action="" method="post" enctype="multipart/form-data">
							<input class="form-control input-sm" type="text" placeholder="点击上传文件" value="<?php echo ($project["other_file_name"]); ?>"  >
							<input type="file" class="file_click" name="file">
						    <input type="hidden" class="file_id" value="<?php echo ($project["other_file_id"]); ?>" id="other_file_id"/>
						</form>
				    </div>
				</td>
			</tr>
			<tr>
				<td class="left"></td>
				<td class="right">
					<div class="Submit_btn">
				      <button type="button" class="btn btn-success" id="Submit">保存</button>&nbsp;&nbsp;&nbsp;
				      <button type="reset" class="btn btn-default" id="reset" onclick="history.go(-1)">返回</button>					    	
				    </div>
				</td>
			</tr>

		</table>
		<!-- E-填写部分 -->		
		<input type="hidden" value="<?php echo ($project["proj_id"]); ?>" id="proj_id" />
		<input type="hidden" value="<?php echo U('Project/getusers');?>" id="departs" />
</div>
<!-- E-页面主要部分 -->
<script>
jQuery(document).ready(function($) {
	$("body").on('change', '.file_click', function(event) {
		event.preventDefault();
		var obj = $(this);
		obj.siblings('.input-sm').attr("placeholder","上传中请稍后....");
		var prospectus=obj.val();
		if(prospectus.length>0){
			obj.parent("form").ajaxSubmit({
	          url: "<?php echo U('Uploadfy/fundfile');?>",
	          type:'post',
	          dataType: 'json',
	          contentType: "application/json; charset=utf-8",
	          success:function(data){
	            if(data.status == 1){
	            	obj.siblings('.input-sm').val(data.file_name);
	            	obj.siblings('.file_id').val(data.file_id);
	                alert("上传文件成功");
	            }else{
	              alert(data.msg);
	              obj.siblings('.input-sm').attr("placeholder","点击上传文件");
	            }            
	          },error:function(){
	            alert("上传文件失败");
	            obj.siblings('.input-sm').attr("placeholder","点击上传文件");
	          }
	        })
		}else{
			obj.next(".file_id").val(0);
		}
	});
	
	
	
	
	
	$("#Submit").click(function(event) {
		//部门名称
		var cust_id = $("#cust_id").val();
		var proj_no = $("#proj_no").val();
		var proj_name = $("#proj_name").val();  
		var proj_id = $("#proj_id").val();
		var proj_mgr = $("#proj_mgr").val();
		var cntr_val = $("#cntr_val").val();
		var file_id = $("#file_id").val();
		var other_file_id = $("#other_file_id").val();
		if(cust_id==0){
			alert("请选择客户！");
			return false;
		}
		if(!proj_id && proj_no.length==0){
			alert("请填写项目编号！");
			return false;
		}
		if (proj_name=="") {
			alert("请填写项目名称！");
			return false;
		}
		if (cntr_val=="") {
			alert("请填写合同金额！");
			return false;
		}
		var a=/^[0-9]*(\.[0-9]{1,2})?$/;
		if(!a.test(cntr_val)){
			alert("合同金额格式不正确");
			return false;
		}
		
		if (proj_mgr==0){
			alert("请选择项目经理！");
			return false;
		}
		
		var user_ids = '';
		$("#group_people input:checked").each(function(){
			user_ids += $(this).val()+",";
		});
		
		if(user_ids.length == 0){
			alert("请选择项目人员！");
			return
		}
		
		if(file_id.length<=1 && other_file_id.length<=1){
			alert("客户合同或者其他项目文件至少上传一个！");
			return
		}
		
		$.ajax({
			url:"<?php echo U('Project/add');?>",
			type:"post",
			dataType:'json', 
			data:{'cust_id':cust_id,'proj_no':proj_no,"proj_name":proj_name, "proj_id":proj_id, "user_ids":user_ids,"proj_mgr":proj_mgr,"cntr_val":cntr_val,"file_id":file_id,"other_file_id":other_file_id},
			success:function(data){
				if(data.status == 1){
					alert("操作成功！","<?php echo U('Project/index');?>");
				}else{
					alert(data.msg);
					return false;
				}
			}
		});
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