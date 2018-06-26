/*
* @Author: win
* @Date:   2016-04-14 12:22:47
* @Last Modified by:   win
* @Last Modified time: 2016-04-21 10:50:33
*/

'use strict';

jQuery(document).ready(function($) {
	//选择组显示对应的组人员
	var canClick1 = true
	$("body").on("change",".group input",function(){
		if (this.checked==true) {
			var val=$(this).val();
			var depart_name=$(this).attr("name"); 
			var proj_id=$("#proj_id").val();
			if (canClick1) {	
				var html="";
				var url = $("#departs").val();
				$.ajax({
					url: url,
					type:'post',
					dataType:'json',
					data:{
						'depart_id':val,
						'proj_id':proj_id
					},
					success:function(data){
						if(data.status == 1){
							if(data.users.length > 0){
								html+='<div class="gr_num">	'									
								html+='	<strong id="'+val+'">'+depart_name+'：</strong>'	
								data.users.map(function(obj){	
									//alert(obj);
								    html+='   	<label style="margin-right: 10px;">';	
								    if(obj.is_check){
								    	html+='     	<input type="checkbox" value="'+obj.user_id+'" checked="checked">'+obj.real_name+'';
								    }else{
								    	html+='     	<input type="checkbox" value="'+obj.user_id+'" checked="checked">'+obj.real_name+'';
								    }
								    html+='   	</label>';	
								})
									html+='</div>'
							}
							$("#group_people .right .checkbox .group_people").append(html);
						}else{
							alert(data.msg);
						}
						canClick1 = true
					},
					error:function(){
						alert("网络繁忙");
						canClick1 = true;
					}
				})
			};
		}else{
			var val=$(this).val();
			for(var i=0;i<=$("#group_people .right .checkbox .group_people .gr_num").length-1;i++){
				var depart_id=$("#group_people .right .checkbox .group_people .gr_num").eq(i).children('strong').attr("id");
				if (depart_id==val) {
					$("#group_people .right .checkbox .group_people .gr_num").eq(i).remove();
				};
			}
		}
	})
});