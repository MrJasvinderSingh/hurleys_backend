<?php
include_once('../common.php');
include_once('../generalFunctions.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$default_lang = $generalobj->get_default_lang();
global $generalobj; global $obj;
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';

$sql = "SELECT iDriverId,vEmail,iCompanyId, CONCAT(vName,' ',vLastName) AS FULLNAME,vLatitude,vLongitude,vServiceLoc,vAvailability,vTripStatus,tLastOnline, vImage, vCode, vPhone, tLocationUpdateDate FROM register_driver WHERE iDriverId =  $iDriverId";
$result = $obj->MySQLSelect($sql);


        $flaGEnable = 0;
	$break = 0;
        
        $vTripStatus = $result[0]['vTripStatus'];
	$orders = GetCountAllOrdersDriver($result[0]['iDriverId']);
	$driverTime = GetDriverStatusIFonGoingWithOrders($result[0]['iDriverId']);
	if(empty($driverTime)) {
		$driverTime = $result[0]['tLastOnline'];
	}
	$status = '';
	if($vTripStatus == 'Active' && !empty($orders)){
		$status = 'AS';
	} else if($vTripStatus == 'Finished' && !empty($orders)) {
		$status = 'EN';
	} else if($vTripStatus == 'On Going Trip' && !empty($orders)) {
		$status = 'EN';
	} else if(empty($orders) && ($result[0]['vAvailability'] == 'Available') ) {
                $status = 'RY';
                $flaGEnable =1;
	} else if(($vTripStatus == 'NONE') && ($result[0]['vAvailability'] == 'Available')){
		$status = 'RY';
                $flaGEnable =1;
	} else {
		$status = 'BK';
		$break = 1;
	}
        
        
        $offlinebutton = '&nbsp;';
if($flaGEnable == 1)
{
   $offlinebutton= '<button onclick="makedriveroffline('.$result[0]['iDriverId'].');" value="'.$result[0]['iDriverId'].'" class="order_button_oc">Break On</button>'; 
}

$DriverrId = $result[0]['iDriverId'];
$CurrentDate = date('Y-m-d');
$SqlTime = "SELECT `dLoginDateTime` FROM `driver_log_report` WHERE `iDriverId` = '$DriverrId' AND DATE(`dLoginDateTime`) = '$CurrentDate' ORDER BY `iDriverLogId` ASC limit 1;";
$resultTime = $obj->MySQLSelect($SqlTime);

$DriverStartTime = date('H:i a', strtotime($resultTime[0]['dLoginDateTime']));

$ERYT  = 0;
$GetLastOrderInformation = "SELECT `tTripRequestDate`,`tStartDate`,`tEndDate`,`iActive` FROM `trips` WHERE `iDriverId` = '$DriverrId' AND DATE(`tTripRequestDate`) = '$CurrentDate' ORDER BY `iTripId` DESC LIMIT 0,1 ";
$LOI_Result = $obj->MySQLSelect($GetLastOrderInformation);
if(count($LOI_Result) > 0)
{
$LOI_Status = $LOI_Result[0]['iActive'];
    if($LOI_Status == "Active")
    {
        $ETATime = GetTimeDiffInMinutes($LOI_Result[0]['tTripRequestDate']); 
        $ERYT =  date('H:i', strtotime('+'.$ETATime.' minutes', strtotime(date('Y-m-d H:i:s'))));
        
    }
    elseif($LOI_Status == "On Going Trip")
    {
        $ETATime = GetTimeDiffInMinutes($LOI_Result[0]['tStartDate']); 
        $ERYT =  date('H:i', strtotime('+'.$ETATime.' minutes', strtotime(date('Y-m-d H:i:s'))));
        
    }
    else
    {
        $ETATime = GetTimeDiffInMinutes($LOI_Result[0]['tEndDate']); 
        $ERYT =  date('H:i', strtotime('+'.$ETATime.' minutes', strtotime(date('Y-m-d H:i:s'))));
    }
}
 else {
    $ERYT = date('H:i', strtotime($resultTime[0]['dLoginDateTime']));
}

//$ADTq = "SELECT (AVG (TIMESTAMPDIFF ( SECOND, `tStartDate`,`tEndDate`))) as adt FROM `trips` WHERE `iDriverId` = '$DriverrId' AND DATE(`tTripRequestDate`) = '$CurrentDate';";
$ADTq = "SELECT (AVG (TIMESTAMPDIFF ( SECOND, `tOrderRequestDate`,`dDeliveryDate`))) as adt FROM `orders` WHERE  `iDriverId` = '$DriverrId' AND DATE(`tOrderRequestDate`) = '$CurrentDate'  AND iStatusCode = 6;";
$ADT_Result = $obj->MySQLSelect($ADTq);
$Adtvalue = 0;

		if (!empty($ADT_Result[0]['adt'])) {
			$adt = $ADT_Result[0]['adt'];
			$Adtvalue =  round(($adt/60));
		}
?>

<!--    <div class="clearfix">
        <div class="col-md-12 text-center"><strong style="padding: 10px 0;text-align:center;width: 100%; text-decoration:underline;">DRIVER</strong></div>
    </div>-->
<div class="clearfix">
    <div class="col-md-12"><?php echo $result[0]['FULLNAME']; ?></div>
    <div class="clearfix">
        <div class="col-md-5">Phone</div>
        <div class="col-md-7 text-left"> <?php echo CCFormatPhoneNumber($result[0]['vPhone']); ?></div>
    </div>
    <div class="clearfix">
        <div class="col-md-5">Status</div> 
        <div class="col-md-3 text-left"><?php echo $status; ?></div>
        <div class="col-md-4 text-right"> <?php echo $DriverStartTime; ?></div>  
    </div>
     <div class="col-md-12">ERYT <?php echo $ERYT; ?></div>
</div>


<?php   

 $DayStart = date('Y-m-d') . ' 00:00:01';
        $DayEnd = date('Y-m-d H:i:s');
$sql = "SELECT  iOrderId,vOrderNo,iStatusCode,deliverytime FROM orders where iStatusCode IN (3,4,5) AND iDriverId = '" . $iDriverId . "' AND orders.tOrderRequestDate BETWEEN '$DayStart' AND '$DayEnd';";
    $returnArrData = $obj->MySQLSelect($sql); 
$OrdersCount = count($returnArrData);
?>
<div class="row">
    <div class="col-md-12"><div class="col-md-12 text-center"><strong style="padding: 5px 0 5px 0;text-align:center;width: 100%; text-decoration:underline; display: inline-block;">Order Assigned : <?php echo $OrdersCount; ?></strong></div></div>
</div>
<?php if($OrdersCount > 0)
{
    foreach ($returnArrData as $dataorder):
  ?>

<div class="row">
    <div class="col-md-12">
        <div class="col-md-4 tdoperationcls text-left" attr="<?php echo $dataorder['iOrderId']; ?>"> <span style="text-decoration: underline; cursor: pointer;"><?php echo $dataorder['vOrderNo']; ?></span></div>
    <div class="col-md-4 text-center"><?php echo $dataorder['iStatusCode'] == "4" ? "Accepted" : ( $dataorder['iStatusCode'] == "5" ? "Picked Up" : " "); ?></div>
    <div class="col-md-4 text-right"><?php echo date('H:i a',strtotime($dataorder['deliverytime'])); ?></div>
    </div>
</div>

<?php  endforeach; }  ?>

<?php 
$vConvertFromDate = date('Y-m-d'). " 00:00:01";
$vConvertToDate = date('Y-m-d H:i:s');
$UserType = "Driver";
$vTimeZone = 'America/New_York';
$DriverAllValues = OrderTotalEarningForChowcallDriver($iDriverId, $vConvertFromDate, $vConvertToDate, $UserType, $vTimeZone);
            $totalEarning = $DriverAllValues['totalEarning'];
            
            ?>
<div class="row">
    <div class="col-md-12"><div class="col-md-12 text-center"><strong style="padding: 5px 0 5px 0;text-align:center;width: 100%; text-decoration:underline; display: inline-block;">Performance</strong></div></div>
</div>
<div class="row">
    <div class="col-md-12"><div class="col-md-6 text-left">ADT</div> <div class="col-md-6 text-left"><?php echo $Adtvalue; ?></div></div>
    <div class="col-md-12"><div class="col-md-6 text-left">Earnings</div> <div class="col-md-6 text-left"><?php echo $totalEarning; ?></div></div>
</div>
<div class="row">
    <div class="col-md-12"><div class="col-md-12 text-center" style="padding-bottom: 10px;"><?php echo $offlinebutton; ?></div></div>
</div>


