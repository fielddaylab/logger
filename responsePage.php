<?php
// Indicate JSON data type 
header('Content-Type: application/json');

// Establish the database connection
include "database.php";
$db = connectToDatabase(DBDeets::DB_NAME_DATA);
if ($db->connect_error) {
    http_response_code(500);
    die('{ "errMessage": "Failed to Connect to DB." }');
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
    $query = "SELECT event_data_complex, client_time FROM log WHERE app_id=? AND session_id=? AND level=? AND (event_custom=? OR event_custom=?);";
    $paramArray = array($gameID, $sessionID, $level, 1, 2);
    $stmt = queryMultiParam($db, $query, "ssiii", $paramArray);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt->bind_result($singleData, $singleTime)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    while($stmt->fetch()) {
        $times[] = $singleTime;
        $eventData[] = $singleData;
    }
    $stmt->close();
    return array("times"=>$times, "event_data"=>$eventData);
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
            for ($i = 0; $i < count($dataObj["times"]); $i++) {
                if (!isset($endIndices[$dataObj["levels"][$i]])) {
                    $dataJson = json_decode($dataObj["data"][$i], true);
                    if (isset($dataJson)) {
                        if ($dataJson["event_custom"] !== 'SLIDER_MOVE_RELEASE' && $dataJson["event_custom"] !== 'ARROW_MOVE_RELEASE') {
                            $indicesToSplice[$dataObj["levels"][$i]] []= $i;
                        }
                    }
                    if ($dataObj["events"][$i] === 'BEGIN') {
                        if (!isset($startIndices[$dataObj["levels"][$i]])) { // check this space isn't filled by a previous attempt on the same level
                            $startIndices[$dataObj["levels"][$i]] = $i;
                        }
                    } else if ($dataObj["events"][$i] === 'COMPLETE') {
                        if (!isset($endIndices[$dataObj["levels"][$i]])) {
                            $endIndices[$dataObj["levels"][$i]] = $i;
                        }
                    } else if ($dataObj["events"][$i] === 'CUSTOM' && ($dataJson["event_custom"] === 'SLIDER_MOVE_RELEASE' || $dataJson["event_custom"] === 'ARROW_MOVE_RELEASE')) {
                        if ($lastSlider !== $dataJson["slider"]) {
                            if (!isset($moveTypeChangesPerLevel[$dataObj["levels"][$i]])) $moveTypeChangesPerLevel[$dataObj["levels"][$i]] = 0;
                            $moveTypeChangesPerLevel[$dataObj["levels"][$i]]++;
                        }
                        $lastSlider = $dataJson["slider"];
                        $numMovesPerChallenge[$dataObj["levels"][$i]] []= $i;
                        if ($dataJson["event_custom"] === 'SLIDER_MOVE_RELEASE') { // arrows don't have std devs
                            if (!isset($knobNumStdDevs[$dataObj["levels"][$i]])) $knobNumStdDevs[$dataObj["levels"][$i]] = 0;
                            $knobNumStdDevs[$dataObj["levels"][$i]]++;
                            if (!isset($knobStdDevs[$dataObj["levels"][$i]])) $knobStdDevs[$dataObj["levels"][$i]] = 0;
                            $knobStdDevs[$dataObj["levels"][$i]] += $dataJson["stdev_val"];
                            if (!isset($knobAmts[$dataObj["levels"][$i]])) $knobAmts[$dataObj["levels"][$i]] = 0;
                            $knobAmts[$dataObj["levels"][$i]] += ($dataJson["max_val"]-$dataJson["min_val"]);
                        }
                    }
                }
            }
            for ($i = 0; $i < count($indicesToSplice); $i++) {
                for ($j = count($indicesToSplice[$i])-1; $j >= 0; $j--) {
                    array_splice($numMovesPerChallenge[$i], $indicesToSplice[$i][$j], 1);
                }
            }
            
            foreach ($startIndices as $i=>$value) {
                if (isset($startIndices[$i])) {
                    $levelTime = "-";
                    if ($dataObj["times"][$endIndices[$i]] && $dataObj["times"][$startIndices[$i]]) {
                        $levelStartTime = new DateTime($dataObj["times"][$startIndices[$i]]);
                        $levelEndTime = new DateTime($dataObj["times"][$endIndices[$i]]);
                        $levelTime = $levelEndTime->getTimestamp() - $levelStartTime->getTimestamp();
                        $totalTime += $levelTime;
                    }
                    $levelTimes[$i] = $levelTime;

                    $totalMoves += count($numMovesPerChallenge[$i]);
                    $moveTypeChangesTotal += $moveTypeChangesPerLevel[$i];
                    if ($knobNumStdDevs[$i] != 0) {
                        $knobAmtsTotal += ($knobAmts[$i]/$knobNumStdDevs[$i]);
                    }
                    
                    $knobSumTotal += $knobAmts[$i];
                    $knobAvgStdDev = 0;
                    if ($knobNumStdDevs[$i] != 0) {
                        $knobAvgStdDev = ($knobStdDevs[$i]/$knobNumStdDevs[$i]);
                    }
                    $avgKnobStdDevs []= $knobAvgStdDev;

                    $knobAvgAmt = 0;
                    if ($knobNumStdDevs[$i] != 0) {
                        $knobAvgAmt = ($knobAmts[$i]/$knobNumStdDevs[$i]);
                    }
                    $knobAvgs []= $knobAvgAmt;

                }
            }
            $avgTime = $totalTime / count(array_filter($startIndices, function ($value) { return isset($value); }));
            $avgMoves = $totalMoves / count(array_filter($startIndices, function ($value) { return isset($value); }));
            $moveTypeChangesAvg = $moveTypeChangesTotal / count(array_filter($startIndices, function ($value) { return isset($value); }));
            $knobAmtsAvg = $knobAmtsTotal / count(array_filter($startIndices, function ($value) { return isset($value); }));
            $knobSumAvg = $knobSumTotal / count(array_filter($startIndices, function ($value) { return isset($value); }));
        }
        $numMoves = array();
        for ($j = 0; $j < count($numMovesPerChallenge); $j++) {
            $numMoves[$j] = count($numMovesPerChallenge[$j]);
        }
        return array("levelTimes"=>$levelTimes, "avgTime"=>$avgTime, "totalTime"=>$totalTime, "numMovesPerChallenge"=>$numMoves, "totalMoves"=>$totalMoves, "avgMoves"=>$avgMoves,
        "moveTypeChangesPerLevel"=>$moveTypeChangesPerLevel, "moveTypeChangesTotal"=>$moveTypeChangesTotal, "moveTypeChangesAvg"=>$moveTypeChangesAvg, "knobStdDevs"=>$avgKnobStdDevs,
        "knobNumStdDevs"=>$knobNumStdDevs, "knobAvgs"=>$knobAvgs, "knobAmtsTotalAvg"=>$knobAmtsTotal, "knobAmtsAvgAvg"=>$knobAmtsAvg, "knobTotalAmts"=>$knobAmts, "knobSumTotal"=>$knobSumTotal, "knobTotalAvg"=>$knobSumAvg);
    } else {
        return null;
    }
}

