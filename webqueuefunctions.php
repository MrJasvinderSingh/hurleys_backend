<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function countnooftrips($iDriverId = '')
        {
             global $generalobj, $obj;
//            if(!empty($iDriverId))
//            {
            $sql = "SELECT `tEndDate` FROM `trips` WHERE `iDriverId`= $iDriverId AND iActive IN ('On Going Trip', 'Active')" ;
            $result = $obj->MySQLSelect($sql);
            return count($result);
        }
        
        
        
        
         function getOnlineDriverArrAnviam($sourceLat, $sourceLon,$address_data=array(),$DropOff="No",$From_Autoassign="No",$Check_Driver_UFX="No",$Check_Date_Time="",$destLat="", $destLon="") {
		global $generalobj, $obj, $RESTRICTION_KM_NEAREST_TAXI,$LIST_RESTAURANT_LIMIT_BY_DISTANCE,$LIST_DRIVER_LIMIT_BY_DISTANCE,$DRIVER_REQUEST_METHOD,$COMMISION_DEDUCT_ENABLE,$WALLET_MIN_BALANCE,$RESTRICTION_KM_NEAREST_TAXI,$APP_TYPE,$vTimeZone;
		                           
		$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
		$str_date = @date('Y-m-d H:i:s', strtotime('-'.$cmpMinutes.' minutes'));
		$LIST_DRIVER_LIMIT_BY_DISTANCE = $From_Autoassign =="Yes" ? $LIST_RESTAURANT_LIMIT_BY_DISTANCE : $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
		$param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLocationUpdateDate";
		
    $sourceLocationArr =array($sourceLat,$sourceLon);
		$destinationLocationArr =array($destLat,$destLon);
		$ssql_available = "";
    $allowed_ans = "Yes";
    $allowed_ans_drop = "Yes";
    $vLatitude = 'vLatitude';
    $vLongitude = 'vLongitude';
    if($Check_Driver_UFX == "No"){
      $ssql_available .= " AND vAvailability = 'Available' AND vTripStatus != 'Active' AND tLocationUpdateDate > '$str_date' ";
    }
		             
		if($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
			$sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
			* cos( radians( ROUND(".$vLatitude.",8) ) )
			* cos( radians( ROUND(".$vLongitude.",8) ) - radians(" . $sourceLon . ") )
			+ sin( radians(" . $sourceLat . ") )
			* sin( radians( ROUND(".$vLatitude.",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver`
			WHERE (".$vLatitude." != '' AND ".$vLongitude." != '' $ssql_available AND eStatus='active')
			HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " ORDER BY distance ASC LIMIT 3";
			
			$Data = $obj->MySQLSelect($sql);
                        //echo "<pre>"; print_r($Data);
      $newData = array();
      $j=0;
      $driver_id_auto = "";
			for($i=0;$i<count($Data);$i++){
$lastprocessstime =  checklastorderprocesstime($Data[$i]['iDriverId']);
$Data[$i]['lastproccessedtime']  = $lastprocessstime[0]['tEndDate'] ? $lastprocessstime[0]['tEndDate'] : 0;

 $pickupdistime = GetDrivingDistance($sourceLat, $Data[$i]['vLatitude'], $sourceLon, $Data[$i]['vLongitude']);
  $Data[$i]['pickupdistance'] = $pickupdistime['distance'];
   $Data[$i]['pickuptime']    =  $pickupdistime['time']; 
          
        $iDriverVehicleId = $Data[$i]['iDriverVehicleId'];
        $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
        $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');
        
        $distanceusercompany = distanceByLocation($sourceLat, $sourceLon, $destLat, $destLon);
				$Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];
        
        if($COMMISION_DEDUCT_ENABLE == 'Yes'){
					$user_available_balance = $generalobj->get_user_available_balance($Data[$i]['iDriverId'],"Driver");
					if($WALLET_MIN_BALANCE > $user_available_balance){
						$Data[$i]['ACCEPT_CASH_TRIPS'] = "No";
					}else{
						$Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
					}
				}else{
					$Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
				}
          $nooftrips = countnooftrips($Data[$i]['iDriverId']);
        if(($fRadius > $distanceusercompany) && ($nooftrips < 2)) {  
           $driver_id_auto .= $Data[$i]['iDriverId'].",";
           $newData[$j] = $Data[$i]; 
           $j++;
        }
			}                           
     //print_r($newData);
      $driver_id_auto = substr($driver_id_auto,0,-1);
			
			//$returnData['DriverList'] = $Data;
      $returnData['DriverList'] = $newData;
      $returnData['driver_id_auto'] = $driver_id_auto;
			$returnData['PickUpDisAllowed'] = $allowed_ans;
			$returnData['DropOffDisAllowed'] = $allowed_ans_drop;
			}else {
			/*$Data = array();
			$returnData['DriverList'] = $Data;*/
      $newData = array();
      $returnData['DriverList'] = $newData;
      $returnData['driver_id_auto'] = "";
			$returnData['PickUpDisAllowed'] = $allowed_ans;
			$returnData['DropOffDisAllowed'] = $allowed_ans_drop;
		}
		 
		return $returnData;
	}