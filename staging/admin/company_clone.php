<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
	require_once(TPATH_CLASS . "class.general_admin.php");
	$generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

if(!empty($_REQUEST['parentvalue']))
{
    $parent_iCompanyid = $_REQUEST['parentvalue'];
$sql = "SELECT `iCompanyId`,`vCompany` FROM `company` WHERE `parent_iCompanyId` = '$parent_iCompanyid' AND `eStatus` = 'Active' ";

$companies = $obj->MySQLSelect($sql);

    if(count($companies) > 0)
    {
        echo json_encode($companies);
    }
 else {
        echo '[{"iCompanyId":0, "vCompany":"Select option"}]';
    }

}
else
{
    echo '[{"iCompanyId":0, "vCompany":"Select option"}]';
}

?>