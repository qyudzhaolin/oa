/*
* @Author: win
* @Date:   2016-04-14 14:41:38
* @Last Modified by:   win
* @Last Modified time: 2017-07-20 17:49:44
*/

'use strict';

//弹出框  alert("内容")
function alert(content,url){
	$("#alert").remove();
	//拼接弹出框
	var html="";
	html+='<div class="modal fade bs-example-modal-sm" id="alert">'
	html+='  <div class="modal-dialog modal-sm">'
	html+='<div class="modal-content">'
	html+='  <div class="modal-header">'
	html+='    <button type="button" class="close" id="X" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
	html+=' <h4 class="modal-title">提示信息</h4>'
	html+='</div>'
	html+='<div class="modal-body">'
	html+='  <p>'+content+'</p>'
	html+='</div>'
	html+='<div class="modal-footer">'
	if (url==undefined||url=="") {
		html+='    <button type="button" class="btn btn-success" id="closeAlert1" data-dismiss="modal">确定</button>'
	}else{
		html+='    <a class="btn btn-success" href="'+url+'" id="closeAlert1">确定</a>'
	}
	html+='  </div>'
	html+=' </div>'
	html+='  </div>'
	html+='</div>'
	$("body").append(html);

		$('#alert').modal({
		  keyboard: false
		});
}


function href(url){
	window.onload=""+url+""
}