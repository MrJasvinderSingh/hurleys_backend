<?php
include_once('../common.php');
include_once('../generalFunctions.php');
if(!isset($generalobjAdmin)){
	require_once(TPATH_CLASS."class.general_admin.php");
	$generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
//$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
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
$sql = "SELECT rd.iDriverId,rd.vEmail,rd.iCompanyId, CONCAT(rd.vName,' ',rd.vLastName) AS FULLNAME,rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline, rd.vImage, rd.vCode, rd.vPhone, rd.tLocationUpdateDate FROM register_driver AS rd WHERE  rd.eStatus = 'active' ORDER BY ( case rd.vTripStatus when 'Active' then 1 when 'On Going Trip' then 2 when 'Finished' then 3 when 'NONE' then 4 else 5 end ) ASC; ";
//rd.vLatitude !='' AND rd.vLongitude !='' AND
$db_records = $obj->MySQLSelect($sql);



$dbDrivers = array();
$con = "";
$status = '';
$color = '';
$RY = $EN = $AS = $BK = 0;
for($i=0;$i<count($db_records);$i++){
    
    $flaGEnable = 0;
	$break = 0;
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
	$orders = GetCountAllOrdersDriver($db_records[$i]['iDriverId']);
	$driverTime = GetDriverStatusIFonGoingWithOrders($db_records[$i]['iDriverId']);
	if(empty($driverTime)) {
		$driverTime = $db_records[$i]['tLastOnline'];
	}
	
	if($vTripStatus == 'Active' && !empty($orders)){
		$status = 'AS';
		$color = '#cccccc';
		$AS++;
	} else if($vTripStatus == 'Finished' && !empty($orders)) {
		$status = 'EN';
		$color = '#cccccc';
		$EN++;
	} else if($vTripStatus == 'On Going Trip' && !empty($orders)) {
		$status = 'EN';
		$color = '#cccccc';
		$EN++;
	} else if(empty($orders) && ($db_records[$i]['vAvailability'] == 'Available') ) {
            //&& (strtotime($db_records[$i]['tLocationUpdateDate'])) > strtotime ($str_date)
		$status = 'RY';
		$color = '#cccccc';
		$RY++;
                $flaGEnable =1;
	} else if(($vTripStatus == 'NONE') && ($db_records[$i]['vAvailability'] == 'Available')){
		$status = 'RY';
		$color = '#cccccc';
		$RY++;
                $flaGEnable =1;
	} else {
		$status = 'BK';
		$color = '#cccccc';
		$BK++;
		$break = 1;
	}
	if($break == 0){
		$Startdate = date('Y-m-d') . ' 00:00:01';
		$Enddate = date('Y-m-d H:i:s');
		$Adtsql = "SELECT SEC_TO_TIME (AVG (TIMESTAMPDIFF ( SECOND, `pickuptime`,`deliverytime`))) as adt FROM orders where iStatusCode IN (4,5) AND `iDriverId` = '$db_records[$i][iDriverId]' AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
		$Adtresult = $obj->MySQLSelect($Adtsql);
		$Adtvalue = 0;

		if (!empty($Adtresult[0]['adt'])) {
			$adt = str_replace(".0000", "", $Adtresult[0]['adt']);
			$Adtvalue =  date('H:i', strtotime($adt));
		}
$offlinebutton = '&nbsp;';
if($flaGEnable == 1)
{
   $offlinebutton= '<button onclick="makedriveroffline('.$db_records[$i]['iDriverId'].');" value="'.$db_records[$i]['iDriverId'].'" class="btn btn-xs btn-default">Off</button>'; 
}
		$driverTimeinMin = date('H:i', strtotime('-'.GetTimeDiffInMinutes($driverTime).'minutes', strtotime(date('Y-m-d H:i:s'))));
		$con .= '<tr style="background-color:'.$color.';" ><td cellspacing="20px" class="opdrivernamecls"><p style="cursor:pointer; font-size: 13px;" class="driver_'.$db_records[$i]['iDriverId'].'"><b>'.$generalobjAdmin->clearName($db_records[$i]['FULLNAME']).'</b> <br/>ADT'.$Adtvalue.'   '. $orders.' Orders</p></td><td class="opdrivernamecls">'.$status.'<br/>'.$driverTimeinMin.'</td><td>'.$offlinebutton.'</td></tr>';
	}
}
$TTLDriverQ = "SELECT COUNT(`iDriverId`) AS 'tcount' FROM `register_driver` WHERE `eStatus` = 'active';";
$TTLDriverO = $obj->MySQLSelect($TTLDriverQ);
$returnarr = array('res'=>$con,'ry'=>$RY,'as'=>$AS,'er'=>$EN,'bk'=>$BK,'ttldriver'=>$TTLDriverO[0]['tcount']);
echo json_encode($returnarr); exit;
?>