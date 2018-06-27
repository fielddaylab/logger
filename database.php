<?php
// Secret database details
abstract class DBDeets {
  const DB_NAME_DATA = 'waves';
  const DB_USER = 'root';
  const DB_PW = 'root';
  const DB_HOST = 'localhost';
  const DB_PORT = 8889;
}

// Function to establish a connection to a named database using the above user and PW
// Only makes localhost connections during PHP processing.
// - returns an active database connection handle (be sure to close it later)
function connectToDatabase($databaseName) {
  $db = new mysqli('localhost:8889', DBDeets::DB_USER, DBDeets::DB_PW, $databaseName);
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