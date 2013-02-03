<?php

//loader:  matches
//this loads matches from an XML file

//include DB

require("/home/bitnami/htdocs/teamsubmarino/SubmarinoWeb/inc/all.php");

$xml = parse_xml_file( '/home/bitnami/htdocs/teamsubmarino/SubmarinoData/files/xml/epl_injuries.xml' );

//print_r($xml);

foreach ( $xml->person as $person ) {
	foreach ( $person->injury as $injury ) {
		$q = "INSERT INTO player_injuries (ID, SEASON_PLAYER_ID, INJURY_TYPE_NAME,INJURY_CATEGORY_NAME,INCLUSIVE_BEGIN_DATE,EXCLUSIVE_END_DATE,EXPECTED_EXCL_END_DATE) VALUES (UUID(),(SELECT ID FROM SEASON_PLAYERS WHERE PLAYER_ID={$person['person_id']} LIMIT 1),'{$injury['type']}','','{$injury['start_date']}','{$injury['end_date']}','{$injury['expected_end_date']}')";
		//@TODO:  fix this query -- we can't tell without transfer data which of a possibly ambiguous set of season_players the injury referrs to.  We'll need transfer data or some other means to associate an injury to a season_player -- team ID and match_id are not available here.
		
		$DB->query($q);
		echo "processed injury for {$person['name']}";
	}
}

?>