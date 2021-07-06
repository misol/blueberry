/*
 * @ author: Min-Soo Kim
 * @ brief: a javascript file for NCA View
*/
console.log('Blueberry NCA Module Copyright (C) 2021 Min-Soo Kim at Seoul National University.');
function loadData (time_concentrations, lambda, time_unit, amount_unit, volume_unit) {
	$(document).ready(function() { drawPlot( time_concentrations, lambda, time_unit, amount_unit, volume_unit) });
}
function drawPlot(time_concentrations, lambda, time_unit, amount_unit, volume_unit) {
	var time = time_concentrations['time'];
	var concentrations = time_concentrations['concentration'];

	var layout = {
		xaxis: {
			title: 'Time (' + time_unit + ')',
			rangemode: 'nonnegative',
			ticks: 'outside',
			range: [0, 1.2*(Math.max(...time))],
			showticklabels: true,
			showline: true,
			showgrid: false,
			linecolor: (getColorScheme() === 'light')?'#000' : '#fff',
			tickcolor: (getColorScheme() === 'light')?'#000' : '#fff',
		},
		yaxis: {
			title: 'Concentration (' + amount_unit + '/' + volume_unit + ')',
			ticks: 'outside',
			showticklabels: true,
			type: 'log',
			range: [Math.floor(Math.log10(Math.min(...concentrations))), Math.ceil(Math.log10(Math.max(...concentrations)))],
			autorange: false,
			exponentformat: 'power',
			showexponent: 'all',
			showline: true,
			showgrid: false,
			linecolor: (getColorScheme() === 'light')?'#000' : '#fff',
			tickcolor: (getColorScheme() === 'light')?'#000' : '#fff',
		},
		autosize: true,
		margin: {
			t: 25,
			r: 20,
			b: 75,
			l: 75,
		},
		plot_bgcolor: (getColorScheme() === 'light')? "rgba(255,255,255,0)":"rgba(0,0,0,0)",
		paper_bgcolor: (getColorScheme() === 'light')? "rgba(255,255,255,0)":"rgba(0,0,0,0)",
		font: {
			size: 14,
			color: (getColorScheme() === 'light')?'#000' : '#fff',
		},
		showlegend: true,
		legend: {
			x: 1,
			xanchor: 'right',
			y: 1
		}
	};
	
	
	var settings = {
		responsive: true,
		displayModeBar: false
	};
	
	
	var TC = {
		name: 'Observed',
		x: time,
		y: concentrations,
		mode: 'markers',
		type: 'scatter',
		cliponaxis: false,
		marker: {
			color: (getColorScheme() === 'light')?'#000' : '#fff',
			size: 10,
		}
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
		mode: 'lines',
		cliponaxis: false,
		line: {
			color: '#9c27b0',
			width: 2
		}
	};
	
	var data = [TC, lambda_line];
	Plotly.newPlot('data-plot', data, layout, settings);
}

function onDeleteData(owner_id, data_srl) {
	$.ajax({
		url: request_uri+'index.php?mid=' + current_mid,
		type: 'POST',
		data: {
			module: 'blueberry',
			act: 'procBlueberryDeleteData',
			owner_id: String(owner_id).trim(),
			data_srl: Number(data_srl)
		},
		dataType: 'json',
		contentType: 'application/json'
	})
	.done(function (data) {
		if(!data.redirect_url) return console.log(data);
		
		window.location.href = data.redirect_url;
		return;
	})
	.fail(function (xhr) {
		// (error)
		console.log(xhr.responseText);
	});

}