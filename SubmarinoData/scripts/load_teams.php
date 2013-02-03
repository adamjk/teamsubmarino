<?php

//loader:  teams
//this loads teams from an XML file

//include DB

require("/home/bitnami/htdocs/teamsubmarino/SubmarinoWeb/inc/all.php");

$xml = parse_xml_file( '/home/bitnami/htdocs/teamsubmarino/SubmarinoData/files/xml/epl_teams_2012.xml' );

foreach ($xml->team as $team) {

	$q = "INSERT INTO submarino.teams (ID,OFFICIAL_NAME,LEAGUE_NAME) values ({$team['team_id']},'{$team['club_name']}','EPL')";
	if( $DB->query($q) ) {
		echo "loaded team\n";
	}
		
}

?>