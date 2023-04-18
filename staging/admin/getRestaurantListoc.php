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
$type = $_REQUEST['restype'];
$title = '';
if($type == 1)
{
$TTLRestQ = "SELECT `iCompanyId`,`vCompany`,`eAvailable` FROM `company` WHERE `eStatus` = 'Active';";
$TTLRestO = $obj->MySQLSelect($TTLRestQ);
$title = 'All Restaurants';
}
if($type == 2)
{
$TTLRestQ = "SELECT `iCompanyId`,`vCompany`,`eAvailable` FROM `company` WHERE `eStatus` = 'Active' AND `eAvailable` = 'Yes';";
$TTLRestO = $obj->MySQLSelect($TTLRestQ);
$title = 'Online Restaurants';
}
if($type == 3)
{
$TTLRestQ = "SELECT `iCompanyId`,`vCompany`,`eAvailable` FROM `company` WHERE `eStatus` = 'Active' AND `eAvailable` = 'No';";
$TTLRestO = $obj->MySQLSelect($TTLRestQ);
$title = 'Offline Restaurants';
}                                   


$resultData = '';

if(count($TTLRestO) > 0)
{
    
    foreach ($TTLRestO as $restaurants)  { 
        $classRes = 'order_button_ocgreen';
       
        $btname = 'Turn Off';
        if($restaurants['eAvailable'] == 'No')
        {
          $classRes = 'order_button_ocgray';  
           $btname = 'Turn On';
        }
        
    	$resultData .= '<div class="clearfix"><div class="col-md-12" style="margin:6px 0; "><div class="col-md-6">'.$restaurants['vCompany'].'</div><div class="col-md-6"><button class="'.$classRes.'"  onclick="makerestaurantoffline('.$restaurants['iCompanyId'].');" >'.$btname.'</button>&nbsp;</div></div></div>';
        
          }   
}
else
{
    $resultData = "<h2>Sorry No restaurant found.!";
}


echo $resultData;
?>
