<?php
//need good cURL requests
include('common.php');

//import area list
$areas = file("areas.txt");

foreach($areas as $area) {
  $url = "https://www.zillow.com/".$area."/open-house/";
  
  $result = do_curl($url, "http://www.google.com");
  crawl_result($result);
  
  //now we do the paginate
  $delim = '<li class="zsg-pagination-ellipsis"><span>...</span></li><li>';
  $max_page_and_pages = explode($delim, $paginate_info);
  
  $max_page_info = $max_page_and_pages[1];
  preg_match("/\>(\d)\<\/a\>/", $max_page_info, $match);
  $max = $match[0];
  
  for($i=2;$i<=$max;$i++) {
    $url = "https://www.zillow.com/".$area."/open-house/".$i."_p/".;
    $referrer = "https://www.zillow.com/".$area."/open-house/".$i-1."_p/".;
    $result = do_curl($url,$referrer);
    sleep(rand(3,15));
    crawl_result($result);
  }
  
}

function crawl_result($result) {
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
}
