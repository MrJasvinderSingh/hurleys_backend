<?php
include_once('../../common.php');
if (!isset($generalobjDriver)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjDriver = new General_admin();
}
$generalobjDriver->check_member_login();

$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
 // echo "<pre>"; print_r($_REQUEST);
// die;
 //Start make deleted
if ($method == 'delete' && $iDriverId != '') {
	if(SITE_TYPE !='Demo'){
            $query = "UPDATE register_driver SET eStatus = 'Deleted' WHERE iDriverId = '" . $iDriverId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"].' Delete Successfully.';   
	}
	else{
            $_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."driver.php?".$parameters); exit;
}
//End make deleted

//Start Change single Status
if ($iDriverId != '' && $status != '') {
	if(SITE_TYPE !='Demo'){
		
		if(strtolower($status) == 'active'){
			$sql="SELECT register_driver.iDriverId from register_driver
			LEFT JOIN driver_vehicle on driver_vehicle.iDriverId=register_driver.iDriverId
			WHERE driver_vehicle.eStatus='Active' AND driver_vehicle.vCarType != ''  AND register_driver.iDriverId='".$iDriverId."'";
			// remove this due to inactive error check
			$Data=$obj->MySQLSelect($sql);
			if(count($Data) == 0){
				$_SESSION['success'] = '3';
				$_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] .' status can not be activated because either '. $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"].' has not added any vehicle or his added vehicle is not activated yet. Please try again after adding and activating the vehicle';
				header("Location:".$tconfig["tsite_url_main_admin"]."driver.php?".$parameters);
				exit;
			}
		}
		
		$query = "UPDATE register_driver SET eStatus = '" . $status . "' WHERE iDriverId = '" . $iDriverId . "'";
		$obj->sql_query($query);
		$_SESSION['success'] = '1';
		if($status == 'Active') {
			   $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"].' Activated Successfully';
		}else {
			   $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"].' Inactivated Successfully';
		}
	}
	else{
            $_SESSION['success']=2;
	}
        header("Location:".$tconfig["tsite_url_main_admin"]."driver.php?".$parameters);
        exit;
}
//End Change single Status

//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") {
	if(SITE_TYPE !='Demo'){
		 $query = "UPDATE register_driver SET eStatus = '" . $statusVal . "' WHERE iDriverId IN (" . $checkbox . ")";
		 $obj->sql_query($query);
		 $_SESSION['success'] = '1';
		 $_SESSION['var_msg'] = $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].'(s) updated successfully.';
	}
	else{
		$_SESSION['success']=2;
	}
        header("Location:".$tconfig["tsite_url_main_admin"]."driver.php?".$parameters);
        exit;
}
/*if ($method == 'reset' && $iDriverId != '') {
	if(SITE_TYPE !='Demo'){
            $query = "UPDATE register_driver SET vCreditCard='NULL',iTripId='0',vTripStatus='NONE',vStripeToken='',vStripeCusId='' WHERE iDriverId = '" . $iDriverId . "'";          
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] =  $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].'Reset successfully';   
	}
	else{
            $_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."driver.php"); exit;
}*/

if ($method == 'reset' && $iDriverId != '') {
	$q = "SELECT iTripId,vTripStatus FROM register_driver WHERE iDriverId = '".$iDriverId."'";
	$drvdata = $obj->MySQLSelect($q);
	
	if(!empty($drvdata) && $drvdata[0]['iTripId'] != '0'){
		$sql = "SELECT iTripId,iActive,iDriverId,iUserId FROM trips WHERE iTripId = '".$drvdata[0]['iTripId']."'";
		$TripData = $obj->MySQLSelect($sql);

		// user
		$userquery = "SELECT iTripId,vTripStatus FROM register_user WHERE iUserId = '".$TripData[0]['iUserId']."'";
		$useData = $obj->MySQLSelect($userquery);

		if($TripData[0]['iActive'] == 'On Going Trip') {

			// driver
			$query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $iDriverId . "'";
			$obj->sql_query($query);

			// trip
			$query1 = "UPDATE trips SET iActive='Finished',tEndDate = NOW() WHERE iTripId = '" . $drvdata[0]['iTripId'] . "'";
			$obj->sql_query($query1);

			// rating 
			$checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver'";
			$TripRateDatadriver = $obj->MySQLSelect($checkrate);

			if(!empty($TripRateDatadriver)){
				$rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver'";
				$obj->sql_query($rateq);
			} else {
				$rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('".$drvdata[0]['iTripId']."','0.0',NOW(),'Driver','')";
				$obj->sql_query($rateq);
			}
			// rating

			if($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
				// user
				$uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
				$obj->sql_query($uquery);
				// rating 
				$checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger'";
				$TripRateDatapass = $obj->MySQLSelect($checkrate);
				if(!empty($TripRateDatapass)){
					$rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger'";
					$obj->sql_query($rateq);
				} else {
					$rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('".$drvdata[0]['iTripId']."','0.0',NOW(),'Passenger','')";
					$obj->sql_query($rateq);
				}
			}
		} else if($TripData[0]['iActive'] == 'Active'){
			// driver
			$aquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $iDriverId . "'";
			$obj->sql_query($aquery);

			// trip
			$qu1 = "UPDATE trips SET iActive = 'Canceled',tEndDate = NOW(),eCancelled = 'Yes', eCancelledBy='Driver', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "'";
			$obj->sql_query($qu1);

			// user
			if($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
				// user
				$uquery = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
				$obj->sql_query($uquery);
			}
		} else {
			if($TripData[0]['iActive'] == 'Canceled'){
				// Driver 
				if($drvdata[0]['vTripStatus'] != 'Cancelled' && $drvdata[0]['iTripId'] == $TripData[0]['iTripId']){
					$dquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $iDriverId . "'";
					$obj->sql_query($dquery);
				}

				// Rider
				if($useData[0]['vTripStatus'] != 'Cancelled' && $useData[0]['iTripId'] == $TripData[0]['iTripId']){
					$rquery = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
					$obj->sql_query($rquery);
				}
			} else {
				// Driver 
				if($drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
					$query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $iDriverId . "'";
					$obj->sql_query($query);

					// rating 
					$checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver'";
					$TripRateDatadriver = $obj->MySQLSelect($checkrate);

					if(!empty($TripRateDatadriver)){
						$rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver'";
						$obj->sql_query($rateq);
					} else {
						$rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('".$drvdata[0]['iTripId']."','0.0',NOW(),'Driver','')";
						$obj->sql_query($rateq);
					}

				}

				// Rider
				if($useData[0]['iTripId'] == $TripData[0]['iTripId']){
					// user
					$uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
					$obj->sql_query($uquery);
					// rating 
					$checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger'";
					$TripRateDatapass = $obj->MySQLSelect($checkrate);
					if(!empty($TripRateDatapass)){
						$rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger'";
						$obj->sql_query($rateq);
					} else {
						$rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('".$drvdata[0]['iTripId']."','0.0',NOW(),'Passenger','')";
						$obj->sql_query($rateq);
					}
				}
			}
		}
	}

	/*    $query = "UPDATE register_driver SET vCreditCard='',iTripId='0',vTripStatus='NONE',vStripeToken='',vStripeCusId='' WHERE iDriverId = '" . $iDriverId . "'";          
    $obj->sql_query($query);*/
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] =  $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].'Reset successfully';   
	
	header("Location:".$tconfig["tsite_url_main_admin"]."driver.php"); exit;
}
//End Change All Selected Status
?>