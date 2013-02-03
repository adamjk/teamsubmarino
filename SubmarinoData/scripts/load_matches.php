<?php

//loader:  matches
//this loads matches from an XML file

//include DB

require("/home/bitnami/htdocs/teamsubmarino/SubmarinoWeb/inc/all.php");

$xml = parse_xml_file( '/home/bitnami/htdocs/teamsubmarino/SubmarinoData/files/xml/epl_matches_2012.xml' );

$fixtures = array();

foreach ($xml->competition as $competition) {
	foreach ($competition->season as $season) {
		foreach ($season->round as $round) {
			foreach ($round->match as $match) {
													
				$A = $match['team_A_name'];
				$B = $match['team_B_name'];
				
				update_fixtures($match, $A, $B);
								
				
				$team_a_fixture = $fixtures["$A"];
				$team_b_fixture = $fixtures["$B"];
				
				//assign points

				
				$team_a_points = ( $match['winner'] == 'team_A' ) ? 3 : 0;
				$team_b_points = ( $match['winner'] == 'team_B' ) ? 3 : 0;
				
				if ($match['winner'] == 'draw') {
					$team_a_points = 1;				
					$team_b_points = 1;
				}
				
				$q_a = "INSERT INTO submarino.match_stats (ID,TEAM_ID,SEASON_START_YEAR,GAME_DATE,FIXTURE_NUMBER,IS_HOME,WON_POINTS,GOALS) VALUES ({$match['match_id']},{$match['team_A_id']},2012,'{$match['date_london']}',{$team_a_fixture},1,{$team_a_points},{$match['fs_A']})";
				
				$q_b = "INSERT INTO submarino.match_stats (ID,TEAM_ID,SEASON_START_YEAR,GAME_DATE,FIXTURE_NUMBER,IS_HOME,WON_POINTS,GOALS) VALUES ({$match['match_id']},{$match['team_B_id']},2012,'{$match['date_london']}',{$team_b_fixture},0,{$team_b_points},{$match['fs_B']})";
						
				
				if ($DB->query( $q_a )) {
					echo("inserted team A fixture $team_a_fixture \n");
				}else{
					die("error inserting");
				}
				
				if ($DB->query( $q_b )) {
					echo("inserted team B fixture $team_b_fixture \n");
				}else{
					die("error inserting\n");
				}
			
			}
		}
	}
}



		


function update_fixtures($match, $A, $B) {
	global $fixtures;
	
	if ( !isset($fixtures["$A"]) ) {
		$fixtures["$A"] = 1;
	}else{
		$fixtures["$A"]++;
	}
	
	if ( !isset($fixtures["$B"]) ) {
		$fixtures["$B"] = 1;
	}else{
		$fixtures["$B"]++;
	}
}

?>