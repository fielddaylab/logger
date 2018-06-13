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

    $query2 = "SELECT DISTINCT session_id FROM log WHERE app_id=?;";
    $stmt2 = simpleQueryParam($db, $query2, "s", $gameID);
    if($stmt2 == NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results
    if (!$stmt2->bind_result($sessions)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    while($stmt2->fetch()) {
        $resultsArray[] = $sessions;
    }
    ?>
        "sessions": <?=json_encode($resultsArray)?>,
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

// Close the database connection
$db->close();
?>