<?php
include("common.php");

//requires the zipcode
function crawl_homefinder($zip) {
  
  $url = "http://www.homefinder.com/zip-code/".$zip."/open-house/";
  
  $page = 1;
  do {
     $result = do_curl($url, "http://www.google.com");
     $data_array = explode('<div id="leftColumn">', $result);
     
     if($page == 1) {
       preg_match('/page\"\>\sPage\s1\sof\s(\d+)\s/', $data_array[1], $matches);
       $total_page = $matches[1];
     }
  
     parse_results($data_array[1]);
    
     $page++;
     $url = "http://www.homefinder.com/zip-code/".$zip."/open-house/?page=".$page;
  } while($total_page <= $page);
  
  
  

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
    
  } 
}
