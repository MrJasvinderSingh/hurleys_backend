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
$script = "operationsdashboard";
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 205) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$driversql = "SELECT iDriverId, vEmail, Concat(vName,' ',vLastName) as drivername FROM register_driver WHERE eStatus = 'active' AND vAvailability = 'Available' AND tLocationUpdateDate > '$str_date';";
//AND tLocationUpdateDate > '$str_date'
//and vAvailability = 'Available' and vTripStatus != 'Active'
$drivers = $obj->MySQLSelect($driversql);

if (isset($_POST['action']) && ($_POST['action'] == 'assignorder')) {
    $assignorder = AssignOrderToDriver($_POST['iOrderId'], $_POST['driverid']);
    header("refresh:0");
}
if (isset($_POST['removedrive'])) {
    $assignorder = UnassignOrderToDriver($_POST['driverid'], $_POST['orderid']);
    header("refresh:0");
}

/*
 * *********************************RESTAURANTS***********************************
 */
$TTLRestQ = "SELECT COUNT(`iCompanyId`) AS 'tcount' FROM `company` WHERE `eStatus` = 'Active';";
$TTLRestO = $obj->MySQLSelect($TTLRestQ);

$ONRestQ = "SELECT COUNT(`iCompanyId`) AS 'tcount' FROM `company` WHERE `eStatus` = 'Active' AND `eAvailable` = 'Yes';";
$ONRestO = $obj->MySQLSelect($ONRestQ);

$OFFRestQ = "SELECT COUNT(`iCompanyId`) AS 'tcount' FROM `company` WHERE `eStatus` = 'Active' AND `eAvailable` = 'No';";
$OFFRestO = $obj->MySQLSelect($OFFRestQ);
?>

