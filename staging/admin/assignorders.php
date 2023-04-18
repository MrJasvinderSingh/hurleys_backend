<?php
include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$generalobjAdmin->check_member_login();
$default_lang 	= $generalobj->get_default_lang();

$script='Assign Orders';
/*$rdr_ssql = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}
*/

//data for select fields
$sql = "select iCompanyId,vCompany from company WHERE eStatus != 'Deleted' $rdr_ssql";
$db_company = $obj->MySQLSelect($sql);

$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName from register_driver WHERE eStatus != 'Deleted' $rdr_ssql";
$db_drivers = $obj->MySQLSelect($sql);

$sql = "select iUserId,CONCAT(vName,' ',vLastName) AS riderName from register_user WHERE eStatus != 'Deleted' $rdr_ssql";
$db_rider = $obj->MySQLSelect($sql);
//data for select fields

$order_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$processing_status_array = array('2','4');
$all_status_array = array('1','2','4','5','6','7','8','9','11','12');

if($_REQUEST['iStatusCode'] != ''){
    $all_status_array = array($_REQUEST['iStatusCode']);
}
$iStatusCode = '('.implode(',',$processing_status_array).')';


//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$promocode = isset($_REQUEST['promocode']) ? $_REQUEST['promocode'] : '';

$ord = ' ORDER BY o.iOrderId DESC';
if($sortby == 1) {

    if($order == 0)
        $ord = " ORDER BY o.tOrderRequestDate ASC";
    else
        $ord = " ORDER BY o.tOrderRequestDate DESC";
}

if($sortby == 2){
    if($order == 0)
        $ord = " ORDER BY riderName ASC";
    else
        $ord = " ORDER BY riderName DESC";
}

if($sortby == 3){
    if($order == 0)
        $ord = " ORDER BY c.vCompany ASC";
    else
        $ord = " ORDER BY c.vCompany DESC";
}

if($sortby == 4){
    if($order == 0)
        $ord = " ORDER BY driverName ASC";
    else
        $ord = " ORDER BY driverName DESC";
}

//End Sorting

// Start Search Parameters
$ssql='';
$action = isset($_REQUEST['action']) ? $_REQUEST['action']: '';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id']: '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchServiceType= isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';


if($startDate!=''){
    $ssql.=" AND Date(o.tOrderRequestDate) >='".$startDate."'";
}
if($endDate!=''){
    $ssql.=" AND Date(o.tOrderRequestDate) <='".$endDate."'";
}
if($serachTripNo!=''){
    $ssql.=" AND o.vOrderNo ='".$serachTripNo."'";
}
if($searchCompany!=''){
    $ssql.=" AND c.iCompanyId ='".$searchCompany."'";
}

if($searchRider!=''){
    $ssql.=" AND o.iUserId ='".$searchRider."'";
}

if($searchServiceType != ''){
    $ssql.=" AND sc.iServiceId ='".$searchServiceType."'";
}

$trp_ssql = "";
if(SITE_TYPE =='Demo'){
    $trp_ssql = " And o.tOrderRequestDate > '".WEEK_DATE."'";
}

if(!empty($promocode) && isset($promocode)) {
    $ssql .= " AND o.vCouponCode LIKE '".$promocode."' AND o.iStatusCode=6";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(o.iOrderId) AS Total FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId=o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceId=o.iServiceId WHERE o.iStatusCode IN $iStatusCode $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql);


$total_results = $totalData[0]['Total'];
//total pages we going to have
$total_pages = ceil($total_results / $per_page);
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    //it will telles the current page
    $show_page = $_GET['page'];
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End

$sql = "SELECT o.fSubTotal,o.iServiceid,sc.vServiceName_".$default_lang." as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem,CONCAT('<b>Phone: </b> +',u.vPhoneCode,' ',u.vPhone)  as user_phone,CONCAT('<b>Phone: </b> +',d.vCode,' ',d.vPhone) as driver_phone,CONCAT('<b>Phone: </b> +',c.vCode,' ',c.vPhone) as resturant_phone FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE o.iStatusCode IN $iStatusCode $ssql $trp_ssql $ord LIMIT $start, $per_page";

$DBProcessingOrders = $obj->MySQLSelect($sql);

$endRecord = count($DBProcessingOrders);

