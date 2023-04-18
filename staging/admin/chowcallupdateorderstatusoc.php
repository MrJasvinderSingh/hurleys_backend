<?php

// Report all errors
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set("error_reporting", E_ALL);

include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');
include_once('../include_config.php');
//echo TPATH_CLASS; exit;
include_once(TPATH_CLASS . 'configuration.php');


//include_once('../include_config.php');
//echo TPATH_CLASS; exit;
//include_once(TPATH_CLASS . 'configuration.php');

//require_once('../assets/libraries/stripe/config.php');
//require_once('../assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
//require_once('../assets/libraries/pubnub/autoloader.php');
//require_once('../assets/libraries/class.ExifCleaning.php');
//include_once(TPATH_CLASS . 'Imagecrop.class.php');
//include_once(TPATH_CLASS . 'twilio/Services/Twilio.php');
//include_once('../generalFunctions.php');
include_once('../send_invoice_receipt.php');
//error_reporting(-1);
//if (!isset($generalobjAdmin)) {
//    require_once(TPATH_CLASS . "class.general_admin.php");
//    $generalobjAdmin = new General_admin();
//}
//global $generalobj, $obj;
//$generalobjAdmin->check_member_login();
//$default_lang = $generalobj->get_default_lang();



if ($_POST['action'] == 'assignorder') {
    $query = "Select `iStatusCode` from orders  WHERE `iOrderId` = '" . $_POST['iOrderId'] . "'";
    $resultData = $obj->MySQLSelect($query);
   // echo "<pre>"; print_r($resultData); echo "</pre>";
        if( (count($resultData) > 0) && ($resultData[0]['iStatusCode'] !=6) && !empty($_POST['driverid']))
        {
            if ($_POST['iStatusCode'] == 2) {

                $assignorder = AssignOrderToDriver($_POST['iOrderId'], $_POST['driverid']);
            } 
           else {
                // echo "<pre>"; print_r($_POST); echo "</pre>";

                    $assignorder = UnassignOrderToDriver($_POST['Olddriverid'], $_POST['iOrderId']);
                    $assignorder = AssignOrderToDriver($_POST['iOrderId'], $_POST['driverid']);

                }
          echo "Driver successfully assigned.";
    }
    else
    {
        echo "Order is already delivered. Or No Driver Found.";
    }

   
} else {

    if ($_POST['iStatusCode'] == 5) {
        $orderStatus = "OrderPickedup";
        $assignorder = ChowcallUpdateOrderStatus($_POST['iOrderId'], $orderStatus);
        if ($assignorder == 'Success') {
            echo $assignorder." Order status changed to Picked Up.";
        } else {
            echo "Please try again.";
        }
    } elseif ($_POST['iStatusCode'] == 6) {
        $orderStatus = "OrderDelivered";
        $assignorder = ChowcallUpdateOrderStatus($_POST['iOrderId'], $orderStatus);
        if ($assignorder == 'Success') {
            echo $assignorder." Order status changed to Delivered.";
        } else {
            echo "Please try again.";
        }
    } elseif ($_POST['iStatusCode'] == 8) {

        $assignorder = ChowcallCancelOrder($_POST['iOrderId']);
        echo "Order No. #" . $assignorder . " is Cancelled";
    } 
    elseif ($_POST['iStatusCode'] == 1) {

        $query = "Select `iCompanyId` from orders  WHERE `iOrderId` = '" . $_POST['iOrderId'] . "'";
        $resultData = $obj->MySQLSelect($query);
        $assignorder = ConfirmOrderByRestaurant($_POST['iOrderId'], $resultData[0]['iCompanyId']);
        if ($assignorder == 'Success') {
            echo $assignorder." Order status changed to Picked Up.";
        } else {
            echo "Please try again.";
        }
    } 
    elseif ($_POST['iStatusCode'] == '112') {
        $assignorder = ChangeOrderToAcceptCondition($_POST['iOrderId']);
        if ($assignorder == 'Success') {
            echo $assignorder." Order status changed to Accept.";
        } else {
            echo "Please try again.";
        }
    } 
    
    
    else {
        echo "Please try again.";
    }
}

