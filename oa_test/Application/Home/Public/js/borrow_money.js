/*
* @Author: win
* @Date:   2016-04-14 12:22:47
* @Last Modified by:   win
* @Last Modified time: 2016-04-20 12:05:28
*/

'use strict';

jQuery(document).ready(function($) {
	//点击添加新的一列
	var id_num=1;
	$(".add_btn").click(function(event) {
		id_num++;
		var html="";
		html+='<tr class="fund">'
		html+='	<td class="col-md-1">'
		html+='	<input class="form-control input-sm money_name cost_id" readonly="readonly" data-cost="" type="text"  value="">'
		html+='	</td>'
		html+='	<td class="col-md-1">'
		html+='		<input class="form-control input-sm money" type="text" onkeyUp="addMoney(this)" onblur="toFixed(this)">'
		html+='	</td>'
		html+='	<td class="col-md-1">'
		html+='		<input class="form-control input-sm usable_money" type="text" onblur="toFixed(this)">'
		html+='	</td>'
		html+='	<td class="col-md-9">'
		html+='		<input class="form-control input-sm comm" type="text">'
		html+='	</td>'
		html+='</tr>'
		$(this).parents("tr").before(html);

	});












});