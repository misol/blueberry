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

/*
 * @ brief: Check the input value can be a part of number
*/
function isNumber(str) {
	str = String(str).trim();
	if (str == '' || isNaN(str)) {
		return false;
	}
	return true;
}

/*
 * @ brief: Return the parameters of an optimized line with least square method.
*/
function linearRegression(y, x) {
	var lr = {};
	var n = y.length;
	var sum_x = sum_y = sum_xy = sum_xx = sum_yy = 0;

	if ( y.length < 1 ) {
		return;
	}
	if ( y.length != x.length ) {
		alert ("X and Y should have the same length.");
		return;
	}

	for (var i = 0; i < y.length; i++) {

		sum_x += x[i];
		sum_y += y[i];
		sum_xy += (x[i]*y[i]);
		sum_xx += (x[i]*x[i]);
		sum_yy += (y[i]*y[i]);
	} 

	lr['slope'] = (n * sum_xy - sum_x * sum_y) / (n*sum_xx - sum_x * sum_x);
	lr['intercept'] = (sum_y - lr.slope * sum_x)/n;
	lr['r2'] = Math.pow((n*sum_xy - sum_x*sum_y)/Math.sqrt((n*sum_xx-sum_x*sum_x)*(n*sum_yy-sum_y*sum_y)),2);
	if(n > 2) {
		lr['adjusted_r2'] = 1-((1-(lr['r2']))*(n-1)/(n-2));
	}

	return lr;
}

/*
 * @ brief: Return an HTML code which would be adequate to present input data.
*/
function getVertiTableHtml(header, data, caption) {
	if( typeof header.row !== "undefined" ) {
		row_header = header.row
	}
	if( typeof header.column !== "undefined" ) {
		column_header = header.column
	}
	
	
	if ( row_header.length !== data.length ) {
		return;
	}
	
	var html = '<table>';
	
	if ( String(caption) != '') {
		html += '<caption>' + String(caption) + '</caption>';
	}
	
	if (typeof column_header !== "undefined") {
		if (column_header.length > 1) {
			html += '<thead><tr>';
			
			column_header.forEach(function(element) {
				html += '<th>' + element + '</th>';
			});
			
			html += '</tr></thead>';
		}
	}
	i = 0;
	row_header.forEach(function(element) {
		
		html += '<tr>';
		html += '<th>' + element + '</th>';
		
		data[i].forEach(function(cell) {
			html += '<td>' + cell + '</td>';
		});
		
		html += '</tr>';
		
		i++;
	});
	
	html += '</table>';
	
	return html
}

function isMultipleDose() {
	if (String($('#dose_repeat').val()).trim() == "multiple") {
		return true
	}
	else {
		return false
	}
}

function existTau() {
	if (String($('#dose_repeat').val()).trim() == "multiple") {
		if ((String($('#tau').val()).trim() != "") && (Number($('#tau').val()) > 0)) {
			return true
		}
	}
	return false
}

function getC0(time, lnC) {
	if (time.length != lnC.length) {
		return;
	}
	var C0 = 0;
	var dose_route = String($('#dose_route').val()).trim();
	
	if(dose_route == "EX" || dose_route == "IV_infusion"){
		if(existTau()) {
			C0 = Math.exp(Math.min(lnC));
		} else {
			C0 = 0;
		}
	} else if (dose_route == "IV_bolus") {
		time_slice = time.slice(0, 2);
		lnC_slice = lnC.slice(0, 2);
		
		if(time_slice[0] == 0) {
			if(lnC_slice[0] != 'NA') {
				C0 = Math.exp(lnC_slice[0]);
			} else {
				C0 = 0;
			}
		} else {
			if (lnC_slice.includes('NA')) {
				C0 = (lnC_slice[0] == 'NA')? lnC_slice[1]:lnC_slice[0];
				if( C0 == 'NA' ) {
					C0 = 0;
					lnC.forEach(function(lnC_item) {
							if (lnC_item != "NA" && C0 == 0) {
								C0 = Math.exp(lnC_item);
							}
						}
					);
				} else {
					C0 = Math.exp(C0);
				}
			} else {
				param = linearRegression(lnC_slice, time_slice);
				
				adj_r2 = param['adjusted_r2'];
				lambdaZ = param['slope'];
				intercept = param['intercept'];
				
				if (lambdaZ > 0) {
					C0 = Math.exp(lnC_slice[0]);
				} else {
					C0 = Math.exp(intercept);
				}
			}
		}
	}
	
	return {"C0": C0};
	
	/*
	var adj_r2 = 0;
	var lambdaZ = 0;
	var intercept = 0;
	var slice_i = 0;
	var n = time.length;
	var i = 0;
	while (i < n) {
		time_slice = time.slice(0, i);
		lnC_slice = lnC.slice(0, i);
		
		if (time_slice.length > 2) {
			param = linearRegression(lnC_slice, time_slice);
			
			if (adj_r2 < param['adjusted_r2']) {
				
				adj_r2 = param['adjusted_r2'];
				lambdaZ = param['slope'];
				intercept = param['intercept'];
				slice_i = i;
			}
			
		}
		i += 1;
	}
	return {"lnC0": intercept, "C0": Math.exp(intercept), "reg_points": time.slice(slice_i).length};
	*/
}

