/*
* @Author: win
* @Date:   2016-04-14 12:22:47
* @Last Modified by:   win
* @Last Modified time: 2016-04-15 16:23:33
*/

'use strict';

jQuery(document).ready(function($) {
	var pre_flow_uids = $('#pre_user_ids').val();
	if(pre_flow_uids){
		loadUserList();
	}
	
	//上传框改变
	$("body").on('change', '.file_click', function(event) {
		event.preventDefault();
		var obj = $(this);
		obj.siblings('.input-sm').attr("placeholder","上传中请稍后....");
		var prospectus=obj.val();
		if(prospectus.length>0){
			obj.closest("form").ajaxSubmit({
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
	
	
	$("#Submit").click(function(event) {
		//供应商名称
		var depart_id = '';
		var user_ids = '';
		var next_date = '';
		var name=$("#name").val();
		var phone=$("#phone").val();
		var email=$("#email").val();
		var sex=$("input[name='sex']:checked").val();
		var job=$("#job").val();
		var file_id=$("#file_id").val();
		var pre_salary=$("#pre_salary").val();
		/*var salary=$("#salary").val();
		var subsidy=$("#subsidy").val();
		var base_tax=$("#base_tax").val();
		var is_pre_tax=$("input[name='is_pre_tax']:checked").val();
		var is_13salary=$("input[name='is_13salary']:checked").val();*/
		var remark=$("#remark").val();
		var disadvantage=$("#disadvantage").val();
		var advantage=$("#advantage").val();
		var result=$("#result").val();
		var result_status=$("input[name='result_status']:checked").val();
		
		var id=$("#id").val();
		if (name=="") {
			alert("请填写姓名");
			return false;
		};
		if (phone=="") {
			alert("请填写电话");
			return
		};
		if (email=="") {
			alert("请填写邮箱");
			return false;
		};
		if (job=="") {
			alert("请填写面试岗位");
			return false;
		}
		if (file_id=="" || file_id=="0") {
			alert("请上传简历");
			return false;
		}
		if (pre_salary=="") {
			alert("请填写期望薪资");
			return false;
		};
		if(result_status==1){
			depart_id = $('#depart_id').val();
			user_ids = $('#user_ids').val();
			next_date = $('#next_date').val();
			if(user_ids==''){
				var pre_user_ids = $('#pre_user_ids').val();
				if(pre_user_ids==''){
					alert('请选择流转人员！');
					return false;
				}else{
					user_ids = pre_user_ids;
				}
			}
		}else{
			user_ids = '';
			next_date = '';
			depart_id = '';
		}
		//表单提交
		//$('#forms').submit();
		var url = $('#forms').attr('action');
		$.ajax({
			url:url,
			type:'post',
			data:{
				name:name,
				phone:phone,
				email:email,
				sex:sex,
				job:job,
				file_id:file_id,
				pre_salary:pre_salary,
				/*salary:salary,
				subsidy:subsidy,
				base_tax:base_tax,
				is_pre_tax:is_pre_tax,
				is_13salary:is_13salary,*/
				remark:remark,
				advantage:advantage,
				disadvantage:disadvantage,
				result:result,
				result_status:result_status,
				depart_id:depart_id,
				user_ids:user_ids,
				next_date:next_date,
				hr_id:$('#hr_id').val(),
				id:id
			},
			dataType:'json',
			success:function(res){
				alert(res.msg);
				if(res.code==1){
					$('#closeAlert1').click(function(){
						window.location.href='/index.php/Home/UserHr/index';
					})
				}
			}
		});
	});
	
	//------modal end
$('.result_status').click(function(){
	var result_status = $(this).find('input[name="result_status"]').val();
	if(result_status==1){
		$('#flowdiv').removeClass('hide');
		$('#flowdiv').show();
	}else{
		$('#flowdiv').hide();
	}
})
	
	//------modal start后期写成扩展 可以直接调用 根据部门读取该部门下的人员
	$('#depart_id').on('change',function(){
		//重置user_ids
		loadUserList();
	})
	
	function loadUserList(){
		var depart_id = $('#depart_id').val();
		$('#user_ids').val('');
		$.ajax({
			url:'/index.php/Home/Depart/ajaxGetUsers',
			type:'post',
			data:{
				depart_id:depart_id,
				flow_uids : $('#pre_user_ids').val()
			},
			dataType:'json',
			success:function(res){
				if(res.status==1){
					$('#flowuser').html(res.html);
				}else{
					$('#flowuser').html('暂无该级别人员');
				}
			}
		});
	}
	
	//用户选择用户后，拼接用户user_ids
	$('#flowuser').on('click','.flowUserTab',function(){
		var user_ids = ''
		 $(".flowUserTab").find("input[type='checkbox']:checked").each(function(){
			 var user_id = $(this).val();
			if(user_ids){
				user_ids = user_ids + ','+ user_id; 
			}else{
				user_ids = user_id;
			}
		  });
		  $('#user_ids').val(user_ids);
		/*var user_ids = $('#user_ids').val();
		var user_id = $(this).find("input[type='checkbox']:checked").val();
		if(user_ids && user_ids!=user_id){
			user_ids = user_ids + ','+ user_id; 
		}else{
			user_ids = user_id;
		}
		$('#user_ids').val(user_ids);*/
	})
	
});