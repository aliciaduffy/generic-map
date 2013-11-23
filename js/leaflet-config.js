jQuery(document).ready(function($) {
	/* Add start coordinates */
	var map = L.map('map').setView([36, -78], 7);
	
	/* Add API Key Placeholder */
	L.tileLayer('http://{s}.tile.cloudmade.com/API-KEY/1/256/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; 2013 OpenStreetMap contributors, Imagery &copy; 2013 CloudMade',
		key: 'API-KEY'
	}).addTo(map);
		
	/* Make a popup for each map marker */
	function onEachFeature(feature, layer) {
		var popupContent = "<h4><a href='" + feature.properties.link + "'>" + feature.properties.name + "</a></h4>";
		layer.bindPopup(popupContent, {maxWidth:200});			
	}
	
	/* Generate a random color for each icon */
	function randomColor() {
		var colors = Array('red', 'darkred', 'orange', 'green', 'darkgreen', 'blue', 'purple', 'darkpuple', 'cadetblue');
		var color = colors[Math.floor(Math.random()*colors.length)];
		return color;
	}
	
	/* Add to options marker color */
	L.geoJson(locationMap, {
		pointToLayer: function (feature, coordinates) {
			return L.marker(coordinates, {icon: L.AwesomeMarkers.icon({icon: feature.properties.symbol, prefix: 'fa', markerColor: randomColor(feature.properties.symbol)}) });
		},
		onEachFeature: onEachFeature
	}).addTo(map);
});	