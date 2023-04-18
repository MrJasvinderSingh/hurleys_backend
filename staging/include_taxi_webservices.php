<?php 
#/***********************************************************************************************************/#
if($iCustomerId == "1")
{
$cmp_ssql = "";
$eSystem  = " AND eSystem = 'General'";
// if(SITE_TYPE =='Demo')
//{
// $cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
// }
$sql = "SELECT * FROM company WHERE eStatus != 'Deleted'  $cmp_ssql order by tRegistrationDate desc";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$ssl = "";
if($status != "" && $status == "active") {
$ssl = " AND rd.eStatus = '".$status."'";
} else if($status != "" && $status == "inactive") {
$ssl = " AND rd.eStatus = '".$status."'";
}
$sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != 'Deleted'".$ssl.$cmp_ssql;

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$ssl = "";
if($status != "" && $status == "active") {
$ssl = " AND rd.eStatus = '".$status."'";
} else if($status != "" && $status == "inactive") {
$ssl = " AND rd.eStatus = '".$status."'";
}
$sql = "SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$sql = "SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName
FROM driver_vehicle dv, register_driver rd, make m, model md, company c
WHERE
dv.iMakeId = m.iMakeId".$cmp_ssql;

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
}
if($status=="all")
$sql = "SELECT * FROM register_user WHERE 1 = 1 ";
else
$sql = "SELECT * FROM register_user WHERE eStatus != 'Deleted'";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
}

$ssql1 = "AND (vEmail != '' OR vPhone != '')";
if($status=="all")
$sql = "SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ".$ssql1.$cmp_ssql;
else
$sql = "SELECT count(iUserId) FROM register_user WHERE eStatus != 'Deleted'".$ssql1.$cmp_ssql;


/*global $tconfig;
$previosLink = $_SERVER['REQUEST_URI'];
if ((strpos($previosLink, 'ajax') === false) && (strpos($previosLink, 'get') === false)) {
$_SESSION['current_link'] = $previosLink;
}
$sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId'])?$_SESSION['sess_iAdminUserId']:'';
$sess_iGroupId = isset($_SESSION['sess_iGroupId'])?$_SESSION['sess_iGroupId']:'';
if($sess_iAdminUserId == "" && basename($_SERVER['PHP_SELF']) != "index.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."index.php");
exit;
}
//If GroupId == 2
//echo basename($_SERVER['PHP_SELF']); die;
if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php");
exit;
} else if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) != "cab_booking.php" && basename($_SERVER['PHP_SELF']) != "add_booking.php" && basename($_SERVER['PHP_SELF']) != "action_booking.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list.php" && basename($_SERVER['PHP_SELF']) != "get_map_drivers_list.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_rider_by_number.php" && basename($_SERVER['PHP_SELF']) != "change_code.php" && basename($_SERVER['PHP_SELF']) != "get_driver_detail_popup.php" && basename($_SERVER['PHP_SELF']) != "ajax_checkBooking_email.php" && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "map.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list_in_godsview.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "ajax_booking_details.php" && basename($_SERVER['PHP_SELF']) != "checkForRestriction.php" && basename($_SERVER['PHP_SELF']) != "ajax_estimate_by_vehicle_type.php" &&  basename($_SERVER['PHP_SELF']) != "ajax_get_user_balance.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php" );
exit;
}
//If GroupId == 3
if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
exit;
}else if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) != "trip.php" && basename($_SERVER['PHP_SELF']) != "referrer.php" && strpos(basename($_SERVER['PHP_SELF']), 'report') == false && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "referrer_action.php" && basename($_SERVER['PHP_SELF']) != "export_driver_details.php" && basename($_SERVER['PHP_SELF']) != "report_export.php" && basename($_SERVER['PHP_SELF']) != "export_driver_pay_details.php" && basename($_SERVER['PHP_SELF']) != "export_trip_pay_details.php" && basename($_SERVER['PHP_SELF']) != "payment_report.php" && basename($_SERVER['PHP_SELF']) != "wallet_report.php" && basename($_SERVER['PHP_SELF']) != "driver_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_log_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_trip.php" && basename($_SERVER['PHP_SELF']) != "ride_acceptance_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "cancellation_payment_report.php"   && basename($_SERVER['PHP_SELF']) != "allorders.php" && basename($_SERVER['PHP_SELF']) != "driver_payment_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_orders.php" && basename($_SERVER['PHP_SELF']) != "restaurants_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "admin_payment_report.php" && basename($_SERVER['PHP_SELF']) != "order_invoice.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
exit;
}*/

