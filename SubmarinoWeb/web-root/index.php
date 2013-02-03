<!DOCTYPE html>
<?php
ini_set ('display_errors', true);
error_reporting(E_ALL);

require("../inc/all.php");
?>
<html>
<head>
<meta charset="UTF-8">
<title>placeholder: Insert title here</title>
</head>
    <body>
    
    <?php
    // injury category names: ????
    $leagueDao = new LeagueDao();
    //$results = $leagueDao->getInjuryReoccurencesByTeam();
    //print_r($results);

    $results1 = $leagueDao->getGamesMissedByTeam();
    print_r($results1 );
    echo('<br/>');
    
    $results2 = $leagueDao->getAvgLengthOfInjuryByTeam();
    print_r($results2 );
    echo('<br/>');
    
    $results3 = $leagueDao->getGamesMissedByTeam("muscle");
    print_r($results3 );
    echo('<br/>');
    
    $results4 = $leagueDao->getAvgLengthOfInjuryByTeam("muscle");
    print_r($results4 );
    echo('<br/>');
    
    $results5 = $leagueDao->getInjuryReoccurencesByTeam();
    print_r($results5 );
    echo('<br/>');
    
    $results6 = $leagueDao->getInjuryReoccurencesByTeam("muscle");
    print_r($results6 );
    echo('<br/>');
    
    $teamDao = new TeamDao();
    $results7 = $teamDao->getGamesMissedByPlayer("660");
    print_r($results7);
    echo('<br/>');
    $results8 = $teamDao->getGamesMissedByPlayer("660","muscle");
    print_r($results8);
    echo('<br/>');
    
	?>
    </body>
</html>