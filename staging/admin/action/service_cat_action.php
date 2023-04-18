<?php
include_once('../../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iServiceId = isset($_REQUEST['iServiceId']) ? $_REQUEST['iServiceId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//Start make deleted
if ($method == 'delete' && $cuisineId != '') {
    /*    $checkRestaurant = "SELECT count(iCompanyId) as TotalRes FROM company_cuisine WHERE cuisineId = '" . $cuisineId . "'";
    $ResData=$obj->MySQLSelect($checkRestaurant);*/
	if(SITE_TYPE !='Demo'){
            $query = "UPDATE service_categories SET eStatus = 'Deleted' WHERE iServiceId = '" . $iServiceId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = 'Service Category deleted successfully.';   
	} else {
            $_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."service_category.php?".$parameters); exit;
}
//End make deleted
//Start Change single Status
if ($iServiceId != '' && $status != '') {
	if(SITE_TYPE !='Demo'){
            $query = "UPDATE service_categories SET eStatus = '" . $status . "' WHERE iServiceId = '" . $iServiceId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            if($status == 'Active') {
                   $_SESSION['var_msg'] = 'Service Category activated successfully.';
            }else {
                   $_SESSION['var_msg'] = 'Service Category inactivated successfully.';
            }
	}
	else{
            $_SESSION['success']=2;
	}
        header("Location:".$tconfig["tsite_url_main_admin"]."service_category.php?".$parameters);
}
//End Change single Status

//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") {
	if(SITE_TYPE !='Demo'){
	     $query = "UPDATE service_categories SET eStatus = '" . $statusVal . "' WHERE iServiceId IN (" . $checkbox . ")";
		 $obj->sql_query($query);
		 $_SESSION['success'] = '1';
		 $_SESSION['var_msg'] = 'Service Category(s) updated successfully.';
	}
	else{
		$_SESSION['success']=2;
	}
        header("Location:".$tconfig["tsite_url_main_admin"]."service_category.php?".$parameters);
        exit;
}
//End Change All Selected Status

?>