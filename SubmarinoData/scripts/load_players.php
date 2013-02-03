<?php

//loader:  teams
//this loads teams from an XML file

//include DB

require("/home/bitnami/htdocs/teamsubmarino/SubmarinoWeb/inc/all.php");

$xml = parse_xml_file( '/home/bitnami/htdocs/teamsubmarino/SubmarinoData/files/xml/epl_players_2012.xml' );

foreach ($xml->team as $team) {
	//print_r($team);
	foreach ($team->person as $person) {
	
		if ($person['type'] == 'player') {
			
			$q = "INSERT INTO submarino.season_players (ID,PLAYER_ID, SEASON_START_YEAR, TEAM_ID, NAME, POSITION) VALUES (UUID(),{$person['person_id']},'2012',{$team['team_id']},'{$person['name']}','{$person['position']}')";
			
			if( $DB->query($q) ) {
				echo "loaded person {$person['name']}\n";
			}
		
		}
	
	}

	

		
}

?>