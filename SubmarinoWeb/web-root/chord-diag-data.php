<?php
require("../inc/all.php");

$teamId = $_GET["team_id"];
$fileType = $_GET["file_type"];

$teamDao = new TeamDao();

$results = $teamDao->getInjuryDataByPlayerAndType($teamId);

if ($fileType == 'json') {
    printf($results[1]);
} else {
    printf($results[0]);
}

?>