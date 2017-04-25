<?php

include("common.php");

function google_geocode($street, $city, $state, $zipcode) {


	$addr_encoded = urlencode($street.' '.$city.' '.$state.' '.$zipcode);
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=$addr_encoded&key=YOUR_API_KEY";
	
	$result = json_decode(do_curl($url), true);
	
	$lat = $long = '';
	
	if($result['results'][0]['geometry']['location_type'] == 'ROOFTOP') {
	
		$lat = $result['results'][0]['geometry']['location']['lat'];
		$long = $result['results'][0]['geometry']['location']['long'];
	}
	
	return $lat.','.$long;

}


function google_reverse($geoloc) {

	$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$geoloc."&key=";
	
	$result = json_decode(do_curl($url), TRUE);
	$components = $result['results'][0]['address_components'];
	foreach($components as $comp) {
		if($comp['types'][0] == 'postal_code') {
			$zipcode = $comp['short_name'];
			return $zipcode;
		}
	}	

}
