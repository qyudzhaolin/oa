/*
* @Author: win
* @Date:   2016-04-14 12:22:47
* @Last Modified by:   win
* @Last Modified time: 2016-04-20 16:13:22
*/

'use strict';
var cla='';
var index=0;
jQuery(document).ready(function($) {
	//点击第三方
	var trilateral=true;
	$("#add_trilateral").click(function(event) {
		if ($(this).text()=='新增第三方费用') {
			$('.trilateral').show();
			$(this).text('删除第三方费用');
		}else{
			$('.trilateral').hide();
			$(this).text('新增第三方费用');
			$(".third_party").each(function(){
				$(this).find("input").val('');
				$(this).find(".cost_id").attr('data-cost','');
			})
			$(".d_budget_total").text('预算总计：');
			$(".d_final_total").text('预算总计：');
			$("#d_budget_total").val(0);
			$("#d_final_total").val(0);
			addBudgetMoney();
		}
	});
	//点击新增智源体系
	$("#add_setup").click(function(event) {
		if ($(this).text()=='新增智源体系费用') {
			$('.setup').show();
			$(this).text('删除智源体系费用');
		}else{
			$('.setup').hide();
			$(this).text('新增智源体系费用');
			$(".system_party").each(function(){
				$(this).find("input").val('');
				$(this).find(".cost_id").attr('data-cost','');
			})
			$(".z_budget_total").text('预算总计：');
			$(".z_final_total").text('预算总计：');
			$("#z_budget_total").val(0);
			$("#z_final_total").val(0);
			addBudgetMoney();
		}
	});

	//第三方费用的新增按钮
	var id_num=1;
	$(".third_party_addBtn").click(function(event) {
		id_num++;
		var html="";
		html+='<tr class="third_party">'
		html+='	<td><input type="text" class="form-control input-sm cost_id" readonly="readonly" data-cost=""  data-type="1"></td>'
		html+='	<td><input class="form-control input-sm budget_money" type="text" value="" onkeyUp="addDBudegtMoney(this)" onblur="toFixed(this);addDBudegtMoney(this)" maxlength="10"></td>'
		html+='	<td><input class="form-control input-sm budget_info"  type="text" maxlength="100"></td>'
		html+='	<td><div class="file_input"><form action="" method="post" enctype="multipart/form-data"><input class="form-control input-sm" type="text" placeholder="点击上传文件"><input type="file" class="file_click" name="file"><input type="hidden" class="file_id" value="" name="file_id"/></form></div></td>'
		html+='	<td><input class="form-control input-sm final_money"  type="text" disabled="disabled"></td>'
		html+='<td></td></tr>'
		$(this).parents("tr").before(html);
	});

	//智源体系新增科目按钮
	var maxpr_num=1;
	$("#system_addBtn").click(function(event) {
		maxpr_num++;
		var html="";
		html+='<tr class="system_party">'
		html+='	<td><input type="text" class="form-control input-sm cost_id" readonly="readonly" data-cost="" data-type="2"></td>'
		html+='	<td><input class="form-control input-sm budget_money" type="text" value="" onkeyUp="addDBudegtMoney(this)" onblur="toFixed(this);addDBudegtMoney(this)" maxlength="10"></td>'		
		html+='	<td><input class="form-control input-sm budget_info"  type="text" maxlength="100"></td>'
		html+='	<td></td>'
		html+='	<td><input class="form-control input-sm final_money"  type="text" disabled="disabled"></td>'
		html+='<td></td></tr>'

		$(this).parents("tr").before(html);
	});
	
	
	$("#po_addBtn").click(function(event) {
		if($("#role").val() == 1){
			var html='<table class="table table-bordered setup po_table" style="display: table; width:800px" >';
			html+='<tr><td class="active2" rowspan="3">PO单号</td><td colspan="1" rowspan="3"><input class="form-control input-sm po_no" value="" type="text" ></td><td class="active2">PO金额</td><td><input class="form-control input-sm po_money" value="" type="text" onkeyUp="addPOMoney(this)" onblur="addPOMoney(this)"></td><td class="active2">快递单号</td><td><input class="form-control input-sm express_no" value="" type="text"></td></tr>'
			html+='<tr><td class="active2">开票金额</td><td><input disabled="disabled" class="form-control input-sm kp_money" value="" type="text"onkeyUp="addKPMoney(this)" onblur="addKPMoney(this)"></td><td class="active2">开票日期</td><td><input disabled="disabled" class="form-control kp_date" placeholder="日期" id="kp_date" name="kp_date" onclick="WdatePicker()" value="" type="text"></td></tr>'
			html+='<tr><td class="active2">回款金额</td><td><input disabled="disabled" class="form-control input-sm back_money" value="" type="text" onkeyUp="addBackMoney(this)" onblur="addBackMoney(this)"></td><td class="active2">回款日期</td><td><input disabled="disabled" class="form-control back_date" placeholder="日期" name="back_date" onclick="WdatePicker()" value="" type="text"></td></tr>'
			html+='</table><br/>'
		}else if($("#role").val() == 2){
			var html='<table class="table table-bordered setup po_table" style="display: table; width:800px" >';
			html+='<tr><td class="active2" rowspan="3">PO单号</td><td colspan="1" rowspan="3"><input disabled="disabled" class="form-control input-sm po_no" value="" type="text" ></td><td class="active2">PO金额</td><td><input disabled="disabled" class="form-control input-sm po_money" value="" type="text" onkeyUp="addPOMoney(this)" onblur="addPOMoney(this)"></td><td class="active2">快递单号</td><td><input disabled="disabled" class="form-control input-sm express_no" value="" type="text"></td></tr>'
			html+='<tr><td class="active2">开票金额</td><td><input class="form-control input-sm kp_money" value="" type="text"onkeyUp="addKPMoney(this)" onblur="addKPMoney(this)"></td><td class="active2">开票日期</td><td><input class="form-control kp_date" placeholder="日期" id="kp_date" name="kp_date" onclick="WdatePicker()" value="" type="text"></td></tr>'
			html+='<tr><td class="active2">回款金额</td><td><input class="form-control input-sm back_money" value="" type="text" onkeyUp="addBackMoney(this)" onblur="addBackMoney(this)"></td><td class="active2">回款日期</td><td><input class="form-control back_date" placeholder="日期" name="back_date" onclick="WdatePicker()" value="" type="text"></td></tr>'
			html+='</table><br/>'
		}

		$(".po_content").append(html);
	});
	
	
	$(".delete_Btn").click(function(){
		$(this).parents("tr").remove();
		addDBudegtMoney(this);
	})
	
	//点击选择款项显示弹出框
	$("body").on("click",".table .cost_id",function(event) {
		index=$(this).parents("tr").index();
		cla=$(this).parents("tr").attr("class");
		
		var cost_id = $(this).attr("data-cost");
		var data_type = $(this).attr("data-type");
		var cb_first_str = '<option value="0">请选择一级</option>';
		var hd_first_str = '<option value="0">请选择一级</option>';
		if(data_type==1){
			cb_first_str += '<option value="3">第三方费用</option>';
			hd_first_str += '<option value="41">第三方费用</option>';
		}else{
			cb_first_str += '<option value="4">智源体系</option>';
			hd_first_str += '<option value="42">智源体系</option>';
		}
		$("#c_b").find(".first").html(cb_first_str);
		$("#h_d").find(".first").html(hd_first_str);
		if(cost_id.length>0){
			$.ajax({
				url:"getpcost",
				type:"post",
				dataType:'json', 
				data:{'cid':cost_id},
				success:function(data){
					if(data.status == 1){
						var pcosts = eval(data.costs);
							var obj;
							if(pcosts['ish'] == 1){
								$("#li-cb").addClass("active");
								$("#c_b").addClass("active");
								$("#li-hd").removeClass("active");
								$("#h_d").removeClass("active");
								obj = $("#c_b");
							}else{
								$("#li-hd").addClass("active");
								$("#h_d").addClass("active");
								$("#li-cb").removeClass("active");
								$("#c_b").removeClass("active");
								obj = $("#h_d");
							}
							
							obj.find(".first").find("option[value='"+pcosts['child']['ish']+"']").attr("selected",true);
							var second_arr = pcosts['child']['child'];
							var second_str = '<option value="0">请选择二级</option>';
							for(var i=0;i<second_arr['row'].length;i++){
								if(second_arr['ish'] == second_arr['row'][i]['id']){
									second_str += '<option value="'+second_arr['row'][i]['id']+'" selected="selected">'+second_arr['row'][i]['costname']+'</option>';
								}else{
									second_str += '<option value="'+second_arr['row'][i]['id']+'">'+second_arr['row'][i]['costname']+'</option>';
								}
							}
							obj.find(".second").html(second_str);
							var third_arr = pcosts['child']['child']['child'];
							var third_str = '<option value="0">请选择三级</option>';
							for(var i=0;i<third_arr['row'].length;i++){
								if(third_arr['ish'] == third_arr['row'][i]['id']){
									third_str += '<option value="'+third_arr['row'][i]['id']+'" selected="selected">'+third_arr['row'][i]['costname']+'</option>';
								}else{
									third_str += '<option value="'+third_arr['row'][i]['id']+'">'+third_arr['row'][i]['costname']+'</option>';
								}
							}
							obj.find(".third").html(third_str);
					}else{
						alert(data.msg);
						return false;
					}
				}
			});
		}else{
			$("#li-cb").addClass("active");
			$("#c_b").addClass("active");
			$("#li-hd").removeClass("active");
			$("#h_d").removeClass("active");
			$("#c_b .first").get(0).selectedIndex=0;
			$("#c_b .second").get(0).selectedIndex=0;
			$("#c_b .third").get(0).selectedIndex=0;
		}
		$('#myModal').modal('show');
	});
	
	
	$(".first").change(function(){
		if($(this).val() > 0){
			var parent = $(this).parent().parent();
			$.ajax({
				url:"getcost",
				type:"post",
				dataType:'json', 
				data:{'pid':$(this).val()},
				success:function(data){
					if(data.status == 1){
						var costs=eval(data.costs);
						var str = '<option value="0">请选择二级</option>';
						for(var i=0;i<costs.length;i++){
							str += '<option value="'+costs[i]['id']+'">'+costs[i]['costname']+'</option>';
						}
						parent.find(".second").html(str);
					}else{
						alert(data.msg);
						return false;
					}
				}
			});
		}
	})
	
	$(".second").on('change',function(){
		if($(this).val() > 0){
			var parent = $(this).parent().parent();
			$.ajax({
				url:"getcost",
				type:"post",
				dataType:'json', 
				data:{'pid':$(this).val()},
				success:function(data){
					if(data.status == 1){
						var costs=eval(data.costs);
						var str = '<option value="0">请选择三级</option>';
						for(var i=0;i<costs.length;i++){
							str += '<option value="'+costs[i]['id']+'">'+costs[i]['costname']+'</option>';
						}
						parent.find(".third").html(str);
						
					}else{
						alert(data.msg);
						return false;
					}
				}
			});
		}
	})

		//点击弹出框的确定
	$("#confirm_btn").click(function(event) {
		if($("#c_b").hasClass("active")){
			var div = $("#c_b")
		}else{
			var div = $("#h_d")
		}
		var cost_id = div.find(".third").val();
		var cost_name = div.find(".third option:selected").text(); 
		if(cost_id == 0){
			alert("请选择第三级");
			return;
		}
		$("."+cla).eq(index-2).find('.cost_id').attr("data-cost",cost_id);
		$("."+cla).eq(index-2).find('.cost_id').val(cost_name);
		
		$('#myModal').modal('hide');
	});
	
	$("#li-cb a").click(function(){
		$("#c_b .first").get(0).selectedIndex=0;
		$("#c_b .second").get(0).selectedIndex=0;
		$("#c_b .third").get(0).selectedIndex=0;
	});
	
	$("#li-hd a").click(function(){
		$("#h_d .first").get(0).selectedIndex=0;
		$("#h_d .second").get(0).selectedIndex=0;
		$("#h_d .third").get(0).selectedIndex=0;
	});
	
	//上传框改变
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

function toFixed(obj){
	$(obj).val(Number($(obj).val()).toFixed(2));
}

function addBudgetMoney(){
	var budget_cntr_income = $("#budget_cntr_income").val();
	var budget_point = (budget_cntr_income*0.0634).toFixed(2);
	var d_budget_total = $("#d_budget_total").val();
	var z_budget_total = $("#z_budget_total").val();
	var budget_proj_profit = (budget_cntr_income-budget_point-d_budget_total-z_budget_total).toFixed(2);
	$("#budget_point").text(budget_point);
	$("#budget_proj_profit").text(budget_proj_profit);
}

function addFinalMoney(){
	var final_cntr_income =$("#final_cntr_income").val();
	var final_point = (final_cntr_income*0.0634).toFixed(2);
	var d_final_total = $("#d_final_total").val();
	var z_final_total = $("#z_final_total").val();
	var final_proj_profit = (final_cntr_income-final_point-d_final_total-z_final_total).toFixed(2);
	$("#final_point").text(final_point);
	$("#final_proj_profit").text(final_proj_profit);
}

function addDBudegtMoney(obj){
	var allMoney=0;
	var cla = $(obj).parents("tr").attr("class");
	$("."+cla).each(function(index, el) {
		allMoney+=Number($(this).find('.budget_money').val());
	});
	allMoney=allMoney.toFixed(2);
	if(cla=='third_party'){
		$("#d_budget_total").val(allMoney);
		$(".d_budget_total").text("预算总计："+allMoney);
	}else{
		$("#z_budget_total").val(allMoney);
		$(".z_budget_total").text("预算总计："+allMoney);
	}
	addBudgetMoney();
}

function addZBudegtMoney(obj){
	var allMoney=0;
	var cla = $(obj).parents("tr").attr("class");
	$("."+cla).each(function(index, el) {
		allMoney+=Number($(this).find('.final_money').val());
	});
	allMoney=allMoney.toFixed(2);
	if(cla=='third_party'){
		$("#d_final_total").val(allMoney);
		$(".d_final_total").text("决算总计："+allMoney);
	}else{
		$("#z_final_total").val(allMoney);
		$(".z_final_total").text("决算总计："+allMoney);
	}
	addFinalMoney();
}

//最后计算
function calculate(){
	var allTBMoney=0;
	$(".third_party").each(function(index, el) {
		allTBMoney+=Number($(this).find('.budget_money').val());
	});
	allTBMoney = allTBMoney.toFixed(2);
	$("#d_budget_total").val(allTBMoney);
	$(".d_budget_total").text("预算总计："+allTBMoney);
	
	var allSBMoney=0;
	$(".system_party").each(function(index, el) {
		allSBMoney+=Number($(this).find('.budget_money').val());
	});
	allSBMoney = allSBMoney.toFixed(2);
	$("#z_budget_total").val(allSBMoney);
	$(".z_budget_total").text("预算总计："+allSBMoney);
	
	addBudgetMoney();
}

//计算po金额
function addPOMoney(obj){
	var allMoney=0;
	$(".po_money").each(function(i) {
		allMoney+=Number($(this).val());
	});
	allMoney=allMoney.toFixed(2);
	$(".z_po_total").text("PO金额总计："+allMoney);
}

//计算开票金额
function addKPMoney(obj){
	var allMoney=0;
	$(".kp_money").each(function(i) {
		allMoney+=Number($(this).val());
	});
	allMoney=allMoney.toFixed(2);
	$(".z_kp_total").text("开票金额总计："+allMoney);
}

//计算回款金额
function addBackMoney(obj){
	var allMoney=0;
	$(".back_money").each(function(i) {
		allMoney+=Number($(this).val());
	});
	allMoney=allMoney.toFixed(2);
	$(".z_back_total").text("回款金额总计："+allMoney);
}