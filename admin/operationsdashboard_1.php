<?php
include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
global $generalobj, $obj;
$generalobjAdmin->check_member_login();
$script = "operationsdashboard";
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 205) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$driversql = "SELECT iDriverId, vEmail, Concat(vName,' ',vLastName) as drivername FROM register_driver WHERE eStatus = 'active'  AND tLocationUpdateDate > '$str_date'";
//and vAvailability = 'Available' and vTripStatus != 'Active'
$drivers = $obj->MySQLSelect($driversql);

if (isset($_POST['action']) && ($_POST['action'] == 'assignorder')) {
    $assignorder = AssignOrderToDriver($_POST['iOrderId'], $_POST['driverid']);
    //print_r($assignorder);die;
    header("Refresh:0");
}
if (isset($_POST['removedrive'])) {
    $driverid = $_POST['driverid'];
    $orderid = $_POST['orderid'];
    $sql = "select d.iDriverId,d.iGcmRegId,d.eDeviceType,d.tSessionId,d.iAppVersion,d.vLang,o.vOrderNo , o.iCompanyId from orders as o LEFT JOIN register_driver as d ON o.iDriverId=d.iDriverId where o.iOrderId = '" . $orderid . "'";
    $drv_data_order = $obj->MySQLSelect($sql);
    $updateorder = "update orders set iDriverId = 0,iStatusCode=2 where iOrderId = $orderid";


    $updatdrivereq = "update driver_request set eStatus = 'RemovedbyAdmin' where iOrderId = $orderid";


    $updatedr = $obj->sql_query($updateorder);
    // echo "1";
    $updatedr1 = $obj->sql_query($updatdrivereq);
    //  echo "2";
    $getreqid = "select iRequestId from driver_request where iOrderId = $orderid and estatus = 'RemovedbyAdmin'";
    $getreqids = $obj->MySQLSelect($getreqid);
    // echo "3";
    $irequestid = $getreqids[0]['iRequestId'];
    $deleteorPreq = "delete from passenger_requests where iRequestId = $irequestid";
    $obj->sql_query($deleteorPreq);
    // echo "4";
    $deletetrips = "delete from trips where iOrderId = $orderid";
    $obj->sql_query($deletetrips);
    // echo "5";
    $CheckDriverwithotherorders = CheckDriverStatusIFonGoingWithOrders($driverid);
    if ($CheckDriverwithotherorders == 'no') {
        $updatedriver = "update register_driver set vAvailability = 'Available' , vTripStatus = 'Finished' where iDriverId = '$driverid';";
        $obj->sql_query($updatedriver);
        //  echo "6";
    }






    $uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";
    $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations", "PUBNUB_DISABLED");
    $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
    $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");


    ## Send Notification To Driver ##
    $Message = "OrderCancelByAdmin";



    $drvLangCode = $drv_data_order[0]['vLang'];
    $drvOrderNo = $drv_data_order[0]['vOrderNo'];
    $iDriverId = $drv_data_order[0]['iDriverId'];

    $message_arr_res = array();
    $message_arr_res['Message'] = $Message;
    $message_arr_res['iOrderId'] = $orderid;
    $message_arr_res['vOrderNo'] = $drvOrderNo;
    $message_arr_res['vTitle'] = 'Order Unassign By Admin';
    $message_arr_res['tSessionId'] = $drv_data_order[0]['tSessionId'];
    ;
    $drvmessage = json_encode($message_arr_res, JSON_UNESCAPED_UNICODE);
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }

    $alertSendAllowed = true;
    /* For PubNub Setting */
    $tableName = "register_driver";
    $iMemberId_VALUE = $iDriverId;
    $iMemberId_KEY = "iDriverId";
    $iAppVersion = $drv_data_order[0]['iAppVersion'];
    $eDeviceType = $drv_data_order[0]['eDeviceType'];
    $iGcmRegId = $drv_data_order[0]['iGcmRegId'];
    $tSessionId = $drv_data_order[0]['tSessionId'];
    $registatoin_ids = $iGcmRegId;
    $drvdeviceTokens_arr_ios = array();
    $drvregistation_ids_new = array();
    /* For PubNub Setting Finished */

    if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
        $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
        $DriverchannelName = "DRIVER_" . $drv_data_order[0]['iCompanyId'];
        $info = $pubnub->publish($DriverchannelName, $drvmessage);
        if ($eDeviceType != "Android") {
            array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
        }
    }

    if ($alertSendAllowed == true) {

        if ($eDeviceType == "Android") {
            array_push($drvregistation_ids_new, $iGcmRegId);

            $Dmessage = array("message" => $drvmessage);

            $result = send_notification($drvregistation_ids_new, $Dmessage, 0);
        } else {
            array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
            sendApplePushNotification(0, $drvdeviceTokens_arr_ios, $drvmessage, $alertMsg, 0);
        }
    }


    ## Send Notification To Driver ##






    header("Refresh:0");
}
//date_default_timezone_set('America/New_York');
//o.iStatusCode = 6 AND
$Startdate = date('Y-m-d', strtotime('-1 days', strtotime(date('Y-m-d')))).' 00:00:01';
$Enddate = date('Y-m-d H:i:s');
$TTLOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";

