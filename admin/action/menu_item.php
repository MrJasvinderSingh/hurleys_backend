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
$iMenuItemId = isset($_REQUEST['iMenuItemId']) ? $_REQUEST['iMenuItemId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//Start Menu Items deleted
if ($method == 'delete' && $iMenuItemId != '') {
	if(SITE_TYPE !='Demo') {
        $sql = "SELECT * FROM menu_items WHERE iMenuItemId = '".$iMenuItemId."'";
        $db_oldData = $obj->MySQLSelect($sql);
        if(!empty($db_oldData)) {
            $iDisplayOrder = $db_oldData[0]['iDisplayOrder'];
            $iFoodMenuId = $db_oldData[0]['iFoodMenuId'];

            //$query = "DELETE FROM menu_items WHERE iMenuItemId = '" . $iMenuItemId . "'";
            $query = "UPDATE `menu_items` SET `eStatus`='Deleted' WHERE iMenuItemId = '" . $iMenuItemId . "'";
            $obj->sql_query($query);

            /*$query1 = "DELETE FROM menuitem_options WHERE iMenuItemId = '" . $iMenuItemId . "'";
            $obj->sql_query($query1);*/

            //Update Display Order
            /* $sql = "SELECT * FROM menu_items where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder >= '$iDisplayOrder' AND `eStatus`!='Deleted' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
           
            if(!empty($db_orders)){
                $j = $iDisplayOrder;
                for($i=0;$i<count($db_orders);$i++){
                    $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '".$db_orders[$i]['iMenuItemId']."'";
                    $obj->sql_query($query);
                    $j++;
                }
            }*/

            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = 'Food item deleted successfully.';
        }  
	} else {
        $_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."menu_item.php?".$parameters); exit;
}
//End Menu Items deleted

//Start Change single Status
if ($iMenuItemId != '' && $status != '') {
	if(SITE_TYPE !='Demo') {
        $query = "UPDATE menu_items SET eStatus = '" . $status . "' WHERE iMenuItemId = '" . $iMenuItemId . "'";
        $obj->sql_query($query);
        $_SESSION['success'] = '1';
        if($status == 'Active') {
            $_SESSION['var_msg'] = 'Food item activated successfully.';
        } else {
            $_SESSION['var_msg'] = 'Food item inactivated successfully.';
        }
	} else {
        $_SESSION['success']=2;
	}
    header("Location:".$tconfig["tsite_url_main_admin"]."menu_item.php?".$parameters);exit;
}
//End Change single Status

//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") {
	if(SITE_TYPE !='Demo') {
        if($statusVal == "Deleted"){
            //$query = "DELETE FROM menu_items WHERE iMenuItemId IN (" . $checkbox . ")";
            $query = "UPDATE `menu_items` SET eStatus = '" . $statusVal . "' WHERE iMenuItemId IN (" . $checkbox . ")"; 
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = 'Food item(s) delete successfully.';
        } else {
            $query = "UPDATE menu_items SET eStatus = '" . $statusVal . "' WHERE iMenuItemId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = 'Food item(s) updated successfully.';
        }
	} else {
		$_SESSION['success']=2;
	}
    header("Location:".$tconfig["tsite_url_main_admin"]."menu_item.php?".$parameters);exit;
}
//End Change All Selected Status

?>