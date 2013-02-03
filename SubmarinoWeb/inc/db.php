<?php 

//for mysqli docs see

error_reporting(E_ALL);

$DB = new mysqli("sandbox.stkywll.com", "root", "bitnami", "submarino");
if ($DB->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

/*function getRefArray($a) {
    if (strnatcmp(phpversion(),'5.3')>=0) {
        $ret = array();
        foreach($a as $key => $val) {
            $ret[$key] = &$a[$key];
        }
        return $ret;
    }
    return $a;
}*/

/**
 * Stateless data-access class relevant TEAM-level data queries.
 * Returns all data as an array of arrays. Each array entry represents a line
 * of CSV as an array.
 *
 **/
class TeamDao {

    public function get() {
         
    }

}

/**
 * Stateless data-access class relevant League-level data queries.
 * Returns all data as an array of arrays. Each array entry represents a line
 * of CSV as an array.
 **/
class LeagueDao {
    // TODO since we only have one season and one league of data, all access
    // functions are missing id parameters for these. add later for support
    static $SEASON_START_YEAR = 2012;
    static $LEAGUE_NAME = "";

    /**
     * Get the number of cumulative games missed by each team in a league
     * and season. This accepts an array of injury category names that will
     * filter the results, if empty it will not filter results. 
     * @param unknown $injuryCats
     * @return query results in row arrays, always includes first row of header strings
     */
    public function getGamesMissedByTeam($injuryCat = null) {
        global $DB, $SEASON_START_YEAR, $LEAGUE_NAME;
        $sql = "SELECT T.OFFICIAL_NAME, T.ID, COUNT(PI.ID) "
                . "FROM TEAMS T, SEASON_PLAYERS SP, PLAYER_INJURIES PI, MATCH_STATS MS "
                . "WHERE T.ID = SP.TEAM_ID AND SP.SEASON_START_YEAR = ? "
                . "AND T.LEAGUE_NAME = ? AND SP.ID = PI.SEASON_PLAYER_ID "
                . "AND T.ID = MS.TEAM_ID AND MS.SEASON_START_YEAR = ? "
                . "AND MS.GAME_DATE <= CURDATE() "
                . "AND ((PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE > MS.GAME_DATE) "
                . "  OR (PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE IS NULL)) ";
        
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

        printf($sql);
        
        $queryResults = array();
        if ($stmt = $DB->prepare ($sql)) {
            $sqlParms = array($SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR);
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
            $stmt->bind_result($teamName, $teamId, $injuryCount);

            // add header entry
            $queryResults[] = array("TEAM_NAME", "TEAM_ID", "INJURY_COUNT");
            /* fetch value */
            while ($stmt->fetch()) {
                $queryResults[] = array($teamName, $teamId, $injuryCount);
            }
            
            $stmt->close();
        }
        
        return $queryResults;
    }
    
    /**
     * 
     * @param unknown $injuryCats
     * @return array of query results, always includes first row of header strings
     */
    public function getAvgLengthOfInjuryByTeam($injuryCat = null) {
        global $DB, $SEASON_START_YEAR, $LEAGUE_NAME;
        
        $sql = "SELECT PIC.OFFICIAL_NAME, PIC.TID, AVG(PIC.M_COUNT) "
            . "FROM ( SELECT T.OFFICIAL_NAME, T.ID AS TID , SP.ID AS SPID, PI.ID AS PIID, COUNT(MS.ID) AS M_COUNT "
            . "FROM TEAMS T, SEASON_PLAYERS SP, PLAYER_INJURIES PI, MATCH_STATS MS "
            . "WHERE T.ID = SP.TEAM_ID AND SP.SEASON_START_YEAR = ? "
            . "AND T.LEAGUE_NAME = ? AND SP.ID = PI.SEASON_PLAYER_ID "
            . "AND T.ID = MS.TEAM_ID AND MS.SEASON_START_YEAR = ? "
            . "AND MS.GAME_DATE <= CURDATE() "
            . "AND ((PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE > MS.GAME_DATE) "
            . "  OR (PI.INCLUSIVE_BEGIN_DATE <= MS.GAME_DATE AND PI.EXCLUSIVE_END_DATE IS NULL)) ";

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
        $sql = $sql . "GROUP BY T.OFFICIAL_NAME, T.ID, SP.ID, PI.ID ) PIC "
            . "GROUP BY PIC.OFFICIAL_NAME, PIC.TID " 
            . "ORDER BY AVG(PIC.M_COUNT) DESC";
        
        printf($sql);
        
        $queryResults = array();
        if ($stmt = $DB->prepare ($sql)) {
            $sqlParms = array($SEASON_START_YEAR, $LEAGUE_NAME, $SEASON_START_YEAR);
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
            $stmt->bind_result($teamName, $teamId, $injuryAvg);

            /* fetch value */
            $queryResults[] = array("TEAM_NAME", "TEAM_ID", "INJURY_AVG");
            
            /* fetch value */
            while ($stmt->fetch()) {
                $queryResults[] = array($teamName, $teamId, $injuryCount);
            }
            
            $stmt->close();
        }
        
        return $queryResults;
    }
    
    public function getInjuryReoccurencesByTeam() {
        global $DB, $SEASON_START_YEAR, $LEAGUE_NAME;
        
        $queryResults = array();
        
        $queryResults[] = array("TEAM_NAME", "TEAM_ID", "REOCCURENCE_COUNT");
        return $queryResults;
    }
    
}

?>