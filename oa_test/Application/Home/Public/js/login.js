/*
* @Author: win
* @Date:   2016-04-14 11:43:36
* @Last Modified by:   win
* @Last Modified time: 2016-04-14 12:09:48
*/

'use strict';
jQuery(document).ready(function($) {
	$("#submit").click(function(event) {
		var user_name=$("#user_name").val();
		var password=$("#password").val();
		//console.log(user_name + ";"+ password)
		if (user_name=="") {
			alert("请填写用户名");
		}
		else if(password==""){
            alert("请填写密码");
		}
		else{
			$.ajax({
				url:"{{:U('Login/index','','')}}",
				type:"post",
				dataType:'json', 
				data:{'username':username,"password":password},
				success:function(data){
					if(data.status == 1){
						window.location.href = "{{:U('Index/index','','')}}"; 
					}else{
						alert(data.msg);
						return false;
					}
				}
			});
		}
	});
});