if(count($data)>0)
{
$common_member  = "SELECT iDriverId
FROM register_driver
WHERE tRegistrationDate < '".$er_date."'";
$sql = "DELETE FROM driver_vehicle WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM trips WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM log_file WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM register_driver WHERE tRegistrationDate < '".$date."'";
}


if(count($userObj->locations) > 0){
$locations = implode(', ', $userObj->locations);
$locations_where = " AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
}
if($tripStatus != "") {
if($tripStatus == "on ride") {
$ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
}else if($tripStatus == "cancelled") {
$ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
}else if($tripStatus == "finished") {
$ssl = " AND iActive = 'Finished' AND eCancelled='No'";
}else {
$ssl = "";
}
$sql = "SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = 'General'".$cmp_ssql.$ssl.$dsql.$locations_where;
}
if($tripStatus != "") {
if($tripStatus == "on ride") {
$ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
}else if($tripStatus == "cancelled") {
$ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
}else if($tripStatus == "finished") {
$ssl = " AND iActive = 'Finished' AND eCancelled='No'";
}else {
$ssl = "";
}
$sql = "SELECT iTripId FROM trips WHERE 1".$cmp_ssql.$ssl.$dsql.$locations_where;
}
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tEndDate > '".WEEK_DATE."'";
}
if($time == "month") {
$startDate = date('Y-m')."-01";
$endDate = date('Y-m')."-31";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-01";
$endDate1 = date('Y')."-12-31";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')."";
$endDate2 = date('Y-m-d')."";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}


$mailerCharSet = 'UTF-8';

$mailerHost       = "mail.example.com"; // SMTP server example
$mailerSMTPDebug  = 0;                     // enables SMTP debug information (for testing)
$mailerSMTPAuth   = true;                  // enable SMTP authentication
$mailerPort       = 25;                    // set the SMTP port for the GMAIL server
$mailerUsername   = "username"; // SMTP account username example
$mailerPassword   = "password";        // SMTP account password example

$message = "The mail message was sent with the following mail setting:\r\nSMTP = aspmx.l.google.com\r\nsmtp_port = 25\r\nsendmail_from = YourMail@address.com";
$headers = "From: YOURMAIL@gmail.com";
//mail("Sending@provider.com", "Testing", $message, $headers);
echo "Check your email now....<BR/>";

$NS = 'http://www.w3.org/2005/Atom';
$ATOM_CONTENT_ELEMENTS = array('content','summary','title','subtitle','rights');
$ATOM_SIMPLE_ELEMENTS = array('id','updated','published','draft');
$debug = false;
$depth = 0;
$indent = 2;
$in_content;
$ns_contexts = array();
$ns_decls = array();
$content_ns_decls = array();
$content_ns_contexts = array();
$is_xhtml = false;
$is_html = false;
$is_text = true;
$skipped_div = false;
$FILE = "php://input";
$feed;
$current;

foreach ($data as $key => $value) {
$fCommision = $value['fCommision'];
$fTotalGenerateFare = $value['fTotalGenerateFare'];
$fDeliveryCharge = $value['fDeliveryCharge'];
$fOffersDiscount = $value['fOffersDiscount'];
$fRestaurantPayAmount = $value['fRestaurantPayAmount'];

if($value['iStatusCode'] == '7' || $value['iStatusCode'] == '8') { 
$amounts = $fRestaurantPaidAmount;
} else {
$amounts = $fTotalGenerate - $fComm - $fDelivery- $fOffersDis;
}
$total += $amounts;
}