//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
function ChowcallCancelOrder($iOrderId) {
    global $generalobj, $obj;
    $hdn_del_id = $iOrderId;
    $vCancelReason = 'Cancelled By Admin';
    $fCancellationCharge = '0';
    $fDeliveryCharge = 0;
    $fRestaurantPayAmount = 0;



    $vIP = get_client_ip();
    $oSql = "SELECT iUserId,iDriverId,iTripId,iCompanyId FROM orders WHERE iOrderId = '" . $hdn_del_id . "'";
    $OrderQ_data = $obj->MySQLSelect($oSql);
    $iUserId = !empty($OrderQ_data[0]['iUserId']) ? $OrderQ_data[0]['iUserId'] : '';
    $iDriverId = !empty($OrderQ_data[0]['iDriverId']) ? $OrderQ_data[0]['iDriverId'] : '';
    $iTripId = !empty($OrderQ_data[0]) ? $_REQUEST['iTripId'] : '';
    $iCompanyId = !empty($OrderQ_data[0]) ? $_REQUEST['iCompanyId'] : '';

    $query = "UPDATE orders SET iStatusCode = '8' , eCancelledBy= 'Admin' ,fCancellationCharge = '0',fRestaurantPayAmount = '0' ,vCancelReason='" . $vCancelReason . "' WHERE iOrderId = '" . $hdn_del_id . "'";
    $obj->sql_query($query);

    $lquery = "INSERT INTO `order_status_logs`(`iOrderId`, `iStatusCode`, `dDate`, `vIp`) VALUES ('" . $hdn_del_id . "','8',Now(),'" . $vIP . "')";
    $obj->sql_query($lquery);


    //}   

    $query_driverPayment = "UPDATE trips SET  fDeliveryCharge ='0' WHERE iOrderId = '" . $hdn_del_id . "'";
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
    $message_arr['title'] = 'Order Cancel By Admin';
    $message_arr['tSessionId'] = $data_order[0]['tSessionId'];
    ;
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
            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
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
    ;
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
            sendApplePushNotification(2, $restaurantdeviceTokens_arr_ios, $restaurantmessage, $alertMsg, 0);
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
        ;
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
                sendApplePushNotification(0, $drvdeviceTokens_arr_ios, $drvmessage, $alertMsg, 0);
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

    return $vOrderNo;
}

