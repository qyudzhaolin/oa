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
		var titile=$("#titile").val();

		//客户简称
		var source=$("#source").val();

		//客户全称
		var content=$("#content").val();

		

		//console.log(brand_name+";"+client_name+';'+client_name_2+";"+contacts+";"+telephone+";"+salesman+";"+department+";"+write_name)
		if (titile=="") {
			alert("请填写新闻标题");
			return
		};
		if (source=="") {
			alert("请填写新闻来源");
			return
		};
		if (content=="") {
			alert("请填写内容");
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
				titile:titile,
				source:source,
				content:content
				
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