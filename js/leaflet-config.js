jQuery(document).ready(function($) {
	/* Add start coordinates */
	var map = L.map('map').setView([36, -80], 6);
	
	/* I want to inline this JS and add to options page - Cloudmade API Key, Cloudmade Map ID */
	L.tileLayer('http://{s}.tile.cloudmade.com/c7c25cdb3bf544a8804f6f6432ea2d28/1/256/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; 2013 OpenStreetMap contributors, Imagery &copy; 2013 CloudMade',
		key: 'c7c25cdb3bf544a8804f6f6432ea2d28'
	}).addTo(map);
		
	
	function onEachFeature(feature, layer) {
		var popupContent = "<h4><a href='" + feature.properties.link + "'>" + feature.properties.name + "</a></h4>";
		layer.bindPopup(popupContent, {maxWidth:200});	
	}	
	
	/* add to options marker color */
	L.geoJson(locationMap, {
		pointToLayer: function (feature, coordinates) {
			return L.marker(coordinates, {icon: L.AwesomeMarkers.icon({icon: feature.properties.symbol, prefix: 'fa', markerColor: 'orange'}) });
		},
		onEachFeature: onEachFeature
	}).addTo(map);
});	