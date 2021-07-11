/*
 * @ author: Min-Soo Kim
 * @ brief: a javascript file for Blueberry Search
*/
function update_input() {
	var search_target_box = {
		'title_content': 'keyword_search_box',
		'title': 'keyword_search_box',
		'dose_dose_unit': 'dose_search_box',
		'dosing_route': 'route_search_box',
		'email_address': 'keyword_search_box',
		'user_id': 'keyword_search_box',
		'regdate': 'date_search_box',
		'last_update': 'date_search_box',
	}
	var available_boxes = ['keyword_search_box', 'dose_search_box', 'route_search_box', 'date_search_box'];
	var search_target = $('#blueberry_search_target').val();
	
	if(typeof search_target_box[search_target] !== "undefined" && search_target_box[search_target] != "") {
		available_boxes.forEach( function (item) {
			if (search_target_box[search_target] === item) {
				$('#' + item).show(0);
			} else {
				$('#' + item).hide(0);
			}
		});
	}
	return;
}
$(document).ready(update_input());