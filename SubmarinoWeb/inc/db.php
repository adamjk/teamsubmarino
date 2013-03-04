<?php 

//for mysqli docs see

error_reporting(E_ALL);

$DB = new mysqli("sandbox.stkywll.com", "root", "bitnami", "submarino");
if ($DB->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

// passed an array of arrays, each entry being a line in csv
function csvString($results) {
    $csvString = '';
    foreach ($results as $line) {
        for ($k = 0; $k < count($line); $k++) {
            $csvString .= $line[$k];
            if ($k != count($line) - 1) {
                $csvString .=  ',';
            }
        }
        unset($entry);
        $csvString .= "\n";
    }
    unset($line);
    
    return $csvString;
}

function jsonString($results, $headers) {
    $jsonString = '[ ';
    
    for ($j = 0; $j < count($results); $j++) {
	$line = $results[$j];
        //print_r($line);
        //print_r($headers);
        $jsonString .= '{ ';
        for ($i = 0; $i < count($line); $i++) {
            
            $jsonString .= $headers[$i] . ': "' . $line[$i] . '"';
            if ($i != count($line) - 1) {
                $jsonString .= ', ';
            }
        }
        
        $jsonString .= '} ';
        if ($j != count($results) - 1) {
            $jsonString .= ', ';
        }
    }
    unset($line);
    $jsonString .= ' ]';
    
    return $jsonString;
}

function jsonStringNoHeaderArray($results) {
    $jsonString = '[ ';

    $curr = 0;
    $len = count($results);
    foreach ($results as $line) {
        //print_r($line);
        //print_r($headers);
        $jsonString .= '[ ';
        for ($i = 0; $i < count($line); $i++) {
            if (is_numeric($line[$i])) {
                $jsonString .= $line[$i];
            } else {
                $jsonString .= '"' . $line[$i] . '"';
            }
            
            if ($i != count($line) - 1) {
                $jsonString .= ', ';
            }
        }

        if ($curr == $len - 1) {
            $jsonString .= '] ';
        } else {
            $jsonString .= '], ';
        }
        
        $curr++;
    }
    unset($line);
    $jsonString .= ' ]';

    return $jsonString;

}

// TODO since we only have one season and one league of data, all access
// functions are missing id parameters for these. add later for support
static $SEASON_START_YEAR = 2012;
static $LEAGUE_NAME = "EPL";

/**
 * Stateless data-access class relevant TEAM-level data queries.
 * Returns all data as an array of arrays. Each array entry represents a line
 * of CSV as an array.
 *
 **/
class TeamDao {

    public function getGamesMissedByPlayer($teamId, $injuryCat = null) {
        global $DB, $SEASON_START_YEAR, $LEAGUE_NAME;
        $sql = "SELECT T.ID, SP.NAME, SP.ID, COUNT(MS.ID) "
            . "FROM TEAMS T, SEASON_PLAYERS SP, PLAYER_INJURIES PI, MATCH_STATS MS "
            . "WHERE T.ID = SP.TEAM_ID AND SP.SEASON_START_YEAR = ? "
            . "AND T.LEAGUE_NAME = ? AND SP.ID = PI.SEASON_PLAYER_ID "
            . "AND T.ID = MS.TEAM_ID AND MS.SEASON_START_YEAR = ? "
            . "AND T.ID = ? "
            . "AND MS.GAME_DATE <= CURDATE() "
            . "AND ((PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE > MS.GAME_DATE) "
            . " OR (PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND (PI.EXCLUSIVE_END_DATE IS NULL OR PI.EXCLUSIVE_END_DATE = 0) " 
                    . "AND (PI.expected_excl_end_date IS NULL OR PI.expected_excl_end_date = 0 OR PI.expected_excl_end_date > MS.GAME_DATE) )) ";
            
        if (!empty($injuryCat)) {
            $sql = $sql . "AND PI.INJURY_CATEGORY_NAME = ? ";
        }
        $sql = $sql . "GROUP BY T.ID, SP.NAME, SP.ID "
            . "ORDER BY COUNT(PI.ID) DESC";
        
        //printf($sql);

        $queryResults = array();
        if ($stmt = $DB->prepare ($sql)) {
            
            $types = "isis";
            if (!empty($injuryCat)) {
                $types = $types . "s";
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR, 
                        $teamId, $injuryCat);
            } else {
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR,
                        $teamId);
            }

            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($teamId, $playerName, $playerId, $numGames);
            
            // add header entry
            //$queryResults[] = array("PLAYER_NAME", "PLAYER_ID", "GAMES_MISSED");
            $headers = array("Team", "TeamId", "GamesLost");
            
            /* fetch value */
            $total_count = 0;
            while ($stmt->fetch()) {
                $queryResults[] = array($playerName, $playerId, $numGames);
                $total_count += $numGames;
            }
            //printf("total count: " + $total_count);
            $stmt->close();
        }

        return jsonString($queryResults, $headers);
    }

    public function getNumInjuriesByGamesMissed($teamId, $injuryCat = null) {
        global $DB, $SEASON_START_YEAR, $LEAGUE_NAME;
        
        $sql = "SELECT PIC.M_COUNT, COUNT(PIC.M_COUNT) "
            . "FROM ( SELECT T.ID AS TID, SP.ID AS SPID, PI.ID AS PIID, COUNT(MS.ID) AS M_COUNT "
            . "FROM TEAMS T, SEASON_PLAYERS SP, PLAYER_INJURIES PI, MATCH_STATS MS "
            . "WHERE T.ID = SP.TEAM_ID AND SP.SEASON_START_YEAR = ? "
            . "AND T.LEAGUE_NAME = ? AND SP.ID = PI.SEASON_PLAYER_ID "
            . "AND T.ID = MS.TEAM_ID AND MS.SEASON_START_YEAR = ? "
            . "AND T.ID = ? "
            . "AND MS.GAME_DATE <= CURDATE() "
            . "AND ((PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE > MS.GAME_DATE) "
            . " OR (PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND (PI.EXCLUSIVE_END_DATE IS NULL OR PI.EXCLUSIVE_END_DATE = 0) " 
                    . "AND (PI.expected_excl_end_date IS NULL OR PI.expected_excl_end_date = 0 OR PI.expected_excl_end_date > MS.GAME_DATE) )) ";
                    
        if (!empty($injuryCat)) {
            $sql = $sql . "AND PI.INJURY_CATEGORY_NAME = ? ";
        }
        $sql = $sql . "GROUP BY T.ID, SP.ID, PI.ID ) PIC "
                . "GROUP BY PIC.M_COUNT "
                . "ORDER BY COUNT(PIC.M_COUNT) DESC, PIC.M_COUNT DESC";
               
        //printf($sql);
        
        $queryResults = array();
        if ($stmt = $DB->prepare($sql)) {
        
            $types = "isis";
            if (!empty($injuryCat)) {
                $types = $types . "s";
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR,
                        $teamId, $injuryCat);
            } else {
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR,
                        $teamId);
            }
        
            $stmt->execute();
        
            /* bind result variables */
            $stmt->bind_result($numGamesMissed, $injuryCount);
        
            // add header entry
            //$queryResults[] = array("GAMES_MISSED", "INJURY_COUNT");
            $headers = array("Team", "GamesLost");
            
            /* fetch value */
            $total_count = 0;
            while ($stmt->fetch()) {
                $queryResults[] = array($numGamesMissed, $injuryCount);
                $total_count += $injuryCount;
            }
            //printf("total count: " + $total_count);
            $stmt->close();
        }
        
        return jsonString($queryResults, $headers);
    }
    
    // could be used for maybe a chord graph?
    // per player-type return the number of occurences and # games missed
    public function getInjuryDataByPlayerAndType($teamId) {
        global $DB, $SEASON_START_YEAR, $LEAGUE_NAME;
        
        $sql = "SELECT SP.NAME, SP.ID, PI.INJURY_CATEGORY_NAME, COUNT(DISTINCT PI.ID), COUNT(MS.ID) "
            . "FROM TEAMS T, SEASON_PLAYERS SP, PLAYER_INJURIES PI, MATCH_STATS MS "
            . "WHERE T.ID = SP.TEAM_ID AND SP.SEASON_START_YEAR = ? "
            . "AND T.LEAGUE_NAME = ? AND SP.ID = PI.SEASON_PLAYER_ID "
            . "AND T.ID = MS.TEAM_ID AND MS.SEASON_START_YEAR = ? "
            . "AND T.ID = ? "
            . "AND MS.GAME_DATE <= CURDATE() "
            . "AND ((PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE > MS.GAME_DATE) "
            . " OR (PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND (PI.EXCLUSIVE_END_DATE IS NULL OR PI.EXCLUSIVE_END_DATE = 0) " 
                . "AND (PI.expected_excl_end_date IS NULL OR PI.expected_excl_end_date = 0 OR PI.expected_excl_end_date > MS.GAME_DATE) )) ";
                    
        $sql = $sql . "GROUP BY SP.NAME, SP.ID, PI.INJURY_CATEGORY_NAME "
                . "ORDER BY SP.ID, PI.INJURY_CATEGORY_NAME";
        
        //printf($sql);
        $categories = array("JOINT", "MISC", "MUSCLE", "FRACTURE", "LIGAMENT");
        $injuryMap = array();
        $playerInjCatResults = array();
        $playerInjCatResults[] = array("name", "color");

        $colors = array(
            "#FF0000", "#00FF00", "#0000FF", "#FFFF00", "#FF00FF", "#00FFFF", "#AEC397",
            "#800000", "#008000", "#000080", "#808000", "#800080", "#008080", "#80F080",
            "#C00000", "#4FC300", "#005FC0", "#C0C000", "#C000C0", "#00C0C0", "#8880C0",
            "#400000", "#004000", "#000040", "#404000", "#400040", "#004040", "#404040",
            "#200000", "#002000", "#000020", "#202000", "#200020", "#002020", "#202020",
            "#600000", "#006000", "#000060", "#606000", "#600060", "#006060", "#606060",
            "#A00000", "#00A000", "#0000A0", "#A0A000", "#A000A0", "#00A0A0", "#A0A0A0",
            "#E00000", "#00E000", "#0000E0", "#E0E000", "#E000E0", "#00E0E0", "#E0E0E0",
        );
        
        if ($stmt = $DB->prepare($sql)) {
            $types = "isis";
            $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR,
                        $teamId);
           
        
            $stmt->execute();
        
            /* bind result variables */
            $stmt->bind_result($playerName, $playerId, $injuryType, $injuryCount, $numGames);
        
            /* fetch value */
            $nextPlayerId = 0;
            $catArray;
            $currInjuryIndex = 0;
            $numPlayers = 0;
            while ($stmt->fetch()) {
                if ($nextPlayerId != $playerId) {
                    $nextPlayerId = $playerId;
                    $catArray = array();
                    foreach ($categories as $category) {
                        $catArray[$category] = 0;
                    }
                    unset($category);
                    
                    $injuryMap[] = $catArray;
                    $playerInjCatResults[] = array($playerName, $colors[ $currInjuryIndex % count($colors)]);
                    
                    $currInjuryIndex++;
                    $numPlayers++;
                }
                
                $injuryMap[$currInjuryIndex - 1][$injuryType] = $numGames;
                //$catArray[$injuryType] = $numGames;
            }
            $stmt->close();
        }
        
        // add injuries to end
        foreach ($categories as $category) {
            $playerInjCatResults[] = array($category, "#CDCDCD");
        }
        unset($category);
        
        $injuryArray = array();
        foreach ($injuryMap as $injuryEntry) {
            $injuryArrayEntry = array();
            
            // add blank players 
            for ($i = 0; $i < $numPlayers; $i++) {
                $injuryArrayEntry[] = 0;
            }
            foreach ($categories as $category) {
                $injuryArrayEntry[] = $injuryEntry[$category];
            }
            unset($category);
            $injuryArray[] = $injuryArrayEntry;
        }
        // initialize category row arrays
        foreach ($categories as $category) {
            $injuryArray[] = array();
        }
        
        // transpose
        for ($i = 0; $i < $numPlayers; $i++) {
            for ($j = $numPlayers; $j < ($numPlayers + count($categories)); $j++) {
                $injuryArray[$j][$i] = $injuryArray[$i][$j];
            }
        }
        
        // pad new rows with category zeros
        for ($i = $numPlayers; $i < ($numPlayers + count($categories)); $i++) {
            for ($j = $numPlayers; $j < ($numPlayers + count($categories)); $j++) {
                $injuryArray[$i][$j] = 0;
            }
        }

        return array(csvString($playerInjCatResults),  jsonStringNoHeaderArray($injuryArray)); //jsonString($queryResults, $headers);
    }
    
}

