<?php
require_once('mssqlConnection/mssqlConnect.php');
$obj1 = new mssqlConnect();

$table = '[dbo].[customers]';
$sql = "SELECT * from $table";
			
$stmt = sqlsrv_query( $obj1->conn, $sql);
$arr = array();
if( $stmt === false ) {
	return json_encode( sqlsrv_errors());
}
while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
	array_push($arr,$row);
}


error_reporting(0);
include_once('common.php');
include_once(TPATH_CLASS.'class.general.php');
require_once('assets/libraries/pubnub/autoloader.php');
include_once(TPATH_CLASS.'configuration.php');
include_once(TPATH_CLASS .'Imagecrop.class.php');
include_once(TPATH_CLASS .'twilio/Services/Twilio.php');
include_once('generalFunctions.php');

if(count($arr) > 0){
	foreach($arr as $val){
		$barcode 		= $val['LOYAL_CUS_NUM'];
		$customerName 	= $val['CUST_NAME'];
		$customerEmail 	= $val['EMAIL_ADDR'];
		$customerphone 	= $val['SECONDARY_ID'];
		$loyalty_points	= $val['PNTS'];
		$email = '';
		if($customerEmail != ''){
			$email = " and vEmail = '".$customerEmail."'";
		}else if($customerphone != ''){
			$email = " and vPhone = '".$customerphone."'";
		}
		$query = "SELECT vEmail, CONCAT(vName,' ',vLastName) as vFullName, barcode, loyalty_points, iUserId, vPhone FROM register_user where barcode = '".$barcode."' $email";
		$db_user = $obj->MySQLSelect($query);
		$fname = $lname = '';
		$name = explode(' ',$customerName);
		$fname = $name[0];
		if($name[1]){
			$lname = $name[1];
		}
		if(count($db_user) == 0){
			$q = "insert into register_user(vEmail,vName,vLastName,barcode,vPhone) values('".$customerEmail."','".$fname."','".$lname."','".$barcode."','".$customerphone."')";
			$obj->sql_query($q);
		}else{
			$q = "update register_user SET vEmail = '".$customerEmail."' ,vName = '".$fname."',vLastName='".$lname."',barcode='".$barcode."',vPhone='".$customerphone."' where iUserId = '".$db_user->iUserId."'";
			$obj->sql_query($q);
		}
		
	}
}
echo '<pre>';print_r($arr);die;