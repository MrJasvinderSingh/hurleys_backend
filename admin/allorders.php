<?php
include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$generalobjAdmin->check_member_login();
$default_lang = $generalobj->get_default_lang();

$script = $_REQUEST['type'] == 'processing' ? "Processing Orders" : "All Orders";

/* $rdr_ssql = "";
  if (SITE_TYPE == 'Demo') {
  $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
  }
 */

//data for select fields
$sql = "select iCompanyId,vCompany from company WHERE eStatus != 'Deleted' $rdr_ssql";
$db_company = $obj->MySQLSelect($sql);

$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName from register_driver WHERE eStatus != 'Deleted' $rdr_ssql";
$db_drivers = $obj->MySQLSelect($sql);

$sql = "select iUserId,CONCAT(vName,' ',vLastName) AS riderName from register_user WHERE eStatus != 'Deleted' $rdr_ssql";
$db_rider = $obj->MySQLSelect($sql);

$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName from register_driver WHERE eStatus != 'Deleted'";
$db_driver = $obj->MySQLSelect($sql);
//data for select fields

$order_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$processing_status_array = array('1', '2', '4', '5');
$all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12');

if ($_REQUEST['iStatusCode'] != '') {
    $all_status_array = array($_REQUEST['iStatusCode']);
}
if ($order_type == 'processing') {
    $iStatusCode = '(' . implode(',', $processing_status_array) . ')';
} else {
    $iStatusCode = '(' . implode(',', $all_status_array) . ')';
}

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$promocode = isset($_REQUEST['promocode']) ? $_REQUEST['promocode'] : '';

$ord = ' ORDER BY o.iOrderId DESC';
if ($sortby == 1) {

    if ($order == 0)
        $ord = " ORDER BY o.tOrderRequestDate ASC";
    else
        $ord = " ORDER BY o.tOrderRequestDate DESC";
}

if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY riderName ASC";
    else
        $ord = " ORDER BY riderName DESC";
}

if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY c.vCompany ASC";
    else
        $ord = " ORDER BY c.vCompany DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY driverName ASC";
    else
        $ord = " ORDER BY driverName DESC";
}

//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';


if ($startDate != '') {
    $ssql .= " AND Date(o.tOrderRequestDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(o.tOrderRequestDate) <='" . $endDate . "'";
}
if ($serachTripNo != '') {
    $ssql .= " AND o.vOrderNo ='" . $serachTripNo . "'";
}
if ($searchCompany != '') {
    $ssql .= " AND c.iCompanyId ='" . $searchCompany . "'";
}

if ($searchRider != '') {
    $ssql .= " AND o.iUserId ='" . $searchRider . "'";
}

if ($searchDriver != '') {
    $ssql .= " AND o.iDriverId ='" . $searchDriver . "'";
}

if ($searchServiceType != '') {
    $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "'";
}

$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
}

if (!empty($promocode) && isset($promocode)) {
    $ssql .= " AND o.vCouponCode LIKE '" . $promocode . "' AND o.iStatusCode=6";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(o.iOrderId) AS Total FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId=o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceId=o.iServiceId WHERE o.iStatusCode IN $iStatusCode $ssql $trp_ssql";
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
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End

$sql = "SELECT o.fSubTotal,o.iServiceid,sc.vServiceName_" . $default_lang . " as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.cookingtime,o.deliverytime,o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fpretip,o.posttip,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem,CONCAT('<b>Phone: </b> +',u.vPhoneCode,' ',u.vPhone)  as user_phone,CONCAT('<b>Phone: </b> +',d.vCode,' ',d.vPhone) as driver_phone,CONCAT('<b>Phone: </b> +',c.vCode,' ',c.vPhone) as resturant_phone FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE o.iStatusCode IN $iStatusCode $ssql $trp_ssql $ord LIMIT $start, $per_page";

$DBProcessingOrders = $obj->MySQLSelect($sql);

$endRecord = count($DBProcessingOrders);

$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$Today = Date('Y-m-d');
$tdate = date("d") - 1;
$mdate = date("d");
$Yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

$curryearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));
$curryearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
$prevyearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));
$prevyearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));

$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));

$monday = date('Y-m-d', strtotime('sunday this week -1 week'));
$sunday = date('Y-m-d', strtotime('saturday this week'));

