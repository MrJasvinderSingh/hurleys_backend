<?php

error_reporting(0);
date_default_timezone_set('America/New_York');
include_once('common.php');
include_once('generalFunctions.php');

class webalgorithm {
    public function getLatestOrderDetails() {
        global $generalobj, $obj;
        //date('Y-m-d').
        $freeDriverList = '';
        $DayStart = date('Y-m-d') . ' 00:00:01';
        $DayEnd = date('Y-m-d H:i:s');
        //$DayEnd = date('Y-m-d') . ' 23:59:59';
        $orderdetails = "SELECT * FROM `orders` WHERE iStatusCode = 2 AND tOrderRequestDate BETWEEN '$DayStart' AND '$DayEnd' AND '$DayEnd' > pickuptime;"; // 
        $OrderQueries = $obj->MySQLSelect($orderdetails);
        //echo "<pre>"; print_r($OrderQueries); echo "</pre>"; exit;
        $log = PHP_EOL . '' . PHP_EOL;
        $log .= PHP_EOL . "-----------------------------------------  Current Time ----------------------------" . PHP_EOL;
        $log .= PHP_EOL . date('Y-m-d H:i:s a') . PHP_EOL;
        $log .= PHP_EOL . "--------------------  Process Start ----------------" . PHP_EOL;
        if (!empty($OrderQueries)) {
            foreach ($OrderQueries as $OrdersD) {
                $isDriverAssigned = 0;
                $SameLocDriver = 0;
                $NearByDriver = 0;

                $checkOrderStaus = 1;
                $iCompanyId = $OrdersD['iCompanyId'];
                $AllDriverList = '';
                $log .= PHP_EOL . "-------------------- Order Id is : $OrdersD[iOrderId] ----------------" . PHP_EOL;
                $checkAlreadyNotificstionSent = CheckNotificaTionsent($OrdersD['iOrderId']);
                if ($checkAlreadyNotificstionSent) {
                    $getOrderAllLatlong = GetLatLongOrderid($OrdersD['iOrderId']);
                    //echo "<pre>"; print_r($getOrderAllLatlong); echo "</pre>";

                    $origin = $getOrderAllLatlong['PickUpLatitude'] . ',' . $getOrderAllLatlong['PickUpLongitude'];
                    $destination = $getOrderAllLatlong['DestLatitude'] . ',' . $getOrderAllLatlong['DestLongitude'];
                    $mat = $getOrderAllLatlong['Mat'];
                    $mon = $getOrderAllLatlong['Mon'];
                    //echo "Working";
                    // $DriverInSameLocations = GetDriverListFromSamePickup($iCompanyId);
                    $DriverInSameLocations = GetDriverListSameDeliveryLocation($getOrderAllLatlong['DestLatitude'], $getOrderAllLatlong['DestLongitude']);
                    //echo "<pre>Driver List: "; print_r($DriverInSameLocations); echo "</pre>"; exit;
                    // echo "Working";
                    if (!empty($DriverInSameLocations)) {
                        foreach ($DriverInSameLocations as $DriverInSameLocation) {
                            if ($isDriverAssigned == 0) {
                                $AllDriverList .= $DriverInSameLocation['iDriverId'] . ',';
                                $checkDeclineRequest = GetDriverDeclineRequest($DriverInSameLocation['iDriverId'], $OrdersD['iOrderId']);
                                if ($checkDeclineRequest == 'NO') {
                                    
                                    $checkFriverExistingRequest = GetDriverStatusIFonGoing($DriverInSameLocation['iDriverId']);
                                    $countNoofOrder = CountNoOfOrderByDriverID($DriverInSameLocation['iDriverId']);
                                    if (($checkFriverExistingRequest == 'NO') && ($countNoofOrder < $mon)) {

//                            $previousorderTimeDiff = GetTimeLeftPrevOrder($DriverInSameLocation['iDriverId']);
//                            $MoreWaitingTime = GetTimeDiffInMinutes($getOrderAllLatlong['pickuptime']);
//                            
//                            $log .= PHP_EOL . "-------------------- Prev order time left ----------------" . PHP_EOL;
//                            $log .= PHP_EOL . $previousorderTimeDiff . PHP_EOL;
//                            $log .= PHP_EOL . "-------------------- More time for preparing order ----------------" . PHP_EOL;
//                            $log .= PHP_EOL . $MoreWaitingTime . PHP_EOL;
//                            $totaltime = $previousorderTimeDiff + $MoreWaitingTime + 2;
                                        //  $driverOrderStacks = GetOrderStackDriver($iCompanyId, $DriverInSameLocation['iDriverId']);
                                        $driverOrderStacks = GetOrderStackDriverWOC($DriverInSameLocation['iDriverId']);
                                        $count = count($driverOrderStacks);
                                        $Ccount = $count - 1;

                                        $PickoneLat = $driverOrderStacks[$Ccount]['PickUpLatitude'];
                                        $PickoneLong = $driverOrderStacks[$Ccount]['PickUpLongitude'];
                                        $PickOneTime = $driverOrderStacks[$Ccount]['cookingtime'];
                                        $PickTwoLat = $getOrderAllLatlong['PickUpLatitude'];
                                        $PickTwoLong = $getOrderAllLatlong['PickUpLongitude'];
                                        $PickTwoTime = $getOrderAllLatlong['cookingtime'];
                                        $stisfywithmat = 'yes';
                                        $pickOneCompany = $driverOrderStacks[$Ccount]['iCompanyId'];
                                        $AddAdditionalTime = 1;
                                        if ($iCompanyId == $pickOneCompany) {
                                            $AddAdditionalTime = 0;
                                        }
                                        //  $CheckPickupSatisfy = CheckTimeDiffBtwTwoLocation($PickoneLat, $PickTwoLat, $PickoneLong, $PickTwoLong, $PickOneTime, $PickTwoTime, $AddAdditionalTime);
                                        $AddAdditionalTime = 1;

                                        if ($getOrderAllLatlong['DestLatitude'] == $driverOrderStacks[0]['DestLatitude']) {
                                            $AddAdditionalTime = 0;
                                        }
                                        $CheckDeliveryWPickupSatisfy = CheckTimeDiffBtwTwoLocation($PickTwoLat, $driverOrderStacks[0]['DestLatitude'], $PickTwoLong, $driverOrderStacks[0]['DestLongitude'], $PickTwoTime, $driverOrderStacks[0]['mattime'], $AddAdditionalTime);
                                        //($CheckPickupSatisfy == "yes") && (  //)
                                        if ($CheckDeliveryWPickupSatisfy == "yes") {
                                            $deliveryOPtions = array();
                                            for ($i = 0; $i < $count; $i++) {
                                                $deliveryOPtions[$i] = array(
                                                    'lat' => $driverOrderStacks[$i]['DestLatitude'],
                                                    'long' => $driverOrderStacks[$i]['DestLongitude'],
                                                    'time' => $driverOrderStacks[$i]['mattime'],
                                                );
                                            }
                                            $deliveryOPtions[$count] = array(
                                                'lat' => $getOrderAllLatlong['DestLatitude'],
                                                'long' => $getOrderAllLatlong['DestLongitude'],
                                                'time' => $getOrderAllLatlong['mattime'],
                                            );

                                            //  print_r($deliveryOPtions); 
                                            $countDO = count($deliveryOPtions);
                                            $countDO = $countDO - 1;
                                            for ($j = 0; $j < $countDO; $j++) {
                                                $nex = $j + 1;
                                                $AddAdditionalTime = 1;

                                                if ($deliveryOPtions[$j]['lat'] == $deliveryOPtions[$nex]['lat']) {
                                                    $AddAdditionalTime = 0;
                                                }

                                                $CheckDeliverySatisfy = CheckTimeDiffBtwTwoLocation($deliveryOPtions[$j]['lat'], $deliveryOPtions[$nex]['lat'], $deliveryOPtions[$j]['long'], $deliveryOPtions[$nex]['long'], $deliveryOPtions[$j]['time'], $deliveryOPtions[$nex]['time'], $AddAdditionalTime);
                                                if ($CheckDeliverySatisfy == 'no') {
                                                    $stisfywithmat = 'no';
                                                    $log .= PHP_EOL . "Check Drop With MatTime Same Location Not satisfy......!" . PHP_EOL;
                                                } else {
                                                    $log .= PHP_EOL . "!...... Check Drop With MatTime Same Location satisfy......!" . PHP_EOL;
                                                }
                                            }

                                            $log .= PHP_EOL . "!......Check Pickup With PickupTime  Same Location satisfy......!" . PHP_EOL;
                                        } else {
                                            $stisfywithmat = 'no';
                                            $log .= PHP_EOL . "Check Pickup With PickupTime Same Location Not satisfy......!" . PHP_EOL;
                                        }


                                        if ($stisfywithmat != 'no') {
                                            $Assignorder = AssignOrderToDriver($OrdersD['iOrderId'], $DriverInSameLocation['iDriverId']);
                                            $isDriverAssigned = $Assignorder;
                                            $SameLocDriver = $Assignorder;

                                            $log .= PHP_EOL . "Same Location Driver with id: " . $DriverInSameLocation['iDriverId'] . " is assigned to order." . $OrdersD['iOrderId'] . PHP_EOL;
                                        } else {
                                            $log .= PHP_EOL . "Mat Exceeded for order no :" . $OrdersD['iOrderId'] . " with Driver id " . $DriverInSameLocation['iDriverId'] . "Found on same location......!" . PHP_EOL;
                                        }

                                        unset($deliveryOPtions);
                                        unset($driverOrderStacks);
                                    } else {

                                        $log .= PHP_EOL . "Same Delivery Location Driver Found  for order no :" . $OrdersD['iOrderId'] . " with Driver id " . $DriverInSameLocation['iDriverId'] . " But he is Ongoing Trip......!" . PHP_EOL;
                                    }
                                } //checkifdeclinedandtimeout
                                else {
                                    $log .= PHP_EOL . "Driver with Driver id " . $DriverInSameLocation['iDriverId'] . " Declined the order ......!" . PHP_EOL;
                                }
                            }
                        }
                    } else {

                        $log .= PHP_EOL . "No Driver Found on same location......!" . PHP_EOL;
                    }

                    if (($isDriverAssigned == 0) && ($SameLocDriver == 0)) {
                        $DriverInNearBYLocations = GetDriverListNearBYLocation($getOrderAllLatlong['PickUpLatitude'], $getOrderAllLatlong['PickUpLongitude'], $AllDriverList);

                        if (!empty($DriverInNearBYLocations)) {
                            foreach ($DriverInNearBYLocations as $DriverInNearBYLocation) {
                                if ($isDriverAssigned == 0) {
                                    $AllDriverList .= $DriverInNearBYLocation['iDriverId'] . ',';
                                    
                                    $checkDeclineRequest = GetDriverDeclineRequest($DriverInNearBYLocation['iDriverId'], $OrdersD['iOrderId']);
                                if ($checkDeclineRequest == 'NO')
                                {
                                    $checkFriverExistingRequest = GetDriverStatusIFonGoing($DriverInNearBYLocation['iDriverId']);
                                    $countNoofOrder = CountNoOfOrderByDriverID($DriverInNearBYLocation['iDriverId']);
                                    if (($checkFriverExistingRequest == 'NO') && ($countNoofOrder < $mon)) {


//                                $previousorderTimeDiff = GetTimeLeftPrevOrder($DriverInNearBYLocation['iDriverId']);
//                                $MoreWaitingTime = GetTimeDiffInMinutes($getOrderAllLatlong['pickuptime']);
//                                
//                                $log .= PHP_EOL . "-------------------- Prev order time left ----------------" . PHP_EOL;
//                                $log .= PHP_EOL . $previousorderTimeDiff . PHP_EOL;
//                                $log .= PHP_EOL . "-------------------- More time for preparing order ----------------" . PHP_EOL;
//                                $log .= PHP_EOL . $MoreWaitingTime . PHP_EOL;
//                                $totaltime = $previousorderTimeDiff + $MoreWaitingTime + 2;

                                        $driverOrderStacks = GetOrderStackDriverWOC($DriverInNearBYLocation['iDriverId']);





                                        $count = count($driverOrderStacks);
                                        $Ccount = $count - 1;
                                        $PickoneLat = $driverOrderStacks[$Ccount]['PickUpLatitude'];
                                        $PickoneLong = $driverOrderStacks[$Ccount]['PickUpLongitude'];
                                        $PickOneTime = $driverOrderStacks[$Ccount]['cookingtime'];
                                        $PickTwoLat = $getOrderAllLatlong['PickUpLatitude'];
                                        $PickTwoLong = $getOrderAllLatlong['PickUpLongitude'];
                                        $PickTwoTime = $getOrderAllLatlong['cookingtime'];
                                        $pickOneCompany = $driverOrderStacks[$Ccount]['iCompanyId'];
                                        $AddAdditionalTime = 1;
                                        if ($iCompanyId == $pickOneCompany) {
                                            $AddAdditionalTime = 0;
                                        }

                                        $stisfywithmat = 'yes';
                                        // $CheckPickupSatisfy = CheckTimeDiffBtwTwoLocation($PickoneLat, $PickTwoLat, $PickoneLong, $PickTwoLong, $PickOneTime, $PickTwoTime, $AddAdditionalTime);

                                        $AddAdditionalTime = 1;

                                        if ($getOrderAllLatlong['DestLatitude'] == $driverOrderStacks[0]['DestLatitude']) {
                                            $AddAdditionalTime = 0;
                                        }

                                        $CheckDeliveryWPickupSatisfy = CheckTimeDiffBtwTwoLocation($PickTwoLat, $driverOrderStacks[0]['DestLatitude'], $PickTwoLong, $driverOrderStacks[0]['DestLongitude'], $PickTwoTime, $driverOrderStacks[0]['mattime'], $AddAdditionalTime);
                                        //($CheckPickupSatisfy == "yes") && ()
                                        if ($CheckDeliveryWPickupSatisfy == "yes") {
                                            $deliveryOPtions = array();
                                            for ($i = 0; $i < $count; $i++) {
                                                $deliveryOPtions[$i] = array(
                                                    'lat' => $driverOrderStacks[$i]['DestLatitude'],
                                                    'long' => $driverOrderStacks[$i]['DestLongitude'],
                                                    'time' => $driverOrderStacks[$i]['mattime'],
                                                );
                                            }
                                            $deliveryOPtions[$count] = array(
                                                'lat' => $getOrderAllLatlong['DestLatitude'],
                                                'long' => $getOrderAllLatlong['DestLongitude'],
                                                'time' => $getOrderAllLatlong['mattime'],
                                            );

                                            //  print_r($deliveryOPtions); 
                                            $countDO = count($deliveryOPtions);
                                            $countDO = $countDO - 1;
                                            for ($j = 0; $j < $countDO; $j++) {
                                                $nex = $j + 1;

                                                $AddAdditionalTime = 1;

                                                if ($deliveryOPtions[$j]['lat'] == $deliveryOPtions[$nex]['lat']) {
                                                    $AddAdditionalTime = 0;
                                                }

                                                $CheckDeliverySatisfy = CheckTimeDiffBtwTwoLocation($deliveryOPtions[$j]['lat'], $deliveryOPtions[$nex]['lat'], $deliveryOPtions[$j]['long'], $deliveryOPtions[$nex]['long'], $deliveryOPtions[$j]['time'], $deliveryOPtions[$nex]['time']);
                                                if ($CheckDeliverySatisfy == 'no') {
                                                    $stisfywithmat = 'no';
                                                    $log .= PHP_EOL . "Check Drop With MatTime NearBY Location Not satisfy......!" . PHP_EOL;
                                                } else {
                                                    $log .= PHP_EOL . "!...... Check Drop With MatTime NearBY Location satisfy......!" . PHP_EOL;
                                                }
                                            }

                                            $log .= PHP_EOL . "!......Check Pickup With PickupTime  NearBY Location satisfy......!" . PHP_EOL;
                                        } else {
                                            $stisfywithmat = 'no';
                                            $log .= PHP_EOL . "Check Pickup With PickupTime NearBY Location Not satisfy......!" . PHP_EOL;
                                        }
                                        if ($stisfywithmat != 'no') {
                                            $Assignorder = AssignOrderToDriver($OrdersD['iOrderId'], $DriverInNearBYLocation['iDriverId']);
                                            $isDriverAssigned = $Assignorder;
                                            $NearByDriver = $Assignorder;

                                            $log .= PHP_EOL . "Near BY Driver with id : " . $DriverInNearBYLocation['iDriverId'] . " is assigned to order" . $OrdersD['iOrderId'] . PHP_EOL;
                                            //   sleep(15);
                                        } else {
                                            $log .= PHP_EOL . "Max Exceeded for order no :" . $OrdersD['iOrderId'] . " with Driver id " . $DriverInNearBYLocation['iDriverId'] . "Found on Near By Location......!" . PHP_EOL;
                                        }

                                        unset($deliveryOPtions);
                                        unset($driverOrderStacks);
                                    } else {

                                        $log .= PHP_EOL . "Near BY Driver found  for order no :" . $OrdersD['iOrderId'] . " with Driver id " . $DriverInNearBYLocation['iDriverId'] . " But he is Ongoing Trip ......!" . PHP_EOL;
                                    }
                                    
                                    
                                }
                                else
                                {
                                    $log .= PHP_EOL . "Near BY Driver with Driver id " . $DriverInNearBYLocation['iDriverId'] . " Declined the order ......!" . PHP_EOL;
                                }
                                    
                                }
                            }
                        } else {
                            $log .= PHP_EOL . "No Driver Found on near by location......!" . PHP_EOL;
                        }
                    }



                    if (($isDriverAssigned == 0) && ($NearByDriver == 0)) {
                        $isDriverAssigned = AssignOrderFreeDriver($OrdersD['iOrderId'], $freeDriverList);

                        if ($isDriverAssigned['status'] == 1) {
                            $freeDriverList .= $isDriverAssigned['iDriverId'] . ',';
                            $log .= PHP_EOL . "Free Driver is assigned to order" . $OrdersD['iOrderId'] . PHP_EOL;
                        }
                        else {

                            $log .= PHP_EOL . "........No Free Driver Found......!" . PHP_EOL;
                        }
                        //sleep(15);
                        //this is pending check for free driver list and assigne order to that driver
                    }

                    if ($isDriverAssigned == 0) {

                        $log .= PHP_EOL . "..........No Driver Found Anywhere......!" . PHP_EOL;
//                    try {
//                    saveAlgoLog($log);
//                } catch (Exception $e) {
//                    
//                }
//                    exit;
                    }
                    $log .= PHP_EOL . "-------------------------------IN LOOP-----------------------------" . PHP_EOL;
                } else {
                    $log .= PHP_EOL . "-------------------------------Notification Already Sent to Device-----------------------------" . PHP_EOL;
                }
            }
        } else {

            $log .= PHP_EOL . "-------------------------------No Order Found-----------------------------" . PHP_EOL;
        }

        try {
            saveAlgoLog($log);
        } catch (Exception $e) {
            
        }
        
       
        $obj->MySQLClose();
         echo Done;
    }