$var_filter = "";
foreach ($_REQUEST as $key=>$val) {
    if($key != "tpages" && $key != 'page')
        $var_filter.= "&$key=".stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;
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


if($action == 'assignorder') {
    $assignorder = AssignOrderToDriver($_POST['iOrderId'], $_POST['driverid']);
    //print_r($assignorder);die;
    header("Refresh:0");
}

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata,true);
$driversql = "SELECT iDriverId, vEmail, Concat(vName,' ',vLastName) as drivername FROM register_driver WHERE eStatus = 'active' and vAvailability = 'Available'";
$drivers = $obj->MySQLSelect($driversql);
//echo '<pre>'; print_r($DBProcessingOrders); echo '</pre>'; die('dsf');
if(isset($_POST['removedrive'])){
    $driverid = $_POST['driverid'];
    $orderid = $_POST['orderid'];
    $updateorder = "update orders set iDriverId = 0,iStatusCode=2 where iOrderId = $orderid";
    //$updatedriver = "update register_driver set vAvailability = 'Available' where iDriverId = $driverid";
    $updatdrivereq = "update driver_request set eStatus = 'RemovedbyAdmin' where iOrderId = $orderid";

    $getreqid = "select iRequestId from driver_request where iOrderId = $orderid and estatus = 'RemovedbyAdmin'";
    $getreqids = $obj->MySQLSelect($getreqid);
    $irequestid = $getreqids[0]['iRequestId'];
    $deleteorPreq = "delete from passenger_requests where iRequestId = $irequestid";
    $obj->MySQLSelect($deleteorPreq);
    $deletetrips = "delete from trips where iOrderId = $orderid";
    $obj->MySQLSelect($deletetrips);
    $updatedr = $obj->MySQLSelect($updateorder);
    $updatedr1 = $obj->MySQLSelect($updatdrivereq);
    header("Refresh:0");
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title><?=$SITE_NAME?> | <?php echo $langage_lbl_admin['LBL_PROCESSING_ORDERS'];?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <?php include_once('global_files.php');?>

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

            <!-- Search Form End -->
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <? if(count($service_cat_data) > 1){ ?>
                                            <th class="text-center">Serivce Type</th>
                                        <? } ?>
                                        <th class="text-center"><?php echo $langage_lbl_admin['LBL_ORDER_NO_ADMIN'];?>#</th>

                                        <th class="text-center"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; } else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_TRIP_DATE_ADMIN'];?> <?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                        <th><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];?> Name <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                        <th><a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)">Store Name <?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                        <th><a href="javascript:void(0);" onClick="Redirect(4,<?php if($sortby == '4'){ echo $order; }else { ?>0<?php } ?>)">Delivery Driver <?php if ($sortby == 4) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                        <th class="text-right">Order Total</th>
                                        <th class="text-center">Order Status</th>
                                        <th class="text-center">Payment Mode</th>
                                        <th class="text-center">Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <? if(!empty($DBProcessingOrders)) {


                                        for($i=0;$i<$endRecord;$i++) { ?>

                                            <tr class="gradeA">
                                                <? if(count($service_cat_data) > 1){ ?>
                                                    <td class="text-center"><?=$DBProcessingOrders[$i]['vServiceName'];?></td>
                                                <? } ?>
                                                <td class="text-center"><a href="invoice.php?iOrderId=<?=$DBProcessingOrders[$i]['iOrderId']?>" target="_blank"><?=$DBProcessingOrders[$i]['vOrderNo'];?></a></td>

                                                <td class="text-center">
                                                    <?=$generalobjAdmin->DateTime($DBProcessingOrders[$i]['tOrderRequestDate'],'yes')?>

                                                </td>
                                                <td><?=$generalobjAdmin->clearName($DBProcessingOrders[$i]['riderName']);?><br>
                                                    <? if(!empty($DBProcessingOrders[$i]['user_phone'])) {
                                                        echo  $generalobjAdmin->clearPhone($DBProcessingOrders[$i]['user_phone']);
                                                    }?>

                                                </td>

                                                <td><?=$generalobjAdmin->clearCmpName($DBProcessingOrders[$i]['vCompany']);?><br>
                                                    <? if(!empty($DBProcessingOrders[$i]['resturant_phone'])) {
                                                        echo  $generalobjAdmin->clearPhone($DBProcessingOrders[$i]['resturant_phone']);
                                                    }?>

                                                </td>
                                                <td>
                                                    <?php if(!empty($DBProcessingOrders[$i]['driverName'])){
                                                        echo  $generalobjAdmin->clearName($DBProcessingOrders[$i]['driverName']);
                                                    }?>
                                                    <br>
                                                    <? if(!empty($DBProcessingOrders[$i]['driver_phone'])){
                                                        echo $generalobjAdmin->clearPhone($DBProcessingOrders[$i]['driver_phone']);
                                                    }?>
                                                </td>

                                                <!-- <td><?= $DBProcessingOrders[$i]['TotalItem']?></td> -->
                                                <td class="text-right"><?= $generalobj->trip_currency($DBProcessingOrders[$i]['fNetTotal']); ?></td>
                                                <td class="text-center"><?= $DBProcessingOrders[$i]['vStatus']?></td>

                                                <td class="text-center"><?= $DBProcessingOrders[$i]['ePaymentOption']?></td>
                                                <td class="text-center">

                                                    <?php if($DBProcessingOrders[$i]['iStatusCode'] == 2): ?>
                                                        <a href="javascript:void(0)" class="custom-order btn btn-info" data-toggle="modal" data-id="<?= $DBProcessingOrders[$i]['iOrderId'];?>">Assign Order</a>

                                                    <?php else : ?>
                                                    <form action="" method="post">
                                                        <button type="submit" class=" btn btn-info"  onclick="return confirm('Are you sure?')">Unassign Order</button>
                                                        <input type="hidden" value="1" name="removedrive" >
                                                        <input type="hidden" name="driverid" value="<?= $DBProcessingOrders[$i]['iDriverId']; ?>">
                                                        <input type="hidden" value="<?= $DBProcessingOrders[$i]['iOrderId'];?>" name="orderid" >
                                                    </form>


                                                    <?php endif; ?>

                                                </td>
                                            </tr>
                                            <div class="clear"></div>
                                        <?php }
                                    } else { ?>
                                        <tr class="gradeA">
                                            <td colspan="8"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
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
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
    <input type="hidden" name="action" value="<?php echo $action; ?>" >
    <input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>" >
    <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>" >
    <input type="hidden" name="searchRider" value="<?php echo $searchRider; ?>" >
    <input type="hidden" name="searchServiceType" value="<?php echo $searchServiceType; ?>" >
    <input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>" >
    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
    <input type="hidden" name="vStatus" value="<?php echo $vStatus; ?>" >
    <input type="hidden" name="eType" value="<?php echo $eType; ?>" >
    <input type="hidden" name="method" id="method" value="" >
</form>
<div id="assign_form" class="modal fade delete_form text-left" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">x</button>
                <h4 class="modal-title">Assign Order</h4>
            </div>

            <form role="form" name="delete_form" id="delete_form1" method="post" action="" class="margin0">

                <div class="modal-body">
                    <?php
                    if(count($drivers) > 0){
                        $i=0;
                        echo "<select name='driverid' class='filter-by-text'>";
                        foreach($drivers as $val){
                            $i++;
                    ?>
                            <option type="radio" name="driver_id" id="driveridcheck<?= $i ?>" value="<?= $val['iDriverId'] ?>"><?= $val['drivername'].' - '.$val['vEmail'] ?>
                            </option>
                    <?php
                        }
                        echo "</select>";
                    }else{
                        echo "<h3>No Driver found right now. Please try again.</h3>";
                    }?>

                    <input type="hidden" name="iOrderId" id="orderid"  value="" />
                    <input type='hidden' name='action' value='assignorder' />
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info" id="assign_booking" title="Cancel Booking">Assign Order</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" id.="close_model">Close</button>
                </div>
            </form>

        </div>
        <!-- Modal content-->
    </div>
</div>
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
    $('#searchCompany').change(function() {
        var company_id = $(this).val(); //get the current value's option
        $.ajax({
            type:'POST',
            url:'ajax_find_driver_by_company.php',
            data:{'company_id':company_id},
            cache: false,
            success:function(data){
                $(".driver_container").html(data);
            }
        });
    });


    /*$('.delete_form').on('hidden.bs.modal', function () {
        //window.location.reload();
         //$('.delete_form').reset();
         $(".modal-body").[0]reset();
    });	*/
</script>

<script type="text/javascript">

    $(document).ready(function()
    {
        $('.custom-order').on('click', function () {
            var order_id = $(this).data('id');
            $('#orderid').val(order_id);
            $('#assign_form').modal('show');
        })
    });
    //
    // $(function(){
    //     $("#assign_booking").on('click', function(e) {
    //         var cancel_reason = $('#cancel_reason').val();
    //         var cancelcharge = $('#fCancellationCharge').val();
    //         if(cancel_reason == '' || cancelcharge == '') {
    //             $(".cnl_error").html("This Field is required.");
    //             $(".cancelcharge_error").html("This Field is required.");
    //             return false;
    //         } else {
    //             $(".cnl_error").html("");
    //             $(".cancelcharge_error").html("");
    //             $( "#delete_form1" )[0].submit();
    //         }
    //
    //     });
    // });

</script>


</body>
<!-- END BODY-->
</html>