$Pmonday = date('Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date('Y-m-d', strtotime('saturday this week -1 week'));


if ($action == 'cancel' && $hdn_del_id != '') {

    $vCancelReason = isset($_REQUEST['cancel_reason']) ? $_REQUEST['cancel_reason'] : '';
    $fCancellationCharge = isset($_REQUEST['fCancellationCharge']) ? $_REQUEST['fCancellationCharge'] : '';
    $fDeliveryCharge = isset($_REQUEST['fDeliveryCharge']) ? $_REQUEST['fDeliveryCharge'] : '';
    $fRestaurantPayAmount = isset($_REQUEST['fRestaurantPayAmount']) ? $_REQUEST['fRestaurantPayAmount'] : '';

    $iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
    $iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';

    $vIP = get_client_ip();

    $oSql = "SELECT fWalletDebit,iUserId,vOrderNo,ePaymentOption,fNetTotal FROM orders WHERE iOrderId = '" . $hdn_del_id . "'";
    $wallet_data = $obj->MySQLSelect($oSql);

    if ($wallet_data[0]['fWalletDebit'] > 0) {
        $iUserId = $wallet_data[0]['iUserId'];
        $iBalance = $wallet_data[0]['fWalletDebit'];
        $vOrderNo = $wallet_data[0]['vOrderNo'];
        $eFor = 'Deposit';
        $eType = 'Credit';
        $tDescription = "#LBL_CREDITED_BOOKING#" . $vOrderNo;
        $ePaymentStatus = 'Unsettelled';
        $dDate = Date('Y-m-d H:i:s');
        $eUserType = 'Rider';

        $generalobj->InsertIntoUserWallet($iUserId, $eUserType, $iBalance, $eType, $hdn_del_id, $eFor, $tDescription, $ePaymentStatus, $dDate);
    }

    $query = "UPDATE orders SET iStatusCode = '8' , eCancelledBy= 'Admin' ,fCancellationCharge = '" . $fCancellationCharge . "',fRestaurantPayAmount = '" . $fRestaurantPayAmount . "' ,vCancelReason='" . $vCancelReason . "' WHERE iOrderId = '" . $hdn_del_id . "'";
    $obj->sql_query($query);

    $lquery = "INSERT INTO `order_status_logs`(`iOrderId`, `iStatusCode`, `dDate`, `vIp`) VALUES ('" . $hdn_del_id . "','8',Now(),'" . $vIP . "')";
    $obj->sql_query($lquery);

    //if($wallet_data[0]['ePaymentOption'] != 'Card' &&  $wallet_data[0]['fNetTotal'] > 0 ){
    if ($fCancellationCharge > 0) {
        $query_trip_outstanding_amount = "INSERT INTO `trip_outstanding_amount`(`iOrderId`, `iTripId`, `iUserId`, `iDriverId`,`iCompanyId`,`fCancellationFare`) VALUES ('" . $hdn_del_id . "','" . $iTripId . "','" . $iUserId . "','" . $iDriverId . "','" . $iCompanyId . "','" . $fCancellationCharge . "')";

        $last_insert_id = $obj->MySQLInsert($query_trip_outstanding_amount);

        $sql = "SELECT * FROM currency WHERE 1=1";
        $db_curr = $obj->MySQLSelect($sql);
        $where = "iTripOutstandId = '" . $last_insert_id . "'";
        for ($i = 0; $i < count($db_curr); $i++) {
            $data_currency_ratio['fRatio_' . $db_curr[$i]['vName']] = $db_curr[$i]['Ratio'];
            $obj->MySQLQueryPerform("trip_outstanding_amount", $data_currency_ratio, 'update', $where);
        }
    }
    //}   

    $query_driverPayment = "UPDATE trips SET  fDeliveryCharge ='" . $fDeliveryCharge . "' WHERE iOrderId = '" . $hdn_del_id . "'";
    $obj->sql_query($query_driverPayment);


    $uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";
    $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations", "PUBNUB_DISABLED");
    $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
    $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");
    ## Send Notification To User  ##
    $Message = "OrderCancelByAdmin";

    $sql = "select ru.iUserId,ru.iGcmRegId,ru.eDeviceType,ru.tSessionId,ru.iAppVersion,ru.vLang,ord.vOrderNo from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $hdn_del_id . "'";
    $data_order = $obj->MySQLSelect($sql);

    $vLangCode = $data_order[0]['vLang'];
    $vOrderNo = $data_order[0]['vOrderNo'];
    $iUserId = $data_order[0]['iUserId'];

    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $vTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : '';
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $alertMsg = $languageLabelsArr['LBL_CANCEL_ORDER_ADMIN_TXT'] . " #" . $vOrderNo . " " . $languageLabelsArr['LBL_REASON_TXT'] . " " . $vTitleReasonMessage;
    $message_arr = array();
    $message_arr['Message'] = $Message;
    $message_arr['iOrderId'] = $hdn_del_id;
    $message_arr['vOrderNo'] = $vOrderNo;
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['title'] = 'Order Canceled By Admin';
    $message_arr['tSessionId'] = $data_order[0]['tSessionId'];
    
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
    if ($PUBNUB_DISABLED == "Yes") {
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

    /* if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
      $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY,"subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
      $channelName = "PASSENGER_".$iUserId;
      $info = $pubnub->publish($channelName, $message);
      if($eDeviceType != "Android"){
      array_push($deviceTokens_arr_ios, $iGcmRegId);
      }
      } */

    if ($alertSendAllowed == true) {
        if ($eDeviceType == "Android") {
            array_push($registation_ids_new, $iGcmRegId);
            $Rmessage = array("message" => $message);
            $result = send_notification($registation_ids_new, $Rmessage, 0);
        } else {
            array_push($deviceTokens_arr_ios, $iGcmRegId);
			$message_arr['message'] = $Message;
			$message_arr['title'] = $alertMsg;
			$message_arr['apns'] = array("payload"=>array("aps"=>array("mutable-content"=>1)));
            sendApplePushNotification(0, $deviceTokens_arr_ios, $message_arr, $alertMsg, 0);
        }
    }
    ## Send Notification To User ## 
    ## Send Notification To Restaurant  ##
    $Message = "OrderCancelByAdmin";
    $sql = "select c.iCompanyId,c.iGcmRegId,c.eDeviceType,c.tSessionId,c.iAppVersion,c.vLang,o.vOrderNo from orders as o LEFT JOIN company as c ON o.iCompanyId=c.iCompanyId where o.iOrderId = '" . $hdn_del_id . "'";
    $Resdata_order = $obj->MySQLSelect($sql);

    $ResLangCode = $Resdata_order[0]['vLang'];
    $ResOrderNo = $Resdata_order[0]['vOrderNo'];
    $iCompanyId = $Resdata_order[0]['iCompanyId'];

    if ($ResLangCode == "" || $ResLangCode == NULL) {
        $ResLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $ResTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : '';
    $ReslanguageLabelsArr = getLanguageLabelsArr($ResLangCode, "1");
    $ResAlertMsg = $ReslanguageLabelsArr['LBL_CANCEL_ORDER_ADMIN_TXT'] . " #" . $ResOrderNo . " " . $ReslanguageLabelsArr['LBL_REASON_TXT'] . " " . $ResTitleReasonMessage;
    $message_arr_res = array();
    $message_arr_res['Message'] = $Message;
    $message_arr_res['iOrderId'] = $hdn_del_id;
    $message_arr_res['vOrderNo'] = $ResOrderNo;
    $message_arr_res['vTitle'] = $ResAlertMsg;
    $message_arr_res['tSessionId'] = $Resdata_order[0]['tSessionId'];
    $message_arr_res['apns'] = array("payload"=>array("aps"=>array("mutable-content"=>1)));
    $restaurantmessage = json_encode($message_arr_res, JSON_UNESCAPED_UNICODE);
    if ($PUBNUB_DISABLED == "Yes") {
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

    /* if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
      $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY,"subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
      $RestaurantchannelName = "COMPANY_".$iCompanyId;
      $info = $pubnub->publish($RestaurantchannelName, $restaurantmessage);
      if($eDeviceType != "Android"){
      array_push($restaurantdeviceTokens_arr_ios, $iGcmRegId);
      }
      } */

    if ($alertSendAllowed == true) {
        if ($eDeviceType == "Android") {
            array_push($restuarantregistation_ids_new, $iGcmRegId);
            $Rmessage = array("message" => $restaurantmessage);
            $result = send_notification($restuarantregistation_ids_new, $Rmessage, 0);
        } else {
            array_push($restaurantdeviceTokens_arr_ios, $iGcmRegId);
            sendApplePushNotification(2, $restaurantdeviceTokens_arr_ios, $message_arr_res, $alertMsg, 0);
        }
    }
    ## Send Notification To Restaurant  ##
    ## Send Notification To Driver ##
    $query1 = "select * from order_status_logs where iOrderId = '" . $hdn_del_id . "' AND iStatusCode = '4'";
    $OrdersData = $obj->MySQLSelect($query1);

    if (count($OrdersData) > 0) {
        $Message = "OrderCancelByAdmin";
        $sql = "select d.iDriverId,d.iGcmRegId,d.eDeviceType,d.tSessionId,d.iAppVersion,d.vLang,o.vOrderNo from orders as o LEFT JOIN register_driver as d ON o.iDriverId=d.iDriverId where o.iOrderId = '" . $hdn_del_id . "'";
        $drv_data_order = $obj->MySQLSelect($sql);

        $drvLangCode = $drv_data_order[0]['vLang'];
        $drvOrderNo = $drv_data_order[0]['vOrderNo'];
        $iDriverId = $drv_data_order[0]['iDriverId'];

        $query1 = "UPDATE register_driver SET vTripStatus = 'Cancelled' WHERE iDriverId = '" . $iDriverId . "'";
        $obj->sql_query($query1);

        $query2 = "UPDATE trips SET iActive = 'Canceled' WHERE iOrderId = '" . $hdn_del_id . "'";
        $obj->sql_query($query2);

        if ($drvLangCode == "" || $drvLangCode == NULL) {
            $drvLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }

        $drvTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : '';
        $drvlanguageLabelsArr = getLanguageLabelsArr($drvLangCode, "1");
        $drvAlertMsg = $drvlanguageLabelsArr['LBL_CANCEL_ORDER_ADMIN_TXT'] . " #" . $drvOrderNo . " " . $drvlanguageLabelsArr['LBL_REASON_TXT'] . " " . $drvTitleReasonMessage;
        $message_arr_res = array();
        $message_arr_res['Message'] = $Message;
        $message_arr_res['iOrderId'] = $hdn_del_id;
        $message_arr_res['vOrderNo'] = $drvOrderNo;
        $message_arr_res['vTitle'] = $drvAlertMsg;
        $message_arr_res['tSessionId'] = $drv_data_order[0]['tSessionId'];
        $message_arr_res['apns'] = array("payload"=>array("aps"=>array("mutable-content"=>1)));
        $drvmessage = json_encode($message_arr_res, JSON_UNESCAPED_UNICODE);
        if ($PUBNUB_DISABLED == "Yes") {
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
        /*
          if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
          $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY,"subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
          $DriverchannelName = "DRIVER_".$iCompanyId;
          $info = $pubnub->publish($DriverchannelName, $drvmessage);
          if($eDeviceType != "Android"){
          array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
          }
          } */



        /* For IOS change */
        $MsgCodde = strval(time() . mt_rand(1000, 9999));
        $data_userRequest = array();
        $data_userRequest['iUserId'] = $iUserId;
        $data_userRequest['iDriverId'] = $iDriverId;
        $data_userRequest['tMessage'] = $drvmessage;
        $data_userRequest['iMsgCode'] = $MsgCodde;
        $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
        $requestId = addToUserRequest2($data_userRequest);
        $data_driverRequest = array();
        $data_driverRequest['iDriverId'] = $iDriverId;
        $data_driverRequest['type'] = 'unassign';
        $data_driverRequest['iRequestId'] = $requestId;
        $data_driverRequest['iUserId'] = $iUserId;
        $data_driverRequest['iTripId'] = 0;
        $data_driverRequest['iOrderId'] = $hdn_del_id;
        $data_driverRequest['eStatus'] = "Timeout";
        $data_driverRequest['vMsgCode'] = $MsgCodde;
        $data_driverRequest['vStartLatlong'] = '0';
        $data_driverRequest['vEndLatlong'] = '0';
        $data_driverRequest['tStartAddress'] = '0';
        $data_driverRequest['tEndAddress'] = '0';
        $data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
        addToDriverRequest2($data_driverRequest);

        /* For IOS change close */

        if ($alertSendAllowed == true) {
            if ($eDeviceType == "Android") {
                array_push($drvregistation_ids_new, $iGcmRegId);
                $Dmessage = array("message" => $drvmessage);
                $result = send_notification($drvregistation_ids_new, $Dmessage, 0);
            } else {
                array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
                sendApplePushNotification(0, $drvdeviceTokens_arr_ios, $message_arr_res, $alertMsg, 0);
            }
        }
    }

    ## Send Notification To Driver ##

    $sql1 = "SELECT tOrderRequestDate,vOrderNo,iUserId,iDriverId,iCompanyId FROM orders WHERE iOrderId=" . $hdn_del_id;
    $bookind_detail = $obj->MySQLSelect($sql1);
    $tOrderRequestDateMail = $bookind_detail[0]['tOrderRequestDate'];
    $vOrderNoMail = $bookind_detail[0]['vOrderNo'];

    $sql2 = "SELECT vName,vLastName,vEmail,iDriverVehicleId,vPhone,vcode,vLang FROM register_driver WHERE iDriverId=" . $iDriverId;
    $driver_db = $obj->MySQLSelect($sql2);
    $vPhone = $driver_db[0]['vPhone'];
    $vcode = $driver_db[0]['vcode'];
    $vLang = $driver_db[0]['vLang'];

    $SQL3 = "SELECT vName,vLastName,vEmail,iUserId,vPhone,vPhoneCode,vLang FROM register_user WHERE iUserId = '" . $iUserId . "'";
    $user_detail = $obj->MySQLSelect($SQL3);
    $vPhone1 = $user_detail[0]['vPhone'];
    $vcode1 = $user_detail[0]['vPhoneCode'];
    $vLang1 = $user_detail[0]['vLang'];

    $sql4 = "select vCompany,vEmail,vPhone,vcode from company where iCompanyId='" . $iCompanyId . "'";
    $comapny_detail = $obj->MySQLSelect($sql4);
    $vPhone = $comapny_detail[0]['vPhone'];
    $vcode = $comapny_detail[0]['vcode'];
    $vLang2 = $default_lang;


    $Data1['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $Data1['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $Data1['vRiderMail'] = $user_detail[0]['vEmail'];
    $Data1['vSourceAddresss'] = $vSourceAddresss;
    $Data1['dBookingdate'] = $tOrderRequestDateMail;
    $Data1['vBookingNo'] = $vOrderNoMail;

    $Data['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $Data['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $Data['vDriverMail'] = $driver_db[0]['vEmail'];
    $Data['vSourceAddresss'] = $vSourceAddresss;
    $Data['dBookingdate'] = $tOrderRequestDateMail;
    $Data['vBookingNo'] = $vOrderNoMail;

    $Data2['vCompany'] = $comapny_detail[0]['vCompany'];
    $Data2['vCompanyMail'] = $comapny_detail[0]['vEmail'];
    $Data2['vSourceAddresss'] = $vSourceAddresss;
    $Data2['dBookingdate'] = $tOrderRequestDateMail;
    $Data2['vBookingNo'] = $vOrderNoMail;

    if ($iDriverId != 0) {
        $return = $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN_TO_DRIVER", $Data);
    }

    $return1 = $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN_TO_COMPANY", $Data2);
    $return1 = $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN_TO_USER", $Data1);

    $Booking_Date = @date('d-m-Y', strtotime($tOrderRequestDateMail));
    $Booking_Time = @date('H:i:s', strtotime($tOrderRequestDateMail));

    $maildata['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $maildata['dBookingdate'] = $Booking_Date;
    $maildata['dBookingtime'] = $Booking_Time;
    $maildata['vBookingNo'] = $vOrderNoMail;

    $maildata1['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $maildata1['dBookingdate'] = $Booking_Date;
    $maildata1['dBookingtime'] = $Booking_Time;
    $maildata1['vBookingNo'] = $vOrderNoMail;

    $maildataCompany['vCompany'] = $comapny_detail[0]['vCompany'];
    $maildataCompany['dBookingdate'] = $Booking_Date;
    $maildataCompany['dBookingtime'] = $Booking_Time;
    $maildataCompany['vBookingNo'] = $vOrderNoMail;

    if ($iDriverId != 0) {
        $message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE_JOB_CANCEL", $maildata1, "", $vLang);
        $return5 = $generalobj->sendUserSMS($vPhone, $vcode, $message_layout, "");
    }

    $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_JOB_CANCEL", $maildata, "", $vLang1);
    $return4 = $generalobj->sendUserSMS($vPhone1, $vcode1, $message_layout, "");

    $message_layout = $generalobj->send_messages_user("COMPANY_SEND_MESSAGE_JOB_CANCEL", $maildataCompany, "", $vLang2);
    $return6 = $generalobj->sendUserSMS($vPhone2, $vcode2, $message_layout, "");

    echo "<script>location.href='allorders.php?type=" . $order_type . "'</script>";
}

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);

function GetTimeDiffInMinutesBwTwoTime($starttime, $Enddate) {
    // $starttime = date('Y-m-d H:i:s');
    if ($starttime != '' && $Enddate != '') {
        $start_date = new DateTime($starttime);
        $since_start = $start_date->diff(new DateTime($Enddate));
        $minutes = $since_start->days * 24 * 60;
        $minutes += $since_start->h * 60;
        $minutes += $since_start->i;
        return $minutes;
    } else {
        return '--';
    }
}




function getAllOrderStatusFromIorderid($iOrderId)
{
    global $obj;
    $SqlOrderStatus = "SELECT `dDate`,`iStatusCode` FROM `order_status_logs` WHERE `iOrderId` = '$iOrderId'  AND `iStatusCode` in (1,2,5,6) ORDER BY `iStatusCode` ASC;"; 
        $AllOrderstatus = $obj->MySQLSelect($SqlOrderStatus);
        $placed = 0;
        $acceptedtime = 0;
        $pickeduptime = 0;
        $deliveredtime = 0;
        
        if(count($AllOrderstatus) > 0)
        {
            
            foreach($AllOrderstatus as $OrderStat):
                if($OrderStat['iStatusCode'] == 2)
                {
                    $acceptedtime = $OrderStat['dDate'];
                }
                elseif($OrderStat['iStatusCode'] == 5)
                {
                    $pickeduptime = $OrderStat['dDate'];
                }
                elseif($OrderStat['iStatusCode'] == 6)
                {
                    $deliveredtime = $OrderStat['dDate'];
                }
                else
                {
                    $placed = $OrderStat['dDate'];
                }
                
            endforeach;
        }
        
        $response = array(
            '1'=>$placed,
            '2'=>$acceptedtime,
            '5'=>$pickeduptime,
            '6'=>$deliveredtime
        );
        
        return $response;
        
}
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_PROCESSING_ORDERS']; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>

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
                                <h2><?php //echo $langage_lbl_admin['LBL_PROCESSING_ORDERS']; ?> </h2>
                                <h2><?php echo $script; ?> </h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <!--  Search Form Start  -->
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page payment-report">
                            <input type="hidden" name="action" value="search" />
                            <input type="hidden" name="type" value="<?= $order_type; ?>" />
                            <h3>Search <?php echo $langage_lbl_admin['LBL_PROCESSING_ORDERS']; ?> ...</h3>
                            <span>
                                <a onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                                <a onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                                <a onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                                <a onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                                <a onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                                <a onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                                <a onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                                <a onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                            </span> 
                            <span>
                                <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default;background-color: #fff" />
                                <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default;background-color: #fff"/>

                                <div class="col-sm-2 select001">
                                    <select class="form-control filter-by-text" name = "searchCompany" id="searchCompany" data-text="Select Store">
                                        <option value="">Select Store</option>
                                        <?php foreach ($db_company as $dbc) { ?>
                                            <option value="<?php echo $dbc['iCompanyId']; ?>" <?php if ($searchCompany == $dbc['iCompanyId']) {
                                            echo "selected";
                                        } ?>><?php echo $generalobjAdmin->clearCmpName($dbc['vCompany']); ?></option>
<?php } ?>
                                    </select>
                                </div>
                                <div class="col-sm-2 select001">
                                    <select class="form-control filter-by-text" name = "searchRider" data-text="Select <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>" id='searchUser'>
                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                                        <?php foreach ($db_rider as $dbr) { ?>
                                            <option value="<?php echo $dbr['iUserId']; ?>" <?php if ($searchRider == $dbr['iUserId']) {
                                            echo "selected";
                                        } ?>><?php echo $generalobjAdmin->clearName($dbr['riderName']); ?></option>
<?php } ?>
                                    </select>
                                </div>	
								
								<!--<div class="col-sm-2 select001" style="padding-right:15px;">
                                    <select class="form-control filter-by-text" name = "searchDriver" data-text="Select Driver" id='searchUser'>
                                        <option value="">Select <?php echo 'Driver'; ?></option>
                                        <?php 
										foreach ($db_driver as $dbr) { ?>
                                            <option value="<?php echo $dbr['iDriverId']; ?>" <?php if ($searchDriver == $dbr['iDriverId']) { echo "selected"; } ?>><?php echo $generalobjAdmin->clearName($dbr['driverName']); ?></option>
										<?php 
										} ?>
                                    </select>
								</div>-->
								 <? if(count($service_cat_data) > 1){ ?>
                        <div class="col-sm-2 select001" style="padding-right:15px;">
                            <select class="form-control filter-by-text" name = "searchServiceType" data-text="Select Serivce Type">
                                <option value="">Select <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
								<?php 
								foreach ($allservice_cat_data as $value) { ?>
									<option value="<?php echo $value['iServiceId']; ?>" <?php if ($searchServiceType == $value['iServiceId']) {
										echo "selected";
									} ?>>
										<?php echo $generalobjAdmin->clearName($value['vServiceName']); ?>
									</option>
								<?php 
								} ?>
                            </select>
                        </div>
                        <? } ?>
                        <div class="col-sm-2 select001">
                            <input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number" class="form-control search-trip001" value="<?php echo $serachTripNo; ?>"/>
                        </div>

                            </span>
                        </div>
                       
                        <div class="col-lg-10">
                            <div class="col-lg-12">
                                
                            </div>
                        </div>
                        <div class="tripBtns001">
                            <b>
								<button type="button" class="btnalt button11" onClick="showExportTypes('orders')" style='float:right'>Export</button>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />

                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'allorders.php?type=<?= $order_type ?>'"/>
                            </b>
                        </div>
                    </form>
					<?php 
//echo '<pre>';print_r($DBProcessingOrders);echo '</pre>';
?>
                    <!-- Search Form End -->
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover" >
                                            <thead>
                                                <tr>
                                                    <? if(count($service_cat_data) > 1){ ?>
                                                    <th class="text-center">Serivce Type</th>
                                                    <? } ?>
                                                    <th class="text-center"><?php echo $langage_lbl_admin['LBL_ORDER_NO_ADMIN']; ?>#</th>

                                                    <th class="text-center"><a href="javascript:void(0);" onClick="Redirect(1,<?php if ($sortby == '1') {
    echo $order;
} else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']; ?> <?php if ($sortby == 1) {
    if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th><a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2') {
    echo $order;
} else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> Name <?php if ($sortby == 2) {
    if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th><a href="javascript:void(0);" onClick="Redirect(3,<?php if ($sortby == '3') {
    echo $order;
} else { ?>0<?php } ?>)">Store Name <?php if ($sortby == 3) {
                                                    if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                        } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <!--<th><a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {
                                                    echo $order;
                                                } else { ?>0<?php } ?>)">Delivery Driver <?php if ($sortby == 4) {
                                                    if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                        } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th class="text-right">PreTip</th>
													<th class="text-right">PostTip</th>
													<th class="text-center">Pick Up Time (min)</th>
                                                    <th class="text-center">Delivery Time (min)</th>-->
                                                    <th class="text-right">Sub Total</th>
                                                     
                                                    <th class="text-right">Order Total</th>
                                                    <th class="text-center">Order Status</th>
                                                    
                                                    <th class="text-center">Payment Mode</th>
                                                    <th class="text-center">Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($DBProcessingOrders)) {

                                                    for ($i = 0; $i < $endRecord; $i++) {
                                                        
                                                       
                                                     $OrderSResponse =  getAllOrderStatusFromIorderid($DBProcessingOrders[$i]['iOrderId']);
                                                    // print_r($OrderSResponse);
                                                        
//                                                        $sqlOS = "SELECT os.vStatus,os.vStatus_Track,osl.dDate,osl.iStatusCode,ord.iUserId,ord.iCompanyId,ord.iDriverId,ord.cookingtime,ord.deliverytime,ord.iStatusCode as OrderCurrentStatusCode,ord.iUserAddressId,ord.vOrderNo,ord.tOrderRequestDate,ord.fNetTotal FROM order_status_logs as osl LEFT JOIN order_status as os ON osl.iStatusCode = os.iStatusCode LEFT JOIN orders as ord ON osl.iOrderId=ord.iOrderId WHERE osl.iOrderId = '" . $DBProcessingOrders[$i]['iOrderId'] . "' AND osl.iStatusCode IN(2) ORDER BY osl.iOrderLogId ASC";
//                                                        $OrderStatus = $obj->MySQLSelect($sqlOS);
//
//                                                        $sqlOS2 = "SELECT os.vStatus,os.vStatus_Track,osl.dDate,osl.iStatusCode,ord.iUserId,ord.iCompanyId,ord.iDriverId,ord.cookingtime,ord.deliverytime,ord.iStatusCode as OrderCurrentStatusCode,ord.iUserAddressId,ord.vOrderNo,ord.tOrderRequestDate,ord.fNetTotal FROM order_status_logs as osl LEFT JOIN order_status as os ON osl.iStatusCode = os.iStatusCode LEFT JOIN orders as ord ON osl.iOrderId=ord.iOrderId WHERE osl.iOrderId = '" . $DBProcessingOrders[$i]['iOrderId'] . "' AND osl.iStatusCode IN(5) ORDER BY osl.iOrderLogId ASC";
//                                                        $OrderStatus2 = $obj->MySQLSelect($sqlOS2);
//
//                                                        $pDate = '';
//                                                        for ($j = 0; $j < count($OrderStatus2); $j++) {
//                                                            $pDate = $OrderStatus2[$j]['dDate'];
//                                                        }
//                                                        $pDate1 = '';
//                                                        for ($j = 0; $j < count($OrderStatus); $j++) {
//                                                            $pDate1 = $OrderStatus[$j]['dDate'];
//                                                        }
//                                                        $pickupminutes = GetTimeDiffInMinutesBwTwoTime($pDate1, $pDate);
//                                                        $sqlOS1 = "SELECT os.vStatus,os.vStatus_Track,osl.dDate,osl.iStatusCode,ord.iUserId,ord.iCompanyId,ord.iDriverId,ord.cookingtime,ord.deliverytime,ord.iStatusCode as OrderCurrentStatusCode,ord.iUserAddressId,ord.vOrderNo,ord.tOrderRequestDate,ord.fNetTotal FROM order_status_logs as osl LEFT JOIN order_status as os ON osl.iStatusCode = os.iStatusCode LEFT JOIN orders as ord ON osl.iOrderId=ord.iOrderId WHERE osl.iOrderId = '" . $DBProcessingOrders[$i]['iOrderId'] . "' AND osl.iStatusCode IN(6) ORDER BY osl.iOrderLogId ASC";
//                                                        $OrderStatus1 = $obj->MySQLSelect($sqlOS1);
//                                                        $dDate = '';
//                                                        for ($j = 0; $j < count($OrderStatus1); $j++) {
//                                                            $dDate = $OrderStatus1[$j]['dDate'];
//                                                        }
//                                                        $deliveryminutes = GetTimeDiffInMinutesBwTwoTime($dDate, $DBProcessingOrders[$i]['tOrderRequestDate']);
                                                     
                                                     $pickupminutes = 0;
                                                     $deliveryminutes = 0;
                                                     
                                                     
                                                     if(!empty($OrderSResponse[5]))
                                                     {
                                                         $pickupminutes = GetTimeDiffInMinutesBwTwoTime($OrderSResponse[2], $OrderSResponse[5]);
                                                     }
                                                     
                                                     
                                                     if(!empty($OrderSResponse[6]))
                                                     {
                                                         $deliveryminutes = GetTimeDiffInMinutesBwTwoTime($OrderSResponse[1], $OrderSResponse[6]);
                                                     }
                                                     
                                                     
                                                     unset($OrderSResponse);
                                                        ?>

                                                        <tr class="gradeA">
                                                            <? if(count($service_cat_data) > 1){ ?>
                                                            <td class="text-center"><?= $DBProcessingOrders[$i]['vServiceName']; ?></td>
                                                            <? } ?>
                                                            <td class="text-center"><a href="invoice.php?iOrderId=<?= $DBProcessingOrders[$i]['iOrderId'] ?>" target="_blank"><?= $DBProcessingOrders[$i]['vOrderNo']; ?></a></td>

                                                            <td class="text-center">
															<?= $generalobjAdmin->DateTime($DBProcessingOrders[$i]['tOrderRequestDate'], 'yes') ?>

                                                            </td>
                                                            <td><?= $generalobjAdmin->clearName($DBProcessingOrders[$i]['riderName']); ?><br>
                                                                <? if(!empty($DBProcessingOrders[$i]['user_phone'])) { 
                                                                echo  $generalobjAdmin->clearPhone($DBProcessingOrders[$i]['user_phone']);
                                                                }?>

                                                            </td>

                                                            <td><?= $generalobjAdmin->clearCmpName($DBProcessingOrders[$i]['vCompany']); ?><br>
                                                                <? if(!empty($DBProcessingOrders[$i]['resturant_phone'])) { 
                                                                echo  $generalobjAdmin->clearPhone($DBProcessingOrders[$i]['resturant_phone']);
                                                                }?>

                                                            </td>
                                                           <!-- <td>
																<?php
																if (!empty($DBProcessingOrders[$i]['driverName'])) {
																	echo $generalobjAdmin->clearName($DBProcessingOrders[$i]['driverName']);
																}
																?>
                                                                <br>
                                                                <? if(!empty($DBProcessingOrders[$i]['driver_phone'])){ 
                                                                echo $generalobjAdmin->clearPhone($DBProcessingOrders[$i]['driver_phone']);
                                                                }?>	
                                                            </td>-->

															<!-- <td><?= $DBProcessingOrders[$i]['TotalItem'] ?></td> -->
                                                            <!--<td class="text-right"><?= $generalobj->trip_currency($DBProcessingOrders[$i]['fpretip'],$DBProcessingOrders[$i]['fRatio_KYD']); ?></td>
															<td class="text-right"><?= $generalobj->trip_currency($DBProcessingOrders[$i]['posttip']); ?></td>
															
															<td class="text-center"><?= /* $generalobjAdmin->DateTime($pDate,'yes').'---'. */ $pickupminutes ?></td>
                                                            <td class="text-center"><?= /* $generalobjAdmin->DateTime($dDate,'yes').'---'. */ $deliveryminutes ?></td>
															-->
                                                            <td class="text-right"><?= $generalobj->trip_currency($DBProcessingOrders[$i]['fNetTotal']); ?></td>
                                                            
                                                            <td class="text-right"><?= $generalobj->trip_currency( ($DBProcessingOrders[$i]['fNetTotal'] + $DBProcessingOrders[$i]['posttip'])); ?></td>
                                                            <td class="text-center"><?= $DBProcessingOrders[$i]['vStatus'] ?></td>
                                                            

                                                            <td class="text-center"><?= $DBProcessingOrders[$i]['ePaymentOption'] ?></td>
                                                            <td class="text-center">

															<?php if (in_array($DBProcessingOrders[$i]['iStatusCode'], $processing_status_array)): ?>
															<!--  <button type="button" class="btn btn-info" data-toggle="modal" data-target="#delete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>">Cancel Order</button> -->

                                                                    <a href="#"   data-target="#delete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class=" custom-order btn btn-info" data-toggle="modal" data-id="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">Cancel Order</a> 

                                                                    <!-- Modal -->

                                                                    <div id="delete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class="modal fade delete_form text-left" role="dialog">
                                                                        <div class="modal-dialog">
                                                                            <!-- Modal content-->
                                                                            <div class="modal-content">

                                                                                <div class="modal-header">
                                                                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                                                                    <h4 class="modal-title">Cancel Order</h4>
                                                                                </div>

                                                                                <form role="form" name="delete_form" id="delete_form1" method="post" action="" class="margin0">

                                                                                    <div class="modal-body">
                                                                                        <div class="form-group col-lg-12" style="display: inline-block;">
                                                                                            <label class="col-lg-4 control-label">Cancellation Reason<span class="red">*</span></label>
                                                                                            <div class="col-lg-7">
                                                                                                <textarea name="cancel_reason" id="cancel_reason" rows="4" cols="40" required="required"></textarea>
                                                                                                <div class="cnl_error error red"></div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="form-group col-lg-12" style="display: none;">
                                                                                            <label class="col-lg-4 control-label" style='display:none;'>Cancellation Charges To Apply For User<span class="red">*</span></label>
																							<?php $MIN_ORDER_CANCELLATION_CHARGES = $generalobj->getConfigurations("configurations", "MIN_ORDER_CANCELLATION_CHARGES"); ?>
                                                                                            <div class="col-lg-7">
                                                                                                <input type="hidden" name="fCancellationCharge" id="fCancellationCharge" value="0">
                                                                                                <div class="cancelcharge_error error red"></div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- 														            <div class="form-group col-lg-12" style="display: inline-block;">
                                                                                                                                                                                                                    
                                                                                                                                                                                                                    <label class="col-lg-4 control-label">Payment To Driver<span class="red">*</span></label>
                                                                                                                                                                                                                    
            <?php $payment_to_driver = $generalobjAdmin->getPaymentToDriver($DBProcessingOrders[$i]['iOrderId']); ?>
                                                                                                                                                                                                                    
                                                                                                                                                                                                                    <div class="col-lg-7"> -->
                                                                                        <input type="hidden" name="fDeliveryCharge" id="fDeliveryCharge" value="<?php echo $payment_to_driver; ?>">
                                                                                        <!-- 														                	<?php if ($payment_to_driver == 0): ?>
                                                                                                                                                                                                                        Driver not Assign 
                                                                                            <?php else: ?>
                                                                                                <?php $DBProcessingOrders[$i]['driverName']; ?>
            <?php endif; ?>
                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                    </div> -->

                                                                                        <!-- 														           <div class="form-group col-lg-12" style="display: inline-block;">
                                                                                                                                                                                                                    <label class="col-lg-4 control-label">Payment To Restaurant<span class="red">*</span></label>
                                                                                        
            <?php $payment_to_restaurant = $generalobjAdmin->getPaymentToRestaurant($DBProcessingOrders[$i]['iOrderId']); ?>
                                                                                                                                                                                                                    
                                                                                                                                                                                                                    <div class="col-lg-7"> -->
                                                                                        <input type="hidden" name="fRestaurantPayAmount" id="fRestaurantPayAmount"  value="<?php echo $payment_to_restaurant; ?>">
                                                                                        <!--  </div>
                                                                                     </div> -->


                                                                                        <!--<div class="form-group col-lg-12 col-md-offset-4">
                                                                                                    <!-- <p>Order Subtotal : <?php //echo $generalobj->trip_currency($DBProcessingOrders[$i]['fSubTotal']); ?></p>
                                                                                                    
                                                                                                    <p>Restaurant Discount : 
            <?php //echo $generalobj->trip_currency($DBProcessingOrders[$i]['fOffersDiscount']); ?>
                                                                                                            
                                                                                                    </p>
                                                                                                    <p>Site Commision : 
            <?php //echo $generalobj->trip_currency($DBProcessingOrders[$i]['fCommision']); ?></p>
                                    
                                                                                                     <p>Delivery Charge : <?php //echo $generalobj->trip_currency($DBProcessingOrders[$i]['fDeliveryCharge']); ?>
                                                                                                      </p> 
                                                                                            <p>Expected Restaurant Payout : <?php //echo $generalobj->trip_currency($payment_to_restaurant); ?></p>
            <?php if ($payment_to_driver > 0) { ?>
                                                                                                <p>Expected Driver Payout : <?php //echo $generalobj->trip_currency($payment_to_driver); ?></p>
            <?php } ?>
                                                                                            <p>Expected Site Commission :<?php //echo $generalobj->trip_currency($DBProcessingOrders[$i]['fCommision']); ?></p>  
                                                                                        </div> -->

                                                                                        <input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">

                                                                                        <input type="hidden" name="iUserId" id="iUserId" value="<?= $DBProcessingOrders[$i]['iUserId']; ?>">

                                                                                        <input type="hidden" name="iDriverId" id="iDriverId" value="<?= $DBProcessingOrders[$i]['iDriverId']; ?>">

                                                                                        <input type="hidden" name="iCompanyId" id="iCompanyId" value="<?= $DBProcessingOrders[$i]['iCompanyId']; ?>">

                                                                                        <input type="hidden" name="action" id="action" value="cancel">
                                                                                        <!--<div class="form-group col-lg-12">
                                                                                            <label class="control-label">Notes:</label>
                                                                                            <p>
                                                                                                1. Set the cancellation charges as per the Order. Also, the expected payouts shown here are just for the your review to check how much to pay if the order will be delivered.</p>

                                                                                            <p>2. If this order contains any wallet settlement then wallet amount will be refunded back to user's wallet as soon as you mark this order as 'CANCEL'.</p>
                                                                                        </div>-->
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="submit" class="btn btn-info" id="cnl_booking1" title="Cancel Booking">Cancel Order</button>
                                                                                        <button type="button" class="btn btn-default" data-dismiss="modal" id="close_model">Close</button>
                                                                                    </div>
                                                                                </form> 

                                                                            </div>
                                                                            <!-- Modal content-->	
                                                                        </div>
                                                                    </div>
                                                                    <!-- Modal -->
                                                                    <script>
                                                                        $('#delete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>').on('show.bs.modal', function () {
                                                                            $("#fCancellationCharge").val("<?php echo $MIN_ORDER_CANCELLATION_CHARGES; ?>");
                                                                            $("#fDeliveryCharge").val("<?php echo $payment_to_driver; ?>");
                                                                            $("#fRestaurantPayAmount").val("<?php echo $payment_to_restaurant; ?>");

                                                                            $(".cancelcharge_error").html("");
                                                                            $(".cnl_error").html("");
                                                                        });
                                                                    </script>
																	<?php 
																	if($DBProcessingOrders[$i]['iStatusCode'] < 2){
																	?>
																		<a href="#"   data-target="#complete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class=" custom-order btn btn-info" data-toggle="modal" data-id="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">Confirm Order</a> 

																		<!-- Modal -->

																		<div id="complete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class="modal fade complete_form text-left" role="dialog">
																			<div class="modal-dialog">
																				<!-- Modal content-->
																				<div class="modal-content">

																					<div class="modal-header">
																						<button type="button" class="close" data-dismiss="modal">x</button>
																						<h4 class="modal-title">Confirm Order</h4>
																					</div>

																					<form role="form" name="complete_form" id="delete_form1" method="post" action="completeorder.php" class="margin0">

																						<div class="modal-body">
																							<input type='hidden' name ='orderstatus' value='OrderConfirmed'>
																							<input type="hidden" name="hdn_del_id" id="complete_hdn_del_id" value="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">
																							<input type="hidden" name="action" id="action" value="updatestatus">
																							<div class="form-group col-lg-12">
																								<p>
																									Are you sure to Confirm order?
																								</p>
																							</div>
																						</div>
																						<div class="modal-footer">
																							<button type="submit" class="btn btn-info" id="cnl_booking1" title="Cancel Booking">Confirm Order</button>
																							<button type="button" class="btn btn-default" data-dismiss="modal" id="close_model">Close</button>
																						</div>
																					</form> 

																				</div>
																				<!-- Modal content-->	
																			</div>
																		</div>
																		<!-- Modal -->
																		<script>
																			$('#complete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>').on('show.bs.modal', function () {
																				$(".cancelcharge_error").html("");
																				$(".cnl_error").html("");
																			});
																		</script>
																		<?php 
																	}else if($DBProcessingOrders[$i]['iStatusCode'] < 5){
																	?>
																		<a href="#"   data-target="#complete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class=" custom-order btn btn-info" data-toggle="modal" data-id="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">Order Prepared</a> 

																		<!-- Modal -->

																		<div id="complete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class="modal fade complete_form text-left" role="dialog">
																			<div class="modal-dialog">
																				<!-- Modal content-->
																				<div class="modal-content">

																					<div class="modal-header">
																						<button type="button" class="close" data-dismiss="modal">x</button>
																						<h4 class="modal-title">Order Prepared</h4>
																					</div>

																					<form role="form" name="complete_form" id="delete_form1" method="post" action="completeorder.php" class="margin0">

																						<div class="modal-body">
																							<input type='hidden' name ='orderstatus' value='OrderPrepared'>
																							<input type="hidden" name="hdn_del_id" id="complete_hdn_del_id" value="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">
																							<input type="hidden" name="action" id="action" value="updatestatus">
																							<div class="form-group col-lg-12">
																								<p>
																									Are you sure to prepare order?
																								</p>
																							</div>
																						</div>
																						<div class="modal-footer">
																							<button type="submit" class="btn btn-info" id="cnl_booking1" title="Cancel Booking">Order Prepared</button>
																							<button type="button" class="btn btn-default" data-dismiss="modal" id="close_model">Close</button>
																						</div>
																					</form> 

																				</div>
																				<!-- Modal content-->	
																			</div>
																		</div>
																		<!-- Modal -->
																		<script>
																			$('#complete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>').on('show.bs.modal', function () {
																				$(".cancelcharge_error").html("");
																				$(".cnl_error").html("");
																			});
																		</script>
																		<?php 
																	} else { ?>
																		<a href="#"   data-target="#complete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class=" custom-order btn btn-info" data-toggle="modal" data-id="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">Complete Order</a> 

																		<!-- Modal -->

																		<div id="complete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class="modal fade complete_form text-left" role="dialog">
																			<div class="modal-dialog">
																				<!-- Modal content-->
																				<div class="modal-content">

																					<div class="modal-header">
																						<button type="button" class="close" data-dismiss="modal">x</button>
																						<h4 class="modal-title">Complete Order</h4>
																					</div>

																					<form role="form" name="complete_form" id="delete_form1" method="post" action="completeorder.php" class="margin0">

																						<div class="modal-body">
																							<input type="hidden" name="hdn_del_id" id="complete_hdn_del_id" value="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">
																							<input type="hidden" name="action" id="action" value="updatestatus">
																							<input type='hidden' name ='orderstatus' value='OrderDelivered'>
																							<div class="form-group col-lg-12">
																								<p>
																									Are you sure to complete order?
																								</p>
																							</div>
																						</div>
																						<div class="modal-footer">
																							<button type="submit" class="btn btn-info" id="cnl_booking1" title="Cancel Booking">Complete Order</button>
																							<button type="button" class="btn btn-default" data-dismiss="modal" id="close_model">Close</button>
																						</div>
																					</form> 

																				</div>
																				<!-- Modal content-->	
																			</div>
																		</div>
																		<!-- Modal -->
																		<script>
																			$('#complete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>').on('show.bs.modal', function () {
																				$(".cancelcharge_error").html("");
																				$(".cnl_error").html("");
																			});
																		</script>
																		<?php
																	}?>

        <?php else : ?> 

																	<a class="btn btn-primary" href="invoice.php?iOrderId=<?= $DBProcessingOrders[$i]['iOrderId'] ?>" target="_blank">
																		<i class="icon-th-list icon-white"><b>View Invoice</b></i>
																	</a>
																	
        <?php endif; ?>	
																	<a class="btn btn-primary" href="print_order.php?iOrderId=<?= $DBProcessingOrders[$i]['iOrderId'] ?>" target="_blank">
																		<b>Print</b>
																	</a>
                                                            </td>
                                                        </tr>
                                                    <div class="clear"></div>
    <?php }
} else {
    ?>
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
            <input type="hidden" name="searchServiceType" value="<?php echo $searchServiceType; ?>" >
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
                                                                                                                                     if ('<?= $startDate ?>' != '') {
                                                                                                                                         $("#dp4").val('<?= $startDate ?>');
                                                                                                                                         $("#dp4").datepicker('update', '<?= $startDate ?>');
                                                                                                                                     }
                                                                                                                                     if ('<?= $endDate ?>' != '') {
                                                                                                                                         $("#dp5").datepicker('update', '<?= $endDate; ?>');
                                                                                                                                         $("#dp5").val('<?= $endDate; ?>');
                                                                                                                                     }

                                                                                                                                 });

                                                                                                                                 function setRideStatus(actionStatus) {
                                                                                                                                     window.location.href = "trip.php?type=" + actionStatus;
                                                                                                                                 }
                                                                                                                                 function todayDate()
                                                                                                                                 {
                                                                                                                                     $("#dp4").val('<?= $Today; ?>');
                                                                                                                                     $("#dp5").val('<?= $Today; ?>');
                                                                                                                                 }
                                                                                                                                 function reset() {
                                                                                                                                     location.reload();

                                                                                                                                 }
                                                                                                                                 function yesterdayDate()
                                                                                                                                 {
                                                                                                                                     $("#dp4").val('<?= $Yesterday; ?>');
                                                                                                                                     $("#dp4").datepicker('update', '<?= $Yesterday; ?>');
                                                                                                                                     $("#dp5").datepicker('update', '<?= $Yesterday; ?>');
                                                                                                                                     $("#dp4").change();
                                                                                                                                     $("#dp5").change();
                                                                                                                                     $("#dp5").val('<?= $Yesterday; ?>');
                                                                                                                                 }
                                                                                                                                 function currentweekDate(dt, df)
                                                                                                                                 {
                                                                                                                                     $("#dp4").val('<?= $monday; ?>');
                                                                                                                                     $("#dp4").datepicker('update', '<?= $monday; ?>');
                                                                                                                                     $("#dp5").datepicker('update', '<?= $sunday; ?>');
                                                                                                                                     $("#dp5").val('<?= $sunday; ?>');
                                                                                                                                 }
                                                                                                                                 function previousweekDate(dt, df)
                                                                                                                                 {
                                                                                                                                     $("#dp4").val('<?= $Pmonday; ?>');
                                                                                                                                     $("#dp4").datepicker('update', '<?= $Pmonday; ?>');
                                                                                                                                     $("#dp5").datepicker('update', '<?= $Psunday; ?>');
                                                                                                                                     $("#dp5").val('<?= $Psunday; ?>');
                                                                                                                                 }
                                                                                                                                 function currentmonthDate(dt, df)
                                                                                                                                 {
                                                                                                                                     $("#dp4").val('<?= $currmonthFDate; ?>');
                                                                                                                                     $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
                                                                                                                                     $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
                                                                                                                                     $("#dp5").val('<?= $currmonthTDate; ?>');
                                                                                                                                 }
                                                                                                                                 function previousmonthDate(dt, df)
                                                                                                                                 {
                                                                                                                                     $("#dp4").val('<?= $prevmonthFDate; ?>');
                                                                                                                                     $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
                                                                                                                                     $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
                                                                                                                                     $("#dp5").val('<?= $prevmonthTDate; ?>');
                                                                                                                                 }
                                                                                                                                 function currentyearDate(dt, df)
                                                                                                                                 {
                                                                                                                                     $("#dp4").val('<?= $curryearFDate; ?>');
                                                                                                                                     $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
                                                                                                                                     $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
                                                                                                                                     $("#dp5").val('<?= $curryearTDate; ?>');
                                                                                                                                 }
                                                                                                                                 function previousyearDate(dt, df)
                                                                                                                                 {
                                                                                                                                     $("#dp4").val('<?= $prevyearFDate; ?>');
                                                                                                                                     $("#dp4").datepicker('update', '<?= $prevyearFDate; ?>');
                                                                                                                                     $("#dp5").datepicker('update', '<?= $prevyearTDate; ?>');
                                                                                                                                     $("#dp5").val('<?= $prevyearTDate; ?>');
                                                                                                                                 }
                                                                                                                                 $("#Search").on('click', function () {
                                                                                                                                     if ($("#dp5").val() < $("#dp4").val()) {
                                                                                                                                         alert("From date should be lesser than To date.")
                                                                                                                                         return false;
                                                                                                                                     } else {
                                                                                                                                         var action = $("#_list_form").attr('action');
                                                                                                                                         var formValus = $("#frmsearch").serialize();
                                                                                                                                         window.location.href = action + "?" + formValus;
                                                                                                                                     }
                                                                                                                                 });
                                                                                                                                 $(function () {
                                                                                                                                     $("select.filter-by-text").each(function () {
                                                                                                                                         $(this).select2({
                                                                                                                                             placeholder: $(this).attr('data-text'),
                                                                                                                                             allowClear: true
                                                                                                                                         }); //theme: 'classic'
                                                                                                                                     });
                                                                                                                                 });
                                                                                                                                 $('#searchCompany').change(function () {
                                                                                                                                     var company_id = $(this).val(); //get the current value's option
                                                                                                                                     $.ajax({
                                                                                                                                         type: 'POST',
                                                                                                                                         url: 'ajax_find_driver_by_company.php',
                                                                                                                                         data: {'company_id': company_id},
                                                                                                                                         cache: false,
                                                                                                                                         success: function (data) {
                                                                                                                                             $(".driver_container").html(data);
                                                                                                                                         }
                                                                                                                                     });
                                                                                                                                 });


                                                                                                                                 /*$('.delete_form').on('hidden.bs.modal', function () {
                                                                                                                                  //window.location.reload();
                                                                                                                                  //$('.delete_form').reset();
                                                                                                                                  $(".modal-body").[0]reset();
                                                                                                                                  });	*/
        </script>

        <script type="text/javascript">

            $(document).ready(function ()
            {
                $('.custom-order').on('click', function () {
                    var order_id = $(this).data('id');

                    (function () {

                        var template = null
                        $('#delete_form' + order_id).on('show.bs.modal', function (event) {

                            if (template == null) {
                                template = $(this).html()
                            } else {
                                $(this).html(template)
                            }

                        })
                    })()
                })
            });

            $(function () {
                $("#cnl_booking1").on('click', function (e) {
                    var cancel_reason = $('#cancel_reason').val();
                    var cancelcharge = $('#fCancellationCharge').val();
                    if (cancel_reason == '' || cancelcharge == '') {
                        $(".cnl_error").html("This Field is required.");
                        $(".cancelcharge_error").html("This Field is required.");
                        return false;
                    } else {
                        $(".cnl_error").html("");
                        $(".cancelcharge_error").html("");
                        $("#delete_form1")[0].submit();
                    }

                });
            });

        </script>


    </body>
    <!-- END BODY-->
</html>
