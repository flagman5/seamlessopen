<?php

//import area list
$areas = file("areas.txt");

foreach($areas as $area) {
  //need DB to get list of URLs to go
  //$db_result = mysql_query("SELECT url FROM openhouses WHERE agent_number is null AND area = $area");
  
  foreach($db_result as $url) {
    do {
    //construct the cURL
    $curl = curl_init();
    $config['useragent'] = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';
    curl_setopt($curl, CURLOPT_USERAGENT, $config['useragent']);
    curl_setopt($curl, CURLOPT_REFERER, 'https://www.google.com/');
    $dir = dirname(__FILE__);
    $config['cookie_file'] = $dir . '/cookies/' . md5($_SERVER['REMOTE_ADDR']) . '.txt';
    curl_setopt($curl, CURLOPT_COOKIEFILE, $config['cookie_file']);
    curl_setopt($curl, CURLOPT_COOKIEJAR, $config['cookie_file']);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_URL,$url);
    $result = curl_exec($curl);
    } while(empty($result));
    
    $delimiter = 'class="profile-name-link"';
    $agent_url = 
    
  }
}
