<?php
// Indicate JSON data type 
header('Content-Type: application/json');

// Establish the database connection
include "database.php";
ini_set('memory_limit','256M');

$db = connectToDatabase(DBDeets::DB_NAME_DATA);
if ($db->connect_error) {
    http_response_code(500);
    die('{ "errMessage": "Failed to Connect to DB." }');
}

function average($arr) {
    $total = 0;
    $filtered = array_filter($arr, function ($value) { return $value != 0 && $value > 0 && $value !== "-"; });
    foreach ($filtered as $value) {
        $total += $value;
    }
    $length = count($filtered);
    return ($length !== 0) ? $total / $length : 0;
}

function sum($arr) {
    $total = 0;
    foreach ($arr as $value) {
        if ($value !== "-")
            $total += $value;
    }
    return $total;
}

function getNumSessions($gameID, $db) {
    $query = "SELECT COUNT(DISTINCT session_id) FROM log WHERE app_id=?;";
    $stmt = simpleQueryParam($db, $query, "s", $gameID);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($numSessions)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    if(!$stmt->fetch()) {
        http_response_code(404);
        die('{ "errMessage": "Resource not found." }');                
    }
    $stmt->close();
    return $numSessions;
}

function getLevels($gameID, $db) {
    $query = "SELECT DISTINCT level FROM log WHERE app_id=? ORDER BY level ASC;";
    $stmt = simpleQueryParam($db, $query, "s", $gameID);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($level)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    while($stmt->fetch()) {
        $levels[] = $level;
    }
    $stmt->close();
    return $levels;
}

function getSessionsAndTimes($gameID, $db) {
    $query = "SELECT DISTINCT q.session_id, q.cl_time FROM (SELECT session_id, MIN(client_time) as cl_time FROM log WHERE app_id=? GROUP BY session_id) q ORDER BY q.cl_time;";
    $stmt = simpleQueryParam($db, $query, "s", $gameID);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($session, $time)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    while($stmt->fetch()) {
        $sessions[] = $session;
        $times[] = $time;
    }
    $stmt->close();
    return array("sessions"=>$sessions, "times"=>$times);
}

function getQuestions($gameID, $sessionID, $db) {
    $query = "SELECT event_data_complex FROM log WHERE app_id=? AND session_id=? AND event_custom=?;";
    $paramArray = array($gameID, $sessionID, 3);
    $stmt = queryMultiParam($db, $query, "ssi", $paramArray);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($dataComplex)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    $data = array();
    while($stmt->fetch()) {
        $data[] = $dataComplex;
    }
    $numCorrect = 0;
    $numQuestions = count($data);
    for ($i = 0; $i < $numQuestions; $i++) {
        $jsonData = json_decode($data[$i], true);
        if ($jsonData["answer"] === $jsonData["answered"]) {
            $numCorrect++;
        }
    }
    $stmt->close();
    return array("numCorrect"=>$numCorrect, "numQuestions"=>$numQuestions);
}

function getGraphData($gameID, $sessionID, $level, $db) {
    $query = "SELECT event, event_data_complex, client_time FROM log WHERE app_id=? AND session_id=? AND level=? AND (event_custom=? OR event_custom=? OR event=?);";
    $paramArray = array($gameID, $sessionID, $level, 1, 2, "SUCCEED");
    $stmt = queryMultiParam($db, $query, "ssiiis", $paramArray);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($event, $singleData, $singleTime)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    $times = array();
    $eventData = array();
    $events = array();
    while($stmt->fetch()) {
        $events[] = $event;
        $times[] = $singleTime;
        $eventData[] = $singleData;
    }
    $stmt->close();
    return array("events"=>$events, "times"=>$times, "event_data"=>$eventData);
}

