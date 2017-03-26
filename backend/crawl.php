<?php
//need good cURL requests
include('common.php');

//import area list
$areas = file("areas.txt");

foreach($areas as $area) {
  $url = "https://www.zillow.com/".$area."/open-house/";
  
  $result = do_curl($url, "http://www.google.com");
  
  $delimiter = '<script type="application/ld+json">';
  $set = array_shift(explode($delimiter, $result));
  
  $paginate_info = $set[count($set)-1];
  
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
  
  //now we do the paginate
  
} 
