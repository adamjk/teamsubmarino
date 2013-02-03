<?php

require("../../inc/all.php");

if ( !isset($_GET['call']) ) {
	echo "you must be in the wrong place";
}

//db
$leagueDao = new LeagueDao();


switch ($_GET['call']) {
	case 'top_injuries':
		top_injuries();
		break;
	default:
		echo "error";
		break;
}


/**data functions -- these spit out CSV **/

function top_injuries() {
	global $leagueDao;
	print_r($leagueDao->getGamesMissedByTeam());
}

?>