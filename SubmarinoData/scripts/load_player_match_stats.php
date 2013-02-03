<?php

//loader:  matches
//this loads matches from an XML file

//include DB

require("/home/bitnami/htdocs/teamsubmarino/SubmarinoWeb/inc/all.php");

$target_folder = '/home/bitnami/htdocs/teamsubmarino/SubmarinoData/files/xml/matches/';

//get the list of matches
$q_matches = "SELECT DISTINCT(ID) FROM match_stats";
$res = $DB->query( $q_matches );

//build match ID list
while( $r = $res->fetch_assoc() ) {

	$filepath = $target_folder . "/" . $r['ID'] . "-match.xml";
		
	player_stats( $filepath );
}

function player_stats( $file ) {
	
	$xml = parse_xml_file( $file );
	
	foreach ($xml->competition as $c) {
		foreach ($c->season as $s) {
			foreach ($s->round as $r) {
				foreach ($r->match as $m) {
					$match_stats = array();
					foreach ($m->goals as $goal) {
						foreach ($goal->goal as $event_g) {
							foreach( $event_g as $eg ) {
								$match_stats[] = $eg[0];							
							}
						}
					}
					
					foreach ($m->bookings as $booking) {
						foreach($booking->event as $eb ) {
							$match_stats[] = $eb[0];								
						}
					}
					
					process_match_stats($match_stats, $m[0]['match_id']);
														
				}
			}
		}
	}
}

function process_match_stats($ms, $mid) {

	global $DB;

	if( empty($ms) ) {
		return false;
	}
		
	$players = array();
	
	$s = array('GOALS'=>0, 'ASSISTS'=>0, 'YELLOW_CARDS'=>0, 'RED_CARDS'=>0, 'TEAM_ID'=>'');
	
	foreach($ms as $stat) {
		if (!isset($players["{$stat['person_id']}"])) {
			$players["{$stat['person_id']}"] = $s;
		}
			switch ($stat['code']) {
				case "G":
				case "PG":
					$players["{$stat['person_id']}"]['GOALS']++;
					break;
				case "AS":
					$players["{$stat['person_id']}"]['ASSISTS']++;
					break;
				case "YC":
					$players["{$stat['person_id']}"]['YELLOW_CARDS']++;
					break;
				case "RC":
					$players["{$stat['person_id']}"]['RED_CARDS']++;
					break;
				default:
					break;
			}
			$players["{$stat['person_id']}"]['TEAM_ID'] = $stat['team_id'];
	}
	
	foreach($players as $player_id => $player) {
		$q = "INSERT INTO player_game_stats (ID, SEASON_PLAYER_ID,MATCH_ID,GOALS,ASSISTS,YELLOW_CARDS,RED_CARDS) VALUES ($player_id,(SELECT ID FROM season_players WHERE PLAYER_ID=$player_id AND TEAM_ID={$player['TEAM_ID']}),$mid,{$player['GOALS']},{$player['ASSISTS']},{$player['YELLOW_CARDS']},{$player['RED_CARDS']})";
		$DB->query($q);
	}
	echo "processed match $mid\n";
}
?>