if($time == "month") {
$startDate = date('Y-m')."-00 00:00:00";
$endDate = date('Y-m')."-31 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-00 00:00:00";
$endDate1 = date('Y')."-12-31 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')." 00:00:00";
$endDate2 = date('Y-m-d')." 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tEndDate > '".WEEK_DATE."'";
}
if($time == "month") {
$startDate = date('Y-m')."-00 00:00:00";
$endDate = date('Y-m')."-31 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-00 00:00:00";
$endDate1 = date('Y')."-12-31 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')." 00:00:00";
$endDate2 = date('Y-m-d')." 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}

}
#/***********************************************************************************************************/#
	$cache = array ();
	$cache_hits = 0;
	$cache_misses = 0;
	$global_groups = array();
	$ride_prefix;
	#/***********************************************************************************************************/#
	if($iCustomerId == "1")
{
$cmp_ssql = "";
$eSystem  = " AND eSystem = 'General'";
// if(SITE_TYPE =='Demo')
//{
// $cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
// }
$sql = "SELECT * FROM company WHERE eStatus != 'Deleted'  $cmp_ssql order by tRegistrationDate desc";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$ssl = "";
if($status != "" && $status == "active") {
$ssl = " AND rd.eStatus = '".$status."'";
} else if($status != "" && $status == "inactive") {
$ssl = " AND rd.eStatus = '".$status."'";
}
$sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != 'Deleted'".$ssl.$cmp_ssql;

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$ssl = "";
if($status != "" && $status == "active") {
$ssl = " AND rd.eStatus = '".$status."'";
} else if($status != "" && $status == "inactive") {
$ssl = " AND rd.eStatus = '".$status."'";
}
$sql = "SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$sql = "SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName
FROM driver_vehicle dv, register_driver rd, make m, model md, company c
WHERE
dv.iMakeId = m.iMakeId".$cmp_ssql;

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
}
if($status=="all")
$sql = "SELECT * FROM register_user WHERE 1 = 1 ";
else
$sql = "SELECT * FROM register_user WHERE eStatus != 'Deleted'";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
}

$ssql1 = "AND (vEmail != '' OR vPhone != '')";
if($status=="all")
$sql = "SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ".$ssql1.$cmp_ssql;
else
$sql = "SELECT count(iUserId) FROM register_user WHERE eStatus != 'Deleted'".$ssql1.$cmp_ssql;


/*global $tconfig;
$previosLink = $_SERVER['REQUEST_URI'];
if ((strpos($previosLink, 'ajax') === false) && (strpos($previosLink, 'get') === false)) {
$_SESSION['current_link'] = $previosLink;
}
$sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId'])?$_SESSION['sess_iAdminUserId']:'';
$sess_iGroupId = isset($_SESSION['sess_iGroupId'])?$_SESSION['sess_iGroupId']:'';
if($sess_iAdminUserId == "" && basename($_SERVER['PHP_SELF']) != "index.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."index.php");
exit;
}
//If GroupId == 2
//echo basename($_SERVER['PHP_SELF']); die;
if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php");
exit;
} else if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) != "cab_booking.php" && basename($_SERVER['PHP_SELF']) != "add_booking.php" && basename($_SERVER['PHP_SELF']) != "action_booking.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list.php" && basename($_SERVER['PHP_SELF']) != "get_map_drivers_list.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_rider_by_number.php" && basename($_SERVER['PHP_SELF']) != "change_code.php" && basename($_SERVER['PHP_SELF']) != "get_driver_detail_popup.php" && basename($_SERVER['PHP_SELF']) != "ajax_checkBooking_email.php" && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "map.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list_in_godsview.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "ajax_booking_details.php" && basename($_SERVER['PHP_SELF']) != "checkForRestriction.php" && basename($_SERVER['PHP_SELF']) != "ajax_estimate_by_vehicle_type.php" &&  basename($_SERVER['PHP_SELF']) != "ajax_get_user_balance.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php" );
exit;
}
//If GroupId == 3
if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
exit;
}else if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) != "trip.php" && basename($_SERVER['PHP_SELF']) != "referrer.php" && strpos(basename($_SERVER['PHP_SELF']), 'report') == false && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "referrer_action.php" && basename($_SERVER['PHP_SELF']) != "export_driver_details.php" && basename($_SERVER['PHP_SELF']) != "report_export.php" && basename($_SERVER['PHP_SELF']) != "export_driver_pay_details.php" && basename($_SERVER['PHP_SELF']) != "export_trip_pay_details.php" && basename($_SERVER['PHP_SELF']) != "payment_report.php" && basename($_SERVER['PHP_SELF']) != "wallet_report.php" && basename($_SERVER['PHP_SELF']) != "driver_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_log_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_trip.php" && basename($_SERVER['PHP_SELF']) != "ride_acceptance_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "cancellation_payment_report.php"   && basename($_SERVER['PHP_SELF']) != "allorders.php" && basename($_SERVER['PHP_SELF']) != "driver_payment_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_orders.php" && basename($_SERVER['PHP_SELF']) != "restaurants_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "admin_payment_report.php" && basename($_SERVER['PHP_SELF']) != "order_invoice.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
exit;
}*/

