jQuery(document).ready(function($) {
	var map = L.map('map').setView([39.74739, -98], 4);
	L.tileLayer('http://{s}.tile.cloudmade.com/c7c25cdb3bf544a8804f6f6432ea2d28/102525/256/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; 2013 OpenStreetMap contributors, Imagery &copy; 2013 CloudMade',
		key: 'c7c25cdb3bf544a8804f6f6432ea2d28'
	}).addTo(map);
		
	
	function onEachFeature(feature, layer) {
		var popupContent = "<h4><a href='" + feature.properties.link + "'>" + feature.properties.name + "</a></h4>";
		
		layer.bindPopup(popupContent, {maxWidth:200});	
	}	
	
	L.geoJson(locationMap, {
		pointToLayer: function (feature, latlng) {
			return L.marker(latlng);
		},
	
		onEachFeature: onEachFeature
	}).addTo(map);
});	