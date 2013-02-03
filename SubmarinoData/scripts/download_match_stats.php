<?php

//loader:  matches
//this loads matches from an XML file

//include DB

require("/home/bitnami/htdocs/teamsubmarino/SubmarinoWeb/inc/all.php");

$target_folder = '/home/bitnami/htdocs/teamsubmarino/SubmarinoData/files/xml/matches/';

$call_format = 'http://api.sportsdatallc.org/soccer-t1/get_match_extra?id=%d&api_key=jn3fzf79k8e45mhg6q4zug43';

$q_matches = "SELECT DISTINCT(ID) FROM match_stats";

$res = $DB->query( $q_matches );

while( $r = $res->fetch_assoc() ) {

	$call =  sprintf( $call_format, $r['ID']);
	$filepath = $target_folder . "/" . $r['ID'] . "-match.xml";
	
	$ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $call); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
 	$output = curl_exec($ch); 
	curl_close($ch); 
	
	if (file_put_contents($filepath, $output) !== FALSE) {
		echo "wrote $filepath\n";
	}else{
		echo "error. stopping\n";
	}
	
	usleep(100);
}
?>