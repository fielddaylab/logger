<?php
$settings = json_decode(file_get_contents("config.json"), true);
$DB_NAME_DATA = $settings['DB_NAME_DATA'];
$DB_USER = $settings['DB_USER'];
$DB_PW = $settings['DB_PW'];
$DB_HOST = $settings['DB_HOST'];
$DB_PORT = $settings['DB_PORT'];

// Function to establish a connection to a named database using the above user and PW
// Only makes localhost connections during PHP processing.
// - returns an active database connection handle (be sure to close it later)
function connectToDatabase($databaseName) {
  global $DB_HOST, $DB_PORT, $DB_USER, $DB_PW;
  $db = new mysqli($DB_HOST.':'.$DB_PORT, $DB_USER, $DB_PW, $databaseName);
  return $db;
}

// Execute a simple query with no parameters
// - returns a 'statement' object for further use
// - returns null on error and prints details in the HTML comments
function simpleQuery($db, $query) {
  // Prepare the query
  if(!($stmt = $db->prepare($query))) {
    http_response_code(500);
    echo '{ "errMessage": "Query prepare failed: '.$db->error.'" }';
    return null;
  }

  // Execute query
  if(!$stmt->execute()) {
    http_response_code(500);
    echo '{ "errMessage": "Query execute failed: '.$db->error.'" }';
    return null;
  }

  // Store the results and return the statement object
  if(strpos($query, 'SELECT') !== false) { $stmt->store_result(); }
  return $stmt;
}

// Execute a simple query with one dynamically bound input parameter
// - returns a 'statement' object for further use
// - returns null on error and prints details in the HTML comments
function simpleQueryParam($db, $query, $ptype, &$param) {
  // Prepare the query
  if(!($stmt = $db->prepare($query))) {
    http_response_code(500);
    echo '{ "errMessage": "Query prepare failed: '.$db->error.'" }';
    return null;
  }

  // Bind input param
  if(!($stmt->bind_param($ptype, $param))) {
    http_response_code(500);
    echo '{ "errMessage": "Query param binding failed: '.$db->error.'" }';
    return null;
  }

  // Execute query
  if(!$stmt->execute()) {
    echo '{ "errMessage": "Query execute failed: '.$db->error.'" }';
    return null;
  }

  // Store the results and return the statement object
  if(strpos($query, 'SELECT') !== false) { $stmt->store_result(); }
  return $stmt;
}

function queryMultiParam($db, $query, $ptypes, &$params) {
    // Prepare the query
    if(!($stmt = $db->prepare($query))) {
      http_response_code(500);
      echo '{ "errMessage": "Query prepare failed: '.$db->error.'" }';
      return null;
    }

    // Bind input params
    if (!$stmt->bind_param($ptypes, ...$params)) {
      http_response_code(500);
      echo '{ "errMessage": "Query param binding failed: '.$db->error.'" }';
      return null;
    }
  
    // Execute query
    if(!$stmt->execute()) {
      echo '{ "errMessage": "Query execute failed: '.$db->error.'" }';
      return null;
    }
  
    // Store the results and return the statement object
    if(strpos($query, 'SELECT') !== false) { $stmt->store_result(); }
    return $stmt;
}
?>