function ChowcallUpdateOrderStatus($iOrderId, $orderStatus) {
    $responseCStatus = '';
    global $generalobj, $obj;
    $oSql = "SELECT `orders`.`iOrderId`, `orders`.`iUserId`,`orders`.`fNetTotal`,`trips`.`iTripId`, `trips`.`iDriverId`  FROM `orders` JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` WHERE `orders`.`iOrderId` =  '" . $iOrderId . "'";
    $OrderQ_data = $obj->MySQLSelect($oSql);
    // echo "<pre>";     print_r($OrderQ_data); echo "</pre>";
    if (count($OrderQ_data) > 0) {
        $iTripId = !empty($OrderQ_data[0]["iTripId"]) ? $OrderQ_data[0]["iTripId"] : "";
        $iOrderId = !empty($OrderQ_data[0]["iOrderId"]) ? $OrderQ_data[0]["iOrderId"] : "";
        $iDriverId = !empty($OrderQ_data[0]["iDriverId"]) ? $OrderQ_data[0]["iDriverId"] : "";
        $billAmount = !empty($OrderQ_data[0]["fNetTotal"]) ? $OrderQ_data[0]["fNetTotal"] : "";

        $fields = "iUserId,iDriverId,iCompanyId,fNetTotal,ePaymentOption,ePaid,vOrderNo,vCouponCode";
        $OrderData = get_value('orders', $fields, 'iOrderId', $iOrderId);
        $iUserId = $OrderData[0]['iUserId'];
        $iCompanyId = $OrderData[0]['iCompanyId'];
        $iDriverId = $OrderData[0]['iDriverId'];
        $ePaymentOption = $OrderData[0]['ePaymentOption'];
        $ePaid = $OrderData[0]['ePaid'];
        $vOrderNo = $OrderData[0]['vOrderNo'];
        $vCouponCode = $OrderData[0]['vCouponCode'];

        $UserDetailsArr = getDriverCurrencyLanguageDetails($OrderData[0]['iDriverId'], $iOrderId);
        $vSymbol = $UserDetailsArr['currencySymbol'];
        $priceRatio = $UserDetailsArr['Ratio'];
        $vLang = $UserDetailsArr['vLang'];
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $confirmprice = getPriceUserCurrency($OrderData[0]['iDriverId'], "Driver", $OrderData[0]['fNetTotal']);

        if ($orderStatus == "OrderPickedup") {
            $billAmount = $confirmprice['fPrice'];
        }

        if ($confirmprice['fPrice'] == $billAmount) {
            $sql = "SELECT vCurrencyPassenger,iAppVersion,iUserPetId FROM `register_user` WHERE iUserId = '$iUserId'";
            $Data_passenger_detail = $obj->MySQLSelect($sql);
            $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
            $Data_vehicle = $obj->MySQLSelect($sql);

            $drivername = $Data_vehicle[0]['driverName'];
            $sql = "SELECT vt.fDeliveryCharge from vehicle_type as vt LEFT JOIN trips as tr ON tr.iVehicleTypeId=vt.iVehicleTypeId WHERE iTripId = '" . $iTripId . "'";
            $Data_trip_vehicle = $obj->MySQLSelect($sql);
            $fDeliveryCharge = $Data_trip_vehicle[0]['fDeliveryCharge'];
//echo "infunction";
            // Notify only user
            $DriverMessage = $orderStatus;
            $title = '';
            if ($orderStatus == 'OrderPickedup') {
                $title = 'Order Pickedup';
                $Data_update_Trips['tDriverArrivedDate'] = @date("Y-m-d H:i:s");
                $Data_update_Trips['tStartDate'] = @date("Y-m-d H:i:s");
                $Data_update_Trips['iActive'] = 'On Going Trip';
                $Data_update_Trips['eImgSkip'] ='Yes';
                $Data_update_orders['iStatusCode'] = '5';
                $Data_update_driver['vTripStatus'] = 'On Going Trip';
                $Order_Status_id = createOrderLog($iOrderId, "5");
                //$tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_EXECUTIVE_TXT']." ".$drivername." ".$languageLabelsArr['LBL_DELIVERY_ON_WAY_TXT']." #".$vOrderNo;
                $tripdriverarrivlbl = $drivername . " " . $languageLabelsArr['LBL_PICKUP_ORDER_NOTIFICATION_TXT'];
            } else if ($orderStatus == 'OrderDelivered') {
                $title = 'Order Delivered';
                $Data_update_Trips['iActive'] = 'Finished';
                $Data_update_Trips['tEndDate'] = @date("Y-m-d H:i:s");
                $Data_update_Trips['fDeliveryCharge'] = $fDeliveryCharge;
                if ($ePaymentOption == "Cash") {
                    $Data_update_orders['ePaid'] = "Yes";
                }
                $Data_update_orders['dDeliveryDate'] = @date("Y-m-d H:i:s");
                $Data_update_orders['iStatusCode'] = '6';
                $Data_update_driver['vTripStatus'] = 'Finished';
                $Order_Status_id = createOrderLog($iOrderId, "6");
                $tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_EXECUTIVE_TXT'] . " " . $drivername . " " . $languageLabelsArr['LBL_DELIVERY_DELIVER_TXT'] . " #" . $vOrderNo;

                $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes',vOrderAdjusmentId = '" . $vOrderNo . "' WHERE iUserId = '" . $iUserId . "' AND ePaidByPassenger = 'No'";
                $obj->sql_query($updateQury);
                ## Deduct Order Amount From Driver's Wallet Only For Cash Delivered Orders ##
                if ($ePaymentOption == "Cash" && $COMMISION_DEDUCT_ENABLE == 'Yes' && $OrderData[0]['fNetTotal'] > 0) {
                    $iBalance = $OrderData[0]['fNetTotal'];
                    $eType = "Debit";
                    $eFor = "Withdrawl";
                    $tDescription = '#LBL_DEBITED_BOOKING# ' . $vOrderNo;
                    $ePaymentStatus = 'Settelled';
                    $dDate = @date('Y-m-d H:i:s');
                    $generalobj->InsertIntoUserWallet($iDriverId, "Driver", $iBalance, $eType, $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate, $iOrderId, "");
                    $Where_Order = " iTripId = '$iTripId'";
                    $Data_update_driver_paymentstatus['eDriverPaymentStatus'] = "Settelled";
                    //$Update_Payment_Id = $obj->MySQLQueryPerform("trips",$Data_update_driver_paymentstatus,'update',$Where_Order);
                }
                ## Deduct Order Amount From Driver's Wallet Only For Cash Delivered Orders ##

                $generalobj->orderemaildataDelivered($iOrderId, "Passenger");
                ## Update Coupon Used Limit ##
                if ($vCouponCode != '') {
                    $Data_update_order['vCouponCode'] = $vCouponCode;

                    $noOfCouponUsed = get_value('coupon', 'iUsed', 'vCouponCode', $vCouponCode, '', 'true');
                    $where_coupon = " vCouponCode = '" . $vCouponCode . "'";
                    $data_coupon['iUsed'] = $noOfCouponUsed + 1;
                    $obj->MySQLQueryPerform("coupon", $data_coupon, 'update', $where_coupon);
                }
                ## Update Coupon Used Limit ##                  
            }
          //  echo "In Function1";
            $twhere = " iTripId = '" . $iTripId . "'";
            $TripId = $obj->MySQLQueryPerform("trips", $Data_update_Trips, 'update', $twhere);
            $owhere = " iOrderId = '" . $iOrderId . "'";
            $OrderId = $obj->MySQLQueryPerform("orders", $Data_update_orders, 'update', $owhere);
            $rdwhere = " iDriverId = '" . $OrderData[0]['iDriverId'] . "'";
            $OrderStatus = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $rdwhere);
            $alertMsg = $tripdriverarrivlbl;
          //   echo "In Function2";
            $message_arr = array();
            $message_arr['iDriverId'] = $iDriverId;
            $message_arr['Message'] = $DriverMessage;
            $message_arr['iTripId'] = strval($iTripId);
            $message_arr['DriverAppVersion'] = strval($Data_vehicle[0]['iAppVersion']);
            $message_arr['driverName'] = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
            //$message_arr['vRideNo'] = $TripRideNO;
            $message_arr['iOrderId'] = $iOrderId;
            $message_arr['vTitle'] = $alertMsg;
            $message_arr['title'] = $title;
            $message = json_encode($message_arr);
            #####################Add Status Message#########################
            /* $DataTripMessages['tMessage']= $message;
              $DataTripMessages['iDriverId']= $iDriverId;
              $DataTripMessages['iTripId']= $iTripId;
              $DataTripMessages['iOrderId']= $iOrderId;
              $DataTripMessages['iUserId']= $iUserId;
              $DataTripMessages['eFromUserType']= "Driver";
              $DataTripMessages['eToUserType']= "Passenger";
              $DataTripMessages['eReceived']= "Yes";
              $DataTripMessages['dAddedDate']= @date("Y-m-d H:i:s");
              $obj->MySQLQueryPerform("trip_status_messages",$DataTripMessages,'insert'); */
            ################################################################
            // Notify user and restaurant for OrderDelivered and order Pickup
           //  echo "In Function3".$iTripId;
            if ($iTripId > 0) {
                if ($PUBNUB_DISABLED == "Yes") {
                    $ENABLE_PUBNUB = "No";
                }
                //echo "In Function4";
                $alertSendAllowed = true;
                /* For PubNub Setting */
                $tableName = "register_user";
                $iMemberId_VALUE = $iUserId;
                $iMemberId_KEY = "iUserId";
                $AppData = get_value($tableName, 'iAppVersion,eDeviceType', $iMemberId_KEY, $iMemberId_VALUE);
                $iAppVersion = $AppData[0]['iAppVersion'];
                $eDeviceType = $AppData[0]['eDeviceType'];
                /* For PubNub Setting Finished */
                $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId='$iUserId'";
                $result = $obj->MySQLSelect($sql);
                $registatoin_ids = $result[0]['iGcmRegId'];
                $deviceTokens_arr_ios = array();
                $registation_ids_new = array();
                $sql = "SELECT iGcmRegId,eDeviceType,iAppVersion,tSessionId FROM company WHERE iCompanyId='$iCompanyId'";
                $result_company = $obj->MySQLSelect($sql);
                $registatoin_ids_company = $result_company[0]['iGcmRegId'];
                $deviceTokens_arr_ios_company = array();
                $registation_ids_new_company = array();
                /* if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
                  $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
                  $channelName = "PASSENGER_" . $iUserId;
                  $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $iUserId, '', 'true');
                  $message_arr['tSessionId'] = $tSessionId;
                  $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
                  $info = $pubnub->publish($channelName, $message_pub);
                  $channelName_company = "COMPANY_" . $iCompanyId;
                  $message_arr['tSessionId'] = $result_company[0]['tSessionId'];
                  $message_pub_company = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
                  $info_company = $pubnub->publish($channelName_company, $message_pub_company);
                  if ($result_company[0]['eDeviceType'] != "Android") {
                  array_push($deviceTokens_arr_ios_company, $result_company[0]['iGcmRegId']);
                  }
                  if ($result[0]['eDeviceType'] != "Android") {
                  array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                  }
                  } else {
                  $alertSendAllowed = true;
                  } */
               // echo "In Function5";
                
                
                //Driver Data
                
                        
                $sqlDriver = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (" . $iDriverId . ")";
                
                $result_Driver = $obj->MySQLSelect($sqlDriver);
                $registatoin_ids_driver = $result_Driver[0]['iGcmRegId'];
                $deviceTokens_arr_ios_driver = array();
                $registation_ids_new_driver = array();
                
                
                $alertMsgDriver = "Order Updated";
                $final_message['Message'] = "OrderCancelByAdmin";
                $final_message['iOrderId'] = strval($iOrderId);
                $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
                $final_message['vTitle'] = $alertMsgDriver;
               $tSessionId = $result_Driver[0]['tSessionId'];
                $final_message['tSessionId'] = $tSessionId;
                 $Drivermessage = json_encode($final_message);
                 
                 
                 
                 
                 /* For IOS change */
        $MsgCodde = strval(time() . mt_rand(1000, 9999));
        $data_userRequest = array();
			$data_userRequest['iUserId'] = $iUserId;
			$data_userRequest['iDriverId'] = $iDriverId;
			$data_userRequest['tMessage'] = $Drivermessage;
			$data_userRequest['iMsgCode'] = $MsgCodde;
			$data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
			$requestId = addToUserRequest2($data_userRequest);
        $data_driverRequest = array();
			$data_driverRequest['iDriverId'] = $iDriverId;
			$data_driverRequest['type'] = 'unassign';
			$data_driverRequest['iRequestId'] = $requestId;
			$data_driverRequest['iUserId'] = $iUserId;
			$data_driverRequest['iTripId'] = 0;
			$data_driverRequest['iOrderId'] = $iOrderId;
			$data_driverRequest['eStatus'] = "Timeout";
			$data_driverRequest['vMsgCode'] = $MsgCodde;
			$data_driverRequest['vStartLatlong'] = '0';
			$data_driverRequest['vEndLatlong'] = '0';
			$data_driverRequest['tStartAddress'] = '0';
			$data_driverRequest['tEndAddress'] = '0';
			$data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
			addToDriverRequest2($data_driverRequest);
        
        /* For IOS change close */
                 
                 
                 
                
                $alertSendAllowed = true;
                if ($alertSendAllowed == true) {
                    if ($result[0]['eDeviceType'] == "Android") {
                        array_push($registation_ids_new, $result[0]['iGcmRegId']);
                        $Rmessage = array("message" => $message);
                        $result = send_notification($registation_ids_new, $Rmessage, 0);
                    } else {
                        array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                        sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    }

                    if ($result_company[0]['eDeviceType'] == "Android") {
                        array_push($registation_ids_new_company, $result_company[0]['iGcmRegId']);
                        $Rmessage = array("message" => $message);
                        $resultc = send_notification($registation_ids_new_company, $Rmessage, 0);
                    } else {
                        array_push($deviceTokens_arr_ios_company, $result_company[0]['iGcmRegId']);
                        sendApplePushNotification(2, $deviceTokens_arr_ios_company, $message, $alertMsg, 0);
                    }
                    
                     if ($result_Driver[0]['eDeviceType'] == "Android") {
                        array_push($registation_ids_new_driver, $result_Driver[0]['iGcmRegId']);
                        $Dmessage = array("message" => $Drivermessage);
                        $resultc = send_notification($registation_ids_new_driver, $Dmessage, 0);
                    } else {
                        array_push($deviceTokens_arr_ios_driver, $result_Driver[0]['iGcmRegId']);
                        sendApplePushNotification(2, $deviceTokens_arr_ios_driver, $Drivermessage, $alertMsgDriver, 0);
                    }
                    
                }
                // New code close for Driver Push
                //$checkMoreorder = GetDriverStatusIFonGoing($iDriverId);
                //$returnArr['MoreOrder'] = $checkMoreorder;
                //$returnArr['Action'] = "1";

                //$generalobj->get_benefit_amount($iTripId);
                /* $data['iTripId'] = $iTripId;
                  $data['PAppVersion'] = $Data_passenger_detail[0]['iAppVersion'];
                  $returnArr['message']=$data; */
                /* if($iOrderId !="") {
                  $passengerData = get_value('register_user', 'vName,vLastName,vImgName,vFbId,vAvgRating,vPhone,vPhoneCode,iAppVersion', 'iUserId', $iUserId);
                  $returnArr['PassengerId'] = $iUserId;
                  $returnArr['PName'] = $passengerData[0]['vName'].' '.$passengerData[0]['vLastName'];
                  $returnArr['PPicName'] = $passengerData[0]['vImgName'];
                  $returnArr['PFId'] = $passengerData[0]['vFbId'];
                  $returnArr['PRating'] = $passengerData[0]['vAvgRating'];
                  $returnArr['PPhone'] = $passengerData[0]['vPhone'];
                  $returnArr['PPhoneC'] = $passengerData[0]['vPhoneCode'];
                  $returnArr['PAppVersion'] = $passengerData[0]['iAppVersion'];
                  $returnArr['TripId'] = strval($iTripId);
                  } */
                $responseCStatus = 'Success';
                return $responseCStatus;
            } else {
               // $returnArr['Action'] = "0";
               // $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
                $responseCStatus = 'Failed';
                return $responseCStatus;
            }
        } else {
            //$returnArr['Action'] = "0";
            //$returnArr['message'] = "LBL_BILL_VALUE_ERROR_TXT";
            $responseCStatus = 'Invalid Data';
            return $responseCStatus;
        }
    }
}




