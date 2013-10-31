jQuery(document).ready(function($) {
	/* Geocode college's location based on ZIP */				
	jQuery.fn.codeAddress = function () {
		var geocoder;
		geocoder = new google.maps.Geocoder();
		var zip = $('input[name="zip_code"]').attr('value');
		if(zip != '') {
			geocoder.geocode( { 'address': zip}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var coordinates = results[0].geometry.location.lng() + ", " + results[0].geometry.location.lat();
					$('input[name="coordinates"]').attr('value',coordinates);
				} else {
					alert("Geocode was not successful for the following reason: " + status);
				}
			});
		}
	}
	$(document).codeAddress();
	
	var typingTimer;
	var doneTypingInterval = 1000; 
	
	/* Live geocode if the city or zip code are  entered or changed */
	$("input#zip_code").keyup(function(){
		typingTimer = setTimeout(function() {
			$(document).codeAddress();
			console.log('taco');
	  	}, doneTypingInterval);
	});
	//on keydown, clear the countdown 
	$("input#zip_code").keydown(function(){
	    clearTimeout(typingTimer);
	});	
});