if(count($data)>0)
{
$common_member  = "SELECT iDriverId
FROM register_driver
WHERE tRegistrationDate < '".$er_date."'";
$sql = "DELETE FROM driver_vehicle WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM trips WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM log_file WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM register_driver WHERE tRegistrationDate < '".$date."'";
}


if(count($userObj->locations) > 0){
$locations = implode(', ', $userObj->locations);
$locations_where = " AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
}
if($tripStatus != "") {
if($tripStatus == "on ride") {
$ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
}else if($tripStatus == "cancelled") {
$ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
}else if($tripStatus == "finished") {
$ssl = " AND iActive = 'Finished' AND eCancelled='No'";
}else {
$ssl = "";
}
$sql = "SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = 'General'".$cmp_ssql.$ssl.$dsql.$locations_where;
}
if($tripStatus != "") {
if($tripStatus == "on ride") {
$ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
}else if($tripStatus == "cancelled") {
$ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
}else if($tripStatus == "finished") {
$ssl = " AND iActive = 'Finished' AND eCancelled='No'";
}else {
$ssl = "";
}
$sql = "SELECT iTripId FROM trips WHERE 1".$cmp_ssql.$ssl.$dsql.$locations_where;
}
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tEndDate > '".WEEK_DATE."'";
}
if($time == "month") {
$startDate = date('Y-m')."-01";
$endDate = date('Y-m')."-31";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-01";
$endDate1 = date('Y')."-12-31";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')."";
$endDate2 = date('Y-m-d')."";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}


$mailerCharSet = 'UTF-8';

$mailerHost       = "mail.example.com"; // SMTP server example
$mailerSMTPDebug  = 0;                     // enables SMTP debug information (for testing)
$mailerSMTPAuth   = true;                  // enable SMTP authentication
$mailerPort       = 25;                    // set the SMTP port for the GMAIL server
$mailerUsername   = "username"; // SMTP account username example
$mailerPassword   = "password";        // SMTP account password example

$message = "The mail message was sent with the following mail setting:\r\nSMTP = aspmx.l.google.com\r\nsmtp_port = 25\r\nsendmail_from = YourMail@address.com";
$headers = "From: YOURMAIL@gmail.com";
//mail("Sending@provider.com", "Testing", $message, $headers);
echo "Check your email now....<BR/>";

$NS = 'http://www.w3.org/2005/Atom';
$ATOM_CONTENT_ELEMENTS = array('content','summary','title','subtitle','rights');
$ATOM_SIMPLE_ELEMENTS = array('id','updated','published','draft');
$debug = false;
$depth = 0;
$indent = 2;
$in_content;
$ns_contexts = array();
$ns_decls = array();
$content_ns_decls = array();
$content_ns_contexts = array();
$is_xhtml = false;
$is_html = false;
$is_text = true;
$skipped_div = false;
$FILE = "php://input";
$feed;
$current;

foreach ($data as $key => $value) {
$fCommision = $value['fCommision'];
$fTotalGenerateFare = $value['fTotalGenerateFare'];
$fDeliveryCharge = $value['fDeliveryCharge'];
$fOffersDiscount = $value['fOffersDiscount'];
$fRestaurantPayAmount = $value['fRestaurantPayAmount'];

if($value['iStatusCode'] == '7' || $value['iStatusCode'] == '8') { 
$amounts = $fRestaurantPaidAmount;
} else {
$amounts = $fTotalGenerate - $fComm - $fDelivery- $fOffersDis;
}
$total += $amounts;
}

