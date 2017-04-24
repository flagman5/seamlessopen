<?php

//https://github.com/kedarmhaswade/cities/blob/master/area-codes.csv

// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";


$data = file('area-codes.txt');
foreach($data as $row) {

        $array = explode(",", $row);
        $area_code = $array[0];
        $state = $array[1];

        $sql = "INSERT INTO area_codes_to_state (area_code, state) VALUES('".$area_code."','".$state."')";
        $conn->query($sql);

}
