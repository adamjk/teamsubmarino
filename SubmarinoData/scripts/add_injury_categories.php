<?php

require("/home/bitnami/htdocs/teamsubmarino/SubmarinoWeb/inc/all.php");

$q = "select * from injury_types";
$res = $DB->query($q);

$injury_lookup = array();
while( $row = $res->fetch_assoc() ) {
	$injury_lookup["{$row['injury_type_name']}"] = $row['injury_category'];
}

$q_injuries = "SELECT * FROM player_injuries";
$i_res = $DB->query($q_injuries);

while ( $row = $i_res->fetch_assoc() ) {
	
	$cat = $injury_lookup["{$row['INJURY_TYPE_NAME']}"];
	$q = "UPDATE player_injuries SET `INJURY_CATEGORY_NAME`='$cat' WHERE ID='{$row['ID']}'";
	$DB->query($q);
}

//print_r($injury_lookup);

?>