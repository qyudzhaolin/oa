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
		html+='		<input class="form-control input-sm money" type="text"  onkeyUp="addMoney(this)" onblur="toFixed(this);addMoney(this)" maxlength="10">'
		html+='	</td>'
		html+='	<td class="col-md-6">'
		html+='		<input class="form-control input-sm comm" type="text" maxlength="100">'
		html+='	</td>'
		html+='	<td class="col-md-2">'
		html+='		<div class="file_input"><form action="" method="post" enctype="multipart/form-data"><input class="form-control input-sm" type="text" placeholder="点击上传文件"><input type="file" class="file_click" name="file"><input type="hidden" class="file_id" value="" name="file_id"/></form></div>'
		html+='	</td>'
		html+='</tr>'
		$(this).parents("tr").before(html);
	});

	//点击不同意显示输入理由
	$("body").on("click",".no_pass",function(){
		$(this).siblings('input').removeClass('hidden');
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
	
	$("#bank_name input").keyup(function() { 
		getSuplist();
	});
	
	//点击模糊列表
	$("body").on("click","#bank_name_list li",function(event) {
		var sup_id = $(this).attr("sup_id");
		var sup_name = $(this).text();
		if(sup_id!=0){
			$('#sup_id').val(sup_name);
			$('#sup_id').attr("sup_id",sup_id);
			var pay_method = $(this).attr("pay_method");
			var bnk_acct = $(this).attr("bnk_acct");
			var bnk_branch = $(this).attr("bnk_branch");
			var other = $(this).attr("other");
			$("#bnk_branch").val("");
			$("#bnk_acct").val("");
			$("#other").val("");
			$("#other").attr("val","");
			$("#bnk_branch").attr("val","");
			$("#bnk_acct").attr("val","");
			if(pay_method == '1'){
				if($("input[name='borrow_way']:checked").val() == '2'){
					$("#bnk_branch").val(bnk_branch);
					$("#bnk_acct").val(bnk_acct);
				}
				$("#bnk_branch").attr("val",bnk_branch);
				$("#bnk_acct").attr("val",bnk_acct);
			}else if(pay_method == '3'){
				if($("input[name='borrow_way']:checked").val() == '3'){
					$("#other").val(other);
				}
				$("#other").attr("val",other);
			}
		}
		$(this).parent("ul").hide();
	});
	
	$(document).click(function(event) {
		if(!$(event.target).is("#bank_name_list")){	
			$('#bank_name_list').hide();
		}
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

function getSuplist(){
	var keyword = $("#sup_id").val();
	var get_type = $("input[name='get_type']:checked").val();
	if(get_type=="1" || get_type=="3"){
		$.ajax({
			url:"/index.php/Home/Recouped/getsuplist",
			type:"post",
			dataType:'json', 
			data:{'keyword':keyword,'get_type':get_type},
			success:function(data){
				var strHTML = '<li class="list-group-item" sup_id="0" bnk_branch="" bnk_acct="" other="" pay_method="">未匹配到相关供应商</li>';
				if(data.status == 1){
					var list = eval(data.list);
					strHTML = '';
					for(var i=0;i<list.length;i++){
						strHTML += '<li class="list-group-item" sup_id="'+list[i]['sup_id']+'" bnk_branch="'+list[i]['bnk_branch']+'" bnk_acct="'+list[i]['bnk_acct']+'" other="'+list[i]['other']+'" pay_method="'+list[i]['pay_method']+'">'+list[i]['sup_full_name']+'</li>'; 
					}
				}
				$("#bank_name_list").html(strHTML);
			}
		});
		$('#sup_id').attr("sup_id",0);
		$('#bank_name_list').show();
	}
}