if($time == "month") {
$startDate = date('Y-m')."-00 00:00:00";
$endDate = date('Y-m')."-31 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-00 00:00:00";
$endDate1 = date('Y')."-12-31 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')." 00:00:00";
$endDate2 = date('Y-m-d')." 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tEndDate > '".WEEK_DATE."'";
}
if($time == "month") {
$startDate = date('Y-m')."-00 00:00:00";
$endDate = date('Y-m')."-31 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-00 00:00:00";
$endDate1 = date('Y')."-12-31 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')." 00:00:00";
$endDate2 = date('Y-m-d')." 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}

}
	#/***********************************************************************************************************/#
	#/***********************************************************************************************************/#    
	include_once('common.php');
	include_once(TPATH_CLASS.'class.general.php');
	$generalobj = new General();
	#/***********************************************************************************************************/#
	if($iCustomerId == "1")
{
$cmp_ssql = "";
$eSystem  = " AND eSystem = 'General'";
// if(SITE_TYPE =='Demo')
//{
// $cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
// }
$sql = "SELECT * FROM company WHERE eStatus != 'Deleted'  $cmp_ssql order by tRegistrationDate desc";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$ssl = "";
if($status != "" && $status == "active") {
$ssl = " AND rd.eStatus = '".$status."'";
} else if($status != "" && $status == "inactive") {
$ssl = " AND rd.eStatus = '".$status."'";
}
$sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != 'Deleted'".$ssl.$cmp_ssql;

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$ssl = "";
if($status != "" && $status == "active") {
$ssl = " AND rd.eStatus = '".$status."'";
} else if($status != "" && $status == "inactive") {
$ssl = " AND rd.eStatus = '".$status."'";
}
$sql = "SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$sql = "SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName
FROM driver_vehicle dv, register_driver rd, make m, model md, company c
WHERE
dv.iMakeId = m.iMakeId".$cmp_ssql;

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
}
if($status=="all")
$sql = "SELECT * FROM register_user WHERE 1 = 1 ";
else
$sql = "SELECT * FROM register_user WHERE eStatus != 'Deleted'";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
}

$ssql1 = "AND (vEmail != '' OR vPhone != '')";
if($status=="all")
$sql = "SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ".$ssql1.$cmp_ssql;
else
$sql = "SELECT count(iUserId) FROM register_user WHERE eStatus != 'Deleted'".$ssql1.$cmp_ssql;


/*global $tconfig;
$previosLink = $_SERVER['REQUEST_URI'];
if ((strpos($previosLink, 'ajax') === false) && (strpos($previosLink, 'get') === false)) {
$_SESSION['current_link'] = $previosLink;
}
$sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId'])?$_SESSION['sess_iAdminUserId']:'';
$sess_iGroupId = isset($_SESSION['sess_iGroupId'])?$_SESSION['sess_iGroupId']:'';
if($sess_iAdminUserId == "" && basename($_SERVER['PHP_SELF']) != "index.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."index.php");
exit;
}
//If GroupId == 2
//echo basename($_SERVER['PHP_SELF']); die;
if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php");
exit;
} else if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) != "cab_booking.php" && basename($_SERVER['PHP_SELF']) != "add_booking.php" && basename($_SERVER['PHP_SELF']) != "action_booking.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list.php" && basename($_SERVER['PHP_SELF']) != "get_map_drivers_list.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_rider_by_number.php" && basename($_SERVER['PHP_SELF']) != "change_code.php" && basename($_SERVER['PHP_SELF']) != "get_driver_detail_popup.php" && basename($_SERVER['PHP_SELF']) != "ajax_checkBooking_email.php" && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "map.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list_in_godsview.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "ajax_booking_details.php" && basename($_SERVER['PHP_SELF']) != "checkForRestriction.php" && basename($_SERVER['PHP_SELF']) != "ajax_estimate_by_vehicle_type.php" &&  basename($_SERVER['PHP_SELF']) != "ajax_get_user_balance.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php" );
exit;
}
//If GroupId == 3
if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
exit;
}else if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) != "trip.php" && basename($_SERVER['PHP_SELF']) != "referrer.php" && strpos(basename($_SERVER['PHP_SELF']), 'report') == false && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "referrer_action.php" && basename($_SERVER['PHP_SELF']) != "export_driver_details.php" && basename($_SERVER['PHP_SELF']) != "report_export.php" && basename($_SERVER['PHP_SELF']) != "export_driver_pay_details.php" && basename($_SERVER['PHP_SELF']) != "export_trip_pay_details.php" && basename($_SERVER['PHP_SELF']) != "payment_report.php" && basename($_SERVER['PHP_SELF']) != "wallet_report.php" && basename($_SERVER['PHP_SELF']) != "driver_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_log_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_trip.php" && basename($_SERVER['PHP_SELF']) != "ride_acceptance_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "cancellation_payment_report.php"   && basename($_SERVER['PHP_SELF']) != "allorders.php" && basename($_SERVER['PHP_SELF']) != "driver_payment_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_orders.php" && basename($_SERVER['PHP_SELF']) != "restaurants_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "admin_payment_report.php" && basename($_SERVER['PHP_SELF']) != "order_invoice.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
exit;
}*/