$TTLOrderO = $obj->MySQLSelect($TTLOrderQ);

$PLOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 1 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$PLOrderO = $obj->MySQLSelect($PLOrderQ);


$ACOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 2 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$ACOrderO = $obj->MySQLSelect($ACOrderQ);


$ASOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 4 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$ASOrderO = $obj->MySQLSelect($ASOrderQ);


$PUOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 5 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$PUOrderO = $obj->MySQLSelect($PUOrderQ);


$EROrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 5 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$EROrderO = $obj->MySQLSelect($EROrderQ);


$DELOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 6 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$DELOrderO = $obj->MySQLSelect($DELOrderQ);


/*
 * *********************************RESTAURANTS***********************************
 */
$TTLRestQ = "SELECT COUNT(`iCompanyId`) AS 'tcount' FROM `company` WHERE `eStatus` = 'Active';";
$TTLRestO = $obj->MySQLSelect($TTLRestQ);

$ONRestQ = "SELECT COUNT(`iCompanyId`) AS 'tcount' FROM `company` WHERE `eStatus` = 'Active' AND `eAvailable` = 'Yes';";
$ONRestO = $obj->MySQLSelect($ONRestQ);

$OFFRestQ = "SELECT COUNT(`iCompanyId`) AS 'tcount' FROM `company` WHERE `eStatus` = 'Active' AND `eAvailable` = 'No';";
$OFFRestO = $obj->MySQLSelect($OFFRestQ);


/*
 * *********************************RESTAURANTS***********************************
 */
$TTLDriverQ = "SELECT COUNT(`iDriverId`) AS 'tcount' FROM `register_driver` WHERE `eStatus` = 'active';";
 $TTLDriverO = $obj->MySQLSelect($TTLDriverQ);

$RYDriverQ = "SELECT COUNT(`iDriverId`) AS 'tcount' FROM `register_driver` WHERE `eStatus` = 'active' AND `vAvailability` = 'Available';";
 $RYDriverO = $obj->MySQLSelect($RYDriverQ);

$BKDriverQ = "SELECT COUNT(`iDriverId`) AS 'tcount' FROM `register_driver` WHERE `eStatus` = 'active' AND `vAvailability` = 'Not Available';";
$BKDriverO = $obj->MySQLSelect($BKDriverQ);

?>

<!DOCTYPE html>
<html lang="en">

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | Live Map</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <!-- GLOBAL STYLES -->
        <? include_once('global_files.php');?>
        <link rel="stylesheet" href="css/style.css" />

        <script src="https://maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>" type="text/javascript"></script>
        <script type='text/javascript' src='../assets/map/gmaps.js'></script>
        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
        <!--END GLOBAL STYLES -->
        <style>
            .Table_TOp_B { 
                border-top: 1px solid #000;
                border-left: 1px solid #000;
                border-right: 1px solid #000;
                border-bottom: 0;
            }

            .Table_Bottom_B { border-top: 0;
                              border-left: 1px solid #000;
                              border-right: 1px solid #000;
                              border-bottom: 1px solid #000;
            }
        </style>
        
         
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">

                <div class="inner" style="min-height: 700px;">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="pull-left">Operations Dashboard</h1>
                            <p onclick="openFullscreen();" class="pull-right"><i class="fa fa-4x fa-border" style="color: #111; cursor: pointer;"></i></p>
                        </div>
                    </div>
                    <hr />

