/*
 * @ author: Min-Soo Kim
 * @ brief: a javascript file for NCA View
*/
console.log('Blueberry NCA Module Copyright (C) 2021 Min-Soo Kim at Seoul National University.');

function drawPlot(time, concentrations, lambda) {

	var time_unit = String($('#time_unit').val()).trim();
	var amount_unit = String($('#amount_unit').val()).trim().replace("ug", "μg");
	var volume_unit = String($('#volume_unit').val()).trim().replace("uL", "μL");
	
	console.log([...Array(Math.ceil(Math.log10(Math.max(...concentrations))) - Math.floor(Math.log10(Math.min(...concentrations))) + 1).keys()]);
	var layout = {
		xaxis: {
			title: 'Time (' + time_unit + ')',
			rangemode: 'nonnegative',
			ticks: 'outside',
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
		
		plot_bgcolor: (getColorScheme() === 'light')? "rgba(255,255,255,0)":"rgba(0,0,0,0)",
		paper_bgcolor: (getColorScheme() === 'light')? "rgba(255,255,255,0)":"rgba(0,0,0,0)",
		font: {
			size: 14,
			color: (getColorScheme() === 'light')?'#000' : '#fff',
		}
	};
	
	
	var settings = {
		/*
		toImageButtonOptions: {
			filename: 'moment_analysis',
			format: 'svg'
		},
		modeBarButtonsToRemove: ['pan2d','select2d','lasso2d','resetScale2d','zoomOut2d'],*/
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