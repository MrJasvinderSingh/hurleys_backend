<?php
include_once('../common.php');
include_once('../generalFunctions.php');
global $generalobj, $obj;
$iDriverId = $_REQUEST['iDriverId'];
$checkdataQ = "Select * from register_driver where vAvailability = 'Available' AND iDriverId = '$iDriverId' AND `vTripStatus` NOT IN ('On Going Trip', 'Active');";
$checkData = $obj->MySQLSelect($checkdataQ);
if (!empty($iDriverId) && (count($checkData) > 0)) {   
    $Data_update_driver['vAvailability'] = "Not Available";
    $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
    $curr_date = date('Y-m-d H:i:s');
    $selct_query = "select * from driver_log_report WHERE iDriverId = '" . $iDriverId . "' order by `iDriverLogId` desc limit 0,1";
    $get_data_log = $obj->sql_query($selct_query);

    $update_sql = "UPDATE driver_log_report set dLogoutDateTime = '" . $curr_date . "' WHERE iDriverLogId ='" . $get_data_log[0]['iDriverLogId'] . "'";
    $result = $obj->sql_query($update_sql);
     $where = " iDriverId='" . $iDriverId . "'";
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    
    
    
    //Send Notification
    $final_message['Message'] 			= "vAvailability";
    $final_message['vAvailability'] 			= "false";
//    $final_message['sourceLatitude'] 	= strval($PickUpLatitude);
//    $final_message['sourceLongitude'] 	= strval($PickUpLongitude);
//    $final_message['PassengerId'] 		= strval($iUserId);
//    $final_message['pickupaddress'] 	= $PickUpAddress;
//    $final_message['dropaddress'] 		= $DestAddress;
//    $final_message['iCompanyId'] 		= strval($iCompanyId);
//    $final_message['iOrderId'] 			= strval($orderid);
//    $passengerFName 					= $passengerData[0]['vCompany'];
//    $final_message['PName'] 			= $passengerFName;
//    $final_message['PPicName'] 			= $passengerData[0]['vImgName'];
//    $final_message['PRating'] 			= $passengerData[0]['vAvgRating'];
//    $final_message['PPhone'] 			= $passengerData[0]['vPhone'];
//    $final_message['PPhoneC'] 			= $passengerData[0]['vPhoneCode'];
//    $final_message['PPhone'] 			= '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
//    $final_message['destLatitude'] 		= strval($DestLatitude);
//    $final_message['destLongitude'] 	= strval($DestLongitude);
    $final_message['MsgCode'] 			= strval(time() . mt_rand(1000, 9999));
    $final_message['vTitle'] 			= "You have been set to offline by the Manager.";
    
    try{
    
    $deviceTokens_arr_ios = array();
    $registation_ids_new = array();
   if ($checkData[0]['eDeviceType'] == "Android") {
				array_push($registation_ids_new, $checkData[0]['iGcmRegId']);
			} else {
				array_push($deviceTokens_arr_ios, $checkData[0]['iGcmRegId']);
			}
	
		 $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
		
     if (count($registation_ids_new) > 0) {
            // $Rmessage = array("message" => $message);
            $Rmessage = array("message" => $msg_encode);

            $result = send_notification($registation_ids_new, $Rmessage, 0);
        }
        if (count($deviceTokens_arr_ios) > 0) {
            // sendApplePushNotification(1,$deviceTokens_arr_ios,$message,$alertMsg,1);
            sendApplePushNotification(1, $deviceTokens_arr_ios, $msg_encode, $alertMsg, 0);
        }
    
    }
 catch (Exception  $e) 
 {
     
 }
    
    
    echo '1';
} else {
    echo '0';
}

