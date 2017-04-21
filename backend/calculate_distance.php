<?php

// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";


$data = file('zipcodes.txt');
foreach($data as $from_zipcode) {

   foreach($data as $to_zipcode) {

        if($to_zipcode == $from_zipcode) { continue; }

        $from_array = explode(",", $from_zipcode);
        $to_array = explode(",", $to_zipcode);

        $distance = distance($from_array[1], $from_array[2], $to_array[1], $to_array[2], 'M');

        if($distance <= 101 ) {
                //write it to db
                $sql = "INSERT INTO zip_code_distances (from_zip_code, to_zip_code, distance) VALUES ('".$from_array[0]."','".$to_array[0]."','$distance')";
                if ($conn->query($sql) === TRUE) {
                        //echo $from_array[0]." to ".$to_array[0]." created successfully \n";
                } else {
                         echo "Error: " . $sql . "<br>" . $conn->error;
                }
        }
   }

}

$conn->close();

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
      return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
  } else {
      return $miles;
  }
}
