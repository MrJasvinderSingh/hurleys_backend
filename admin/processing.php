<?php
include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$default_lang 	= $generalobj->get_default_lang();
$script = 'Processing';

$rdr_ssql = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}

//data for select fields
$sql = "select iCompanyId,vCompany from company WHERE eStatus != 'Deleted' $rdr_ssql";
$db_company = $obj->MySQLSelect($sql);

$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName from register_driver WHERE eStatus != 'Deleted' $rdr_ssql";
$db_drivers = $obj->MySQLSelect($sql);

$sql = "select iUserId,CONCAT(vName,' ',vLastName) AS riderName from register_user WHERE eStatus != 'Deleted' $rdr_ssql";
$db_rider = $obj->MySQLSelect($sql);
//data for select fields

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$promocode = isset($_REQUEST['promocode']) ? $_REQUEST['promocode'] : '';
$ord = ' ORDER BY o.iOrderId DESC';

if($sortby == 1){
  if($order == 0)
  $ord = " ORDER BY c.vCompany ASC";
  else
  $ord = " ORDER BY c.vCompany DESC";
}

if($sortby == 2){
  if($order == 0)
  $ord = " ORDER BY o.tTripRequestDate ASC";
  else
  $ord = " ORDER BY o.tTripRequestDate DESC";
}

if($sortby == 3){
  if($order == 0)
  $ord = " ORDER BY u.vName ASC";
  else
  $ord = " ORDER BY u.vName DESC";
}
//End Sorting

// Start Search Parameters
$ssql='';
$action = isset($_REQUEST['action']) ? $_REQUEST['action']: '';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id']: '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';

if($startDate!=''){
	$ssql.=" AND Date(o.tOrderRequestDate) >='".$startDate."'";
}
if($endDate!=''){
	$ssql.=" AND Date(o.tOrderRequestDate) <='".$endDate."'";
}
if($serachTripNo!=''){
	$ssql.=" AND o.vOrderNo ='".$serachTripNo."'";
}
if($searchCompany!=''){
	$ssql.=" AND d.iCompanyId ='".$searchCompany."'";
}

if($searchRider!=''){
	$ssql.=" AND o.iUserId ='".$searchRider."'";
}

$trp_ssql = "";
if(SITE_TYPE =='Demo'){
	$trp_ssql = " And o.tOrderRequestDate > '".WEEK_DATE."'";
}

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(o.iOrderId) AS Total FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId=o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode WHERE 1=1 AND o.iStatusCode IN ('2','4','5') $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
//total pages we going to have
$total_pages = ceil($total_results / $per_page); 
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
	//it will telles the current page
    $show_page = $_GET['page'];             
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End

$sql = "SELECT o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName, CONCAT(d.vName,' ',d.vLastName) AS driverName, c.vCompany,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode   WHERE 1=1  AND o.iStatusCode IN ('2','4','5') $ssql $trp_ssql $ord LIMIT $start, $per_page";
$DBProcessingOrders = $obj->MySQLSelect($sql);
$endRecord = count($DBProcessingOrders);
$var_filter = "";
foreach ($_REQUEST as $key=>$val) {
    if($key != "tpages" && $key != 'page')
    $var_filter.= "&$key=".stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;
$Today=Date('Y-m-d');
$tdate=date("d")-1;
$mdate=date("d");
$Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));

$curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
$curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
$prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")-1));
$prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")-1));

$currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$tdate,date("Y")));
$currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m")+1,date("d")-$mdate,date("Y")));
$prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d")-$tdate,date("Y")));
$prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$mdate,date("Y")));

$monday = date( 'Y-m-d', strtotime( 'sunday this week -1 week' ) );
$sunday = date( 'Y-m-d', strtotime( 'saturday this week' ) );

