<?php
include_once('../common.php');
include_once('../generalFunctions.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$script = 'DriverPayout';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
//$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';


$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);

// Start Search Parameters
$OrderstartDate = date('Y-m-d', strtotime('sunday this week -1 week'));
$OrderendDate = date('Y-m-d', strtotime('saturday this week'));


$ssql = '';
$ssql1 = '';

   

if ($startDate != '') {
    $OrderstartDate = $startDate;
}
if ($endDate != '') {
    $OrderendDate = $endDate;
}
$ssql .= " AND Date(`tOrderRequestDate`) >='" . $OrderstartDate . "'";
$ssql .= " AND Date(`tOrderRequestDate`) <='" . $OrderendDate . "'";
$result = array();

 if (($action == 'search')&&($searchDriver != '')) {
        $ssql1 = " AND iDriverId ='" . $searchDriver . "'";
        
        $Sdriv = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE eStatus != 'Deleted' $ssql1 order by vName";
$Sdriver = $obj->MySQLSelect($Sdriv);
        
        
        $OrderQuery = "SELECT COUNT(`iOrderId`) AS totalorders, SUM(`fpretip`) as totalpretip,SUM(`posttip`) AS totalpostip   FROM `orders` WHERE `iStatusCode` = 6 $ssql $ssql1;";
    $OrderDatas = $obj->MySQLSelect($OrderQuery);
    if ( (count($OrderDatas) > 0) && ($OrderDatas[0]['totalorders'] != 0)) {
        $tip = $OrderDatas[0]['totalpretip'] + $OrderDatas[0]['totalpostip'];
        $reimbursement = calculateDriverReimbursementOfDriverwithrange ($OrderstartDate, $OrderendDate, $searchDriver);
        $bonus = calculateTotalDriverRatingBonus ($OrderstartDate, $OrderendDate, $searchDriver);
        $totalEarning = $tip + $reimbursement + $bonus;
        $adtq = "SELECT ROUND(AVG (TIMESTAMPDIFF ( SECOND, `tTripRequestDate`,`tEndDate`))) as adt FROM `trips` where `iDriverId` = '".$Sdriver[0]['iDriverId']."' AND `tTripRequestDate` BETWEEN '".$OrderstartDate." 00:00:01' AND '".$OrderendDate." 23:59:59';"; 
        $Adtdata = $obj->MySQLSelect($adtq);
       
        $AdtValue = 0;
        if(count($Adtdata) != 0) { $AdtValue = round($Adtdata[0]['adt']/60); }
        $result[] = array(
            'name' => $Sdriver[0]['driverName'],
            'orders' => $OrderDatas[0]['totalorders'],
            'tip' => $tip,
            'rating' =>GetAverageRatingOfDriver($OrderstartDate, $OrderendDate, $searchDriver),
            'reimbursement'=>$reimbursement,
            'bonus'=>$bonus,
            'ttlearning'=>$totalEarning,
            'adt'=> $AdtValue
        );
    }
    
//    else {
//        $result[] = array(
//            'name' => $Sdriver[0]['driverName'],
//            'orders' => 0,
//            'tip' => 0,
//            'rating' => 0,
//        );
//    }
        
    }
    else
    {
foreach ($db_drivers as $driver):
$OrderQuery = "SELECT COUNT(`iOrderId`) AS totalorders, SUM(`fpretip`) as totalpretip,SUM(`posttip`) AS totalpostip   FROM `orders` WHERE `iStatusCode` = 6 $ssql  AND `iDriverId` = '$driver[iDriverId]';";
    $OrderDatas = $obj->MySQLSelect($OrderQuery);
    
    
    if ( (count($OrderDatas) > 0) && ($OrderDatas[0]['totalorders'] != 0)) {
        
        $reimbursement = calculateDriverReimbursementOfDriverwithrange ($OrderstartDate, $OrderendDate, $driver['iDriverId']);
        $bonus = calculateTotalDriverRatingBonus ($OrderstartDate, $OrderendDate, $driver['iDriverId']);
          $tip = $OrderDatas[0]['totalpretip'] + $OrderDatas[0]['totalpostip'];
           $totalEarning = $tip + $reimbursement + $bonus;
           
           $adtq = "SELECT ROUND(AVG (TIMESTAMPDIFF ( SECOND, `tTripRequestDate`,`tEndDate`))) as adt FROM `trips` where `iDriverId` = '".$driver['iDriverId']."' AND `tTripRequestDate` BETWEEN '".$OrderstartDate." 00:00:01' AND '".$OrderendDate." 23:59:59';"; 
        $Adtdata = $obj->MySQLSelect($adtq);
        
        $AdtValue = 0;
        if(count($Adtdata) != 0) { $AdtValue = round($Adtdata[0]['adt']/60); }
        $result[] = array(
            'name' => $driver['driverName'],
            'orders' => $OrderDatas[0]['totalorders'],
            'tip' => $OrderDatas[0]['totalpretip'] + $OrderDatas[0]['totalpostip'],
            'rating' => GetAverageRatingOfDriver($OrderstartDate, $OrderendDate, $driver['iDriverId']),
            'reimbursement'=>$reimbursement,
            'bonus'=>$bonus,
            'ttlearning'=>$totalEarning,
            'adt'=> $AdtValue
        );
    } 
    
//    else {
//        $result[] = array(
//            'name' => $driver['driverName'],
//            'orders' => 0,
//            'tip' => 0,
//            'rating' => 0,
//        );
//    }
unset($OrderDatas);

endforeach;
    }

