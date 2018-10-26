<?php
header('Content-Type: application/json');

$settings = json_decode(file_get_contents("config.json"), true);
ini_set('memory_limit', $settings['memory_limit']);
ini_set('max_execution_time', $settings['max_execution_time']);
ini_set('max_input_vars', $settings['max_input_vars']);
define('DATA_DIR', $settings['DATA_DIR']);

$table = $_GET['table'];
$row = $_GET['row'];
$col = $_GET['col'];

$dataFile = DATA_DIR . '/' . $table . '/' . $table . 'DataForR_' . $col . '.txt';
$altFile = DATA_DIR . '/' . $table . '/' . $table . 'DataForR_' . substr($col, 3) . '.txt';

if (!file_exists($dataFile)) {
    $dataFile = $altFile;
}

$csv = array_map('str_getcsv', array_slice(file($dataFile), 1));
$header = array_shift($csv); 

$output = array();
$c = array_search($row, $header);
foreach ($csv as $r) {      
    $output['inputs'][] = $r[$c];
    $output['outputs'][] = $r[count($r) - 1];
}
echo json_encode($output);
?>