function ConfirmOrderByRestaurant($iOrderId, $iCompanyId) {
     global $generalobj, $obj;
    $ron = isset($_REQUEST["ron"]) ? $_REQUEST["ron"] : "";
    $where = " iOrderId = '" . $iOrderId . "'";
    $Data_update_order['iStatusCode'] = '2';
    $Data_update_order['ron'] = $ron;
    $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order, 'update', $where);

    $id = createOrderLog($iOrderId, "2");

    ## Send Notification To User ##
    $Message = "OrderConfirmByRestaurant";
    $sql = "select ru.iUserId,ru.iGcmRegId,ru.eDeviceType,ru.tSessionId,ru.iAppVersion,ru.vLang,ord.vOrderNo from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $vLangCode = $data_order[0]['vLang'];
    $vOrderNo = $data_order[0]['vOrderNo'];
    $iUserId = $data_order[0]['iUserId'];
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $alertMsg = $languageLabelsArr['LBL_CONFIRM_ORDER_BY_RESTAURANT_APP_TXT'];
    $message_arr = array();
    $message_arr['Message'] = $Message;
    $message_arr['iOrderId'] = $iOrderId;
    $message_arr['vOrderNo'] = $vOrderNo;
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['tSessionId'] = $data_order[0]['tSessionId'];
    $message_arr['title'] = "Order Confirm By Restaurant";
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
    /* if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
      $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
      $channelName = "PASSENGER_" . $iUserId;
      $info = $pubnub->publish($channelName, $message);
      if ($eDeviceType != "Android") {
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
            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
        }
    }
    ## Send Notification To User ##  
$messageresponse = '';
    if ($Order_Update_Id > 0) {
        $messageresponse = "Success";
        $generalobj->orderemaildata($iOrderId, 'Passenger');
    } else {
        $messageresponse = "Failure";
    }
    return $messageresponse;
}


function ChangeOrderToAcceptCondition($iOrderId)
{
     global  $obj;

     $deleteorPreq = "delete from passenger_requests where iRequestId IN (SELECT iRequestId from (SELECT iRequestId from driver_request where iOrderId = '$iOrderId') as temp);";
    $Preq =  $obj->sql_query($deleteorPreq);
    
    $updatdrivereq = "delete from driver_request where iOrderId = $iOrderId;";
    $updatedr1 = $obj->sql_query($updatdrivereq);
    
    
    $deletetrips = "delete from trips where iOrderId = $iOrderId;";
    $obj->sql_query($deletetrips);
    
    
    $updatOrderres = "delete from order_status_logs where iOrderId = $iOrderId AND iStatusCode > 2;";
    $updatedr12 = $obj->sql_query($updatOrderres);
    
    $updateorder = "update orders set iDriverId = 0,iStatusCode=2 where iOrderId = $iOrderId;";
    $updatedr = $obj->sql_query($updateorder);
     
     $responseCStatus = 'Success';
     return $responseCStatus;
}


?>