function getLambda(time, lnC) {
	if (time.length != lnC.length) {
		return;
	}
	
	var r2 = 0;
	var adj_r2 = 0;
	var lambdaZ = 0;
	var intercept = 0;
	var slice_i = 0;
	var n = time.length;
	var i = 0;
	while (i < n) {
		var time_slice = time.slice(i);
		var lnC_slice = lnC.slice(i);
		
		if (time_slice.length > 2) {
			var param = linearRegression(lnC_slice, time_slice);
			
			if (adj_r2 < param['adjusted_r2'] && param['slope'] < 0) {
				r2 = param['r2'];
				adj_r2 = param['adjusted_r2'];
				lambdaZ = param['slope'];
				intercept = param['intercept'];
				slice_i = i;
			}
			
		}
		i += 1;
	}
	return {"lambda_Z": lambdaZ, "intercept": intercept, "terminal_points": time.slice(slice_i).length, "adj_r2": adj_r2, "r2": r2};
}

function getExpFirstDeri_center(time, concentrations) {
	var sorted_T_C = sortTimeConcentration(time, concentrations)['time-concentration'];
	
	t1 = sorted_T_C[0][0];
	t2 = sorted_T_C[1][0];
	t3 = sorted_T_C[2][0];
	t4 = sorted_T_C[3][0];
	
	C1 = sorted_T_C[0][1];
	C2 = sorted_T_C[1][1];
	C3 = sorted_T_C[2][1];
	C4 = sorted_T_C[3][1];
	
	dfdt_center1 = (C2 - C1) / (t2 - t1);
	dfdt_center2 = (C4 - C3) / (t4 - t3);
	
	t_center1 = (t1 + t2) / 2;
	t_center2 = (t3 + t4) / 2;
	
	dfdt_2 = ((dfdt_center1) * ( t_center2 - t2 ) + (dfdt_center2) * ( t2 - t_center1 ) ) / ( t_center2 - t_center1 );
	dfdt_3 = ((dfdt_center1) * ( t_center2 - t3 ) + (dfdt_center2) * ( t3 - t_center1 ) ) / ( t_center2 - t_center1 );
	
	return [dfdt_2, dfdt_3];
}

