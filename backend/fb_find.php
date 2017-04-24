<?php
// Pass session data over.
session_start();
$realtor_search_array = array("realtor", "real estate agent", "real estate", "realty", "agent", "broker", "associate","owner", "team");

// Include the required dependencies.
require_once( 'vendor/autoload.php' );
include("db_config");

// Initialize the Facebook PHP SDK v5.
$fb = new Facebook\Facebook([
  'app_id'                => '',
  'app_secret'            => '',
  'default_graph_version' => 'v2.8',
]);

$token = json_decode(file_get_contents("https://graph.facebook.com/oauth/access_token?client_id=&client_secret=&grant_type=client_credentials"));
$fb->setDefaultAccessToken($token->access_token);

try {
  $response = $fb->get('/search?q=Maryalice+Ryan+real+estate&type=page');
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

//$output = $response->getGraphObject();
$output = $response->getGraphEdge();
$num_results = count($output);
foreach($output as $graphNode) {
        $page_id = $graphNode->getField('id');
        $name = $graphNode->getField('name');
        $company = array("Coldwell", "Banker", "Residential", "Brokerage", "Westfield", "East", "Office");
        $confidence = 0;
        if(contains($name, $company)) {
                $confidence += 5;
        }

        if(contains($name, $realtor_search_array)) {
                $confidence += 2;
        }

        $page_details = $fb->get('/'.$id.'?fields=about,phone,emails');
        $details = $page_details->getGraphObject();
        $emails = $details->getField('emails');
        $phone = $details->getField('phone');

        if($confidence >= 7 || $num_results == 1) {
                 echo $emails[0];
                 break;
        }
        else if($confidence == 2) {
                preg_match("/\((\d\d\d)\)", $phone, $matches);
                if(checkAreaCode($matches[1], $addressState)) {
                        echo $emails[0];
                        break;
                }

        }
}

//return

function contains($str, array $arr)
{
    foreach($arr as $a) {
        if (stripos($str,$a) !== false) return true;
    }
    return false;
}

function checkAreaCode($area_code, $state) {
  $conn = db_connect();
  $sql = "SELECT state FROM area_codes_to_state WHERE area_code = '".$area_code."'";
  $result = $conn->query($sql);
  if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      if($state == $row['state']) { return true; }
  }
  return false;
}
