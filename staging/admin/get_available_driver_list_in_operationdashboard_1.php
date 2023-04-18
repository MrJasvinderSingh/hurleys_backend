<?php
include_once('../common.php');
include_once('../generalFunctions.php');
if(!isset($generalobjAdmin)){
	require_once(TPATH_CLASS."class.general_admin.php");
	$generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

//function fetchtripstatustimeMAXinterval(){
//	global $generalobjAdmin,$FETCH_TRIP_STATUS_TIME_INTERVAL;
//
//	$FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-",$FETCH_TRIP_STATUS_TIME_INTERVAL);
//
//	$FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
//
//	return $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX;
//}

$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-0'.$cmpMinutes.' minutes'));

$ssql = " ";
if($keyword != "") {
	$ssql .= " AND CONCAT(rd.vName,' ',rd.vLastName) like '%$keyword%'";
}
if($type != ""){
	if($type == 'Available'){
		$ssql .= " AND rd.vAvailability = '".$type."' AND rd.vTripStatus != 'Active' AND rd.tLocationUpdateDate > '$str_date'";
	} else {
		$ssql .= " AND rd.vTripStatus = '".$type."' ";
	}
}
$sql = "SELECT rd.iDriverId,rd.vEmail,rd.iCompanyId, CONCAT(rd.vName,' ',rd.vLastName) AS FULLNAME,rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline, rd.vImage, rd.vCode, rd.vPhone, dv.vCarType,rd.tLocationUpdateDate FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' AND rd.eStatus = 'active' ORDER BY ( case rd.vTripStatus when 'Active' then 1 when 'On Going Trip' then 2 when 'Finished' then 3 when 'NONE' then 4 else 5 end ) ASC; ";
$db_records = $obj->MySQLSelect($sql);
//echo"<pre>";print_R($db_records);die;

$dbDrivers = array();
for($i=0;$i<count($db_records);$i++){
	$newArray = array();
	$newArray = explode(',',$db_records[$i]['vCarType']);
	if($iVehicleTypeId == '' || (!empty($newArray) && in_array($iVehicleTypeId,$newArray))) {
	if ($db_records[$i]['vImage'] != 'NONE' && $db_records[$i]['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"]. '/' . $db_records[$i]['iDriverId'] . '/2_'.$db_records[$i]['vImage'])) {
		$DriverImage = $tconfig["tsite_upload_images_driver"]. '/' . $db_records[$i]['iDriverId'] . '/2_'.$db_records[$i]['vImage'];
	}else{
		$DriverImage = $tconfig["tsite_url"]."assets/img/profile-user-img.png";
	}
	$db_records[$i]['vImageDriver'] = $DriverImage;
	$time = time();
	$last_online_time = strtotime($db_records[$i]['tLastOnline']);
	$time_difference = $time-$last_online_time;
	$vTripStatus = $db_records[$i]['vTripStatus'];
	if($vTripStatus == 'Active'){
		$db_records[$i]['vAvailability'] = $vTripStatus;
		$dbDrivers[$i] = $db_records[$i];
	} else if($vTripStatus == 'Arrived') {
		$db_records[$i]['vAvailability'] = $vTripStatus;
		$dbDrivers[$i] = $db_records[$i];
	} else if($vTripStatus == 'On Going Trip'){
		$db_records[$i]['vAvailability'] = $vTripStatus;
		$dbDrivers[$i] = $db_records[$i];
	} else if($vTripStatus != 'Active' && $db_records[$i]['vAvailability'] == "Available" && $db_records[$i]['tLocationUpdateDate'] > $str_date){
		$db_records[$i]['vAvailability'] = "Available";
		$dbDrivers[$i] = $db_records[$i];
	} else {
		$db_records[$i]['vAvailability'] = "Not Available";
		$dbDrivers[$i] = $db_records[$i];
	}
/*	if($db_records[$i]['vAvailability'] == "Available"){
		$db_records[$i]['vAvailability'] = "Available";
		$dbDrivers[$i] = $db_records[$i];
	}else{

	  if($vTripStatus == 'Active' || $vTripStatus == 'On Going Trip' || $vTripStatus == 'Arrived'){
		 $db_records[$i]['vAvailability'] = $vTripStatus;
		 $dbDrivers[$i] = $db_records[$i];
	  }else{
	  	$dbDrivers[$i] = $db_records[$i];
		$db_records[$i]['vAvailability'] = "Not Available";
	  }
	}*/
	}
}
/* echo "<pre>";
 print_r($dbDrivers); die;*/
#marker Add
$con = "";
$RY = $ER = $AS = $BK = 0;
foreach ($dbDrivers as $key => $value) {
	//if($value['vAvailability'] != "Not Available") {
    $status = '';
    $color = '';
    //$dbDrivers['iDriverId']
    $driverTime = GetDriverStatusIFonGoingWithOrders($value['iDriverId']);
		if(empty($driverTime))
		{
			$driverTime = $value['tLastOnline'];
		}
		$orders = GetCountAllOrdersDriver($value['iDriverId']);
                $Startdate = date('Y-m-d') . ' 00:00:01';
$Enddate = date('Y-m-d H:i:s');
               $Adtsql = "SELECT SEC_TO_TIME (AVG (TIMESTAMPDIFF ( SECOND, `pickuptime`,`deliverytime`))) as adt FROM orders where iStatusCode IN (4,5) AND `iDriverId` = '$value[iDriverId]' AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
                $Adtresult = $obj->MySQLSelect($Adtsql);
                $Adtvalue = 0;
                
                if (!empty($Adtresult[0]['adt'])) {
    $adt = str_replace(".0000", "", $Adtresult[0]['adt']);
    $Adtvalue =  date('H:i', strtotime($adt));
}
		if($value['vAvailability'] == " ") {
			$status = 'BK';
			$color = '#cccccc';
			$BK++;
            $statusIcon = "../assets/img/green-icon.png";
		} else if($value['vAvailability'] == "Active"  && !empty($orders) ) {
			$status = 'AS';
			$AS++;
			$color = '#cccccc';
			$statusIcon = "../assets/img/blue.png";
			$driverTimeinMin = date('H:i', strtotime('-'.GetTimeDiffInMinutes($driverTime).'minutes', strtotime(date('Y-m-d H:i:s'))));
			//onclick="showPopupDriver('.$value['iDriverId'].');" 
			$con .= '<tr style="background-color:'.$color.';" ><td cellspacing="20px" class="opdrivernamecls"><p style="cursor:pointer; font-size: 13px;" class="driver_'.$value['iDriverId'].'"><b>'.$generalobjAdmin->clearName($value['FULLNAME']).'</b> <br/>ADT'.$Adtvalue.'   '. $orders.' Orders</p></td><td class="opdrivernamecls">'.$status.'<br/>'.$driverTimeinMin.'</td></tr>';
		} else if($value['vAvailability'] == "Active" && empty($orders)) {
			$status = 'RY';
			$color = '#cccccc';
			$statusIcon = "../assets/img/red.png";
			$driverTimeinMin = date('H:i', strtotime('-'.GetTimeDiffInMinutes($driverTime).'minutes', strtotime(date('Y-m-d H:i:s'))));
			//onclick="showPopupDriver('.$value['iDriverId'].');" 
			$con .= '<tr style="background-color:'.$color.';" ><td cellspacing="20px" class="opdrivernamecls"><p style="cursor:pointer; font-size: 13px;" class="driver_'.$value['iDriverId'].'"><b>'.$generalobjAdmin->clearName($value['FULLNAME']).'</b> <br/>ADT'.$Adtvalue.'  '.$orders.' Orders</p></td><td class="opdrivernamecls">'.$status.'<br/>'.$driverTimeinMin.'</td></tr>';
			$RY++;
		}else if($value['vAvailability'] == "On Going Trip") {
			$ER++;
			$status = 'ER';
			$color = '#cccccc';
			$statusIcon = "../assets/img/yellow.png";
			$driverTimeinMin = date('H:i', strtotime('-'.GetTimeDiffInMinutes($driverTime).'minutes', strtotime(date('Y-m-d H:i:s'))));
			//onclick="showPopupDriver('.$value['iDriverId'].');" 
			$con .= '<tr style="background-color:'.$color.';" ><td cellspacing="20px" class="opdrivernamecls"><p style="cursor:pointer; font-size: 13px;" class="driver_'.$value['iDriverId'].'"><b>'.$generalobjAdmin->clearName($value['FULLNAME']).'</b> <br/>ADT'.$Adtvalue.'  '.$orders.' Orders</p></td><td class="opdrivernamecls">'.$status.'<br/>'.$driverTimeinMin.'</td></tr>';
		} else if($value['vAvailability'] == "Available") {
			$status = 'RY';
			$color = '#cccccc';
			$driverTimeinMin = date('H:i', strtotime('-'.GetTimeDiffInMinutes($driverTime).'minutes', strtotime(date('Y-m-d H:i:s'))));
			//onclick="showPopupDriver('.$value['iDriverId'].');" 
			$con .= '<tr style="background-color:'.$color.';" ><td cellspacing="20px" class="opdrivernamecls"><p style="cursor:pointer; font-size: 13px;" class="driver_'.$value['iDriverId'].'"><b>'.$generalobjAdmin->clearName($value['FULLNAME']).'</b> <br/>ADT'.$Adtvalue.'  '.$orders.' Orders</p></td><td class="opdrivernamecls">'.$status.'<br/>'.$driverTimeinMin.'</td></tr>';
			$statusIcon = "../assets/img/offline-icon.png";
			$RY++;
		} else if($value['vAvailability'] == "Not Available") {
			$status = 'BK';
			$color = '#cccccc';
			$BK++;
			$statusIcon = "../assets/img/offline-icon.png";
		} else {
			$status = 'BK';
			$color = '#cccccc';
			$statusIcon = "../assets/img/offline-icon.png";
			$BK++;
		}
}
$returnarr = array('res'=>$con,'ry'=>$RY,'as'=>$AS,'er'=>$ER,'bk'=>$BK);
echo json_encode($returnarr); exit;
?>