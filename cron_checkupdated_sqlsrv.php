<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once('common.php');
include_once('include_config.php');
include_once(TPATH_CLASS.'/class.general.php');
include_once(TPATH_CLASS . 'configuration.php');
include_once('generalFunctions.php');
 global $lang_label, $obj, $tconfig, $generalobj;
$server = '23.188.0.162';//"DESKTOP-S3N90HL";
//$conn = sqlsrv_connect($server, array("database"=>'test', "UID"=>'anviamdb',"PWD"=>'anviam123'));
$conn = sqlsrv_connect($server, array("database"=>'FrontOff', "UID"=>'HurleyAccess',"PWD"=>'yQs#M6=V'));

if (!$conn) {
    echo '<pre>';die( print_r( sqlsrv_errors(), true));
}
$sql = "SELECT * FROM cron_sqlsrv";
$check_crondate = $obj->MySQLSelect($sql);

$datetocompare = date('Y-m-d H:i:s');

if(isset($check_crondate[0]['date'])){
	$datetocompare = $check_crondate[0]['date'];
}else{
	$cron_sqlsrv['date'] = date('Y-m-d H:i:s');
	$id = $obj->MySQLQueryPerform('cron_sqlsrv', $cron_sqlsrv, 'insert');
}
$sql = "SELECT * from [dbo].[LOYAL_CUST] where UPD_DATE > '$datetocompare'";
//$sql = "SELECT * from [dbo].[LOYAL_CUST]";
$stmt = sqlsrv_query( $conn, $sql);
if( $stmt === false ) {
	//echo '<pre>'; die( print_r( sqlsrv_errors(), true));
}
$emailsOfSqlsrv = array();
$cardOfSqlsrv = array();
$sqlsrv_points = array();
$sqlsrv_rdmpt = array();
while($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)){
	if(trim($row['EMAIL_ADDR']) != ''){
		array_push($emailsOfSqlsrv,trim($row['EMAIL_ADDR']));
		array_push($cardOfSqlsrv,trim($row['LOYAL_CUS_NUM']));
		$sqlsrv_points[trim($row['LOYAL_CUS_NUM'])] = trim($row['PNTS']);
		$sqlsrv_rdmpt[trim($row['LOYAL_CUS_NUM'])] = trim($row['RDMPT_VAL']);
	}
}
$emailsformsqlsrv = implode($emailsOfSqlsrv,',');
$cardsformsqlsrv = implode($cardOfSqlsrv,',');
$sql = "SELECT * FROM register_user WHERE 1=1 AND vEmail in ('$emailsformsqlsrv') OR card_number in ($cardsformsqlsrv)";
$check_passenger = $obj->MySQLSelect($sql);
echo '<pre>';print_r($check_passenger);//die;
if(count($check_passenger) > 0){
	foreach($check_passenger as $val){
		if($val['iGcmRegId'] != ''){
			$devicetoken = array($val['iGcmRegId']);
			$vLangCode = $val['vLang'];
			if ($vLangCode == "" || $vLangCode == NULL) {
				$vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
			}
			$languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
			if($sqlsrv_points[$val['card_number']] > $val['loyality_points']){
				$credited_points = $sqlsrv_points[$val['card_number']] - $val['loyality_points'];
				$vTitle = $languageLabelsArr['PUSH_POINTS_CREDIT'];
				$message = str_replace('#POINTS#', $credited_points, $languageLabelsArr['LBL_PUSH_CREDITED_POITNS_TXT']);
				$result = sendpushnotification($val['eDeviceType'],$devicetoken,$vTitle,$message);
				print_r($result);echo $val['vEmail'].'----'.$message;
				$Data_history_points = array();
				$Data_history_points['iUserId'] = $val['iUserId'];
				$Data_history_points['ipoints'] = $val['loyality_points'];
				$Data_history_points['inewPoints'] = $credited_points;
				$obj->MySQLQueryPerform("loyalty_points_history", $Data_history_points, 'insert');
				
				$where = " iUserId = ".$val['iUserId'];
				$Data_passenger['loyality_points'] 		= $sqlsrv_points[$val['card_number']];
				$Data_passenger['redeem_points_value'] 	= $sqlsrv_rdmpt[$val['card_number']];
				$obj->MySQLQueryPerform('register_user', $Data_passenger, 'update',$where);
			}else if($sqlsrv_points[$val['card_number']] < $val['loyality_points']){
				$debited_points = $val['loyality_points'] - $sqlsrv_points[$val['card_number']];
				$vTitle = $languageLabelsArr['PUSH_POINTS_DEBITED'];
				$message = str_replace('#POINTS#', $debited_points, $languageLabelsArr['LBL_PUSH_DEBITED_POITNS_TXT']);
				$result = sendpushnotification($val['eDeviceType'],$devicetoken,$vTitle,$message);
				print_r($result);echo $val['vEmail'].'----'.$message;
				$Data_history_points = array();
				$Data_history_points['iUserId'] = $val['iUserId'];
				$Data_history_points['ipoints'] = $val['loyality_points'];
				$Data_history_points['inewPoints'] = $debited_points;
				$Data_history_points['etype'] = "debit";
				$obj->MySQLQueryPerform("loyalty_points_history", $Data_history_points, 'insert');
				
				$where = " iUserId = ".$val['iUserId'];
				$Data_passenger['loyality_points'] 		= $sqlsrv_points[$val['card_number']];
				$Data_passenger['redeem_points_value'] 	= $sqlsrv_rdmpt[$val['card_number']];
				$obj->MySQLQueryPerform('register_user', $Data_passenger, 'update',$where);
			}
		}
	}
}

function sendpushnotification($type,$devicetoken,$vTitle,$message){
	global $tconfig;
	$message = stripslashes($message);
	$imagepush = $tconfig["tsite_url"]."webimages/upload/DefaultImg/offer_logo.png";
	if($type == 'Android'){
		$Rmessage         = array("message" => array('message'=>$message,"title" => $vTitle,'image'=>$imagepush,'type'=>'pointsUpdate'));
		
		$result = send_notification($devicetoken, $Rmessage,0);
	}else{
		$Rmessage         = array("message" => $message,"title" => $vTitle,'image'=>$imagepush,'type'=>'pointsUpdate',"apns"=>array("payload"=>array("aps"=>array("mutable-content"=>1)),"fcm_options"=>array("image"=>$imagepush)));
		
		$result = sendApplePushNotification(0,$devicetoken,$Rmessage,$message,0,'admin');	
	}
	return $result;
	
}

$where = " id = 1";
$cron_sqlsrv['date'] = date('Y-m-d H:i:s');
$obj->MySQLQueryPerform("cron_sqlsrv", $cron_sqlsrv, 'update', $where);

sqlsrv_close( $conn );





