<?php

include("db_config.php");
include("crawl_homefinder.php");

//this script needs to run 2x daily wed, thurs, fri, sat, sun. On wed, create a new table and archive the old one
$today = date('N', time());

if($today == 3) {
	//need to archival and create new table
	$this_week_number = date('W', time()-(60*60*24*7));
	$old_table = "open_listings_".$this_week_number;
	$sql = "RENAME TABLE open_listings TO $old_table";
	$conn->query($sql);
	
	//create new
	$sql = 'CREATE TABLE IF NOT EXISTS `open_listings` (
			  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
			  `street` varchar(100) NOT NULL,
			  `city` varchar(80) NOT NULL,
			  `state` varchar(10) NOT NULL,
			  `zipcode` int(10) NOT NULL,
			  `agent` varchar(100) NOT NULL,
			  `agency` varchar(100) DEFAULT NULL,
			  `longitude` varchar(30) DEFAULT NULL,
			  `latitude` varchar(30) DEFAULT NULL,
			  `contact_email` varchar(100) DEFAULT NULL,
			  `crawl_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
	$conn->query($sql);
}

//now get all zipcodes in user profiles and current location reports
$sql = "SELECT distinct zipcode FROM users";
$result = $conn->query($sql);

if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		//for each zipcode
		$zipcode = $row['zipcode'];
		crawl_homefinder($zipcode);
		
		$sql = "SELECT to_zip_code FROM zip_code_distances WHERE from_zip_code = '".$zipcode."' AND distance <= 80";
		$nearby_zips = $conn->query($sql);
		while($zip_row = $nearby_zips->fetch_assoc()) {
			
			$nearby_zipcode = $zip_row['to_zip_code'];
			
			crawl_homefinder($nearby_zipcode);
		
		}
	}
}
