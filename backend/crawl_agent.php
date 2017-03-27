<?php
include('common.php');

//import area list
$areas = file("areas.txt");

foreach($areas as $area) {
  
  //need DB to get list of URLs to go
  //$db_result = mysql_query("SELECT url FROM openhouses WHERE agent_number is null AND area = $area");
  
  foreach($db_result as $url) {
    $result = do_curl($url, "http://www.google.com");
    
    $delimiter = 'class="profile-name-link">';
    $agent_info = explode($delimiter, $result);
    
    preg_match("/(.*)\<\/a\>\<\/span\>/", $agent_info[1], $match);
    $agent_name = $match[0];
    preg_match("/\<a\shref=\"(.*)\"\sclass/",  $agent_info[1], $match);
    $agent_profile = explode("/", $match[0]);
    $agent_profile_name = $agent_profile[2];
    
    sleep(rand(1,5));
    $agent_url = "https://www.zillow.com/profile/".$agent_profile_name."/";
    $result = do_curl($agent_url, $url);
    
    preg_match("/\<a\srel=\"nofollow\"\shref=\"(.*)\"\starget=\"_blank\"\>Website\<\/a\>/", $result, $match);
    $agent_own_url = $match[0];
    
    $result = do_curl($agent_own_url, "http://www.google.com");
    //here come the hard part
    
    
  }
}
