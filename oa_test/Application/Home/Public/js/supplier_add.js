/*
* @Author: win
* @Date:   2016-04-14 12:22:47
* @Last Modified by:   win
* @Last Modified time: 2016-04-15 16:23:33
*/

'use strict';

jQuery(document).ready(function($) {
	//下拉框改变对应的框改变
	$('#way_select').change(function(event) {
		var val=$(this).val();
		if (val=="其它") {
			$(this).parents("#select").hide();
			$("#write").show().children('input').val("").blur();
		}else{
			$("#write").children('input').val(val);
		}
	});
	$("#Submit").click(function(event) {
		//供应商名称
		var supplier_name=$("#supplier_name").val();

		//供应商简称
		var supplier_name_2=$("#supplier_name_2").val();
		
		var level1_cid = $("#level1_cid").val();
		
		
		var level2_cid = $("#level2_cid").val();
		var level2_cid_requird = $("#level2_cid").attr('rqd');
		
		//收款人
		var collect_money=$("#collect_money").val();

		//收款方式
		var way=$("#way").val();
		var area=$("#area").val();

		//收款账号
		var bank_num=$("#bank_num").val();

		//所属银行
		var branch_info=$("#branch_info").val();

		var other=$("#other").val();		
		var sup_id = $("#sup_id").val();
		//console.log(supplier_name+";"+supplier_name_2+';'+collect_money+";"+way+";"+bank_num+";"+bank+";"+branch_info+";"+write_name)
		if (supplier_name=="") {
			alert("请填写供应商名称");
			return
		};
		if (supplier_name_2=="") {
			alert("请填写供应商简称");
			return
		};
		if (level1_cid=="0") {
			alert("请选择一级分类");
			return
		};
		if (level2_cid_requird==1){
			if (level2_cid=="0") {
				alert("请选择二级分类");
				return;
			};
		};
		if (collect_money=="") {
			alert("请填写收款人");
			return
		};
		if (way=="") {
			alert("请填写或选择收款方式");
			return
		};
		if (area=="") {
			alert("请填写区域");
			return
		};
		
		if(way=="1"){
			if (bank_num=="") {
				alert("请填写银行账号");
				return
			};
			if (branch_info=="") {
				alert("请填写分行信息");
				return
			};
		}else if(way=="4"){
			if (other=="") {
				alert("请填写其他信息");
				return
			};
		}
		var contact = $('#contact').val();
		var contact_tel = $('#contact_tel').val();
		/*if (contact=="") {
			alert("请填写联系人");
			return
		};
		if (contact_tel=="") {
			alert("请填写联系方式");
			return
		};*/
		
		
		//表单提交
		//$('#forms').submit();
		var url = $('#forms').attr('action');
		$.ajax({
			url:url,
			type:'post',
			data:{
				sup_short_name:supplier_name_2,
				sup_full_name:supplier_name,
				payee:collect_money,
				pay_method:way,
				bnk_acct:bank_num,
				bnk_branch:branch_info,
				other:other,
				sup_id:sup_id,
				contact_tel:contact_tel,
				contact:contact,
				area:area,
				level1_cid:level1_cid,
				level2_cid:level2_cid,
			},
			dataType:'json',
			success:function(res){
				alert(res.msg);
				if(res.code==1){
					$('#closeAlert1').click(function(){
						var backurl = $('#back_url').val();
						window.location.href=backurl;
					})
				}
			}
		});
	});
	
	$("#SubmitCategory").click(function(event) {
		//供应商名称
		var name=$("#name").val();

		//供应商简称
		var pid =$("#pid").val();

		
		if (name=="") {
			alert("请填写供应商分类名称");
			return
		};
		
		
		
		//表单提交
		//$('#forms').submit();
		var url = $('#forms').attr('action');
		$.ajax({
			url:url,
			type:'post',
			data:{
				name:name,
				pid:pid,
				id:$('#id').val()
			},
			dataType:'json',
			success:function(res){
				alert(res.msg);
				if(res.code==1){
					$('#closeAlert1').click(function(){
						var backurl = $('#back_url').val();
						window.location.href=backurl;
					})
				}
			}
		});
	});





	










});