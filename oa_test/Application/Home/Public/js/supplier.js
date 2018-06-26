/*
* @Author: win
* @Date:   2016-04-14 12:22:47
* @Last Modified by:   win
* @Last Modified time: 2016-04-15 14:13:07
*/

'use strict';

jQuery(document).ready(function($) {

	//删除
	$("body").on("click",".table_bd .operate .deleat",function(){

		var _this = $(this);

		//当点击确定时 返回 true 
		if(window.confirm("确定要删除吗?")){
			//$(this).parents(".table_bd").remove();
			$.ajax({
				url:$(this).attr('url'),
				data:{id:_this.attr('value')},
				dataType:'json',
				success:function(res){
					alert(res.msg);
					if(res.code==1){
						location.reload();
					}
				}
			})
		}	
	});
});