//Select dates
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
?>
<!DOCTYPE html>
<html lang="en">

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Driver Payment Report</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta content="" name="keywords" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <? include_once('global_files.php');?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>Hurley's Drivers Payment Report</h2>
                            
                        </div>
                        <div class="col-lg-12 pull-right">
                            <h3>From : <?php echo $OrderstartDate; ?>  To:  <?php echo $OrderendDate; ?></h3>
                        </div>
                    </div>
                    <hr />
<form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
						<div class="Posted-date mytrip-page">
								<input type="hidden" name="action" value="search" />
								<h3>Search by Date...</h3>
								<span>
								<a onClick="return todayDate('dp4','dp5');"><?=$langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
								<a onClick="return yesterdayDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
								<a onClick="return currentweekDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
								<a onClick="return previousweekDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
								<a onClick="return currentmonthDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
								<a onClick="return previousmonthDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
								<a onClick="return currentyearDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
								<a onClick="return previousyearDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
								</span> 
								<span>
								<input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value=""/>
								<input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" />
                    <div class="col-lg-3 select001">
                        <select class="form-control filter-by-text driver_container" name = 'searchDriver' data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>">
                            <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                            <?php foreach ($db_drivers as $dbd) { ?>
                                <option value="<?php echo $dbd['iDriverId']; ?>" <?php
                                if ($searchDriver == $dbd['iDriverId']) {
                                    echo "selected";
                                }
                                ?>><?php echo $generalobjAdmin->clearName($dbd['driverName']); ?></option>
<?php } ?>
                        </select>
                    </div>
<!--  - ( <?php //echo $generalobjAdmin->clearEmail($dbd['vEmail']); ?> )-->
                    <div class="tripBtns001">
                        <b>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'driverpayout.php'"/>
                        <?php if (count($result) > 0) { ?>
                                <button type="button" onClick="exportlist()" class="export-btn001" >Export</button></b>
<?php } ?>
                    </div>
                    </span>
                    <div class="tripBtns001">
                    </div>
                </div>
                </form>


                <table class="table table-striped table-bordered table-hover" id="dataTables-example123" >
                    <thead>
                        <tr>
                            <th>Name</th>    
                            <th>Number of Orders</th>  
                            <th>Tip</th> 
                            <th>Reimbursement</th> 
                            <th>Bonus</th>
                            <th>TTL Earnings</th>
                            <th>DDT(Min)</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($result) > 0) {
                            foreach ($result as $value):
                                ?>
                                <tr>
                                    <th><?php echo $value['name']; ?></th>    
                                    <th><?php echo $value['orders']; ?></th>  
                                    <th><?php echo $value['tip']; ?></th> 
                                    <th><?php echo $value['reimbursement']; ?></th> 
                                    <th><?php echo $value['bonus']; ?></th> 
                                    <th><?php echo $value['ttlearning']; ?></th> 
                                    <th><?php echo $value['adt']; ?></th> 
                                </tr>

    <?php endforeach;
} else { ?>
                            <tr class="gradeA">
                                <td colspan="13" style="text-align:center;"> No Results Found.</td>
                            </tr>
<?php } ?>
                    </tbody>
                </table>


            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<? include_once('footer.php');?>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