$Pmonday = date( 'Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date( 'Y-m-d', strtotime('saturday this week -1 week'));

if($action == 'cancel' && $hdn_del_id != '') {

	$vCancelReason = isset($_REQUEST['cancel_reason']) ? $_REQUEST['cancel_reason'] : '';
	$fCancellationCharge = isset($_REQUEST['fCancellationCharge']) ? $_REQUEST['fCancellationCharge'] : '';
	$vIP = get_client_ip();

	$query = "UPDATE orders SET iStatusCode = '8' , eCancelledBy= 'Admin' ,fCancellationCharge = '".$fCancellationCharge."' ,vCancelReason='".$vCancelReason."' WHERE iOrderId = '".$hdn_del_id."'";
	$obj->sql_query($query);
	  	
	$lquery = "INSERT INTO `order_status_logs`(`iOrderId`, `iStatusCode`, `dDate`, `vIp`) VALUES ('".$hdn_del_id."','8',Now(),'".$vIP."')";
	$obj->sql_query($lquery);

	$uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";
	$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
	$PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
	$PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
	$PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");
    ## Send Notification To User  ##
	$Message= "OrderCancelByAdmin";

	$sql="select ru.iUserId,ru.iGcmRegId,ru.eDeviceType,ru.tSessionId,ru.iAppVersion,ru.vLang,ord.vOrderNo from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '".$hdn_del_id."'";
	$data_order = $obj->MySQLSelect($sql);

	$vLangCode=$data_order[0]['vLang'];
	$vOrderNo=$data_order[0]['vOrderNo'];
	$iUserId=$data_order[0]['iUserId'];

	if($vLangCode == "" || $vLangCode == NULL){
		$vLangCode = get_value('language_master', 'vCode', 'eDefault','Yes','','true');
	}                  

	$vTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : '';
	$languageLabelsArr= getLanguageLabelsArr($vLangCode,"1");
	$alertMsg = $languageLabelsArr['LBL_CANCEL_ORDER_ADMIN_TXT']." #".$vOrderNo." ".$languageLabelsArr['LBL_REASON_TXT']." ".$vTitleReasonMessage;
	$message_arr = array();
	$message_arr['Message'] = $Message;
	$message_arr['iOrderId'] = $hdn_del_id;
	$message_arr['vOrderNo'] = $vOrderNo;
	$message_arr['vTitle'] = $alertMsg;
	$message_arr['tSessionId'] = $data_order[0]['tSessionId'];;
	$message = json_encode($message_arr,JSON_UNESCAPED_UNICODE);
	if($PUBNUB_DISABLED == "Yes"){
		$ENABLE_PUBNUB = "No";
	}

	$alertSendAllowed = true;
	/* For PubNub Setting */
	$tableName = "register_user";
	$iMemberId_VALUE = $iUserId;
	$iMemberId_KEY = "iUserId";
	$iAppVersion = $data_order[0]['iAppVersion'];
	$eDeviceType = $data_order[0]['eDeviceType'];
	$iGcmRegId = $data_order[0]['iGcmRegId'];
	$tSessionId = $data_order[0]['tSessionId'];
	$registatoin_ids = $iGcmRegId;
	$deviceTokens_arr_ios = array();
	$registation_ids_new = array();
	/* For PubNub Setting Finished */

   	if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
      	$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY,"subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
  		$channelName = "PASSENGER_".$iUserId;
      	$info = $pubnub->publish($channelName, $message);
      	if($eDeviceType != "Android"){
  			array_push($deviceTokens_arr_ios, $iGcmRegId);	
  		} 
   	}

   if($alertSendAllowed == true){
  		if($eDeviceType == "Android"){
  			array_push($registation_ids_new, $iGcmRegId);
  			$Rmessage         = array("message" => $message);
  			$result = send_notification($registation_ids_new, $Rmessage,0);	
      	} else {
  			array_push($deviceTokens_arr_ios, $iGcmRegId);
  			sendApplePushNotification(0,$deviceTokens_arr_ios,$message,$alertMsg,0);
  		}
   }
  	## Send Notification To User ## 
   ## Send Notification To Restaurant  ##
	$Message= "OrderCancelByAdmin";
	$sql="select c.iCompanyId,c.iGcmRegId,c.eDeviceType,c.tSessionId,c.iAppVersion,c.vLang,o.vOrderNo from orders as o LEFT JOIN company as c ON o.iCompanyId=c.iCompanyId where o.iOrderId = '".$hdn_del_id."'";
	$Resdata_order = $obj->MySQLSelect($sql);

	$ResLangCode = $Resdata_order[0]['vLang'];
	$ResOrderNo = $Resdata_order[0]['vOrderNo'];
	$iCompanyId = $Resdata_order[0]['iCompanyId'];

	if($ResLangCode == "" || $ResLangCode == NULL){
		$ResLangCode = get_value('language_master', 'vCode', 'eDefault','Yes','','true');
	}                  

	$ResTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : '';
	$ReslanguageLabelsArr= getLanguageLabelsArr($ResLangCode,"1");
	$ResAlertMsg = $ReslanguageLabelsArr['LBL_CANCEL_ORDER_ADMIN_TXT']." #".$ResOrderNo." ".$ReslanguageLabelsArr['LBL_REASON_TXT']." ".$ResTitleReasonMessage;
	$message_arr_res = array();
	$message_arr_res['Message'] = $Message;
	$message_arr_res['iOrderId'] = $hdn_del_id;
	$message_arr_res['vOrderNo'] = $ResOrderNo;
	$message_arr_res['vTitle'] = $ResAlertMsg;
	$message_arr_res['tSessionId'] = $Resdata_order[0]['tSessionId'];;
	$restaurantmessage = json_encode($message_arr_res,JSON_UNESCAPED_UNICODE);
	if($PUBNUB_DISABLED == "Yes"){
		$ENABLE_PUBNUB = "No";
	}

	$alertSendAllowed = true;
	/* For PubNub Setting */
	$tableName = "company";
	$iMemberId_VALUE = $iCompanyId;
	$iMemberId_KEY = "iCompanyId";
	$iAppVersion = $Resdata_order[0]['iAppVersion'];
	$eDeviceType = $Resdata_order[0]['eDeviceType'];
	$iGcmRegId = $Resdata_order[0]['iGcmRegId'];
	$tSessionId = $Resdata_order[0]['tSessionId'];
	$registatoin_ids = $iGcmRegId;
	$restaurantdeviceTokens_arr_ios = array();
	$restuarantregistation_ids_new = array();
	/* For PubNub Setting Finished */

   	if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
      	$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY,"subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
  		$RestaurantchannelName = "RESTAURANT_".$iCompanyId;
      	$info = $pubnub->publish($RestaurantchannelName, $restaurantmessage);
      	if($eDeviceType != "Android"){
  			array_push($restaurantdeviceTokens_arr_ios, $iGcmRegId);	
  		} 
   	}

   if($alertSendAllowed == true){
  		if($eDeviceType == "Android"){
  			array_push($restuarantregistation_ids_new, $iGcmRegId);
  			$Rmessage = array("message" => $restaurantmessage);
  			$result = send_notification($restuarantregistation_ids_new, $Rmessage,0);	
      	} else {
  			array_push($restaurantdeviceTokens_arr_ios, $iGcmRegId);
  			sendApplePushNotification(0,$restaurantdeviceTokens_arr_ios,$restaurantmessage,$alertMsg,0);
  		}
   }
   ## Send Notification To Restaurant  ##
   ## Send Notification To Driver ##
    $query1="select * from order_status_logs where iOrderId = '".$hdn_del_id."' AND iStatusCode = '4'";
	$OrdersData = $obj->MySQLSelect($query1);

	if(count($OrdersData) > 0){
		$Message= "OrderCancelByAdmin";
		$sql="select d.iDriverId,d.iGcmRegId,d.eDeviceType,d.tSessionId,d.iAppVersion,d.vLang,o.vOrderNo from orders as o LEFT JOIN register_driver as d ON o.iDriverId=d.iDriverId where o.iOrderId = '".$hdn_del_id."'";
		$drv_data_order = $obj->MySQLSelect($sql);
		
		$drvLangCode = $drv_data_order[0]['vLang'];
		$drvOrderNo = $drv_data_order[0]['vOrderNo'];
		$iDriverId = $drv_data_order[0]['iDriverId'];

		$query1 = "UPDATE register_driver SET vTripStatus = 'Cancelled' WHERE iDriverId = '".$iDriverId."'";
		$obj->sql_query($query1);

		$query2 = "UPDATE trips SET iActive = 'Canceled' WHERE iOrderId = '".$hdn_del_id."'";
		$obj->sql_query($query2);

		if($drvLangCode == "" || $drvLangCode == NULL){
			$drvLangCode = get_value('language_master', 'vCode', 'eDefault','Yes','','true');
		}                  

		$drvTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : '';
		$drvlanguageLabelsArr= getLanguageLabelsArr($drvLangCode,"1");
		$drvAlertMsg = $drvlanguageLabelsArr['LBL_CANCEL_ORDER_ADMIN_TXT']." #".$drvOrderNo." ".$drvlanguageLabelsArr['LBL_REASON_TXT']." ".$drvTitleReasonMessage;
		$message_arr_res = array();
		$message_arr_res['Message'] = $Message;
		$message_arr_res['iOrderId'] = $hdn_del_id;
		$message_arr_res['vOrderNo'] = $drvOrderNo;
		$message_arr_res['vTitle'] = $drvAlertMsg;
		$message_arr_res['tSessionId'] = $drv_data_order[0]['tSessionId'];;
		$drvmessage = json_encode($message_arr_res,JSON_UNESCAPED_UNICODE);
		if($PUBNUB_DISABLED == "Yes"){
			$ENABLE_PUBNUB = "No";
		}

		$alertSendAllowed = true;
		/* For PubNub Setting */
		$tableName = "register_driver";
		$iMemberId_VALUE = $iDriverId;
		$iMemberId_KEY = "iDriverId";
		$iAppVersion = $drv_data_order[0]['iAppVersion'];
		$eDeviceType = $drv_data_order[0]['eDeviceType'];
		$iGcmRegId = $drv_data_order[0]['iGcmRegId'];
		$tSessionId = $drv_data_order[0]['tSessionId'];
		$registatoin_ids = $iGcmRegId;
		$drvdeviceTokens_arr_ios = array();
		$drvregistation_ids_new = array();
		/* For PubNub Setting Finished */

	   	if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
	      	$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY,"subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
	  		$DriverchannelName = "DRIVER_".$iCompanyId;
	      	$info = $pubnub->publish($DriverchannelName, $drvmessage);
	      	if($eDeviceType != "Android"){
	  			array_push($drvdeviceTokens_arr_ios, $iGcmRegId);	
	  		} 
	   	}

	   	if($alertSendAllowed == true){
	  		if($eDeviceType == "Android"){
	  			array_push($drvregistation_ids_new, $iGcmRegId);
	  			$Dmessage = array("message" => $drvmessage);
	  			$result = send_notification($drvregistation_ids_new, $Dmessage,0);	
	      	} else {
	  			array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
	  			sendApplePushNotification(0,$drvdeviceTokens_arr_ios,$drvmessage,$alertMsg,0);
	  		}
	   	}
	}

   ## Send Notification To Driver ##
      /*
    $sql1="select * from orders where iOrderId=".$hdn_del_id;
	$bookind_detail = $obj->MySQLSelect($sql1);
	        
	$tOrderRequestDate = $bookind_detail[0]['tOrderRequestDate'];     
	$vOrderNo = $bookind_detail[0]['vOrderNo'];

      $sql2 = "select vName,vLastName,vEmail,iDriverVehicleId,vPhone,vcode,vLang from register_driver where iDriverId=".$iDriverId;
      $driver_db = $obj->MySQLSelect($sql2);
      $vPhone = $driver_db[0]['vPhone'];
      $vcode = $driver_db[0]['vcode'];
      $vLang = $driver_db[0]['vLang'];
            
      $SQL3 = "SELECT vName,vLastName,vEmail,iUserId,vPhone,vPhoneCode,vLang FROM register_user WHERE iUserId = '".$iUserId."'";
      $user_detail = $obj->MySQLSelect($SQL3);
      $vPhone1 = $user_detail[0]['vPhone'];   
      $vcode1 = $user_detail[0]['vPhoneCode'];
      $vLang1 = $user_detail[0]['vLang'];
          
      $Data1['vRider']=$user_detail[0]['vName']." ".$user_detail[0]['vLastName'];
      $Data1['vDriver']=$driver_db[0]['vName']." ".$driver_db[0]['vLastName'];  
      $Data1['vRiderMail']=$user_detail[0]['vEmail'];           
      $Data1['vSourceAddresss']=$vSourceAddresss;        
      $Data1['dBookingdate']=$dBooking_date;
      $Data1['vBookingNo']=$vBookingNo;
      
      $Data['vRider']=$user_detail[0]['vName']." ".$user_detail[0]['vLastName'];
      $Data['vDriver']=$driver_db[0]['vName']." ".$driver_db[0]['vLastName'];     
      $Data['vDriverMail']=$driver_db[0]['vEmail'];     
      $Data['vSourceAddresss']=$vSourceAddresss;          
      $Data['dBookingdate']=$dBooking_date;
      $Data['vBookingNo']=$vBookingNo;
            
      $return = $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN_TO_DRIVER",$Data);
      $return1 = $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN_TO_USER",$Data1);*/
            
   /*   $Booking_Date = @date('d-m-Y',strtotime($dBooking_date));    
      $Booking_Time = @date('H:i:s',strtotime($dBooking_date));     
            
      $maildata['vDriver'] = $driver_db[0]['vName']." ".$driver_db[0]['vLastName'];  
      $maildata['dBookingdate'] = $Booking_Date;      
      $maildata['dBookingtime'] =  $Booking_Time;      
      $maildata['vBookingNo'] = $vBookingNo;      
                  
      $maildata1['vRider'] = $user_detail[0]['vName']." ".$user_detail[0]['vLastName'];      
      $maildata1['dBookingdate'] = $Booking_Date;      
      $maildata1['dBookingtime'] =  $Booking_Time;      
      $maildata1['vBookingNo'] = $vBookingNo;     
                  
      $message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE_JOB_CANCEL",$maildata1,"",$vLang);
      $return5 = $generalobj->sendUserSMS($vPhone,$vcode,$message_layout,"");  
                  
      $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_JOB_CANCEL",$maildata,"",$vLang1);
      $return4 = $generalobj->sendUserSMS($vPhone1,$vcode1,$message_layout,"");    */
            
    echo "<script>location.href='processing.php'</script>";
  } 

