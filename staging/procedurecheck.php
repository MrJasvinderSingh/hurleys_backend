<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
$myFile = 'mssqlConnection/procedure.txt';
$message = date('Y-m-d H:i:s').'--procedure';
//$myfile = fopen($myFile, "w") or die("Unable to open file!");
if (file_exists($myFile)) {
  $fh = fopen($myFile, 'a');
  fwrite($fh, $message."\n");
} else {
  $fh = fopen($myFile, 'wb');//print_R($fh);echo $myFile;
  fwrite($fh, $message."\n");
}
fclose($fh);