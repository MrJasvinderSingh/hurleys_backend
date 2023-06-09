<?php
include_once('../../common.php');

if (!isset($generalobjRider)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjRider = new General_admin();
}
$generalobjRider->check_member_login();

$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iDeliveyChargeId = isset($_REQUEST['iDeliveyChargeId']) ? $_REQUEST['iDeliveyChargeId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//echo "<pre>"; print_r($_REQUEST); die;

//Start make deleted
if ($method == 'delete' && $iDeliveyChargeId != '') {
	if(SITE_TYPE !='Demo'){
            $query = "UPDATE delivery_charges SET eStatus = 'deleted' WHERE iDeliveyChargeId ='".$iDeliveyChargeId."'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = 'Delivery Charges deleted successfully.';   
	}
	else{
            $_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."delivery_charges.php?".$parameters); exit;
}
//End make deleted

//Start Change single Status
if ($iDeliveyChargeId != '' && $status != '') {
    if(SITE_TYPE !='Demo'){
            $query = "UPDATE delivery_charges SET eStatus = '".$status."' WHERE iDeliveyChargeId = '".$iDeliveyChargeId."'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            if($status == 'Active') {
                   $_SESSION['var_msg'] = 'Delivery Charges activated successfully.';
            }else {
                   $_SESSION['var_msg'] = 'Delivery Charges inactivated successfully.';
            }
    }
    else{
            $_SESSION['success']= 2;
    }
        header("Location:".$tconfig["tsite_url_main_admin"]."delivery_charges.php?".$parameters);
        exit;
}
//End Change single Status

//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") {
    if(SITE_TYPE !='Demo'){
         $query = "UPDATE delivery_charges SET eStatus = '" . $statusVal . "' WHERE iDeliveyChargeId IN (" . $checkbox . ")";
         $obj->sql_query($query);
         $_SESSION['success'] = '1';
         $_SESSION['var_msg'] = 'Delivery Charges updated successfully.';
    }
    else{
        $_SESSION['success']=2;
    }
        header("Location:".$tconfig["tsite_url_main_admin"]."delivery_charges.php?".$parameters);
        exit;
}
//End Change All Selected Status
?>