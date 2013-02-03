<?php

require("../../inc/all.php");

if ( !isset($_GET['call']) ) {
	echo "you must be in the wrong place";
}


switch ($_GET['call']) {
	case 'top_injuries':
		top_injuries();
		break;
}

//db
$leagueDao = new LeagueDao();

/**data functions -- these spit out CSV **/


function top_injuries() {
	global $leagueDao;
	echo 'hi';
	echo('<pre>');
	print_r($leagueDao->getGamesMissedByTeam());
	echo("</pre>");
}

?>