?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title><?=$SITE_NAME?> | <?php echo $langage_lbl_admin['LBL_PROCESSING_ORDERS'];?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <?php include_once('global_files.php');?>

</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 " >
    <!-- Main LOading -->
    <!-- MAIN WRAPPER -->
    <div id="wrap">
        <?php include_once('header.php'); ?>
        <?php include_once('left_menu.php'); ?>
        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
	            <div id="add-hide-show-div">
	                <div class="row">
	                    <div class="col-lg-12">
	                        <h2><?php echo $langage_lbl_admin['LBL_PROCESSING_ORDERS'];?> </h2>
	                    </div>
	                </div>
	                <hr />
	            </div>
                <?php include('valid_msg.php'); ?>
               	<!--  Search Form Start  -->
				<form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
					<div class="Posted-date mytrip-page">
						<input type="hidden" name="action" value="search" />
						<h3>Search <?php echo $langage_lbl_admin['LBL_PROCESSING_ORDERS'];?> ...</h3>
						<span>
							<a onClick="return todayDate('dp4','dp5');"><?=$langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
							<a onClick="return yesterdayDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
							<a onClick="return currentweekDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
							<a onClick="return previousweekDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
							<a onClick="return currentmonthDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
							<a onClick="return previousmonthDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
							<a onClick="return currentyearDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
							<a onClick="return previousyearDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
						</span> 
						<span>
							<input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default;background-color: #fff" />
							<input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default;background-color: #fff"/>
								
							<div class="col-lg-2">
								<input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'];?> Number" class="form-control search-trip001" value="<?php echo $serachTripNo; ?>"/>
							</div>
							<div class="col-lg-3 select001">
								<select class="form-control filter-by-text" name = 'searchCompany' id="searchCompany" data-text="Select Company">
								   <option value="">Select Company</option>
								   <?php foreach($db_company as $dbc){ ?>
								   <option value="<?php echo $dbc['iCompanyId']; ?>" <?php if($searchCompany == $dbc['iCompanyId']) { echo "selected"; } ?>><?php echo $generalobjAdmin->clearCmpName($dbc['vCompany']); ?></option>
								   <?php } ?>
								</select>
							</div>
						</span>
					</div>
					<div class="row">
						<div class="col-lg-3">
							<select class="form-control filter-by-text" name = 'searchRider' data-text="Select <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];?>">
								<option value="">Select <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];?></option>
							   <?php foreach($db_rider as $dbr){ ?>
							   <option value="<?php echo $dbr['iUserId']; ?>" <?php if($searchRider == $dbr['iUserId']) { echo "selected"; } ?>><?php echo $generalobjAdmin->clearName($dbr['riderName']); ?></option>
							   <?php } ?>
							</select>
						</div>
					</div>
					<div class="tripBtns001">
						<b>
							<input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
							<input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='processing.php'"/>
						</b>
					</div>
				</form>
				<!-- Search Form End -->
				<div class="table-list">
					<div class="row">
						<div class="col-lg-12">
							<div class="table-responsive">
								<form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
									<table class="table table-striped table-bordered table-hover" >
										<thead>
											<tr>
											<th><?php echo $langage_lbl_admin['LBL_ORDER_NO_ADMIN'];?>#</th>
											<th><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Restaurant Name <?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
											<th><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; } else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_TRIP_DATE_ADMIN'];?> <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
											<th><a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];?> Name <?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
											<th>Total Item</th>
											<th><?php echo $langage_lbl_admin['LBL_DRIVER_TRIP_FARE_TXT'];?></th>
											<th>Status</th>
											<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<? if(!empty($DBProcessingOrders)) {
												for($i=0;$i<count($DBProcessingOrders);$i++) { ?>
													<tr class="gradeA">
														<td><?=$DBProcessingOrders[$i]['vOrderNo'];?></td>
														<td><?=$generalobjAdmin->clearCmpName($DBProcessingOrders[$i]['vCompany']);?></td>
														<td><?= date('d-F-Y',strtotime($DBProcessingOrders[$i]['tOrderRequestDate'])); ?></td>
														<td><?=$generalobjAdmin->clearName($DBProcessingOrders[$i]['riderName']);?></td>
														<td><?= $DBProcessingOrders[$i]['TotalItem']?></td>
														<td><?= $generalobj->trip_currency($DBProcessingOrders[$i]['fNetTotal']); ?></td>
														<td width="20%"><?= $DBProcessingOrders[$i]['vStatus']?></td>
														<td>
															<button type="button" class="btn btn-info" data-toggle="modal" data-target="#delete_form<?= $DBProcessingOrders[$i]['iOrderId'];?>">Cancel Order</button>
															<!-- Modal -->
														    <div id="delete_form<?= $DBProcessingOrders[$i]['iOrderId'];?>" class="modal fade delete_form" role="dialog">
														      <div class="modal-dialog">
														        <!-- Modal content-->
														        <div class="modal-content">
														          <div class="modal-header">
														            <button type="button" class="close" data-dismiss="modal">x</button>
														            <h4 class="modal-title">Order Cancel</h4>
														          </div>
														            <form role="form" name="delete_form" id="delete_form1" method="post" action="" class="margin0">
														          <div class="modal-body">
														            <div class="form-group col-lg-12" style="display: inline-block;">
														                <label class="col-lg-4 control-label">Cancel Reason<span class="red">*</span></label>
														                <div class="col-lg-7">
														                    <textarea name="cancel_reason" id="cancel_reason" rows="4" cols="40" required="required"></textarea>
														                    <div class="cnl_error error red"></div>
														                </div>
														            </div>
														            <div class="form-group col-lg-12" style="display: inline-block;">
														                <label class="col-lg-4 control-label">Cancel Charges<span class="red">*</span></label>
														                <?php $MIN_ORDER_CANCELLATION_CHARGES = $generalobj->getConfigurations("configurations","MIN_ORDER_CANCELLATION_CHARGES");?>
														                <div class="col-lg-7">
														                	<input type="fCancellationCharge" name="fCancellationCharge" id="fCancellationCharge" required="required" value="<?= $MIN_ORDER_CANCELLATION_CHARGES;?>">
														                    <div class="cancelcharge_error error red"></div>
														                </div>
														            </div>
														              <input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">
														              <input type="hidden" name="action" id="action" value="cancel">
														          </div>
														          <div class="modal-footer">
														            <button type="submit" class="btn btn-info" id="cnl_booking" title="Cancel Booking">Cancel Order</button>
														            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
														          </div>
														          </form> 
														        </div>
														         <!-- Modal content-->	
														      </div>
														    </div>
														    <!-- Modal -->
														</td>
													</tr>
													<div class="clear"></div>
											<?php } 
											} else { ?>
											<tr class="gradeA">
												<td colspan="8"> No Records Found.</td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
								</form>
								<?php include('pagination_n.php'); ?>
							</div>
						</div>
					</div>
				</div>
        		<div class="clear"></div>
			</div>
		</div>
	<!--END PAGE CONTENT -->
	</div>
	<!--END MAIN WRAPPER -->
	<form name="pageForm" id="pageForm" action="" method="post" >
		<input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
		<input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
		<input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
		<input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
		<input type="hidden" name="action" value="<?php echo $action; ?>" >
		<input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>" >
		<input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>" >
		<input type="hidden" name="searchRider" value="<?php echo $searchRider; ?>" >
		<input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>" >
		<input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
		<input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
		<input type="hidden" name="vStatus" value="<?php echo $vStatus; ?>" >
		<input type="hidden" name="eType" value="<?php echo $eType; ?>" >
		<input type="hidden" name="method" id="method" value="" >
	</form>
	<? include_once('footer.php');?>
	<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
	<link rel="stylesheet" href="css/select2/select2.min.css" />
	<script src="js/plugins/select2.min.js"></script>
	<script src="../assets/js/jquery-ui.min.js"></script>
	<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
    <script>
			$('#dp4').datepicker()
            .on('changeDate', function (ev) {
                if (ev.date.valueOf() < endDate.valueOf()) {
                    $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                } else {
                    $('#alert').hide();
                    startDate = new Date(ev.date);
                    $('#startDate').text($('#dp4').data('date'));
                }
                $('#dp4').datepicker('hide');
            });
			$('#dp5').datepicker()
            .on('changeDate', function (ev) {
                if (ev.date.valueOf() < startDate.valueOf()) {
                    $('#alert').show().find('strong').text('The end date can not be less then the start date');
                } else {
                    $('#alert').hide();
                    endDate = new Date(ev.date);
                    $('#endDate').text($('#dp5').data('date'));
                }
                $('#dp5').datepicker('hide');
            });
	
         $(document).ready(function () {
			 if('<?=$startDate?>'!=''){
				 $("#dp4").val('<?=$startDate?>');
				 $("#dp4").datepicker('update' , '<?=$startDate?>');
			 }
			 if('<?=$endDate?>'!=''){
				 $("#dp5").datepicker('update' , '<?= $endDate;?>');
				 $("#dp5").val('<?= $endDate;?>');
			 }
			 
         });
		 
		 function setRideStatus(actionStatus) {
			 window.location.href = "trip.php?type="+actionStatus;
		 }
		 function todayDate()
		 {
			 $("#dp4").val('<?= $Today;?>');
			 $("#dp5").val('<?= $Today;?>');
		 }
		 function reset() {
			location.reload();
			
		}	
		 function yesterdayDate()
		 {
			 $("#dp4").val('<?= $Yesterday;?>');
			 $("#dp4").datepicker('update' , '<?= $Yesterday;?>');
			 $("#dp5").datepicker('update' , '<?= $Yesterday;?>');
			 $("#dp4").change();
			 $("#dp5").change();
			 $("#dp5").val('<?= $Yesterday;?>');
		 }
		 function currentweekDate(dt,df)
		 {
			 $("#dp4").val('<?= $monday;?>');
			 $("#dp4").datepicker('update' , '<?= $monday;?>');
			 $("#dp5").datepicker('update' , '<?= $sunday;?>');
			 $("#dp5").val('<?= $sunday;?>');
		 }
		 function previousweekDate(dt,df)
		 {
			 $("#dp4").val('<?= $Pmonday;?>');
			 $("#dp4").datepicker('update' , '<?= $Pmonday;?>');
			 $("#dp5").datepicker('update' , '<?= $Psunday;?>');
			 $("#dp5").val('<?= $Psunday;?>');
		 }
		 function currentmonthDate(dt,df)
		 {
			 $("#dp4").val('<?= $currmonthFDate;?>');
			 $("#dp4").datepicker('update' , '<?= $currmonthFDate;?>');
			 $("#dp5").datepicker('update' , '<?= $currmonthTDate;?>');
			 $("#dp5").val('<?= $currmonthTDate;?>');
		 }
		 function previousmonthDate(dt,df)
		 {
			 $("#dp4").val('<?= $prevmonthFDate;?>');
			 $("#dp4").datepicker('update' , '<?= $prevmonthFDate;?>');
			 $("#dp5").datepicker('update' , '<?= $prevmonthTDate;?>');
			 $("#dp5").val('<?= $prevmonthTDate;?>');
		 }
		 function currentyearDate(dt,df)
		 {
			 $("#dp4").val('<?= $curryearFDate;?>');
			 $("#dp4").datepicker('update' , '<?= $curryearFDate;?>');
			 $("#dp5").datepicker('update' , '<?= $curryearTDate;?>');
			 $("#dp5").val('<?= $curryearTDate;?>');
		 }
		 function previousyearDate(dt,df)
		 {
			 $("#dp4").val('<?= $prevyearFDate;?>');
			 $("#dp4").datepicker('update' , '<?= $prevyearFDate;?>');
			 $("#dp5").datepicker('update' , '<?= $prevyearTDate;?>');
			 $("#dp5").val('<?= $prevyearTDate;?>');
		 }
		$("#Search").on('click', function(){
			 if($("#dp5").val() < $("#dp4").val()){
				 alert("From date should be lesser than To date.")
				 return false;
			 }else {
				var action = $("#_list_form").attr('action');
                var formValus = $("#frmsearch").serialize();
                window.location.href = action+"?"+formValus;
			 }
		});
		$(function () {
		  $("select.filter-by-text").each(function(){
			  $(this).select2({
					placeholder: $(this).attr('data-text'),
					allowClear: true
			  }); //theme: 'classic'
			});
		});
		$('#searchCompany').change(function() {
		    var company_id = $(this).val(); //get the current value's option
		    $.ajax({
		        type:'POST',
		        url:'ajax_find_driver_by_company.php',
		        data:{'company_id':company_id},
				cache: false,
		        success:function(data){
		            $(".driver_container").html(data);
		        }
		    });
		});
		$(function(){
          $("#cnl_booking").on('click', function(e) {
             var cancel_reason = $('#cancel_reason');
             var cancelcharge = $('#fCancellationCharge');
             if(!cancel_reason.val()) {
              $(".cnl_error").html("This Field is required.");
              return false;
             } else if(!cancelcharge.val()){
             	$(".cancelcharge_error").html("This Field is required.");
              	return false;
             }else {
              $( "#delete_form1" )[0].submit();
             }

          });
        });
        $('.delete_form').on('hidden.bs.modal', function () {
			window.location.reload();
		});
    </script>
</body>
<!-- END BODY-->
</html>
