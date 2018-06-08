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

if (isset($_GET['gameID'])) {
    $gameID = $_GET['gameID'];
    
    // Prepare and execute a query for the basic game information
    $query = "SELECT DISTINCT COUNT(session_id) FROM log WHERE app_id=?;";
    $stmt = simpleQueryParam($db, $query, "s", $gameID);
    if($stmt == NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    // Bind variables to the results (same order as in the query)
    if (!$stmt->bind_result($numSessions)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    // Fetch and display the results
    if($stmt->fetch()) {
    ?>
        {
            "sessions": <?=json_encode($numSessions)?>
        }
    <?php
    } else {
        http_response_code(404);
        die('{ "errMessage": "Resource not found." }');                
    }
}
    // Close the database connection
    $stmt->close();
    $db->close();
?>