/**
 * Stateless data-access class relevant League-level data queries.
 * Returns all data as an array of arrays. Each array entry represents a line
 * of CSV as an array.
 **/
class LeagueDao {

    /**
     * Get the number of cumulative games missed by each team in a league
     * and season. This accepts an array of injury category names that will
     * filter the results, if empty it will not filter results. 
     * @param unknown $injuryCats
     * @return query results in row arrays, always includes first row of header strings
     */
    public function getGamesMissedByTeam($injuryCat = null) {
        global $DB, $SEASON_START_YEAR, $LEAGUE_NAME;
        $sql = "SELECT T.OFFICIAL_NAME, T.ID, COUNT(MS.ID) "
                . "FROM TEAMS T, SEASON_PLAYERS SP, PLAYER_INJURIES PI, MATCH_STATS MS "
                . "WHERE T.ID = SP.TEAM_ID AND SP.SEASON_START_YEAR = ? "
                . "AND T.LEAGUE_NAME = ? AND SP.ID = PI.SEASON_PLAYER_ID "
                . "AND T.ID = MS.TEAM_ID AND MS.SEASON_START_YEAR = ? "
                . "AND MS.GAME_DATE <= CURDATE() "
                . "AND ((PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE > MS.GAME_DATE) "
                . " OR (PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND (PI.EXCLUSIVE_END_DATE IS NULL OR PI.EXCLUSIVE_END_DATE = 0) " 
                    . "AND (PI.expected_excl_end_date IS NULL OR PI.expected_excl_end_date = 0 OR PI.expected_excl_end_date > MS.GAME_DATE) )) ";
                        
        //for ($i = 0; $i < count($injuryCats); $i++) {
        //    if ($i == 0) { $sql = $sql . "AND (";};
        //    if ($i == count($injuryCats) - 1) {
        //        $sql = $sql . ") ";
        //    };
        //    $sql = $sql . "PI.INJURY_CATEGORY_NAME = ? ";
        //    if ($i != count($injuryCats) - 1) {
        //        $sql = $sql . "OR ";
        //    }
        //}
        if (!empty($injuryCat)) {
            $sql = $sql . "AND PI.INJURY_CATEGORY_NAME = ? ";
        }
        $sql = $sql . "GROUP BY T.OFFICIAL_NAME, T.ID "
                . "ORDER BY COUNT(PI.ID) DESC";

        //printf($sql);
        
        $queryResults = array();
        if ($stmt = $DB->prepare ($sql)) {
            //$sqlParms = array($SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR);
            $types = "isi";
            //foreach ($injuryCats as $cat) {
            //    $types = $types . "s";
            //    $sqlParms[] = $cat;
            //}
            //$types = array($types);
            //$sqlParms = array_merge($types, $sqlParms);
            //call_user_func_array( array($stmt, 'bind_param'), array(&$sqlParms));
            if (!empty($injuryCat)) {
                $types = $types . "s";
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR, $injuryCat);
            } else {
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR);
            }
            
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($teamName, $teamId, $numGames);

