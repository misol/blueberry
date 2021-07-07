/*
 * @ author: Min-Soo Kim
 * @ brief: a javascript file for NCA
*/
console.log('Blueberry NCA Module Copyright (C) 2021 Min-Soo Kim at Seoul National University.');

// https://developer.mozilla.org/ko/docs/Web/JavaScript/Reference/Global_Objects/Array/includes
if (!Array.prototype.includes) {
	Array.prototype.includes = function(searchElement, fromIndex) {

	if (this == null) {
		throw new TypeError('"this" is null or not defined');
	}

	// 1. Let O be ? ToObject(this value).
	var o = Object(this);

	// 2. Let len be ? ToLength(? Get(O, "length")).
	var len = o.length >>> 0;

	// 3. If len is 0, return false.
	if (len === 0) {
		return false;
	}

	// 4. Let n be ? ToInteger(fromIndex).
	//    (If fromIndex is undefined, this step produces the value 0.)
	var n = fromIndex | 0;

	// 5. If n ≥ 0, then
	//  a. Let k be n.
	// 6. Else n < 0,
	//  a. Let k be len + n.
	//  b. If k < 0, let k be 0.
	var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

	function sameValueZero(x, y) {
		return x === y || (typeof x === 'number' && typeof y === 'number' && isNaN(x) && isNaN(y));
	}

	// 7. Repeat, while k < len
	while (k < len) {
		// a. Let elementK be the result of ? Get(O, ! ToString(k)).
		// b. If SameValueZero(searchElement, elementK) is true, return true.
		if (sameValueZero(o[k], searchElement)) {
			return true;
		}
		// c. Increase k by 1. 
		k++;
	}

	// 8. Return false
	return false;
}
}

// https://developer.mozilla.org/ko/docs/Web/JavaScript/Reference/Global_Objects/String/includes
if (!String.prototype.includes) {
	String.prototype.includes = function(search, start) {
		'use strict';
	if (typeof start !== 'number') {
		start = 0;
	}

	if (start + search.length > this.length) {
		return false;
	} else {
		return this.indexOf(search, start) !== -1;
	}
	};
}

/*
 * @ brief: Round number in the designated significant number.
 * @ param:
		number a: the object number to be rounded;
		integer sig_figs: significant number.
 * @ return: number
*/
function float_to_sig(a, sig_figs) {
	sig_figs = (typeof sig_figs !== 'undefined') ?  sig_figs : 3;
	
	return parseFloat(a).toPrecision(sig_figs);
}


/*
 * @ brief: variable container to transact between functions.
*/
var __variables = {};
/*
 * @ brief: Set variable.
*/
function set(name, value) {
	__variables[name] = value;
}
/*
 * @ brief: Append the value to the variable with "name". If the variable is not an array, create an array containing the value.
*/
function append(name, value) {
	if (typeof(__variables[name]) !== "undefined") {
		if (Array.isArray(__variables[name])) {
			__variables[name].push(value)
			return true;
		}
		else {
			return false;
		}
	}
	else {
		__variables[name] = []
		__variables[name].push(value)
		return true;
	}
}

/*
 * @ brief: Return the value of the variable with the "name".
*/
function get(name) {
	if (typeof(__variables[name]) !== "undefined") {
		return __variables[name];
	}
	else {
		return false;
	}
}

/*
 * @ brief: Delete the variable with the "name".
*/
function unset(name) {
	if (typeof(__variables[name]) === "undefined") {
		return false;
	}
	return delete __variables[name];
}


