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
$iOrderStatusId = isset($_REQUEST['iOrderStatusId']) ? $_REQUEST['iOrderStatusId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

 //echo "<pre>"; print_r($_REQUEST); die;

//Start make deleted
if ($method == 'delete' && $iOrderStatusId != '') {
	if(SITE_TYPE !='Demo'){
            $query = "DELETE FROM order_status_pratik20072018 WHERE iOrderStatusId ='".$iOrderStatusId."'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = 'Order Status pratik deleted successfully.';   
	}
	else{
            $_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."order_status_pratik20072018.php?".$parameters); exit;
}
//End make deleted

//Start Change All Selected Status
if($checkbox != "" && $statusVal == "Deleted") {
	if(SITE_TYPE !='Demo'){
		 $query = "DELETE FROM order_status_pratik20072018 WHERE iOrderStatusId IN (" . $checkbox . ")";
		 $obj->sql_query($query);
		 $_SESSION['success'] = '1';
		 $_SESSION['var_msg'] = 'Order Status pratik(s) deleted successfully.';
	}
	else{
		$_SESSION['success']=2;
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."order_status_pratik200718.php?".$parameters); exit;
}
//End Change All Selected Status

//if ($iDriverId != '' && $status != '') {
//    if (SITE_TYPE != 'Demo') {
//        $query = "UPDATE register_driver SET eStatus = '" . $status . "' WHERE iDriverId = '" . $iDriverId . "'";
//        $obj->sql_query($query);
//        $_SESSION['success'] = '1';
//        $_SESSION['var_msg'] = "Rider " . $status . " Successfully.";
//        header("Location:".$tconfig["tsite_url_main_admin"]."rider.php?".$parameters);
//        exit;
//    } else {
//        $_SESSION['success']=2;
//        header("Location:".$tconfig["tsite_url_main_admin"]."rider.php?".$parameters);
//        exit;
//    }
//}
?>