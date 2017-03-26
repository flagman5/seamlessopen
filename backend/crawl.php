<?php
//need good cURL requests

//import area list
$areas = file("areas.txt");

foreach($areas as $area) {
  $url = "https://www.zillow.com/".$area."/open-house/";
  
  $result = do_curl($url);
  
  $delimiter = '<script type="application/ld+json">';
  $set = array_shift(explode($delimiter, $result));
  
  foreach($set as $house) {
    $house_info = explode("</script>", $house);
    $house_json_info = json_decode($house_info[0], TRUE);
    
    //write everything into a database
    //['endDate']
    //['location']['geo']['latitude']
    //['location']['geo']['longitude']
    //['location']['address']['streetAddress']
    //['location']['address']['postalCode']
    //['location']['address']['addressLocality']
    //['location']['address']['addressRegion']
    //['url']
  }
  
} 