            // add header entry
            $headers = array("Team", "TeamId", "GamesLost");
            
            /* fetch value */
            $total_count = 0;
            while ($stmt->fetch()) {
                $queryResults[] = array($teamName, $teamId, $numGames);
                $total_count += $numGames;
            }
            //printf("total count: " + $total_count);
            
            $stmt->close();
        }
        
        return jsonString($queryResults, $headers);
    }
    
    /**
     * 
     * @param unknown $injuryCats
     * @return array of query results, always includes first row of header strings
     */
    public function getAvgLengthOfInjuryByTeam($injuryCat = null) {
        global $DB, $SEASON_START_YEAR, $LEAGUE_NAME;
        
        $sql = "SELECT PIC.OFFICIAL_NAME, PIC.TID, AVG(PIC.M_COUNT) "
            . "FROM ( SELECT T.OFFICIAL_NAME, T.ID AS TID, SP.ID AS SPID, PI.ID AS PIID, COUNT(MS.ID) AS M_COUNT "
            . "FROM TEAMS T, SEASON_PLAYERS SP, PLAYER_INJURIES PI, MATCH_STATS MS "
            . "WHERE T.ID = SP.TEAM_ID AND SP.SEASON_START_YEAR = ? "
            . "AND T.LEAGUE_NAME = ? AND SP.ID = PI.SEASON_PLAYER_ID "
            . "AND T.ID = MS.TEAM_ID AND MS.SEASON_START_YEAR = ? "
            . "AND MS.GAME_DATE <= CURDATE() "
            . "AND ((PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE > MS.GAME_DATE) "
            . " OR (PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND (PI.EXCLUSIVE_END_DATE IS NULL OR PI.EXCLUSIVE_END_DATE = 0) " 
                    . "AND (PI.expected_excl_end_date IS NULL OR PI.expected_excl_end_date = 0 OR PI.expected_excl_end_date > MS.GAME_DATE) )) ";
            
        if (!empty($injuryCat)) {
            $sql = $sql . "AND PI.INJURY_CATEGORY_NAME = ? ";
        }
        $sql = $sql . "GROUP BY T.OFFICIAL_NAME, T.ID, SP.ID, PI.ID ) PIC "
            . "GROUP BY PIC.OFFICIAL_NAME, PIC.TID " 
            . "ORDER BY AVG(PIC.M_COUNT) DESC";
        
        //printf($sql);
        
        $queryResults = array();
        if ($stmt = $DB->prepare ($sql)) {
            $types = "isi";
            
            if (!empty($injuryCat)) {
                $types = $types . "s";
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR, $injuryCat);
            } else {
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR);
            }
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($teamName, $teamId, $injuryAvg);

            /* fetch value */
            //$queryResults[] = array("TEAM_NAME", "TEAM_ID", "INJURY_AVG");
            $headers = array("Team", "TeamId", "GamesLost");
            
            /* fetch value */
            while ($stmt->fetch()) {
                $queryResults[] = array($teamName, $teamId, $injuryAvg);
            }
            
            $stmt->close();
        }
        
        return jsonString($queryResults, $headers);
    }
    
    public function getInjuryReoccurencesByTeam($injuryCat = null) {
       global $DB, $SEASON_START_YEAR, $LEAGUE_NAME;
       
       $sql = "SELECT DUDE.OFFICIAL_NAME, DUDE.TID, COUNT(DUDE.TID) FROM ( "
               . "SELECT PIC.OFFICIAL_NAME, PIC.TID, PIC.SPID, PIC.PIIT, COUNT(PIC.PIIT) "
               . "FROM ( SELECT DISTINCT T.OFFICIAL_NAME, T.ID AS TID, SP.ID AS SPID, PI.ID AS PIID, "
               . "PI.INJURY_TYPE_NAME AS PIIT FROM TEAMS T, SEASON_PLAYERS SP, PLAYER_INJURIES PI, "
               . "MATCH_STATS MS WHERE T.ID = SP.TEAM_ID AND SP.SEASON_START_YEAR = ? "
               . "AND T.LEAGUE_NAME = ? AND SP.ID = PI.SEASON_PLAYER_ID AND T.ID = MS.TEAM_ID "
               . "AND MS.SEASON_START_YEAR = ? AND MS.GAME_DATE <= CURDATE() AND "
               . "((PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE > MS.GAME_DATE) "
               . "OR (PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND (PI.EXCLUSIVE_END_DATE IS NULL "
               . "OR PI.EXCLUSIVE_END_DATE = 0) AND (PI.expected_excl_end_date IS NULL OR PI.expected_excl_end_date = 0 OR PI.expected_excl_end_date > MS.GAME_DATE) ))";

       if (!empty($injuryCat)) {
           $sql = $sql . "AND PI.INJURY_CATEGORY_NAME = ? ";
       }                
                       
       $sql = $sql . ") PIC GROUP BY PIC.OFFICIAL_NAME, PIC.TID, PIC.SPID, PIC.PIIT "
               . "HAVING COUNT(PIC.PIIT) > 1 ) DUDE GROUP BY DUDE.OFFICIAL_NAME, DUDE.TID "
               . "ORDER BY COUNT(DUDE.TID) DESC";

        //printf($sql);
        
        $queryResults = array();
        if ($stmt = $DB->prepare ($sql)) {
            $types = "isi";

            if (!empty($injuryCat)) {
                $types = $types . "s";
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR, $injuryCat);
            } else {
                $stmt->bind_param($types, $SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR);
            }
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($teamName, $teamId, $recCount);

            /* fetch value */
            $headers = array("Team", "TeamId", "GamesLost");

            /* fetch value */
            while ($stmt->fetch()) {
                $queryResults[] = array($teamName, $teamId, $recCount);
            }

            $stmt->close();
        }
        
        return jsonString($queryResults, $headers);
    }
    
}

?>
