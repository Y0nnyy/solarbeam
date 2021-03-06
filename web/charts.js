var yearsChart = null;

getJSONFromUrl('api.php?q=yearly', updateYearly);
getJSONFromUrl('api.php?q=year&y=' + new Date().getFullYear(), updateYears);
getJSONFromUrl('api.php?q=w', updateWeek);

function updateYearsChartAll() {
	getJSONFromUrl('api.php?q=year', function(json) {
		yearsChart.destroy();
		
		var label = [];
		var labeljsn = json[1].data.labels;
		for(x in labeljsn){
			label.push(labeljsn[x]);
		}
		
		datasets = [];
		count = Object.keys(json).length;
		for(j in json) {
			var year = json[j].year;
			
			var data=[];
	
			var seriesjsn = json[j].data.series;
			for(y in seriesjsn) {
				data.push(seriesjsn[y]);
			}
			var dataset = {
				data: data,
				label: year,
				backgroundColor: 'rgba(' + Math.round((255/count * j)) + ',128,185 ,1)'
			}
			datasets.push(dataset);
		}
		
		var charData = {
		  type: 'bar',
		  data: {
				labels : label,
				datasets: datasets
			}
		}
		console.log(charData);
		var ctx = document.getElementById("yearsChart").getContext("2d");
	    yearsChart = new Chart(ctx, charData);
	});
}


function updateYearly(json) {
	
	getJSONFromUrl('api.php?q=yearlybezug', function(jsonbezug) {
			
		var labels = [], datalieferung=[], databezug=[], datagoal=[];
		
		//labels from bezug - are more
		for(x in jsonbezug.labels){
			labels.push(jsonbezug.labels[x]);
		}
		
		//series from lieferung
		for(y in json.series) {
			datalieferung[parseInt(y) + 6] = json.series[y];
		}
		
		//series from bezug
		for(y in jsonbezug.series) {
			databezug.push(jsonbezug.series[y]);
		}

		//series for goal line
		for(var i = 0; i < labels.length; i++) {
			datagoal.push(4081);
		}
		
		// Create the chart.js data structure using 'labels' and 'data'
		var charData = {
		  type: 'bar',
		  data: {
				labels : labels,
				datasets: [
				{
					label: 'Lieferung',
					data: datalieferung,
					backgroundColor: 'rgba(41,128,185 ,0.2)', 
					borderColor: 'rgba(41,128,185 ,1)',
					borderWidth: 1
				},
				{
					label: 'Bezug',
					data: databezug,
					backgroundColor: 'rgba(180,128,185 ,0.2)', 
					borderColor: 'rgba(180,128,185 ,1)',
					borderWidth: 1
				},
				{
					type: 'line',
					label: 'lokaler Durchschnitt',
					data: datagoal,
					fill: false
				}
				]
			}
		}
		
	  var ctx = document.getElementById("yearlyChart").getContext("2d");
	  var yearlyChart = new Chart(ctx, charData);
	});
	
}

function updateYears(json) {
	var labels = [], data=[];
	
    for(x in json.labels){
		labels.push(json.labels[x]);
    }
	for(y in json.series) {
		data.push(json.series[y]);
	}
	
	var datasoll = [106.11, 236.70, 346.89, 457.07, 510.13, 534.61, 514.21, 510.13, 391.78, 257.10, 122.43, 93.86];

    // Create the chart.js data structure using 'labels' and 'data'
    var charData = {
      type: 'bar',
	  data: {
			labels : labels,
			datasets: [
			{
				data: data,
				backgroundColor: [
						'rgba(26,188,156 ,0.2)',
						'rgba(22,160,133 ,0.2)',
						'rgba(241,196,15 ,0.2)',
						'rgba(243,156,18 ,0.2)',
						'rgba(46,204,113 ,0.2)',
						'rgba(39,174,96 ,0.2)',
						'rgba(230,126,34 ,0.2)',
						'rgba(211,84,0 ,0.2)',
						'rgba(52,152,219 ,0.2)',
						'rgba(41,128,185 ,0.2)',
						'rgba(231,76,60 ,0.2)',
						'rgba(192,57,43 ,0.2)'
					],
					borderColor: [
						'rgba(26,188,156 ,1)',
						'rgba(22,160,133 ,1)',
						'rgba(241,196,15 ,1)',
						'rgba(243,156,18 ,1)',
						'rgba(46,204,113 ,1)',
						'rgba(39,174,96 ,1)',
						'rgba(230,126,34 ,1)',
						'rgba(211,84,0 ,1)',
						'rgba(52,152,219 ,1)',
						'rgba(41,128,185 ,1)',
						'rgba(231,76,60 ,1)',
						'rgba(192,57,43 ,1)'
					],
					borderWidth: 1
			},
			{
				type: 'line',
				data: datasoll
			}
			]
		},
		options: {
			legend: {
				display: false
			}
		}
	}
	
  var ctx = document.getElementById("yearsChart").getContext("2d");
  if(yearsChart != null) yearsChart.destroy();
  yearsChart = new Chart(ctx, charData);

}

function updateWeek(json) {
	var labels = [], data=[];
	
    for(x in json.labels){
		labels.push(json.labels[x]);
    }
	for(y in json.series) {
		data.push(json.series[y]);
	}

    // Create the chart.js data structure using 'labels' and 'data'
    var charData = {
      type: 'bar',
	  data: {
			labels : labels,
			datasets: [
			{
				data: data,
				backgroundColor: [
						'rgba(26,188,156 ,0.2)',
						'rgba(22,160,133 ,0.2)',
						'rgba(241,196,15 ,0.2)',
						'rgba(243,156,18 ,0.2)',
						'rgba(46,204,113 ,0.2)',
						'rgba(39,174,96 ,0.2)',
						'rgba(230,126,34 ,0.2)',
						'rgba(211,84,0 ,0.2)',
						'rgba(52,152,219 ,0.2)',
						'rgba(41,128,185 ,0.2)',
						'rgba(231,76,60 ,0.2)',
						'rgba(192,57,43 ,0.2)'
					],
					borderColor: [
						'rgba(26,188,156 ,1)',
						'rgba(22,160,133 ,1)',
						'rgba(241,196,15 ,1)',
						'rgba(243,156,18 ,1)',
						'rgba(46,204,113 ,1)',
						'rgba(39,174,96 ,1)',
						'rgba(230,126,34 ,1)',
						'rgba(211,84,0 ,1)',
						'rgba(52,152,219 ,1)',
						'rgba(41,128,185 ,1)',
						'rgba(231,76,60 ,1)',
						'rgba(192,57,43 ,1)'
					],
					borderWidth: 1
			}
			]
		},
		options: {
			legend: {
				display: false
			},
			scales: {
				yAxes: [{
					display: true,
					ticks: {
						suggestedMin: 0,    // minimum will be 0, unless there is a lower value.
						// OR //
						beginAtZero: true   // minimum value will be 0.
					}
				}]
			}
		}
	}
	
  var ctx = document.getElementById("lastWeekChart").getContext("2d");
  var chart = new Chart(ctx, charData);
}
	