function getAUC(time, concentrations, C0, lambda_z) {
	lambda_z = Math.abs(lambda_z);
	
	var integration_method = String($('#integration_method').val()).trim();
	var integration_methods = ["linear-trapezoidal", "logarithmic-trapezoidal", "linear-trapezoidal-with-end-correction"];
	if (!integration_methods.includes(integration_method)) {
		integration_method = "linear-trapezoidal";
	}
	
	var sorted_T_C = sortTimeConcentration(time, concentrations)['time-concentration'];
	
	
	if (sorted_T_C[0][0] != 0) {
		sorted_T_C = [[0, C0]].concat(sorted_T_C);
	}
	
	var i = 0;
	var AUC = AUCinf = 0;
	var time_before = time_this = conc_before = conc_this = 0;
	
	sorted_T_C.forEach(function(time_conc) {
		if (i != 0) {
			time_before = Number(sorted_T_C[i-1][0]);
			time_this = Number(sorted_T_C[i][0]);
			
			conc_before = Number(sorted_T_C[i-1][1]);
			conc_this = Number(sorted_T_C[i][1]);
			
			if (integration_method == "logarithmic-trapezoidal" && (conc_this !== 0 && conc_before !== 0) && conc_this != conc_before) {
				AUC += (time_this - time_before) * (conc_this - conc_before) / ( Math.log(conc_this / conc_before) );
			} else {
				AUC += (time_this - time_before) * (conc_this + conc_before) / 2;
				if (integration_method == "linear-trapezoidal-with-end-correction" && i != 1 && i < (sorted_T_C.length - 1)) {
					time_before_before = Number(sorted_T_C[i-2][0]);
					time_after = Number(sorted_T_C[i+1][0]);
					
					conc_before_before = Number(sorted_T_C[i-2][1]);
					conc_after = Number(sorted_T_C[i+1][1]);
					
					dfdt = getExpFirstDeri_center([time_before_before, time_before, time_this, time_after], [conc_before_before, conc_before, conc_this, conc_after]);

					AUC -=  (dfdt[1] - dfdt[0]) * Math.pow((time_this - time_before), 2) / 12;
				}
			}
		}
		i++;
	});
	conc_last = conc_this;
	
	AUCinf += AUC + conc_last/lambda_z;
	
	Extrapolation_portion = (conc_last/lambda_z)/AUCinf;
	
	return {"AUC": AUCinf, "AUClast": AUC, "Extrapolation_portion": Extrapolation_portion};
}

