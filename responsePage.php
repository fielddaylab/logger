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
            }

            for ($i = 0; $i < count($dataObj["times"]); $i++) {
                if (!isset($endIndices[$dataObj["levels"][$i]])) {
                    $dataJson = json_decode($dataObj["data"][$i], true);
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
            
            foreach ($startIndices as $i=>$value) {
                if (isset($startIndices[$i])) {
                    $levelTime = -1;
                    if (isset($dataObj["times"][$endIndices[$i]]) && isset($dataObj["times"][$startIndices[$i]])) {
                        $levelStartTime = new DateTime($dataObj["times"][$startIndices[$i]]);
                        $levelEndTime = new DateTime($dataObj["times"][$endIndices[$i]]);
                        $levelTime = $levelEndTime->getTimestamp() - $levelStartTime->getTimestamp();
                        $totalTime += $levelTime;
                    }
                    $levelTimes[$i] = $levelTime;

                    $totalMoves += count($numMovesPerChallenge[$i]);
                    $moveTypeChangesTotal += $moveTypeChangesPerLevel[$i];
                    if (isset($knobNumStdDevs[$i]) && $knobNumStdDevs[$i] != 0) {
                        $knobAmtsTotal += ($knobAmts[$i]/$knobNumStdDevs[$i]);
                    }
                    if (isset($knobAmts[$i])) $knobSumTotal += $knobAmts[$i];
                    $knobAvgStdDev = 0;
                    if (isset($knobNumStdDevs[$i]) && $knobNumStdDevs[$i] != 0) {
                        $knobAvgStdDev = ($knobStdDevs[$i]/$knobNumStdDevs[$i]);
                    }
                    $avgKnobStdDevs []= $knobAvgStdDev;

                    $knobAvgAmt = 0;
                    if (isset($knobNumStdDevs[$i]) && $knobNumStdDevs[$i] != 0) {
                        $knobAvgAmt = ($knobAmts[$i]/$knobNumStdDevs[$i]);
                    }
                    $knobAvgs []= $knobAvgAmt;

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
            // This query is a lot faster than looping through getQuestions for all sessions
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
            $gameID = $_GET["gameID"];
            $sessionIDs = getSessionsAndTimes($gameID, $db)["sessions"];
            $allData = array();

            $numLevels = count(getLevels($gameID, $db));
            $numSessions = count($sessionIDs);

            $levelTimesAll = array();
            $levelTimesAvgAll = array();
            $avgTimeAll = 0;
            $totalTimeAll = 0;
            $numMovesPerChallengeAll = array();
            $totalMovesAll = 0;
            $totalMovess = array();
            $avgMovesAll = 0;
            $moveTypeChangesPerLevelAll = array();
            $moveTypeChangesTotalAll = 0;
            $moveTypeChangesAvgAll = array();
            $knobStdDevsAll = array();
            $knobNumStdDevsAll = array();
            $knobAvgsAll = array();
            $knobAmtsTotalAll = 0;
            $knobAmtsAvgAll = 0;
            $knobSumTotalAll = 0;
            $knobSumAvgAll = 0;
            for ($i = 0; $i < $numLevels; $i++) {
                $levelTimesAll[$i] = array();
                $moveTypeChangesPerLevelAll[$i] = array();
                $numMovesPerChallengeAll[$i] = array();
                $knobAvgsAll[$i] = 0;
            }
            $allData["18020410454796070"] = parseBasicInfo(getBasicInfo($gameID, 18020410454796070, $db), $gameID, $db);
            $allData["18020414085766550"] = parseBasicInfo(getBasicInfo($gameID, 18020414085766550, $db), $gameID, $db);
            $allData["18020410051068496"] = parseBasicInfo(getBasicInfo($gameID, 18020410051068496, $db), $gameID, $db);
            $allData["18020409553488828"] = parseBasicInfo(getBasicInfo($gameID, 18020409553488828, $db), $gameID, $db);
            $allData["18020414243056364"] = parseBasicInfo(getBasicInfo($gameID, 18020414243056364, $db), $gameID, $db);
            $allData["18020409460082890"] = parseBasicInfo(getBasicInfo($gameID, 18020409460082890, $db), $gameID, $db);
            $allData["18020414111365300"] = parseBasicInfo(getBasicInfo($gameID, 18020414111365300, $db), $gameID, $db);
            $allData["18020414240304052"] = parseBasicInfo(getBasicInfo($gameID, 18020414240304052, $db), $gameID, $db);
            $allData["18020414191844732"] = parseBasicInfo(getBasicInfo($gameID, 18020414191844732, $db), $gameID, $db);
            $allData["18020409575887704"] = parseBasicInfo(getBasicInfo($gameID, 18020409575887704, $db), $gameID, $db);
            $allData["18020414124838264"] = parseBasicInfo(getBasicInfo($gameID, 18020414124838264, $db), $gameID, $db);
            $allData["18020409434141040"] = parseBasicInfo(getBasicInfo($gameID, 18020409434141040, $db), $gameID, $db);
            $allData["18020414033230916"] = parseBasicInfo(getBasicInfo($gameID, 18020414033230916, $db), $gameID, $db);
            $allData["18020409473269000"] = parseBasicInfo(getBasicInfo($gameID, 18020409473269000, $db), $gameID, $db);
            $allData["18020414175586176"] = parseBasicInfo(getBasicInfo($gameID, 18020414175586176, $db), $gameID, $db);
            $allData["18020411125135564"] = parseBasicInfo(getBasicInfo($gameID, 18020411125135564, $db), $gameID, $db);
            $allData["18020409524381744"] = parseBasicInfo(getBasicInfo($gameID, 18020409524381744, $db), $gameID, $db);

            foreach ($sessionIDs as $i=>$session) {
                //$allData[$session] = parseBasicInfo(getBasicInfo($gameID, $session, $db), $gameID, $db);
            }

            // loop through all the sessions we got above and add their variables to totals
            foreach ($allData as $index=>$dataObj) {
                foreach ($dataObj["levelTimes"] as $i=>$levelTime) {
                    $levelTimesAll[$i] []= $dataObj["levelTimes"][$i];
                }

                foreach ($dataObj["numMovesPerChallenge"] as $i=>$numMoves) {
                    $numMovesPerChallengeAll[$i] []= $dataObj["numMovesPerChallenge"][$i];
                }

                foreach ($dataObj["moveTypeChangesPerLevel"] as $i=>$moveTypeChanges) {
                    $moveTypeChangesPerLevelAll[$i] []= $dataObj["moveTypeChangesPerLevel"][$i];
                }

                foreach ($dataObj["knobAvgs"] as $i=>$knobAvg) {
                    $knobAvgsAll[$i] += $knobAvg;
                }

                $totalTimeAll += $dataObj["totalTime"];
                $totalMovesAll += $dataObj["totalMoves"];
                $moveTypeChangesTotalAll += $dataObj["moveTypeChangesTotal"];
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
