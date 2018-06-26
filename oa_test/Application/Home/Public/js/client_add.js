/*
* @Author: win
* @Date:   2016-04-14 12:22:47
* @Last Modified by:   win
* @Last Modified time: 2016-04-15 17:03:44
*/

'use strict';

jQuery(document).ready(function($) {
	//下拉框改变对应的框改变
	$('#contacts_select').change(function(event) {
		var val=$(this).val();
		if (val=="其它") {
			$(this).parents("#select").hide();
			$("#write").show().children('input').val("").blur();
		}else{
			$("#write").children('input').val(val);
		}
	});
	$("#Submit").click(function(event) {
		//品牌名称
		var brand_name=$("#brand_name").val();

		//客户简称
		var client_name=$("#client_name").val();

		//客户全称
		var client_name_2=$("#client_name_2").val();

		//联系人
		var contacts=$("#contacts").val();

		//联系电话
		var telephone=$("#telephone").val();

		//专营业务员
		var salesman=$("#salesman").val();

		//分管部门
		var department=$("#department").val();

		//填写人
		var write_name=$("#write_name").val();		

		//console.log(brand_name+";"+client_name+';'+client_name_2+";"+contacts+";"+telephone+";"+salesman+";"+department+";"+write_name)
		if (brand_name=="") {
			alert("请填写品牌名称");
			return
		};
		if (client_name=="") {
			alert("请填写客户简称");
			return
		};
		if (client_name_2=="") {
			alert("请填写客户全称");
			return
		};
		/*if (contacts=="") {
			alert("请填写联系人");
			return
		};
		if (telephone=="") {
			alert("请填写联系电话");
			return
		};
		if (salesman=="") {
			alert("请填写专营业务员");
			return
		};
		if (department=="") {
			alert("请填写分管部门");
			return
		};
		if (write_name=="") {
			alert("请填写填写人");
			return
		};*/

		//表单提交
		//$('#forms').submit();
		var url = $('#forms').attr('action'),
			cust_id = $('#cust_id').val();
		$.ajax({
			url:url,
			type:'post',
			data:{
				cust_short_name:client_name,
				cust_full_name:client_name_2,
				brand_name:brand_name,
				contact:contacts,
				contact_number:telephone,
				department:department,
				sales_man:salesman,
				cust_id:cust_id,
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