    public function getLatestOrderDetails_OLD() {
        global $generalobj, $obj;
        //date('Y-m-d').

        $DayStart = date('Y-m-d') . ' 00:00:01';
        $DayEnd = date('Y-m-d H:i:s');
        //$DayEnd = date('Y-m-d') . ' 23:59:59';
        $orderdetails = "SELECT * FROM `orders` WHERE iStatusCode = 2 AND tOrderRequestDate BETWEEN '$DayStart' AND '$DayEnd' ;"; // AND '$DayEnd' > pickuptime
        $OrderQueries = $obj->MySQLSelect($orderdetails);
        //echo "<pre>"; print_r($OrderQueries); echo "</pre>"; exit;
        $log = '';
        $log .= PHP_EOL . "--------------------  Current Time ----------------" . PHP_EOL;
        $log .= PHP_EOL . date('Y-m-d H:i:s a') . PHP_EOL;
        $log .= PHP_EOL . "--------------------  Process Start ----------------" . PHP_EOL;
        if (!empty($OrderQueries)) {
            foreach ($OrderQueries as $OrdersD) {
                $isDriverAssigned = 0;
                $SameLocDriver = 0;
                $NearByDriver = 0;

                $checkOrderStaus = 1;
                $iCompanyId = $OrdersD['iCompanyId'];
                $AllDriverList = '';
                $log .= PHP_EOL . "-------------------- Order Id is ----------------" . PHP_EOL;
                $log .= PHP_EOL . $OrdersD['iOrderId'] . PHP_EOL;
                $getOrderAllLatlong = GetLatLongOrderid($OrdersD['iOrderId']);
                //echo "<pre>"; print_r($getOrderAllLatlong); echo "</pre>";

                $origin = $getOrderAllLatlong['PickUpLatitude'] . ',' . $getOrderAllLatlong['PickUpLongitude'];
                $destination = $getOrderAllLatlong['DestLatitude'] . ',' . $getOrderAllLatlong['DestLongitude'];
                $mat = $getOrderAllLatlong['Mat'];
                $DriverInSameLocations = GetDriverListFromSamePickup($iCompanyId);
                //echo "<pre>"; print_r($DriverInSameLocations); echo "</pre>"; exit;
                // echo "Working";
                if (!empty($DriverInSameLocations)) {
                    foreach ($DriverInSameLocations as $DriverInSameLocation) {
                        if ($isDriverAssigned == 0) {
//                            $checkFriverExistingRequest = GetDriverTimeOutRequest($DriverInSameLocation['iDriverId'], $OrdersD['iOrderId']);
//                            if ($checkFriverExistingRequest == 'NO') {
                            $AllDriverList .= $DriverInSameLocation['iDriverId'] . ',';
                            $previousorderTimeDiff = GetTimeLeftPrevOrder($DriverInSameLocation['iDriverId']);
                            $MoreWaitingTime = GetTimeDiffInMinutes($getOrderAllLatlong['pickuptime']);

                            $log .= PHP_EOL . "-------------------- Prev order time left ----------------" . PHP_EOL;
                            $log .= PHP_EOL . $previousorderTimeDiff . PHP_EOL;
                            $log .= PHP_EOL . "-------------------- More time for preparing order ----------------" . PHP_EOL;
                            $log .= PHP_EOL . $MoreWaitingTime . PHP_EOL;
                            $totaltime = $previousorderTimeDiff + $MoreWaitingTime + 2;


                            $driverOrderStacks = GetOrderStackDriver($iCompanyId, $DriverInSameLocation['iDriverId']);

                            $Waypoints = '';
                            foreach ($driverOrderStacks as $driverOrderStack):
                                $Waypoints .= $driverOrderStack['DestLatitude'] . ',' . $driverOrderStack['DestLongitude'] . '|';
                            endforeach;
                            $count = count($driverOrderStacks);

                            $CHeckRouteWithMat = GetDrivingDistanceRouting($origin, $destination, $Waypoints, $mat, $totaltime);
                            // print_r($CHeckRouteWithMat);
                            if ($CHeckRouteWithMat == 'yes') {

                                $Assignorder = AssignOrderToDriver($OrdersD['iOrderId'], $DriverInSameLocation['iDriverId']);
                                $isDriverAssigned = $Assignorder;
                                $SameLocDriver = $Assignorder;

                                $log .= PHP_EOL . "Same Location Driver with id: " . $DriverInSameLocation['iDriverId'] . " is assigned to order." . $OrdersD['iOrderId'] . PHP_EOL;
                                //   sleep(15);
                            } else {
                                $log .= PHP_EOL . "Mat Exceeded for order no :" . $OrdersD['iOrderId'] . " with Driver id " . $DriverInSameLocation['iDriverId'] . "Found on same location......!" . PHP_EOL;
                            }


//                            } else {
//                                $AllDriverList .= $DriverInSameLocation['iDriverId'] . ',';
//                                 echo "No Driver Found on same location......!";
//                            }
                        } //checkifdeclinedandtimeout
                    }
                } else {

                    $log .= PHP_EOL . "No Driver Found on same location......!" . PHP_EOL;
                }

                if (($isDriverAssigned == 0) && ($SameLocDriver == 0)) {
                    $DriverInNearBYLocations = GetDriverListNearBYLocation($getOrderAllLatlong['PickUpLatitude'], $getOrderAllLatlong['PickUpLongitude'], $AllDriverList);

                    if (!empty($DriverInNearBYLocations)) {
                        foreach ($DriverInNearBYLocations as $DriverInNearBYLocation) {
                            if ($isDriverAssigned == 0) {
//                                $checkFriverExistingRequest = GetDriverTimeOutRequest($DriverInNearBYLocation['iDriverId'], $OrdersD['iOrderId']);
//                                if ($checkFriverExistingRequest == 'NO') {

                                $AllDriverList .= $DriverInNearBYLocation['iDriverId'] . ',';
                                $previousorderTimeDiff = GetTimeLeftPrevOrder($DriverInNearBYLocation['iDriverId']);
                                $MoreWaitingTime = GetTimeDiffInMinutes($getOrderAllLatlong['pickuptime']);

                                $log .= PHP_EOL . "-------------------- Prev order time left ----------------" . PHP_EOL;
                                $log .= PHP_EOL . $previousorderTimeDiff . PHP_EOL;
                                $log .= PHP_EOL . "-------------------- More time for preparing order ----------------" . PHP_EOL;
                                $log .= PHP_EOL . $MoreWaitingTime . PHP_EOL;
                                $totaltime = $previousorderTimeDiff + $MoreWaitingTime + 2;

                                $driverOrderStacks = GetOrderStackDriverWOC($DriverInNearBYLocation['iDriverId']);
                                $Waypoints = '';
                                foreach ($driverOrderStacks as $driverOrderStack):
                                    $Waypoints .= $driverOrderStack['DestLatitude'] . ',' . $driverOrderStack['DestLongitude'] . '|';
                                endforeach;
                                $count = count($driverOrderStacks);

                                $CHeckRouteWithMat = GetDrivingDistanceRouting($origin, $destination, $Waypoints, $mat, $totaltime);
                                // print_r($CHeckRouteWithMat);
                                if ($CHeckRouteWithMat == 'yes') {

                                    $Assignorder = AssignOrderToDriver($OrdersD['iOrderId'], $DriverInNearBYLocation['iDriverId']);
                                    $isDriverAssigned = $Assignorder;
                                    $NearByDriver = $Assignorder;

                                    $log .= PHP_EOL . "Near BY Driver with id : " . $DriverInNearBYLocation['iDriverId'] . " is assigned to order" . $OrdersD['iOrderId'] . PHP_EOL;
                                    //   sleep(15);
                                } else {
                                    $log .= PHP_EOL . "Max Exceeded for order no :" . $OrdersD['iOrderId'] . " with Driver id " . $DriverInNearBYLocation['iDriverId'] . "Found on same location......!" . PHP_EOL;
                                }
//                                } else {
//                                    $AllDriverList .= $DriverInNearBYLocation['iDriverId'] . ',';
//                                     echo "No Driver Found on near by location......!";
//                                }
                            }
                        }
                    } else {
                        $log .= PHP_EOL . "No Driver Found on near by location......!" . PHP_EOL;
                    }
                }



                if (($isDriverAssigned == 0) && ($NearByDriver == 0)) {
                    $isDriverAssigned = AssignOrderFreeDriver($OrdersD['iOrderId']);
                    if ($isDriverAssigned == 1) {
                        $log .= PHP_EOL . "Free Driver is assigned to order" . $OrdersD['iOrderId'] . PHP_EOL;
                    } else {

                        $log .= PHP_EOL . "........No Driver Found......!" . PHP_EOL;
                    }
                    //sleep(15);
                    //this is pending check for free driver list and assigne order to that driver
                }

                if ($isDriverAssigned == 0) {

                    $log .= PHP_EOL . "..........No Driver Found......!" . PHP_EOL;
                }
                $log .= PHP_EOL . "-------------------------------IN LOOP-----------------------------" . PHP_EOL;
            }
        } else {

            $log .= PHP_EOL . "-------------------------------No Order Found-----------------------------" . PHP_EOL;
        }

        try {
            saveAlgoLog($log);
        } catch (Exception $e) {
            
        }
    }