if(count($data)>0)
{
$common_member  = "SELECT iDriverId
FROM register_driver
WHERE tRegistrationDate < '".$er_date."'";
$sql = "DELETE FROM driver_vehicle WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM trips WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM log_file WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM register_driver WHERE tRegistrationDate < '".$date."'";
}


if(count($userObj->locations) > 0){
$locations = implode(', ', $userObj->locations);
$locations_where = " AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
}
if($tripStatus != "") {
if($tripStatus == "on ride") {
$ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
}else if($tripStatus == "cancelled") {
$ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
}else if($tripStatus == "finished") {
$ssl = " AND iActive = 'Finished' AND eCancelled='No'";
}else {
$ssl = "";
}
$sql = "SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = 'General'".$cmp_ssql.$ssl.$dsql.$locations_where;
}
if($tripStatus != "") {
if($tripStatus == "on ride") {
$ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
}else if($tripStatus == "cancelled") {
$ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
}else if($tripStatus == "finished") {
$ssl = " AND iActive = 'Finished' AND eCancelled='No'";
}else {
$ssl = "";
}
$sql = "SELECT iTripId FROM trips WHERE 1".$cmp_ssql.$ssl.$dsql.$locations_where;
}
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tEndDate > '".WEEK_DATE."'";
}
if($time == "month") {
$startDate = date('Y-m')."-01";
$endDate = date('Y-m')."-31";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-01";
$endDate1 = date('Y')."-12-31";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')."";
$endDate2 = date('Y-m-d')."";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}


$mailerCharSet = 'UTF-8';

$mailerHost       = "mail.example.com"; // SMTP server example
$mailerSMTPDebug  = 0;                     // enables SMTP debug information (for testing)
$mailerSMTPAuth   = true;                  // enable SMTP authentication
$mailerPort       = 25;                    // set the SMTP port for the GMAIL server
$mailerUsername   = "username"; // SMTP account username example
$mailerPassword   = "password";        // SMTP account password example

$message = "The mail message was sent with the following mail setting:\r\nSMTP = aspmx.l.google.com\r\nsmtp_port = 25\r\nsendmail_from = YourMail@address.com";
$headers = "From: YOURMAIL@gmail.com";
//mail("Sending@provider.com", "Testing", $message, $headers);
echo "Check your email now....<BR/>";

$NS = 'http://www.w3.org/2005/Atom';
$ATOM_CONTENT_ELEMENTS = array('content','summary','title','subtitle','rights');
$ATOM_SIMPLE_ELEMENTS = array('id','updated','published','draft');
$debug = false;
$depth = 0;
$indent = 2;
$in_content;
$ns_contexts = array();
$ns_decls = array();
$content_ns_decls = array();
$content_ns_contexts = array();
$is_xhtml = false;
$is_html = false;
$is_text = true;
$skipped_div = false;
$FILE = "php://input";
$feed;
$current;

foreach ($data as $key => $value) {
$fCommision = $value['fCommision'];
$fTotalGenerateFare = $value['fTotalGenerateFare'];
$fDeliveryCharge = $value['fDeliveryCharge'];
$fOffersDiscount = $value['fOffersDiscount'];
$fRestaurantPayAmount = $value['fRestaurantPayAmount'];

if($value['iStatusCode'] == '7' || $value['iStatusCode'] == '8') { 
$amounts = $fRestaurantPaidAmount;
} else {
$amounts = $fTotalGenerate - $fComm - $fDelivery- $fOffersDis;
}
$total += $amounts;
}

