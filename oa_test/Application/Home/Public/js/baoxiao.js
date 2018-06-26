/*
* @Author: win
* @Date:   2016-04-14 12:22:47
* @Last Modified by:   win
* @Last Modified time: 2016-04-18 16:14:33
*/

'use strict';
var index=0;
var costs;
jQuery(document).ready(function($) {

	$(".add_btn").click(function(event) {
		var cost_id = $(".fund").find('td').first().html();
		var html="";
		html+='<tr class="fund">'
		html+='	<td class="col-md-1">'
		html+=cost_id
		html+='	</td>'
		html+='	<td class="col-md-1">'
		html+='		<input class="form-control input-sm usable_money" type="text" disabled="disabled">'
		html+='	</td>'
		html+='	<td class="col-md-1">'
		html+='		<input class="form-control input-sm money" type="text"  onkeyUp="addMoney(this)" onblur="toFixed(this);addMoney(this)" maxlength="10">'
		html+='	</td>'
		html+='	<td class="col-md-9">'
		html+='		<input class="form-control input-sm comm" type="text" maxlength="100">'
		html+='	</td>'
		html+='</tr>'
		$(this).parents("tr").before(html);
	});


	//点击选择款项显示弹出框
/*	$("body").on("click",".fund .cost_id",function(event) {
		index=$(this).parents(".fund").index();
		var cost_id = $(this).attr("data-cost");
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
						costs=eval(data.costs);
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
						costs=eval(data.costs);
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
		//$(".fund").eq(index-1).find('.cost_id').val(1);
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
		$(".fund").eq(index-1).find('.cost_id').attr("data-cost",cost_id);
		$(".fund").eq(index-1).find('.cost_id').val(cost_name);
		
		$('#myModal').modal('hide');
	});*/
	
	
	//点击不同意显示输入理由
	$("body").on("click",".no_pass",function(){
		$(this).siblings('input').removeClass('hidden');
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

function dx(n) {
    if (!/^(0|[1-9]\d*)(\.\d+)?$/.test(n))
        return "数据非法";
    var unit = "千百拾亿千百拾万千百拾元角分", str = "";
        n += "00";
    var p = n.indexOf('.');
    if (p >= 0)
        n = n.substring(0, p) + n.substr(p+1, 2);
        unit = unit.substr(unit.length - n.length);
    for (var i=0; i < n.length; i++)
        str += '零壹贰叁肆伍陆柒捌玖'.charAt(n.charAt(i)) + unit.charAt(i);
    return str.replace(/零(千|百|拾|角)/g, "零").replace(/(零)+/g, "零").replace(/零(万|亿|元)/g, "$1").replace(/(亿)万|壹(拾)/g, "$1$2").replace(/^元零?|零分/g, "").replace(/元$/g, "元整");
}

function addMoney(obj){
	var allMoney=0;
	$(".fund").each(function(index, el) {
		allMoney+=Number($(this).find('.money').val());
	});
	allMoney=allMoney.toFixed(2);
	$("#tot_amt").val(allMoney);
	$("#tot_amt_d").val(dx(allMoney));
}