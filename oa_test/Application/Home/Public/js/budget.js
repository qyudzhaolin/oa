/*
* @Author: win
* @Date:   2016-04-14 12:22:47
* @Last Modified by:   win
* @Last Modified time: 2016-04-15 12:30:01
*/

'use strict';

jQuery(document).ready(function($) {
/*	//编辑
	$("body").on("click",".table_bd .operate .edit",function(){
		var num=$(this).siblings('.flag').val();
		if (num==0) {
			$(this).siblings('.flag').val(1);
			$(this).text("完成");
			$(this).parents(".table_bd").find('input').removeAttr('readonly').addClass('current');


		}else{
			$(this).siblings('.flag').val(0);	
			$(this).text("编辑");	
			$(this).parents(".table_bd").find('input').attr('readonly','readonly').removeClass('current');
	
		}
	});*/

	//删除
	$("body").on("click",".table_bd .operate .deleat",function(){



		//当点击确定时 返回 true 
		if(window.confirm("确定要删除吗?")){
			$(this).parents(".table_bd").remove();
		}	
	});











});