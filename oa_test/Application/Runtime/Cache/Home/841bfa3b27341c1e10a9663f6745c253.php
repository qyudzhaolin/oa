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
<script type="text/javascript" src="/oa/oa_test/Application/Home/Public/js/supplier.js"></script>
<style>
.error_index{border:1px solid red}
</style>	<!--E-头部 -->

		<!-- S-页面主要部分 -->
		<div class="mainContent">
			<ol class="breadcrumb">
			  <li class="active">办公用品管理</li>
			</ol>
			<!-- S-搜索框部分 -->
			<div class="search_main">
				<?php if($is_admin OR $user_id == 172 OR $user_id == 173 OR $depart_id == 42): ?><div id="select" class="mg-right-15">
				<a class="btn btn-primary" href="<?php echo U('OfficeSupplies/add');?>">&nbsp;&nbsp;新增办公用品&nbsp;&nbsp;</a>
				</div><?php endif; ?>
				
				<div id="select" class="mg-right-15">
				<a class="btn btn-primary" href="<?php echo U('officeSuppliesMain/index');?>" >申领记录</a>
				</div>
				<!--form name="searchform" action="<?php echo U('OfficeSupplies/index');?>" method="post">
				<div class="search_input">
					<div class="col-lg-9">
					    <div class="input-group">
					      <input type="text" placeholder="名称" value="<?php echo ($search["name"]); ?>" name="name" class="form-control">
					      <span class="input-group-btn">
					        <button class="btn btn-default"  type="submit">搜索</button>
					      </span>
					    </div>
				    </div>
				</div>
				</form-->
			</div>
			
			<!-- E-搜索框部分 -->
			<!-- S-表格部分 -->
			<div class="table">
			<!--div class="pd10 tabgroup">
				<a class="btn btn-default mg-right-5" href="/oa/oa_test/index.php/Home/OfficeSupplies/index">全部</a>
			<?php if(is_array($category)): $i = 0; $__LIST__ = $category;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a class="btn btn-default mg-right-5" data-cid="<?php echo ($vo["id"]); ?>" href="/oa/oa_test/index.php/Home/OfficeSupplies/index?cid=<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
			</div-->
			<div id="tablegroup">
				<div class="table">				
					<div class="min-width1000">
						<table class="table-striped table-bordered table-hover <?php if(($vkey) > "1"): ?>hide<?php endif; ?>" id="table_list<?php echo ($vol["cid"]); ?>" >
							<thead>
								<tr id="table_head">
									<th width="5%">序号</th>
									<th width="15%">名称</th>
									<th width="10%">分类</th>
									<th width="10%">库存</th>
									<th width="10%">单价</th>
								    <th width="15%">更新时间</th>
								    <th width="10%">备注</th>
								    <th width="5%">申领数量</th>
									<?php if($is_admin OR $user_id == 172 OR $user_id == 173 OR $depart_id == 42): ?><th width="15%">操作</th><?php endif; ?>
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
										<?php echo ($vo["name"]); ?>
									</td>
									<td>
										<?php echo ($vo["cat_name"]); ?>							
									</td>
									<td class=""><?php echo ((isset($vo["inventory"]) && ($vo["inventory"] !== ""))?($vo["inventory"]):0); ?> 
									<!--&nbsp; &nbsp;<input size="5" class="form-control input-small inventory" type="text" name="inventory" id="inventory">-->
									
									</td>
									<td>
										<?php echo ((isset($vo["price"]) && ($vo["price"] !== ""))?($vo["price"]):0); ?>			
									</td>
									<td>
										<?php if(!empty($vo["mod_time"])): echo (date('Y-m-d H:i',$vo["mod_time"])); else: echo (date('Y-m-d H:i',$vo["crt_time"])); endif; ?>
									</td>
									<td>
										<?php echo ($vo["remark"]); ?>			
									</td>
									<td class="">
									<input size="5" class="form-control input-small get_num" type="text" data-inventory="<?php echo ($vo["inventory"]); ?>" data-recordid="<?php echo ($vo["id"]); ?>" value="0" name="get_num">
									</td>
									<?php if($is_admin OR $user_id == 172 OR $user_id == 173 OR $depart_id == 42): ?><td class="operate">							
										<a href="<?php echo U('OfficeSupplies/add',array('id'=>$vo['id']));?>" class="edit">修改</a>
										<a href="javascript:void(0)" value="<?php echo ($vo["id"]); ?>" url="<?php echo U('OfficeSupplies/del');?>" class="deleat">删除</a>
									</td><?php endif; ?>
								</tr><?php endforeach; endif; else: echo "" ;endif; ?> 
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<a id="get_goods_btn" class="btn btn-primary get_goods_btn" href="javascript:void(0)" >提交申领</a>
			</div>
			
			<div class="Pagination"><?php echo ($page); ?></div>
			
			<!-- E-表格部分 -->

		</div>
		<!-- E-页面主要部分 -->
		<script>
		$("#checkall").click(function() {
			  if($(this).attr("ischecked")==1)
			  {
				$(this).attr("ischecked",0);
				$("input[name='checkmenu[]']").each(function() {
				  $(this).prop('checked',false);
				});
			  }else{
				$(this).attr("ischecked",1);
				$("input[name='checkmenu[]']").each(function() {
				  $(this).prop('checked',true);
				});
			  }
		});
		 $(".get_goods_btn").click(function(){
				var ids = 0;
				var error_i = 0;
				var postdata = [];
				$("input[name='get_num']").each(function(){
					var get_num = $(this).val();
					var inventory = $(this).data('inventory');
					var id = $(this).data('recordid');
					//是否>0&&<库存
					if(get_num>0){
						if(get_num>inventory){
							$(this).addClass('error_index');
							/*alert('申领数量不能大于库存');
							$(this).css({"border":"1px solid red"});
							$(this).focus();
							throw "";*/
							error_i++;
						}else{
							$(this).removeClass('error_index');
							ids++;
							postdata.push({'id': id,'num':get_num});
						}
					}
				})
				if(error_i>0){
					$('.error_index').eq(0).focus();
					alert('申领数量不能大于库存');
				}else{
					if(ids==0){
						alert("请填写要申领的办公用品的数量！");
						return false;
					}
					$.ajax({
						url: "<?php echo U('OfficeSuppliesRecord/saveByMain');?>",
						data:{'data':postdata},
						type:'post',
						cache:false,
						dataType:'json',
						success:function(data) {
						  if(data.code==1){
							 window.location.href="<?php echo U('OfficeSuppliesRecord/index');?>?mid="+data.mid;	
						  }else{
							alert(data.msg);
						  }
						}
					});
				}
			
		  });
		  /*$(".tabgroup a").click(function(){
			var cid = $(this).data('cid');
			$("#tablegroup table").addClass("hide");
			$('#table_list'+cid).removeClass("hide");
		  })*/
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