if($time == "month") {
$startDate = date('Y-m')."-00 00:00:00";
$endDate = date('Y-m')."-31 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-00 00:00:00";
$endDate1 = date('Y')."-12-31 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')." 00:00:00";
$endDate2 = date('Y-m-d')." 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tEndDate > '".WEEK_DATE."'";
}
if($time == "month") {
$startDate = date('Y-m')."-00 00:00:00";
$endDate = date('Y-m')."-31 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-00 00:00:00";
$endDate1 = date('Y')."-12-31 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')." 00:00:00";
$endDate2 = date('Y-m-d')." 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}

}
	#/***********************************************************************************************************/#
	#/***********************************************************************************************************/#    
	#/***********************************************************************************************************/#
	if($iCustomerId == "1")
{
$cmp_ssql = "";
$eSystem  = " AND eSystem = 'General'";
// if(SITE_TYPE =='Demo')
//{
// $cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
// }
$sql = "SELECT * FROM company WHERE eStatus != 'Deleted'  $cmp_ssql order by tRegistrationDate desc";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$ssl = "";
if($status != "" && $status == "active") {
$ssl = " AND rd.eStatus = '".$status."'";
} else if($status != "" && $status == "inactive") {
$ssl = " AND rd.eStatus = '".$status."'";
}
$sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != 'Deleted'".$ssl.$cmp_ssql;

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$ssl = "";
if($status != "" && $status == "active") {
$ssl = " AND rd.eStatus = '".$status."'";
} else if($status != "" && $status == "inactive") {
$ssl = " AND rd.eStatus = '".$status."'";
}
$sql = "SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$sql = "SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName
FROM driver_vehicle dv, register_driver rd, make m, model md, company c
WHERE
dv.iMakeId = m.iMakeId".$cmp_ssql;

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
}
if($status=="all")
$sql = "SELECT * FROM register_user WHERE 1 = 1 ";
else
$sql = "SELECT * FROM register_user WHERE eStatus != 'Deleted'";

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
}

$ssql1 = "AND (vEmail != '' OR vPhone != '')";
if($status=="all")
$sql = "SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ".$ssql1.$cmp_ssql;
else
$sql = "SELECT count(iUserId) FROM register_user WHERE eStatus != 'Deleted'".$ssql1.$cmp_ssql;


/*global $tconfig;
$previosLink = $_SERVER['REQUEST_URI'];
if ((strpos($previosLink, 'ajax') === false) && (strpos($previosLink, 'get') === false)) {
$_SESSION['current_link'] = $previosLink;
}
$sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId'])?$_SESSION['sess_iAdminUserId']:'';
$sess_iGroupId = isset($_SESSION['sess_iGroupId'])?$_SESSION['sess_iGroupId']:'';
if($sess_iAdminUserId == "" && basename($_SERVER['PHP_SELF']) != "index.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."index.php");
exit;
}
//If GroupId == 2
//echo basename($_SERVER['PHP_SELF']); die;
if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php");
exit;
} else if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) != "cab_booking.php" && basename($_SERVER['PHP_SELF']) != "add_booking.php" && basename($_SERVER['PHP_SELF']) != "action_booking.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list.php" && basename($_SERVER['PHP_SELF']) != "get_map_drivers_list.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_rider_by_number.php" && basename($_SERVER['PHP_SELF']) != "change_code.php" && basename($_SERVER['PHP_SELF']) != "get_driver_detail_popup.php" && basename($_SERVER['PHP_SELF']) != "ajax_checkBooking_email.php" && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "map.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list_in_godsview.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "ajax_booking_details.php" && basename($_SERVER['PHP_SELF']) != "checkForRestriction.php" && basename($_SERVER['PHP_SELF']) != "ajax_estimate_by_vehicle_type.php" &&  basename($_SERVER['PHP_SELF']) != "ajax_get_user_balance.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php" );
exit;
}
//If GroupId == 3
if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
exit;
}else if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) != "trip.php" && basename($_SERVER['PHP_SELF']) != "referrer.php" && strpos(basename($_SERVER['PHP_SELF']), 'report') == false && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "referrer_action.php" && basename($_SERVER['PHP_SELF']) != "export_driver_details.php" && basename($_SERVER['PHP_SELF']) != "report_export.php" && basename($_SERVER['PHP_SELF']) != "export_driver_pay_details.php" && basename($_SERVER['PHP_SELF']) != "export_trip_pay_details.php" && basename($_SERVER['PHP_SELF']) != "payment_report.php" && basename($_SERVER['PHP_SELF']) != "wallet_report.php" && basename($_SERVER['PHP_SELF']) != "driver_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_log_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_trip.php" && basename($_SERVER['PHP_SELF']) != "ride_acceptance_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "cancellation_payment_report.php"   && basename($_SERVER['PHP_SELF']) != "allorders.php" && basename($_SERVER['PHP_SELF']) != "driver_payment_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_orders.php" && basename($_SERVER['PHP_SELF']) != "restaurants_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "admin_payment_report.php" && basename($_SERVER['PHP_SELF']) != "order_invoice.php") {
header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
exit;
}*/

if(count($data)>0)
{
$common_member  = "SELECT iDriverId
FROM register_driver
WHERE tRegistrationDate < '".$er_date."'";
$sql = "DELETE FROM driver_vehicle WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM trips WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM log_file WHERE iDriverId IN (".$member.")";
$sql = "DELETE FROM register_driver WHERE tRegistrationDate < '".$date."'";
}


