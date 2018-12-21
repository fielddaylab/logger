<?php
$settings = json_decode(file_get_contents("config.json"), true);
ini_set('memory_limit', $settings['memory_limit']);
ini_set('max_execution_time', $settings['max_execution_time']);
ini_set('max_input_vars', $settings['max_input_vars']);
define('DATA_DIR', $settings['DATA_DIR'] . '/' . $_GET['gameID']);

date_default_timezone_set('America/Chicago');

$filename = $_GET['file'];
$path = DATA_DIR . $filename;

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($path));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($path));
ob_clean();
flush();
readfile($path);
exit;
?>