if (!isset($_GET['minMoves']) && !isset($_GET['minQuestions']) && !isset($_GET['minLevels'])) {
    if (!isset($_GET['isAll'])) {
        // Return number of sessions for a given game and return those session ids
        if (!isset($_GET['isBasicFeatures']) && !isset($_GET['sessionID']) && isset($_GET['gameID'])) {
            $numSessions = getNumSessions($_GET['gameID'], $db);
            $levels = getLevels($_GET['gameID'], $db);
            $sessionsAndTimes = getSessionsAndTimes($_GET['gameID'], $db);
            $data = array("numSessions"=>$numSessions, "levels"=>$levels, "sessions"=>$sessionsAndTimes["sessions"], "times"=>$sessionsAndTimes["times"]);
            echo json_encode($data);
        } else
    
        // Return number of questions and number correct for a given session id and game
        if (!isset($_GET['isBasicFeatures']) && !isset($_GET['level']) && isset($_GET['sessionID']) && isset($_GET['gameID'])) {
            $data = getQuestions($_GET['gameID'], $_GET['sessionID'], $db);
            echo json_encode($data);
        } else
    
        // Return graphing data
        if (!isset($_GET['isBasicFeatures']) && isset($_GET['gameID']) && isset($_GET['sessionID']) && isset($_GET['level'])) {
            $data = getGraphData($_GET['gameID'], $_GET['sessionID'], $_GET['level'], $db);
            echo json_encode($data);
        } else
    
        // Return basic information
        if (isset($_GET['isBasicFeatures']) && isset($_GET['gameID']) && isset($_GET['sessionID'])) {
            $data = parseBasicInfo(getBasicInfo($_GET['gameID'], $_GET['sessionID'], $db), $_GET['gameID'], $db);
            echo json_encode($data);
        }
    } else { // The same functions as above but for all sessions
        // Return number of questions and number correct for a given game
        if (!isset($_GET['isAggregate']) && !isset($_GET['isBasicFeatures']) && !isset($_GET['level']) && isset($_GET['gameID'])) {
            $gameID = $_GET['gameID'];
            $query = "SELECT event_data_complex, session_id FROM log WHERE app_id=? AND event_custom=? ORDER BY session_id;";
            $paramArray = array($gameID, 3);
            $stmt = queryMultiParam($db, $query, "si", $paramArray);
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
            $numSessions = count(array_unique($sessionIDs));
            $arrayValues = array_count_values($sessionIDs);
            for ($i = 0; $i < $numSessions; $i++) {
                $numCorrect = 0;
                $numQuestions = $arrayValues[$sessionIDs[$i]];
                for ($j = 0; $j < $numQuestions; $j++) {
                    $jsonData = json_decode($data[$i*$numQuestions+$j], true);
                    if ($jsonData["answer"] === $jsonData["answered"]) {
                        $numCorrect++;
                    }
                }
                $totalNumCorrect += $numCorrect;
                $totalNumQuestions += $numQuestions;
            }
            $stmt->close();
            $output = array("totalNumCorrect"=>$totalNumCorrect, "totalNumQuestions"=>$totalNumQuestions);
            echo json_encode($output);
        } else
    
        // Return basic information
        if (isset($_GET['isAggregate']) && isset($_GET['isBasicFeatures']) && isset($_GET['gameID'])) {
            $gameID = $_GET['gameID'];
            $sessionIDs = getSessionsAndTimes($gameID, $db)['sessions'];
            $allData = array();

            $numLevels = count(getLevels($gameID, $db));
            $numSessions = count($sessionIDs);

            $levelTimesAll = array();
            $levelTimesAvgAll = array();
            $avgTimeAll = 0;
            $totalTimeAll = 0;
            $numMovesPerChallengeAll = array();
            $totalMovesAll = 0;
            $avgMovesAll = 0;
            $moveTypeChangesPerLevelAll = array();
            $moveTypeChangesTotalAll = 0;
            $moveTypeChangesAvgAll = array();
            $knobStdDevsAll = array();
            $knobNumStdDevsAll = array();
            $knobAmtsTotalAll = 0;
            $knobAmtsAvgAll = 0;
            $knobSumTotalAll = 0;
            $knobSumAvgAll = 0;
            for ($i = 0; $i < $numLevels; $i++) {
                $levelTimesAll[$i] = array();
                $moveTypeChangesPerLevelAll[$i] = array();
                $numMovesPerChallengeAll[$i] = array();
            }
            $k = 0;
            foreach ($sessionIDs as $i=>$session) {
                $k++;
                if ($k > 10) break;
                $allData[$session] = getBasicInfo($gameID, $session, $db);
            }
            $i = 0;
            foreach ($allData as $index=>$dataObj) {
                $avgTime = 0;
                $totalTime = 0;
                $numMovesPerChallenge = array();
                $totalMoves = 0;
                $avgMoves = 0;
                $moveTypeChangesPerLevel = array();
                $moveTypeChangesTotal = 0;
                $moveTypeChangesAvg = 0;
                $knobStdDevs = array();
                $knobNumStdDevs = array();
                $knobAmtsTotal = 0;
                $knobAmtsAvg = 0;
                $knobSumTotal = 0;
                $knobSumAvg = 0;
                $levelStartTime = new DateTime();
                $levelEndTime = new Datetime();
                $lastSlider = null;
                $startIndices = array();
                $endIndices = array();
                $numMovesPerChallenge = array();
                $moveTypeChangesPerLevel = array_fill(0, $numLevels, 0);
                $knobStdDevs = array_fill(0, $numLevels, 0);
                $knobNumStdDevs = array_fill(0, $numLevels, 0);
                $knobAmts = array_fill(0, $numLevels, 0);
                $startIndices = array_fill(0, $numLevels, null);
                $endIndices = array_fill(0, $numLevels, null);
                $indicesToSplice = array();
                for ($j = 0; $j < $numLevels; $j++) {
                    $numMovesPerChallenge[$j] = array();
                    $indicesToSplice[$j] = array();
                }
                
                if ($dataObj['times'] !== null) {
                    // Basic features stuff
                    $levelStartTime;
                    $levelEndTime;
                    $lastSlider = null;
                    $startIndices = [];
                    $endIndices = [];
                    $moveTypeChangesPerLevel = [];
                    $knobStdDevs = [];
                    $knobNumStdDevs = [];
                    $knobAmts = [];
                    $numMovesPerChallenge = array();
                    $moveTypeChangesPerLevel = array();
                    $knobStdDevs = array();
                    $knobNumStdDevs = array();
                    $knobAmts = array();
                    $startIndices = array();
                    $endIndices = array();
                    $indicesToSplice = array();
                    for ($k = 0; $k < $numLevels; $k++) {
                        $numMovesPerChallenge[$k] = array();
                        $indicesToSplice[$k] = array();
                    }
                    for ($k = 0; $k < count($dataObj['times']); $k++) {
                        if (!isset($endIndices[$dataObj['levels'][$k]])) {
                            $dataJson = json_decode($dataObj['event_data'][$k]);
                            if (!isset($dataJson)) {
                                if ($dataJson['event_custom'] !== 'SLIDER_MOVE_RELEASE' && $dataJson['event_custom'] !== 'ARROW_MOVE_RELEASE') {
                                    $indicesToSplice[$dataObj['levels'][$k]] []= $k;
                                }
                            }
                            if ($dataObj['events'][$k] === 'BEGIN') {
                                if (!isset($startIndices[$dataObj['levels'][$k]])) { // check this space isn't filled by a previous attempt on the same level
                                    $startIndices[$dataObj['levels'][$k]] = $k;
                                }
                            } else if ($dataObj['events'][$k] === 'COMPLETE') {
                                if (!isset($endIndices[$dataObj['levels'][$k]])) {
                                    $endIndices[$dataObj['levels'][$k]] = $k;
                                }
                            } else if ($dataObj['events'][$k] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE' || $dataJson['event_custom'] === 'ARROW_MOVE_RELEASE')) {
                                if ($lastSlider !== $dataJson['slider']) {
                                    $moveTypeChangesPerLevel[$dataObj['levels'][$k]]++;
                                }
                                $lastSlider = $dataJson['slider'];
                                $numMovesPerChallenge[$dataObj['levels'][$k]] []= $k;
                                if ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE') { // arrows don't have std devs
                                    $knobNumStdDevs[$dataObj['levels'][$k]]++;
                                    $knobStdDevs[$dataObj['levels'][$k]] += $dataJson['stdev_val'];
                                    $knobAmts[$dataObj['levels'][$k]] += ($dataJson['max_val']-$dataJson['min_val']);
                                }
                            }
                        }
                    }
                    for ($j = 0; $j < count($indicesToSplice); $j++) {
                        for ($j = count($indicesToSplice[$j])-1; $j >= 0; $j--) {
                            array_splice($numMovesPerChallenge[$j], $indicesToSplice[$j][$j], 1);
                        }
                    }
                    
                    foreach ($startIndices as $index=>$k) {
                        if (isset($startIndices[$k])) {
                            $levelTime = "-";
                            if (isset($dataObj['times'][$endIndices[$k]]) && isset($dataObj['times'][$startIndices[$k]])) {
                                $levelStartTime = new DateTime($dataObj["times"][$startIndices[$k]]);
                                $levelEndTime = new DateTime($dataObj["times"][$endIndices[$k]]);
                                $levelTime = $levelEndTime->getTimestamp() - $levelStartTime->getTimestamp();
                                $totalTime += $levelTime;
                            }

                            $totalMoves += count($numMovesPerChallenge[$k]);
                            $moveTypeChangesTotal += $moveTypeChangesPerLevel[$k];
                            if ($knobNumStdDevs[$k] !== 0) {
                                //$knobAmtsTotal += ($knobAmts[$i]/$knobNumStdDevs[$i]);
                            }
                            
                            $knobSumTotal += $knobAmts[$k];
                        }
                    }
                    // $avgTime = $totalTime / count(array_filter($startIndices, function ($value) { return isset($value); }));
                    // $avgMoves = $totalMoves / count(array_filter($startIndices, function ($value) { return isset($value); }));
                    // $moveTypeChangesAvg = $moveTypeChangesTotal / count(array_filter($startIndices, function ($value) { return isset($value); }));
                    // $knobAmtsAvg = $knobAmtsTotal / count(array_filter($startIndices, function ($value) { return isset($value); }));
                    // $knobSumAvg = $knobSumTotal / count(array_filter($startIndices, function ($value) { return isset($value); }));
                }

                $totalTimeAll += $totalTime;
                $totalMovesAll += $totalMoves;
                $moveTypeChangesTotalAll += $moveTypeChangesTotal;
                $knobAmtsTotalAll += $knobAmtsTotal;
                $knobSumTotalAll += $knobSumTotal;

                foreach ($numMovesPerChallenge as $k => $numMoves) {
                    $numMovesPerChallengeAll[$k][] = count($numMoves);
                }

                foreach ($moveTypeChangesPerLevel as $k => $typeChanges) {
                    $moveTypeChangesPerLevelAll[$k][] = $typeChanges;
                }
                $totalTimes[$i] = $totalTime;
                $totalMovess[$i] = $totalMoves;
                $i++;
            }
            $output = array("times"=>$levelTimesAll, "numMoves"=>$numMovesPerChallengeAll, "moveTypeChanges"=>$moveTypeChangesPerLevelAll,
                 "totalTimes"=>$totalTimeAll, "totalMoves"=>$totalMovesAll);
            echo json_encode($output);
        }
    }
} else {
    $minMoves = $_GET['minMoves'];
    $minLevels = $_GET['minLevels'];
    $minQuestions = $_GET['minQuestions'];
    $startDate = new DateTime($_GET['startDate']);
    $endDate = new DateTime($_GET['endDate']);
    $gameID = $_GET['gameID'];
    $query1 = "SELECT event, event_custom, session_id, client_time FROM log WHERE app_id=? ORDER BY client_time;";
    $paramArray = array($gameID);
    $stmt1 = queryMultiParam($db, $query1, "s", $paramArray);
    if($stmt1 === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt1->bind_result($event, $event_custom, $sessionID, $time)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    while($stmt1->fetch()) {
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
        for ($i = 0; $i < $eventsPerSession[$session]; $i++) {
            if ($events[$index + $i] === "COMPLETE") {
                $numLevels++;
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
    ?>
    {
        "sessions": <?=json_encode($filteredSessions)?>,
        "times": <?=json_encode($filteredSessionsTimes)?>
    }
    <?php

    $stmt1->close();
}

// Close the database connection
$db->close();
?>
