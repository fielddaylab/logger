<?php
// Indicate JSON data type 
header('Content-Type: application/json');

// Establish the database connection
include "database.php";
include "Regression.php";
include "Matrix.php";

require_once "KMeans/Space.php";
require_once "KMeans/Point.php";
require_once "KMeans/Cluster.php";

require_once "PCA/pca.php";

ini_set('memory_limit','1024M');
ini_set('max_execution_time', 300);

$db = connectToDatabase(DBDeets::DB_NAME_DATA);
if ($db->connect_error) {
    http_response_code(500);
    die('{ "errMessage": "Failed to Connect to DB." }');
}

function average($arr) {
    $filtered = array_filter($arr, function($val) { return !is_string($val) && isset($val); });
    $total = array_sum($filtered);
    $length = count($filtered);
    return ($length > 0) ? $total / $length : -1;
}

if (isset($_GET['gameID'])) {
    $returned;
    if (isset($_GET['sessionID'])) {
        if (isset($_GET['level'])) {
            $returned = json_encode(getAndParseData($_GET['gameID'], $db, $_GET['sessionID'], $_GET['level']));
        } else {
            $returned = json_encode(getAndParseData($_GET['gameID'], $db, $_GET['sessionID'], null));
        }
    } else {
        $returned = json_encode(getAndParseData($_GET['gameID'], $db, null, null));
    }
    
    echo $returned;//substr($returned, 0, 1000);
}