    public function getbestordermixasaSA($picklat, $picklong, $droplat, $droplong) {

        $result = array();
        global $generalobj, $obj;
        $driverLists = array();
        $driverlisttt = "SELECT vName,vEmail, vLatitude, vLongitude , 111.111 *
            DEGREES(ACOS(COS(RADIANS($picklat))
                 * COS(RADIANS(`vLatitude`))
                 * COS(RADIANS($picklong - `vLongitude`))
                 + SIN(RADIANS($picklat))
                 * SIN(RADIANS(`vLatitude`)))) AS distance FROM register_driver WHERE eStatus = 'active'  AND vTripStatus IN ('Finished', 'NONE') AND vAvailability = 'Available' AND  ORDER BY distance ASC LIMIT 3";
        $driverLists = $obj->MySQLSelect($driverlisttt);

        echo json_encode($result);
    }

    public function distancesasaSA($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    public function GetDrivingDistancesASAsASasA($lat1, $lat2, $long1, $long2) {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&mode=driving&key=AIzaSyBpS6KkKA19pQD7Ai663AbGInvuSZWxICI";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
        $distvalue = $response_a['rows'][0]['elements'][0]['distance']['value'];
        $time = $response_a['rows'][0]['elements'][0]['duration']['text'];
        $timevalue = $response_a['rows'][0]['elements'][0]['duration']['value'];
        //if(substr($dist, $start))
        return array('distance' => $distvalue, 'time' => $timevalue);
    }

    // Convert google distance in KM
    public function changedistanceformat($value = null) {
        $result = ($value / 1000);

        return $result;
    }

    // Convert Google time in minutes

    public function changetimeformat($value = null) {
        $result = ($value / 60);

        return $result;
    }

    public function cookingtimecalculatorSAsaSAsA($order = null) {
        global $generalobj, $obj;
        $orderdetails = "SELECT iMenuItemId FROM `order_details` WHERE iOrderId = $order";
        $OrderQuery = $obj->MySQLSelect($orderdetails);
        $cookingtime = 0;
        $menuitem = '';
        foreach ($OrderQuery as $orderquries):
            $menuitem .= $orderquries['iMenuItemId'] . ',';
        endforeach;
        $menuitems = substr($menuitem, 0, -1);
        $menuQuery = "SELECT `cookingtime` FROM `menu_items` WHERE `iMenuItemId` IN( $menuitems ) ";
        $MenuitemQuerydatas = $obj->MySQLSelect($menuQuery);
        foreach ($MenuitemQuerydatas as $MenuitemQuerydata):
            if ($cookingtime < $MenuitemQuerydata['cookingtime']) {
                $cookingtime = $MenuitemQuerydata['cookingtime'];
            }
        endforeach;
        echo $cookingtime;
    }

    /*
     * The Main Algo to calculate nearest driver and check drop and pickup time based on that
     */

    // Nearest Driver List From Database 
    public function getneareastdriverlistfromdbSAsASasa($picklat, $picklong, $droplat, $droplong) {

        $result = array();
        global $generalobj, $obj;
        $driverLists = array();
        $driverlisttt = "SELECT vName,vEmail, vLatitude, vLongitude , 111.111 *
            DEGREES(ACOS(COS(RADIANS($picklat))
                 * COS(RADIANS(`vLatitude`))
                 * COS(RADIANS($picklong - `vLongitude`))
                 + SIN(RADIANS($picklat))
                 * SIN(RADIANS(`vLatitude`)))) AS distance FROM register_driver WHERE eStatus = 'active'  AND vTripStatus IN ('Finished', 'NONE') AND vAvailability = 'Available' AND  ORDER BY distance ASC LIMIT 3";
        //
        $driverLists = $obj->MySQLSelect($driverlisttt);

        // In query check for the driver which is waiting for long time
        /*
         * This need to implement on high periority
         */


//  $distancefromdrop = $this->distance($droplat, $droplong, $driverList[0]['vLatitude'], $driverList[0]['vLongitude'], 'K');
//$cookingtime = '15';


        $finaldriver = array();
        $cookingtime = '20';
        foreach ($driverLists as $driverList):
            $pickupdistime = $this->GetDrivingDistance($picklat, $driverList['vLatitude'], $picklong, $driverList['vLongitude']);
            $finaldriver[] = array(
                'vName' => $driverList['vName'],
                'vEmail' => $driverList['vEmail'],
                'vLatitude' => $driverList['vLatitude'],
                'vLongitude' => $driverList['vLongitude'],
                'distance' => $driverList['distance'],
                'distancepickup' => $this->changedistanceformat($pickupdistime['distance']),
                'timepickup' => $this->changetimeformat($pickupdistime['time']),
            );
            unset($pickupdistime);
        endforeach;
        echo "<pre>";
        print_r($finaldriver);
        echo "</pre>";
        exit;


        $pickupdistime = $this->GetDrivingDistance($picklat, $driverList[0]['vLatitude'], $picklong, $driverList[0]['vLongitude']);
        //print_r($pickupdistime);
        $dropdistime = $this->GetDrivingDistance($droplat, $picklat, $droplong, $picklong);
        //print_r($dropdistime);
        $pickupdistance = $this->changedistanceformat($pickupdistime['distance']);
        $pickuptime = $this->changetimeformat($pickupdistime['time']);
        $dropdistance = $this->changedistanceformat($dropdistime['distance']);
        $droptime = $this->changetimeformat($dropdistime['time']);

        $estimatetimeforpickup = 0;
        if ($cookingtime > $pickuptime) {
            $estimatetimeforpickup = $cookingtime;
        } else {
            $estimatetimeforpickup = $pickuptime;
        }

        $totaltime = $estimatetimeforpickup + $droptime;
        $totaldistance = $pickupdistance + $dropdistance;

        $ordertime = date('H:i:s');
        $otct = date("H:i:s", strtotime('+' . $pickuptime . ' minutes', strtotime($ordertime)));
        //$opod =  date("H:i:s", strtotime('+'.$droptime.' minutes', strtotime($ordertime)));
        $totalordertime = date("H:i:s", strtotime('+' . $totaltime . ' minutes', strtotime($ordertime)));
        $result = array(
            'driverlocation' => array('latitude' => $driverList[0]['vLatitude'], 'longitude' => $driverList[0]['vLongitude']),
            'pickuplocation' => array('latitude' => $picklat, 'longitude' => $picklong),
            'droplocation' => array('latitude' => $droplat, 'longitude' => $droplong),
            'pickupdistance' => $pickupdistance,
            'pickuptime' => $pickuptime,
            'dropdistance' => $dropdistance,
            'droptime' => $droptime,
            'totaltime' => $totaltime,
            'totaldistnace' => $totaldistance,
            'ordertime' => $ordertime,
            'ordertimewithcooking' => $otct,
            //  'ordertimewithdrop'=>$opod,
            'totalordertime' => $totalordertime
        );

        if ($totaltime > 50) {
            $result['notification'] = true;
            $result['message'] = 'Time is more than 50 Min do you want to proceed.';
        } else {
            $result['notification'] = false;
            $result['message'] = 'Order will deliver on time.';
        }

        echo json_encode($result);
    }

    public function millitarybasesAsaSasA($passengerLat, $passengerLon, $checkgoogle) {

        global $generalobj, $obj;

        $lat = $passengerLat;
        $long = $passengerLon;
        $sqlLat = "SELECT  building,  latitude, longitude, ABS($lat - `latitude`) as distance  FROM `militarylocations` ORDER BY distance ASC LIMIT 2 ";
        $sqlLong = "SELECT  building,  latitude, longitude, ABS($long - `longitude`) as distance  FROM `militarylocations` ORDER BY distance ASC LIMIT 2 ";
        $query1 = $obj->MySQLSelect($sqlLat);
        $query2 = $obj->MySQLSelect($sqlLong);

        $query = array_merge($query1, $query2);

        $query = array_unique($query);

        if ($checkgoogle) {
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&sensor=true&key=AIzaSyBpS6KkKA19pQD7Ai663AbGInvuSZWxICI";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
            }
            $result = curl_exec($ch);
            if ($result === false) {
                echo "Error in cURL : " . curl_error($ch);
            }
            curl_close($ch);

            $Fresults = json_decode($result, true);

            $finalresult = array();
//echo $status = $Fresults['status'];
            $count = 1;
            foreach ($Fresults['results'] as $addData) {
                if ($count < 10) {
                    $distnce = 0;

                    $distnce = distance($lat, $long, $addData['geometry']['location']['lat'], $addData['geometry']['location']['lng'], 'K');

                    if (($count == 1) && ($query[0]['distance'] <= $distnce)) {
                        $resultdata[] = array(
                            'building' => $query[0]['building'],
                            'latitude' => $query[0]['latitude'],
                            'longitude' => $query[0]['longitude'],
                            'distance' => $query[0]['distance']
                        );
                    }

                    $resultdata[] = array(
                        'building' => $addData['formatted_address'],
                        'latitude' => $addData['geometry']['location']['lat'],
                        'longitude' => $addData['geometry']['location']['lng'],
                        'distance' => $distnce
                    );
                }
                $count++;
            }
        } else {
            $resultdata = $query;
        }
        return $resultdata;
    }

}

$objalgo = new webalgorithm;
//return $objalgo->getneareastdriverlistfromdb('30.754476','76.7977413', '30.572166','76.8710313');
 $objalgo->getLatestOrderDetails();
 
// sleep(6);
// $objalgo2 = new webalgorithm;
// $objalgo2->getLatestOrderDetails();
// 
//  sleep(7);
//  $objalgo3 = new webalgorithm;
// $objalgo3->getLatestOrderDetails();
// 
//  sleep(8);
//  $objalgo4 = new webalgorithm;
// $objalgo4->getLatestOrderDetails();
// 
//  sleep(9);
//   $objalgo5 = new webalgorithm;
// $objalgo5->getLatestOrderDetails();
?>