if(count($userObj->locations) > 0){
$locations = implode(', ', $userObj->locations);
$locations_where = " AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
}
if($tripStatus != "") {
if($tripStatus == "on ride") {
$ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
}else if($tripStatus == "cancelled") {
$ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
}else if($tripStatus == "finished") {
$ssl = " AND iActive = 'Finished' AND eCancelled='No'";
}else {
$ssl = "";
}
$sql = "SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = 'General'".$cmp_ssql.$ssl.$dsql.$locations_where;
}
if($tripStatus != "") {
if($tripStatus == "on ride") {
$ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
}else if($tripStatus == "cancelled") {
$ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
}else if($tripStatus == "finished") {
$ssl = " AND iActive = 'Finished' AND eCancelled='No'";
}else {
$ssl = "";
}
$sql = "SELECT iTripId FROM trips WHERE 1".$cmp_ssql.$ssl.$dsql.$locations_where;
}
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tEndDate > '".WEEK_DATE."'";
}
if($time == "month") {
$startDate = date('Y-m')."-01";
$endDate = date('Y-m')."-31";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-01";
$endDate1 = date('Y')."-12-31";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')."";
$endDate2 = date('Y-m-d')."";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}


$mailerCharSet = 'UTF-8';

$mailerHost       = "mail.example.com"; // SMTP server example
$mailerSMTPDebug  = 0;                     // enables SMTP debug information (for testing)
$mailerSMTPAuth   = true;                  // enable SMTP authentication
$mailerPort       = 25;                    // set the SMTP port for the GMAIL server
$mailerUsername   = "username"; // SMTP account username example
$mailerPassword   = "password";        // SMTP account password example

$message = "The mail message was sent with the following mail setting:\r\nSMTP = aspmx.l.google.com\r\nsmtp_port = 25\r\nsendmail_from = YourMail@address.com";
$headers = "From: YOURMAIL@gmail.com";
//mail("Sending@provider.com", "Testing", $message, $headers);
echo "Check your email now....<BR/>";

$NS = 'http://www.w3.org/2005/Atom';
$ATOM_CONTENT_ELEMENTS = array('content','summary','title','subtitle','rights');
$ATOM_SIMPLE_ELEMENTS = array('id','updated','published','draft');
$debug = false;
$depth = 0;
$indent = 2;
$in_content;
$ns_contexts = array();
$ns_decls = array();
$content_ns_decls = array();
$content_ns_contexts = array();
$is_xhtml = false;
$is_html = false;
$is_text = true;
$skipped_div = false;
$FILE = "php://input";
$feed;
$current;

foreach ($data as $key => $value) {
$fCommision = $value['fCommision'];
$fTotalGenerateFare = $value['fTotalGenerateFare'];
$fDeliveryCharge = $value['fDeliveryCharge'];
$fOffersDiscount = $value['fOffersDiscount'];
$fRestaurantPayAmount = $value['fRestaurantPayAmount'];

if($value['iStatusCode'] == '7' || $value['iStatusCode'] == '8') { 
$amounts = $fRestaurantPaidAmount;
} else {
$amounts = $fTotalGenerate - $fComm - $fDelivery- $fOffersDis;
}
$total += $amounts;
}

if($time == "month") {
$startDate = date('Y-m')."-00 00:00:00";
$endDate = date('Y-m')."-31 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-00 00:00:00";
$endDate1 = date('Y')."-12-31 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')." 00:00:00";
$endDate2 = date('Y-m-d')." 23:59:59";
$ssl = " AND rd.tRegistrationDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}
if(SITE_TYPE =='Demo'){
$cmp_ssql = " And tEndDate > '".WEEK_DATE."'";
}
if($time == "month") {
$startDate = date('Y-m')."-00 00:00:00";
$endDate = date('Y-m')."-31 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate."' AND '".$endDate."'";
}else if($time == "year") {
$startDate1 = date('Y')."-00-00 00:00:00";
$endDate1 = date('Y')."-12-31 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate1."' AND '".$endDate1."'";
}else {
$startDate2 = date('Y-m-d')." 00:00:00";
$endDate2 = date('Y-m-d')." 23:59:59";
$ssl = " AND tTripRequestDate BETWEEN '".$startDate2."' AND '".$endDate2."'";
}

}
#/***********************************************************************************************************/#
?>