function getTotalNumSessions($gameID, $db) {
    $query = "SELECT COUNT(session_id) FROM (SELECT DISTINCT session_id FROM log WHERE app_id=?) q;";
    $params = array($gameID);
    $stmt = queryMultiParam($db, $query, 's', $params);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    if (!$stmt->bind_result($numSessions)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    $sessionAttributes = array(); // the master array of all sessions that will be built with attributes
    $allEvents = array();
    $stmt->fetch();
    $stmt->close();
    return $numSessions;
}

function getAndParseData($gameID, $db, $reqSessionID, $reqLevel) {
    if (!isset($reqSessionID)) {
        $minMoves = $_GET['minMoves'];
        $minLevels = $_GET['minLevels'];
        $minQuestions = $_GET['minQuestions'];
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];
        $maxRows = $_GET['maxRows'];

        // Main query that returns filtered data
        $query = "SELECT a.session_id, a.level, a.event, a.event_custom, a.event_data_complex, a.client_time, a.app_id
        FROM log as a
        WHERE a.client_time>=? AND a.client_time<=? AND a.app_id=? ";
        $params = array($startDate, $endDate, $gameID);
        $paramTypes = 'sss';

        if ($minMoves > 0) {
            $query .= "AND a.session_id IN
            (
                SELECT session_id FROM
                (
                    SELECT session_id, event_custom
                    FROM log
                    WHERE event_custom=1 OR event_custom=2
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                ) AS moves
            ) ";
            $params []= $minMoves;
            $paramTypes .= 'i';
        }
        if ($minLevels > 0) {
            $query .= "AND a.session_id IN
            (
                SELECT session_id FROM
                (
                    SELECT session_id, event
                    FROM log
                    WHERE event='COMPLETE'
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                ) AS levels
            ) ";
            $params []= $minLevels;
            $paramTypes .= 'i';
        }
        if ($minQuestions > 0) {
            $query .= "AND a.session_id IN
            (
                SELECT session_id FROM
                (
                    SELECT session_id, event_custom
                    FROM log
                    WHERE event_custom=3
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                ) AS questions
            ) ";
            $params []= $minQuestions;
            $paramTypes .= 'i';
        }

        $query .= "ORDER BY a.client_time LIMIT ?";
        $params []= $maxRows;
        $paramTypes .= 'i';

        $stmt = queryMultiParam($db, $query, $paramTypes, $params);
        if($stmt === NULL) {
            http_response_code(500);
            die('{ "errMessage": "Error running query." }');
        }
        if (!$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time, $app_id)) {
            http_response_code(500);
            die('{ "errMessage": "Failed to bind to results." }');
        }
        $sessionAttributes = array(); // the master array of all sessions that will be built with attributes
        $allEvents = array();
        while($stmt->fetch()) {
            $tuple = array('session_id'=>$session_id, 'level'=>$level, 'event'=>$event, 'event_custom'=>$event_custom, 
            'event_data_complex'=>$event_data_complex, 'time'=>$client_time);
            // Group the variables into their sessionIDs in a big associative array
            $sessionAttributes[$session_id][] = $tuple;
            // Also make one big array of every event for easier extraction of unique attributes
            $allEvents[] = $tuple;
        }
        $stmt->close();
    } else {
        $query =
        "SELECT session_id, level, event, event_custom, event_data_complex, client_time
        FROM log
        WHERE session_id=?
        ORDER BY client_time;";

        $params = array($reqSessionID);
        $stmt = queryMultiParam($db, $query, 's', $params);
        if($stmt === NULL) {
            http_response_code(500);
            die('{ "errMessage": "Error running query." }');
        }
        if (!$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time)) {
            http_response_code(500);
            die('{ "errMessage": "Failed to bind to results." }');
        }
        $sessionAttributes = array(); // the master array of all sessions that will be built with attributes
        $allEvents = array();
        while($stmt->fetch()) {
            $tuple = array('session_id'=>$session_id, 'level'=>$level, 'event'=>$event, 'event_custom'=>$event_custom, 
            'event_data_complex'=>$event_data_complex, 'time'=>$client_time);
            // Group the variables into their sessionIDs in a big associative array
            $sessionAttributes[$session_id][] = $tuple;
            // Also make one big array of every event for easier extraction of unique attributes
            $allEvents[] = $tuple;
        }
        $stmt->close();
    }

    // Sort every id's sessions by date
    foreach ($sessionAttributes as $i=>$val) {
        uasort($sessionAttributes[$i], function($a, $b) {
           return ($a['time'] <= $b['time']) ? -1 : 1;
        });
    }

    // Sort session ids by date, the default from before
    uasort($sessionAttributes, function($a, $b) {
       return ($a[0]['time'] <= $b[0]['time']) ? -1 : 1;
    });

    $sessions = array_keys($sessionAttributes);
    $uniqueSessions = array_unique($sessions);
    $numSessions = count($uniqueSessions);

    $numEvents = count($allEvents);
    $levels = array_unique(array_column($allEvents, 'level'));
    sort($levels);
    $numLevels = count($levels);

    $times = array(); // Construct array of each session's first time
    foreach ($sessionAttributes as $i=>$val) {
        $times[$i] = $val[0]['time'];
    }
    
    // Construct sessions and times array
    $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));

    // Questions answered for session provided
    $questionsSingle = array();
    if (isset($reqSessionID) && !isset($reqLevel)) {
        $questionEvents = array();
        foreach ($sessionAttributes[$reqSessionID] as $i=>$val) {
            if ($val['event_custom'] === 3) {
                $questionEvents []= $val;
            }
        }
        $numCorrect = 0;
        $numQuestions = count($questionEvents);
        for ($i = 0; $i < $numQuestions; $i++) {
            $jsonData = json_decode($questionEvents[$i]['event_data_complex'], true);
            if ($jsonData['answer'] === $jsonData['answered']) {
                $numCorrect++;
            }
        }
        $questionsSingle = array('numCorrect'=>$numCorrect, 'numQuestions'=>$numQuestions);
    }

    // Graph data for one session
    $graphDataSingle = array();
    $basicInfoSingle = array();
    if (isset($reqSessionID)) {
        $graphEvents = null;
        $graphTimes = null;
        $graphEventData = null;
        $graphLevels = null;
        foreach ($sessionAttributes[$reqSessionID] as $i=>$val) {
            if (isset($reqLevel) && $val['level'] == $reqLevel) {
                if ($val['event_custom'] === 1 ||
                    $val['event_custom'] === 2 ||
                    $val['event'] === 'SUCCEED'
                ) {
                    if (!isset($graphEvents, $graphTimes, $graphEventData, $graphLevels)) {
                        $graphEvents = array();
                        $graphTimes = array();
                        $graphEventData = array();
                        $graphLevels = array();
                    }
                    $graphEvents []= $val['event'];
                    $graphTimes [] = $val['time'];
                    $graphEventData []= $val['event_data_complex'];
                }
            }
        } 
        $graphDataSingle = array('events'=>$graphEvents, 'times'=>$graphTimes, 'event_data'=>$graphEventData);
    
        // Basic info for one session
        $infoTimes = array();
        $infoEventData = array();
        $infoLevels = array();
        $infoEvents = array();
        foreach ($sessionAttributes[$reqSessionID] as $i=>$val) {
            $infoTimes []= $val['time'];
            $infoEventData []= $val['event_data_complex'];
            $infoLevels []= $val['level'];
            $infoEvents []= $val['event'];
        }
        $dataObj = array('data'=>$infoEventData, 'times'=>$infoTimes, 'events'=>$infoEvents, 'levels'=>$infoLevels);
        $avgTime;
        $totalTime = 0;
        $numMovesPerChallenge;
        $totalMoves = 0;
        $avgMoves;
        $moveTypeChangesPerLevel;
        $moveTypeChangesTotal = 0;
        $moveTypeChangesAvg;
        $knobStdDevs;
        $knobNumStdDevs;
        $knobAmtsTotal = 0;
        $knobAmtsAvg;
        $knobSumTotal = 0;
        $knobSumAvg;
        $numLevelsThisSession = count(array_unique($dataObj['levels']));
        if (isset($dataObj['times'])) {
            // Basic features stuff
            $levelStartTime;
            $levelEndTime;
            $lastSlider = null;
            $startIndices = array();
            $endIndices = array();
            $moveTypeChangesPerLevel = array();
            $knobStdDevs = array();
            $knobNumStdDevs = array();
            $knobAmts = array();
            $numMovesPerChallenge = array();
            $moveTypeChangesPerLevel = array();
            $knobStdDevs = array();
            $knobNumStdDevs = array();
            $startIndices = array();
            $endIndices = array();
            $indicesToSplice = array();
            $levelTimes = array();
            $avgKnobStdDevs = array();
            $knobAvgs = array();
            foreach ($dataObj['levels'] as $i) {
                $numMovesPerChallenge[$i] = array();
                $indicesToSplice[$i] = array();
                
                $startIndices[$i] = null;
                $endIndices[$i] = null;
                $moveTypeChangesPerLevel[$i] = 0;
                $knobStdDevs[$i] = 0;
                $knobNumStdDevs[$i] = 0;
                $knobAmts[$i] = 0;
                $knobAvgs[$i] = 0;
                $avgKnobStdDevs[$i] = 0;
            }
    
            for ($i = 0; $i < count($dataObj['times']); $i++) {
                if (!isset($endIndices[$dataObj['levels'][$i]])) {
                    $dataJson = json_decode($dataObj['data'][$i], true);
                    if ($dataObj['events'][$i] === 'BEGIN') {
                        if (!isset($startIndices[$dataObj['levels'][$i]])) { // check this space isn't filled by a previous attempt on the same level
                            $startIndices[$dataObj['levels'][$i]] = $i;
                        }
                    } else if ($dataObj['events'][$i] === 'COMPLETE') {
                        if (!isset($endIndices[$dataObj['levels'][$i]])) {
                            $endIndices[$dataObj['levels'][$i]] = $i;
                        }
                    } else if ($dataObj['events'][$i] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE' || $dataJson['event_custom'] === 'ARROW_MOVE_RELEASE')) {
                        if ($lastSlider !== $dataJson['slider']) {
                            if (!isset($moveTypeChangesPerLevel[$dataObj['levels'][$i]])) $moveTypeChangesPerLevel[$dataObj['levels'][$i]] = 0;
                            $moveTypeChangesPerLevel[$dataObj['levels'][$i]]++;
                        }
                        $lastSlider = $dataJson['slider'];
                        $numMovesPerChallenge[$dataObj['levels'][$i]] []= $i;
                        if ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE') { // arrows don't have std devs
                            //if (!isset($knobNumStdDevs[$dataObj['levels'][$i]])) $knobNumStdDevs[$dataObj['levels'][$i]] = 0;
                            $knobNumStdDevs[$dataObj['levels'][$i]]++;
                            //if (!isset($knobStdDevs[$dataObj['levels'][$i]])) $knobStdDevs[$dataObj['levels'][$i]] = 0;
                            $knobStdDevs[$dataObj['levels'][$i]] += $dataJson['stdev_val'];
                            //if (!isset($knobAmts[$dataObj['levels'][$i]])) $knobAmts[$dataObj['levels'][$i]] = 0;
                            $knobAmts[$dataObj['levels'][$i]] += ($dataJson['max_val']-$dataJson['min_val']);
                        }
                    }
                }
            }
            
            foreach ($endIndices as $i=>$value) {
                if (isset($endIndices[$i])) {
                    $levelTime = 99999;
                    if (isset($dataObj['times'][$endIndices[$i]], $dataObj['times'][$startIndices[$i]])) {
                        $levelStartTime = new DateTime($dataObj['times'][$startIndices[$i]]);
                        $levelEndTime = new DateTime($dataObj['times'][$endIndices[$i]]);
                        $levelTime = $levelEndTime->getTimestamp() - $levelStartTime->getTimestamp();
                        $totalTime += $levelTime;
                    }
                    $levelTimes[$i] = $levelTime;
    
                    $totalMoves += count($numMovesPerChallenge[$i]);
                    $moveTypeChangesTotal += $moveTypeChangesPerLevel[$i];
    
                    $knobAvgAmt = 0;
                    $knobAvgStdDev = 0;
                    if ($knobNumStdDevs[$i] != 0) {
                        $temp = $knobAmts[$i]/$knobNumStdDevs[$i];
                        $knobAmtsTotal += $temp;
                        $knobAvgAmt = $temp;
                        $knobAvgStdDev = ($knobStdDevs[$i]/$knobNumStdDevs[$i]);
                    }
                    $knobAvgs[$i] = $knobAvgAmt;
                    $avgKnobStdDevs[$i] = $knobAvgStdDev;
    
                    if ($knobAmts[$i] != 0) {
                        $knobSumTotal += $knobAmts[$i];
                    } 
                }
            }
            $avgTime = $totalTime / $numLevelsThisSession;
            $avgMoves = $totalMoves / $numLevelsThisSession;
            $moveTypeChangesAvg = $moveTypeChangesTotal / $numLevelsThisSession;
            $knobAmtsAvg = $knobAmtsTotal / $numLevelsThisSession;
            $knobSumAvg = $knobSumTotal / $numLevelsThisSession;
        }
        $numMoves = array();
        $filteredNumMoves = array_filter($numMovesPerChallenge, function ($value) { return isset($value) && !is_null($value); });
        foreach ($filteredNumMoves as $j=>$value) {
            $numMoves[$j] = count($numMovesPerChallenge[$j]);
        }
        /*
         * The above values are
         * levelTimes                -    array       - elements hold time per level for this session
         * avgTime                   -    value       - average value of levelTimes
         * totalTime                 -    value       - sum value of levelTimes
         * 
         * numMovesPerChallenge      -    array       - elements hold number of moves per level (NOT a list of indices at this point)
         * totalMoves                -    value       - sum value of numMovesPerChallenge
         * avgMoves                  -    value       - average value of numMovesPerChallenge
         * 
         * moveTypeChangesPerLevel   -    array       - elements hold number of times move type changed
         * moveTypeChangesTotal      -    value       - sum value of moveTypeChangesPerLevel
         * moveTypeChangesAvg        -    value       - average value of moveTypeChangesPerLevel 
         * 
         * knobStdDevs               -    array       - elements hold average std dev for moves in level
         * knobNumStdDevs            -    array       - elements hold number of std devs in level
         * 
         * knobAvgs                  -    array       - elements hold average max-min for each level
         * knobAmtsTotalAvg          -    value       - sum value of knobAvgs
         * knobAmtsAvgAvg            -    value       - average value of knobAvgs
         * 
         * knobTotalAmts             -    array       - elements hold total max-min for each level
         * knobTotalAvg              -    value       - average value of knobTotalAmts
         * knobSumTotal              -    value       - sum value of knobTotalAmts
         * 
         * numMovesPerChallengeArray -    array[][]   - original numMovesPerChallenge (list of indices of moves per level)
         * dataObj                   -    object      - dataObj from old structure
         */
        $basicInfoSingle = array('levelTimes'=>$levelTimes, 'avgTime'=>$avgTime, 'totalTime'=>$totalTime, 'numMovesPerChallenge'=>$numMoves, 'totalMoves'=>$totalMoves, 'avgMoves'=>$avgMoves,
        'moveTypeChangesPerLevel'=>$moveTypeChangesPerLevel, 'moveTypeChangesTotal'=>$moveTypeChangesTotal, 'moveTypeChangesAvg'=>$moveTypeChangesAvg, 'knobStdDevs'=>$avgKnobStdDevs,
        'knobNumStdDevs'=>$knobNumStdDevs, 'knobAvgs'=>$knobAvgs, 'knobAmtsTotalAvg'=>$knobAmtsTotal, 'knobAmtsAvgAvg'=>$knobAmtsAvg, 'knobTotalAmts'=>$knobAmts, 'knobSumTotal'=>$knobSumTotal,
        'knobTotalAvg'=>$knobSumAvg, 'numMovesPerChallengeArray'=>$numMovesPerChallenge, 'dataObj'=>$dataObj);
    }

    // Get basic info for all sessions
    $sessionIDs = array();
    $basicInfoAll = array();
    $numLevelsAll = array();
    $questionsAll = array();
    $numMovesAll = array();
    $questionsTotal = array();
    $questionAnswereds = array();
    if (!isset($reqSessionID)) {
        $sessionIDs = $sessionsAndTimes['sessions'];
        $allData = array();
        // arrays of arrays (temp)
        $levelTimesPerLevelAll = array();
        $numMovesPerLevelAll = array();
        $moveTypeChangesPerLevelAll = array();
        $knobStdDevsPerLevelAll = array();
        $knobTotalAmtsPerLevelAll = array();
        $knobAvgsPerLevelAll = array();
    
        // arrays of totals of above arrays (temp)
        $totalTimesPerLevelAll = array();
        $totalMovesPerLevelArray = array();
        $totalMoveTypeChangesPerLevelAll = array();
        $totalStdDevsPerLevelAll = array();
        $totalKnobTotalsPerLevelAll = array();
        $totalKnobAvgsPerLevelAll = array();
    
        // scalar totals of totals arrays (temp)
        $totalTimeAll = 0;
        $totalMovesAll = 0;
        $totalMoveTypeChangesAll = 0;
        $totalStdDevsAll = 0;
        $totalKnobTotalsAll = 0;
        $totalKnobAvgsAll = 0;
    
        // arrays of averages per level (display)
        $avgLevelTimesAll = array();
        $avgMovesArray = array();
        $avgMoveTypeChangesPerLevelAll = array();
        $avgStdDevsPerLevelAll = array();
        $avgKnobTotalsPerLevelAll = array();
        $avgKnobAvgsPerLevelAll = array();
    
        // scalar averages of averages arrays (display)
        $avgTimeAll = 0;
        $avgMovesAll = 0;
        $avgMoveTypeChangesAll = 0;
        $avgStdDevAll = 0;
        $avgKnobTotalsAll = 0;
        $avgKnobAvgsAll = 0;
    
        for ($i = 0; $i < $numLevels; $i++) {
            $levelTimesPerLevelAll[$i] = array();
            $moveTypeChangesPerLevelAll[$i] = array();
            $numMovesPerLevelAll[$i] = array();
            $knobStdDevsPerLevelAll[$i] = array();
            $knobTotalAmtsPerLevelAll[$i] = array();
            $knobAvgsPerLevelAll[$i] = array();
        }
        foreach ($sessionIDs as $s=>$sessionID) {
            $infoTimes = array();
            $infoEventData = array();
            $infoLevels = array();
            $infoEvents = array();
            foreach ($sessionAttributes[$sessionID] as $i=>$val) {
                $infoTimes []= $val['time'];
                $infoEventData []= $val['event_data_complex'];
                $infoLevels []= $val['level'];
                $infoEvents []= $val['event'];
            }
            $dataObj = array('data'=>$infoEventData, 'times'=>$infoTimes, 'events'=>$infoEvents, 'levels'=>$infoLevels);
            $avgTime;
            $totalTime = 0;
            $numMovesPerChallenge;
            $totalMoves = 0;
            $avgMoves;
            $moveTypeChangesPerLevel;
            $moveTypeChangesTotal = 0;
            $moveTypeChangesAvg;
            $knobStdDevs;
            $knobNumStdDevs;
            $knobAmtsTotal = 0;
            $knobAmtsAvg;
            $knobSumTotal = 0;
            $knobSumAvg;
            $numLevelsThisSession2 = count(array_unique($dataObj['levels']));
            if (isset($dataObj['times'])) {
                // Basic features stuff
                $levelStartTime;
                $levelEndTime;
                $lastSlider = null;
                $startIndices = array();
                $endIndices = array();
                $moveTypeChangesPerLevel = array();
                $knobStdDevs = array();
                $knobNumStdDevs = array();
                $knobAmts = array();
                $numMovesPerChallenge = array();
                $moveTypeChangesPerLevel = array();
                $knobStdDevs = array();
                $knobNumStdDevs = array();
                $startIndices = array();
                $endIndices = array();
                $indicesToSplice = array();
                $levelTimes = array();
                $avgKnobStdDevs = array();
                $knobAvgs = array();
                foreach ($dataObj['levels'] as $i) {
                    $numMovesPerChallenge[$i] = array();
                    $indicesToSplice[$i] = array();
                    
                    $startIndices[$i] = null;
                    $endIndices[$i] = null;
                    $moveTypeChangesPerLevel[$i] = 0;
                    $knobStdDevs[$i] = 0;
                    $knobNumStdDevs[$i] = 0;
                    $knobAmts[$i] = 0;
                    $knobAvgs[$i] = 0;
                    $avgKnobStdDevs[$i] = 0;
                }
        
                for ($i = 0; $i < count($dataObj['times']); $i++) {
                    if (!isset($endIndices[$dataObj['levels'][$i]])) {
                        $dataJson = json_decode($dataObj['data'][$i], true);
                        if ($dataObj['events'][$i] === 'BEGIN') {
                            if (!isset($startIndices[$dataObj['levels'][$i]])) { // check this space isn't filled by a previous attempt on the same level
                                $startIndices[$dataObj['levels'][$i]] = $i;
                            }
                        } else if ($dataObj['events'][$i] === 'COMPLETE') {
                            if (!isset($endIndices[$dataObj['levels'][$i]])) {
                                $endIndices[$dataObj['levels'][$i]] = $i;
                            }
                        } else if ($dataObj['events'][$i] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE' || $dataJson['event_custom'] === 'ARROW_MOVE_RELEASE')) {
                            if ($lastSlider !== $dataJson['slider']) {
                                if (!isset($moveTypeChangesPerLevel[$dataObj['levels'][$i]])) $moveTypeChangesPerLevel[$dataObj['levels'][$i]] = 0;
                                $moveTypeChangesPerLevel[$dataObj['levels'][$i]]++;
                            }
                            $lastSlider = $dataJson['slider'];
                            $numMovesPerChallenge[$dataObj['levels'][$i]] []= $i;
                            if ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE') { // arrows don't have std devs
                                //if (!isset($knobNumStdDevs[$dataObj['levels'][$i]])) $knobNumStdDevs[$dataObj['levels'][$i]] = 0;
                                $knobNumStdDevs[$dataObj['levels'][$i]]++;
                                //if (!isset($knobStdDevs[$dataObj['levels'][$i]])) $knobStdDevs[$dataObj['levels'][$i]] = 0;
                                $knobStdDevs[$dataObj['levels'][$i]] += $dataJson['stdev_val'];
                                //if (!isset($knobAmts[$dataObj['levels'][$i]])) $knobAmts[$dataObj['levels'][$i]] = 0;
                                $knobAmts[$dataObj['levels'][$i]] += ($dataJson['max_val']-$dataJson['min_val']);
                            }
                        }
                    }
                }
                
                foreach ($endIndices as $i=>$value) {
                    if (isset($endIndices[$i])) {
                        $levelTime = 999999;
                        if (isset($dataObj['times'][$endIndices[$i]], $dataObj['times'][$startIndices[$i]])) {
                            $levelStartTime = new DateTime($dataObj['times'][$startIndices[$i]]);
                            $levelEndTime = new DateTime($dataObj['times'][$endIndices[$i]]);
                            $levelTime = $levelEndTime->getTimestamp() - $levelStartTime->getTimestamp();
                            $totalTime += $levelTime;
                        }
                        $levelTimes[$i] = $levelTime;
        
                        $totalMoves += count($numMovesPerChallenge[$i]);
                        $moveTypeChangesTotal += $moveTypeChangesPerLevel[$i];
        
                        $knobAvgAmt = 0;
                        $knobAvgStdDev = 0;
                        if ($knobNumStdDevs[$i] != 0) {
                            $temp = $knobAmts[$i]/$knobNumStdDevs[$i];
                            $knobAmtsTotal += $temp;
                            $knobAvgAmt = $temp;
                            $knobAvgStdDev = ($knobStdDevs[$i]/$knobNumStdDevs[$i]);
                        }
                        $knobAvgs[$i] = $knobAvgAmt;
                        $avgKnobStdDevs[$i] = $knobAvgStdDev;
        
                        if ($knobAmts[$i] != 0) {
                            $knobSumTotal += $knobAmts[$i];
                        } 
                    }
                }
                $avgTime = $totalTime / $numLevelsThisSession2;
                $avgMoves = $totalMoves / $numLevelsThisSession2;
                $moveTypeChangesAvg = $moveTypeChangesTotal / $numLevelsThisSession2;
                $knobAmtsAvg = $knobAmtsTotal / $numLevelsThisSession2;
                $knobSumAvg = $knobSumTotal / $numLevelsThisSession2;
            }
            $numMoves = array();
            $filteredNumMoves = array_filter($numMovesPerChallenge, function ($value) { return isset($value); });
            foreach ($filteredNumMoves as $j=>$value) {
                $numMoves[$j] = count($numMovesPerChallenge[$j]);
            }
            $allData[$sessionID] = array('levelTimes'=>$levelTimes, 'avgTime'=>$avgTime, 'totalTime'=>$totalTime, 'numMovesPerChallenge'=>$numMoves, 'totalMoves'=>$totalMoves, 'avgMoves'=>$avgMoves, 'moveTypeChangesPerLevel'=>$moveTypeChangesPerLevel, 'moveTypeChangesTotal'=>$moveTypeChangesTotal, 'moveTypeChangesAvg'=>$moveTypeChangesAvg, 'knobStdDevs'=>$avgKnobStdDevs, 'knobNumStdDevs'=>$knobNumStdDevs, 'knobAvgs'=>$knobAvgs, 'knobAmtsTotalAvg'=>$knobAmtsTotal, 'knobAmtsAvgAvg'=>$knobAmtsAvg,
            'knobTotalAmts'=>$knobAmts, 'knobSumTotal'=>$knobSumTotal, 'knobTotalAvg'=>$knobSumAvg, 'numMovesPerChallengeArray'=>$numMovesPerChallenge, 'dataObj'=>$dataObj);
        }
    
        // loop through all the sessions we got above and add their variables to totals
        $levelCol = array_column($allData, 'levelTimes');
        $moveCol = array_column($allData, 'numMovesPerChallenge');
        $typeCol = array_column($allData, 'moveTypeChangesPerLevel');
        $stdCol = array_column($allData, 'knobStdDevs');
        $totalCol = array_column($allData, 'knobTotalAmts');
        $avgCol = array_column($allData, 'knobAvgs');

        for ($i = 0; $i < $numLevels; $i++) {
            $totalTimesPerLevelAll[$i] = average(array_column($levelCol, $i));
            $totalMovesPerLevelArray[$i] = average(array_column($moveCol, $i));
            $totalMoveTypeChangesPerLevelAll[$i] = average(array_column($typeCol, $i));
            $totalStdDevsPerLevelAll[$i] = average(array_column($stdCol, $i));
            $totalKnobTotalsPerLevelAll[$i] = average(array_column($totalCol, $i));
            $totalKnobAvgsPerLevelAll[$i] = average(array_column($avgCol, $i));
        }
        // foreach ($allData as $index=>$dataObj) {
        //     foreach ($dataObj['levelTimes'] as $i=>$levelTime) { $levelTimesPerLevelAll[$i] []= $levelTime; }
        //     foreach ($levelTimesPerLevelAll as $i=>$array) { $totalTimesPerLevelAll[$i] = average($array); }
        //     foreach ($dataObj['numMovesPerChallenge'] as $i=>$numMoves) { $numMovesPerLevelAll[$i] []= $numMoves; }
        //     foreach ($numMovesPerLevelAll as $i=>$array) { $totalMovesPerLevelArray[$i] = average($array); }
        //     foreach ($dataObj['moveTypeChangesPerLevel'] as $i=>$moveTypeChanges) { $moveTypeChangesPerLevelAll[$i] []= $moveTypeChanges; }
        //     foreach ($moveTypeChangesPerLevelAll as $i=>$array) { $totalMoveTypeChangesPerLevelAll[$i] = average($array); }
        //     foreach ($dataObj['knobStdDevs'] as $i=>$knobStdDevs) { $knobStdDevsPerLevelAll[$i] []= $knobStdDevs; }
        //     foreach ($knobStdDevsPerLevelAll as $i=>$array) { $totalStdDevsPerLevelAll[$i] = average($array); }
        //     foreach ($dataObj['knobTotalAmts'] as $i=>$knobTotalAmts) { $knobTotalAmtsPerLevelAll[$i] []= $knobTotalAmts; }
        //     foreach ($knobTotalAmtsPerLevelAll as $i=>$array) { $totalKnobTotalsPerLevelAll[$i] = average($array); }
        //     foreach ($dataObj['knobAvgs'] as $i=>$knobAvg) { $knobAvgsPerLevelAll[$i] []= $knobAvg; }
        //     foreach ($knobAvgsPerLevelAll as $i=>$array) { $totalKnobAvgsPerLevelAll[$i] = average($array); }
        // }
        $totalTimeAll = array_sum($totalTimesPerLevelAll);
        $totalMovesAll = array_sum($totalMovesPerLevelArray);
        $totalMoveTypeChangesAll = array_sum($totalMoveTypeChangesPerLevelAll);
        //$totalStdDevsAll = sum($totalStdDevsPerLevelAll);
        $totalKnobTotalsAll = array_sum($totalKnobTotalsPerLevelAll);
        $totalKnobAvgsAll = array_sum($totalKnobAvgsPerLevelAll);
    
        $avgTimeAll = average($totalTimesPerLevelAll);
        $avgMovesAll = average($totalMovesPerLevelArray);
        $avgMoveTypeChangesAll = average($totalMoveTypeChangesPerLevelAll);
        //$avgStdDevAll = average($totalStdDevsPerLevelAll);
        $avgKnobTotalsAll = average($totalKnobTotalsPerLevelAll);
        $avgKnobAvgsAll = average($totalKnobAvgsPerLevelAll);
        
        $basicInfoAll = array('times'=>$totalTimesPerLevelAll, 'numMoves'=>$totalMovesPerLevelArray, 'moveTypeChanges'=>$totalMoveTypeChangesPerLevelAll,
            'knobStdDevs'=>$totalStdDevsPerLevelAll, 'totalMaxMin'=>$totalKnobTotalsPerLevelAll, 'avgMaxMin'=>$totalKnobAvgsPerLevelAll,
            'totalTime'=>$totalTimeAll, 'totalMoves'=>$totalMovesAll, 'totalMoveChanges'=>$totalMoveTypeChangesAll,
            'totalKnobTotals'=>$totalKnobTotalsAll, 'totalKnobAvgs'=>$totalKnobAvgsAll,
            'avgTime'=>$avgTimeAll, 'avgMoves'=>$avgMovesAll, 'avgMoveChanges'=>$avgMoveTypeChangesAll,
            'avgKnobTotals'=>$avgKnobTotalsAll, 'avgKnobAvgs'=>$avgKnobAvgsAll);
        // Get questions histogram data
        $questionsCorrect = array();
        $questionsAnswered = array();
        $questionAnswereds = array();
        $totalCorrect = 0;
        $totalAnswered = 0;
        foreach ($sessionIDs as $i=>$val) {
            $questionEvents = array();
            foreach ($sessionAttributes[$val] as $j=>$jval) {
                if ($jval['event_custom'] === 3) {
                    $questionEvents []= $jval;
                }
            }
            $numCorrect = 0;
            $numQuestions = count($questionEvents);
            for ($j = 0; $j < $numQuestions; $j++) {
                $jsonData = json_decode($questionEvents[$j]['event_data_complex'], true);
                $questionAnswereds[$i][$j] = $jsonData['answered'];
                if ($jsonData['answer'] === $jsonData['answered']) {
                    $numCorrect++;
                }
            }
            $totalCorrect += $numCorrect;
            $totalAnswered += $numQuestions;
            $questionsCorrect[$i] = $numCorrect;
            $questionsAnswered[$i] = $numQuestions;
        }
        $questionsAll = array('numsCorrect'=>$questionsCorrect, 'numsQuestions'=>$questionsAnswered);
        $questionsTotal = array('totalNumCorrect'=>$totalCorrect, 'totalNumQuestions'=>$totalAnswered);
    
        // Get moves histogram data
        $numMovesAll = array();
        foreach ($sessionIDs as $i=>$session) {
            $numMoves = 0;
            foreach ($sessionAttributes[$session] as $j=>$val) {
                if ($val['event_custom'] === 1 || $val['event_custom'] === 2) {
                    $numMoves++;
                }
            }
            $numMovesAll []= $numMoves;
        }
    
        // Get levels histogram data
        $numLevelsAll = array();
        foreach ($sessionIDs as $i=>$session) {
            $levelsCompleted = array();
            foreach ($sessionAttributes[$session] as $j=>$val) {
                if ($val['event'] === 'COMPLETE') {
                    $levelsCompleted[$val['level']] = true;
                }
            }
            $numLevelsAll []= count($levelsCompleted);
        }
    }

    // Get goals data for a session
    $goalsSingle = array();
    if (isset($reqSessionID, $reqLevel)) {
        $data = $basicInfoSingle;
        $dataObj = $data['dataObj'];
        $numMovesPerChallenge = $data['numMovesPerChallengeArray'][$reqLevel];
        
        $distanceToGoal1;
        $moveGoodness1;
        $absDistanceToGoal1;
        if (isset($numMovesPerChallenge)) {
            $absDistanceToGoal1 = array_fill(0, count($numMovesPerChallenge), 0);
            $distanceToGoal1 = array_fill(0, count($numMovesPerChallenge), 0); // this one is just -1/0/1
            $moveGoodness1 = array_fill(0, count($numMovesPerChallenge), 0); // an array of 0s
        }
        $moveNumbers = array();
        $cumulativeDistance1 = 0;
        $lastCloseness1;
    
        foreach ($numMovesPerChallenge as $i=>$val) {
            $dataJson = json_decode($dataObj['data'][$i], true);
            if ($dataObj['events'][$i] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE' || $dataJson['event_custom'] === 'ARROW_MOVE_RELEASE')) {
                if ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE') { // sliders have before and after closeness
                    if ($dataJson['end_closeness'] < $dataJson['begin_closeness']) $moveGoodness1[$i] = 1;
                    else if ($dataJson['end_closeness'] > $dataJson['begin_closeness']) $moveGoodness1[$i] = -1;
    
                    $lastCloseness1 = $dataJson['end_closeness'];
                } else { // arrow
                    if (!isset($lastCloseness1)) $lastCloseness1 = $dataJson['closeness'];
                    if ($dataJson['closeness'] < $lastCloseness1) $moveGoodness1[$i] = -1;
                    else if ($dataJson['closeness'] > $lastCloseness1) $moveGoodness1[$i] = 1;
    
                    $lastCloseness1 = $dataJson['closeness'];
                }
                if ($lastCloseness1 < 99999)
                    $absDistanceToGoal1[$i] = round($lastCloseness1, 2);
            }
            $moveNumbers[$i] = $i;
            $cumulativeDistance1 += $moveGoodness1[$i];
            $distanceToGoal1[$i] = $cumulativeDistance1;
        }
        $goalSlope1 = 0;
        $deltaX = 0;
        $deltaY = 0;
        if (count($moveNumbers) > 0) {
            $deltaX = $moveNumbers[count($moveNumbers)-1] - $moveNumbers[0];
        }
        if (count($distanceToGoal1) > 0) {
            $deltaY = $distanceToGoal1[count($distanceToGoal1)-1] - $distanceToGoal1[0];
        }

        if ($deltaX != 0) {
            $goalSlope1 = $deltaY / $deltaX;
        }
    
        $distanceToGoal2;
        $moveGoodness2;
        $absDistanceToGoal2;
        if (isset($numMovesPerChallenge)) {
            $absDistanceToGoal2 = array_fill(0, count($numMovesPerChallenge), 0);
            $distanceToGoal2 = array_fill(0, count($numMovesPerChallenge), 0); // this one is just -1/0/1
            $moveGoodness2 = array_fill(0, count($numMovesPerChallenge), 0); // an array of 0s
        }
        $cumulativeDistance2 = 0;
        $lastCloseness2;
    
        $graph_min_x = -50;
        $graph_max_x =  50;
        $graph_max_y =  50;
        $graph_max_offset = $graph_max_x;
        $graph_max_wavelength = $graph_max_x*2;
        $graph_max_amplitude = $graph_max_y*(3/5);
        $graph_default_offset = ($graph_min_x+$graph_max_x)/2;
        $graph_default_wavelength = (2+($graph_max_x*2))/2;
        $graph_default_amplitude = $graph_max_y/4;
        $lastCloseness = array();
        $thisCloseness = array();
        $lastCloseness['OFFSET']['left'] = $lastCloseness['OFFSET']['right'] = $graph_max_offset-$graph_default_offset;
        $lastCloseness['AMPLITUDE']['left'] = $lastCloseness['AMPLITUDE']['right'] = $graph_max_amplitude-$graph_default_amplitude;
        $lastCloseness['WAVELENGTH']['left'] = $lastCloseness['WAVELENGTH']['right'] = $graph_max_wavelength-$graph_default_wavelength;
    
        foreach ($numMovesPerChallenge as $i=>$val) {
            $dataJson = json_decode($dataObj['data'][$i], true);
            if ($dataObj['events'][$i] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE' || $dataJson['event_custom'] === 'ARROW_MOVE_RELEASE')) {
                if ($dataJson['slider'] ===  'AMPLITUDE') {
                    $thisCloseness[$dataJson['slider']][$dataJson['wave']] = $graph_max_amplitude-$dataJson['end_val'];
                } else if ($dataJson['slider'] === 'OFFSET') {
                    $thisCloseness[$dataJson['slider']][$dataJson['wave']] = $graph_max_offset-$dataJson['end_val'];
                } else if ($dataJson['slider'] === 'WAVELENGTH') {
                    $thisCloseness[$dataJson['slider']][$dataJson['wave']] = $graph_max_wavelength-$dataJson['end_val'];
                }
    
                if ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE') { // sliders have before and after closeness
                    if ($thisCloseness[$dataJson['slider']][$dataJson['wave']] < $lastCloseness[$dataJson['slider']][$dataJson['wave']]) $moveGoodness2[$i] = 1;
                    else if ($thisCloseness[$dataJson['slider']][$dataJson['wave']] > $lastCloseness[$dataJson['slider']][$dataJson['wave']]) $moveGoodness2[$i] = -1;
    
                    $lastCloseness[$dataJson['slider']][$dataJson['wave']] = $thisCloseness[$dataJson['slider']][$dataJson['wave']];
                } else { // arrow
                    if ($thisCloseness[$dataJson['slider']][$dataJson['wave']] < $lastCloseness[$dataJson['slider']][$dataJson['wave']]) $moveGoodness2[$i] = 1;
                    else if ($thisCloseness[$dataJson['slider']][$dataJson['wave']] > $lastCloseness[$dataJson['slider']][$dataJson['wave']]) $moveGoodness2[$i] = -1;
    
                    $lastCloseness[$dataJson['slider']][$dataJson['wave']] = $thisCloseness[$dataJson['slider']][$dataJson['wave']];
                }
                if ($thisCloseness[$dataJson['slider']][$dataJson['wave']] < 99999)
                    $absDistanceToGoal2[$i] = round($thisCloseness[$dataJson['slider']][$dataJson['wave']], 2);
            }
            $cumulativeDistance2 += $moveGoodness2[$i];
            $distanceToGoal2[$i] = $cumulativeDistance2;
        }
    
        $goalSlope2 = 0;
        $deltaY = 0;
        if (count($distanceToGoal2) > 0 ) {
            $deltaY = $distanceToGoal2[count($distanceToGoal2)-1] - $distanceToGoal2[0];
        }
        
        if ($deltaX != 0) {
            $goalSlope2 = $deltaY / $deltaX;
        }
        
        $goalsSingle = array('moveNumbers'=>$moveNumbers, 'distanceToGoal1'=>$distanceToGoal1, 'distanceToGoal2'=>$distanceToGoal2,
            'absDistanceToGoal1'=>$absDistanceToGoal1, 'absDistanceToGoal2'=>$absDistanceToGoal2, 'goalSlope1'=>$goalSlope1, 'goalSlope2'=>$goalSlope2, 'dataObj'=>$dataObj);
    }

    // Cluster stuff
    if (!isset($reqSessionID)) {
        $clusterLevel = 4;
        $columns = [
            [array_column($moveCol, $clusterLevel), 'numMovesPerChallenge', [216]],
            [array_column($avgCol, $clusterLevel), 'knobAvgs', []],
            [array_column($levelCol, $clusterLevel), 'levelTimes', [999999]],
            [array_column($typeCol, $clusterLevel), 'moveTypeChangesPerLevel', []],
            [array_column($stdCol, $clusterLevel), 'knobStdDevs', []],
            [array_column($totalCol, $clusterLevel), 'knobTotalAmts', []],
        ];

        $pcaData = [[], [], [], [], [], []];
        for ($i = 0; $i < count($columns[0][0]); $i++) {
            $good = true;
            for ($j = 0; $j < count($columns); $j++) {
                $val = $columns[$j][0][$i];
                if (!is_numeric($val) || in_array($val, $columns[$j][2])) {
                    $good = false;
                    break;
                }
            }
            if ($good) {
                for ($j = 0; $j < count($columns); $j++) {
                    $pcaData[$j][] = $columns[$j][0][$i];
                }
            }
        }
        // scale to 0..1
        for ($i = 0; $i < count($pcaData); $i++) {
            $min_val = null;
            $max_val = null;
            for ($j = 0; $j < count($pcaData[$i]); $j++) {
                $val = $pcaData[$i][$j];
                if (is_null($min_val) || $val < $min_val) $min_val = $val;
                if (is_null($max_val) || $val > $max_val) $max_val = $val;
            }
            $range = $max_val - $min_val;
            for ($j = 0; $j < count($pcaData[$i]); $j++) {
                $pcaData[$i][$j] = ($pcaData[$i][$j] - $min_val) / $range;
            }
        }

        $pca = new PCA\PCA($pcaData);
        $pca->changeDimension(2);
        $pca->applayingPca();
        $columns = $pca->getNewData();
        $columnNames = ['pca1', 'pca2'];

        $bestDunn = 0;
        $bestColumn1 = null;
        $bestColumn2 = null;
        $bestClusters = [];
        $clusterLevel = 0;

        for ($i = 0; $i < 2; $i++) {
            for ($j = $i + 1; $j < 2; $j++) {
                for ($k = 2; $k < 5; $k++) {
                    $space = new KMeans\Space(2);
                    $xs = $columns[$i];
                    $ys = $columns[$j];
                    foreach ($xs as $xi => $x) {
                        $y = $ys[$xi];
                        $space->addPoint([$x, $y]);
                    }
                    $clusters = $space->solve($k);

                    $minInterDist = null;
                    $maxIntraDist = null;
                    for ($ci = 0; $ci < count($clusters); $ci++) {
                        for ($cj = $ci + 1; $cj < count($clusters); $cj++) {
                            // use distance between centers for simplicity
                            $interDist = sqrt
                                ( (($clusters[$ci][0] - $clusters[$cj][0]) ** 2)
                                + (($clusters[$ci][1] - $clusters[$cj][1]) ** 2)
                                );
                            if (is_null($minInterDist) || $interDist < $minInterDist) {
                                $minInterDist = $interDist;
                            }
                        }
                    }
                    for ($ci = 0; $ci < count($clusters); $ci++) {
                        $cluster = $clusters[$ci];
                        $intraDist = null;
                        // fudge intracluster distance by finding max distance from center to a point
                        foreach ($cluster as $point) {
                            $pointDist = sqrt
                                ( (($point[0] - $cluster[0]) ** 2)
                                + (($point[1] - $cluster[1]) ** 2)
                                );
                            if (is_null($intraDist) || $pointDist > $intraDist) {
                                $intraDist = $pointDist;
                            }
                        }
                        if (is_null($maxIntraDist) || $intraDist > $maxIntraDist) {
                            $maxIntraDist = $intraDist;
                        }
                    }

                    $thisDunn = $minInterDist / $maxIntraDist;
                    if ($thisDunn > $bestDunn) {
                        $bestDunn = $thisDunn;
                        $bestColumn1 = $columnNames[$i];
                        $bestColumn2 = $columnNames[$j];
                        $bestClusters = $clusters;
                    }
                }
            }
        }

        $clusterPoints = [];
        foreach ($bestClusters as $cluster) {
            $points = [];
            foreach ($cluster->getIterator() as $point) {
                $points[] = [$point[0], $point[1]];
            }
            $clusterPoints[] = $points;
        }
    }

    // Linear regression stuff
    $linRegCoefficients = array();
    $regressionVars = array();
    if (!isset($reqSessionID)) {
        $predictors = array();
        $predictedGameComplete = array();
        $predictedLevel10 = array();
        $predictedLevel20 = array();

        $numSessionsTemp = count($sessionIDs);
        for ($i = 0; $i < $numSessionsTemp; $i++) {
            $predictors []= array($numMovesAll[$i], array_sum($typeCol[$i]), array_sum($levelCol[$i]), array_sum($avgCol[$i]));

            $gameComplete = ($numLevelsAll[$i] >= 30) ? 1 : 0;
            $level10Complete = ($numLevelsAll[$i] >= 9) ? 1 : 0;
            $level20Complete = ($numLevelsAll[$i] >= 20) ? 1 : 0;

            $predictedGameComplete []= array($gameComplete);
            $predictedLevel10 []= array($level10Complete);
            $predictedLevel20 []= array($level20Complete);
        }

        $regression1 = new \mnshankar\LinearRegression\Regression();
        $regression1->setX($predictors);
        $regression1->setY($predictedGameComplete);
        $regression1->compute();
        $linRegCoefficients['gameComplete'] = $regression1->getCoefficients();
        $regressionVars []= array($predictors, $predictedGameComplete);

        $regression2 = new \mnshankar\LinearRegression\Regression();
        $regression2->setX($predictors);
        $regression2->setY($predictedLevel10);
        $regression2->compute();
        $linRegCoefficients['level10'] = $regression2->getCoefficients();
        $regressionVars []= array($predictors, $predictedLevel10);

        $regression3 = new \mnshankar\LinearRegression\Regression();
        $regression3->setX($predictors);
        $regression3->setY($predictedLevel20);
        $regression3->compute();
        $linRegCoefficients['level20'] = $regression3->getCoefficients();
        $regressionVars []= array($predictors, $predictedLevel20);

        $predictorsQ = array();
        $predictedQ = array();
        foreach ($questionAnswereds as $i=>$val) {
            for ($j = 0; $j < 4; $j++) {
                if (isset($val[$j])) {
                    $numMoves = $numMovesAll[$i];
                    $numTypeChanges = array_sum($typeCol[$i]);
                    $time = array_sum($levelCol[$i]);
                    $minMax = array_sum($avgCol[$i]);
                    $predictorsQ[$j] []= array($numMovesAll[$i], array_sum($typeCol[$i]), array_sum($levelCol[$i]), array_sum($avgCol[$i]));
                    $q1a = ($val[$j] === 0) ? 1 : 0;
                    $q1b = ($val[$j] === 1) ? 1 : 0;
                    $q1c = ($val[$j] === 2) ? 1 : 0;
                    $q1d = ($val[$j] === 3) ? 1 : 0;
                    $predictedQ[$j][0] []= array($q1a);
                    $predictedQ[$j][1] []= array($q1b);
                    $predictedQ[$j][2] []= array($q1c);
                    $predictedQ[$j][3] []= array($q1d);
                }
            }
        }
        for ($i = 0; $i < 4; $i++) {
            for ($j = 0; $j < 4; $j++) {
                $regressionVars []= array($predictorsQ[$i], $predictedQ[$i][$j]);
                if (isset($predictorsQ[$i]) && count($predictorsQ[$i]) > 0) {
                    $regression = new \mnshankar\LinearRegression\Regression();
                    $regression->setX($predictorsQ[$i]);
                    $regression->setY($predictedQ[$i][$j]);
                    $regression->compute();
                    $pvalues = array_values($regression->getPValues());
                    if (!is_nan($pvalues[0])) {
                        $linRegCoefficients['q'.$i.$j] = $pvalues;
                    } else {
                        $linRegCoefficients['q'.$i.$j] = '-';
                    }
                } else {
                    $linRegCoefficients['q'.$i.$j] = '-';
                }                  
            }
        }
    }
    $totalNumSessions = getTotalNumSessions($_GET['gameID'], $db);

    $output = array('goalsSingle'=>$goalsSingle, 'numLevelsAll'=>$numLevelsAll, 'numMovesAll'=>$numMovesAll, 'questionsAll'=>$questionsAll, 'basicInfoAll'=>$basicInfoAll,
    'sessionsAndTimes'=>$sessionsAndTimes, 'basicInfoSingle'=>$basicInfoSingle, 'graphDataSingle'=>$graphDataSingle, 
    'questionsSingle'=>$questionsSingle, 'levels'=>$levels, 'numSessions'=>$numSessions, 'questionsTotal'=>$questionsTotal,
    'linRegCoefficients'=>$linRegCoefficients, 'clusters'=>array('col1'=>$bestColumn1, 'col2'=>$bestColumn2, 'clusters'=>$clusterPoints, 'dunn'=>$bestDunn),
    'totalNumSessions'=>$totalNumSessions, 'regressionVars'=>$regressionVars);

    // Return ALL the above information at once in a big array
    return $output;
}

$db->close();
?>