function getAUMC(time, concentrations, lambda_z) {
	lambda_z = Math.abs(lambda_z);
	var integration_method = String($('#integration_method').val()).trim();
	var integration_methods = ["linear-trapezoidal", "logarithmic-trapezoidal", "linear-trapezoidal-with-end-correction"];
	if (!integration_methods.includes(integration_method)) {
		integration_method = "linear-trapezoidal";
	}
	
	var sorted_T_C = sortTimeConcentration(time, concentrations)['time-concentration'];
	
	
	if (sorted_T_C[0][0] != 0) {
		sorted_T_C = [[0, 0]].concat(sorted_T_C);
	}
	
	var i = 0;
	var AUMC = AUMCinf = 0;
	var time_before = time_this = conc_before = conc_this = 0;
	
	sorted_T_C.forEach(function(time_conc) {
		if (i != 0) {
			time_before = Number(sorted_T_C[i-1][0]);
			time_this = Number(sorted_T_C[i][0]);
			
			conc_before = Number(sorted_T_C[i-1][1]);
			conc_this = Number(sorted_T_C[i][1]);
			
			if (integration_method == "logarithmic-trapezoidal" && (conc_this !== 0 && conc_before !== 0) && conc_this != conc_before && Math.log(conc_this / conc_before) !== 0) {
				AUMC += (((time_this - time_before) / (Math.log(conc_this / conc_before))) * (time_this * conc_this - time_before * conc_before)) - (Math.pow((time_this - time_before) / (Math.log(conc_this / conc_before)), 2) * (conc_this - conc_before))
			} else {
				AUMC += (time_this - time_before) * (time_this * conc_this + time_before * conc_before) / 2;
				
				
				if (integration_method == "linear-trapezoidal-with-end-correction" && i != 1 && i < (sorted_T_C.length - 1)) {
					time_before_before = Number(sorted_T_C[i-2][0]);
					time_after = Number(sorted_T_C[i+1][0]);
					
					conc_before_before = Number(sorted_T_C[i-2][1]);
					conc_after = Number(sorted_T_C[i+1][1]);
					
					dfdt = getExpFirstDeri_center([time_before_before, time_before, time_this, time_after], [time_before_before * conc_before_before, time_before * conc_before, time_this * conc_this, time_after * conc_after]);
					
					AUMC -=  (dfdt[1] - dfdt[0]) * Math.pow((time_this - time_before), 2) / 12;
				}
				
			}
		}
		i++;
	});
	
	time_last = time_this;
	conc_last = conc_this;
	
	AUMCinf = AUMC + conc_last / Math.pow(lambda_z, 2) + time_last * conc_last / lambda_z;
	
	Extrapolation_portion = (AUMCinf - AUMC) / AUMCinf;
	
	return {"AUMC": AUMCinf, "AUMClast": AUMC, "Extrapolation_portion": Extrapolation_portion};
	
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

function printResults( results ) {
	
}

function drawPlot(time, concentrations, lambda) {

	var time_unit = String($('#time_unit').val()).trim();
	var amount_unit = String($('#amount_unit').val()).trim().replace("ug", "μg");
	var volume_unit = String($('#volume_unit').val()).trim().replace("uL", "μL");
	
	
	var layout = {
		xaxis: {
			title: 'Time (' + time_unit + ')',
			rangemode: 'nonnegative',
			ticks: 'outside',
			showticklabels: true,
			showline: true,
			showgrid: false
		},
		yaxis: {
			title: 'Concentration (' + amount_unit + '/' + volume_unit + ')',
			ticks: 'outside',
			showticklabels: true,
			type: 'log',
			autorange: true,
			exponentformat: 'e',
			showexponent: 'all',
			showline: true,
			showgrid: false
		},
		
		font: {
			family: ' TimesNewRoman, Times New Roman, Times, Baskerville, Georgia, serif',
			size: 14,
			color: '#000'
		}
	};
	
	
	var settings = {
		toImageButtonOptions: {
			filename: 'moment_analysis',
			format: 'svg'
		}
	};
	
	
	var TC = {
		name: 'Observed',
		x: time,
		y: concentrations,
		mode: 'markers',
		type: 'scatter'
	};
	
	var lambda_times = time.slice(lambda.terminal_points * -1);
	var n = (Math.max(window.screen.availWidth, window.screen.availHeight)*20);
	var h = (lambda_times[lambda_times.length-1] - lambda_times[0])/n;
	
	var ex_times = [];
	var ex_conc = [];
	
	var i = 0;
	while (i < n) {
		var lamda_t = lambda_times[0] + h * i;
		var lamda_c = Math.exp(lambda.intercept) * Math.exp(lambda.lambda_Z * lamda_t);
		ex_times.push(lamda_t);
		ex_conc.push(lamda_c);
		
		i++;
	}
	
	var lambda_line = {
		name: 'Predicted',
		x: ex_times,
		y: ex_conc,
		mode: 'lines'
	};
	
	var data = [TC, lambda_line];
	Plotly.newPlot('data-plot', data, layout, {responsive: true});
	/*
	var time_unit = String($('#time_unit').val()).trim();
	var amount_unit = String($('#amount_unit').val()).trim().replace("ug", "μg");
	var volume_unit = String($('#volume_unit').val()).trim().replace("uL", "μL");
	
	Chart.defaults.global.defaultFontColor = '#000';
	Chart.defaults.global.defaultFontFamily = 'TimesNewRoman, Times New Roman, Times, Baskerville, Georgia, serif';
	Chart.defaults.global.defaultFontStyle = 14;
	var options = {
		scales: {
			xAxes: [{
				display: true,
				type: 'linear',
				position: 'bottom',
				min: 0,
				gridLines: {
					drawOnChartArea: false,
				}
			}],
			yAxes: [{
				display: true,
				type: 'logarithmic',
				position: 'left',
				gridLines: {
					drawOnChartArea: false,
				}
			}]
		},
		
		animation: {
			duration: 0 // general animation time
		},
		hover: {
			animationDuration: 0 // duration of animations when hovering an item
		},
		responsiveAnimationDuration: 0, // animation duration after a resize
		
		legend: {
			position: 'top',
		},
		title: {
			display: true,
			text: 'Time-Concentration'
		}
	};
	
	console.log(time);
	
	var TC = {
		label: 'Observed',
		data: []
	};
	
	time_lenth = time.length;
	for (var i = 0; i < time_lenth; i++) {
		TC.data.push({
			x: time[i],
			y: concentrations[i]
		});
	}
	
	console.log(TC);
	var lambda_times = time.slice(lambda.terminal_points * -1);
	var n = (Math.max(window.screen.availWidth, window.screen.availHeight)*20);
	var h = (lambda_times[lambda_times.length-1] - lambda_times[0])/n;
	
	var lambda_line = {
		label: 'Predicted',
		data: [],
		type: 'line',
		options: {
			pointRadius: 0,
			borderColor: '#FFA500',
			fill: false,
			lineTension: 0
		}
	}
	var i = 0;
	while (i < n) {
		var lamda_t = lambda_times[0] + h * i;
		var lamda_c = Math.exp(lambda.intercept) * Math.exp(lambda.lambda_Z * lamda_t);
		lambda_line.data.push({
			x: lamda_t,
			y: lamda_c
		});
		
		i++;
	}
	
	console.log(lambda_line);
	var ctx = document.getElementById('data-plot').getContext('2d');
	TCPlot = new Chart(ctx, {
		type: 'scatter',
		data: {
			datasets: [TC, lambda_line]
		},
		options: options
	});
	
	*/
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
	
	/* 계산값 생성 */
	
	var C0 = getC0(get('time'), get('lnC'))['C0'];
	var lambda_Z = getLambda(get('time'), get('lnC'));
	var t_half = Math.log(2) / lambda_Z.lambda_Z * -1;
	
	var AUC = getAUC(get('time'), get('concentration'), C0, lambda_Z.lambda_Z);
	var AUMC = getAUMC(get('time'), get('concentration'), lambda_Z.lambda_Z);
	var MRT = AUMC.AUMC/AUC.AUC;
	
	var CL = dose / AUC.AUC;
	var Vss = MRT * CL;
	
	/* 정리된 계산값 보여주기 */
	
	drawPlot(get('time'), get('concentration'), lambda_Z);
	
	
	$("#data-estimates").html(
		getVertiTableHtml(
			{
				'column': ['Parameter', 'Unit', 'Value'],
				'row': ['C<sub>0</sub>', 'λ<sub>Z</sub>', 'Number of points to estimate λ<sub>Z</sub>', 'R<sup>2</sup> (λ<sub>Z</sub>)', 'Adjusted R<sup>2</sup> (λ<sub>Z</sub>)', 't<sub>1/2</sub> (ln(2)/λ<sub>Z</sub>)', 'AUC', 'AUC<sub>last</sub>', '&#37; Extrapolated AUC', 'AUMC', 'AUMC<sub>last</sub>', '&#37; Extrapolated AUMC', 'MRT', 'CL', 'V<sub>SS</sub>']
				},
			[
				[amount_unit + '&frasl;' + volume_unit,											float_to_sig(C0, 4)									], 
				['&frasl;' + time_unit, 														float_to_sig(lambda_Z.lambda_Z * -1, 4)				],
				['', 																			float_to_sig(lambda_Z.terminal_points, 1)			],
				['', 																			float_to_sig(lambda_Z.r2, 4)						],
				['', 																			float_to_sig(lambda_Z.adj_r2, 4)					],
				[time_unit,																		float_to_sig(t_half, 4)								],
				[amount_unit + '&sdot;' + time_unit + '&frasl;' + volume_unit,					float_to_sig(AUC.AUC, 4)							],
				[amount_unit + '&sdot;' + time_unit + '&frasl;' + volume_unit,					float_to_sig(AUC.AUClast, 4)						],
				['&#37;',																		float_to_sig(AUC.Extrapolation_portion * 100, 4)	],
				[amount_unit + '&sdot;' + time_unit + '<sup>2</sup>' + '&frasl;' + volume_unit,	float_to_sig(AUMC.AUMC, 4)							],
				[amount_unit + '&sdot;' + time_unit + '<sup>2</sup>' + '&frasl;' + volume_unit,	float_to_sig(AUMC.AUMClast, 4)						],
				['&#37;',																		float_to_sig(AUMC.Extrapolation_portion * 100, 4)	],
				[time_unit,																		float_to_sig(MRT, 4)								],
				[volume_unit + '&frasl;' + time_unit + ((kg_normalized)? '&frasl;kg' : ''),		float_to_sig(CL, 4)									],
				[volume_unit + ((kg_normalized)? '&frasl;kg' : ''),								float_to_sig(Vss, 4)								],
			],
			'Estimates'
			));
	
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
	
	var dose_repeat = String($('#dose_repeat').val()).trim();
	
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
		'dose_unit': String($('#dose_unit').val()).trim(),
		'dosing_route': String($('#dose_route').val()).trim(),
		'dose_repeat': String($('#dose_repeat').val()).trim(),
		
		'last_dosing_time': Number($('#last_dosing_time').val()),
		'tau': Number($('#tau').val()),
		'integration_method': String($('#integration_method').val()).trim(),
		'TC_data': data_array
	};
	
	var response_tags = new Array('error','message','results');
	exec_xml('blueberry', 'procBlueberryInsertData', params, function(a,b) { alert(a.message); window.location.href = a.redirect_url; return; }, response_tags);
	
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