function getBasicInfo($gameID, $sessionID, $db) {
    $query = "SELECT event_data_complex, client_time, level, event FROM log WHERE app_id=? AND session_id=?;";
    $paramArray = array($gameID, $sessionID);
    $stmt = queryMultiParam($db, $query, "ss", $paramArray);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($singleData, $singleTime, $singleLevel, $singleEvent)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    while($stmt->fetch()) {
        $times[] = $singleTime;
        $eventData[] = $singleData;
        $levels[] = $singleLevel;
        $events[] = $singleEvent;
    }
    $stmt->close();
    // TODO: add averages and totals in here
    return array("times"=>$times, "event_data"=>$eventData, "levels"=>$levels, "events"=>$events);
}

function parseBasicInfo($data, $gameID, $db) {
    if ($gameID === "WAVES") {
        $dataObj = array("data"=>$data["event_data"], "times"=>$data["times"], "events"=>$data["events"], "levels"=>$data["levels"]);
        // Variables holding "basic features" for waves game, filled by database data
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

        $numLevels = count(array_unique($dataObj["levels"]));
        
        if (isset($dataObj["times"])) {
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
            foreach ($dataObj["levels"] as $i) {
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

            for ($i = 0; $i < count($dataObj["times"]); $i++) {
                if (!isset($endIndices[$dataObj["levels"][$i]])) {
                    $dataJson = json_decode($dataObj["data"][$i], true);
                    if ($dataObj["events"][$i] === "BEGIN") {
                        if (!isset($startIndices[$dataObj["levels"][$i]])) { // check this space isn't filled by a previous attempt on the same level
                            $startIndices[$dataObj["levels"][$i]] = $i;
                        }
                    } else if ($dataObj["events"][$i] === "COMPLETE") {
                        if (!isset($endIndices[$dataObj["levels"][$i]])) {
                            $endIndices[$dataObj["levels"][$i]] = $i;
                        }
                    } else if ($dataObj["events"][$i] === "CUSTOM" && ($dataJson["event_custom"] === "SLIDER_MOVE_RELEASE" || $dataJson["event_custom"] === "ARROW_MOVE_RELEASE")) {
                        if ($lastSlider !== $dataJson["slider"]) {
                            if (!isset($moveTypeChangesPerLevel[$dataObj["levels"][$i]])) $moveTypeChangesPerLevel[$dataObj["levels"][$i]] = 0;
                            $moveTypeChangesPerLevel[$dataObj["levels"][$i]]++;
                        }
                        $lastSlider = $dataJson["slider"];
                        $numMovesPerChallenge[$dataObj["levels"][$i]] []= $i;
                        if ($dataJson["event_custom"] === "SLIDER_MOVE_RELEASE") { // arrows don't have std devs
                            //if (!isset($knobNumStdDevs[$dataObj["levels"][$i]])) $knobNumStdDevs[$dataObj["levels"][$i]] = 0;
                            $knobNumStdDevs[$dataObj["levels"][$i]]++;
                            //if (!isset($knobStdDevs[$dataObj["levels"][$i]])) $knobStdDevs[$dataObj["levels"][$i]] = 0;
                            $knobStdDevs[$dataObj["levels"][$i]] += $dataJson["stdev_val"];
                            //if (!isset($knobAmts[$dataObj["levels"][$i]])) $knobAmts[$dataObj["levels"][$i]] = 0;
                            $knobAmts[$dataObj["levels"][$i]] += ($dataJson["max_val"]-$dataJson["min_val"]);
                        }
                    }
                }
            }
            
            foreach ($startIndices as $i=>$value) {
                if (isset($startIndices[$i])) {
                    $levelTime = "-";
                    if (isset($dataObj["times"][$endIndices[$i]], $dataObj["times"][$startIndices[$i]])) {
                        $levelStartTime = new DateTime($dataObj["times"][$startIndices[$i]]);
                        $levelEndTime = new DateTime($dataObj["times"][$endIndices[$i]]);
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
            $avgTime = $totalTime / $numLevels;
            $avgMoves = $totalMoves / $numLevels;
            $moveTypeChangesAvg = $moveTypeChangesTotal / $numLevels;
            $knobAmtsAvg = $knobAmtsTotal / $numLevels;
            $knobSumAvg = $knobSumTotal / $numLevels;
        }
        $numMoves = array();
        $filteredNumMoves = array_filter($numMovesPerChallenge, function ($value) { return isset($value) && !is_null($value); });
        foreach ($filteredNumMoves as $j=>$value) {
            $numMoves[$j] = count($numMovesPerChallenge[$j]);
        }
        return array("levelTimes"=>$levelTimes, "avgTime"=>$avgTime, "totalTime"=>$totalTime, "numMovesPerChallenge"=>$numMoves, "totalMoves"=>$totalMoves, "avgMoves"=>$avgMoves,
        "moveTypeChangesPerLevel"=>$moveTypeChangesPerLevel, "moveTypeChangesTotal"=>$moveTypeChangesTotal, "moveTypeChangesAvg"=>$moveTypeChangesAvg, "knobStdDevs"=>$avgKnobStdDevs,
        "knobNumStdDevs"=>$knobNumStdDevs, "knobAvgs"=>$knobAvgs, "knobAmtsTotalAvg"=>$knobAmtsTotal, "knobAmtsAvgAvg"=>$knobAmtsAvg, "knobTotalAmts"=>$knobAmts, "knobSumTotal"=>$knobSumTotal,
        "knobTotalAvg"=>$knobSumAvg, "numMovesPerChallengeArray"=>$numMovesPerChallenge, "dataObj"=>$dataObj);
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
    } else {
        return null;
    }
}

function getFilteredSessionsAndTimes($gameID, $minMoves, $minLevels, $minQuestions, $db) {
    $startDate = new DateTime($_GET['startDate']);
    $endDate = new DateTime($_GET['endDate']);
    $query = "SELECT level, event, event_custom, session_id, client_time FROM log WHERE app_id=? ORDER BY session_id;";
    $paramArray = array($gameID);
    $stmt = queryMultiParam($db, $query, "s", $paramArray);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($level, $event, $event_custom, $sessionID, $time)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    while($stmt->fetch()) {
        $levels[] = $level;
        $events[] = $event;
        $event_customs[] = $event_custom;
        $sessionIDs[] = $sessionID;
        $times[] = $time;
    }

    $eventsPerSession = array_count_values($sessionIDs);
    $filteredSessions = [];
    $filteredSessionsMoves = [];
    $filteredSessionsQuestions = [];
    $filteredSessionsLevels = [];
    $filteredSessionsTimes = [];
    $uniqueSessionIDs = array_unique($sessionIDs);

    foreach ($uniqueSessionIDs as $index=>$session) {
        $numMoves = 0;
        $numLevels = 0;
        $numQuestions = 0;
        $date = new DateTime($times[$index]);
        $levelsCompleted = array();
        for ($i = 0; $i < $eventsPerSession[$session]; $i++) {
            if ($events[$index + $i] === "COMPLETE" && !in_array($levels[$index+$i], $levelsCompleted)) {
                $numLevels++;
                $levelsCompleted []= $levels[$index+$i];
            } else if ($events[$index + $i] === "CUSTOM") {
                if ($event_customs[$index + $i] === 1 || $event_customs[$index + $i] === 2) {
                    $numMoves++;
                } else if ($event_customs[$index + $i] === 3) {
                    $numQuestions++;
                }
            }
        }
        
        if ($numMoves >= $minMoves && $numLevels >= $minLevels && $numQuestions >= $minQuestions &&
                $startDate <= $date && $date <= $endDate) {
            $filteredSessions[] = $session;
            $filteredSessionsMoves[] = $numMoves;
            $filteredSessionsQuestions[] = $numQuestions;
            $filteredSessionsLevels[] = $numLevels;
            $filteredSessionsTimes[] = $times[$index];
        }
    }

    array_multisort($filteredSessionsTimes, SORT_ASC, $filteredSessions, SORT_ASC);
    $output = array("sessions"=>$filteredSessions, "times"=>$filteredSessionsTimes);
    return $output;
}

function getBasicInfoAll($gameID, $isFiltered, $db) {
    $sessionIDs;
    $maxSessions;

    if ($isFiltered) {
        $maxSessions = $_GET["maxSessions"];
        $sessionIDs = getFilteredSessionsAndTimes($gameID, $_GET["minMoves"], $_GET["minLevels"], $_GET["minQuestions"], $db)["sessions"];
    } else {
        $maxSessions = 100;
        $sessionIDs = getSessionsAndTimes($gameID, $db)["sessions"];
    }

    $numLevels = count(getLevels($gameID, $db));
    $numSessions = count($sessionIDs);
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
    $k = 0;
    foreach ($sessionIDs as $i=>$session) {
        $k++;
        $allData[$session] = parseBasicInfo(getBasicInfo($gameID, $session, $db), $gameID, $db);
        if ($k > $maxSessions) break;
    }

    // loop through all the sessions we got above and add their variables to totals
    foreach ($allData as $index=>$dataObj) {
        foreach ($dataObj["levelTimes"] as $i=>$levelTime) { $levelTimesPerLevelAll[$i] []= $levelTime; }
        foreach ($levelTimesPerLevelAll as $i=>$array) { $totalTimesPerLevelAll[$i] = average($array); }

        foreach ($dataObj["numMovesPerChallenge"] as $i=>$numMoves) { $numMovesPerLevelAll[$i] []= $numMoves; }
        foreach ($numMovesPerLevelAll as $i=>$array) { $totalMovesPerLevelArray[$i] = average($array); }

        foreach ($dataObj["moveTypeChangesPerLevel"] as $i=>$moveTypeChanges) { $moveTypeChangesPerLevelAll[$i] []= $moveTypeChanges; }
        foreach ($moveTypeChangesPerLevelAll as $i=>$array) { $totalMoveTypeChangesPerLevelAll[$i] = average($array); }

        foreach ($dataObj["knobStdDevs"] as $i=>$knobStdDevs) { $knobStdDevsPerLevelAll[$i] []= $knobStdDevs; }
        foreach ($knobStdDevsPerLevelAll as $i=>$array) { $totalStdDevsPerLevelAll[$i] = average($array); }

        foreach ($dataObj["knobTotalAmts"] as $i=>$knobTotalAmts) { $knobTotalAmtsPerLevelAll[$i] []= $knobTotalAmts; }
        foreach ($knobTotalAmtsPerLevelAll as $i=>$array) { $totalKnobTotalsPerLevelAll[$i] = average($array); }

        foreach ($dataObj["knobAvgs"] as $i=>$knobAvg) { $knobAvgsPerLevelAll[$i] []= $knobAvg; }
        foreach ($knobAvgsPerLevelAll as $i=>$array) { $totalKnobAvgsPerLevelAll[$i] = average($array); }
    }
    $totalTimeAll = sum($totalTimesPerLevelAll);
    $totalMovesAll = sum($totalMovesPerLevelArray);
    $totalMoveTypeChangesAll = sum($totalMoveTypeChangesPerLevelAll);
    //$totalStdDevsAll = sum($totalStdDevsPerLevelAll);
    $totalKnobTotalsAll = sum($totalKnobTotalsPerLevelAll);
    $totalKnobAvgsAll = sum($totalKnobAvgsPerLevelAll);

    $avgTimeAll = average($totalTimesPerLevelAll);
    $avgMovesAll = average($totalMovesPerLevelArray);
    $avgMoveTypeChangesAll = average($totalMoveTypeChangesPerLevelAll);
    //$avgStdDevAll = average($totalStdDevsPerLevelAll);
    $avgKnobTotalsAll = average($totalKnobTotalsPerLevelAll);
    $avgKnobAvgsAll = average($totalKnobAvgsPerLevelAll);
    
    $output = array("times"=>$totalTimesPerLevelAll, "numMoves"=>$totalMovesPerLevelArray, "moveTypeChanges"=>$totalMoveTypeChangesPerLevelAll,
        "knobStdDevs"=>$totalStdDevsPerLevelAll, "totalMaxMin"=>$totalKnobTotalsPerLevelAll, "avgMaxMin"=>$totalKnobAvgsPerLevelAll,
        "totalTime"=>$totalTimeAll, "totalMoves"=>$totalMovesAll, "totalMoveChanges"=>$totalMoveTypeChangesAll,
        "totalKnobTotals"=>$totalKnobTotalsAll, "totalKnobAvgs"=>$totalKnobAvgsAll,
        "avgTime"=>$avgTimeAll, "avgMoves"=>$avgMovesAll, "avgMoveChanges"=>$avgMoveTypeChangesAll,
        "avgKnobTotals"=>$avgKnobTotalsAll, "avgKnobAvgs"=>$avgKnobAvgsAll);
    return $output;
}

function getQuestionsHistogram($gameID, $minMoves, $minLevels, $minQuestions, $db) {
    $maxSessions = 100;
    if (isset($_GET['maxSessions'])) {
        $maxSessions = $_GET['maxSessions'];
    }

    $sessionIDs;
    if (isset($minMoves)) { // filtered
        $sessionIDs = getFilteredSessionsAndTimes($gameID, $minMoves, $minLevels, $minQuestions, $db)["sessions"];
    } else { // not filtered
        $sessionIDs = getSessionsAndTimes($gameID, $db)["sessions"];
    }
    
    $numSessions = min($maxSessions, count($sessionIDs));
    $questionsCorrect = array();
    $questionsAnswered = array();

    for ($i = 0; $i < $numSessions; $i++) {
        $questions = getQuestions($gameID, $sessionIDs[$i], $db);
        $questionsCorrect[$i] = $questions["numCorrect"];
        $questionsAnswered[$i] = $questions["numQuestions"];
    }

    return array("numsCorrect"=>$questionsCorrect, "numsQuestions"=>$questionsAnswered);
}

function getMovesHistogram($gameID, $minMoves, $minLevels, $minQuestions, $db) {
    $maxSessions = 100;
    if (isset($_GET['maxSessions'])) {
        $maxSessions = $_GET['maxSessions'];
    }

    $sessionIDs;
    if (isset($minMoves)) { // filtered
        $sessionIDs = getFilteredSessionsAndTimes($gameID, $minMoves, $minLevels, $minQuestions, $db)["sessions"];
    } else { // not filtered
        $sessionIDs = getSessionsAndTimes($gameID, $db)["sessions"];
    }

    $numSessions = min($maxSessions, count($sessionIDs));
    array_walk($sessionIDs, 'intval');
    $ids = implode(',', $sessionIDs);

    $query = "SELECT session_id, count(*) FROM log WHERE app_id=? AND (event_custom=1 OR event_custom=2) AND session_id IN ($ids) GROUP BY session_id LIMIT ?;";
    $paramArray = array($gameID, $numSessions);
    $stmt = queryMultiParam($db, $query, "si", $paramArray);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($sessionID, $count)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }

    $counts = array();
    // Fetch and display the results
    while($stmt->fetch()) {
        $counts []= $count;
    }

    $output = array("numMoves"=>$counts);
    return $output;
}

function getLevelsHistogram($gameID, $minMoves, $minLevels, $minQuestions, $db) {
    $maxSessions = 100;
    if (isset($_GET['maxSessions'])) {
        $maxSessions = $_GET['maxSessions'];
    }

    $sessionIDs;
    if (isset($minMoves)) { // filtered
        $sessionIDs = array_slice(getFilteredSessionsAndTimes($gameID, $minMoves, $minLevels, $minQuestions, $db)["sessions"], 0, $maxSessions, true);
    } else { // not filtered
        $sessionIDs = array_slice(getSessionsAndTimes($gameID, $db)["sessions"], 0, $maxSessions, true);
    }

    $numSessions = min($maxSessions, count($sessionIDs));
    array_walk($sessionIDs, 'intval');
    $ids = implode(',', $sessionIDs);

    $query = "SELECT q.level, count(q.session_id), session_id FROM (SELECT DISTINCT level, session_id FROM log WHERE app_id=? AND event=? AND session_id IN ($ids)) q GROUP BY q.session_id;";
    $paramArray = array($gameID, "COMPLETE");
    $stmt = queryMultiParam($db, $query, "ss", $paramArray);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($level, $count, $session)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }

    $counts = array();
    $levels = array();
    $sessions = array();
    // Fetch and display the results
    while($stmt->fetch()) {
        $counts []= $count;
        $levels []= $level;
        $sessions []= $session;
    }

    $zeroLevelSessions = array_diff($sessionIDs, $sessions);
    for ($i = 0; $i < count($zeroLevelSessions); $i++) {
        $counts []= 0;
    }

    $output = array("numLevels"=>$counts);
    return $output;
}

function getGoalsData($gameID, $sessionID, $level, $db) {
    $data = parseBasicInfo(getBasicInfo($gameID, $sessionID, $db), $gameID, $db);
    $dataObj = $data["dataObj"];
    $numMovesPerChallenge = $data["numMovesPerChallengeArray"][$level];
    
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
        $dataJson = json_decode($dataObj["data"][$i], true);
        if ($dataObj["events"][$i] == "CUSTOM" && ($dataJson["event_custom"] == 'SLIDER_MOVE_RELEASE' || $dataJson["event_custom"] == 'ARROW_MOVE_RELEASE')) {
            if ($dataJson["event_custom"] == "SLIDER_MOVE_RELEASE") { // sliders have before and after closeness
                if ($dataJson["end_closeness"] < $dataJson["begin_closeness"]) $moveGoodness1[$i] = 1;
                else if ($dataJson["end_closeness"] > $dataJson["begin_closeness"]) $moveGoodness1[$i] = -1;

                $lastCloseness1 = $dataJson["end_closeness"];
            } else { // arrow
                if (!isset($lastCloseness1)) $lastCloseness1 = $dataJson["closeness"];
                if ($dataJson["closeness"] < $lastCloseness1) $moveGoodness1[$i] = -1;
                else if ($dataJson["closeness"] > $lastCloseness1) $moveGoodness1[$i] = 1;

                $lastCloseness1 = $dataJson["closeness"];
            }
            if ($lastCloseness1 < 99999)
                $absDistanceToGoal1[$i] = round($lastCloseness1, 2);
        }
        $moveNumbers[$i] = $i;
        $cumulativeDistance1 += $moveGoodness1[$i];
        $distanceToGoal1[$i] = $cumulativeDistance1;
    }
    $goalSlope1 = 0;
    $deltaX = $moveNumbers[count($moveNumbers)-1] - $moveNumbers[0];
    $deltaY = $distanceToGoal1[count($distanceToGoal1)-1] - $distanceToGoal1[0];
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
        $dataJson = json_decode($dataObj["data"][$i], true);
        if ($dataObj["events"][$i] == 'CUSTOM' && ($dataJson["event_custom"] == 'SLIDER_MOVE_RELEASE' || $dataJson["event_custom"] == 'ARROW_MOVE_RELEASE')) {
            if ($dataJson["slider"] ==  'AMPLITUDE') {
                $thisCloseness[$dataJson["slider"]][$dataJson["wave"]] = $graph_max_amplitude-$dataJson["end_val"];
            } else if ($dataJson["slider"] == 'OFFSET') {
                $thisCloseness[$dataJson["slider"]][$dataJson["wave"]] = $graph_max_offset-$dataJson["end_val"];
            } else if ($dataJson["slider"] == 'WAVELENGTH') {
                $thisCloseness[$dataJson["slider"]][$dataJson["wave"]] = $graph_max_wavelength-$dataJson["end_val"];
            }

            if ($dataJson["event_custom"] == 'SLIDER_MOVE_RELEASE') { // sliders have before and after closeness
                if ($thisCloseness[$dataJson["slider"]][$dataJson["wave"]] < $lastCloseness[$dataJson["slider"]][$dataJson["wave"]]) $moveGoodness2[$i] = 1;
                else if ($thisCloseness[$dataJson["slider"]][$dataJson["wave"]] > $lastCloseness[$dataJson["slider"]][$dataJson["wave"]]) $moveGoodness2[$i] = -1;

                $lastCloseness[$dataJson["slider"]][$dataJson["wave"]] = $thisCloseness[$dataJson["slider"]][$dataJson["wave"]];
            } else { // arrow
                if ($thisCloseness[$dataJson["slider"]][$dataJson["wave"]] < $lastCloseness[$dataJson["slider"]][$dataJson["wave"]]) $moveGoodness2[$i] = 1;
                else if ($thisCloseness[$dataJson["slider"]][$dataJson["wave"]] > $lastCloseness[$dataJson["slider"]][$dataJson["wave"]]) $moveGoodness2[$i] = -1;

                $lastCloseness[$dataJson["slider"]][$dataJson["wave"]] = $thisCloseness[$dataJson["slider"]][$dataJson["wave"]];
            }
            if ($thisCloseness[$dataJson["slider"]][$dataJson["wave"]] < 99999)
                $absDistanceToGoal2[$i] = round($thisCloseness[$dataJson["slider"]][$dataJson["wave"]], 2);
        }
        $cumulativeDistance2 += $moveGoodness2[$i];
        $distanceToGoal2[$i] = $cumulativeDistance2;
    }

    $goalSlope2 = 0;
    $deltaY = $distanceToGoal2[count($distanceToGoal2)-1] - $distanceToGoal2[0];
    
    if ($deltaX != 0) {
        $goalSlope2 = $deltaY / $deltaX;
    }
    
    $output = array("moveNumbers"=>$moveNumbers, "distanceToGoal1"=>$distanceToGoal1, "distanceToGoal2"=>$distanceToGoal2,
        "absDistanceToGoal1"=>$absDistanceToGoal1, "absDistanceToGoal2"=>$absDistanceToGoal2, "goalSlope1"=>$goalSlope1, "goalSlope2"=>$goalSlope2, "dataObj"=>$dataObj);
    return $output;
}