<script>
                        var elem = document.getElementById("content");
                        function openFullscreen() {
                            if (elem.requestFullscreen) {
                                elem.requestFullscreen();
                            } else if (elem.mozRequestFullScreen) { /* Firefox */
                                elem.mozRequestFullScreen();
                            } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
                                elem.webkitRequestFullscreen();
                            } else if (elem.msRequestFullscreen) { /* IE/Edge */
                                elem.msRequestFullscreen();
                            }

                        }
                    </script> 

                    

                    <div class="row">
                        <div class="col-lg-12 Top_Blocks">
                            <div class="square-block">
                                <h3>Orders</h3>
                                <table>
                                    <tr>
                                        <th>TTL</th>
                                        <th>PL</th>
                                        <th>AC</th>
                                        <th>AS</th>
                                        <th>PU</th>
                                        <th>ER</th>
                                        <th>DEL</th>
                                    </tr>
                                    <tr>
                                        <td><?php echo $TTLOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $PLOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $ACOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $ASOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $PUOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $EROrderO[0]['tcount']; ?></td>
                                        <td><?php echo $DELOrderO[0]['tcount']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="square-block">
                                <h3>Restaurants</h3>
                                <table>
                                    <tr>
                                        <th>TTL</th>
                                        <th>ON</th>
                                        <th>OFF</th>
                                    </tr>
                                    <tr>
                                        <td><?php echo $TTLRestO[0]['tcount']; ?></td>
                                        <td><?php echo $ONRestO[0]['tcount']; ?></td>
                                        <td><?php echo $OFFRestO[0]['tcount']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="square-block">
                                <h3>Drivers</h3>
                                
                                <table>
                                    <tr>
                                        <th>TTL</th>
                                        <th>RY</th>
                                        <th>PU</th>
                                        <th>ER</th>
                                        <th>BK</th>
                                        <th>ADT</th>
                                    </tr>
                                    <tr>
                                        <td><?php echo $TTLDriverO[0]['tcount']; ?></td>
                                        <td><?php echo $RYDriverO[0]['tcount']; ?></td>
                                        <td><?php echo ''; ?></td>
                                        <td><?php echo ''; ?></td>
                                        <td><?php echo $BKDriverO[0]['tcount']; ?></td>
                                        <td><?php echo ''; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="Operation-Dashboard">
                        <div class="col-md-3 nopadding">
                            <div class="orders_data_dashboard">
                                <ul>
                                    <li> <button role="button" class="btn btn-sm btn-default raised setclass  active all" id="active" onClick="allorders('', 'active')">Active</button></li>
                                    <li> <button role="button" class="btn btn-sm btn-default raised setclass" id="delivered" onClick="allorders('', 'delivered')">Delivered</button></li>
                                    <li> <button role="button" class="btn btn-sm btn-default raised setclass" id="cancelled" onClick="allorders('', 'cancelled')">Cancelled</button></li>
                                    <li> <button role="button" class="btn btn-sm btn-default raised setclass" id="all" onClick="allorders('', 'all')">All</button></li>
                                </ul>

                            </div>
                            <!--                    <h3 class="list_title"><a href="javascript:void(0);" class="active">All</a></h3>-->
                            <!--                    <span>-->
                            <!--                        <input name="" type="text" placeholder="Search --><?//=$langage_lbl['LBL_DRIVER'];?><!--" onKeyUp="get_drivers_list(this.value)">-->
                            <!--                    </span>-->
                            <table id="orders_main_list">
                                <thead>
                                <th style="width:60%;">Ord#-Restaurant <br/>Sector-Address </th>
                                <th style="width:25%;">Status<br/>Time</th>
                                <th style="width:15%;">Action</th>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-7 nopadding">
                            <div class="orders_data_dashboard">
                                <ul>
                                    <li> <button role="button" class="btn btn-sm btn-default raised setclass active all" id="active" onClick="allorders('', 'drivers')">Drivers</button></li>
                                    <li> <button role="button" class="btn btn-sm btn-default raised setclass " id="delivered" onClick="allorders('', 'restaurants')">Restaurant</button></li>
                                    <li> <button role="button" class="btn btn-sm btn-default raised setclass" id="cancelled" onClick="allorders('', 'customers')">Customers</button></li>
                                    <li> <button role="button" class="btn btn-sm btn-default raised setclass" id="all" onClick="allorders('', 'all')">All</button></li>
                                </ul>

                            </div>
                            <div class="panel-heading location-map" style="background:none;">
                                <div class="google-map-wrap" style="float:left; width:100%;">
                                    <div id="google-map" class="google-map map001"> </div>
                                    <!-- #google-map -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 nopadding">
                            <!--                    <h3 class="list_title"><a href="javascript:void(0);" class="active">All</a></h3>-->
                            <!--                    <span>-->
                            <!--                        <input name="" type="text" placeholder="Search --><?//=$langage_lbl['LBL_DRIVER'];?><!--" onKeyUp="get_drivers_list(this.value)">-->
                            <!--                    </span>-->
                            <table id="driver_main_list">
                                <thead>
                                <th style="width:40%;">Name<br/>Ord#</th>
                                <th style="width:20%;">Status</th>
                                <th style="width:40%;">Time</th>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <!-- popup -->
                        <div class="map-popup" style="display:none" id="driver_popup"></div>
                        <!-- popup end -->
                    </div>
                    <input type="hidden" name="newType" id="newType" value="">
                    <div style="clear:both;"></div>
                    <?php if (SITE_TYPE != 'Demo') { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-hover table-bordered">
                                    <tr>
                                        <td style="width:50%">
                                            <h4>Order Status:</h4>
                                        </td>
                                        <td style="width:50%">
                                            <h4>Driver Status </h4> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:50%">
                                            <p>Placed (PL), Accepted (AC), Assigned (AS), Picked Up (PU), Enroute (ER), Delivered (DL), Cancelled (CL)</p>
                                        </td>
                                        <td style="width:50%">
                                            <p>Ready (RY), Assigned (AS), Enroute (ER), Break/Not Available (BK) </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <!--                    <div class="col-md-6">
                                                    
                                                <ul>
                                                    <li>“Placed” (PL) Placed by customer & awaiting acceptance by restaurant.</li>
                                                    <li>“Accepted” (AC) Accepted by restaurant & awaiting driver assignment.</li>
                                                    <li>“Assigned” (AS) Assigned to a driver & awaiting pickup. Oldest on top.</li>
                                                    <li>“Picked Up” (PU)Picked Up by driver but not Enroute, driver picking up more orders.</li>
                                                    <li>“Enroute” (ER) Picked up and Enroute by driver but not delivered.</li>
                                                    <li>“Delivered” (DL) Order Delivered.</li>
                                                    <li>“Cancelled” (CL) Order Cancelled.</li>
                                                </ul>
                                                </div>
                                                <div class="col-md-6">
                                                     
                                                    <ul>
                                                   
                                                    <li>“Ready” (RY) Driver ready for order assignment.  </li>
                                                    <li>“Assigned” (AS) Driver assigned orders but has not picked up ALL of them yet.</li>
                                                    <li>“Enroute” (ER) Driver has picked up all assigned orders and is enroute to customers.</li>
                                                    <li>“Break” (BK) Driver with no assigned orders but is not available for assignment. Ie, gas, restroom, etc.</li>
                                                </ul>
                                                </div>-->

                        </div>

                    <?php } ?>
                </div>
                
                
                
                <div style="clear:both;"></div>
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
                                if (count($drivers) > 0) {
                                    $i = 0;
                                    echo "<select name='driverid' class='filter-by-text'>";
                                    foreach ($drivers as $val) {
                                        $i++;
                                        ?>
                                        <option type="radio" name="driver_id" id="driveridcheck<?= $i ?>" value="<?= $val['iDriverId'] ?>"><?= $val['drivername'] . ' - ' . $val['vEmail'] ?>
                                        </option>
                                        <?php
                                    }
                                    echo "</select>";
                                } else {
                                    echo "<h3>No Driver found right now. Please try again.</h3>";
                                }
                                ?>

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
                
                

                <!--END PAGE CONTENT -->
            </div>

            <? include_once('footer.php'); ?>
            
            <script>
                //var is_touch_device = 'ontouchstart' in document.documentElement;
                var newName;
                var newAddr;
                var newOnlineSt;
                var newLat;
                var newLong;
                var newImg;
                var map;
                var bounds = [];
                var markers = [];
                var latlng;
                var newImg;
                var newLocations;
                jQuery(document).ready(function ($) {
                    /* Do not drag on mobile. */
                    $.ajax({
                        type: "POST",
                        url: "get_map_drivers_list.php",
                        dataType: "json",
                        data: {type: ''},
                        success: function (dataHtml) {
                            newLocations = dataHtml.locations;
                            if (newLocations == "") {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: '',
                                    lng: '',
                                    //scrollwheel: false,
                                    //draggable: ! is_touch_device
                                });
                            } else {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: newLocations[0].google_map.lat,
                                    lng: newLocations[0].google_map.lng,
                                    //scrollwheel: false,
                                    //draggable: ! is_touch_device
                                });
                            }

                            for (var i = 0; i < newLocations.length; i++) {
                                newName = newLocations[i].location_name;
                                newAddr = newLocations[i].location_address;
                                newOnlineSt = newLocations[i].location_online_status;
                                newLat = newLocations[i].google_map.lat;
                                newLong = newLocations[i].google_map.lng;
                                newDriverImg = newLocations[i].location_image;
                                newMobile = newLocations[i].location_mobile;
                                newDriverID = newLocations[i].location_ID;
                                newImg = newLocations[i].location_icon;

                                latlng = new google.maps.LatLng(newLat, newLong);
                                bounds.push(latlng);

                                // if(newOnlineSt == 'Available') { newImg = '../webimages/upload/mapmarker/available.png'; } else if(newOnlineSt == 'Active') { newImg = '../webimages/upload/mapmarker/enroute.png'; }else if(newOnlineSt == 'Arrived') { newImg = '../webimages/upload/mapmarker/reached.png'; }else { newImg = '../webimages/upload/mapmarker/started.png'; }
                                var marker = map.addMarker({
                                    lat: newLat,
                                    lng: newLong,
                                    icon: newImg,
                                    infoWindow: {
                                        content: '<table><tr><td rowspan="4"><img src="' + newDriverImg + '" height="60" width="60"></td></tr><tr><td>&nbsp;&nbsp;Email: </td><td><b>' + newDriverID + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>'
                                    }
                                });
                                markers.push(marker);
                            }
                            // map.fitLatLngBounds(bounds);

                            latlngJacksonville = new google.maps.LatLng(34.758792, -77.418553);
                            map.setZoom(10);
                            map.panTo(latlngJacksonville);

                            $.ajax({
                                type: "POST",
                                url: "get_available_driver_list_in_operationdashboard.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    $('#driver_main_list').show();
                                    $('#driver_main_list tbody').html(dataHtml2);

                                }, error: function (dataHtml2) {

                                }
                            });
                            $.ajax({
                                type: "POST",
                                url: "get_today_orders.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    $('#orders_main_list').show();
                                    $('#orders_main_list tbody').html(dataHtml2);

                                }, error: function (dataHtml2) {

                                }
                            });

                        },
                        error: function (dataHtml) {
                            var map = new GMaps({
                                el: '#google-map',
                                lat: '',
                                lng: '',
                                // zoom: 14,
                                // scrollwheel: false,
                                // draggable: ! is_touch_device
                            });
                            // map.setZoom(11);
                        }
                    });

                    var $window = $(window);
                    function mapWidth() {
                        var size = $('.google-map-wrap').width();
                        $('.google-map').css({width: size + 'px', height: (size / 2) + 'px'});
                    }
                    mapWidth();
                    $(window).resize(mapWidth);

                });

                /* Map Reload after a minute */
                setInterval(function () {
                    newType = $("#newType").val();

                    $.ajax({
                        type: "POST",
                        url: "get_map_drivers_list.php",
                        dataType: "json",
                        data: {type: newType},
                        success: function (dataHtml) {
                            for (var i = 0; i < markers.length; i++) {
                                markers[i].setMap(null);
                            }
                            newLocations = dataHtml.locations;
                            for (var i = 0; i < newLocations.length; i++) {
                                if (newType == newLocations[i].location_type || newType == "") {
                                    newName = newLocations[i].location_name;
                                    newAddr = newLocations[i].location_address;
                                    newOnlineSt = newLocations[i].location_online_status;
                                    newLat = newLocations[i].google_map.lat;
                                    newLong = newLocations[i].google_map.lng;
                                    newDriverImg = newLocations[i].location_image;
                                    newMobile = newLocations[i].location_mobile;
                                    newDriverID = newLocations[i].location_ID;
                                    newImg = newLocations[i].location_icon;

                                    latlng = new google.maps.LatLng(newLat, newLong);
                                    bounds.push(latlng);

                                    // if(newOnlineSt == 'Available') { newImg = '../webimages/upload/mapmarker/available.png'; } else if(newOnlineSt == 'Active') { newImg = '../webimages/upload/mapmarker/enroute.png'; }else if(newOnlineSt == 'Arrived') { newImg = '../webimages/upload/mapmarker/reached.png'; }else { newImg = '../webimages/upload/mapmarker/started.png'; }
                                    var marker = map.addMarker({
                                        lat: newLat,
                                        lng: newLong,
                                        icon: newImg,
                                        infoWindow: {
                                            content: '<table><tr><td rowspan="4"><img src="' + newDriverImg + '" height="60" width="60"></td></tr><tr><td>&nbsp;&nbsp;Email: </td><td><b>' + newDriverID + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>'
                                        }
                                    });
                                    markers.push(marker);
                                }
                            }

                            $.ajax({
                                type: "POST",
                                url: "get_available_driver_list_in_operationdashboard.php",
                                dataType: "html",
                                data: {type: newType},
                                success: function (dataHtml2) {
                                    $('#driver_main_list').show();
                                    $('#driver_main_list tbody').html(dataHtml2);

                                }, error: function (dataHtml2) {

                                }
                            });
                            $.ajax({
                                type: "POST",
                                url: "get_today_orders.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    $('#orders_main_list').show();
                                    $('#orders_main_list tbody').html(dataHtml2);

                                }, error: function (dataHtml2) {

                                }
                            });
                        },
                        error: function (dataHtml) {

                        }
                    });

                }, 120000);
                /* Map Reload after a minute */

                function allorders(type, set) {
                    if (set != "") {
                        $("#newType").val(type);
                        $('.setclass').removeClass('active');
                        $("#" + set).addClass('active');
                        if (type == 'Active') {
                            title = 'Enroute to Pickup';
                            classname = 'enroute';
                        } else if (type == 'Arrived') {
                            title = 'Reached Pickup';
                            classname = 'reached';
                        } else if (type == 'On Going Trip') {
                            title = 'Journey Started';
                            classname = 'tripstart';
                        } else if (type == 'Available') {
                            title = 'Available';
                            classname = 'available';
                        } else {
                            title = 'All';
                            classname = 'all';
                        }
                        $(".list_title").html('<a href="javascript:void(0);" class="active ' + classname + '">' + title + '</a>');
                    }

                    for (var i = 0; i < markers.length; i++) {
                        markers[i].setMap(null);
                    }
                    //console.log(newLocations);
                    //return false;
                    for (var i = 0; i < newLocations.length; i++) {
                        if (type == newLocations[i].location_type || type == "") {
                            newName = newLocations[i].location_name;
                            newAddr = newLocations[i].location_address;
                            newOnlineSt = newLocations[i].location_online_status;
                            newLat = newLocations[i].google_map.lat;
                            newLong = newLocations[i].google_map.lng;
                            newDriverImg = newLocations[i].location_image;
                            newMobile = newLocations[i].location_mobile;
                            newDriverID = newLocations[i].location_ID;
                            newImg = newLocations[i].location_icon;
                            latlng = new google.maps.LatLng(newLat, newLong);
                            bounds.push(latlng);

                            // if(newOnlineSt == 'Available') { newImg = '../webimages/upload/mapmarker/available.png'; } else if(newOnlineSt == 'Active') { newImg = '../webimages/upload/mapmarker/enroute.png'; }else if(newOnlineSt == 'Arrived') { newImg = '../webimages/upload/mapmarker/reached.png'; }else { newImg = '../webimages/upload/mapmarker/started.png'; }
                            var marker = map.addMarker({
                                lat: newLat,
                                lng: newLong,
                                icon: newImg,
                                infoWindow: {
                                    content: '<table><tr><td rowspan="4"><img src="' + newDriverImg + '" height="60" width="60"></td></tr><tr><td>&nbsp;&nbsp;ID: </td><td><b>' + newDriverID + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>'
                                }
                            });
                            markers.push(marker);
                        }
                    }

                    $.ajax({
                        type: "POST",
                        url: "get_available_driver_list_in_operationdashboard.php",
                        dataType: "html",
                        data: {type: type},
                        success: function (dataHtml2) {
                            $('#driver_main_list').show();
                            $('#driver_main_list tbody').html(dataHtml2);

                        }, error: function (dataHtml2) {

                        }
                    });
                    $.ajax({
                        type: "POST",
                        url: "get_today_orders.php",
                        dataType: "html",
                        data: {type: set},
                        success: function (dataHtml2) {
                            $('#orders_main_list').show();
                            $('#orders_main_list tbody').html(dataHtml2);

                        }, error: function (dataHtml2) {

                        }
                    });
                }

                function get_drivers_list(keyword) {
                    newType = $("#newType").val();
                    $.ajax({
                        type: "POST",
                        url: "get_available_driver_list_in_operationdashboard.php",
                        dataType: "html",
                        data: {keyword: keyword, type: newType},
                        success: function (dataHtml2) {
                            $('#driver_main_list').show();
                            $('#driver_main_list tbody').html(dataHtml2);

                        }, error: function (dataHtml2) {

                        }
                    });
                }

                function showPopupDriver(driverId) {
                    if ($("#driver_popup").is(":visible") && $('#driver_popup ul').attr('class') == driverId) {
                        $("#driver_popup").hide("slide", {direction: "right"}, 700);
                    } else {
                        //alert(driverId);
                        $("#driver_popup").hide();
                        $.ajax({
                            type: "POST",
                            url: "get_driver_detail_popup.php",
                            dataType: "html",
                            data: {driverId: driverId},
                            success: function (dataHtml2) {
                                $('#driver_popup').html(dataHtml2);
                                $("#driver_popup").show("slide", {direction: "right"}, 700);
                            }, error: function (dataHtml2) {

                            }
                        });
                    }
                }


                $(document).mouseup(function (e)
                {
                    var container = $("#driver_popup");
                    var container1 = $("#driver_main_list");

                    if (!container.is(e.target) && !container1.is(e.target) // if the target of the click isn't the container...
                            && container.has(e.target).length === 0 && container1.has(e.target).length === 0) // ... nor a descendant of the container
                    {
                        container.hide("slide", {direction: "right"}, 700);
                    }
                });
                $(document).on('click', '.custom-order', function () {
                    var order_id = $(this).data('id');
                    $('#orderid').val(order_id);
                    $('#assign_form').modal('show');
                });


                $('#IOrderStatus').on('change', function () {

                    allorders('', $(this).val());
                });
            </script>
    </body>
    <!-- END BODY-->
</html>