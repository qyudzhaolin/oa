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
	//第三方费用的新增按钮
	var id_num=1;
	$(".third_party_addBtn").click(function(event) {
		id_num++;
		var html="";
		html+='<tr class="third_party">'
		html+='	<td><input type="text" class="form-control input-sm cost cost_id" readonly="readonly" data-cost="" data-exp="" data-type="1"></td>'
		html+='	<td><input class="form-control input-sm budget_money" type="text" value="" onblur="toFixed(this);" maxlength="10"></td>'
		html+='	<td><input class="form-control input-sm budget_info"  type="text"></td>'
		html+='	<td><input class="form-control input-sm modify_money" type="text" readonly="readonly"></td>'
		html+='<td colspan="2"><input class="form-control input-sm mark"  type="text"></td></tr>'
		$(this).parents("tr").before(html);
	});

	//智源体系新增科目按钮
	var maxpr_num=1;
	$("#system_addBtn").click(function(event) {
		maxpr_num++;
		var html="";
		html+='<tr class="system_party">'
		html+='	<td><input type="text" class="form-control input-sm cost cost_id" readonly="readonly" data-cost="" data-exp="" data-type="2"></td>'
		html+='	<td><input class="form-control input-sm budget_money" type="text" value="" onblur="toFixed(this);" maxlength="10"></td>'
		html+='	<td><input class="form-control input-sm budget_info"  type="text"></td>'
		html+='	<td><input class="form-control input-sm modify_money" type="text" readonly="readonly"></td>'
		html+='<td colspan="2"><input class="form-control input-sm mark"  type="text"></td></tr>'

		$(this).parents("tr").before(html);
	});
	
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
});

function toFixed(obj){
	$(obj).val(Number($(obj).val()).toFixed(2));
}
