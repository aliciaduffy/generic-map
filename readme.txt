generic-map
===========

This is an example of how to map locations in WordPress using custom post types and leafletJS.

To use:

1) Replace API-KEY Placeholders

	- Google Maps API Key / generic-map.php line 172
		wp_register_script( 'googlemaps', 'http://maps.googleapis.com/maps/api/js?v=3&key=API-KEY&sensor=false' );
		
	- Cloudmade API Key, or other map tile reference / leaflet-config.js line 6
		L.tileLayer('http://{s}.tile.cloudmade.com/API-KEY/1/256/{z}/{x}/{y}.png'
		
2) Activate Plugin

3) Create a location, location symbol list is here: http://fontawesome.io/icons/ (remove 'fa-')