function unitConversion(value, unit_type, org_unit, target_unit) {
	var unit_types = ['amount', 'time', '1/time', 'volume', 'concentration'];
	if (unit_types.includes(unit_type) == false) {
		return;
	}
	org_unit = org_unit.replace(/^g/, '1g').replace(/^L/, '1L').replace(/^mol/, '1mol').replace(/^IU/, '1IU');
	target_unit = target_unit.replace(/^g/, '1g').replace(/^L/, '1L').replace(/^mol/, '1mol').replace(/^IU/, '1IU');
	
	var SI_prefixes = {
		'Y': 1e24,
		'Z': 1e21,
		'E': 1e18,
		'P': 1e15,
		'T': 1e12,
		'G': 1e9,
		'M': 1e6,
		'k': 1e3,
		'h': 1e2,
		'1': 1e0,
		'd': 1e-1,
		'c': 1e-2,
		'm': 1e-3,
		'u': 1e-6,
		'μ': 1e-6,
		'n': 1e-9,
		'p': 1e-12,
		'f': 1e-15,
		'a': 1e-18,
		'z': 1e-21,
		'y': 1e-24
	};
	
	var time_units_eq = {
		's': 604800,
		'min': 10080,
		'h': 168,
		'd': 7,
		'week': 1
	};
	
	if (unit_type == 'time') {
		if ((org_unit in time_units_eq) && (target_unit in time_units_eq)) {
			return value * (time_units_eq[target_unit]/time_units_eq[org_unit]);
		}
	} else if (unit_type == '1/time') {
		if ((org_unit.replace("/","") in time_units_eq) && (target_unit.replace("/","") in time_units_eq)) {
			return value * (time_units_eq[org_unit]/time_units_eq[target_unit]);
		}
	} else if (unit_type == 'amount' || unit_type == 'volume') {
		if( org_unit.slice(1) !== target_unit.slice(1) ){
			console.log('Unit conversion: unit not matched.');
			console.log(org_unit.slice(1) + ' to ' + target_unit.slice(1));
			return 'Unit not matched.';
		}
		if ((org_unit.slice(0, 1) in SI_prefixes) && (target_unit.slice(0, 1) in SI_prefixes)) {
			return value * (SI_prefixes[org_unit.slice(0, 1)]/SI_prefixes[target_unit.slice(0, 1)]);
		}
	} else if (unit_type == 'concentration') {
		var org_unit = org_unit.split("/");
		var target_unit = target_unit.split("/");
		
		if ((org_unit[0].slice(0, 1) in SI_prefixes) && (org_unit[1].slice(0, 1) in SI_prefixes) && (target_unit[0].slice(0, 1) in SI_prefixes) && (target_unit[1].slice(0, 1) in SI_prefixes)) {
			return value * (SI_prefixes[org_unit[0].slice(0, 1)]/SI_prefixes[target_unit[0].slice(0, 1)]) * (SI_prefixes[target_unit[1].slice(0, 1)]/SI_prefixes[org_unit[1].slice(0, 1)]);
		}
	}
	
	console.log('Unit conversion: unpredicted error.');
	return;
	
}

function sortTimeConcentration(time, concentrations) {
	var mapped = time.map(function(el, i) { return { index: i, value: Number(el) };	});

	// 축소 치를 포함한 매핑 된 배열의 소트
	mapped.sort(function(a, b) {
		return (a.value - b.value);
	});

	/*  */
	var sorted_time = mapped.map(function(el){ return time[el.index]; });
	var sorted_concentrations = mapped.map(function(el){ return concentrations[el.index]; });
	var sorted_lnC = mapped.map(function(el){ if(concentrations[el.index] > 0) { return Math.log(concentrations[el.index]); } else { return 'NA'; } });
	var sorted_T_C = mapped.map(function(el){ return [time[el.index], concentrations[el.index]]; });
	
	return {"time": sorted_time, "concentration": sorted_concentrations, "lnC": sorted_lnC, "time-concentration":sorted_T_C}
}



function trimTCdata (raw_data) {
	
	
	var times = [];
	var concentrations = [];
	var i = 0
	raw_data.forEach(function(raw_row) {
		if (String(raw_row[0]) != "" && String(raw_row[1]) != "") {
			time = Number(raw_row[0]);
			concentration = Number(raw_row[1]);
			
			if (!isNaN (time) && !isNaN (concentration)) {
				times.push(time);
				concentrations.push(concentration);
			}
		}
		
		i++;
	});
	
	if (times.length < 1) {
		return;
	}
	
	var data_array = sortTimeConcentration(times, concentrations);
	return data_array;
}

function updateTCtable( is_arrange ) {
	is_arrange = (typeof is_arrange !== 'undefined') ?  is_arrange : true;
	
	/* 변수 초기화 */
	unset('time');
	unset('concentration');
	unset('lnC');
	unset('time-concentration');
	var raw_data = TCtable.getData(false);
	var time_unit = String($('#time_unit').val()).trim();
	var amount_unit = String($('#amount_unit').val()).trim().replace("ug", "μg");
	var volume_unit = String($('#volume_unit').val()).trim().replace("uL", "μL");
	
	var dose = Number($('#dose').val());
	var dose_unit = String($('#dose_unit').val());
	var kg_normalized = false;
	
	
	if (dose_unit.indexOf('/') > 0) {
		kg_normalized = true;
		dose_unit = dose_unit.substr(0, dose_unit.indexOf('/'));
	}
	
	if (dose_unit.indexOf('mol')>=0 || amount_unit.indexOf('mol')>=0) {
		$("#molecular_weight_section").show(300);
	} else {
		$("#molecular_weight_section").hide(300);
	}
	
	dose = unitConversion (dose, "amount", dose_unit, amount_unit);
	if (dose == 'Unit not matched.' ) {
		$('#system_message').html(' Unit of dosing and measurement should be matched. e.g., IU to IU, mol to mol, and gram to gram. ');
		return;
	}
	else {
		$('#system_message').html('');
	}
	
	
	var data_array = trimTCdata (raw_data);
	
	if(!data_array) return;
	
	set('time', data_array.time);
	set('concentration', data_array.concentration);
	set('lnC', data_array.lnC);
	set('time-concentration', data_array['time-concentration']);
	
	
	if (is_arrange !== false) {
		/* 정리된 입력값 보여주기 */
		TCtable.setData(get('time-concentration'));
	}
}

