<?php
include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');
include_once('include_config.php');
//echo TPATH_CLASS; exit;
include_once(TPATH_CLASS . 'configuration.php');
//error_reporting(-1);
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
global $generalobj, $obj;
$generalobjAdmin->check_member_login();
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 205) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$iCompanyId = $_REQUEST['iCompanyId'];

$sql = "SELECT `iCompanyId`,`vCompany`,`eAvailable` FROM `company` WHERE `iCompanyId` = '$iCompanyId';";
$restquery = $obj->MySQLSelect($sql);

$result = 0;
$ELogoutV = '0';
if(count($restquery) > 0)
{
    
    $availibilty = $restquery[0]['eAvailable'];
    $result = 'Yes';
    $ELogoutV = 'No';
    if($availibilty == 'Yes' )
    {
        $result = 'No';
         $ELogoutV = 'Yes';
    }
    $Data_update_restaurant['eAvailable'] = $result;
    $Data_update_restaurant['eLogout'] = $ELogoutV;
    $where = " iCompanyId='" . $iCompanyId . "'";
    $id = $obj->MySQLQueryPerform("company", $Data_update_restaurant, 'update', $where);
    
    if($id)
    {
        $result = 1;
    }
    
}


echo $result;

?>
