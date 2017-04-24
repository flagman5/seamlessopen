<?php

include("common.php");

function google_geocode($street, $city, $state, $zipcode) {


	$addr_encoded = urlencode($street.' '.$city.' '.$state.' '.$zipcode);
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=$addr_encoded&key=YOUR_API_KEY";
	
	$result = json_decode(do_curl($url), true);
	
	$lat = $result['results'][0]['geometry']['location']['lat'];
	$long = $result['results'][0]['geometry']['location']['long'];
	
	return $lat.','.$long;

}


function google_reverse($lat, $lon) {


}
