<?php
error_reporting('E_All');
        
	date_default_timezone_set('America/New_York');
        @session_start();
	$_SESSION['sess_hosttype'] = 'ufxall';
	$inwebservice = "1";
	error_reporting(0);
	//include_once('include_taxi_webservices.php');
	include_once('include_config.php');
        //echo TPATH_CLASS; exit;
	include_once(TPATH_CLASS.'configuration.php');
	
	require_once('assets/libraries/stripe/config.php');
	require_once('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
	require_once('assets/libraries/pubnub/autoloader.php');
	require_once('assets/libraries/class.ExifCleaning.php');
	include_once(TPATH_CLASS .'Imagecrop.class.php');
	include_once(TPATH_CLASS .'twilio/Services/Twilio.php');
	include_once('generalFunctionsLCL.php');
	include_once('send_invoice_receipt.php'); 
        
        	if ($type == "sendRequestToDrivers") {
            //error_reporting(E_All);
		$iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    	$vDeviceToken    =isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
		$trip_status  = "Requesting";
    
    $checkOrderRequestStatusArr = checkOrderRequestStatus($iOrderId);
    $action = $checkOrderRequestStatusArr['Action'];
    if($action == 0){
      	echo json_encode($checkOrderRequestStatusArr);exit;
    } 
    
    $sql="select * from orders WHERE iOrderId='".$iOrderId."'";
		$db_order = $obj->MySQLSelect($sql);
      
		//checkmemberemailphoneverification($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId']; 
    $ePaymentOption = $db_order[0]['ePaymentOption']; 
    
    $companyfields="vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId,"Passenger",$iUserAddressId);
		//echo "<pre>";print_r($UserSelectedAddressArr);exit;
		$vLangCode = get_value('language_master', 'vCode', 'eDefault','Yes','','true');
		
		$languageLabelsArr= getLanguageLabelsArr($vLangCode,"1",$iServiceId);
		$userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
		$alertMsg = $userwaitinglabel;
		
    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;                                                  
		
		
                
//                $DataArr = getOnlineDriverArrAnviamDriver($PickUpLatitude,$PickUpLongitude,$address_data,"Yes","No","No","",$DestLatitude,$DestLongitude);
//                
                
                 $DataArr = getOnlineDriverArrAnviam($PickUpLatitude,$PickUpLongitude,$address_data,"Yes","No","No","",$DestLatitude,$DestLongitude);
                 $flag = 1;
                // echo count($DataArr['DriverList']);
               //  print_r($DataArr['DriverList']);
                if( empty($DataArr['DriverList']))
                {
                    $DataArr = getOnlineDriverArrAnviamDriver($PickUpLatitude,$PickUpLongitude,$address_data,"Yes","No","No","",$DestLatitude,$DestLongitude);
                    $flag = 0;
                }
                
    $DataUnprocess = $DataArr['DriverList']; 
    $Data = array();
    /* New code Anviam */
 //echo "Flag is $flag <br/> <pre>"; print_r($DataUnprocess); echo "</pre>"; 
  $cookingtime = cookingtimecalculator($iOrderId);
      //echo "Cookingtime" .$cookingtime. " <br/>";
  //print_r($pickupdistime);
  $dropdistime = GetDrivingDistance($DestLatitude, $PickUpLatitude, $DestLongitude, $PickUpLongitude);
  //print_r($dropdistime);
  $pickupdistance = $DataUnprocess[0]['pickupdistance'];
   $pickuptime = $DataUnprocess[0]['pickuptime'];
   $dropdistance = $dropdistime['distance'];
   $droptime = $dropdistime['time'];
  $lesstime = 0;
  $lessid = 0;
 $estimatetimeforpickup = 0; 
  if($cookingtime > $pickuptime)
  {
      //echo "Enter In looping<br/> Count is: ". count($DataUnprocess) ."<br/>";
     for($i=0;$i<count($DataUnprocess);$i++){
       
         if(($lesstime == 0) && (strtotime($DataUnprocess[$i]['lastproccessedtime']) != 0))
         {
            // echo "1";
             $lesstime = strtotime($DataUnprocess[$i]['lastproccessedtime']);
             $lessid = $DataUnprocess[$i]; 
            
         }
         elseif(($lesstime != 0) && (strtotime($DataUnprocess[$i]['lastproccessedtime']) != 0))
         {
           //  echo "2";
             if($lesstime  > strtotime($DataUnprocess[$i]['lastproccessedtime']))
             {
                 $lesstime = strtotime($DataUnprocess[$i]['lastproccessedtime']);
             $lessid = $DataUnprocess[$i]; 
             }
         }
         else
         {
            //  echo "3";
             $lesstime = 0;
             $lessid = $DataUnprocess[$i]; 
         }
         
        //  echo "4";
             
     }
     
    // print_r ($lessid); echo "<br/>";
       $Data[0] = $lessid;
      $estimatetimeforpickup = $cookingtime;
     //  echo "5";
  }
  else
  {
       //echo "6";
      $Data[0] = $DataUnprocess[0];
       $estimatetimeforpickup = $pickuptime;
  }
  
   //echo "7";
  // echo "<pre>"; print_r($Data);  echo "working"; exit;
  $totaltime = $estimatetimeforpickup + $droptime;
  $totaldistance = $pickupdistance + $dropdistance;
  
  
    
    /* New COde Anviam close */
    
//    if($totaltime > 50)
//{
        
      /*  $userdataquery = "SELECT `iGcmRegId`,`eDeviceType`,`tSessionId` FROM `register_user` WHERE `iUserId` =  $iUserId";
        $getUserdata = $obj->MySQLSelect($userdataquery);
        //print_r($getUserdata);
   if(true){
			$deviceTokens_arr_ios_usernotify = array();
			$registation_ids_new_usernotify = array();
			
			
				if( $getUserdata[0]['eDeviceType'] != "Android"){
					array_push($deviceTokens_arr_ios_usernotify, $getUserdata[0]['iGcmRegId']);
					}else{
					array_push($registation_ids_new_usernotify,  $getUserdata[0]['iGcmRegId']);
				}
        

				$usermessage = array( "title"=> "Order Confirmation",
                      "body"=>  "Your order time is greater than 50 Minutes, Ignore if you want to continue your order.");
         $msg_encode_usernotify  = json_encode($usermessage,JSON_UNESCAPED_UNICODE);
			if(count($registation_ids_new_usernotify) > 0){
                          $Rmessage_usernotify = array("message" => $msg_encode_usernotify);
			  $result_usernotify = send_notification($registation_ids_new_usernotify, $Rmessage_usernotify,0);
			
			}
			if(count($deviceTokens_arr_ios_usernotify) > 0){
			        $alertMsg_usernotify = "Order Confirmation";
				$result_usernotify= sendApplePushNotification(1,$deviceTokens_arr_ios_usernotify,$msg_encode_usernotify,$alertMsg_usernotify,0);
			}
		}
             */  
              
//}

    
    $driver_id_auto = $DataArr['driver_id_auto'];
    ## Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    if($ePaymentOption == "Cash"){
      $Data_new = array();
      $Data_new = $Data;
      for($i=0;$i<count($Data);$i++){
         $isRemoveFromList = "No";
         $ACCEPT_CASH_TRIPS = $Data[$i]['ACCEPT_CASH_TRIPS']; 
         if($ACCEPT_CASH_TRIPS == "No"){
            $isRemoveFromList = "Yes";
         }
         
         if($isRemoveFromList == "Yes"){   
             unset($Data_new[$i]);
         }
      }
      $Data = array_values($Data_new);      
      $driver_id_auto = "";
      for($j=0;$j<count($Data);$j++){
        $driver_id_auto .= $Data[$j]['iDriverId'].",";
      }
      $driver_id_auto = substr($driver_id_auto,0,-1);
    }
    ## Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    //echo "<pre>";print_r($Data);exit; 
		       
		$sqlp = "SELECT iGcmRegId,vCompany,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode FROM company WHERE iCompanyId = '".$iCompanyId."'";
    $passengerData = $obj->MySQLSelect($sqlp);
    //$iGcmRegId=get_value('register_user', 'iGcmRegId', 'iUserId',$passengerId,'','true');
    $iGcmRegId = $passengerData[0]['iGcmRegId']; 
		
		if($vDeviceToken != "" && $vDeviceToken != $iGcmRegId){
			$returnArr['Action'] = "0";
			$returnArr['message'] = "SESSION_OUT";
			echo json_encode($returnArr);
			exit;
		}
		
		$final_message['Message'] = "CabRequested";
		$final_message['sourceLatitude'] = strval($PickUpLatitude);
		$final_message['sourceLongitude'] = strval($PickUpLongitude);
		$final_message['PassengerId'] = strval($iUserId);
    $final_message['iCompanyId'] = strval($iCompanyId);
    $final_message['iOrderId'] = strval($iOrderId);
		$passengerFName = $passengerData[0]['vCompany'];
    $final_message['PName'] = $passengerFName;
    $final_message['PPicName'] = $passengerData[0]['vImgName'];
    $final_message['PRating'] = $passengerData[0]['vAvgRating'];
    $final_message['PPhone'] = $passengerData[0]['vPhone'];
    $final_message['PPhoneC'] = $passengerData[0]['vPhoneCode'];
		$final_message['PPhone'] = '+'.$final_message['PPhoneC'].$final_message['PPhone'];
		$final_message['destLatitude'] = strval($DestLatitude);
		$final_message['destLongitude'] = strval($DestLongitude);
		$final_message['MsgCode'] = strval(time().mt_rand(1000, 9999));
		$final_message['vTitle'] = $alertMsg;
		//$final_message['Time']= strval(date('Y-m-d'));
		
		$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
		$str_date = @date('Y-m-d H:i:s', strtotime('-'.$cmpMinutes.' minutes'));
                
//                if($flag == 1)
//                {
//    $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (".$driver_id_auto.") AND tLocationUpdateDate > '$str_date' ";
//                }
//                else
//                {
//      $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (".$driver_id_auto.") AND vAvailability='Available' AND tLocationUpdateDate > '$str_date' ";              
//                }
                
                
                if($flag == 1)
                {
                 $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (".$driver_id_auto.") AND vAvailability='Available' AND tLocationUpdateDate > '$str_date' ";
    
                }
                else
                {
                   $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (".$driver_id_auto.") AND tLocationUpdateDate > '$str_date' ";     
                }
		$result = $obj->MySQLSelect($sql);            
		// echo "Res:count:".count($result);exit;
    if(count($result) == 0 || $driver_id_auto == "" || count($Data) == 0){
			$returnArr['Action'] = "0";
			$returnArr['message'] = "NO_CARS";
            echo json_encode($returnArr);
            exit;
		}
//	if($totaltime > 55)
//        {
//            $returnArr['Action'] = "0";
//			$returnArr['message'] = "Sorry time is greater than excected";
//            echo json_encode($returnArr);
//            exit;
//        }
		// $where = " iUserId = '$passengerId'";
		$where = "";
		if($PUBNUB_DISABLED == "Yes"){
			$ENABLE_PUBNUB = "No";
		}
		$alertSendAllowed = true;
		
		if($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
			
			//$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
			$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY,"subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
			$filter_driver_ids = str_replace(' ', '', $driver_id_auto);
			$driverIds_arr = explode(",",$filter_driver_ids);
			
			$message= stripslashes(preg_replace("/[\n\r]/","",$message));
			
			$deviceTokens_arr_ios = array();
			$registation_ids_new = array();
			
			$sourceLoc = $PickUpLatitude.','.$PickUpLongitude;
			$destLoc = $DestLatitude.','.$DestLongitude;
                        //count($driverIds_arr)
			for($i=0;$i<1; $i++){
      
        $sqld = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId,vLang FROM register_driver WHERE iDriverId = '".$driverIds_arr[$i]."'";
        $driverTripData = $obj->MySQLSelect($sqld);
        $iAppVersion = $driverTripData[0]['iAppVersion'];
        $eDeviceType = $driverTripData[0]['eDeviceType'];
        $vDeviceToken = $driverTripData[0]['iGcmRegId'];
        $tSessionId = $driverTripData[0]['tSessionId'];
        $vLang = $driverTripData[0]['vLang']; 
				/* For PubNub Setting Finished */
				
				$final_message['tSessionId'] = $tSessionId;
        $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING'," and vCode='".$vLang."'",'true');
        $final_message['vTitle'] = $alertMsg_db; 
				$msg_encode_pub = json_encode($final_message,JSON_UNESCAPED_UNICODE);
				$channelName = "CAB_REQUEST_DRIVER_".$driverIds_arr[$i];
				// $info = $pubnub->publish($channelName, $message);
				$info = $pubnub->publish($channelName, $msg_encode_pub );
				    
				if($eDeviceType != "Android"){
					array_push($deviceTokens_arr_ios, $vDeviceToken);
				}
				
			}
			
		}
		
		if($alertSendAllowed == true){
			$deviceTokens_arr_ios = array();
			$registation_ids_new = array();
			
			foreach ($result as $item) {
				if($item['eDeviceType'] == "Android"){
					array_push($registation_ids_new, $item['iGcmRegId']);
					}else{
					array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
				}
        
        $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING'," and vCode='".$item['vLang']."'",'true');
				$tSessionId= $item['tSessionId'];

				$final_message['tSessionId'] = $tSessionId;
				$final_message['vTitle'] = $alertMsg_db;
        $msg_encode  = json_encode($final_message,JSON_UNESCAPED_UNICODE);
				// Add User Request
				$data_userRequest = array();
				$data_userRequest['iUserId'] = $iUserId;
				$data_userRequest['iDriverId'] = $item['iDriverId'];
				$data_userRequest['tMessage'] = $msg_encode;
				$data_userRequest['iMsgCode'] = $final_message['MsgCode'];
				$data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
				$requestId = addToUserRequest2($data_userRequest);
				
				// Add Driver Request
				$data_driverRequest = array();
				$data_driverRequest['iDriverId'] = $item['iDriverId'];
				$data_driverRequest['iRequestId'] = $requestId;
				$data_driverRequest['iUserId'] = $iUserId;
				$data_driverRequest['iTripId'] = 0;
        $data_driverRequest['iOrderId'] = $iOrderId;
				$data_driverRequest['eStatus'] = "Timeout";
				$data_driverRequest['vMsgCode'] = $final_message['MsgCode'];
				$data_driverRequest['vStartLatlong'] = $sourceLoc;
				$data_driverRequest['vEndLatlong'] = $destLoc;
				$data_driverRequest['tStartAddress'] = $PickUpAddress;
				$data_driverRequest['tEndAddress'] = $DestAddress ;
				$data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
				addToDriverRequest2($data_driverRequest);
				// addToUserRequest($passengerId,$item['iDriverId'],$msg_encode,$final_message['MsgCode']);
				// addToDriverRequest($item['iDriverId'],$passengerId,0,"Timeout");
			}
			if(count($registation_ids_new) > 0){
				// $Rmessage = array("message" => $message);
				$Rmessage = array("message" => $msg_encode);
				 
				 $result = send_notification($registation_ids_new, $Rmessage,0);
				
			}
			if(count($deviceTokens_arr_ios) > 0){
				// sendApplePushNotification(1,$deviceTokens_arr_ios,$message,$alertMsg,1);
				sendApplePushNotification(1,$deviceTokens_arr_ios,$msg_encode,$alertMsg,0);
			}
		}
		
		
		
		$returnArr['Action'] = "1";
    echo json_encode($returnArr);
	}
	
	###########################################################################
        
        
?>