<link rel="stylesheet" href="css/select2/select2.min.css" />
<script src="js/plugins/select2.min.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script>
$('#dp4').datepicker().on('changeDate', function (ev) {
                                                var endDate = $('#dp5').val();
                                                if (ev.date.valueOf() < endDate.valueOf()) {
                                                    $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                                                } else {
                                                    $('#alert').hide();
                                                    var startDate = new Date(ev.date);
                                                    $('#startDate').text($('#dp4').data('date'));
                                                }
                                                $('#dp4').datepicker('hide');
                                            });
                                    $('#dp5').datepicker()
                                            .on('changeDate', function (ev) {
                                                var startDate = $('#dp4').val();
                                                if (ev.date.valueOf() < startDate.valueOf()) {
                                                    $('#alert').show().find('strong').text('The end date can not be less then the start date');
                                                } else {
                                                    $('#alert').hide();
                                                    var endDate = new Date(ev.date);
                                                    $('#endDate').text($('#dp5').data('date'));
                                                }
                                                $('#dp5').datepicker('hide');
                                            });

                                    $(document).ready(function () {
                                        $("#dp5").click(function () {
                                            $('#dp5').datepicker('show');
                                            $('#dp4').datepicker('hide');
                                        });

                                        $("#dp4").click(function () {
                                            $('#dp4').datepicker('show');
                                            $('#dp5').datepicker('hide');
                                        });

                                        if ('<?= $startDate ?>' != '') {
                                            $("#dp4").val('<?= $startDate ?>');
                                            $("#dp4").datepicker('update', '<?= $startDate ?>');
                                        }
                                        if ('<?= $endDate ?>' != '') {
                                            $("#dp5").datepicker('update', '<?= $endDate; ?>');
                                            $("#dp5").val('<?= $endDate; ?>');
                                        }
                                    });

                                    function setRideStatus(actionStatus) {
                                        window.location.href = "trip.php?type=" + actionStatus;
                                    }
                                    function todayDate()
                                    {
                                        $("#dp4").val('<?= $Today; ?>');
                                        $("#dp5").val('<?= $Today; ?>');
                                    }
                                    function reset() {
                                        location.reload();

                                    }
                                    function yesterdayDate()
                                    {
                                        $("#dp4").val('<?= $Yesterday; ?>');
                                        $("#dp4").datepicker('update', '<?= $Yesterday; ?>');
                                        $("#dp5").datepicker('update', '<?= $Yesterday; ?>');
                                        $("#dp4").change();
                                        $("#dp5").change();
                                        $("#dp5").val('<?= $Yesterday; ?>');
                                    }
                                    function currentweekDate(dt, df)
                                    {
                                        $("#dp4").val('<?= $monday; ?>');
                                        $("#dp4").datepicker('update', '<?= $monday; ?>');
                                        $("#dp5").datepicker('update', '<?= $sunday; ?>');
                                        $("#dp5").val('<?= $sunday; ?>');
                                    }
                                    function previousweekDate(dt, df)
                                    {
                                        $("#dp4").val('<?= $Pmonday; ?>');
                                        $("#dp4").datepicker('update', '<?= $Pmonday; ?>');
                                        $("#dp5").datepicker('update', '<?= $Psunday; ?>');
                                        $("#dp5").val('<?= $Psunday; ?>');
                                    }
                                    function currentmonthDate(dt, df)
                                    {
                                        $("#dp4").val('<?= $currmonthFDate; ?>');
                                        $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
                                        $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
                                        $("#dp5").val('<?= $currmonthTDate; ?>');
                                    }
                                    function previousmonthDate(dt, df)
                                    {
                                        $("#dp4").val('<?= $prevmonthFDate; ?>');
                                        $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
                                        $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
                                        $("#dp5").val('<?= $prevmonthTDate; ?>');
                                    }
                                    function currentyearDate(dt, df)
                                    {
                                        $("#dp4").val('<?= $curryearFDate; ?>');
                                        $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
                                        $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
                                        $("#dp5").val('<?= $curryearTDate; ?>');
                                    }
                                    function previousyearDate(dt, df)
                                    {
                                        $("#dp4").val('<?= $prevyearFDate; ?>');
                                        $("#dp4").datepicker('update', '<?= $prevyearFDate; ?>');
                                        $("#dp5").datepicker('update', '<?= $prevyearTDate; ?>');
                                        $("#dp5").val('<?= $prevyearTDate; ?>');
                                    }

                                    function exportlist() {
                                        $("#actionpay").val("export");
                                        $("#frmsearch").attr("action", "driverpayoutexport.php");
                                        document.frmsearch.submit();
                                    }

                                    $("#Search").on('click', function () {
                                         $("#frmsearch").attr("action", "driverpayout.php");
                                        if ($("#dp5").val() < $("#dp4").val()) {
                                            alert("From date should be lesser than To date.")
                                            return false;
                                        } else {
                                            var action = "<?php echo $_SERVER['PHP_SELF']; ?>";
                                            var formValus = $("#frmsearch").serialize();
                                            window.location.href =  action + "?" + formValus;
                                        }
                                    });
                                    $(function () {
                                        $("select.filter-by-text").each(function () {
                                            $(this).select2({
                                                placeholder: $(this).attr('data-text'),
                                                allowClear: true
                                            }); //theme: 'classic'
                                        });
                                    });
                                   
</script>
</body>
<!-- END BODY-->
</html>
