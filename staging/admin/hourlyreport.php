<?php
include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');
date_default_timezone_set('America/New_York');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$generalobjAdmin->check_member_login();
$default_lang 	= $generalobj->get_default_lang();

$script =  "HourlyReport";


// Start Search Parameters
$ssql='';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : date('Y-m-d');
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : date('Y-m-d');

$Today=Date('Y-m-d');
$tdate=date("d")-1;
$mdate=date("d");
$Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));

$curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
$curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
$prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")-1));
$prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")-1));

$currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$tdate,date("Y")));
$currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m")+1,date("d")-$mdate,date("Y")));
$prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d")-$tdate,date("Y")));
$prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$mdate,date("Y")));

$monday = date( 'Y-m-d', strtotime( 'sunday this week -1 week' ) );
$sunday = date( 'Y-m-d', strtotime( 'saturday this week' ) );

$Pmonday = date( 'Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date( 'Y-m-d', strtotime('saturday this week -1 week'));



$starttime = date('Y-m-d').' 07:00:00';
$endtime = date('Y-m-d').' 24:00:00';

 $responses = array();

 $AllOrderCount = 0;
 
 $TotalNewCustomers = 0;
 
 $grandtotalNewcustomer = 0;
 //"SELECT count(DISTINCT(`register_user`.`iUserId`)) as totalc  FROM `register_user` JOIN `orders` ON `orders`.`iUserId` = `register_user`.`iUserId` WHERE DATE(`register_user`.`tRegistrationDate`) BETWEEN '2018-11-01' AND '$endDate' AND `orders`.`iStatusCode` = 6 HAVING COUNT(`orders`.`iOrderId`) >= 1;"
 
   $GTotalNewCustQ = "SELECT count(DISTINCT(`iUserId`)) as totalc  FROM `orders` WHERE DATE(`tOrderRequestDate`) BETWEEN '$startDate' AND '$endDate' AND `iStatusCode` = 6";
 $GTotalNewCustData = $obj->MySQLSelect($GTotalNewCustQ);
 
 if(count($GTotalNewCustData) > 0)
 {
     $grandtotalNewcustomer = $GTotalNewCustData[0]['totalc'];
 }
 
 $TotalNewCustQ = "SELECT count(DISTINCT(`register_user`.`iUserId`)) as totalc  FROM `register_user` JOIN `orders` ON `orders`.`iUserId` = `register_user`.`iUserId` WHERE DATE(`register_user`.`tRegistrationDate`) BETWEEN '$startDate' AND '$endDate' AND `orders`.`iStatusCode` = 6 HAVING COUNT(`orders`.`iOrderId`) >= 1;";
 $TotalNewCustData = $obj->MySQLSelect($TotalNewCustQ);
 
 
 
 if(count($TotalNewCustData) > 0)
 {
     
     $CalculatedValNewcus = $grandtotalNewcustomer - $TotalNewCustData[0]['totalc'];
     
     $TotalNewCustomers = round(($TotalNewCustData[0]['totalc'] / $CalculatedValNewcus) * 100 , 2);
 }
 

 
 $GranDtotal = 0;
 $Totalquery = "SELECT COUNT(`iOrderId`) as tcount, SUM(`fNetTotal`) as grandtotal FROM `orders` WHERE `iStatusCode` = 6 AND DATE(`tOrderRequestDate`) BETWEEN '$startDate' AND '$endDate';";
 $TotalData = $obj->MySQLSelect($Totalquery);
 
 

 
 if(count($TotalData) > 0)
 {
     $AllOrderCount = $TotalData[0]['tcount'];
     $GranDtotal = round($TotalData[0]['grandtotal'] , 2);
 }
 
 $TotalPUT = 0;
 $TotalDDT = 0;
 $TotalTDT = 0;

   $TDTTotalq = "SELECT (AVG (TIMESTAMPDIFF ( SECOND, `orders`.`tOrderRequestDate`, `trips`.`tStartDate`))) as put, (AVG (TIMESTAMPDIFF ( SECOND, `trips`.`tStartDate`, `trips`.`tEndDate`))) as ddt ,  (AVG (TIMESTAMPDIFF ( SECOND, `orders`.`tOrderRequestDate`, `trips`.`tEndDate`))) as tdt FROM `orders` JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` WHERE `orders`.`iStatusCode` = 6 AND DATE(`orders`.`tOrderRequestDate`) BETWEEN '$startDate' AND '$endDate' ;";
   $TDTTotalData = $obj->MySQLSelect($TDTTotalq);
   
   
   if (!empty($TDTTotalData[0]['tdt'])) {
			$tdt= $TDTTotalData[0]['tdt'];
			$TotalTDT =  round(($tdt/60));
}

 if (!empty($TDTTotalData[0]['put'])) {
                                    $put = $TDTTotalData[0]['put'];
                                    $TotalPUT =  round(($put/60));
                            }
    if (!empty($TDTTotalData[0]['ddt'])) {
			$ddt = $TDTTotalData[0]['ddt'];
			$TotalDDT =  round(($ddt/60));
}
/*
 * ********************************GrandTotalDrivers*********************************************
 */
$GrandTotalDrivers  = 0;
//For Unique Drivers $GranddriverSql = "SELECT SUM(T1.`TDrivers`) AS totalDrivers FROM  (SELECT count(DISTINCT(`iDriverId`)) AS TDrivers, DATE(`logtime`) AS DATIME FROM `driverslocationlogs` WHERE DATE(`logtime`) BETWEEN '$startDate' AND '$endDate' AND `eStatus` = 'Available' GROUP BY  DATE(`logtime`)) AS T1;"; 

// For Unique Time
$GranddriverSql = "SELECT COUNT(*)  AS totalminutes FROM `driverslocationlogs` WHERE DATE(`logtime`) BETWEEN '$startDate' AND '$endDate' AND time(`logtime`) BETWEEN '07:00:00' AND '24:00:00' AND `eStatus` = 'Available';";
                             $GrandDriverDatas = $obj->MySQLSelect($GranddriverSql);
                            
                             
                             $totalDrivers = 0;
                             
                             if(count($GrandDriverDatas) > 0)
                             {
                                 $GrandTotalDrivers = round(((2 * $GrandDriverDatas[0]['totalminutes'])/60) , 2);
                             }
  /*
 * ********************************GrandTotalDrivers Close*********************************************
 */                           
                             
 while (strtotime($starttime) < strtotime($endtime)) {
               // echo date('Y-m-d H:i:s') . $starttime ."\r\n\r\n";
               // echo date('Y-m-d h:I:S') . $endtime ."\r\n\r\n";
                 $querystarttime  = date ("H:i",strtotime($starttime)).':00';
                $queryendtime  = date ("H:i", strtotime("+1 hour", strtotime($starttime))).':00';
                 

                
                $startDateValue = date ("Y-m-d",strtotime($starttime));
                 $Orderssql = "SELECT `iOrderId`, `vOrderNo`,`tOrderRequestDate` `fSubTotal`,`fOffersDiscount`,`fDeliveryCharge`,`fDeliveryCharge`,`fTax`, `fpretip`,`posttip`,`fDiscount`, `fNetTotal`,`fTotalGenerateFare`,`dDate`,`dDeliveryDate` FROM `orders` WHERE `iStatusCode` = 6 AND DATE(`tOrderRequestDate`) BETWEEN '$startDate' AND '$endDate'  AND TIME(`tOrderRequestDate`) BETWEEN '$querystarttime' AND '$queryendtime';";
                
                $ordersDatas = $obj->MySQLSelect($Orderssql);
                
                
                
                // Total Driver Count
                
                //$driverSql = "SELECT count(DISTINCT(`iDriverId`)) AS totalDrivers FROM `driver_log_report` WHERE `dLoginDateTime` BETWEEN '$startDate $querystarttime' AND '$endDate $queryendtime' OR `dLogoutDateTime` BETWEEN '$startDate $querystarttime' AND '$endDate $queryendtime';"; 
  
                        //$driverSql = "SELECT count(DISTINCT(`iDriverId`)) AS totalDrivers FROM `driver_log_report` WHERE `dLoginDateTime` < '$startDate $querystarttime' AND `dLogoutDateTime` > '$endDate $queryendtime' OR `dLogoutDateTime` = '0000-00-00 00:00:00' ;";
                        
                        // $driverSql = "SELECT count(DISTINCT(`iDriverId`)) AS totalDrivers FROM `driverslocationlogs` WHERE  `logtime` < '$startDate $querystarttime' AND `logtime` > '$endDate $queryendtime' ;";
                         
                         //For Unique Drivers $driverSql = "SELECT SUM(T1.`TDrivers`) AS totalDrivers FROM  (SELECT count(DISTINCT(`iDriverId`)) AS TDrivers, DATE(`logtime`) AS DATIME  FROM `driverslocationlogs` WHERE DATE(`logtime`) BETWEEN '$startDate' AND '$endDate' AND TIME(`logtime`)  BETWEEN '$querystarttime' AND '$queryendtime' AND `eStatus` = 'Available' GROUP BY  DATE(`logtime`)) AS T1;"; 
                
                // FOr Driver time
                
                $driverSql = "SELECT COUNT(*)  AS totalminutes FROM `driverslocationlogs` WHERE DATE(`logtime`) BETWEEN '$startDate' AND '$endDate' AND time(`logtime`) BETWEEN '$querystarttime' AND '$queryendtime' AND `eStatus` = 'Available';";
                             $DriverDatas = $obj->MySQLSelect($driverSql);
                            
                             
                             $totalDrivers = 0;
                             
                             if(count($DriverDatas) > 0)
                             {
                                 $totalDrivers =  round(((2 * $DriverDatas[0]['totalminutes'])/60) , 2);
                                 
                             }
                //Total Driver Count Close
                
                             
                          
                 $NewCustomers = 0;

 
 
 $NewCustQ = "SELECT count(DISTINCT(`register_user`.`iUserId`)) as totalc FROM `register_user` JOIN `orders` ON `orders`.`iUserId` = `register_user`.`iUserId` WHERE DATE(`register_user`.`tRegistrationDate`) BETWEEN '$startDate' AND '$endDate' AND time(`register_user`.`tRegistrationDate`) BETWEEN '$querystarttime' AND '$queryendtime' AND `orders`.`iStatusCode` = 6  HAVING COUNT(`orders`.`iOrderId`) >= 1;";
 $NewCustData = $obj->MySQLSelect($NewCustQ);
 
 if(count($NewCustData) > 0)
 {
     $CalTTlVal = $grandtotalNewcustomer - $NewCustData[0]['totalc'];
     $NewCustomers = round(($NewCustData[0]['totalc'] / $CalTTlVal) * 100 , 2);
 }
            
                    if(count($ordersDatas) > 0)
                    {
                        
                        $totalAmount =0;
                        $alliorderid = '';
                        
                        foreach($ordersDatas as $ordersData):
                            $alliorderid .= $ordersData['iOrderId'].',';
                            $totalAmount = $totalAmount + $ordersData['fNetTotal'];
                        endforeach;
                       
                        
                                     
                                     
                            $alliorderid = substr($alliorderid, 0, -1);
                       // echo $alliorderid; exit;
                            
                            //PUT Caculation
                            $totalDT = 0;
                            $putvalue = 0;
                            $ddtvalue = 0;
                            
                              $totalDTq = "SELECT (AVG (TIMESTAMPDIFF ( SECOND, `orders`.`tOrderRequestDate`, `trips`.`tStartDate`))) as put, (AVG (TIMESTAMPDIFF ( SECOND, `trips`.`tStartDate`, `trips`.`tEndDate`))) as ddt ,  (AVG (TIMESTAMPDIFF ( SECOND, `orders`.`tOrderRequestDate`, `trips`.`tEndDate`))) as tdt FROM `orders` JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` WHERE `orders`.`iOrderId` IN ($alliorderid);";
    $totalDTData = $obj->MySQLSelect($totalDTq);
                            
                            
                            if (!empty($totalDTData[0]['put'])) {
                                    $put = $totalDTData[0]['put'];
                                    $putvalue =  round(($put/60));
                            }
                            if (!empty($totalDTData[0]['ddt'])) {
                                    $ddt = $totalDTData[0]['ddt'];
                                    $ddtvalue =  round(($ddt/60));
                            }
                            if (!empty($totalDTData[0]['tdt'])) {
                                    $tdt = $totalDTData[0]['tdt'];
                                    $totalDT =  round(($tdt/60));
                            }
                            
                            
               
                        $tottalOrders = count($ordersDatas);
                        $responses[] = array(
                         'time'=> date('g:i A', strtotime($starttime)) ,
                          'orders'=>$tottalOrders,
                          'customers'=>$NewCustomers,  
                          'sales'=>$totalAmount,
                          'drivers'=> $totalDrivers,
                          'orddrv'=>round(($tottalOrders/$totalDrivers), 2 ),
                          'put'=>$putvalue,  
                          'ddt'=>$ddtvalue,
                          'tdt'=> $totalDT 
                        );
//.' - '.date('d g:i:s A', strtotime($queryendtime))
                    }
                    else {
                         $responses[] = array(
                          'time'=> date('g:i A', strtotime($starttime)),
                          'orders'=>0,
                             'customers'=>0,  
                          'sales'=>0,
                          'drivers'=> $totalDrivers,
                          'orddrv'=>0,
                          'put'=>0,  
                          'ddt'=>0,
                          'tdt'=> 0 
                          );
                    }
                 
                $starttime = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($starttime)));
                unset($ordersDatas);  unset($ordersData); 
                
 }
 $Ttl_Order_DRV = round(($AllOrderCount/$GrandTotalDrivers), 2);
       
