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
		html+=cost_id
		html+='	</td>'
		html+='	<td class="col-md-1">'
		html+='		<input class="form-control input-sm usable_money" type="text" disabled="disabled">'
		html+='	</td>'
		html+='	<td class="col-md-1">'
		html+='		<input class="form-control input-sm money" type="text"  onkeyUp="addMoney(this)" onblur="toFixed(this);addMoney(this)" maxlength="10">'
		html+='	</td>'
		html+='	<td class="col-md-9">'
		html+='		<input class="form-control input-sm comm" type="text" maxlength="100">'
		html+='	</td>'
		html+='</tr>'
		$(this).parents("tr").before(html);
	});
	
	//点击不同意显示输入理由
	$("body").on("click",".no_pass",function(){
		$(this).siblings('input').removeClass('hidden');
	});
	
	$("#li-cb a").click(function(){
		$("#c_b .first").get(0).selectedIndex=0;
		$("#c_b .second").get(0).selectedIndex=0;
		$("#c_b .third").get(0).selectedIndex=0;
	});
	
	$("#li-hd a").click(function(){
		$("#h_d .first").get(0).selectedIndex=0;
		$("#h_d .second").get(0).selectedIndex=0;
		$("#h_d .third").get(0).selectedIndex=0;
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