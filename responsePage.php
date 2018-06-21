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
if (!isset($_GET['minMoves']) && !isset($_GET['minQuestions']) && !isset($_GET['minLevels'])) {
    if (!isset($_GET['isAll'])) {
        // Return number of sessions for a given game and return those session ids
        if (!isset($_GET['isBasicFeatures']) && !isset($_GET['sessionID']) && isset($_GET['gameID'])) {
            $gameID = $_GET['gameID'];
            $query = "SELECT COUNT(DISTINCT session_id) FROM log WHERE app_id=?;";
            $stmt = simpleQueryParam($db, $query, "s", $gameID);
            if($stmt == NULL) {
                http_response_code(500);
                die('{ "errMessage": "Error running query." }');
            }
            // Bind variables to the results
            if (!$stmt->bind_result($numSessions)) {
                http_response_code(500);
                die('{ "errMessage": "Failed to bind to results." }');
            }
            // Fetch and display the results
            if($stmt->fetch()) {
            ?>
                {
                    "numSessions": <?=json_encode($numSessions)?>,
            <?php
            } else {
                http_response_code(404);
                die('{ "errMessage": "Resource not found." }');                
            }
    
            $query2 = "SELECT session_id, client_time FROM log WHERE app_id=? GROUP BY client_time;";
            $stmt2 = simpleQueryParam($db, $query2, "s", $gameID);
            if($stmt2 == NULL) {
                http_response_code(500);
                die('{ "errMessage": "Error running query." }');
            }
            // Bind variables to the results
            if (!$stmt2->bind_result($sessions, $time)) {
                http_response_code(500);
                die('{ "errMessage": "Failed to bind to results." }');
            }
            // Fetch and display the results
            while($stmt2->fetch()) {
                $resultsArray[] = $sessions;
                $times[] = $time;
            }
            ?>
                "sessions": <?=json_encode($resultsArray)?>,
                "times": <?=json_encode($times)?>,
            <?php
    
            $query3 = "SELECT DISTINCT level FROM log WHERE app_id=?;";
            $stmt3 = simpleQueryParam($db, $query3, "s", $gameID);
            if($stmt3 == NULL) {
                http_response_code(500);
                die('{ "errMessage": "Error running query." }');
            }
            // Bind variables to the results
            if (!$stmt3->bind_result($level)) {
                http_response_code(500);
                die('{ "errMessage": "Failed to bind to results." }');
            }
            // Fetch and display the results
            while($stmt3->fetch()) {
                $levels[] = $level;
            }
            ?>
                "levels": <?=json_encode($levels)?>
            }
            <?php
            $stmt4->close();
            $stmt->close();
            $stmt2->close();
            $stmt3->close();
        } else
    
        // Return number of questions and number correct for a given session id and game
        if (!isset($_GET['isBasicFeatures']) && !isset($_GET['level']) && isset($_GET['sessionID']) && isset($_GET['gameID'])) {
            $sessionID = $_GET['sessionID'];
            $gameID = $_GET['gameID'];
            $query1 = "SELECT event_data_complex FROM log WHERE app_id=? AND session_id=? AND event_custom=?;";
            $paramArray = array($gameID, $sessionID, 3);
            $stmt1 = queryMultiParam($db, $query1, "ssi", $paramArray);
            if($stmt1 == NULL) {
                http_response_code(500);
                die('{ "errMessage": "Error running query." }');
            }
            // Bind variables to the results
            if (!$stmt1->bind_result($dataComplex)) {
                http_response_code(500);
                die('{ "errMessage": "Failed to bind to results." }');
            }
            // Fetch and display the results
            while($stmt1->fetch()) {
                $data[] = $dataComplex;
            }
            $numCorrect = 0;
            $numQuestions = count($data);
            for ($i = 0; $i < count($data); $i++) {
                $jsonData = json_decode($data[$i], true);
                if ($jsonData["answer"] == $jsonData["answered"]) {
                    $numCorrect++;
                }
            }
            ?>
            {
                "numCorrect": <?=json_encode($numCorrect)?>,
                "numQuestions": <?=json_encode($numQuestions)?>
            }
            <?php
            $stmt1->close();
        } else
    
        // Return graphing data
        if (!isset($_GET['isBasicFeatures']) && isset($_GET['gameID']) && isset($_GET['sessionID']) && isset($_GET['level'])) {
            $level = $_GET['level'];
            $gameID = $_GET['gameID'];
            $sessionID = $_GET['sessionID'];
    
            $query4 = "SELECT event_data_complex, client_time FROM log WHERE app_id=? AND session_id=? AND level=? AND (event_custom=? OR event_custom=?);";
            $paramArray = array($gameID, $sessionID, $level, 1, 2);
            $stmt4 = queryMultiParam($db, $query4, "ssiii", $paramArray);
            if($stmt4 == NULL) {
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
            if($stmt == NULL) {
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
        if (isset($_GET['isAll']) && !isset($_GET['isBasicFeatures']) && !isset($_GET['level']) && isset($_GET['gameID'])) {
            $gameID = $_GET['gameID'];
            $query1 = "SELECT event_data_complex, session_id FROM log WHERE app_id=? AND event_custom=? ORDER BY session_id;";
            $paramArray = array($gameID, 3);
            $stmt1 = queryMultiParam($db, $query1, "si", $paramArray);
            if($stmt1 == NULL) {
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
                $numQuestions = count(array_count_values($sessionIDs)[$sessionID]);
                for ($j = 0; $j < $numQuestions; $j++) {
                    $jsonData = json_decode($data[$index], true);
                    if ($jsonData["answer"] == $jsonData["answered"]) {
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
        if (isset($_GET['isAll']) && isset($_GET['isBasicFeatures']) && isset($_GET['gameID']) && isset($_GET['level'])) {
            $level = $_GET['level'];
            $gameID = $_GET['gameID'];
    
            $query = "SELECT event_data_complex, client_time, level, event, session_id FROM log WHERE app_id=? ORDER BY session_id, client_time;";
            $paramArray = array($gameID);
            $stmt = queryMultiParam($db, $query, "s", $paramArray);
            if($stmt == NULL) {
                http_response_code(500);
                die('{ "errMessage": "Error running query." }');
            }
            // Bind variables to the results
            if (!$stmt->bind_result($singleData, $singleTime, $singleLevel, $singleEvent, $sessionID)) {
                http_response_code(500);
                die('{ "errMessage": "Failed to bind to results." }');
            }
            // Fetch and display the results
            while($stmt->fetch()) {
                $times[] = $singleTime;
                $eventData[] = $singleData;
                $levels[] = $singleLevel;
                $events[] = $singleEvent;
                $sessionIDs[] = $sessionID;
            }
    
            $sessionNumEvents = array_count_values($sessionIDs);
            ?>
            {
                "times": <?=json_encode($times)?>,
                "event_data": <?=json_encode($eventData)?>,
                "levels": <?=json_encode($levels)?>,
                "events": <?=json_encode($events)?>,
                "sessions": <?=json_encode($sessionIDs)?>,
                "sessionNumEvents": <?json_encode($sessionNumEvents)?>
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
    if($stmt1 == NULL) {
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
            if ($events[$index + $i] == "COMPLETE") {
                $numLevels++;
            } else if ($events[$index + $i] == "CUSTOM") {
                if ($event_customs[$index + $i] == 1 || $event_customs[$index + $i] == 2) {
                    $numMoves++;
                } else if ($event_customs[$index + $i] == 3) {
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