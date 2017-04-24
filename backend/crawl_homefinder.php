<?php
include("db_config.php");
include("common.php");
include("google_geocode.php");

//requires the zipcode
function crawl_homefinder($zip) {
  
  $url = "http://www.homefinder.com/zip-code/".$zip."/open-house/";
  
  $page = 1;
  $total_page = 0;
  do {
     $result = do_curl($url, "http://www.google.com");
     $data_array = explode('<div id="leftColumn">', $result);
     
    if(strpos($data_array[1],'noResultsMessage') !== false) {
       if($page == 1) {
         preg_match('/page\"\>\sPage\s1\sof\s(\d+)\s/', $data_array[1], $matches);
         $total_page = $matches[1];
       }
       parse_results($data_array[1]);
     }
    
     $page++;
     $url = "http://www.homefinder.com/zip-code/".$zip."/open-house/?page=".$page;
  } while($total_page >= $page);
  
  
  

}

function parse_results($result) {
 
  $listing_array = explode('<div class="resultsBands last" itemscope itemtype="http://schema.org/SingleFamilyResidence">', $result]);
  
  foreach($listings_array as $listing) {
    $parts = explode("<meta itemprop=", $listing);
    
    preg_match("/streetAddress\"\scontent=\"(.*)\"/", $parts[1], $matches);
    $street = $matches[1];
    preg_match("/addressLocality\"\scontent=\"(.*)\"/", $parts[2], $matches);
    $city = $matches[1];
    preg_match("/addressRegion\"\scontent=\"(.*)\"/", $parts[3], $matches);
    $state = $matches[1];
    preg_match("/postalCode\"\scontent=\"(.*)\"/", $parts[4], $matches);
    $zipcode = $matches[1];
    preg_match("/member\">\s(.*)<\/div>\s<p/", $parts[5], $matches);
    $agent = $matches[1];
    preg_match("/brand\">\s(.*)<\/div>\s<div/", $parts[5], $matches);
    $agency = $matches[1];
	
	//check if exists
	$sql = "SELECT * FROM open_listings WHERE street='".$street."' and city='".$city."' and state='".$state." and zipcode='".$zipcode."'";
	$result = $conn->query($sql);
	if($result->num_rows == 0) {
		//call google to get geocoordinates
		$coordinates = explode(",", google_geocode($street, $city, $state, $zipcode));
	
		//now call FB to get the agent email
		$contact_email = fb_find($agent, $agency);
	
		//insert into DB 
		$sql = "INSERT INTO open_listings (street, city, state, zipcode, agent, agency, longitude, latitude, contact_email)
			VALUES('".$street."','".$city."','".$state."','".$zipcode."','".$agent."','".$agency."','".$coordinates[1]."','".$coordinates[0]."','".$contact_email."')";
			
		$conn->query($sql);
	}
  } 
}
