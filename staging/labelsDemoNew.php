<?php
	include_once('include_taxi_webservices.php');
	include_once(TPATH_CLASS.'configuration.php');
	
	require_once('assets/libraries/stripe/config.php');
	require_once('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
	require_once('assets/libraries/pubnub/autoloader.php');
	include_once(TPATH_CLASS .'Imagecrop.class.php');
	include_once(TPATH_CLASS .'twilio/Services/Twilio.php');
	include_once('generalFunctions.php');
	include_once('send_invoice_receipt.php');
  

$dataLblArr=array();

$sql = "SELECT * FROM `language_label` WHERE vCode = 'EN' ";
$data = $obj->MySQLSelect($sql);

for($i=0;$i<count($data);$i++){
  // echo $data[$i]['vLabel']." - ".$data[$i]['vValue'];
  echo "<br>";
  echo "$"."dataLblArr['language_label#".$data[$i]['vLabel']."']" . "='".addslashes($data[$i]['vValue'])."';";
                                          // '$'.'dataLblArr'.'['".$data[$i]['vLabel']."'] = '".$data[$i]['vValue']."';
} 

$sql = "SELECT * FROM `language_label_other` WHERE vCode = 'EN' ";
$data_other = $obj->MySQLSelect($sql);

for($k=0;$k<count($data_other);$k++){
  // echo $data[$i]['vLabel']." - ".$data[$i]['vValue'];
  echo "<br>";
  echo "$"."dataLblArr['language_label_other#".$data_other[$k]['vLabel']."']" . "='".addslashes($data_other[$k]['vValue'])."';";
                                          // '$'.'dataLblArr'.'['".$data[$i]['vLabel']."'] = '".$data[$i]['vValue']."';
} 



$sql = "SELECT * FROM `language_label_1` WHERE vCode = 'EN' ";
$labeldata_other_1 = $obj->MySQLSelect($sql);

for($j=0;$j<count($labeldata_other_1);$j++){
  // echo $data[$i]['vLabel']." - ".$data[$i]['vValue'];
  echo "<br>";
  echo "$"."dataLblArr['language_label_1#".trim($labeldata_other_1[$j]['vLabel'])."']" . "='".addslashes($labeldata_other_1[$j]['vValue'])."';";
                                          // '$'.'dataLblArr'.'['".$data[$i]['vLabel']."'] = '".$data[$i]['vValue']."';
} 

$sql = "SELECT * FROM `language_label_2` WHERE vCode = 'EN' ";
$data_other_2 = $obj->MySQLSelect($sql);

for($l=0;$l<count($data_other_2);$l++){
  // echo $data[$i]['vLabel']." - ".$data[$i]['vValue'];
  echo "<br>";
  echo "$"."dataLblArr['language_label_2#".trim($data_other_2[$l]['vLabel'])."']" . "='".addslashes($data_other_2[$l]['vValue'])."';";
                                          // '$'.'dataLblArr'.'['".$data[$i]['vLabel']."'] = '".$data[$i]['vValue']."';
} 

$sql = "SELECT * FROM `language_label_3` WHERE vCode = 'EN' ";
$data_other_3 = $obj->MySQLSelect($sql);

for($m=0;$m<count($data_other_3);$m++){
  // echo $data[$i]['vLabel']." - ".$data[$i]['vValue'];
  echo "<br>";
  echo "$"."dataLblArr['language_label_3#".trim($data_other_3[$m]['vLabel'])."']" . "='".addslashes($data_other_3[$m]['vValue'])."';";
                                          // '$'.'dataLblArr'.'['".$data[$i]['vLabel']."'] = '".$data[$i]['vValue']."';
} 







?>