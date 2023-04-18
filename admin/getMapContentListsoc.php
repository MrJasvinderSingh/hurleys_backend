<?php
include_once('../common.php');
header('Content-Type: text/html; charset=utf-8');
if(!isset($generalobjAdmin)){
	require_once(TPATH_CLASS."class.general_admin.php");
	$generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

function fetchtripstatustimeMAXinterval(){
	global $generalobjAdmin,$FETCH_TRIP_STATUS_TIME_INTERVAL;
	
	//$FETCH_TRIP_STATUS_TIME_INTERVAL = $generalobj->getConfigurations("configurations", "FETCH_TRIP_STATUS_TIME_INTERVAL");
	$FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-",$FETCH_TRIP_STATUS_TIME_INTERVAL);
	
	$FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
	
	return $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX;
}

$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-0'.$cmpMinutes.' minutes'));

$driver = $_REQUEST['driver'];
$customer = $_REQUEST['customer'];
$restaurant = $_REQUEST['restaurant'];
//$Startdate = date('Y-m-d', strtotime('-1 days', strtotime(date('Y-m-d')))).' 00:00:01';
$Startdate = date('Y-m-d').' 00:00:01';
$Enddate = date('Y-m-d H:i:s');
/*
$where = " where o.iStatusCode not in (6,7,8,9) AND o.tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate'";
$sql = "SELECT o.iOrderId, o.vOrderNo, d.vName AS vNameD, d.vLastName AS vLastNameD, d.vLatitude AS vLatitudeD, d.vLongitude AS vLongitudeD, c.iCompanyId, c.vRestuarantLocation, c.vCompany, c.vRestuarantLocationLat, c.vRestuarantLocationLong, u.vName, u.vLastName, u.vPhone, ua.vBuildingNo, ua.vServiceAddress, ua.vLatitude, ua.vLongitude FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN user_address ua ON ua.iUserAddressId = o.iUserAddressId $where ORDER BY o.tOrderRequestDate ASC LIMIT 100;";
$db_records = $obj->MySQLSelect($sql);
*/
$FinalLocations = array();
$type = $_REQUEST['type'];

if(!isset($type) && ($type == '')) { $type = 'allmapdata';} 
$imageurl = 'http://hurleys.anviam.in:8086/admin/map_icons/';


// All Active restaurants
if(($type == 'restaurants') || ($type == 'allmapdata'))
{
$ResQ = "SELECT `iCompanyId`,`vCompany`,`vCaddress`,`vPhone`,`vRestuarantLocation`,`vRestuarantLocationLat`,`vRestuarantLocationLong`,`eAvailable` FROM `company` WHERE `eStatus` = 'Active' and eAvailable = 'Yes'";
$ResO = $obj->MySQLSelect($ResQ);
//Yes|No


foreach ($ResO as $keyR => $valueR) {
    $Ricon = '';
    if($valueR['eAvailable'] == "Yes") {
        $Ricon = 'res_green.png';
    }
    else
    {
        $Ricon = 'res_red.png';
    }
    $FinalLocations[] = array(
  		'google_map' => array(
  			'lat' => $valueR['vRestuarantLocationLat'],
  			'lng' => $valueR['vRestuarantLocationLong'],
  		),
                'location_name' => $valueR['vCompany'],
		'location_icon' => $imageurl.$Ricon,
  		'location_address' => $valueR['vRestuarantLocation'],
  		'location_mobile'    => $valueR['vPhone'],
  
  	);
}


}


//All Inactive restaurants
if(($type == 'inactiverestaurants') || ($type == 'inactiveallmapdata'))
{
$ResQ = "SELECT `iCompanyId`,`vCompany`,`vCaddress`,`vPhone`,`vRestuarantLocation`,`vRestuarantLocationLat`,`vRestuarantLocationLong`,`eAvailable` FROM `company` WHERE `eStatus` = 'Active' and eAvailable = 'No'";
$ResO = $obj->MySQLSelect($ResQ);
//Yes|No


foreach ($ResO as $keyR => $valueR) {
    $Ricon = '';
    if($valueR['eAvailable'] == "Yes") {
        $Ricon = 'res_green.png';
    }
    else
    {
        $Ricon = 'res_red.png';
    }
    $FinalLocations[] = array(
  		'google_map' => array(
  			'lat' => $valueR['vRestuarantLocationLat'],
  			'lng' => $valueR['vRestuarantLocationLong'],
  		),
                'location_name' => $valueR['vCompany'],
		'location_icon' => $imageurl.$Ricon,
  		'location_address' => $valueR['vRestuarantLocation'],
  		'location_mobile'    => $valueR['vPhone'],
  
  	);
}


}

//All Active Driver Lists

if(($type == 'drivers') || ($type == 'allmapdata'))
{
$DriverQ = "SELECT `iDriverId`,`vName`,`vLastName`,`vEmail`,`vPhone`,`vLatitude`,`vLongitude`,`vAvailability` FROM `register_driver` WHERE `eStatus` = 'active' and vAvailability = 'Available' ; ";
//AND tLocationUpdateDate > '$str_date'

$DriverO = $obj->MySQLSelect($DriverQ);

foreach ($DriverO as $keyD => $valueD) {
    $Dicon = '';
    if($valueD['vAvailability'] == "Available") {
        $Dicon = 'dri_green.png';
    }
    else
    {
        $Dicon = 'dri_red.png';
    }
    $FinalLocations[] = array(
  		'google_map' => array(
  			'lat' => $valueD['vLatitude'],
  			'lng' => $valueD['vLongitude'],
  		),
                'location_name' => $valueD['vName'].' '.$valueD['vLastName'],
		'location_icon' => $imageurl.$Dicon,
  		'location_address' => $valueD['vEmail'],
  		'location_mobile'    => $valueD['vPhone'],
  
  	);
}
}



//All Inactive Driver Lists
if(($type == 'inactivedrivers') || ($type == 'inactiveallmapdata'))
{
$DriverQ = "SELECT `iDriverId`,`vName`,`vLastName`,`vEmail`,`vPhone`,`vLatitude`,`vLongitude`,`vAvailability` FROM `register_driver` WHERE `eStatus` = 'active' and vAvailability != 'Available'";

$DriverO = $obj->MySQLSelect($DriverQ);

foreach ($DriverO as $keyD => $valueD) {
    $Dicon = '';
    if($valueD['vAvailability'] == "Available") {
        $Dicon = 'dri_green.png';
    }
    else
    {
        $Dicon = 'dri_red.png';
    }
    $FinalLocations[] = array(
  		'google_map' => array(
  			'lat' => $valueD['vLatitude'],
  			'lng' => $valueD['vLongitude'],
  		),
                'location_name' => $valueD['vName'].' '.$valueD['vLastName'],
		'location_icon' => $imageurl.$Dicon,
  		'location_address' => $valueD['vEmail'],
  		'location_mobile'    => $valueD['vPhone'],
  
  	);
}
}

//Not Available | 

if(($type == 'customers') || ($type == 'allmapdata'))
{
//$CustomerQ = "SELECT `register_user`.`iUserId`, `register_user`.`vName`, `register_user`.`vLastName`, `register_user`.`vEmail`, `register_user`.`vPhone`, `user_address`.`iUserAddressId`, `user_address`.`vServiceAddress`, `user_address`.`vBuildingNo`, `user_address`.`vLatitude`, `user_address`.`vLongitude` FROM `register_user` LEFT JOIN `user_address` ON `user_address`.`iUserId` = `register_user`.`iUserId` WHERE `register_user`.`eStatus` = 'Active' AND `user_address`.`vLatitude` != '' AND `user_address`.`vLongitude` != '' GROUP BY `register_user`.`iUserId` DESC LIMIT 100;";
    $where = " where o.iStatusCode not in (6,7,8,9,11,12) AND o.tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate'";
$CustomerQ = "SELECT o.iOrderId, o.vOrderNo, o.iStatusCode, u.vName, u.vLastName, u.vPhone, u.vEmail, ua.vBuildingNo, ua.vServiceAddress, ua.vLatitude, ua.vLongitude FROM orders o LEFT JOIN register_user u ON u.iUserId = o.iUserId LEFT JOIN user_address ua ON ua.iUserAddressId = o.iUserAddressId $where ORDER BY o.tOrderRequestDate ASC;";    
$CustomerO = $obj->MySQLSelect($CustomerQ);
foreach ($CustomerO as $keyC => $valueC) {
    $Cicon = 'map_user.png';
    /*if($valueC['iStatusCode'] == 6)
    {
        $Cicon = 'map_userg.png';
    }
    if($valueC['iStatusCode'] == 5)
    {
        $Cicon = 'map_userb.png';
    }
    if($valueC['iStatusCode'] == 4)
    {
        $Cicon = 'map_usery.png';
    }
    if($valueC['iStatusCode'] == 2)
    {
        $Cicon = 'map_userp.png';
    }*/
    $FinalLocations[] = array(
  		'google_map' => array(
  			'lat' => $valueC['vLatitude'],
  			'lng' => $valueC['vLongitude'],
  		),
                'location_name' => 'Order: '.substr($valueC['vOrderNo'], -3).'('.$valueC['vName'].' '.$valueC['vLastName'].')',
		'location_icon' => $imageurl.$Cicon,
  		'location_address' => $valueC['vEmail'],
  		'location_mobile'    => $valueC['vPhone'],
  
  	);
}

}
/*
$locations = array();
// if($type != "") {
// }
#marker Add

foreach ($db_records as $key => $value) {
	if(!empty($value['vLatitudeD']) && !empty($value['vLongitudeD']))
        {
  	$locations[] = array(
  		'google_map' => array(
  			'lat' => $value['vLatitudeD'],
  			'lng' => $value['vLongitudeD'],
  		),
            'location_name' => $value['vName'].''.$value['vLastName'],
		'location_icon' => $tconfig["tsite_url"]."webimages/upload/mapmarker/enroute.png",
  		'location_address' => $value['vRestuarantLocation'],
  		'location_image'    => $tconfig["tsite_url"]."webimages/upload/mapmarker/enroute.png",
  		'location_mobile'    => '9876543210',
  		'location_ID'    =>  $value['iOrderId'],
  		'location_type'    => 'Available',
  		'location_online_status'    => 'Available',
  		'location_carType'    => 'Car',
  		'location_driverId'    => $value['iCompanyId'],
  	);
        
        }
        $locations[] = array(
  		'google_map' => array(
  			'lat' => $value['vRestuarantLocationLat'],
  			'lng' => $value['vRestuarantLocationLong'],
  		),
		'location_icon' => $tconfig["tsite_url"]."webimages/upload/mapmarker/available.png",
//  		'location_address' => $value['vServiceLoc'],
//  		'location_image'    => $value['vImageDriver'],
//  		'location_mobile'    => $generalobjAdmin->clearPhone($value['vCode'].$value['vPhone']),
//  		'location_ID'    => $generalobjAdmin->clearEmail($value['vEmail']),
//  		'location_type'    => $value['vAvailability'],
//  		'location_online_status'    => $value['vAvailability'],
//  		'location_carType'    => $value['vCarType'],
//  		'location_driverId'    => $value['iDriverId'],
             'location_name' => $value['vName'].''.$value['vLastName'],
            'location_address' => $value['vRestuarantLocation'],
  		'location_image'    => $tconfig["tsite_url"]."webimages/upload/mapmarker/enroute.png",
  		'location_mobile'    => '9876543210',
  		'location_ID'    =>  $value['iOrderId'],
  		'location_type'    => 'Available',
  		'location_online_status'    => 'Available',
  		'location_carType'    => 'Car',
  		'location_driverId'    => $value['iCompanyId'],
  	);
        $locations[] = array(
  		'google_map' => array(
  			'lat' => $value['vLatitude'],
  			'lng' => $value['vLongitude'],
  		),
		'location_icon' => $tconfig["tsite_url"]."webimages/upload/mapmarker/reached.png",
//  		'location_address' => $value['vServiceLoc'],
//  		'location_image'    => $value['vImageDriver'],
//  		'location_mobile'    => $generalobjAdmin->clearPhone($value['vCode'].$value['vPhone']),
//  		'location_ID'    => $generalobjAdmin->clearEmail($value['vEmail']),
//  		'location_type'    => $value['vAvailability'],
//  		'location_online_status'    => $value['vAvailability'],
//  		'location_carType'    => $value['vCarType'],
//  		'location_driverId'    => $value['iDriverId'],
             'location_name' => $value['vName'].''.$value['vLastName'],
            'location_address' => $value['vRestuarantLocation'],
  		'location_image'    => $tconfig["tsite_url"]."webimages/upload/mapmarker/enroute.png",
  		'location_mobile'    => '9876543210',
  		'location_ID'    =>  $value['iOrderId'],
  		'location_type'    => 'Available',
  		'location_online_status'    => 'Available',
  		'location_carType'    => 'Car',
  		'location_driverId'    => $value['iCompanyId'],
  	);
}
*/
$returnArr['Action'] = "0";
$returnArr['locations'] = $FinalLocations;
//$returnArr['db_records'] = $db_records;
//$returnArr['newStatus'] = $newStatus;
// echo "<pre>"; print_r($returnArr); die;
echo json_encode($returnArr);exit;
?>