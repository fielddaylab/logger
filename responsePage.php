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
    if (!$stmt->bind_result($sessions, $time)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    while($stmt->fetch()) {
        $resultsArray[] = $sessions;
        $times[] = $time;
    }
    $stmt->close();
    return array("sessions"=>$resultsArray, "times"=>$times);
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
            $level = $_GET['level'];
            $gameID = $_GET['gameID'];
            $sessionID = $_GET['sessionID'];
    
            $query4 = "SELECT event_data_complex, client_time FROM log WHERE app_id=? AND session_id=? AND level=? AND (event_custom=? OR event_custom=?);";
            $paramArray = array($gameID, $sessionID, $level, 1, 2);
            $stmt4 = queryMultiParam($db, $query4, "ssiii", $paramArray);
            if($stmt4 === NULL) {
                http_response_code(500);
                die('{ "errMessage": "Error running query." }');
            }
            // Bind variables to the results
            if (!$stmt4->bind_result($singleData, $singleTime)) {
                http_response_code(500);
                die('{ "errMessage": "Failed to bind to results." }');
            }
            // Fetch and display the results
            while($stmt4->fetch()) {
                $times[] = $singleTime;
                $eventData[] = $singleData;
            }
            ?>
            {
                "times": <?=json_encode($times)?>,
                "event_data": <?=json_encode($eventData)?>
            }
            <?php
            $stmt4->close();
        } else
    
        // Return basic information
        if (isset($_GET['isBasicFeatures']) && isset($_GET['gameID']) && isset($_GET['sessionID'])) {
            $level = $_GET['level'];
            $gameID = $_GET['gameID'];
            $sessionID = $_GET['sessionID'];
    
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
            ?>
            {
                "times": <?=json_encode($times)?>,
                "event_data": <?=json_encode($eventData)?>,
                "levels": <?=json_encode($levels)?>,
                "events": <?=json_encode($events)?>
            }
            <?php
            $stmt->close();
        }
    } else { // The same functions as above but for all sessions
        // Return number of questions and number correct for a given game
        if (!isset($_GET['isAggregate']) && isset($_GET['isAll']) && !isset($_GET['isBasicFeatures']) && !isset($_GET['level']) && isset($_GET['gameID'])) {
            $gameID = $_GET['gameID'];
            $query1 = "SELECT event_data_complex, session_id FROM log WHERE app_id=? AND event_custom=? ORDER BY session_id;";
            $paramArray = array($gameID, 3);
            $stmt1 = queryMultiParam($db, $query1, "si", $paramArray);
            if($stmt1 === NULL) {
                http_response_code(500);
                die('{ "errMessage": "Error running query." }');
            }
            // Bind variables to the results
            if (!$stmt1->bind_result($dataComplex, $sessionID)) {
                http_response_code(500);
                die('{ "errMessage": "Failed to bind to results." }');
            }
            // Fetch and display the results
            while($stmt1->fetch()) {
                $data[] = $dataComplex;
                $sessionIDs[] = $sessionID;
            }
            $totalNumCorrect = 0;
            $totalNumQuestions = count($data);
            $numSessions = count(array_unique($sessionIDs));
            for ($i = 0; $i < $numSessions; $i++) {
                $numCorrect = 0;
                $numQuestions = array_count_values($sessionIDs)[$sessionID];
                for ($j = 0; $j < $numQuestions; $j++) {
                    $jsonData = json_decode($data[$index], true);
                    if ($jsonData["answer"] === $jsonData["answered"]) {
                        $numCorrect++;
                        $totalNumCorrect++;
                    }
                }
            }
            ?>
            {
                "totalNumCorrect": <?=json_encode($totalNumCorrect)?>,
                "totalNumQuestions": <?=json_encode($totalNumQuestions)?>
            }
            <?php
            $stmt1->close();
        } else
    
        // Return basic information
        if (isset($_GET['isAggregate']) && isset($_GET['isBasicFeatures']) && isset($_GET['gameID'])) {
            $gameID = $_GET['gameID'];
    
            //$query = "SELECT session_id, event_data_complex, client_time, level, event FROM log WHERE app_id=? ORDER BY session_id LIMIT 500;";
            $query = "SELECT session_id, event_data_complex, client_time, level, event FROM log WHERE app_id=? AND session_id IN 
            (SELECT session_id FROM (SELECT session_id, COUNT(session_id) AS occurrence FROM log GROUP BY session_id ORDER BY occurrence DESC LIMIT 10) temp_tab)
            ORDER BY session_id;";
            $paramArray = array($gameID);
            $stmt = queryMultiParam($db, $query, "s", $paramArray);
            if($stmt === NULL) {
                http_response_code(500);
                die('{ "errMessage": "Error running query." }');
            }
            // Bind variables to the results
            if (!$stmt->bind_result($singleID, $singleData, $singleTime, $singleLevel, $singleEvent)) {
                http_response_code(500);
                die('{ "errMessage": "Failed to bind to results." }');
            }
            $numEvents = 0;
            // Fetch and display the results
            while($stmt->fetch()) {
                $sessionIDs[] = $singleID;
                $times[$singleID][] = $singleTime;
                $eventData[$singleID][] = $singleData;
                $levels[$singleID][] = $singleLevel;
                $events[$singleID][] = $singleEvent;
                $levelsCounts[] = $singleLevel;
                $numEvents++;
            }
            $uniqueIDs = array_values(array_unique($sessionIDs));
            $numLevels = count(array_unique($levelsCounts));
            $numSessions = count($uniqueIDs);

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
            $numEventsAllSessions = array_count_values($sessionIDs);
            $totalTimes = array();
            $totalMovess = array();
            for ($i = 0; $i < $numSessions; $i++) {
                $numEventsThisSession = $numEventsAllSessions[$uniqueIDs[$i]];
                $dataObj = array("data"=>$eventData[$uniqueIDs[$i]], "times"=>$times[$uniqueIDs[$i]], "events"=>$events[$uniqueIDs[$i]], "levels"=>$levels[$uniqueIDs[$i]]);

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
                for ($j = 0; $j < $numEventsThisSession; $j++) {
                    if (!isset($endIndices[$dataObj["levels"][$j]])) {
                        $dataJson = json_decode($dataObj["data"][$j], true);
                        if (isset($dataJson)) {
                            if ($dataJson["event_custom"] !== "SLIDER_MOVE_RELEASE" && $dataJson["event_custom"] !== "ARROW_MOVE_RELEASE") {
                                $indicesToSplice[$dataObj["levels"][$j]][] = $j;
                            }
                        }
                        if ($dataObj["events"][$j] === "BEGIN") {
                            if (!isset($startIndices[$dataObj["levels"][$j]])) {
                                $startIndices[$dataObj["levels"][$j]] = $j;
                            }
                        } else if ($dataObj["events"][$j] === "COMPLETE") {
                            if (!isset($endIndices[$dataObj["levels"][$j]])) {
                                $endIndices[$dataObj["levels"][$j]] = $j;
                            }
                        } else if ($dataObj["events"][$j] === "CUSTOM" && ($dataJson["event_custom"] === "SLIDER_MOVE_RELEASE" || $dataJson["event_custom"] === "ARROW_MOVE_RELEASE")) {
                            if ($lastSlider !== $dataJson["slider"]) {
                                $moveTypeChangesPerLevel[$dataObj["levels"][$j]]++;
                            }
                            $lastSlider = $dataJson["slider"];
                            $numMovesPerChallenge[$dataObj["levels"][$j]][] = $j;
                            if ($dataJson["event_custom"] === "SLIDER_MOVE_RELEASE") { // arrows don't have std devs
                                $knobNumStdDevs[$dataObj["levels"][$j]]++;
                                $knobStdDevs[$dataObj["levels"][$i]] += $dataJson["stdev_val"];
                                $knobAmts[$dataObj["levels"][$i]] += ($dataJson["max_val"]-$dataJson["min_val"]);
                            }
                        }
                    }
                }
                for ($j = 0; $j < count($indicesToSplice); $j++) {
                    for ($k = count($indicesToSplice[$j])-1; $k >= 0; $k--) {
                        array_splice($numMovesPerChallenge[$j], $indicesToSplice[$j][$k], 1);
                    }
                }
                foreach ($startIndices as $j => $value) {
                    if (isset($startIndices[$j])) {
                        $levelTime = "-";
                        if (isset($dataObj["times"][$endIndices[$j]]) && isset($dataObj["times"][$startIndices[$j]])) {
                            $levelStartTime = new DateTime($dataObj["times"][$startIndices[$j]]);
                            $levelEndTime = new DateTime($dataObj["times"][$endIndices[$j]]);
                            $levelTime = $levelEndTime->getTimestamp() - $levelStartTime->getTimestamp();
                            $totalTime += $levelTime;
                        }
                        $levelTimesAll[$j][] = $levelTime;

                        $totalMoves += count($numMovesPerChallenge[$j]);
                        $moveTypeChangesTotal += $moveTypeChangesPerLevel[$j];
                        if ($knobNumStdDevs[$j] !== 0) {
                            $knobAmtsTotal += ($knobAmts[$j]/$knobNumStdDevs[$j]);
                        }

                        $knobSumTotal += $knobAmts[$j];

                        $knobAvgStdDev = 0;
                        if ($knobNumStdDevs[$j] === 0) {
                            $knobAvgStdDev = 0;
                        } else {
                            $knobAvgStdDev = ($knobStdDevs[$j]/$knobNumStdDevs[$j]);
                        }
    
                        $knobAvgAmt = 0;
                        if ($knobNumStdDevs[$j] === 0) {
                            $knobAvgAmt = 0;
                        } else {
                            $knobAvgAmt = ($knobAmts[$j]/$knobNumStdDevs[$j]);
                        }
                    }
                }
                $avgTime = $totalTime / count(array_filter($startIndices, function ($value) { return isset($value); }));
                $avgMoves = $totalMoves / count(array_filter($startIndices, function ($value) { return isset($value); }));
                $moveTypeChangesAvg = $moveTypeChangesTotal / count(array_filter($startIndices, function ($value) { return isset($value); }));
                $knobAmtsAvg = $knobAmtsTotal / count(array_filter($startIndices, function ($value) { return isset($value); }));
                $knobSumAvg = $knobSumTotal / count(array_filter($startIndices, function ($value) { return isset($value); }));

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
            }
            ?>
            {
                "times": <?=json_encode($levelTimesAll)?>,
                "numMoves": <?=json_encode($numMovesPerChallengeAll)?>,
                "moveTypeChanges": <?=json_encode($moveTypeChangesPerLevelAll)?>,
                "sessionIDs": <?=json_encode($uniqueIDs)?>,
                "totalTimes": <?=json_encode($totalTimes)?>,
                "totalMoves": <?=json_encode($totalMovess)?>
            }
            <?php
            $stmt->close();
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