?>

<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title><?=$SITE_NAME?> | <?php echo $langage_lbl_admin['LBL_PROCESSING_ORDERS'];?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <?php include_once('global_files.php');?>

    
    <style>
        .hrtable th , .hrtable td { text-align: center;}
        .exportformatdiv  {position: relative; top:  15px;}
        .exportformatdiv label{ position: relative; top: 2px;}
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 " >
    <!-- Main LOading -->
    <!-- MAIN WRAPPER -->
    <div id="wrap">
        <?php include_once('header.php'); ?>
        <?php include_once('left_menu.php'); ?>
        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
	            <div id="add-hide-show-div">
	                <div class="row">
	                    <div class="col-lg-12">
	                        <h2><?php //echo $langage_lbl_admin['LBL_PROCESSING_ORDERS'];?> </h2>
	                         <h2><?php echo $script;?> </h2>
	                    </div>
	                </div>
	                <hr />
	            </div>
                <?php include('valid_msg.php'); ?>
               	<!--  Search Form Start  -->
				<form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
					<div class="Posted-date mytrip-page payment-report">
						<input type="hidden" name="action" value="search" />
						<input type="hidden" name="type" value="<?= $order_type;?>" />
<!--						<h3>Search <?php echo $langage_lbl_admin['LBL_PROCESSING_ORDERS'];?> ...</h3>-->
						<span>
							<a onClick="return todayDate('dp4','dp5');"><?=$langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
							<a onClick="return yesterdayDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
							<a onClick="return currentweekDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
							<a onClick="return previousweekDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
							<a onClick="return currentmonthDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
							<a onClick="return previousmonthDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
							<a onClick="return currentyearDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
							<a onClick="return previousyearDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
						</span> 
						<span>
							<input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default;background-color: #fff" />
							<input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default;background-color: #fff"/>
                                                        <input type="submit" value="Search" class="btn btn-info btn-lg" id="Search" name="Search" title="Search" style="margin:0;"/>
                                               </span> 
                                                         <?php if (count($responses) > 0) { ?>
                                                <span class="exportformatdiv">
                                                    <label> XLS : </label><input type="radio" value="xls" class="btn btn-info btn-lg" class="exportfromat" name="exportfromat" title="Export Format" />
                                                    <label> PDF : </label> <input type="radio" value="pdf" class="btn btn-info btn-lg" class="exportfromat" name="exportfromat" title="Export Format" />
                                <button type="button" onClick="exportlist()" class="btn btn-danger btn-xl" >Export</button>
                                                </span>
                                                          <?php } ?>

						
					</div>
					

				</form> 
				<!-- Search Form End -->
				<div class="table-list">
					<div class="row">
						<div class="col-lg-12">
							<div class="table-responsive">
								<form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
									<table class="table table-striped table-bordered table-hover hrtable" >
										<thead>
                                                                                    <tr>
                                                                                        <th>Time</th>
                                                                                        <th>Order #</th>
                                                                                        <th>%New_Customers</th>
                                                                                        <th>Sales</th>
                                                                                        <!--<th>Drivers</th>
                                                                                        <th>Ord/Drv</th>-->
                                                                                        <th>PUT</th>
                                                                                        <th>DDT</th>
                                                                                        <th>TDT</th>
                                                                                    </tr>
											
											
										</thead>
										<tbody>
											<?php if(!empty($responses)) {


												foreach($responses as $response) { ?>
                                                                                    
													<tr class="gradeA">
														<td><?php echo $response['time']; ?></td>
														<td><?php echo $response['orders']; ?></td>
														<td><?php echo $response['customers']; ?></td>
														<td><?php echo $response['sales']; ?></td>
														<!--<td><?php echo $response['drivers']; ?></td>
														<td><?php echo $response['orddrv']; ?></td>-->
														<td><?php echo $response['put']; ?></td>
														<td><?php echo $response['ddt']; ?></td>
														<td><?php echo $response['tdt']; ?></td>
													</tr>            
	<?php } ?>
													
	<tr>
									<th>Total</th>
									   <th><?php echo $AllOrderCount; ?></th>
										<th><?php echo $TotalNewCustomers; ?></th>
									<th><?php echo $GranDtotal; ?></th>
									<!--<th><?php echo $GrandTotalDrivers; ?></th>
									<th><?php echo $Ttl_Order_DRV; ?></th>-->
									<th><?php echo $TotalPUT; ?></th>
							   <th><?php echo $TotalDDT; ?></th> 
								<th><?php echo $TotalTDT; ?></th>
             </tr>                                                                       
                                                                                                        
                                                                                                        
										<?php	} else { ?>
											<tr class="gradeA">
												<td colspan="8"> No Records Found.</td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
								</form>
								<?php //include('pagination_n.php'); ?>
							</div>
						</div>
					</div>
				</div>
        		<div class="clear"></div>
			</div>
		</div>
	<!--END PAGE CONTENT -->
	</div>
	<!--END MAIN WRAPPER -->
	<form name="pageForm" id="pageForm" action="" method="post" >
		<input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
		<input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
		<input type="hidden" name="method" id="method" value="" >
	</form>

	<? include_once('footer.php');?>
	<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
	<link rel="stylesheet" href="css/select2/select2.min.css" />
	<script src="js/plugins/select2.min.js"></script>
	<script src="../assets/js/jquery-ui.min.js"></script>
	<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
    <script>
			$('#dp4').datepicker()
            .on('changeDate', function (ev) {
                if (ev.date.valueOf() < endDate.valueOf()) {
                    $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                } else {
                    $('#alert').hide();
                    startDate = new Date(ev.date);
                    $('#startDate').text($('#dp4').data('date'));
                }
                $('#dp4').datepicker('hide');
            });
			$('#dp5').datepicker()
            .on('changeDate', function (ev) {
                if (ev.date.valueOf() < startDate.valueOf()) {
                    $('#alert').show().find('strong').text('The end date can not be less then the start date');
                } else {
                    $('#alert').hide();
                    endDate = new Date(ev.date);
                    $('#endDate').text($('#dp5').data('date'));
                }
                $('#dp5').datepicker('hide');
            });
	
         $(document).ready(function () {
			 if('<?=$startDate?>'!=''){
				 $("#dp4").val('<?=$startDate?>');
				 $("#dp4").datepicker('update' , '<?=$startDate?>');
			 }
			 if('<?=$endDate?>'!=''){
				 $("#dp5").datepicker('update' , '<?= $endDate;?>');
				 $("#dp5").val('<?= $endDate;?>');
			 }
			 
         });
		 
		 function setRideStatus(actionStatus) {
			 window.location.href = "trip.php?type="+actionStatus;
		 }
		 function todayDate()
		 {
			 $("#dp4").val('<?= $Today;?>');
			 $("#dp5").val('<?= $Today;?>');
		 }
		 function reset() {
			location.reload();
			
		}	
		 function yesterdayDate()
		 {
			 $("#dp4").val('<?= $Yesterday;?>');
			 $("#dp4").datepicker('update' , '<?= $Yesterday;?>');
			 $("#dp5").datepicker('update' , '<?= $Yesterday;?>');
			 $("#dp4").change();
			 $("#dp5").change();
			 $("#dp5").val('<?= $Yesterday;?>');
		 }
		 function currentweekDate(dt,df)
		 {
			 $("#dp4").val('<?= $monday;?>');
			 $("#dp4").datepicker('update' , '<?= $monday;?>');
			 $("#dp5").datepicker('update' , '<?= $sunday;?>');
			 $("#dp5").val('<?= $sunday;?>');
		 }
		 function previousweekDate(dt,df)
		 {
			 $("#dp4").val('<?= $Pmonday;?>');
			 $("#dp4").datepicker('update' , '<?= $Pmonday;?>');
			 $("#dp5").datepicker('update' , '<?= $Psunday;?>');
			 $("#dp5").val('<?= $Psunday;?>');
		 }
		 function currentmonthDate(dt,df)
		 {
			 $("#dp4").val('<?= $currmonthFDate;?>');
			 $("#dp4").datepicker('update' , '<?= $currmonthFDate;?>');
			 $("#dp5").datepicker('update' , '<?= $currmonthTDate;?>');
			 $("#dp5").val('<?= $currmonthTDate;?>');
		 }
		 function previousmonthDate(dt,df)
		 {
			 $("#dp4").val('<?= $prevmonthFDate;?>');
			 $("#dp4").datepicker('update' , '<?= $prevmonthFDate;?>');
			 $("#dp5").datepicker('update' , '<?= $prevmonthTDate;?>');
			 $("#dp5").val('<?= $prevmonthTDate;?>');
		 }
		 function currentyearDate(dt,df)
		 {
			 $("#dp4").val('<?= $curryearFDate;?>');
			 $("#dp4").datepicker('update' , '<?= $curryearFDate;?>');
			 $("#dp5").datepicker('update' , '<?= $curryearTDate;?>');
			 $("#dp5").val('<?= $curryearTDate;?>');
		 }
		 function previousyearDate(dt,df)
		 {
			 $("#dp4").val('<?= $prevyearFDate;?>');
			 $("#dp4").datepicker('update' , '<?= $prevyearFDate;?>');
			 $("#dp5").datepicker('update' , '<?= $prevyearTDate;?>');
			 $("#dp5").val('<?= $prevyearTDate;?>');
		 }
		$("#Search").on('click', function(){
			 if($("#dp5").val() < $("#dp4").val()){
				 alert("From date should be lesser than To date.")
				 return false;
			 }else {
				var action = $("#_list_form").attr('action');
                var formValus = $("#frmsearch").serialize();
                window.location.href = action+"?"+formValus;
			 }
		});
		$(function () {
		  $("select.filter-by-text").each(function(){
			  $(this).select2({
					placeholder: $(this).attr('data-text'),
					allowClear: true
			  }); //theme: 'classic'
			});
		});
        
        /*$('.delete_form').on('hidden.bs.modal', function () {
			//window.location.reload();
			 //$('.delete_form').reset();
			 $(".modal-body").[0]reset();
		});	*/
    </script>

    <script type="text/javascript">
  function exportlist() {
		$("#actionpay").val("export");
		$("#frmsearch").attr("action", "hourlyreportexport.php");
		$("#frmsearch").attr("target", "_blank");
		document.frmsearch.submit();
		location.reload();
	}
    </script>


</body>
<!-- END BODY-->
</html>

