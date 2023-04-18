<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
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
include_once('../send_invoice_receipt.php');



if ($_POST['action'] == 'updatestatus') {
	$res = ChowcallUpdateOrderStatus($_POST['hdn_del_id'], $_POST['orderstatus']);
	//echo $res;die;
	echo "<script>location.href='allorders.php'</script>";
}
function ChowcallUpdateOrderStatus($iOrderId, $orderStatus) {
    $responseCStatus = '';
    global $generalobj, $obj;
    $oSql = "SELECT `orders`.`iOrderId`, `orders`.`iUserId`,`orders`.`fNetTotal`,`trips`.`iTripId`, `trips`.`iDriverId`  FROM `orders` LEFT JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` WHERE `orders`.`iOrderId` =  '" . $iOrderId . "'";
    $OrderQ_data = $obj->MySQLSelect($oSql);
    //echo "<pre>";     print_r($OrderQ_data); echo "</pre>";
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

        if ($orderStatus == "OrderPickedup" || $orderStatus == "OrderPrepared" || $orderStatus == "OrderDelivered") {
            $billAmount = $confirmprice['fPrice'];
        }

        if ($confirmprice['fPrice'] == $billAmount) {
            $sql = "SELECT vCurrencyPassenger,iAppVersion,iUserPetId FROM `register_user` WHERE iUserId = '$iUserId'";
            $Data_passenger_detail = $obj->MySQLSelect($sql);
            $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
            $Data_vehicle = $obj->MySQLSelect($sql);

            $drivername = $Data_vehicle[0]['driverName'];
            $DriverMessage = $orderStatus;
            $title = '';
			if ($orderStatus == 'OrderConfirmed') {
                $title = 'Order Confirmed';
                $Data_update_Trips['tDriverArrivedDate'] = @date("Y-m-d H:i:s");
                $Data_update_Trips['tStartDate'] = @date("Y-m-d H:i:s");
                $Data_update_Trips['iActive'] = 'Order Confirmed';
                $Data_update_Trips['eImgSkip'] ='Yes';
                $Data_update_orders['iStatusCode'] = '2';
                $Data_update_driver['vTripStatus'] = 'Order Confirmed';
                $Order_Status_id = createOrderLog($iOrderId, "2");
                //$tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_EXECUTIVE_TXT']." ".$drivername." ".$languageLabelsArr['LBL_DELIVERY_ON_WAY_TXT']." #".$vOrderNo;
                $tripdriverarrivlbl = $languageLabelsArr['LBL_CONFIRM_ORDER_BY_RESTAURANT'];
				$alertMsg = $languageLabelsArr['LBL_CONFIRM_ORDER_BY_RESTAURANT'];
				$title = "Order Confirm By Restaurant";
				sendpushtouser($iOrderId,'OrderConfirmByRestaurant',$alertMsg,5,$title);
            } else if ($orderStatus == 'OrderPrepared') {
                $title = 'Order Prepared';
                $Data_update_Trips['tDriverArrivedDate'] = @date("Y-m-d H:i:s");
                $Data_update_Trips['tStartDate'] = @date("Y-m-d H:i:s");
                $Data_update_Trips['iActive'] = 'Order Prepared';
                $Data_update_Trips['eImgSkip'] ='Yes';
                $Data_update_orders['iStatusCode'] = '5';
                $Data_update_driver['vTripStatus'] = 'Order Prepared';
                //$Order_Status_id = createOrderLog($iOrderId, "2");
                $Order_Status_id = createOrderLog($iOrderId, "5");
                //$tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_EXECUTIVE_TXT']." ".$drivername." ".$languageLabelsArr['LBL_DELIVERY_ON_WAY_TXT']." #".$vOrderNo;
                $tripdriverarrivlbl = $languageLabelsArr['LBL_PREPARE_ORDER_BY_RESTAURANT_APP_TXT'];
				$alertMsg = $languageLabelsArr['LBL_PREPARE_ORDER_BY_RESTAURANT_APP_TXT'];
				//$title = "Order Completed By Restaurant";
				sendpushtouser($iOrderId,'OrderPreparedByRestaurant',$alertMsg,6,$title);
            } else if ($orderStatus == 'OrderPickedup') {
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
                $Data_update_Trips['fDeliveryCharge'] = '';//$fDeliveryCharge;
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

				$alertMsg = $languageLabelsArr['LBL_TRIP_FINISHED_TXT'];
				$title = "Order Completed By Restaurant";
				sendpushtouser($iOrderId,'OrderCompletedByRestaurant',$alertMsg,6,$title);
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
                $responseCStatus = 'Success';
                return $responseCStatus;
            } else {
                $responseCStatus = 'Success';
                return $responseCStatus;
            }
        } else {
            $responseCStatus = 'Invalid Data';
            return $responseCStatus;
        }
    }
}


function sendpushtouser($iOrderId,$message_send,$alertMsg,$nextcode,$title){
	global $generalobj, $obj;
    ## Send Notification To User ##
    $Message = $message_send;
    $sql = "select ru.iUserId,ru.iGcmRegId,ru.eDeviceType,ru.tSessionId,ru.iAppVersion,ru.vLang,ord.vOrderNo from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $vLangCode = $data_order[0]['vLang'];
    $vOrderNo = $data_order[0]['vOrderNo'];
    $iUserId = $data_order[0]['iUserId'];
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    //$alertMsg = $languageLabelsArr['LBL_PREPARE_ORDER_BY_RESTAURANT_APP_TXT'];
    $message_arr = array();
    $message_arr['Message'] = $Message;
    $message_arr['iOrderId'] = $iOrderId;
    $message_arr['vOrderNo'] = $vOrderNo;
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['nextcode'] = $nextcode;
    $message_arr['tSessionId'] = $data_order[0]['tSessionId'];
    $message_arr['title'] = $title;
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
	//print_r($message);die;
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
	

    if ($alertSendAllowed == true) {
        if ($eDeviceType == "Android") {
            array_push($registation_ids_new, trim($iGcmRegId));
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