if (!isset($_GET['isAll'])) {
    if (!isset($_GET['isHistogram']) && !isset($_GET['minMoves']) && !isset($_GET['minQuestions']) && !isset($_GET['minLevels'])) {
        // Return number of sessions for a given game and return those session ids
        if (!isset($_GET['isBasicFeatures']) && !isset($_GET['sessionID']) && !isset($_GET['isGoals']) && isset($_GET['gameID'])) {
            $numSessions = getNumSessions($_GET['gameID'], $db);
            $levels = getLevels($_GET['gameID'], $db);
            $sessionsAndTimes = getSessionsAndTimes($_GET['gameID'], $db);
            $data = array("numSessions"=>$numSessions, "levels"=>$levels, "sessions"=>$sessionsAndTimes["sessions"], "times"=>$sessionsAndTimes["times"]);
            echo json_encode($data);
        } else 
    
        // Return number of questions and number correct for a given session id and game
        if (!isset($_GET['isBasicFeatures']) && !isset($_GET['level']) && !isset($_GET['isGoals']) && isset($_GET['sessionID'], $_GET['gameID'])) {
            $data = getQuestions($_GET['gameID'], $_GET['sessionID'], $db);
            echo json_encode($data);
        } else
    
        // Return graphing data
        if (!isset($_GET['isBasicFeatures']) && !isset($_GET['isGoals']) && isset($_GET['gameID'], $_GET['sessionID'], $_GET['level'])) {
            $data = getGraphData($_GET['gameID'], $_GET['sessionID'], $_GET['level'], $db);
            echo json_encode($data);
        } else
    
        // Return basic information
        if (isset($_GET['isBasicFeatures'], $_GET['gameID'], $_GET['sessionID'])) {
            $data = parseBasicInfo(getBasicInfo($_GET['gameID'], $_GET['sessionID'], $db), $_GET['gameID'], $db);
            echo json_encode($data);
        } else

        // Return goals information
        if (isset($_GET['gameID'], $_GET['isGoals'], $_GET['sessionID'], $_GET['level'])) {
            $data = getGoalsData($_GET['gameID'], $_GET['sessionID'], $_GET['level'], $db);
            echo json_encode($data);
        }
        
        else {
            echo "{ 'error': 'Invalid set of parameters provided.' }";
            file_put_contents("log.log", print_r($_GET, true));
        }
    } else {
        $minMoves = $_GET['minMoves'];
        $minLevels = $_GET['minLevels'];
        $minQuestions = $_GET['minQuestions'];
        $gameID = $_GET['gameID'];

        $output = getFilteredSessionsAndTimes($gameID, $minMoves, $minLevels, $minQuestions, $db);
    
        echo json_encode($output);
    }
} else { // The same functions as above but for all sessions
    // Return number of questions and number correct for a given game
    if (!isset($_GET['isHistogram']) && !isset($_GET['isAggregate']) && !isset($_GET['isBasicFeatures']) && !isset($_GET['level']) && isset($_GET['gameID'])) {
        // This query is a lot faster than looping through getQuestions for all sessions
        $gameID = $_GET['gameID'];
        $maxSessions;
        if (isset($_GET['maxSessions'])) {
            $maxSessions = $_GET['maxSessions'];
        } else {
            $maxSessions = 100;
        }
        $query = "SELECT q.event_data_complex, q.session_id FROM
        (SELECT event_data_complex, session_id FROM log WHERE app_id=? AND event_custom=? GROUP BY session_id LIMIT ?) q
        ORDER BY q.session_id;";
        $paramArray = array($gameID, 3, $maxSessions);
        $stmt = queryMultiParam($db, $query, "sii", $paramArray);
        if($stmt === NULL) {
            http_response_code(500);
            die('{ "errMessage": "Error running query." }');
        }
        // Bind variables to the results
        if (!$stmt->bind_result($dataComplex, $sessionID)) {
            http_response_code(500);
            die('{ "errMessage": "Failed to bind to results." }');
        }
        // Fetch and display the results
        while($stmt->fetch()) {
            $data[] = $dataComplex;
            $sessionIDs[] = $sessionID;
        }
        $totalNumCorrect = 0;
        $totalNumQuestions = 0;

        $filteredSessions = $sessionIDs;
        if (isset($_GET['minMoves'])) {
            $filteredSessions = array_values(array_intersect(getFilteredSessionsAndTimes($gameID, $_GET['minMoves'], $_GET['minLevels'], $_GET['minQuestions'], $db)["sessions"], $sessionIDs));
        }
        $numSessions = count($filteredSessions);
        
        $index = 0;
        for ($i = 0; $i < $numSessions; $i++) {
            $questions = getQuestions($gameID, $filteredSessions[$i], $db);
            $totalNumCorrect += $questions["numCorrect"];
            $totalNumQuestions += $questions["numQuestions"];
        }
        $stmt->close();
        $output = array("totalNumCorrect"=>$totalNumCorrect, "totalNumQuestions"=>$totalNumQuestions);
        echo json_encode($output);
    } else

    // Return basic information
    if (!isset($_GET['isHistogram']) && isset($_GET['isAggregate'], $_GET['isBasicFeatures'], $_GET['gameID'])) {
        $gameID = $_GET["gameID"];
        $output = getBasicInfoAll($gameID, isset($_GET['isFiltered']), $db);
        
        echo json_encode($output);
    } else

    // Return histogram information
    if (isset($_GET['isHistogram'], $_GET['isAggregate'], $_GET['gameID']) && !isset($_GET['isBasicFeatures'])) {
        $gameID = $_GET['gameID'];
        if (isset($_GET['minMoves'])) {
            $questions = getQuestionsHistogram($gameID, $_GET['minMoves'], $_GET['minLevels'], $_GET['minQuestions'], $db);
            $moves = getMovesHistogram($gameID, $_GET['minMoves'], $_GET['minLevels'], $_GET['minQuestions'], $db);
            $levels = getLevelsHistogram($gameID, $_GET['minMoves'], $_GET['minLevels'], $_GET['minQuestions'], $db);
        } else {
            $questions = getQuestionsHistogram($gameID, null, null, null, $db);
            $moves = getMovesHistogram($gameID, null, null, null, $db);
            $levels = getLevelsHistogram($gameID, null, null, null, $db);
        }

        $output = array_merge($questions, $moves, $levels);
        echo json_encode($output);
    }
}

// Close the database connection
$db->close();
?>