<!DOCTYPE html>
<html lang="en">

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | Operations Center</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <!-- GLOBAL STYLES -->
        <? include_once('global_files.php');?>
        <link rel="stylesheet" href="css/style.css" />

        <script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>" type="text/javascript"></script>
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
            .appendpopdata .col-md-12{padding-top:5px;padding-bottom:5px;}
            #orders_main_list td { padding: 0 4px 0 4px;}
            .modal-header .close {
                margin-top: -30px;
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
                    <!--      <div class="row">
                              <div class="col-lg-12 operationdash_title">
                                  <h1 class="pull-left">Operations Dashboard</h1>
                                  <p onclick="openFullscreen();" class="pull-right openclose" title='Full Screen'><i class="fa fa-4x fa-border" style="color: #111; cursor: pointer;"></i></p>
                              </div>
                          </div>
                          <hr />
      
                          
      
                    -->


                    <div class="row">
                        <div class="col-lg-12 Top_Blocks">
                            <div class="square-block col-md-4">
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
                                        <th>CL</th>
                                    </tr>
                                    <tr class='totalodersStatus'>
                                        <td><?php echo $TTLOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $PLOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $ACOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $ASOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $PUOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $EROrderO[0]['tcount']; ?></td>
                                        <td><?php echo $DELOrderO[0]['tcount']; ?></td>
                                        <td><?php echo $CANCOrderO[0]['tcount']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="square-block  col-md-4">
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
                            <div class="square-block  col-md-4">
                                <h3>Drivers <span onclick="openFullscreen();" class="pull-right openclose" title='Full Screen'><i class="fa fa-2x fa-border" style="color: #111; cursor: pointer;"></i></span></h3>


                                <table>
                                    <tr>
                                        <th>TTL</th>
                                        <th>RY</th>
                                        <th>AS</th>
                                        <th>ER</th>
                                        <th>BK</th>
                                        <th>ADT</th>
                                    </tr>
                                    <tr>
                                        <td class='driverttl'><?php echo $TTLDriverO[0]['tcount']; ?></td>
                                        <td class='driverry'><?php //echo $RYDriverO[0]['tcount'];     ?></td>
                                        <td class='driveras'><?php //echo '0';     ?></td>
                                        <td class='driverer'><?php //echo '0';     ?></td>
                                        <td class='driverbk'><?php //echo $BKDriverO[0]['tcount'];     ?></td>
                                        <td><?php
                                            if ($ADTOrderO[0]['adt'] != '') {
                                                $adt = str_replace(".0000", "", $ADTOrderO[0]['adt']);
                                                echo date('H:i', strtotime($adt));
                                            } else {
                                                echo 0;
                                            }
                                            ?></td>
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
<!--                                <thead>
                                <th>Ord#-Restaurant <br/>Sector-Address </th>
                                <th>Status<br/>Time</th>
                                <th>Action</th>
                                </thead>-->
                                <tbody  class='tbodycls'>

                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-7 nopadding">
                            <div class="orders_data_dashboard" style="padding:0;">
                                <div class="form-group" style="margin-top:10px;">
                                    <!--                                    <label for="Mapcontentlist">&nbsp;</label>-->
                                    <select id="Mapcontentlist" class="form-control" style="width:50%;">
                                        <option value="allmapdata">All Active</option>
                                        <option value="drivers">Active Drivers</option>
                                        <option value="restaurants">Active Restaurants</option>
                                        <option value="customers">Customers</option>
                                        <option value="inactivedrivers">Inactive Drivers</option>
                                        <option value="inactiverestaurants">Inactive Restaurants</option>
                                        <option value="inactiveallmapdata">All Inactive</option>

                                    </select>
                                </div>

                                <!-- <ul>
                                     <li> <button role="button" class="btn btn-sm btn-default raised setclassmap " id="drivers" onClick="getmapdata('drivers')">Drivers</button></li>
                                     <li> <button role="button" class="btn btn-sm btn-default raised setclassmap " id="restaurants" onClick="getmapdata('restaurants')">Restaurant</button></li>
                                     <li> <button role="button" class="btn btn-sm btn-default raised setclassmap" id="customers" onClick="getmapdata('customers')">Customers</button></li>
                                     <li> <button role="button" class="btn btn-sm btn-default raised setclassmap active all" id="allmapdata" onClick="getmapdata('allmapdata')">All</button></li>
                                 </ul> -->

                            </div>
                            <div class="panel-heading location-map" style="background:none;">
                                <div class="google-map-wrap" style="width:100%;">
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
                            <table id="driver_main_list" style="margin-top: 60px;">
<!--                                <thead>
                                <th>Name<br/>Ord#</th>
                                
                                <th>Status<br/>Time</th>
                                </thead>-->
                                <tbody  class='tbodycls'>

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
                        </div>

                    <?php } ?>
                </div>



                <div style="clear:both;"></div>
                <div id="assign_form" class="modal fade delete_form text-left" role="dialog" style="z-index: 10500;">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">

                            <div class="modal-header">
                                
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: 0px;"><span aria-hidden="true">X</span></button>
                                <h4 class="modal-title">Assign Order</h4>
                                <span class="assignDrivermessage"></span>
                            </div>

                            <form role="form" name="delete_form" id="delete_form1" method="post" action="" class="margin0">

                                <div class="modal-body">
                                    <?php
                                    if (count($drivers) > 0) {
                                        $i = 0;
                                        echo "<select name='driverid' class='filter-by-text form-control'>";
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
                                    <input type="hidden" name="iStatusCode" id="iStatusCode"  value="" />
                                    <input type="hidden" name="Olddriverid" id="Olddriverid"  value="" />
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



                <!-- Assign modal close  Order details modal pop up -->
                <div class="modal bd-example-modal-lg" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Order <span class="appendpopdataheader"></span></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row clearfix appendpopdata">
                                    
                                </div>
                            </div>
                            <div class="modal-footer" style="border:none;">
                                <div class="text-center">
                                     <span class="Orderstatuschangemessage"></span>
                                </div>
                                <span class="appendpopdatafooter"></span>
<!--                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>-->
                            </div>
                        </div>
                    </div>
                </div>
                
                
                
                <!-- Driver Popup Modal Popup -->
                
                
                <div id="driver_model" class="modal fade" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Driver Details</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row clearfix appendpopdataDriver">
                                    
                                </div>
                            </div>
                            <div class="modal-footer" style="border:none;">
                                
<!--                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>-->
                            </div>
                        </div>
                    </div>
                </div>



                <!--END PAGE CONTENT -->
            </div>

            <? include_once('footer.php'); ?>

            <script>
                
                 $(document).ready(function () {
                    $("#assign_booking").click(function(e) {
                        e.preventDefault();
                        $.ajax({
                            type: "POST",
                            url: "chowcallupdateorderstatus.php",
                            dataType: "html",
                            data: $("#delete_form1").serialize(), 
                            success: function (response)
                            {
                            
                                $(".assignDrivermessage").text(response); 
                                setInterval(function () {  $(".assignDrivermessage").text(" "); }, 5000);
                                location.reload();
                            }
                        });
                    });
                }); 
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
                    var drpval = $('#Mapcontentlist :selected').val();
                    $.ajax({
                        type: "POST",
                        url: "getMapContentLists.php",
                        dataType: "json",
                        data: {type: drpval},
                        success: function (dataHtml) {
                            newLocations = dataHtml.locations;
                            if (newLocations == "") {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: '',
                                    lng: '',
                                    mapTypeId: 'terrain'
                                            //scrollwheel: false,
                                            //draggable: ! is_touch_device
                                });
                            } else {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: '34.758792',
                                    lng: '-77.418553',
                                    mapTypeId: 'terrain'
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
                                        content: '<table><tr><td>&nbsp;&nbsp;Name: </td><td><b>' + newName + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>'
                                    }
                                });
                                markers.push(marker);
                                // alert(markers.push(marker));
                            }
                            // map.fitLatLngBounds(bounds);

                            latlngJacksonville = new google.maps.LatLng(34.758792, -77.418553);
                            map.setZoom(12);
                            map.panTo(latlngJacksonville);

                            $.ajax({
                                type: "POST",
                                url: "get_available_driver_list_in_operationdashboard.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    var response = $.parseJSON(dataHtml2);
                                    $('#driver_main_list').show();
                                    $('#driver_main_list tbody').html(response.res);
                                    $('.driverry').text(response.ry);
                                    $('.driverttl').text(response.ttldriver);
                                    $('.driveras').text(response.as);
                                    $('.driverer').text(response.er);
                                    $('.driverbk').text(response.bk);

                                }, error: function (dataHtml2) {

                                }
                            });
                            $.ajax({
                                type: "POST",
                                url: "get_today_orders.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    var response = $.parseJSON(dataHtml2);
                                    $('#orders_main_list').show();
                                    $('#orders_main_list tbody').html(response.res);
                                    $('.totalodersStatus').html(response.totalodersStatus);

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


                $('#Mapcontentlist').on('change', function () {

                    type = this.value;
                    $('.setclassmap').removeClass('active');
                    $("#" + type).addClass('active');
                    $.ajax({
                        type: "POST",
                        url: "getMapContentLists.php",
                        dataType: "json",
                        data: {type: type},
                        success: function (dataHtml) {
                            newLocations = dataHtml.locations;
                            if (newLocations == "") {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: '',
                                    lng: '',
                                    mapTypeId: 'terrain'
                                            //scrollwheel: false,
                                            //draggable: ! is_touch_device
                                });
                            } else {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: '34.758792',
                                    lng: '-77.418553',
                                    mapTypeId: 'terrain'
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
                                        content: '<table><tr><td>&nbsp;&nbsp;Name: </td><td><b>' + newName + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>'
                                    }
                                });
                                markers.push(marker);
                                // alert(markers.push(marker));
                            }
                            // map.fitLatLngBounds(bounds);

                            latlngJacksonville = new google.maps.LatLng(34.758792, -77.418553);
                            map.setZoom(12);
                            map.panTo(latlngJacksonville);

                            $.ajax({
                                type: "POST",
                                url: "get_available_driver_list_in_operationdashboard.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    var response = $.parseJSON(dataHtml2);
                                    $('#driver_main_list').show();
                                    $('#driver_main_list tbody').html(response.res);
                                    $('.driverry').text(response.ry);
                                    $('.driverttl').text(response.ttldriver);
                                    $('.driveras').text(response.as);
                                    $('.driverer').text(response.er);
                                    $('.driverbk').text(response.bk);
                                }, error: function (dataHtml2) {

                                }
                            });
                            $.ajax({
                                type: "POST",
                                url: "get_today_orders.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    var response = $.parseJSON(dataHtml2);
                                    $('#orders_main_list').show();
                                    $('#orders_main_list tbody').html(response.res);
                                    $('.totalodersStatus').html(response.totalodersStatus);

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


                function getmapdata(type)
                {

                    $('.setclassmap').removeClass('active');
                    $("#" + type).addClass('active');
                    $.ajax({
                        type: "POST",
                        url: "getMapContentLists.php",
                        dataType: "json",
                        data: {type: type},
                        success: function (dataHtml) {
                            newLocations = dataHtml.locations;
                            if (newLocations == "") {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: '',
                                    lng: '',
                                    mapTypeId: 'terrain'
                                            //scrollwheel: false,
                                            //draggable: ! is_touch_device
                                });
                            } else {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: '34.758792',
                                    lng: '-77.418553',
                                    mapTypeId: 'terrain'
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
                                        content: '<table><tr><td>&nbsp;&nbsp;Name: </td><td><b>' + newName + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>'
                                    }
                                });
                                markers.push(marker);
                                // alert(markers.push(marker));
                            }
                            // map.fitLatLngBounds(bounds);

                            latlngJacksonville = new google.maps.LatLng(34.758792, -77.418553);
                            map.setZoom(12);
                            map.panTo(latlngJacksonville);

                            $.ajax({
                                type: "POST",
                                url: "get_available_driver_list_in_operationdashboard.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    var response = $.parseJSON(dataHtml2);
                                    $('#driver_main_list').show();
                                    $('#driver_main_list tbody').html(response.res);
                                    $('.driverry').text(response.ry);
                                    $('.driverttl').text(response.ttldriver);
                                    $('.driveras').text(response.as);
                                    $('.driverer').text(response.er);
                                    $('.driverbk').text(response.bk);
                                }, error: function (dataHtml2) {

                                }
                            });
                            $.ajax({
                                type: "POST",
                                url: "get_today_orders.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    var response = $.parseJSON(dataHtml2);
                                    $('#orders_main_list').show();
                                    $('#orders_main_list tbody').html(response.res);
                                    $('.totalodersStatus').html(response.totalodersStatus);

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
                }
                /* Map Reload after a minute */
                setInterval(function () {
                    //newType = $("#newType").val();
                    var drpval = $('#Mapcontentlist :selected').val();
                    $.ajax({
                        type: "POST",
                        url: "getMapContentLists.php",
                        dataType: "json",
                        data: {type: drpval},
                        success: function (dataHtml) {
                            newLocations = dataHtml.locations;
                            if (newLocations == "") {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: '',
                                    lng: '',
                                    mapTypeId: 'terrain'
                                            //scrollwheel: false,
                                            //draggable: ! is_touch_device
                                });
                            } else {
                                map = new GMaps({
                                    el: '#google-map',
                                    lat: '34.758792',
                                    lng: '-77.418553',
                                    mapTypeId: 'terrain'
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
                                        content: '<table><tr><td>&nbsp;&nbsp;Name: </td><td><b>' + newName + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>'
                                    }
                                });
                                markers.push(marker);
                                // alert(markers.push(marker));
                            }
                            // map.fitLatLngBounds(bounds);

                            latlngJacksonville = new google.maps.LatLng(34.758792, -77.418553);
                            map.setZoom(12);
                            map.panTo(latlngJacksonville);

                            $.ajax({
                                type: "POST",
                                url: "get_available_driver_list_in_operationdashboard.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    var response = $.parseJSON(dataHtml2);
                                    $('#driver_main_list').show();
                                    $('#driver_main_list tbody').html(response.res);
                                    $('.driverry').text(response.ry);
                                    $('.driverttl').text(response.ttldriver);
                                    $('.driveras').text(response.as);
                                    $('.driverer').text(response.er);
                                    $('.driverbk').text(response.bk);
                                }, error: function (dataHtml2) {

                                }
                            });
                            $.ajax({
                                type: "POST",
                                url: "get_today_orders.php",
                                dataType: "html",
                                data: {type: ''},
                                success: function (dataHtml2) {
                                    var response = $.parseJSON(dataHtml2);
                                    $('#orders_main_list').show();
                                    $('#orders_main_list tbody').html(response.res);
                                    $('.totalodersStatus').html(response.totalodersStatus);

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

                }, 60000);
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



                    $.ajax({
                        type: "POST",
                        url: "get_available_driver_list_in_operationdashboard.php",
                        dataType: "html",
                        data: {type: type},
                        success: function (dataHtml2) {
                            var response = $.parseJSON(dataHtml2);
                            $('#driver_main_list').show();
                            $('#driver_main_list tbody').html(response.res);
                            $('.driverry').text(response.ry);
                            $('.driverttl').text(response.ttldriver);
                            $('.driveras').text(response.as);
                            $('.driverer').text(response.er);
                            $('.driverbk').text(response.bk);
                        }, error: function (dataHtml2) {

                        }
                    });
                    $.ajax({
                        type: "POST",
                        url: "get_today_orders.php",
                        dataType: "html",
                        data: {type: set},
                        success: function (dataHtml2) {
                            var response = $.parseJSON(dataHtml2);
                            $('#orders_main_list').show();
                            $('#orders_main_list tbody').html(response.res);
                            $('.totalodersStatus').html(response.totalodersStatus);
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
                            var response = $.parseJSON(dataHtml2);
                            $('#driver_main_list').show();
                            $('#driver_main_list tbody').html(response.res);
                            $('.driverry').text(response.ry);
                            $('.driverttl').text(response.ttldriver);
                            $('.driveras').text(response.as);
                            $('.driverer').text(response.er);
                            $('.driverbk').text(response.bk);
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
                     var iStatusCode = $(this).data('status');
                     var Olddriverid = $(this).data('olddriver');
                     //alert(Olddriverid);
                    $('#orderid').val(order_id);
                    
                    $('#iStatusCode').val(iStatusCode);
                    $('#Olddriverid').val(Olddriverid);
                    $('#assign_form').modal('show');
                });


                $('#IOrderStatus').on('change', function () {

                    allorders('', $(this).val());
                });
                $(document).on('click', '.tdoperationcls', function () {
                    var orderId = $(this).attr('attr');
                    $.ajax({
                        type: "POST",
                        url: "get_orderinformation_popup.php",
                        //dataType: "html",
                        dataType: "json",
                        data: {orderId: orderId},
                        success: function (dataHtml2) {
                            $('.appendpopdata').html(dataHtml2.res);
                            $('.appendpopdataheader').html(dataHtml2.appendpopdataheader);
                            $('.appendpopdatafooter').html(dataHtml2.appendpopdatafooter);
                            $('.bd-example-modal-lg').modal('show');
                            //$("#driver_popup").show("slide", {direction: "right"}, 700);
                        }, error: function (dataHtml2) {

                        }
                    });
                });
                
                
                $(document).on('click', '.opdrivernamecls', function () {
                    var iDriverId = $(this).attr('attr');
                    $.ajax({
                        type: "POST",
                        url: "getdriverOrderDetialsOD.php",
                       dataType: "html",
                       // dataType: "json",
                        data: {iDriverId: iDriverId},
                        success: function (dataHtml2) {
                            $('.appendpopdataDriver').html(dataHtml2);
                            $('#driver_model').modal('show');
                            //$("#driver_popup").show("slide", {direction: "right"}, 700);
                        }, error: function (dataHtml2) {

                        }
                    });
                });







                function makedriveroffline(iDriverId)
                {
                    var result = confirm("Do you really want to make driver offline?");
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: "makedriveroffline.php",
                            dataType: "html",
                            data: {iDriverId: iDriverId},
                            success: function (response) {
                                if (response == 1)
                                {


                                    //refersh Content



                                    var drpval = $('#Mapcontentlist :selected').val();
                                    $.ajax({
                                        type: "POST",
                                        url: "getMapContentLists.php",
                                        dataType: "json",
                                        data: {type: drpval},
                                        success: function (dataHtml) {
                                            newLocations = dataHtml.locations;
                                            if (newLocations == "") {
                                                map = new GMaps({
                                                    el: '#google-map',
                                                    lat: '',
                                                    lng: '',
                                                    mapTypeId: 'terrain'
                                                            //scrollwheel: false,
                                                            //draggable: ! is_touch_device
                                                });
                                            } else {
                                                map = new GMaps({
                                                    el: '#google-map',
                                                    lat: '34.758792',
                                                    lng: '-77.418553',
                                                    mapTypeId: 'terrain'
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
                                                        content: '<table><tr><td>&nbsp;&nbsp;Name: </td><td><b>' + newName + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>'
                                                    }
                                                });
                                                markers.push(marker);
                                                // alert(markers.push(marker));
                                            }
                                            // map.fitLatLngBounds(bounds);

                                            latlngJacksonville = new google.maps.LatLng(34.758792, -77.418553);
                                            map.setZoom(12);
                                            map.panTo(latlngJacksonville);

                                            $.ajax({
                                                type: "POST",
                                                url: "get_available_driver_list_in_operationdashboard.php",
                                                dataType: "html",
                                                data: {type: ''},
                                                success: function (dataHtml2) {
                                                    var response = $.parseJSON(dataHtml2);
                                                    $('#driver_main_list').show();
                                                    $('#driver_main_list tbody').html(response.res);
                                                    $('.driverry').text(response.ry);
                                                    $('.driverttl').text(response.ttldriver);
                                                    $('.driveras').text(response.as);
                                                    $('.driverer').text(response.er);
                                                    $('.driverbk').text(response.bk);
                                                }, error: function (dataHtml2) {

                                                }
                                            });
                                            $.ajax({
                                                type: "POST",
                                                url: "get_today_orders.php",
                                                dataType: "html",
                                                data: {type: ''},
                                                success: function (dataHtml2) {
                                                    var response = $.parseJSON(dataHtml2);
                                                    $('#orders_main_list').show();
                                                    $('#orders_main_list tbody').html(response.res);
                                                    $('.totalodersStatus').html(response.totalodersStatus);

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

                                    //refresh content close






                                } else
                                {
                                    alert('Could not make Driver Offline as he is in processing order.');
                                }

                            }, error: function (response) {

                            }
                        });
                    }
                }



            </script>




            <script>
                var elem = document.getElementById("content");

                document.addEventListener("keydown", event => {
                    switch (event.key) {
                        case "Escape":

                            if (document.exitFullscreen) {
                                document.exitFullscreen();
                            } else if (document.mozCancelFullScreen) { /* Firefox */
                                document.mozCancelFullScreen();
                            } else if (document.webkitExitFullscreen) { /* Chrome, Safari and Opera */
                                document.webkitExitFullscreen();
                            } else if (document.msExitFullscreen) { /* IE/Edge */
                                document.msExitFullscreen();
                            }
                            $('#content').css('overflow', '');
                            $('.openclose').attr('onclick', 'openFullscreen()');
                            var $window = $(window);
                            mapWidth();
                            $(window).resize(mapWidth);

                            break;
                    }
                });
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
                    $('#content').css('overflow', 'auto');

                    $('.openclose').attr('onclick', 'closeFullscreen()');
                    var $window = $(window);
                    mapWidth();
                    $(window).resize(mapWidth);

                }
                function closeFullscreen() {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.mozCancelFullScreen) { /* Firefox */
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) { /* Chrome, Safari and Opera */
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) { /* IE/Edge */
                        document.msExitFullscreen();
                    }
                    $('#content').css('overflow', '');
                    $('.openclose').attr('onclick', 'openFullscreen()');
                    var $window = $(window);
                    mapWidth();
                    $(window).resize(mapWidth);
                }




                var $window = $(window);
                function mapWidth() {
                    var size = $('.google-map-wrap').width();
                    $('.google-map').css({width: size + 'px', height: (size / 2) + 'px'});
                }
                mapWidth();
                $(window).resize(mapWidth);
            </script> 
    </body>
    <!-- END BODY-->
</html>
<?php $obj->MySQLClose(); ?>