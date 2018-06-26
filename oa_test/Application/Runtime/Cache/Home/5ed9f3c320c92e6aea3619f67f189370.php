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
	<div class="row"><link rel="stylesheet" type="text/css" href="/oa/oa_test/Application/Home/Public/css/supplier.css">
<link rel="stylesheet" type="text/css" href="/oa/oa_test/Public/css/page.css">
<link rel="stylesheet" type="text/css" href="/oa/oa_test/Application/Home/Public/css/sp_assignment.css">
<script charset="utf-8" src="/oa/oa_test/Public/datetime/WdatePicker.js"></script>
<style>
.error_index{border:1px solid red}
</style>	<!--E-头部 -->
		<!-- S-页面主要部分 -->
		<div class="mainContent">
			<ol class="breadcrumb">
			  <li class="active">名片申请记录</li>
			</ol>
			<!-- S-搜索框部分 -->
			<form action="" method="get">
			<div class="search_main  form-inline">
				<div class="form-group">
			        <label for="exampleInputName2">起止时间：</label>
			        <input type="text" class="form-control" placeholder="开始日期" name="start_date" id="start_date" onclick="WdatePicker()" value="<?php echo ($_GET['start_date']); ?>">
			    </div>
			    <div class="form-group">
			        <label for="exampleInputName2">至</label>
			        <input type="text" class="form-control" placeholder="结束日期" name="end_date" id="end_date" onclick="WdatePicker()" value="<?php echo ($_GET['end_date']); ?>">
			    </div>
			    <div class="form-group">
			        <label for="exampleInputName2">姓名：</label>
			        <input type="text" class="form-control" placeholder="姓名" name="name" value="<?php echo ($search["name"]); ?>">
			    </div>
			    <div class="form-group">
			        <label for="exampleInputName2">状态：</label>
			        <select class="form-control" name="get_status">
						<option value="">状态</option>
						<option value="0" <?php if(($search["get_status"]) == "0"): ?>selected<?php endif; ?>>已提交</option>
						<option value="1" <?php if(($search["get_status"]) == "1"): ?>selected<?php endif; ?>>已接受</option>
						<option value="2" <?php if(($search["get_status"]) == "2"): ?>selected<?php endif; ?>>已完成</option>
					</select>
			    </div>
			    <div class="form-group">
			    	<button class="btn btn-primary" type="submit" id="search">查询</button>
			    	<a id="get_goods_btn" class="btn btn-primary" href="<?php echo U('CardRecord/add');?>" >名片申请</a>
			    </div>
			</div>
			</form>
			<!-- E-搜索框部分 -->
			<!-- S-表格部分 -->
			<div class="table">
				<div class="min-width1000">
					<form class="form-inline">
					<table class="table-striped table-bordered table-hover ">
						<thead>
							<tr id="table_head">
								<th>序号</th>
								<th>姓名</th>
								<th>职位</th>
								<th>电话</th>
								<th>手机号</th>
								<th>邮箱</th>
								<th>公司地址</th>
								<th>申请时间</th>
							    <th>状态</th>
							    <th>操作者</th>
							    <th>备注</th>
								<th>操作</th>
							</tr>
						</thead>
						<tbody>  
							<?php if(is_array($list)): $key = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?><tr class="table_bd active">
								<td>
									<!--div class="checkbox-nice">
									<label style="width:100%;height:100%" for="m-checkbox-<?php echo ($vo["id"]); ?>"><input id="m-checkbox-<?php echo ($vo["id"]); ?>" name="checkmenu[]" value="<?php echo ($vo["id"]); ?>" type="checkbox">
									<?php echo ($key); ?></label>
									</div-->
									<?php echo ($key); ?>
								</td>
								<td>
									<?php echo ($vo["name"]); if(!empty($vo["en_name"])): ?>&nbsp;- <?php echo ($vo["en_name"]); endif; ?>
								</td>
								<td>
									<?php echo ($vo["title"]); if(!empty($vo["en_title"])): ?>&nbsp;- <?php echo ($vo["en_title"]); endif; ?>
								</td>
								<td>
									<?php echo ($vo["tel"]); ?>							
								</td>
								<td class="">
									<?php echo ($vo["phone"]); ?>
								</td>
								<td>
									<?php echo ($vo["email"]); ?>			
								</td>
								<td>
									<?php echo ($vo["address"]); ?>			
								</td>
								<td>
									<?php echo (date('Y-m-d H:i',$vo["crt_time"])); ?>
								</td>
								<td>
									<?php if(($vo["get_status"]) == "1"): ?>已接受<?php endif; ?>
									<?php if(($vo["get_status"]) == "2"): ?>已完成<?php endif; ?>
									<?php if(($vo["get_status"]) == "0"): ?>已提交<?php endif; ?>
								</td>
								<td>
									<?php echo ($vo["deal_name"]); ?>
								</td>
								<td>
									<?php echo ($vo["remark"]); ?>
								</td>
								
								<td class="operate">
									<?php if(($vo["get_status"]) == "2"): else: ?>
									<a class="edit" href="<?php echo U('CardRecord/add',array('id'=>$vo['id']));?>" value="<?php echo ($vo["id"]); ?>">修改</a>
									<?php if($is_admin OR $user_id == 172 OR $user_id == 173 OR $depart_id == 42): ?><a href="javascript:void(0)" value="<?php echo ($vo["id"]); ?>" url="<?php echo U('CardRecord/del');?>" class="deleat">删除</a>
									<a href="javascript:;" class="changebtn edit" data-id="<?php echo ($vo["id"]); ?>" data-toggle="modal" data-target="#exampleModal">更新状态</a><?php endif; endif; ?>
									
								</td>
								
							</tr><?php endforeach; endif; else: echo "" ;endif; ?> 
							<!--tr>
								<td colspan="8" class="text-center">
								数量：<?php echo ((isset($sum["page_count_num"]) && ($sum["page_count_num"] !== ""))?($sum["page_count_num"]):0); ?> &nbsp;&nbsp;&nbsp;&nbsp;
								总价：<?php echo ((isset($sum["page_count_price"]) && ($sum["page_count_price"] !== ""))?($sum["page_count_price"]):0); ?> 元
								</td>
							</tr-->
						</tbody>
					</table>
					
					</form>
				</div>
			</div>
			
			<div class="Pagination"><?php echo ($page); ?></div>
			
			<!-- E-表格部分 -->
			<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
			  <div class="modal-dialog" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="exampleModalLabel">新增分类</h4>
				  </div>
				  <div class="modal-body">
					<form>
					  <div class="form-group">
						<label for="cat_name" class="control-label">状态:</label>
							<label>
							  <input type="radio" name="get_status" value="1"> 已接受
							</label>
							<label>
							  <input type="radio" name="get_status" value="2"> 已完成
							</label>
					  </div>
					  <input type="hidden" value="0" id="recordid" name="recordid" >
					</form>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal" id="closebtn">关闭</button>
					<button type="button" class="btn btn-primary" id="modalsave">保存</button>
				  </div>
				</div>
			  </div>
			</div>
		</div>
		<!-- E-页面主要部分 -->
		<script>
		$(".changebtn").click(function() {
			$('#recordid').val($(this).data('id'));
			
		});
		$("#modalsave").click(function() {
			var id = $('#recordid').val();
			var get_status = $('input[name="get_status"]:checked').val();
			$.ajax({
				url: "<?php echo U('CardRecord/changestatus');?>",
				data:{id:id,get_status:get_status},
				type:'post',
				cache:false,
				dataType:'json',
				success:function(data) {
				  if(data.code==1)
				  {
					 alert("更新成功！");
					//刷新当前页面
					window.location.reload();
				  }else{
					alert(data.msg);
				  }
				}
			});
		});
		//删除
		$(".deleat").on("click",function(){
			var _this = $(this);
			//当点击确定时 返回 true 
			if(window.confirm("确定要删除吗?")){
				//$(this).parents(".table_bd").remove();
				$.ajax({
					url:$(this).attr('url'),
					data:{id:_this.attr('value')},
					dataType:'json',
					success:function(res){
						alert(res.msg);
						if(res.code==1){
							location.reload();
						}
					}
				})
			}	
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