function linspace(start, stop, step) {
	if (typeof step === "undefined") {
		step = 1;
	}
	
	var output_arr = [];
	var i = Number(start);
	stop = Number(stop);
	
	while (i < stop) {
		output_arr.push(i);
		i += step;
	}
	
	return output_arr;
}


function drawLambdaZPlot (t_range, intercept, lambdaZ) {
	t_range = linspace(t_range[0], t_range[1], 0.01);
	
	intercept = Math.exp(intercept);
	
	var output_arr = [];
	
	//t_range.forEach(
	
	
}

function procUpdateSheet() {
	var time_unit = String($('#time_unit').val()).trim();
	var amount_unit = String($('#amount_unit').val()).trim().replace("ug", "μg");
	var volume_unit = String($('#volume_unit').val()).trim().replace("uL", "μL");
	
	
	var dose_route = String($('#dose_route').val()).trim();
	var dose_repeat = String($('#dose_repeat').val()).trim();
	
	if (dose_route == "IVeIf") {
		$("#iv_infusion").show(300);
	} else {
		$("#iv_infusion").hide(300);
	}
	
	if (dose_repeat == "multiple") {
		$("#multiple_dosing").show(300);
	} else {
		$("#multiple_dosing").hide(300);
	}


	TCtable.setHeader(0, 'Time (' + String(time_unit) + ')');
	TCtable.setHeader(1, 'Concentration (' + String(amount_unit) + '/' + String(volume_unit) + ')');
	updateTCtable( false );
}

var triggerTimer_UpdateSheet = 0, triggerTimerID_UpdateSheet;
function triggerUpdateSheet(instance, cell, x, y, value) {
	if (triggerTimer_UpdateSheet > 0) {
		window.clearTimeout(triggerTimerID_UpdateSheet);
	}
	triggerTimerID_UpdateSheet = window.setTimeout(function() { updateTCtable( false ); triggerTimer_UpdateSheet = 0}, 300);
}

function saveData () {
	var raw_data = TCtable.getData(false);
	var data_array = trimTCdata (raw_data);
	if(!data_array) {
		alert('Time-concentration table have no data.');
		return;
	}
	var dose_unit = String($('#dose_unit').val()).trim();
	var amount_unit = String($('#amount_unit').val()).trim();
	var molecular_weight = Number($('#molecular_weight').val());
	var dose = Number($('#dose').val());
	
	if ((dose_unit.indexOf('mol')>=0 || amount_unit.indexOf('mol')>=0) && !molecular_weight) {
		alert('Molecular weight is required.');
		return;
	}
	
	if (!dose) {
		alert('Dose is required.');
		return;
	}
	
	var params = {
		'mid': String($('input[name=mid]').val()).trim(),
		'act': String($('input[name=act]').val()).trim(),
		'data_srl': Number($('input[name=data_srl]').val()),
		
		'title': String($('#title_text').val()).trim(),
		'content': String($('#content_area').val()).trim(),
		'time_unit': String($('#time_unit').val()).trim(),
		'amount_unit': String($('#amount_unit').val()).trim(),
		'volume_unit': String($('#volume_unit').val()).trim(),
		
		'dose': Number($('#dose').val()),
		'molecular_weight': Number($('#molecular_weight').val()),
		'dose_unit': String($('#dose_unit').val()).trim(),
		'administration_route': String($('#dose_route').val()).trim(),
		'duration_of_infusion': Number($('#duration_of_infusion').val()),
		'dose_repeat': String($('#dose_repeat').val()).trim(),
		
		'last_dosing': Number($('#last_dosing_time').val()),
		'tau': Number($('#tau').val()),
		'integration_method': String($('#integration_method').val()).trim(),
		'TC_data': data_array
	};
	
	var response_tags = new Array('error','message','results');
	exec_xml('blueberry', (params['data_srl'])?'procBlueberryUpdateData':'procBlueberryInsertData', params, function(a,b) { window.location.href = a.redirect_url; return; }, response_tags);
	
}




function detectIE() {
	var ua = window.navigator.userAgent;

	var msie = ua.indexOf('MSIE ');
	if (msie > 0) {
		// IE 10 or older => return version number
		return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
	}

	var trident = ua.indexOf('Trident/');
	if (trident > 0) {
		// IE 11 => return version number
		var rv = ua.indexOf('rv:');
		return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
	}

	var edge = ua.indexOf('Edge/');
	if (edge > 0) {
		// Edge (IE 12+) => return version number
		return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
	}

	// other browser
	return false;
}
var IE_version = detectIE();
if( IE_version !== false ) {
	if (IE_version < 11) {
		alert('This webpage may not support your browser.\nPlease update your web browser, or use another one\n(Edge, FireFox, Chrome, etc).');
	}
}