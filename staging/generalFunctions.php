<?php

/* to clean function */
error_reporting(0);

function clean($str) {
    global $obj;
    $str = trim($str);
    //$str = mysqli_real_escape_string($str);
    $str = $obj->SqlEscapeString($str);
    $str = htmlspecialchars($str);
    $str = strip_tags($str);
    return($str);
}

/* get vLangCode as per member or if member not found check lcode and then defualt take lang code set at $lang_label */

function getLanguageCode($memberId = '', $lcode = '') {
    global $lang_label, $lang_code, $obj;
    /* find vLanguageCode using member id */
    if ($memberId != '') {

        $sql = "SELECT  `vLanguageCode` FROM  `member` WHERE iMemberId = '" . $memberId . "' AND `eStatus` = 'Active' ";
        $get_vLanguageCode = $obj->MySQLSelect($sql);

        if (count($get_vLanguageCode) > 0)
            $lcode = (isset($get_vLanguageCode[0]['vLanguageCode']) && $get_vLanguageCode[0]['vLanguageCode'] != '') ? $get_vLanguageCode[0]['vLanguageCode'] : '';
    }

    /* find default language of website set by admin */
    if ($lcode == '') {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);

        $lcode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }

    $lang_code = $lcode;
    $sql = "SELECT  `vLabel` ,  `vValue`  FROM  `language_label`  WHERE  `vCode` = '" . $lcode . "' ";
    $all_label = $obj->MySQLSelect($sql);

    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $lang_label[$vLabel] = $vValue;
    }
    //echo "<pre>"; print_R($lang_label); echo "</pre>";
}

#function to get value from table can be use for any table - create to get value from configuration
#$check_phone = get_value('configurations', 'vValue', 'vName', 'PHONE_VERIFICATION_REQUIRED');

function get_value($table, $field_name, $condition_field = '', $condition_value = '', $setParams = '', $directValue = '') {
    global $obj;
    $returnValue = array();

    $where = ($condition_field != '') ? ' WHERE ' . clean($condition_field) : '';
    $where .= ($where != '' && $condition_value != '') ? ' = "' . clean($condition_value) . '"' : '';

    if ($table != '' && $field_name != '' && $where != '') {
        $sql = "SELECT $field_name FROM  $table $where";
        if ($setParams != '') {
            $sql .= $setParams;
        }
        $returnValue = $obj->MySQLSelect($sql);
    } else if ($table != '' && $field_name != '') {
        $sql = "SELECT $field_name FROM  $table";
        if ($setParams != '') {
            $sql .= $setParams;
        }
        $returnValue = $obj->MySQLSelect($sql);
    }
    if ($directValue == '') {
        return $returnValue;
    } else {
        $temp = $returnValue[0][$field_name];
        return $temp;
    }
}

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function createUserLog($userType, $eAutoLogin, $iMemberId, $deviceType) {
    global $generalobj, $obj;

    if (SITE_TYPE != "Demo") {
        return "";
    }
    $data['iMemberId'] = $iMemberId;
    $data['eMemberType'] = $userType;
    $data['eMemberLoginType'] = "AppLogin";
    $data['eDeviceType'] = $deviceType;
    $data['eAutoLogin'] = $eAutoLogin;
    $data['vIP'] = get_client_ip();

    $id = $obj->MySQLQueryPerform("member_log", $data, 'insert');
}

function dateDifference($date_1, $date_2, $differenceFormat = '%a') {
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);

    $interval = date_diff($datetime1, $datetime2);

    return $interval->format($differenceFormat);
}

function getVehicleTypes($cityName = "") {
    global $obj;
    $sql_vehicle_type = "SELECT * FROM vehicle_type";

    $row_result_vehivle_type = $obj->MySQLSelect($sql_vehicle_type);
    return $row_result_vehivle_type;
}

function paymentimg($paymentm) {
    global $tconfig;
    if ($paymentm == "Card") {
        // return "webimages/icons/payment_images/ic_payment_type_card.png";
        return $tconfig["tsite_url"] . "webimages/icons/payment_images/ic_payment_type_card.png";
    } else {
        // return "webimages/icons/payment_images/ic_payment_type_cash.png";
        return $tconfig["tsite_url"] . "webimages/icons/payment_images/ic_payment_type_cash.png";
    }
}

function ratingmark($ratingval) {
    global $tconfig;
    $a = $ratingval;
    $b = explode('.', $a);
    $c = $b[0];

    $str = "";
    $count = 0;
    for ($i = 0; $i < 5; $i++) {
        if ($c > $i) {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Full.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" align="left" >';
        } elseif ($a > $c && $count == 0) {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Half-Full.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" align="left" >';
            $count = 1;
        } else {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-blank.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" align="left" >';
        }
    }
    return $str;
}

function getTripFare($Fare_data, $surgePrice) {
    global $generalobj, $obj;
    //$ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations", "ALLOW_SERVICE_PROVIDER_AMOUNT");
    $iVehicleTypeId = get_value('trips', 'iVehicleTypeId', 'iTripId', $Fare_data[0]['iTripId'], '', 'true');
    $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
    $iParentId = get_value('vehicle_category', 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    if ($iParentId == 0) {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    $eFlatTrip = $Fare_data[0]['eFlatTrip'];
    $fFlatTripPrice = $Fare_data[0]['fFlatTripPrice'];
    if ($eFlatTrip == "Yes") {
        $Fare_data[0]['iBaseFare'] = $fFlatTripPrice;
        $Fare_data[0]['fPricePerMin'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;
    }
    //$ePriceType=get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');  
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";

    $fAmount = 0;
    if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
        $iDriverVehicleId = get_value('trips', 'iDriverVehicleId', 'iTripId', $Fare_data[0]['iTripId'], '', 'true');


        $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $serviceProData = $obj->MySQLSelect($sqlServicePro);

        if (count($serviceProData) > 0) {
            $fAmount = $serviceProData[0]['fAmount'];
        }
    }
    if ($surgePrice >= 1) {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'] * $surgePrice;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'] * $surgePrice;
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'] * $surgePrice;
        $Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'] * $surgePrice;
    }

    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['fPricePerMin'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes" && $fAmount != 0) {
            $Fare_data[0]['iBaseFare'] = $fAmount * $Fare_data[0]['iQty'];
        } else {
            $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'] * $Fare_data[0]['iQty'];
        }
    } else if ($Fare_data[0]['eFareType'] == 'Hourly') {
        $Fare_data[0]['iBaseFare'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;

        $totalHour = $Fare_data[0]['TripTimeMinutes'] / 60;
        $Fare_data[0]['TripTimeMinutes'] = $totalHour;

        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes" && $fAmount != 0) {
            $Fare_data[0]['fPricePerMin'] = $fAmount;
        } else {
            $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerHour'];
        }
    }

    $Minute_Fare = round($Fare_data[0]['fPricePerMin'] * $Fare_data[0]['TripTimeMinutes'], 2);
    $Distance_Fare = round($Fare_data[0]['fPricePerKM'] * $Fare_data[0]['TripDistance'], 2);
    $iBaseFare = round($Fare_data[0]['iBaseFare'], 2);
    $fMaterialFee = round($Fare_data[0]['fMaterialFee'], 2);
    $fMiscFee = round($Fare_data[0]['fMiscFee'], 2);
    $fDriverDiscount = round($Fare_data[0]['fDriverDiscount'], 2);
    $fVisitFee = round($Fare_data[0]['fVisitFee'], 2);
    //  print_r($Fare_data); 
    $total_fare = ($iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee + $fVisitFee) - $fDriverDiscount;
    //exit();
    $total_fare_for_commission_ufx = $iBaseFare + $Minute_Fare + $Distance_Fare;
    $Commision_Fare = round((($total_fare_for_commission_ufx * $Fare_data[0]['fCommision']) / 100), 2);

    $result['FareOfMinutes'] = $Minute_Fare;
    $result['FareOfDistance'] = $Distance_Fare;
    $result['FareOfCommision'] = $Commision_Fare;
    // $result['iBaseFare'] = $iBaseFare;
    $result['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
    $result['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
    $result['fCommision'] = $Fare_data[0]['fCommision'];
    $result['FinalFare'] = $total_fare;
    $result['FinalFare_UFX_Commission'] = $total_fare_for_commission_ufx;
    $result['iBaseFare'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
    $result['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
    $result['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
    $result['iMinFare'] = $Fare_data[0]['iMinFare'];

    return $result;
}

function calculateFare($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $couponCode = "", $tripId, $fMaterialFee = 0, $fMiscFee = 0, $fDriverDiscount = 0) {
    global $generalobj, $obj;
    $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);

    // $defaultCurrency = ($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $sql = "select fPickUpPrice,fNightPrice,iQty,eFareType,eFlatTrip,fFlatTripPrice,fVisitFee,fTollPrice,eTollSkipped from trips where iTripId='" . $tripId . "'";
    $data_trips = $obj->MySQLSelect($sql);
    $fPickUpPrice = $data_trips[0]['fPickUpPrice'];
    $fNightPrice = $data_trips[0]['fNightPrice'];
    $iQty = $data_trips[0]['iQty'];
    $eFareType = $data_trips[0]['eFareType'];
    $eFlatTrip = $data_trips[0]['eFlatTrip'];
    $fFlatTripPrice = $data_trips[0]['fFlatTripPrice'];
    /* if($eFlatTrip == "No"){
      $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
      }else{
      $surgePrice = 1;
      } */
    $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
    $fVisitFee = $data_trips[0]['fVisitFee'];
    $tripTimeInMinutes = ($totalTimeInMinutes_trip != '') ? $totalTimeInMinutes_trip : 0;
    $fPricePerKM = getVehicleCountryUnit_PricePerKm($vehicleTypeID, $Fare_data[0]['fPricePerKM']);
    $fTollPrice = $data_trips[0]['fTollPrice'];
    $eTollSkipped = $data_trips[0]['eTollSkipped'];
    $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    $fTax1 = $TaxArr['fTax1'];
    $fTax2 = $TaxArr['fTax2'];

    if ($eTollSkipped == "Yes") {
        $fTollPrice = 0;
    }

    $Fare_data[0]['TripTimeMinutes'] = $tripTimeInMinutes;
    $Fare_data[0]['TripDistance'] = $tripDistance;
    $Fare_data[0]['eFlatTrip'] = $eFlatTrip;
    $Fare_data[0]['fFlatTripPrice'] = $fFlatTripPrice;
    $Fare_data[0]['iTripId'] = $tripId;
    $Fare_data[0]['eFareType'] = $eFareType;
    $Fare_data[0]['iQty'] = $iQty;
    $Fare_data[0]['fVisitFee'] = $fVisitFee;
    $Fare_data[0]['fMaterialFee'] = $fMaterialFee;
    $Fare_data[0]['fMiscFee'] = $fMiscFee;
    $Fare_data[0]['fDriverDiscount'] = $fDriverDiscount;
    $Fare_data[0]['fPricePerKM'] = $fPricePerKM;


    $result = getTripFare($Fare_data, "1");
    //$resultArr_Orig = getTripFare($Fare_data,"1");


    $total_fare = $result['FinalFare'];
    $fTripGenerateFare = $result['FinalFare'];
    //$fTripGenerateFare_For_Commission = $result['FinalFare'];
    $fTripGenerateFare_For_Commission = $result['FinalFare_UFX_Commission'];

    $fSurgePriceDiff = round(($fTripGenerateFare * $surgePrice) - $fTripGenerateFare, 2);
    $total_fare = $total_fare + $fSurgePriceDiff;
    $fTripGenerateFare = $fTripGenerateFare + $fSurgePriceDiff;

    $iMinFare = $result['iMinFare'];

    if ($eFlatTrip == "No") {
        if ($iMinFare > $fTripGenerateFare) {
            $MinFareDiff = $iMinFare - $total_fare;
            $total_fare = $iMinFare;
            $fTripGenerateFare = $iMinFare;
            $fTripGenerateFare_For_Commission = $iMinFare;
        } else {
            $MinFareDiff = "0";
            $fTripGenerateFare_For_Commission = $fTripGenerateFare_For_Commission + $fSurgePriceDiff;
        }
    } else {
        $fTripGenerateFare_For_Commission = $fTripGenerateFare_For_Commission + $fSurgePriceDiff;
    }

    /* Tax Calculation */
    $result['fTax1'] = 0;
    $result['fTax2'] = 0;
    if ($fTax1 > 0) {
        $fTaxAmount1 = round((($fTripGenerateFare * $fTax1) / 100), 2);
        $fTripGenerateFare = $fTripGenerateFare + $fTaxAmount1;
        $total_fare = $total_fare + $fTaxAmount1;
        $result['fTax1'] = $fTaxAmount1;
    }
    if ($fTax2 > 0) {
        $total_fare_new = $fTripGenerateFare - $fTaxAmount1;
        $fTaxAmount2 = round((($total_fare_new * $fTax2) / 100), 2);
        $fTripGenerateFare = $fTripGenerateFare + $fTaxAmount2;
        $total_fare = $total_fare + $fTaxAmount2;
        $result['fTax2'] = $fTaxAmount2;
    }
    /* Tax Calculation */
    if ($fTollPrice > 0) {
        $total_fare = $total_fare + $fTollPrice;
        $fTripGenerateFare = $fTripGenerateFare + $fTollPrice;
    }

    //$result['fCommision'] = round((($fTripGenerateFare * $Fare_data[0]['fCommision']) / 100), 2);
    //$fTripGenerateFare_For_Commission = $fTripGenerateFare_For_Commission+$fSurgePriceDiff;
    $result['fCommision'] = round((($fTripGenerateFare_For_Commission * $Fare_data[0]['fCommision']) / 100), 2);
    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
        $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
    }
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $vDiscount = round($discountValue, 1) . ' ' . "%";
            $discountValue = round(($total_fare * $discountValue), 1) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $total_fare) {
                $vDiscount = round($total_fare, 1) . ' ' . $curr_sym;
            } else {
                $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
            }
        }
        $fare = $total_fare - $discountValue;
        if ($fare < 0) {
            $fare = 0;
            $discountValue = $total_fare;
        }
        $total_fare = $fare;
        $Fare_data[0]['fDiscount'] = $discountValue;
        $Fare_data[0]['vDiscount'] = $vDiscount;
    }
    /* Check Coupon Code Total Fare  End */

    /* Check debit wallet For Count Total Fare  Start */
    $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
    $user_wallet_debit_amount = 0;
    if ($total_fare > $user_available_balance) {
        $total_fare = $total_fare - $user_available_balance;
        $user_wallet_debit_amount = $user_available_balance;
    } else {
        $user_wallet_debit_amount = $total_fare;
        $total_fare = 0;
    }

    // Update User Wallet
    if ($user_wallet_debit_amount > 0) {
        $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $tripId, '', 'true');
        $data_wallet['iUserId'] = $iUserId;
        $data_wallet['eUserType'] = "Rider";
        $data_wallet['iBalance'] = $user_wallet_debit_amount;
        $data_wallet['eType'] = "Debit";
        $data_wallet['dDate'] = date("Y-m-d H:i:s");
        $data_wallet['iTripId'] = $tripId;
        $data_wallet['eFor'] = "Booking";
        $data_wallet['ePaymentStatus'] = "Unsettelled";
        $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING# " . $vRideNo;

        $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);
        //$obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
    }
    /* Check debit wallet For Count Total Fare  End */


    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = 0;
    } else {
        $Fare_data[0]['iBaseFare'] = $result['iBaseFare'];
    }

    $finalFareData['total_fare'] = $total_fare;
    $finalFareData['iBaseFare'] = $result['iBaseFare'];
    $finalFareData['fPricePerMin'] = $result['FareOfMinutes'];
    $finalFareData['fPricePerKM'] = $result['FareOfDistance'];
    //$finalFareData['fCommision'] = $result['FareOfCommision'];
    //$finalFareData['fCommision'] = round((($fTripGenerateFare*$result['fCommision'])/100),2);
    $finalFareData['fCommision'] = $result['fCommision'];
    $finalFareData['fDiscount'] = $Fare_data[0]['fDiscount'];
    $finalFareData['vDiscount'] = $Fare_data[0]['vDiscount'];
    $finalFareData['MinFareDiff'] = $MinFareDiff;
    $finalFareData['fSurgePriceDiff'] = $fSurgePriceDiff;
    $finalFareData['user_wallet_debit_amount'] = $user_wallet_debit_amount;
    $finalFareData['fTripGenerateFare'] = $fTripGenerateFare;
    $finalFareData['SurgePriceFactor'] = $surgePrice;
    $finalFareData['fTax1'] = $result['fTax1'];
    $finalFareData['fTax2'] = $result['fTax2'];
    return $finalFareData;
}

function calculateFareEstimate($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $surgePrice = 1) {
    global $generalobj, $obj;
    $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);

    // $defaultCurrency = ($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');

    if ($surgePrice > 1) {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'] * $surgePrice;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'] * $surgePrice;
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'] * $surgePrice;
        $Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'] * $surgePrice;
    }

    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'];
        $Fare_data[0]['fPricePerMin'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;
    }

    $resultArr = $generalobj->getFinalFare($Fare_data[0]['iBaseFare'], $Fare_data[0]['fPricePerMin'], $totalTimeInMinutes_trip, $Fare_data[0]['fPricePerKM'], $tripDistance, $Fare_data[0]['fCommision'], $priceRatio, $defaultCurrency, $startDate, $endDate);

    $resultArr['FinalFare'] = $resultArr['FinalFare'] - $resultArr['FareOfCommision']; // Temporary set: Remove addition of commision from above function

    $Fare_data[0]['total_fare'] = $resultArr['FinalFare'];

    if ($Fare_data[0]['iMinFare'] > $Fare_data[0]['total_fare']) {
        $Fare_data[0]['MinFareDiff'] = $Fare_data[0]['iMinFare'] - $Fare_data[0]['total_fare'];
        $Fare_data[0]['total_fare'] = $Fare_data[0]['iMinFare'];
    } else {
        $Fare_data[0]['MinFareDiff'] = "0";
    }

    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = 0;
    } else {
        $Fare_data[0]['iBaseFare'] = $resultArr['iBaseFare'];
    }
    $Fare_data[0]['fPricePerMin'] = $resultArr['FareOfMinutes'];
    $Fare_data[0]['fPricePerKM'] = $resultArr['FareOfDistance'];
    $Fare_data[0]['fCommision'] = $resultArr['FareOfCommision'];
    return $Fare_data;
}

function calculateFareEstimateAll($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $couponCode = "", $surgePrice = 1, $fMaterialFee = 0, $fMiscFee = 0, $fDriverDiscount = 0, $DisplySingleVehicleFare = "", $eUserType = "Passenger", $iQty = 1, $SelectedCarTypeID = "", $isDestinationAdded = "Yes", $eFlatTrip = "No", $fFlatTripPrice = 0, $sourceLocationArr, $destinationLocationArr) {
    //                                          1                   2               3            4           5           6                7               8                 9                   10             11                12                  13                             14                   15              16                   17                      18                  19                     20                     21
    global $generalobj, $obj, $tconfig, $APPLY_SURGE_ON_FLAT_FARE;

    if ($eUserType == "Passenger") {
        $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
        $userlangcode = get_value("register_user", "vLang", "iUserId", $iUserId, '', 'true');
        $eUnit = getMemberCountryUnit($iUserId, "Passenger");
        $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    } else {
        $vCurrencyPassenger = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iUserId, '', 'true');
        $userlangcode = get_value("register_driver", "vLang", "iDriverId", $iUserId, '', 'true');
        $eUnit = getMemberCountryUnit($iUserId, "Driver");
        $TaxArr = getMemberCountryTax($iUserId, "Driver");
    }

    if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
        $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
    $vSymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');



    if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    //$eUnit = getMemberCountryUnit($iUserId,"Passenger");
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");

    if ($DisplySingleVehicleFare == "") {
        $ssql = "";
        if ($SelectedCarTypeID != "") {
            $ssql .= " AND iVehicleTypeId IN ($SelectedCarTypeID) ";
        }
        $sql_vehicle_type = "SELECT * FROM vehicle_type WHERE 1 " . $ssql;
        $Fare_data = $obj->MySQLSelect($sql_vehicle_type);
        $result = array();
        for ($i = 0; $i < count($Fare_data); $i++) {
            $fPickUpPrice = 1;
            $fNightPrice = 1;

            $data_surgePrice = checkSurgePrice($Fare_data[$i]['iVehicleTypeId'], "");

            if ($data_surgePrice['Action'] == "0") {
                if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                    $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
                } else {
                    $fNightPrice = $data_surgePrice['SurgePriceValue'];
                }
            }

            $Fare_data[$i]['TripTimeMinutes'] = $totalTimeInMinutes_trip;
            $Fare_data[$i]['TripDistance'] = $tripDistance;
            //$result = getTripFare($Fare_data[$i], $surgePrice);
            /** calculate fare * */
            $Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['iBaseFare'];
            $Fare_data[$i]['fPricePerMin'] = $Fare_data[$i]['fPricePerMin'];
            $Fare_data[$i]['fPricePerKM'] = getVehicleCountryUnit_PricePerKm($Fare_data[$i]['iVehicleTypeId'], $Fare_data[$i]['fPricePerKM']);
            $Fare_data[$i]['fPricePerKM'] = $Fare_data[$i]['fPricePerKM'];
            $Fare_data[$i]['iMinFare'] = $Fare_data[$i]['iMinFare'];
            $iBaseFare = $Fare_data[$i]['iBaseFare'];
            $fPricePerKM = $Fare_data[$i]['fPricePerKM'];
            $fPricePerMin = $Fare_data[$i]['fPricePerMin'];

            if ($Fare_data[$i]['eFareType'] == 'Fixed') {
                $Fare_data[$i]['fPricePerMin'] = 0;
                $Fare_data[$i]['fPricePerKM'] = 0;
                //$Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['fFixedFare'] * $Fare_data[$i]['iQty'];
                $Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['fFixedFare'] * $iQty;
            } else if ($Fare_data[$i]['eFareType'] == 'Hourly') {
                $Fare_data[$i]['iBaseFare'] = 0;
                $Fare_data[$i]['fPricePerKM'] = 0;

                $totalHour = $Fare_data[$i]['TripTimeMinutes'] / 60;
                $Fare_data[$i]['TripTimeMinutes'] = $totalHour;
                $Fare_data[$i]['fPricePerMin'] = $Fare_data[$i]['fPricePerHour'];
            }

            $Minute_Fare = round(($fPricePerMin * $totalTimeInMinutes_trip) * $priceRatio, 2);
            $Distance_Fare = round(($fPricePerKM * $tripDistance) * $priceRatio, 2);
            $iBaseFare = round($iBaseFare * $priceRatio, 2);
            $fMaterialFee = round($Fare_data[$i]['fMaterialFee'] * $priceRatio, 2);
            $fMiscFee = round($Fare_data[$i]['fMiscFee'] * $priceRatio, 2);
            $fDriverDiscount = round($Fare_data[$i]['fDriverDiscount'] * $priceRatio, 2);
            $fVisitFee = round($Fare_data[$i]['fVisitFee'] * $priceRatio, 2);
            if ($isDestinationAdded == "Yes") {
                $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $Fare_data[$i]['iVehicleTypeId']);
                $eFlatTrip = $data_flattrip['eFlatTrip'];
                $fFlatTripPrice = $data_flattrip['Flatfare'];
            } else {
                $eFlatTrip = "No";
                $fFlatTripPrice = 0;
            }
            $Fare_data[$i]['eFlatTrip'] = $eFlatTrip;
            $Fare_data[$i]['fFlatTripPrice'] = $fFlatTripPrice;
            if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $eFlatTrip == "Yes") {
                $fPickUpPrice = 1;
                $fNightPrice = 1;
            }
            $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
            if ($eFlatTrip == "No") {
                $total_fare = ($iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee + $fVisitFee) - $fDriverDiscount;
                $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
                $SurgePriceFactor = strval($surgePrice);
                $total_fare = $total_fare + $fSurgePriceDiff;
                $minimamfare = round($Fare_data[$i]['iMinFare'] * $priceRatio, 2);
                if ($minimamfare > $total_fare) {
                    $fMinFareDiff = $minimamfare - $total_fare;
                    $total_fare = $minimamfare;
                    $Fare_data[$i]['FinalFare'] = $total_fare;
                } else {
                    $fMinFareDiff = 0;
                }
            } else {
                $total_fare = round($fFlatTripPrice * $priceRatio, 2);
                $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
                $SurgePriceFactor = strval($surgePrice);
                $total_fare = $total_fare + $fSurgePriceDiff;
                $Fare_data[$i]['FinalFare'] = $total_fare;
                $fMinFareDiff = 0;
            }
            $Commision_Fare = round((($total_fare * $Fare_data[$i]['fCommision']) / 100), 2);
            /* Tax Calculation */
            $fTax1 = $TaxArr['fTax1'];
            $fTax2 = $TaxArr['fTax2'];
            if ($fTax1 > 0) {
                $fTaxAmount1 = round((($total_fare * $fTax1) / 100), 2);
                $total_fare = $total_fare + $fTaxAmount1;
                $Fare_data[$i]['fTax1'] = $vSymbol . " " . number_format($fTaxAmount1, 2);
            }
            if ($fTax2 > 0) {
                $total_fare_new = $total_fare - $fTaxAmount1;
                $fTaxAmount2 = round((($total_fare_new * $fTax2) / 100), 2);
                $total_fare = $total_fare + $fTaxAmount2;
                $Fare_data[$i]['fTax1'] = $vSymbol . " " . number_format($fTaxAmount2, 2);
            }
            /* Tax Calculation */

            $discountValue = 0;
            $discountValueType = "cash";
            if ($couponCode != "") {
                $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
                $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
                if ($discountValueType == "percentage") {
                    $vDiscount = round($discountValue, 1) . ' ' . "%";
                    $discountValue = round(($total_fare * $discountValue), 1) / 100;
                } else {
                    $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                    if ($discountValue > $total_fare) {
                        $vDiscount = round($total_fare, 1) . ' ' . $curr_sym;
                    } else {
                        $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
                    }
                }
                $total_fare = $total_fare - $discountValue;
                $Fare_data[0]['fDiscount_fixed'] = $discountValue;
                if ($total_fare < 0) {
                    $total_fare = 0;
                    //$discountValue = $total_fare;
                }
                if ($Fare_data[0]['eFareType'] == "Regular") {
                    $Fare_data[0]['fDiscount'] = $discountValue;
                    $Fare_data[0]['vDiscount'] = $vDiscount;
                } else {
                    $Fare_data[0]['fDiscount'] = $Fare_data[0]['fDiscount_fixed'];
                    $Fare_data[0]['vDiscount'] = $vDiscount;
                }
            }
            /** calculate fare * */
            $Fare_data[$i]['FareOfMinutes'] = $Minute_Fare;
            $Fare_data[$i]['FareOfDistance'] = $Distance_Fare;
            $Fare_data[$i]['FareOfCommision'] = $Commision_Fare;
            $Fare_data[$i]['fPricePerMin'] = $Fare_data[$i]['fPricePerMin'];
            $Fare_data[$i]['fPricePerKM'] = $Fare_data[$i]['fPricePerKM'];
            $Fare_data[$i]['fCommision'] = $Fare_data[$i]['fCommision'];
            $Fare_data[$i]['FinalFare'] = $total_fare;
            $Fare_data[$i]['iBaseFare'] = ($Fare_data[$i]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
            $Fare_data[$i]['iMinFare'] = round($Fare_data[$i]['iMinFare'] * $priceRatio, 2);
            if ($Fare_data[$i]['eFareType'] == "Regular") {
                //$Fare_data[$i]['total_fare'] = $vSymbol." ".number_format($total_fare,2);
                $Fare_data[$i]['total_fare'] = $vSymbol . " " . number_format($total_fare, 2);
            } else {
                $Fare_data[$i]['total_fare'] = $vSymbol . " " . number_format($Fare_data[$i]['FinalFare'], 2);
            }
            $Fare_data[$i]['iBaseFare'] = $vSymbol . " " . number_format($Fare_data[$i]['iBaseFare'], 2);
            $Fare_data[$i]['fPricePerMin'] = $vSymbol . " " . number_format(round($Fare_data[$i]['fPricePerMin'] * $priceRatio, 1), 2);
            $Fare_data[$i]['fPricePerKM'] = $vSymbol . " " . number_format(round($Fare_data[$i]['fPricePerKM'] * $priceRatio, 1), 2);
            $Fare_data[$i]['fCommision'] = $vSymbol . " " . number_format(round($Fare_data[$i]['fCommision'] * $priceRatio, 1), 2);
        }
    } else {
        $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);
        $fPickUpPrice = 1;
        $fNightPrice = 1;

        $data_surgePrice = checkSurgePrice($Fare_data[0]['iVehicleTypeId'], "");

        if ($data_surgePrice['Action'] == "0") {
            if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
            } else {
                $fNightPrice = $data_surgePrice['SurgePriceValue'];
            }
        }
        if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $eFlatTrip == "Yes") {
            $fPickUpPrice = 1;
            $fNightPrice = 1;
        }
        $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
        $Fare_data[0]['TripTimeMinutes'] = $totalTimeInMinutes_trip;
        $Fare_data[0]['TripDistance'] = $tripDistance;
        //$result = getTripFare($Fare_data[0], $surgePrice);
        /** calculate fare * */
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'];
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
        $Fare_data[0]['fPricePerKM'] = getVehicleCountryUnit_PricePerKm($Fare_data[0]['iVehicleTypeId'], $Fare_data[0]['fPricePerKM']);
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
        $Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'];
        $iBaseFare = $Fare_data[0]['iBaseFare'];


        $fPricePerKM = $Fare_data[0]['fPricePerKM'];
        $fPricePerMin = $Fare_data[0]['fPricePerMin'];

        if ($Fare_data[0]['eFareType'] == 'Fixed') {
            $Fare_data[0]['fPricePerMin'] = 0;
            $Fare_data[0]['fPricePerKM'] = 0;
            //$Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'] * $Fare_data[0]['iQty'];
            $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'] * $iQty;
        } else if ($Fare_data[0]['eFareType'] == 'Hourly') {
            $Fare_data[0]['iBaseFare'] = 0;
            $Fare_data[0]['fPricePerKM'] = 0;
            $totalHour = $Fare_data[0]['TripTimeMinutes'] / 60;
            $Fare_data[0]['TripTimeMinutes'] = $totalHour;
            $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerHour'];
        }

        $Minute_Fare = round(($fPricePerMin * $totalTimeInMinutes_trip) * $priceRatio, 2);
        $Distance_Fare = round(($fPricePerKM * $tripDistance) * $priceRatio, 2);
        $iBaseFare = round($iBaseFare * $priceRatio, 2);
        $fMaterialFee = round($Fare_data[0]['fMaterialFee'] * $priceRatio, 2);
        $fMiscFee = round($Fare_data[0]['fMiscFee'] * $priceRatio, 2);
        $fDriverDiscount = round($Fare_data[0]['fDriverDiscount'] * $priceRatio, 2);
        $fVisitFee = round($Fare_data[0]['fVisitFee'] * $priceRatio, 2);
        if ($eFlatTrip == "No") {
            $total_fare = ($iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee + $fVisitFee) - $fDriverDiscount;
            $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
            $SurgePriceFactor = strval($surgePrice);
            $total_fare = $total_fare + $fSurgePriceDiff;
            $minimamfare = round($Fare_data[0]['iMinFare'] * $priceRatio, 2);
            if ($minimamfare > $total_fare) {
                $fMinFareDiff = $minimamfare - $total_fare;
                $total_fare = $minimamfare;
                $Fare_data[0]['FinalFare'] = $total_fare;
            } else {
                $fMinFareDiff = 0;
            }
        } else {
            $total_fare = round($fFlatTripPrice * $priceRatio, 2);
            $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
            $SurgePriceFactor = strval($surgePrice);
            $total_fare = $total_fare + $fSurgePriceDiff;
            $Fare_data[0]['FinalFare'] = $total_fare;
            $fMinFareDiff = 0;
            $Minute_Fare = 0;
            $Distance_Fare = 0;
        }
        $Commision_Fare = round((($total_fare * $Fare_data[0]['fCommision']) / 100), 2);
        /* Tax Calculation */
        $fTax1 = $TaxArr['fTax1'];
        $fTax2 = $TaxArr['fTax2'];
        if ($fTax1 > 0) {
            $fTaxAmount1 = round((($total_fare * $fTax1) / 100), 2);
            $total_fare = $total_fare + $fTaxAmount1;
            $Fare_data[0]['fTax1'] = $vSymbol . " " . number_format($fTaxAmount1, 2);
        }
        if ($fTax2 > 0) {
            $total_fare_new = $total_fare - $fTaxAmount1;
            $fTaxAmount2 = round((($total_fare_new * $fTax2) / 100), 2);
            $total_fare = $total_fare + $fTaxAmount2;
            $Fare_data[0]['fTax2'] = $vSymbol . " " . number_format($fTaxAmount2, 2);
        }
        /* Tax Calculation */

        ## Calculate for Discount ##
        //$fSurgePriceDiff = $farewithsurcharge - $minimamfare;

        $discountValue = 0;
        $discountValueType = "cash";
        if ($couponCode != "") {
            $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
            $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
            if ($discountValueType == "percentage") {
                $vDiscount = round($discountValue, 1) . ' ' . "%";
                $discountValue = round(($total_fare * $discountValue), 1) / 100;
            } else {
                $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                if ($discountValue > $total_fare) {
                    $vDiscount = round($total_fare, 1) . ' ' . $curr_sym;
                } else {
                    $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
                }
            }
            $total_fare = $total_fare - $discountValue;
            $Fare_data[0]['fDiscount_fixed'] = $discountValue;
            if ($total_fare < 0) {
                $total_fare = 0;
                //$discountValue = $total_fare;
            }
            if ($Fare_data[0]['eFareType'] == "Regular") {
                $Fare_data[0]['fDiscount'] = $discountValue;
                $Fare_data[0]['vDiscount'] = $vDiscount;
            } else {
                $Fare_data[0]['fDiscount'] = $Fare_data[0]['fDiscount_fixed'];
                $Fare_data[0]['vDiscount'] = $vDiscount;
            }
        }
        ## Calculate for Discount ##
        /** calculate fare * */
        $Fare_data[0]['FareOfMinutes'] = $Minute_Fare;
        $Fare_data[0]['FareOfDistance'] = $Distance_Fare;
        $Fare_data[0]['FareOfCommision'] = $Commision_Fare;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
        $Fare_data[0]['fCommision'] = $Fare_data[0]['fCommision'];
        $Fare_data[0]['FinalFare'] = $total_fare;
        $Fare_data[0]['iBaseFare'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
        $Fare_data[0]['iMinFare'] = round($Fare_data[0]['iMinFare'] * $priceRatio, 2);
        if ($Fare_data[0]['eFareType'] == "Regular") {
            //$Fare_data[0]['total_fare'] = $vSymbol." ".number_format($total_fare,2);
            $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format($total_fare, 2);
        } else {
            $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format($Fare_data[0]['FinalFare'], 2);
        }
        $Fare_data[0]['iBaseFare'] = $vSymbol . " " . number_format($Fare_data[0]['iBaseFare'], 2);
        $Fare_data[0]['fPricePerMin'] = $vSymbol . " " . number_format(round($Fare_data[0]['fPricePerMin'] * $priceRatio, 1), 2);
        $Fare_data[0]['fPricePerKM'] = $vSymbol . " " . number_format(round($Fare_data[0]['fPricePerKM'] * $priceRatio, 1), 2);
        $Fare_data[0]['fCommision'] = $vSymbol . " " . number_format(round($Fare_data[0]['fCommision'] * $priceRatio, 1), 2);
        $vVehicleType = get_value('vehicle_type', "vVehicleType_" . $userlangcode, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $vVehicleTypeLogo = get_value('vehicle_type', "vLogo", 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $vVehicleCategoryData = get_value('vehicle_category', 'vLogo,vCategory_' . $userlangcode . ' as vCategory', 'iVehicleCategoryId', $iVehicleCategoryId);
        $Fare_data[0]['vVehicleCategory'] = $vVehicleCategoryData[0]['vCategory'];
        $vVehicleFare = get_value('vehicle_type', 'fFixedFare', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $eType = $Fare_data[0]['eFareType'];
        $tripFareDetailsArr = array();
        // echo "<pre>"; print_r($Fare_data); die;
        if ($eFlatTrip == "Yes") {
            $i = 0;
            $displayfare = round($fFlatTripPrice * $priceRatio, 2);
            $displayfare = $vSymbol . " " . number_format($displayfare, 2);
            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_FLAT_TRIP_FARE_TXT']] = $displayfare;
            $i++;
            if ($fSurgePriceDiff > 0) {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $vSymbol . " " . formatNum($fSurgePriceDiff);
                $i++;
            }
            if ($vDiscount > 0) {
                $farediscount = $vSymbol . " " . formatNum($Fare_data[0]['fDiscount']);
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = "- " . $farediscount;
                $i++;
            }
            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $Fare_data[0]['total_fare'];
            $Fare_data = $tripFareDetailsArr;
        } else {
            $i = 0;
            $countUfx = 0;
            if ($eType == "UberX") {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $Fare_data[0]['vVehicleCategory'] . "-" . $vVehicleType;
                $countUfx = 1;
            }
            if ($eType == "Regular") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vSymbol . " " . formatNum($iBaseFare);
                if ($countUfx == 1) {
                    $i++;
                }
                if ($eUnit == "Miles") {
                    $tripDistanceDisplay = $tripDistance * 0.621371;
                    $tripDistanceDisplay = round($tripDistanceDisplay, 2);
                    //$DisplayDistanceTxt = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
                    $LBL_MILE_DISTANCE_TXT = ($tripDistanceDisplay > 1) ? $languageLabelsArr['LBL_MILE_DISTANCE_TXT'] : $languageLabelsArr['LBL_ONE_MILE_TXT'];
                    $DisplayDistanceTxt = $LBL_MILE_DISTANCE_TXT;
                } else {
                    $tripDistanceDisplay = $tripDistance;
                    //$DisplayDistanceTxt = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
                    $LBL_KM_DISTANCE_TXT = ($tripDistanceDisplay > 1) ? $languageLabelsArr['LBL_DISPLAY_KMS'] : $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
                    $DisplayDistanceTxt = $LBL_KM_DISTANCE_TXT;
                }
                $tripDistanceDisplay = formatNum($tripDistanceDisplay);
                if ($isDestinationAdded == "Yes") {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $tripDistanceDisplay . " " . $DisplayDistanceTxt . ")"] = $vSymbol . " " . formatNum($Fare_data[0]['FareOfDistance']);
                } else {
                    $priceperkm = getVehiclePrice_ByUSerCountry($iUserId, $fPricePerKM);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT']] = $vSymbol . " " . formatNum($priceperkm) . "/" . strtolower($DisplayDistanceTxt);
                }
                $i++;
                //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $totalTimeInMinutes_trip . ")"] = $vSymbol . formatNum($Fare_data[0]['FareOfMinutes']);
                $hours = floor($totalTimeInMinutes_trip / 60); // No. of mins/60 to get the hours and round down
                $mins = $totalTimeInMinutes_trip % 60; // No. of mins/60 - remainder (modulus) is the minutes
                $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
                $LBL_MINUTES_TXT = ($mins > 1) ? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
                if ($hours >= 1) {
                    $tripDurationDisplay = $hours . " " . $LBL_HOURS_TXT . ", " . $mins . " " . $LBL_MINUTES_TXT;
                } else {
                    $tripDurationDisplay = $totalTimeInMinutes_trip . " " . $LBL_MINUTES_TXT;
                }
                if ($isDestinationAdded == "Yes") {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $tripDurationDisplay . ")"] = $vSymbol . " " . formatNum($Fare_data[0]['FareOfMinutes']);
                } else {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT']] = $vSymbol . " " . formatNum($fPricePerMin) . "/" . $languageLabelsArr['LBL_MIN_SMALL_TXT'];
                }
                $i++;
            } else if ($eType == "Fixed") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = ($Fare_data[0]['iQty'] > 1) ? $Fare_data[0]['iQty'] . ' X ' . $vSymbol . " " . $vVehicleFare : $vSymbol . " " . $vVehicleFare;
                if ($countUfx == 1) {
                    $i++;
                }
                $total_fare = $vVehicleFare + $Fare_data[0]['fVisitFee'] - $Fare_data[0]['fDiscount_fixed'];
                $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format(round($total_fare * $priceRatio, 1), 2);
            } else if ($eType == "Hourly") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $totalTimeInMinutes_trip . ")"] = $vSymbol . " " . $Fare_data[0]['FareOfMinutes'];
                if ($countUfx == 1) {
                    $i++;
                }
            }
            $fVisitFee = $Fare_data[0]['fVisitFee'];
            if ($fVisitFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_VISIT_FEE']] = $vSymbol . " " . $fVisitFee;
                $i++;
            }
            if ($fMaterialFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MATERIAL_FEE']] = $vSymbol . " " . $fMaterialFee;
                $i++;
            }
            if ($fMiscFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MISC_FEE']] = $vSymbol . " " . $fMiscFee;
                $i++;
            }

            if ($fMinFareDiff > 0 && $isDestinationAdded == "Yes") {
                //$minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
                $minimamfare = formatNum($minimamfare);
                $tripFareDetailsArr[$i + 1][$vSymbol . $minimamfare . " " . $languageLabelsArr['LBL_MINIMUM']] = $vSymbol . " " . formatNum($fMinFareDiff);
                $Fare_data[0]['TotalMinFare'] = $minimamfare;
                $i++;
            }

            if ($fSurgePriceDiff > 0) {
                if ($isDestinationAdded == "Yes") {
                    $normalfare = $total_fare - $fSurgePriceDiff + $vDiscount - $fTaxAmount1 - $fTaxAmount2;
                    //$normalfare = formatNum($normalfare * $priceRatio);
                    $normalfare = formatNum($normalfare);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = $vSymbol . " " . $normalfare;
                    $i++;
                }
                //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $vSymbol." ".formatNum($fSurgePriceDiff * $priceRatio);
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $vSymbol . " " . formatNum($fSurgePriceDiff);
                $i++;
            }
            if ($fDriverDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = "- " . $vSymbol . " " . $fDriverDiscount;
                $i++;
            }
            if ($vDiscount > 0) {
                //$farediscount = $vSymbol." ".number_format(round($Fare_data[0]['fDiscount'] * $priceRatio,1),2);
                $farediscount = $vSymbol . " " . formatNum($Fare_data[0]['fDiscount']);
                //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = "- " . $vSymbol . $Fare_data[0]['fDiscount'];
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = "- " . $farediscount;
                $i++;
            }
            if ($fTax1 > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fTax1 . " % "] = $Fare_data[0]['fTax1'];
                $i++;
            }
            if ($fTax2 > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fTax2 . " % "] = $Fare_data[0]['fTax2'];
                $i++;
            }

            if ($isDestinationAdded == "Yes") {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $Fare_data[0]['total_fare'];
            }
            //$Fare_data = array_merge($Fare_data[0], $tripFareDetailsArr);
            $Fare_data = $tripFareDetailsArr;
        }
    }

    return $Fare_data;
}

function getVehicleFareConfig($tabelName, $vehicleTypeID) {
    global $obj;
    $sql = "SELECT * FROM `" . $tabelName . "` WHERE iVehicleTypeId='$vehicleTypeID'";
    $Data_fare = $obj->MySQLSelect($sql);

    return $Data_fare;
}

function processTripsLocations($tripId, $latitudes, $longitudes) {
    global $obj;
    $sql = "SELECT * FROM `trips_locations` WHERE iTripId = '$tripId'";
    $DataExist = $obj->MySQLSelect($sql);

    if (count($DataExist) > 0) {

        $latitudeList = $DataExist[0]['tPlatitudes'];
        $longitudeList = $DataExist[0]['tPlongitudes'];

        if ($latitudeList != '') {
            $data_latitudes = $latitudeList . ',' . $latitudes;
        } else {
            $data_latitudes = $latitudes;
        }

        if ($longitudeList != '') {
            $data_longitudes = $longitudeList . ',' . $longitudes;
        } else {
            $data_longitudes = $longitudes;
        }

        $where = " iTripId = '" . $tripId . "'";
        $Data_tripsLocations['tPlatitudes'] = $data_latitudes;
        $Data_tripsLocations['tPlongitudes'] = $data_longitudes;
        $id = $obj->MySQLQueryPerform("trips_locations", $Data_tripsLocations, 'update', $where);
    } else {

        $sql = "SELECT tStartLat,tStartLong FROM `trips` WHERE iTripId = '$tripId'";
        $TripData = $obj->MySQLSelect($sql);
        $tStartLat = $TripData[0]['tStartLat'];
        $tStartLong = $TripData[0]['tStartLong'];
        if ($latitudes != "") {
            $insertlat = $tStartLat . "," . $latitudes;
        } else {
            $insertlat = $tStartLat;
        }
        if ($longitudes != "") {
            $insertlong = $tStartLong . "," . $longitudes;
        } else {
            $insertlong = $tStartLong;
        }

        $Data_trips_locations['iTripId'] = $tripId;
        $Data_trips_locations['tPlatitudes'] = $insertlat;
        $Data_trips_locations['tPlongitudes'] = $insertlong;

        $id = $obj->MySQLQueryPerform("trips_locations", $Data_trips_locations, 'insert');
    }
    return $id;
}

function calcluateTripDistance($tripId) {
    global $obj;
    $sql = "SELECT * FROM `trips_locations` WHERE iTripId = '$tripId'";
    $Data_tripsLocations = $obj->MySQLSelect($sql);
    $TotalDistance = 0;
    if (count($Data_tripsLocations) > 0) {
        $trip_path_latitudes = $Data_tripsLocations[0]['tPlatitudes'];
        $trip_path_longitudes = $Data_tripsLocations[0]['tPlongitudes'];
        $trip_path_latitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_latitudes);
        $trip_path_longitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_longitudes);
        $TripPathLatitudes = explode(",", $trip_path_latitudes);
        $TripPathLongitudes = explode(",", $trip_path_longitudes);
        $previousDistance = 0;
        $isFirstProcessed = false;
        for ($i = 0; $i < count($TripPathLatitudes) - 1; $i++) {
            if ($isFirstProcessed == false) {
                $firsttemplat = $TripPathLatitudes[0];
                $firsttempLon = $TripPathLongitudes[0];
                $nexttempLat = $TripPathLatitudes[$i];
                $nexttempLon = $TripPathLongitudes[$i];
                $TempDistance_First = distanceByLocation($firsttemplat, $firsttempLon, $nexttempLat, $nexttempLon, "K");
                if ($TempDistance_First > 2) {
                    continue;
                } else {
                    $isFirstProcessed = true;
                    $previousDistance = $TempDistance_First;
                    continue;
                }
            }
            $tempLat_current = $TripPathLatitudes[$i];
            $tempLon_current = $TripPathLongitudes[$i];
            $tempLat_next = $TripPathLatitudes[$i + 1];
            $tempLon_next = $TripPathLongitudes[$i + 1];
            if ($tempLat_current == '0.0' || $tempLon_current == '0.0' || $tempLat_next == '0.0' || $tempLon_next == '0.0' || $tempLat_current == '-180.0' || $tempLon_current == '-180.0' || $tempLat_next == '-180.0' || $tempLon_next == '-180.0' || ($tempLat_current == $tempLat_next && $tempLon_current == $tempLon_next)) {
                //if ($tempLat_current == '0.0' || $tempLon_current == '0.0' || $tempLat_next == '0.0' || $tempLon_next == '0.0' || $tempLat_current == '-180.0' || $tempLon_current == '-180.0' || $tempLat_next == '-180.0' || $tempLon_next == '-180.0' || $tempLat_current == $tempLat_next || $tempLon_current == $tempLon_next) {
                continue;
            }
            $TempDistance = distanceByLocation($tempLat_current, $tempLon_current, $tempLat_next, $tempLon_next, "K");
            if (is_nan($TempDistance)) {
                $TempDistance = 0;
            }
            if (abs($previousDistance - $TempDistance) > 0.1) {
                $TempDistance = 0;
            } else {
                $previousDistance = $TempDistance;
            }
            $TotalDistance += $TempDistance;
        }
    }
    return round($TotalDistance, 2);
}

/* function calcluateTripDistance($tripId) {
  global $obj;
  $sql = "SELECT * FROM `trips_locations` WHERE iTripId = '$tripId'";
  $Data_tripsLocations = $obj->MySQLSelect($sql);

  $TotalDistance = 0;
  if (count($Data_tripsLocations) > 0) {
  $trip_path_latitudes = $Data_tripsLocations[0]['tPlatitudes'];
  $trip_path_longitudes = $Data_tripsLocations[0]['tPlongitudes'];

  $trip_path_latitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_latitudes);
  $trip_path_longitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_longitudes);

  $TripPathLatitudes = explode(",", $trip_path_latitudes);

  $TripPathLongitudes = explode(",", $trip_path_longitudes);

  $previousDistance = 0;
  for ($i = 0; $i < count($TripPathLatitudes) - 1; $i++) {
  $tempLat_current = $TripPathLatitudes[$i];
  $tempLon_current = $TripPathLongitudes[$i];
  $tempLat_next = $TripPathLatitudes[$i + 1];
  $tempLon_next = $TripPathLongitudes[$i + 1];

  if ($tempLat_current == '0.0' || $tempLon_current == '0.0' || $tempLat_next == '0.0' || $tempLon_next == '0.0' || $tempLat_current == '-180.0' || $tempLon_current == '-180.0' || $tempLat_next == '-180.0' || $tempLon_next == '-180.0') {
  continue;
  }

  $TempDistance = distanceByLocation($tempLat_current, $tempLon_current, $tempLat_next, $tempLon_next, "K");

  if (is_nan($TempDistance)) {
  $TempDistance = 0;
  }
  if($previousDistance == 0){
  $previousDistance = $TempDistance;
  }else if(abs($previousDistance - $TempDistance) > 0.1){
  $TempDistance = 0;
  }else{
  $previousDistance = $TempDistance;
  }
  $TotalDistance += $TempDistance;
  }
  }

  return round($TotalDistance, 2);
  } */

/* 	function checkDistanceWithGoogleDirections($tripDistance, $startLatitude, $startLongitude, $endLatitude, $endLongitude, $isFareEstimate = "0", $vGMapLangCode = "") {
  global $generalobj, $obj, $GOOGLE_SEVER_GCM_API_KEY;

  if ($vGMapLangCode == "" || $vGMapLangCode == NULL) {
  $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault', 'Yes');
  $vGMapLangCode = $vLangCodeData[0]['vGMapLangCode'];
  }

  $GOOGLE_API_KEY = $GOOGLE_SEVER_GCM_API_KEY;
  $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $startLatitude . "," . $startLongitude . "&destination=" . $endLatitude . "," . $endLongitude . "&sensor=false&key=" . $GOOGLE_API_KEY . "&language=" . $vGMapLangCode;

  try {
  $jsonfile = file_get_contents($url);
  } catch (ErrorException $ex) {
  // return $tripDistance;

  $returnArr['Action'] = "0";
  echo json_encode($returnArr);
  exit;
  // echo 'Site not reachable (' . $ex->getMessage() . ')';
  }

  $jsondata = json_decode($jsonfile);
  $distance_google_directions = ($jsondata->routes[0]->legs[0]->distance->value) / 1000;

  if ($isFareEstimate == "0") {
  $comparedDist = ($distance_google_directions * 85) / 100;

  if ($tripDistance > $comparedDist) {
  return $tripDistance;
  } else {
  return round($distance_google_directions, 2);
  }
  } else {
  $duration_google_directions = ($jsondata->routes[0]->legs[0]->duration->value) / 60;
  $sAddress = ($jsondata->routes[0]->legs[0]->start_address);
  $dAddress = ($jsondata->routes[0]->legs[0]->end_address);
  $steps = ($jsondata->routes[0]->legs[0]->steps);

  $returnArr['Time'] = $duration_google_directions;
  $returnArr['Distance'] = $distance_google_directions;
  $returnArr['SAddress'] = $sAddress;
  $returnArr['DAddress'] = $dAddress;
  $returnArr['steps'] = $steps;

  return $returnArr;
  }
  } */

function checkDistanceWithGoogleDirections($tripDistance, $startLatitude, $startLongitude, $endLatitude, $endLongitude, $isFareEstimate = "0", $vGMapLangCode = "", $isReturnArr = false) {
    global $generalobj, $obj;

    if ($vGMapLangCode == "" || $vGMapLangCode == NULL) {
        $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault', 'Yes');
        $vGMapLangCode = $vLangCodeData[0]['vGMapLangCode'];
    }

    $GOOGLE_API_KEY = $generalobj->getConfigurations("configurations", "GOOGLE_SEVER_GCM_API_KEY");
    $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $startLatitude . "," . $startLongitude . "&destination=" . $endLatitude . "," . $endLongitude . "&sensor=false&key=" . $GOOGLE_API_KEY . "&language=" . $vGMapLangCode;

    try {
        $jsonfile = file_get_contents($url);
    } catch (ErrorException $ex) {
        // return $tripDistance;

        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
        exit;
        // echo 'Site not reachable (' . $ex->getMessage() . ')';
    }

    $jsondata = json_decode($jsonfile);
    $distance_google_directions = ($jsondata->routes[0]->legs[0]->distance->value) / 1000;

    if ($isFareEstimate == "0") {
        $comparedDist = ($distance_google_directions * 85) / 100;

        if ($isReturnArr == true) {

            if ($tripDistance > $comparedDist) {
                $distance_google_directions_val = $tripDistance;
            } else {
                $distance_google_directions_val = round($distance_google_directions, 2);
            }

            $duration_google_directions = ($jsondata->routes[0]->legs[0]->duration->value);
            $sAddress = ($jsondata->routes[0]->legs[0]->start_address);
            $dAddress = ($jsondata->routes[0]->legs[0]->end_address);
            $steps = ($jsondata->routes[0]->legs[0]->steps);

            $returnArr['Time'] = $duration_google_directions;
            $returnArr['Distance'] = $distance_google_directions_val;
            $returnArr['GDistance'] = $distance_google_directions;
            $returnArr['SAddress'] = $sAddress;
            $returnArr['DAddress'] = $dAddress;
            $returnArr['steps'] = $steps;

            return $returnArr;
        } else {
            if ($tripDistance > $comparedDist) {
                return $tripDistance;
            } else {
                return round($distance_google_directions, 2);
            }
        }
    } else {
        $duration_google_directions = ($jsondata->routes[0]->legs[0]->duration->value) / 60;
        $sAddress = ($jsondata->routes[0]->legs[0]->start_address);
        $dAddress = ($jsondata->routes[0]->legs[0]->end_address);
        $steps = ($jsondata->routes[0]->legs[0]->steps);

        $returnArr['Time'] = $duration_google_directions;
        $returnArr['Distance'] = $distance_google_directions;
        $returnArr['SAddress'] = $sAddress;
        $returnArr['DAddress'] = $dAddress;
        $returnArr['steps'] = $steps;

        return $returnArr;
    }
}

function distanceByLocation($lat1, $lon1, $lat2, $lon2, $unit) {
    if ((($lat1 == $lat2) && ($lon1 == $lon2)) || ($lat1 == '' || $lon1 == '' || $lat2 == '' || $lon2 == '')) {
        return 0;
    }

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

function getLanguageLabelsArr_01092017($lCode = '', $directValue = "") {
    global $obj;

    /* find default language of website set by admin */
    $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
    $default_label = $obj->MySQLSelect($sql);

    if ($lCode == '') {
        $lCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }


    $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label`  WHERE lPage_id >= 27 AND  `vCode` = '" . $lCode . "' ";
    $all_label = $obj->MySQLSelect($sql);

    $x = array();
    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $x[$vLabel] = $vValue;
    }

    /*
      $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_other`  WHERE  `vCode` = '" . $lCode . "' ";
      $all_label = $obj->MySQLSelect($sql);

      for ($i = 0; $i < count($all_label); $i++) {
      $vLabel = $all_label[$i]['vLabel'];

      $vValue = $all_label[$i]['vValue'];
      $x[$vLabel] = $vValue;
      } */

    $x['vCode'] = $lCode; // to check in which languge code it is loading

    if ($directValue == "") {
        $returnArr['Action'] = "1";
        $returnArr['LanguageLabels'] = $x;

        return $returnArr;
    } else {
        return $x;
    }
}

function getLanguageLabelsArr($lCode = '', $directValue = "", $iServiceId = "") {
    global $obj;

    /* find default language of website set by admin */
    $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
    $default_label = $obj->MySQLSelect($sql);

    if ($lCode == '') {
        $lCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }

    if (empty($iServiceId)) {
        $iServiceId = $_REQUEST["iServiceId"];
		$sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label` WHERE  `vCode` = '" . $lCode . "'";
    }else{
		$sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_" . $iServiceId . "` WHERE  `vCode` = '" . $lCode . "'";
	}

    //$sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_".$iServiceId."` WHERE  `vCode` = '" . $lCode . "' UNION SELECT `vLabel` , `vValue`  FROM  `language_label_other` WHERE  `vCode` = '" . $lCode . "' ";
    
    $all_label = $obj->MySQLSelect($sql);

    $x = array();
    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $x[$vLabel] = $vValue;
    }


    /* $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_other`  WHERE  `vCode` = '" . $lCode . "' ";
      $all_label = $obj->MySQLSelect($sql);

      for ($i = 0; $i < count($all_label); $i++) {
      $vLabel = $all_label[$i]['vLabel'];

      $vValue = $all_label[$i]['vValue'];
      $x[$vLabel] = $vValue;
      } */

    $x['vCode'] = $lCode; // to check in which languge code it is loading

    if ($directValue == "") {
        $returnArr['Action'] = "1";
        $returnArr['LanguageLabels'] = $x;

        return $returnArr;
    } else {
        return $x;
    }
}

function sendEmeSms($toMobileNum, $message) {
    global $generalobj, $MOBILE_VERIFY_SID_TWILIO, $MOBILE_VERIFY_TOKEN_TWILIO, $MOBILE_NO_TWILIO;
    $account_sid = $MOBILE_VERIFY_SID_TWILIO;
    $auth_token = $MOBILE_VERIFY_TOKEN_TWILIO;
    $twilioMobileNum = $MOBILE_NO_TWILIO;

    $client = new Services_Twilio($account_sid, $auth_token);
    try {
        $sms = $client->account->messages->sendMessage($twilioMobileNum, $toMobileNum, $message);
        return 1;
    } catch (Services_Twilio_RestException $e) {
        return 0;
    }
}

function converToTz($time, $toTz, $fromTz, $dateFormat = "Y-m-d H:i:s") {
    $date = new DateTime($time, new DateTimeZone($fromTz));
    $date->setTimezone(new DateTimeZone($toTz));
    $time = $date->format($dateFormat);
    return $time;
}

/**
 * Sending Push Notification
 */
function send_notification($registatoin_ids, $message, $filterMsg = 0) {
    // include config
    // include_once './config.php';
    //global $generalobj, $obj,$FIREBASE_API_ACCESS_KEY,$ENABLE_PUBNUB;
    global $generalobj, $obj;


    $FIREBASE_API_ACCESS_KEY = $generalobj->getConfigurations("configurations", "FIREBASE_API_ACCESS_KEY");
    $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");

    $fields = array
        (
        'registration_ids' => $registatoin_ids,
        'click_action' => ".MainActivity",
        'priority' => "high",
        //'data'          => $msg
        'data' => $message,
        'type'=>'orders'    
    );

    $finalFields = json_encode($fields, JSON_UNESCAPED_UNICODE);


    if ($filterMsg == 1) {
        $finalFields = stripslashes(preg_replace("/[\n\r]/", "", $finalFields));
    }

    $headers = array
        (
        'Authorization: key=' . $FIREBASE_API_ACCESS_KEY,
        'Content-Type: application/json',
    );
    //Setup headers:
    // echo "<pre>";print_r($headers);exit;
    //Setup curl, add headers and post parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $finalFields);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    
    //Send the request
    $response = curl_exec($ch); //echo "<pre>";print_r($response);exit;
    if ($response === FALSE) {
        // die('Curl failed: ' . curl_error($ch));
        if ($ENABLE_PUBNUB == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_SERVER_COMM_ERROR";
            $returnArr['ERROR'] = curl_error($ch);
            echo json_encode($returnArr);
            exit;
        }
    }
    $responseArr = json_decode($response);//echo '<pre>';print_r($responseArr);
    $success = $responseArr->success;
    /*    try {
      $log = "Start: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
      "Data: " . $finalFields . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
      "Response: " . ($response) . PHP_EOL . "-------------------------" . PHP_EOL;
      //Save string to log, use FILE_APPEND to append.
      saveNotiLog($log);
      } catch (Exception $e) {

      } */
    //Close request
    curl_close($ch);
    return $success;
}

function sendApplePushNotification($PassengerToDriver = 0, $deviceTokens, $message, $alertMsg, $filterMsg = 0, $fromDepart = '') {
	global $generalobj, $obj;//print_r($message);
    //$message = json_decode($message);
	//$deviceTokens = array('eSGc0lfNlik:APA91bFtSIN6ebEMJcr2QPWk9VtYGciTyFEtyTklsE_W2VWoAA7eDkZR8atsz4Ka9JiL6ZLeQVvMvrxwrK8fhmwpfZELU1iNcgXt4ZrkAzAGGovaTatximU_DN9cXwPL9Mhx83-Ma_Vv');print_r($deviceTokens);
	$url = 'https://fcm.googleapis.com/fcm/send';
	$FIREBASE_API_ACCESS_KEY = $generalobj->getConfigurations("configurations", "FIREBASE_API_ACCESS_KEY_IOS");
	if (!is_array($deviceTokens)) {
		$deviceTokens = array($deviceTokens);
	}

	if (!is_array($message)) {
		$message = array("message" => $message);
	}
	/*$fields = array (
		'to' => $deviceTokens,
		"content_available"=> true,
		"mutable_content"=> true,
		"data" => $message,
		
	);*/
	$image = array('url'=>'');
	if(isset($message['image'])){
		$image =  array('url'=>$message['image']);
	}
	$new_array = array_values($deviceTokens);
	if(isset($message['content_available'])){
		$fields = array (
			'registration_ids' => $new_array,
			"content_available"=>true,
			'priority' => "high",
			"data" => $message,
			'type'=>'orders'   
		);
	}else{
		$fields = array (
			'registration_ids' => $new_array,
			"notification" => $message,
			"data" => $image,
			'priority' => "high",
			'type'=>'orders'   
		);

	}
	

	$finalFields = json_encode($fields, JSON_UNESCAPED_UNICODE);

	//define("GOOGLE_API_KEY", $google_key);
	define("FIREBASE_API_ACCESS_KEY", $FIREBASE_API_ACCESS_KEY);
	
	$headers = array
		(
		'Authorization: key=' .FIREBASE_API_ACCESS_KEY,
		'Content-Type: application/json',
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_POST, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $finalFields);

	$result = curl_exec($ch);
	
	if ($result === FALSE) {

		echo 'Curl error: ' . curl_error($ch);

	}

	//curl_close($ch);

	$responseArr = json_decode($result);
	//echo '<pre>';print_r($fields);print_r($responseArr);die('asdf');
	/*
	try {
		$log = "Start: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
			"Data: " . $finalFields . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
			"Response: " . ($result) .'---'.$app.'---'.$FIREBASE_API_ACCESS_KEY.'----------IOStoken----'.json_encode($deviceTokens).date_default_timezone_get(). PHP_EOL . "-------------------------" . PHP_EOL;
		//Save string to log, use FILE_APPEND to append.
		$this->saveNotiLog($log);
	} catch (Exception $e) {    
	}*/


	$success = $responseArr->success;
	//Close request
	curl_close($ch);
	return $success;
}


function time_Ago($time) {
  
		// Calculate difference between current 
		// time and given timestamp in seconds 
		if(time() > $time){
			$diff     = time() - $time; 
		}else{
			$diff     = $time - time(); 
		}
		  
		// Time difference in seconds 
		$sec     = $diff; 
		  
		// Convert time difference in minutes 
		$min     = round($diff / 60 ); 
		  
		// Convert time difference in hours 
		$hrs     = round($diff / 3600); 
		  
		// Convert time difference in days 
		$days     = round($diff / 86400 ); 
		  
		// Convert time difference in weeks 
		$weeks     = round($diff / 604800); 
		  
		// Convert time difference in months 
		$mnths     = round($diff / 2600640 ); 
		  
		// Convert time difference in years 
		$yrs     = round($diff / 31207680 ); 
		  
		// Check for seconds 
		if($sec <= 60) { 
			return "$sec seconds ago"; 
		} 
		  
		// Check for minutes 
		else if($min <= 60) { 
			if($min==1) { 
				return "one minute ago"; 
			} 
			else { 
				return "$min minutes ago"; 
			} 
		} 
		  
		// Check for hours 
		else if($hrs <= 24) { 
			if($hrs == 1) {  
				return "an hour ago"; 
			} 
			else { 
				return "$hrs hours ago"; 
			} 
		} 
		  
		// Check for days 
		else if($days <= 7) { 
			if($days == 1) { 
				return "Yesterday"; 
			} 
			else { 
				return "$days days ago"; 
			} 
		} 
		  
		// Check for weeks 
		else if($weeks <= 4.3) { 
			if($weeks == 1) { 
				return "a week ago"; 
			} 
			else { 
				return "$weeks weeks ago"; 
			} 
		} 
		  
		// Check for months 
		else if($mnths <= 12) { 
			if($mnths == 1) { 
				return "a month ago"; 
			} 
			else { 
				return "$mnths months ago"; 
			} 
		} 
		  
		// Check for years 
		else { 
			if($yrs == 1) { 
				return "one year ago"; 
			} 
			else { 
				return "$yrs years ago"; 
			} 
		} 
	} 

/* Apple Push in order */

function sendApplePushNotificationOrder($PassengerToDriver = 0, $deviceTokens, $message, $alertMsg, $filterMsg = 0, $fromDepart = '') {
    //global $generalobj, $obj, $IPHONE_PEM_FILE_PASSPHRASE,$APP_MODE,$ENABLE_PUBNUB, $PARTNER_APP_IPHONE_PEM_FILE_NAME, $PASSENGER_APP_IPHONE_PEM_FILE_NAME;
    global $generalobj, $obj;

    $sql = "select vValue,vName from configurations where vName in('IPHONE_PEM_FILE_PASSPHRASE','APP_MODE','ENABLE_PUBNUB','PARTNER_APP_IPHONE_PEM_FILE_NAME','PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PARTNER_APP_IPHONE_PEM_FILE_NAME','COMPANY_APP_IPHONE_PEM_FILE_NAME','PRO_COMPANY_APP_IPHONE_PEM_FILE_NAME')";
    $Data_config = $obj->MySQLSelect($sql);

    for ($i = 0; $i < count($Data_config); $i++) {
        $temp_val = $Data_config[$i]['vValue'];
        $temp_vName = $Data_config[$i]['vName'];
        $$temp_vName = $temp_val;
    }

    if ($message == "") {
        return "";
    }
    $passphrase = $IPHONE_PEM_FILE_PASSPHRASE;
    //$APP_MODE = $APP_MODE;
    //$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");

    $prefix = "";
    $url_apns = 'ssl://gateway.sandbox.push.apple.com:2195';
    if ($APP_MODE == "Production") {
        $prefix = "PRO_";
        $url_apns = 'ssl://gateway.push.apple.com:2195';
    }

    if ($PassengerToDriver == 1) {
        //$name = $generalobj->getConfigurations("configurations", $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME");    // send notification to driver
        $name1 = $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else if ($PassengerToDriver == 2) {
        //$name = $generalobj->getConfigurations("configurations", $prefix . "COMPANY_APP_IPHONE_PEM_FILE_NAME");    // send notification to company
        $name1 = $prefix . "COMPANY_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else {
        //$name = $generalobj->getConfigurations("configurations", $prefix . "PASSENGER_APP_IPHONE_PEM_FILE_NAME");  // send notification to passenger
        $name1 = $prefix . "PASSENGER_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    }
    $ctx = stream_context_create();

    if ($fromDepart == 'admin') {
        $name = '../' . $name;
    }
    stream_context_set_option($ctx, 'ssl', 'local_cert', $name);

    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    $fp = stream_socket_client(
            $url_apns, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

    // echo "deviceTokens => <pre>";
    // print_r($deviceTokens);
    // echo "<pre>"; print_r($fp); die;
    if (!$fp) {
        if ($ENABLE_PUBNUB == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_SERVER_COMM_ERROR";
            $returnArr['ERROR'] = $err . $errstr . " " . PHP_EOL;
            echo json_encode($returnArr);
            exit;
            //exit("Failed to connect: $err $errstr" . PHP_EOL);
        }
    }

    // Create the payload body

    $body['aps'] = array(
        'alert' => $alertMsg,
        //'content-available' => 1,
        "badge" => 0,
        'body' => $message,
        'sound' => 'default'
    );

    // Encode the payload as JSON
    $payload = json_encode($body, JSON_UNESCAPED_UNICODE);

    //        $payload= stripslashes(preg_replace("/[\n\r]/","",$payload));
    if ($filterMsg == 1) {
        $payload = stripslashes(preg_replace("/[\n\r]/", "", $payload));
    }

    for ($device = 0; $device < count($deviceTokens); $device++) {
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokens[$device]) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
    }

    /*   try {
      $log = "StartApnsNoti: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
      "Data: " . $payload . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
      "Response: " . ($result) . PHP_EOL . "-------------------------" . PHP_EOL;
      //Save string to log, use FILE_APPEND to append.
      saveNotiLog($log);
      } catch (Exception $e) {

      } */

    // Close the connection to the server
    fclose($fp);
}

/* Apples Push in order Close */



/* New_Change_Anviam */
/*
 * *********************Check IS base******************************
 */

function CheckLocationOfBase($getAdressarray) {
    global $generalobj, $obj;
    $ssql = "";
    $isbase = 'no';
    $getBaselists = array();
    if (!empty($getAdressarray)) {
        $sqlaa = "SELECT `iLocationId`, `iCountryId`, `vLocationName`, `tLatitude`, `tLongitude`, `eStatus` FROM `baselocations` WHERE `eStatus`='Active'" . $ssql;
        $allowed_data = $obj->MySQLSelect($sqlaa);
        //print_r($allowed_data);
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($getAdressarray, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {

                        $getBaselists = array(
                            'id' => $val['iLocationId'],
                            'base' => $val['vLocationName']
                        );
                        break;
                    }
                }
            }
        }
    }

    if (!empty($getBaselists)) {
        $isbase = "yes";
        $result = array(
            'isbase' => $isbase,
            'id' => $getBaselists['id'],
            'base' => $getBaselists['base'],
            'building' => '',
            'latitude' => '',
            'longitude' => '',
        );
    } else {
        $result = array(
            'isbase' => $isbase,
            'id' => '0',
            'base' => '0',
            'building' => '0',
            'latitude' => '0',
            'longitude' => '0',
        );
    }

    return $result;
}

function CheckUserOnBase($lat, $long) {

    global $generalobj, $obj;
    $isbase = "no";
    $result = array();

    $BaseQuery = "SELECT `id`, `base`, `region`, `building`, `latitude`, `longitude`, `eStatus` , 111.111 *
            DEGREES(ACOS(COS(RADIANS($lat))
                 * COS(RADIANS(`latitude`))
                 * COS(RADIANS($long - `longitude`))
                 + SIN(RADIANS($lat))
                 * SIN(RADIANS(`latitude`)))) AS distance FROM militarylocations WHERE eStatus = 'active'  HAVING distance < '0.5' ORDER BY distance ASC LIMIT 3";
    $getBaselists = $obj->MySQLSelect($BaseQuery);
    // echo $getBaselists[0]['base'];
    //echo "<pre>";  print_r($getBaselists);
    if (!empty($getBaselists)) {
        $isbase = "yes";
        $result = array(
            'isbase' => $isbase,
            'id' => $getBaselists[0]['id'],
            'base' => $getBaselists[0]['base'],
            'building' => $getBaselists[0]['building'],
            'latitude' => $getBaselists[0]['latitude'],
            'longitude' => $getBaselists[0]['longitude'],
        );
    } else {
        $result = array(
            'isbase' => $isbase,
            'id' => '0',
            'base' => '0',
            'building' => '0',
            'latitude' => '0',
            'longitude' => '0',
        );
    }
    //   print_r($result);
    return $result;
}

/*
 *  ***************** Rearrannge Driver Task List
 */

function RearrangeDriverTask($PickArray, $DropArray) {
    $CombinedData = array_merge($PickArray, $DropArray);
    array_multisort(array_column($CombinedData, 'distance'), SORT_ASC, array_column($CombinedData, 'type'), SORT_DESC, $CombinedData);
    return $CombinedData;
}

/*
 * *********************GET BEST ORDER MIX*******************************
 */

function getbestordermix($picklat, $picklong, $droplat, $droplong, $iorderid) {
    global $generalobj, $obj;
    //date('Y-m-d').
    $DayStart = '2018-09-01 00:00:01';
    $DayEnd = date('Y-m-d H:i:s');
    $orderdetails = "SELECT * FROM `orders` WHERE iStatusCode = 2 AND tOrderRequestDate BETWEEN '$DayStart' AND '$DayEnd' ;";
    $OrderQueries = $obj->MySQLSelect($orderdetails);
    foreach ($OrderQueries as $OrdersD) {
        
    }
    echo "<pre>";
    print_r($OrderQuery);
    echo "</pre>";

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

/*
 * ********************************Get othertime script **************************
 */

function GetTimeDiffInMinutes($Enddate) {
    $starttime = date('Y-m-d H:i:s');
    $start_date = new DateTime($starttime);
    $since_start = $start_date->diff(new DateTime($Enddate));
    $minutes = $since_start->days * 24 * 60;
    $minutes += $since_start->h * 60;
    $minutes += $since_start->i;
    return $minutes;
}

/*
 * ******************************* GET FREE DRIVER********************************************
 */

function getdeclinedriverlistByorderId($iOrderId) {
    global $generalobj, $obj;
    $sql = "SELECT DISTINCT(`iDriverId`) FROM `driver_request` WHERE `iOrderId` = '$iOrderId' AND `eStatus` = 'Decline';";
    $list = '';
    $Data = $obj->MySQLSelect($sql);
    if (!empty($Data)) {
        foreach ($Data as $datalist) {
            $list .= $datalist['iDriverId'] . ',';
        }
    }
    return $list;
}

function getFreeDriver($sourceLat, $sourceLon, $address_data = array(), $destLat = "", $destLon = "", $EarlierDriverlist, $iOrderId) {
    global $generalobj, $obj;



    $checkDeclineDriverlist = getdeclinedriverlistByorderId($iOrderId);

    $NotINQuery = '';
    if (!empty($EarlierDriverlist) && !empty($checkDeclineDriverlist)) {
        $finalDeclinelist = $EarlierDriverlist . $checkDeclineDriverlist;
        $finalDeclinelist = substr($finalDeclinelist, 0, -1);
        $NotINQuery = 'AND iDriverId NOT IN (' . $finalDeclinelist . ')';
    } elseif (!empty($checkDeclineDriverlist)) {
        $checkDeclineDriverlist = substr($checkDeclineDriverlist, 0, -1);
        $NotINQuery = 'AND iDriverId NOT IN (' . $checkDeclineDriverlist . ')';
    } elseif (!empty($EarlierDriverlist)) {
        $EarlierDriverlist = substr($EarlierDriverlist, 0, -1);
        $NotINQuery = 'AND iDriverId NOT IN (' . $EarlierDriverlist . ')';
    }

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

    $sourceLocationArr = array($sourceLat, $sourceLon);
    $destinationLocationArr = array($destLat, $destLon);
    $ssql_available = "";
    $allowed_ans = "Yes";
    $allowed_ans_drop = "Yes";
    $vLatitude = 'vLatitude';
    $vLongitude = 'vLongitude';
    //if ($Check_Driver_UFX == "No") {  
    $ssql_available .= " AND vAvailability = 'Available'  AND vTripStatus NOT IN ('Active', 'On Going Trip') AND tLocationUpdateDate > '$str_date'  ";
    //AND iAvailable = '1'
    // }

    if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
        $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
			* cos( radians( ROUND(" . $vLatitude . ",8) ) )
			* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
			+ sin( radians(" . $sourceLat . ") )
			* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver`
			WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active') $NotINQuery HAVING distance < '15' ORDER BY distance ASC  LIMIT 1";
        //HAVING distance < '15'
        //" . $LIST_DRIVER_LIMIT_BY_DISTANCE . "

        $Data = $obj->MySQLSelect($sql);
        //echo "<pre>"; print_r($Data);
        $newData = array();
        $j = 0;
        $driver_id_auto = "";
        for ($i = 0; $i < count($Data); $i++) {
            $lastprocessstime = checklastorderprocesstime($Data[$i]['iDriverId']);
            $Data[$i]['lastproccessedtime'] = $lastprocessstime[0]['tEndDate'] ? $lastprocessstime[0]['tEndDate'] : 0;

            $pickupdistime = GetDrivingDistance($sourceLat, $Data[$i]['vLatitude'], $sourceLon, $Data[$i]['vLongitude']);
            $Data[$i]['pickupdistance'] = $pickupdistime['distance'];
            $Data[$i]['pickuptime'] = $pickupdistime['time'];

            $iDriverVehicleId = $Data[$i]['iDriverVehicleId'];
            $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
            $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');

            $distanceusercompany = distanceByLocation($sourceLat, $sourceLon, $destLat, $destLon, "K");
            $Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];

            $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
            $nooftrips = countnooftrips($Data[$i]['iDriverId']);
            //  if (($fRadius > $distanceusercompany) && ($nooftrips < 2)) {
            $driver_id_auto .= $Data[$i]['iDriverId'] . ",";
            $newData[$j] = $Data[$i];
            $j++;
            //}
        }
        //print_r($newData);
        $driver_id_auto = substr($driver_id_auto, 0, -1);

        //$returnData['DriverList'] = $Data;
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = $driver_id_auto;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    } else {
        /* $Data = array();
          $returnData['DriverList'] = $Data; */
        $newData = array();
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = "";
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    }

    return $returnData;
}

/*
 * ************************* Get Three nearest Driver list Partial **************************
 */

function getThreePartialDrivers($sourceLat, $sourceLon, $destLat = "", $destLon = "") {
    global $generalobj, $obj;
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $sourceLocationArr = array($sourceLat, $sourceLon);
    $destinationLocationArr = array($destLat, $destLon);
    $ssql_available = "";
    $allowed_ans = "Yes";
    $allowed_ans_drop = "Yes";
    $vLatitude = 'vLatitude';
    $vLongitude = 'vLongitude';

    //AND vAvailability = 'Available' AND vTripStatus != 'Active'
    $ssql_available .= "  AND tLocationUpdateDate > '$str_date' ";


    if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
        $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
			* cos( radians( ROUND(" . $vLatitude . ",8) ) )
			* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
			+ sin( radians(" . $sourceLat . ") )
			* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver`
			WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active')
			 ORDER BY distance ASC LIMIT 3";
        //HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . "
        $Data = $obj->MySQLSelect($sql);
        //echo "<pre>"; print_r($Data);
        $newData = array();
        $j = 0;
        $driver_id_auto = "";
        for ($i = 0; $i < count($Data); $i++) {
            $lastprocessstime = checklastorderprocesstime($Data[$i]['iDriverId']);
            $Data[$i]['lastproccessedtime'] = $lastprocessstime[0]['tEndDate'] ? $lastprocessstime[0]['tEndDate'] : 0;

            $pickupdistime = GetDrivingDistance($sourceLat, $Data[$i]['vLatitude'], $sourceLon, $Data[$i]['vLongitude']);
            $Data[$i]['pickupdistance'] = $pickupdistime['distance'];
            $Data[$i]['pickuptime'] = $pickupdistime['time'];

            $iDriverVehicleId = $Data[$i]['iDriverVehicleId'];
            $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
            $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');

            $distanceusercompany = distanceByLocation($sourceLat, $sourceLon, $destLat, $destLon, "K");
            $Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];

            $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
            $nooftrips = countnooftrips($Data[$i]['iDriverId']);

            $driver_id_auto .= $Data[$i]['iDriverId'] . ",";
            $newData[$j] = $Data[$i];
            $j++;
        }
        //print_r($newData);
        $driver_id_auto = substr($driver_id_auto, 0, -1);

        //$returnData['DriverList'] = $Data;
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = $driver_id_auto;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    } else {
        /* $Data = array();
          $returnData['DriverList'] = $Data; */
        $newData = array();
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = "";
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    }

    return $returnData;
}

/*
 * ************************** Check If request Decline ****************************
 */

function GetDriverDeclineRequest($iDriverId, $iOrderId) {
    global $generalobj, $obj;
    $sql = "SELECT `iDriverId` FROM `driver_request` WHERE `iDriverId` = '$iDriverId' AND `iOrderId` = '$iOrderId'  AND `eStatus` = 'Decline';";
    //HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . "
    $datas = $obj->MySQLSelect($sql);
    $response = 'NO';

    if (count($datas) != 0) {
        $response = 'YES';
    }

    return $response;
}

/*
 * ******************** GET Driver from Trips**********************
 */

function GetDriverListFromSamePickup($iCompanyId) {
    global $generalobj, $obj;

    $notInCondition = '';
//    $OngoingDriverid = GetDriverListOngoing();
//    if (!empty($OngoingDriverid)) {
//        $notInCondition = "AND `register_driver`.`iDriverId` NOT IN ($OngoingDriverid)";
//    }
    $sql = "SELECT DISTINCT(`trips`.`iDriverId`),`register_driver`.vLatitude ,`register_driver`.vLongitude FROM `trips`  JOIN `register_driver` ON `trips`.`iDriverId` = `register_driver`.`iDriverId`  WHERE `trips`.`iCompanyId` = '" . $iCompanyId . "' AND `trips`.`iActive` = 'Active'  AND `register_driver`.`eStatus` = 'Active'  $notInCondition ORDER BY `trips`.`tTripRequestDate` DESC LIMIT 3";
    //AND `register_driver`.`iAvailable` = '1'
    //HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . "
    $data = $obj->MySQLSelect($sql);
    //echo "yes";
    return $data;
}

function GetDriverListSameDeliveryLocation($CheckLat, $CheckLong) {
    global $generalobj, $obj;
//    $notInCondition = '';
//     //$OngoingDriverid = GetDriverListOngoing();
//    if (!empty($NotDriverid)) {
//        $notInCondition = "AND `register_driver`.`iDriverId` NOT IN ($NotDriverid)";
//        //, $OngoingDriverid
//    }

    $sql = "SELECT DISTINCT(`trips`.`iDriverId`),`register_driver`.vLatitude ,`register_driver`.vLongitude , 111.111 * DEGREES(ACOS(COS(RADIANS($CheckLat)) * COS(RADIANS(`trips`.`tEndLat`)) * COS(RADIANS($CheckLong - `trips`.`tEndLong`)) + SIN(RADIANS($CheckLat)) * SIN(RADIANS(`trips`.`tEndLat`)))) AS distance FROM `trips` JOIN `register_driver` ON `trips`.`iDriverId` = `register_driver`.`iDriverId` WHERE `trips`.`iActive` = 'Active' AND `register_driver`.`eStatus` = 'Active'  HAVING distance < '10' ORDER BY distance ASC LIMIT 3 ";


    //$sql = "SELECT DISTINCT(`driver_request`.`iDriverId`),`register_driver`.vLatitude ,`register_driver`.vLongitude , 111.111 * DEGREES(ACOS(COS(RADIANS($CheckLat)) * COS(RADIANS(`user_address`.`vLatitude`)) * COS(RADIANS($CheckLong - `user_address`.`vLongitude`)) + SIN(RADIANS($CheckLat)) * SIN(RADIANS(`user_address`.`vLatitude`)))) AS distance FROM `driver_request` JOIN `orders` ON `orders`.`iOrderId` = `driver_request`.`iOrderId` JOIN `user_address` ON `user_address`.`iUserAddressId` = `orders`.`iUserAddressId` JOIN `register_driver` ON `register_driver`.`iDriverId` = `driver_request`.`iDriverId` WHERE `driver_request`.`eStatus` IN( 'Accept', 'Timeout', 'Received') AND `register_driver`.`eStatus` = 'Active'  HAVING distance < '10' ORDER BY distance ASC LIMIT 3";
    //AND `register_driver`.`iAvailable` = '1'
    //HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . "
    $data = $obj->MySQLSelect($sql);
    //echo "yes";
    return $data;
}

/*
 *  Near By Location Driver List
 */

function GetDriverListOngoing() {
    global $generalobj, $obj;
    $sql = "SELECT DISTINCT(`trips`.`iDriverId`),`register_driver`.vLatitude ,`register_driver`.vLongitude FROM `trips`  JOIN `register_driver` ON `trips`.`iDriverId` = `register_driver`.`iDriverId`  WHERE `trips`.`iActive` = 'On Going Trip'  AND `register_driver`.`eStatus` = 'Active' ORDER BY `trips`.`tTripRequestDate` DESC LIMIT 3";
    //HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . "
    $datas = $obj->MySQLSelect($sql);

    $NotDriverid = '';
    if (!empty($datas)) {
        foreach ($datas as $data):
            $NotDriverid .= $data['iDriverId'] . ',';
        endforeach;
    }

    return substr($NotDriverid, 0, -1);
}

/* For Operation Dashboard */

function GetAllOrdersDriver($iDriverId) {
    global $generalobj, $obj;
    $DayStart = date('Y-m-d') . ' 00:00:01';
    $DayEnd = date('Y-m-d H:i:s');
    $sql = "SELECT iUserId,iDriverId,iCompanyId,iOrderId,vOrderNo,iUserAddressId,iStatusCode,ePaid,ePaymentOption,cookingtime,deliverytime, vInstruction FROM orders where iStatusCode IN (3,4,5) AND iDriverId = '" . $iDriverId . "' AND orders.tOrderRequestDate BETWEEN '$DayStart' AND '$DayEnd';";
    $returnArrData = $obj->MySQLSelect($sql);

    $result = '';
    if (!empty($returnArrData)) {
        //array start
        for ($i = 0; $i < count($returnArrData); $i++) {
            $result .= $returnArrData[$i]['vOrderNo'] . ', <br/>';
        }
    }
    return $result;
}

function GetCountAllOrdersDriver($iDriverId) {
    global $generalobj, $obj;
    $DayStart = date('Y-m-d') . ' 00:00:01';
    $DayEnd = date('Y-m-d H:i:s');
    $sql = "SELECT count(iOrderId) as countss FROM orders where iStatusCode IN (3,4,5) AND iDriverId = '" . $iDriverId . "' AND orders.tOrderRequestDate BETWEEN '$DayStart' AND '$DayEnd';";
    $returnArrData = $obj->MySQLSelect($sql);

    $result = 0;
    if (!empty($returnArrData)) {
        $result = $returnArrData[0]['countss'];
    }
    return $result;
}

/* For Operation Dashboard */

function CheckDriverStatusIFonGoingWithOrders($iDriverId) {
    global $generalobj, $obj;
    $DayStart = date('Y-m-d') . ' 00:00:01';
    $DayEnd = date('Y-m-d H:i:s');
    $sql = "SELECT `trips`.`iDriverId`,`trips`.`tTripRequestDate` FROM `trips` WHERE `trips`.`iActive` IN('Active','On Going Trip')  AND `trips`.`iDriverId` = '$iDriverId' AND `trips`.`tTripRequestDate` BETWEEN  '$DayStart' AND '$DayEnd' ORDER BY `trips`.`tTripRequestDate` ASC LIMIT 3";
    //HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . "
    $datas = $obj->MySQLSelect($sql);


    if (count($datas) != 0) {
        $response = 'yes';
    } else {
        $response = 'no';
    }
    return $response;
}

/* For Operation Dashboard */

function GetDriverStatusIFonGoingWithOrders($iDriverId) {
    global $generalobj, $obj;
    $sql = "SELECT `trips`.`iDriverId`,`trips`.`tTripRequestDate`,`register_driver`.vLatitude ,`register_driver`.vLongitude FROM `trips`  JOIN `register_driver` ON `trips`.`iDriverId` = `register_driver`.`iDriverId`  WHERE `trips`.`iActive` IN('Active', 'On Going Trip')  AND `register_driver`.`iDriverId` = '$iDriverId' ORDER BY `trips`.`tTripRequestDate` ASC LIMIT 1";
    //HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . "
    $datas = $obj->MySQLSelect($sql);


    if (count($datas) != 0) {
        $response = $datas[0]['tTripRequestDate'];
    } else {
        $response = '';
    }
    return $response;
}

function GetDriverStatusIFonGoing($iDriverId) {
    global $generalobj, $obj;
    $sql = "SELECT `trips`.`iDriverId`,`register_driver`.vLatitude ,`register_driver`.vLongitude FROM `trips`  JOIN `register_driver` ON `trips`.`iDriverId` = `register_driver`.`iDriverId`  WHERE `trips`.`iActive` = 'On Going Trip'  AND `register_driver`.`iDriverId` = '$iDriverId' ORDER BY `trips`.`tTripRequestDate` DESC LIMIT 3";
    //HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . "
    $datas = $obj->MySQLSelect($sql);
    $response = 'NO';

    if (count($datas) != 0) {
        $response = 'YES';
    }

    return $response;
}

function GetDriverListNearBYLocation($CheckLat, $CheckLong, $NotDriverid) {
    global $generalobj, $obj;
    $notInCondition = '';
    //$OngoingDriverid = GetDriverListOngoing();
    if (!empty($NotDriverid)) {
        $notInCondition = "AND `register_driver`.`iDriverId` NOT IN ($NotDriverid)";
        //, $OngoingDriverid
    }

    $sql = "SELECT DISTINCT(`trips`.`iDriverId`),`register_driver`.vLatitude ,`register_driver`.vLongitude , 111.111 * DEGREES(ACOS(COS(RADIANS($CheckLat)) * COS(RADIANS(`register_driver`.`vLatitude`)) * COS(RADIANS($CheckLong - `register_driver`.`vLongitude`)) + SIN(RADIANS($CheckLat)) * SIN(RADIANS(`register_driver`.`vLatitude`)))) AS distance FROM `trips` JOIN `register_driver` ON `trips`.`iDriverId` = `register_driver`.`iDriverId` WHERE `trips`.`iActive` = 'Active' AND `register_driver`.`eStatus` = 'Active'  $notInCondition HAVING distance < '25' ORDER BY distance ASC LIMIT 3 ";
    //AND `register_driver`.`iAvailable` = '1'
    //HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . "
    $data = $obj->MySQLSelect($sql);
    //echo "yes";
    return $data;
}

/*
 * ******************** GET Order Stack Of Driver**********************
 */

function GetTimeLeftPrevOrder($iDriverId) {
    global $generalobj, $obj;
    $DayStart = date('Y-m-d') . ' 00:00:01';
    $DayEnd = date('Y-m-d') . ' 23:59:59';
    $sql = "SELECT `iOrderId` FROM `trips` WHERE `iActive` = 'Active'  AND `iDriverId` = '" . $iDriverId . "' AND `tTripRequestDate`  BETWEEN '$DayStart' AND '$DayEnd'  ORDER BY `trips`.`tTripRequestDate` ASC ";
    $datas = $obj->MySQLSelect($sql);
    $AllOrderStack = GetLatLongOrderid($datas[0]['iOrderId']);


    $orderdeliverytime = GetTimeDiffInMinutes($AllOrderStack['deliverytime']);

    return $orderdeliverytime;
}

function GetOrderStackDriver($iCompanyId, $iDriverId) {
    global $generalobj, $obj;
    $sql = "SELECT `iOrderId` FROM `trips` WHERE `iCompanyId` = '" . $iCompanyId . "' AND `iActive` = 'Active'  AND `iDriverId` = '" . $iDriverId . "' ORDER BY `trips`.`tTripRequestDate` ASC ";
    $datas = $obj->MySQLSelect($sql);
    $AllOrderStack = array();
    foreach ($datas as $data) {
        $AllOrderStack[] = GetLatLongOrderid($data['iOrderId']);
    }

    return $AllOrderStack;
}

function GetOrderStackDriverWOC($iDriverId) {
    global $generalobj, $obj;
    $sql = "SELECT `iOrderId` FROM `trips` WHERE `iActive` = 'Active'  AND `iDriverId` = '" . $iDriverId . "' ORDER BY `trips`.`tTripRequestDate` ASC ";
    $datas = $obj->MySQLSelect($sql);
    $AllOrderStack = array();
    foreach ($datas as $data) {
        $AllOrderStack[] = GetLatLongOrderid($data['iOrderId']);
    }

    return $AllOrderStack;
}

function CountNoOfOrderByDriverID($iDriverId) {
    global $generalobj, $obj;
    $sql = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `trips` WHERE `iActive` IN ( 'Active', 'On Going Trip' )  AND `iDriverId` = '" . $iDriverId . "' ORDER BY `trips`.`tTripRequestDate` ASC ";
    $datas = $obj->MySQLSelect($sql);
    if (!empty($datas)) {
        return $datas[0]['tcount'];
    } else {
        return 0;
    }
}

/*
 * ************************ Get Lat Long BY OrderId **********************
 */

function GetLatLongOrderid($iOrderId) {
    global $generalobj, $obj;
    $sql = "select * from orders WHERE iOrderId='" . $iOrderId . "'";
    $db_order = $obj->MySQLSelect($sql);
    //checkmemberemailphoneverification($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];

    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);
    //echo "<pre>";print_r($UserSelectedAddressArr);exit;
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
    $alertMsg = $userwaitinglabel;

    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $getSectorAdressarray = array($DestLatitude, $DestLongitude);
    $MatMon = getmonmat($getSectorAdressarray);
    $result = array(
        'iCompanyId' => $iCompanyId,
        'PickUpLatitude' => $PickUpLatitude,
        'PickUpLongitude' => $PickUpLongitude,
        'DestLatitude' => $DestLatitude,
        'DestLongitude' => $DestLongitude,
        'Mat' => $MatMon['mat'],
        'Mon' => $MatMon['mon'],
        'vTimeZone' => $db_order[0]['vTimeZone'],
        'cookingtime' => $db_order[0]['cookingtime'],
        'pickuptime' => $db_order[0]['pickuptime'],
        'mattime' => $db_order[0]['mattime'],
        'deliverytime' => $db_order[0]['deliverytime'],
    );

    return $result;
}

/*
 * ******************  Get Dat from Backend ******************************
 */

function getDatValue() {
    global $generalobj, $obj;
    $DatQuery = 'SELECT `vValue` FROM `configurations` WHERE `vName` = "DRIVER_ALLOCATION_TIME";';
    $DatDetails = $obj->MySQLSelect($DatQuery);
    return $DatDetails[0]['vValue'];
}

function getRestaurantPhoneNumber() {
    global $generalobj, $obj;
    $DatQuery = 'SELECT `vValue` FROM `configurations` WHERE `vName` = "RESTAURANT_PHONE_DRIVER";';
    $DatDetails = $obj->MySQLSelect($DatQuery);
    return $DatDetails[0]['vValue'];
}

function CheckNotificaTionsent($iOrderId) {
    global $generalobj, $obj;
    $DatQuery = "SELECT count(`iDriverRequestId`) AS 'tcount' FROM `driver_request` WHERE `iOrderId` = '$iOrderId' AND `eStatus` IN( 'Received', 'Timeout') AND 'type'='assign';";
    $DatDetails = $obj->MySQLSelect($DatQuery);
    if (!empty($DatDetails) && ($DatDetails[0]['tcount'] > 0)) {
        return false;
    } else {
        return true;
    }
}

/*
 * Get time details of order and generate pickup and cooking time and delivery time
 */

function getOrderTimeDetials($iOrderId, $iUserId, $iUserAddressId, $iCompanyId) {
    $time = strtotime(date('Y-m-d H:i:s'));
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);
    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;


    $getSectorAdressarray = array($DestLatitude, $DestLongitude);
    $MatMon = getmonmat($getSectorAdressarray);

    $CookMinutes = cookingtimecalculator($iOrderId);
    $cookingtime = date("Y-m-d H:i:s", strtotime('+' . $CookMinutes . ' minutes', $time));
    $MAT = $MatMon['mat'];
    $mattime = date("Y-m-d H:i:s", strtotime('+' . $MAT . ' minutes', $time));
    $DatValue = getDatValue();
    $pickT = $CookMinutes - $DatValue;
    $pickuptime = date("Y-m-d H:i:s", strtotime('+' . $pickT . ' minutes', $time));
    $deliveryTime = $mattime;

    $result = array(
        'cookingtime' => $cookingtime,
        'pickuptime' => $pickuptime,
        'mattime' => $mattime,
        'deliverytime' => $deliveryTime
    );

    return $result;
}

/*
 * ********************************Cooking time calculator **************************
 */

function cookingtimecalculator($order = null) {
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
    return $cookingtime;
}

/*
 * ********************************Get Mat Mon **************************
 */

function getmonmatwithregionid($getAdressarray, $iLocationId = null) {

    global $generalobj, $obj;

    $result = array();
    if (!empty($getAdressarray) && !empty($iLocationId)) {
        $ssql = "AND `iLocationId` = $iLocationId;";
        $sqlaa = "SELECT `iLocationId`, `iCountryId`, `vLocationName`, `tLatitude`, `tLongitude`, `eStatus`, `mon`, `mat` FROM `location_master_driver` WHERE `eStatus`='Active'" . $ssql;
        $allowed_data = $obj->MySQLSelect($sqlaa);
        //print_r($allowed_data);
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($getAdressarray, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {

                        $result = array(
                            'mat' => $val['mat'],
                            'mon' => $val['mon'],
                            'iLocationId' => $val['iLocationId']
                        );
                        break;
                    }
                }
            }
        }
    }
    return $result;
}

/*
 * ********************************Get Mat Mon **************************
 */

function getmonmat($getAdressarray) {

    global $generalobj, $obj;
    $ssql = "";
    $result = array();
    if (!empty($getAdressarray)) {
        $sqlaa = "SELECT `iLocationId`, `iCountryId`, `vLocationName`, `tLatitude`, `tLongitude`, `eStatus`, `mon`, `mat` FROM `location_master_driver` WHERE `eStatus`='Active'" . $ssql;
        $allowed_data = $obj->MySQLSelect($sqlaa);
        //print_r($allowed_data);
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($getAdressarray, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {

                        $result = array(
                            'mat' => $val['mat'],
                            'mon' => $val['mon'],
                            'iLocationId' => $val['iLocationId']
                        );
                        break;
                    }
                }
            }
        }
    }

    if (empty($result)) {
        $result = array(
            'mat' => '45',
            'mon' => '4',
            'iLocationId' => '1'
        );
    }

    return $result;
}

/*
 * Assign Driver To Order BY Multi 
 */

function saveNotiLog($log_msg) {
    $log_filename = "log";
    if (!file_exists($log_filename)) {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename . '/log_' . date('d-M-Y') . '.log';
    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
}

function saveAlgoLog($log_msg) {
    $log_filename = "algorithmlog";
    if (!file_exists($log_filename)) {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename . '/log_' . date('d-M-Y') . '.log';
    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
}

function GetDriverFromID($iDriverId) {
    global $generalobj, $obj;
    $allowed_ans = "Yes";
    $allowed_ans_drop = "Yes";
    $vLatitude = 'vLatitude';
    $vLongitude = 'vLongitude';


    if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
        $sql = "SELECT concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver` WHERE iDriverId = '$iDriverId'  AND eStatus='active'";
        $Data = $obj->MySQLSelect($sql);
        // echo "<pre>"; print_r($Data);
        $newData = array();
        $j = 0;
        $driver_id_auto = "";
        for ($i = 0; $i < count($Data); $i++) {
            $lastprocessstime = checklastorderprocesstime($Data[$i]['iDriverId']);
            $Data[$i]['lastproccessedtime'] = $lastprocessstime[0]['tEndDate'] ? $lastprocessstime[0]['tEndDate'] : 0;
            $iDriverVehicleId = $Data[$i]['iDriverVehicleId'];
            $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
            $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');


            $Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];

            $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
            $driver_id_auto .= $Data[$i]['iDriverId'] . ",";
            $newData[$j] = $Data[$i];
            $j++;
        }
        //print_r($newData);
        $driver_id_auto = substr($driver_id_auto, 0, -1);

        //$returnData['DriverList'] = $Data;
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = $driver_id_auto;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    } else {
        /* $Data = array();
          $returnData['DriverList'] = $Data; */
        $newData = array();
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = "";
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    }

    return $returnData;
}

/*
 * ******************* Auto Accept Driver *****************************
 */

function configDriverTripStatus($iMemberId, $userType, $vLatitude, $vLongitude, $iTripId, $isSubsToCabReq, $vTimeZone) {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $isSubsToCabReq = isset($_REQUEST["isSubsToCabReq"]) ? $_REQUEST["isSubsToCabReq"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';

    if ($iMemberId != "") {
        if (!empty($isSubsToCabReq) && $isSubsToCabReq == 'true') {
            $driver_update['tLastOnline'] = date('Y-m-d H:i:s');
            $driver_update['tOnline'] = date('Y-m-d H:i:s');
        }

        if (!empty($vLatitude) && !empty($vLongitude)) {
            $driver_update['vLatitude'] = $vLatitude;
            $driver_update['vLongitude'] = $vLongitude;
        }

        if (count($driver_update) > 0) {
            $where = " iDriverId = '" . $iMemberId . "'";
            $Update_driver = $obj->MySQLQueryPerform("register_driver", $driver_update, "update", $where);
            # Update User Location Date #
            Updateuserlocationdatetime($iMemberId, "Driver", $vTimeZone);
            # Update User Location Date #
        }
    }
    if ($iTripId != "") {
        $sql = "SELECT tMessage as msg, iStatusId FROM trip_status_messages WHERE iDriverId='" . $iMemberId . "' AND eToUserType='Driver' AND eReceived='No' ORDER BY iStatusId DESC LIMIT 1 ";
        $msg = $obj->MySQLSelect($sql);
    } else {
        $date = @date("Y-m-d");
        $sql = "SELECT passenger_requests.tMessage as msg  FROM passenger_requests LEFT JOIN driver_request ON  driver_request.iRequestId=passenger_requests.iRequestId  LEFT JOIN register_driver ON register_driver.iDriverId=passenger_requests.iDriverId where date_format(passenger_requests.dAddedDate,'%Y-%m-%d')= '" . $date . "' AND  passenger_requests.iDriverId=" . $iMemberId . " AND driver_request.eStatus='Timeout' AND driver_request.iDriverId='" . $iMemberId . "'  ORDER BY passenger_requests.iRequestId DESC LIMIT 1 ";
        //AND register_driver.vTripStatus IN ('Not Active','NONE','Cancelled')
        $msg = $obj->MySQLSelect($sql);
    }


    $returnArr['Action'] = "0";
    if (!empty($msg)) {

        $returnArr['Action'] = "1";

        if ($iTripId != "") {
            //$updateQuery = "UPDATE trip_status_messages SET eReceived = 'Yes' WHERE iStatusId='".$msg[0]['iStatusId']."'";
            $updateQuery = "UPDATE trip_status_messages SET eReceived = 'Yes' WHERE iDriverId='" . $iMemberId . "'";
            $obj->sql_query($updateQuery);

            $returnArr['Action'] = "1";
            $returnArr['message'] = $msg[0]['msg'];
        } else {

            $driver_request['eStatus'] = "Received";
            $where = " iDriverId =" . $iMemberId . " and date_format(tDate,'%Y-%m-%d') = '" . $date . "' AND eStatus = 'Timeout' ";
            $obj->MySQLQueryPerform("driver_request", $driver_request, "update", $where);


            // $updatequery = "update driver_request set eStatus='Received' where iDriverId='".$iMemberId."' AND   date_format(tDate,'%Y-%m-%d') = '" . $date . "'  AND eStatus = 'Timeout'";
            // $obj->sql_query($updateQuery);


            $returnArr['Action'] = "1";
            $dataArr = array();
            for ($i = 0; $i < count($msg); $i++) {
                $dataArr[$i] = $msg[$i]['msg'];
            }
            $returnArr['message'] = $dataArr;
        }
    }
    $obj->MySQLClose();
    echo json_encode($returnArr, JSON_UNESCAPED_UNICODE);
    exit;
}

function AutoAcceptDriver($iOrderId, $iDriverId, $Source_point_latitude, $Source_point_longitude, $Source_point_Address, $Dest_point_latitude, $Dest_point_longitude, $Dest_point_Address, $GoogleServerKey, $vMsgCode, $setCron) {

    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Source_point_latitude = isset($_REQUEST["tSourceLat"]) ? $_REQUEST["tSourceLat"] : '';
    $Source_point_longitude = isset($_REQUEST["tSourceLong"]) ? $_REQUEST["tSourceLong"] : '';
    $Source_point_Address = isset($_REQUEST["tSourceAddress"]) ? $_REQUEST["tSourceAddress"] : '';
    $Dest_point_latitude = isset($_REQUEST["tDestLatitude"]) ? $_REQUEST["tDestLatitude"] : '';
    $Dest_point_longitude = isset($_REQUEST["tDestLongitude"]) ? $_REQUEST["tDestLongitude"] : '';
    $Dest_point_Address = isset($_REQUEST["tDestAddress"]) ? $_REQUEST["tDestAddress"] : '';

    $GoogleServerKey = isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';
    $vMsgCode = isset($_REQUEST["vMsgCode"]) ? $_REQUEST["vMsgCode"] : '';
    $setCron = isset($_REQUEST["setCron"]) ? $_REQUEST["setCron"] : 'No';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    $sql = "select * from orders WHERE iOrderId='" . $iOrderId . "'";
    $db_order = $obj->MySQLSelect($sql);
    $checkOrderStatusAl = $db_order[0]['iStatusCode'];

    if (isset($checkOrderStatusAl) && ($checkOrderStatusAl == 4)) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DRIVER_NOT_ACCEPT_TRIP";
        echo json_encode($returnArr);
        exit;
    }
    $sqldata = "SELECT iTripId FROM `trips` WHERE ( iActive='On Going Trip' OR iActive='Active' ) AND iDriverId='" . $driver_id . "'";
    $TripData = $obj->MySQLSelect($sqldata);
    if (count($TripData) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DRIVER_NOT_ACCEPT_TRIP";
        echo json_encode($returnArr);
        exit;
    }

    $sqld = "SELECT iTripId FROM `trips` WHERE iOrderId ='" . $iOrderId . "'";
    $TripOrderData = $obj->MySQLSelect($sqld);
    if (count($TripOrderData) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_SAME_ORDER_TRIP_EXIST_TXT";
        echo json_encode($returnArr);
        exit;
    }

    #### Update Driver Request Status of Trip ####
    UpdateDriverRequest2($driver_id, $passenger_id, $iTripId, "", $vMsgCode, "Yes", $iOrderId);
    #### Update Driver Request Status of Trip ####



    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $vOrderNo = $db_order[0]['vOrderNo'];


    $DriverMessage = "CabRequestAccepted";

    $TripRideNO = GenerateUniqueTripNo();
    $TripVerificationCode = rand(1000, 9999);
    $Active = "Active";

    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $vGMapLangCode = get_value('language_master', 'vGMapLangCode', 'vCode', $vLangCode, '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $tripdriverarrivlbl = $languageLabelsArr['LBL_DRIVER_ARRIVING'];

    $reqestId = "";
    $trip_status_chkField = "iCabRequestId";


    if ($iOrderId > 0) {

        if ($iDriverId != "") {
            $where = " iOrderId = '$iOrderId'";

            $Data_update_order_driver['iDriverId'] = $iDriverId;
            $Data_update_order_driver['iStatusCode'] = "4";

            $obj->MySQLQueryPerform("orders", $Data_update_order_driver, 'update', $where);
            $Order_Status_id = createOrderLog($iOrderId, "4");
        }

        $sql = "SELECT vCurrencyPassenger,iAppVersion,iUserPetId FROM `register_user` WHERE iUserId = '$iUserId'";
        $Data_passenger_detail = $obj->MySQLSelect($sql);

        $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,vName,vLastName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
        $Data_vehicle = $obj->MySQLSelect($sql);

        $CAR_id_driver = $Data_vehicle[0]['iDriverVehicleId'];
        $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $CAR_id_driver, '', 'true');
        $fDeliveryCharge = get_value('vehicle_type', 'fDeliveryChargeCancelOrder', 'iVehicleTypeId', $vCarType, '', 'true');

        $Data_trips['iOrderId'] = $iOrderId;
        $Data_trips['fDeliveryCharge'] = $fDeliveryCharge;
        $Data_trips['vRideNo'] = $TripRideNO;
        $Data_trips['iUserId'] = $iUserId;
        $Data_trips['iDriverId'] = $iDriverId;
        $Data_trips['iCompanyId'] = $iCompanyId;
        $Data_trips['tTripRequestDate'] = @date("Y-m-d H:i:s");
        $Data_trips['iDriverVehicleId'] = $CAR_id_driver;
        $Data_trips['tStartLat'] = $Source_point_latitude;
        $Data_trips['tStartLong'] = $Source_point_longitude;
        $Data_trips['tSaddress'] = $Source_point_Address;
        $Data_trips['tEndLat'] = $Dest_point_latitude;
        $Data_trips['tEndLong'] = $Dest_point_longitude;
        $Data_trips['tDaddress'] = $Dest_point_Address;
        $Data_trips['iActive'] = $Active;
        $Data_trips['iVerificationCode'] = $TripVerificationCode;
        $Data_trips['iVehicleTypeId'] = $vCarType;
        $Data_trips['vTripPaymentMode'] = $db_order[0]['ePaymentOption'];
        $Data_trips['fTripGenerateFare'] = $db_order[0]['fNetTotal'];
        $Data_trips['vCountryUnitRider'] = getMemberCountryUnit($iUserId, "Passenger");
        $Data_trips['vCountryUnitDriver'] = getMemberCountryUnit($iDriverId, "Driver");
        $Data_trips['vTimeZone'] = $vTimeZone;
        $Data_trips['iUserAddressId'] = $iUserAddressId;

        $currencyList = get_value('currency', '*', 'eStatus', 'Active');

        for ($i = 0; $i < count($currencyList); $i++) {
            $currencyCode = $currencyList[$i]['vName'];
            $Data_trips['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
        }

        $Data_trips['vCurrencyPassenger'] = $Data_passenger_detail[0]['vCurrencyPassenger'];
        $Data_trips['vCurrencyDriver'] = $Data_vehicle[0]['vCurrencyDriver'];

        $id = $obj->MySQLQueryPerform("trips", $Data_trips, 'insert');
        $iTripId = $id;
        $trip_status = "Active";

        #### Update Driver Request Status of Trip ####
        UpdateDriverRequest2($iDriverId, $iUserId, $iTripId, "Accept", $vMsgCode, "No", $iOrderId);
        #### Update Driver Request Status of Trip ####

        $where = " iUserId = '$iUserId'";
        $Data_update_passenger['iTripId'] = $iTripId;
        $Data_update_passenger['vTripStatus'] = $trip_status;
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

        $where = " iDriverId = '$iDriverId'";
        $Data_update_driver['iTripId'] = $iTripId;
        $Data_update_driver['vTripStatus'] = $trip_status;
        $Data_update_driver['vRideCountry'] = $vRideCountry;
        $Data_update_driver['vAvailability'] = "Not Available";
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

        /* if($eType == "Deliver"){
          $drivername = $Data_vehicle[0]['vName']." ".$Data_vehicle[0]['vLastName'];
          $tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_DRIVER_TXT']." ".$drivername." ".$languageLabelsArr['LBL_DRIVER_IS_ARRIVING'];
          } */
        $drivername = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
        $tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_EXECUTIVE_TXT'] . " " . $drivername . " " . $languageLabelsArr['LBL_DELIVERY_ON_WAY_TXT'] . " #" . $vOrderNo;

        $alertMsg = $tripdriverarrivlbl;
        $message_arr = array();
        $message_arr['iDriverId'] = $iDriverId;
        $message_arr['Message'] = $DriverMessage;
        $message_arr['iTripId'] = strval($iTripId);
        $message_arr['DriverAppVersion'] = strval($Data_vehicle[0]['iAppVersion']);
        $message_arr['iTripVerificationCode'] = $TripVerificationCode;
        $message_arr['driverName'] = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
        $message_arr['vRideNo'] = $TripRideNO;
        $message_arr['iOrderId'] = $iOrderId;
        $message_arr['vTitle'] = $alertMsg;

        $message = json_encode($message_arr);

        #####################Add Status Message#########################
        $DataTripMessages['tMessage'] = $message;
        $DataTripMessages['iDriverId'] = $iDriverId;
        $DataTripMessages['iTripId'] = $iTripId;
        $DataTripMessages['iOrderId'] = $iOrderId;
        $DataTripMessages['iUserId'] = $iUserId;
        $DataTripMessages['eFromUserType'] = "Driver";
        $DataTripMessages['eToUserType'] = "Passenger";
        $DataTripMessages['eReceived'] = "No";
        $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");

        $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
        ################################################################

        if ($iTripId > 0) {
            /* $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
              $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
              $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
              $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY"); */
            if ($PUBNUB_DISABLED == "Yes") {
                $ENABLE_PUBNUB = "No";
            }

            $alertSendAllowed = true;

            /* For PubNub Setting */
            $tableName = "register_user";
            $iMemberId_VALUE = $iUserId;
            $iMemberId_KEY = "iUserId";
            /* $iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
              $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true'); */
            $AppData = get_value($tableName, 'iAppVersion,eDeviceType', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            /* For PubNub Setting Finished */

            $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId='$iUserId'";
            $result = $obj->MySQLSelect($sql);
            $registatoin_ids = $result[0]['iGcmRegId'];
            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();

            $sql = "SELECT iGcmRegId,eDeviceType,iAppVersion,tSessionId FROM company WHERE iCompanyId='$iCompanyId'";
            $result_company = $obj->MySQLSelect($sql);
            $registatoin_ids_company = $result_company[0]['iGcmRegId'];
            $deviceTokens_arr_ios_company = array();
            $registation_ids_new_company = array();

            if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
                //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
                //$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
                $channelName = "PASSENGER_" . $iUserId;

                $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $iUserId, '', 'true');
                $message_arr['tSessionId'] = $tSessionId;
                $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
                //$info = $pubnub->publish($channelName, $message_pub);
                pubnubnotification($channelName, $message_pub);

                $channelName_company = "COMPANY_" . $iCompanyId;
                $message_arr['tSessionId'] = $result_company[0]['tSessionId'];
                $message_pub_company = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
                //$info_company = $pubnub->publish($channelName_company, $message_pub_company);
                pubnubnotification($channelName_company, $message_pub_company);

                if ($result_company[0]['eDeviceType'] != "Android") {
                    array_push($deviceTokens_arr_ios_company, $result_company[0]['iGcmRegId']);
                }

                if ($result[0]['eDeviceType'] != "Android") {
                    //$alertMsg = "Driver is arriving";
                    //$alertMsg = $tripdriverarrivlbl;
                    array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                    // sendApplePushNotification(0,$deviceTokens_arr_ios,"",$alertMsg,0);
                }
            } else {
                $alertSendAllowed = true;
            }
            if ($alertSendAllowed == true) {
                if ($result[0]['eDeviceType'] == "Android") {
                    array_push($registation_ids_new, $result[0]['iGcmRegId']);
                    $Rmessage = array("message" => $message);
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                } else {
                    //$alertMsg = "Driver is arriving";
                    //$alertMsg = $tripdriverarrivlbl;
                    array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                    sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                }

                if ($result_company[0]['eDeviceType'] == "Android") {
                    array_push($registation_ids_new_company, $result_company[0]['iGcmRegId']);
                    $Rmessage = array("message" => $message);
                    $resultc = send_notification($registation_ids_new_company, $Rmessage, 0);
                } else {
                    array_push($deviceTokens_arr_ios_company, $result_company[0]['iGcmRegId']);
                    sendApplePushNotification(2, $deviceTokens_arr_ios_company, $message, $alertMsg, 0);
                }
            }

            $returnArr['Action'] = "1";
            $data['iTripId'] = $iTripId;
            $data['tEndLat'] = $Dest_point_latitude;
            $data['tEndLong'] = $Dest_point_longitude;
            $data['tDaddress'] = $Dest_point_Address;
            $data['PAppVersion'] = $Data_passenger_detail[0]['iAppVersion'];
            $data['eFareType'] = $Data_trips['eFareType'];
            $data['vVehicleType'] = $eIconType;
            $returnArr['APP_TYPE'] = $APP_TYPE;
            $returnArr['message'] = $data;

            if ($iOrderId != "") {
                $passengerData = get_value('register_user', 'vName,vLastName,vImgName,vFbId,vAvgRating,vPhone,vPhoneCode,iAppVersion', 'iUserId', $iUserId);
                $returnArr['sourceLatitude'] = $Source_point_latitude;
                $returnArr['sourceLongitude'] = $Source_point_longitude;
                $returnArr['PassengerId'] = $iUserId;
                $returnArr['PName'] = $passengerData[0]['vName'] . ' ' . $passengerData[0]['vLastName'];
                $returnArr['PPicName'] = $passengerData[0]['vImgName'];
                $returnArr['PFId'] = $passengerData[0]['vFbId'];
                $returnArr['PRating'] = $passengerData[0]['vAvgRating'];
                $returnArr['PPhone'] = $passengerData[0]['vPhone'];
                $returnArr['PPhoneC'] = $passengerData[0]['vPhoneCode'];
                $returnArr['PAppVersion'] = $passengerData[0]['iAppVersion'];
                $returnArr['TripId'] = strval($iTripId);
                $returnArr['DestLocLatitude'] = $Dest_point_latitude;
                $returnArr['DestLocLongitude'] = $Dest_point_longitude;
                $returnArr['DestLocAddress'] = $Dest_point_Address;
                $returnArr['vVehicleType'] = $eIconType;
            }
            echo json_encode($returnArr);
            exit;
        } else {
            $data['Action'] = "0";
            $data['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($data);
            exit;
        }

        /* }else{
          $returnArr['Action'] = "0";
          $returnArr['message']="LBL_CAR_REQUEST_CANCELLED_TXT";
          echo json_encode($returnArr);
          } */
    } else {
        if ($eStatus == "Complete") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_FAIL_ASSIGN_TO_PASSENGER_TXT";
        } else if ($eStatus == "Cancelled") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_CAR_REQUEST_CANCELLED_TXT";
        }
        echo json_encode($returnArr);
    }
}

function UnassignOrderToDriver($driverid, $orderid) {
    global $generalobj, $obj;
    $sql = "select d.iDriverId,d.iGcmRegId,d.eDeviceType,d.tSessionId,d.iAppVersion,d.vLang,o.vOrderNo , o.iCompanyId from orders as o LEFT JOIN register_driver as d ON o.iDriverId=d.iDriverId where o.iOrderId = '" . $orderid . "'";

    $updateorder = "update orders set iDriverId = 0,iStatusCode=2 where iOrderId = $orderid";


    $updatdrivereq = "update driver_request set eStatus = 'RemovedbyAdmin' where iOrderId = $orderid";


    $updatedr = $obj->sql_query($updateorder);
    // echo "1";
    $updatedr1 = $obj->sql_query($updatdrivereq);
    //  echo "2";
    $getreqid = "select iRequestId from driver_request where iOrderId = $orderid and estatus = 'RemovedbyAdmin' ORDER BY iDriverRequestId DESC;";
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
        //    echo "6";
    }

    $drv_data_order = $obj->MySQLSelect($sql);


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

    $drvmessage = json_encode($message_arr_res, JSON_UNESCAPED_UNICODE);
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }

    $alertSendAllowed = true;
    // For PubNub Setting 
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


    $sql = "select * from orders WHERE iOrderId='" . $orderid . "'";
    $db_order = $obj->MySQLSelect($sql);
    //checkmemberemailphoneverification($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];


    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];

    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);

    $sqlp = "SELECT iGcmRegId,vCompany,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);

    //echo "<pre>";print_r($UserSelectedAddressArr);exit;
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $userwaitinglabel = $languageLabelsArr['LBL_ORDER_UNASSIGN_BY_ADMIN'];
    $alertMsg = $userwaitinglabel;


    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];


    $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
    $destLoc = $DestLatitude . ',' . $DestLongitude;

    $final_message['Message'] = "OrderCancelByAdmin";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($iUserId);
    $final_message['pickupaddress'] = $PickUpAddress;
    $final_message['dropaddress'] = $DestAddress;
    $final_message['iCompanyId'] = strval($iCompanyId);
    $final_message['iOrderId'] = strval($orderid);
    $passengerFName = $passengerData[0]['vCompany'];
    $final_message['PName'] = $passengerFName;
    $final_message['PPicName'] = $passengerData[0]['vImgName'];
    $final_message['PRating'] = $passengerData[0]['vAvgRating'];
    $final_message['PPhone'] = $passengerData[0]['vPhone'];
    $final_message['PPhoneC'] = $passengerData[0]['vPhoneCode'];
    $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);
    $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
    $final_message['vTitle'] = $alertMsg;


    // For PubNub Setting Finished 

    if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
        //$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
        $DriverchannelName = "DRIVER_" . $drv_data_order[0]['iCompanyId'];
        //$info = $pubnub->publish($DriverchannelName, $drvmessage);
        pubnubnotification($DriverchannelName, $drvmessage);
        if ($eDeviceType != "Android") {
            array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
        }
    }
    $DataArr = GetDriverFromID($driverid);
    $Data = $DataArr['DriverList'];
    $driver_id_auto = $DataArr['driver_id_auto'];
    $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ")";
    $result = $obj->MySQLSelect($sql);
    $deviceTokens_arr_ios = array();
    $registation_ids_new = array();
    if (count($result) > 0) {
        foreach ($result as $item) {
            if ($item['eDeviceType'] == "Android") {
                array_push($registation_ids_new, $item['iGcmRegId']);
            } else {
                array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
            }

            $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_ORDER_UNASSIGN_BY_ADMIN', " and vCode='" . $item['vLang'] . "'", 'true');
            $tSessionId = $item['tSessionId'];

            $final_message['tSessionId'] = $tSessionId;
            $final_message['vTitle'] = $alertMsg_db;



            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            // Add User Request
            $data_userRequest = array();
            $data_userRequest['iUserId'] = $iUserId;
            $data_userRequest['iDriverId'] = $item['iDriverId'];
            $data_userRequest['tMessage'] = $msg_encode;
            $data_userRequest['iMsgCode'] = $final_message['MsgCode'];
            $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
            $requestId = addToUserRequest2($data_userRequest);

            // Add Driver Request
            $data_driverRequest = array();
            $data_driverRequest['iDriverId'] = $item['iDriverId'];
            $data_driverRequest['type'] = 'unassign';
            $data_driverRequest['iRequestId'] = $requestId;
            $data_driverRequest['iUserId'] = $iUserId;
            $data_driverRequest['iTripId'] = 0;
            $data_driverRequest['iOrderId'] = $orderid;
            $data_driverRequest['eStatus'] = "Timeout";
            $data_driverRequest['vMsgCode'] = $final_message['MsgCode'];
            $data_driverRequest['vStartLatlong'] = $sourceLoc;
            $data_driverRequest['vEndLatlong'] = $destLoc;
            $data_driverRequest['tStartAddress'] = $PickUpAddress;
            $data_driverRequest['tEndAddress'] = $DestAddress;
            $data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
            addToDriverRequest2($data_driverRequest);
            // addToUserRequest($passengerId,$item['iDriverId'],$msg_encode,$final_message['MsgCode']);
            // addToDriverRequest($item['iDriverId'],$passengerId,0,"Timeout");
        }
    }


    if ($alertSendAllowed == true) {

        if ($eDeviceType == "Android") {
            array_push($drvregistation_ids_new, $iGcmRegId);

            $Dmessage = array("message" => $msg_encode);

            send_notification($drvregistation_ids_new, $Dmessage, 0);
        } else {
            array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
            sendApplePushNotification(0, $drvdeviceTokens_arr_ios, $drvmessage, $alertMsg, 0);
        }
    }
    return 1;
}

function AssignOrderToDriver($iOrderId, $iDriverId) {
    global $generalobj, $obj;
    $trip_status = "Requesting";
    $sql = "select * from orders WHERE iOrderId='" . $iOrderId . "'";
    $db_order = $obj->MySQLSelect($sql);
    //checkmemberemailphoneverification($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];

    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);
    //echo "<pre>";print_r($UserSelectedAddressArr);exit;
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
    $alertMsg = $userwaitinglabel;

    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $DataArr = GetDriverFromID($iDriverId);
    $Data = $DataArr['DriverList'];
    $driver_id_auto = $DataArr['driver_id_auto'];
    ## Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    if ($ePaymentOption == "Cash") {
        $Data_new = array();
        $Data_new = $Data;
        for ($i = 0; $i < count($Data); $i++) {
            $isRemoveFromList = "No";
            $ACCEPT_CASH_TRIPS = $Data[$i]['ACCEPT_CASH_TRIPS'];
            if ($ACCEPT_CASH_TRIPS == "No") {
                $isRemoveFromList = "Yes";
            }

            if ($isRemoveFromList == "Yes") {
                unset($Data_new[$i]);
            }
        }
        $Data = array_values($Data_new);
        $driver_id_auto = "";
        for ($j = 0; $j < count($Data); $j++) {
            $driver_id_auto .= $Data[$j]['iDriverId'] . ",";
        }
        $driver_id_auto = substr($driver_id_auto, 0, -1);
    }
    ## Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    //echo "<pre>";print_r($Data);exit; 

    $sqlp = "SELECT iGcmRegId,vCompany,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    //$iGcmRegId=get_value('register_user', 'iGcmRegId', 'iUserId',$passengerId,'','true');
    $iGcmRegId = $passengerData[0]['iGcmRegId'];
    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($iUserId);
    $final_message['pickupaddress'] = $PickUpAddress;
    $final_message['dropaddress'] = $DestAddress;
    $final_message['iCompanyId'] = strval($iCompanyId);
    $final_message['iOrderId'] = strval($iOrderId);
    $passengerFName = $passengerData[0]['vCompany'];
    $final_message['PName'] = $passengerFName;
    $final_message['PPicName'] = $passengerData[0]['vImgName'];
    $final_message['PRating'] = $passengerData[0]['vAvgRating'];
    $final_message['PPhone'] = $passengerData[0]['vPhone'];
    $final_message['PPhoneC'] = $passengerData[0]['vPhoneCode'];
    $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);
    $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
    $final_message['vTitle'] = $alertMsg;
    //$final_message['Time']= strval(date('Y-m-d'));

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

    $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ")";
    $result = $obj->MySQLSelect($sql);

    //echo "Res:count:".count($Data);exit;
    if (count($result) == 0 || $driver_id_auto == "" || count($Data) == 0) {

        return 0;
        exit;
    }
//	if($totaltime > 55)
//        {
//            $returnArr['Action'] = "0";
//			$returnArr['message'] = "Sorry time is greater than excected";
//            echo json_encode($returnArr);
//            exit;
//        }
    // $where = " iUserId = '$passengerId'";
    $where = "";
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }
    $alertSendAllowed = true;

    if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {

        //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
        //$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
        $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
        $driverIds_arr = explode(",", $filter_driver_ids);

        $message = stripslashes(preg_replace("/[\n\r]/", "", $message));

        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
        $destLoc = $DestLatitude . ',' . $DestLongitude;
        //count($driverIds_arr)
        for ($i = 0; $i < 1; $i++) {

            $sqld = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId,vLang FROM register_driver WHERE iDriverId = '" . $driverIds_arr[$i] . "'";
            $driverTripData = $obj->MySQLSelect($sqld);
            $iAppVersion = $driverTripData[0]['iAppVersion'];
            $eDeviceType = $driverTripData[0]['eDeviceType'];
            $vDeviceToken = $driverTripData[0]['iGcmRegId'];
            $tSessionId = $driverTripData[0]['tSessionId'];
            $vLang = $driverTripData[0]['vLang'];
            /* For PubNub Setting Finished */

            $final_message['tSessionId'] = $tSessionId;
            $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $vLang . "'", 'true');
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode_pub = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$i];
            // $info = $pubnub->publish($channelName, $message);
            //$info = $pubnub->publish($channelName, $msg_encode_pub);
            pubnubnotification($channelName, $msg_encode_pub);

            if ($eDeviceType != "Android") {
                array_push($deviceTokens_arr_ios, $vDeviceToken);
            }
        }
    }

    if ($alertSendAllowed == true) {
        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        foreach ($result as $item) {
            if ($item['eDeviceType'] == "Android") {
                array_push($registation_ids_new, $item['iGcmRegId']);
            } else {
                array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
            }

            $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
            $tSessionId = $item['tSessionId'];

            $final_message['tSessionId'] = $tSessionId;
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            // Add User Request
            $data_userRequest = array();
            $data_userRequest['iUserId'] = $iUserId;
            $data_userRequest['iDriverId'] = $item['iDriverId'];
            $data_userRequest['tMessage'] = $msg_encode;
            $data_userRequest['iMsgCode'] = $final_message['MsgCode'];
            $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
            $requestId = addToUserRequest2($data_userRequest);

            // Add Driver Request
            $data_driverRequest = array();
            $data_driverRequest['iDriverId'] = $item['iDriverId'];
            $data_driverRequest['iRequestId'] = $requestId;
            $data_driverRequest['iUserId'] = $iUserId;
            $data_driverRequest['iTripId'] = 0;
            $data_driverRequest['iOrderId'] = $iOrderId;
            $data_driverRequest['eStatus'] = "Timeout";
            $data_driverRequest['vMsgCode'] = $final_message['MsgCode'];
            $data_driverRequest['vStartLatlong'] = $sourceLoc;
            $data_driverRequest['vEndLatlong'] = $destLoc;
            $data_driverRequest['tStartAddress'] = $PickUpAddress;
            $data_driverRequest['tEndAddress'] = $DestAddress;
            $data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
            addToDriverRequest2($data_driverRequest);
            // addToUserRequest($passengerId,$item['iDriverId'],$msg_encode,$final_message['MsgCode']);
            // addToDriverRequest($item['iDriverId'],$passengerId,0,"Timeout");
        }
        if (count($registation_ids_new) > 0) {
            // $Rmessage = array("message" => $message);
            $Rmessage = array("message" => $msg_encode);

            $result = send_notification($registation_ids_new, $Rmessage, 0);
        }
        if (count($deviceTokens_arr_ios) > 0) {
            // sendApplePushNotification(1,$deviceTokens_arr_ios,$message,$alertMsg,1);
            sendApplePushNotification(1, $deviceTokens_arr_ios, $msg_encode, $alertMsg, 0);
        }
    }




    return 1;
}

/*
 * 
 * 
 * Assign Order To Free Driver
 * 
 */

function AssignOrderFreeDriver($iOrderId, $EarlierDriverlist) {
    global $generalobj, $obj;
    $trip_status = "Requesting";
    $AssignResp = array();
//    $checkOrderRequestStatusArr = checkOrderRequestStatus($iOrderId);
//    $action = $checkOrderRequestStatusArr['Action'];
//    if ($action == 0) {
//        echo json_encode($checkOrderRequestStatusArr);
//        exit;
//    }

    $sql = "select * from orders WHERE iOrderId='" . $iOrderId . "'";
    $db_order = $obj->MySQLSelect($sql);

    //checkmemberemailphoneverification($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];

    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);
    //echo "<pre>";print_r($UserSelectedAddressArr);exit;
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
    $alertMsg = $userwaitinglabel;

    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;

    $DataArr = getFreeDriver($PickUpLatitude, $PickUpLongitude, $address_data, $DestLatitude, $DestLongitude, $EarlierDriverlist, $iOrderId);

    $DataUnprocess = $DataArr['DriverList'];
    $Data = array();
    /* New code Anviam */
    //echo "<pre>"; print_r($DataUnprocess); echo "</pre>"; 
    $cookingtime = cookingtimecalculator($iOrderId);
    //echo "Cookingtime" .$cookingtime. " <br/>";
    //print_r($pickupdistime);
    $dropdistime = GetDrivingDistance($DestLatitude, $PickUpLatitude, $DestLongitude, $PickUpLongitude);
    //print_r($dropdistime);
    $pickupdistance = $DataUnprocess[0]['pickupdistance'];
    $pickuptime = $DataUnprocess[0]['pickuptime'];
    $dropdistance = $dropdistime['distance'];
    $droptime = $dropdistime['time'];
    $lesstime = 0;
    $lessid = 0;
    $estimatetimeforpickup = 0;
    if ($cookingtime > $pickuptime) {
        //echo "Enter In looping<br/> Count is: ". count($DataUnprocess) ."<br/>";
        for ($i = 0; $i < count($DataUnprocess); $i++) {

            if (($lesstime == 0) && (strtotime($DataUnprocess[$i]['lastproccessedtime']) != 0)) {
                // echo "1";
                $lesstime = strtotime($DataUnprocess[$i]['lastproccessedtime']);
                $lessid = $DataUnprocess[$i];
            } elseif (($lesstime != 0) && (strtotime($DataUnprocess[$i]['lastproccessedtime']) != 0)) {
                //  echo "2";
                if ($lesstime > strtotime($DataUnprocess[$i]['lastproccessedtime'])) {
                    $lesstime = strtotime($DataUnprocess[$i]['lastproccessedtime']);
                    $lessid = $DataUnprocess[$i];
                }
            } else {
                //  echo "3";
                $lesstime = 0;
                $lessid = $DataUnprocess[$i];
            }

            //  echo "4";
        }

        // print_r ($lessid); echo "<br/>";
        $Data[0] = $lessid;
        $estimatetimeforpickup = $cookingtime;
        //  echo "5";
    } else {
        //echo "6";
        $Data[0] = $DataUnprocess[0];
        $estimatetimeforpickup = $pickuptime;
    }

    //echo "7";
    // echo "<pre>"; print_r($Data);  echo "working"; exit;
    $totaltime = $estimatetimeforpickup + $droptime;
    $totaldistance = $pickupdistance + $dropdistance;

    $driver_id_auto = $DataArr['driver_id_auto'];
    // $driver_id_auto = $Data[0]['iDriverId'] . ",";
    ## Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    if ($ePaymentOption == "Cash") {
        $Data_new = array();
        $Data_new = $Data;
        for ($i = 0; $i < count($Data); $i++) {
            $isRemoveFromList = "No";
            $ACCEPT_CASH_TRIPS = $Data[$i]['ACCEPT_CASH_TRIPS'];
            if ($ACCEPT_CASH_TRIPS == "No") {
                $isRemoveFromList = "Yes";
            }

            if ($isRemoveFromList == "Yes") {
                unset($Data_new[$i]);
            }
        }
        $Data = array_values($Data_new);
        $driver_id_auto = "";
        for ($j = 0; $j < count($Data); $j++) {
            //  for ($j = 0; $j < 1; $j++) {
            $driver_id_auto .= $Data[$j]['iDriverId'] . ",";
        }
        $driver_id_auto = substr($driver_id_auto, 0, -1);
    }

    ## Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    //echo "<pre>";print_r($Data);exit; 

    $sqlp = "SELECT iGcmRegId,vCompany,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    //$iGcmRegId=get_value('register_user', 'iGcmRegId', 'iUserId',$passengerId,'','true');
    $iGcmRegId = $passengerData[0]['iGcmRegId'];


    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($iUserId);
    $final_message['pickupaddress'] = $PickUpAddress;
    $final_message['dropaddress'] = $DestAddress;
    $final_message['iCompanyId'] = strval($iCompanyId);
    $final_message['iOrderId'] = strval($iOrderId);
    $passengerFName = $passengerData[0]['vCompany'];
    $final_message['PName'] = $passengerFName;
    $final_message['PPicName'] = $passengerData[0]['vImgName'];
    $final_message['PRating'] = $passengerData[0]['vAvgRating'];
    $final_message['PPhone'] = $passengerData[0]['vPhone'];
    $final_message['PPhoneC'] = $passengerData[0]['vPhoneCode'];
    $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);
    $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
    $final_message['vTitle'] = $alertMsg;
    //$final_message['Time']= strval(date('Y-m-d'));

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

    $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ") AND vAvailability='Available' AND tLocationUpdateDate > '$str_date' ";
    $result = $obj->MySQLSelect($sql);

    // echo "Res:count:".count($result);exit;
    if (count($result) == 0 || $driver_id_auto == "" || count($Data) == 0) {

        $AssignResp = array(
            'status' => 0,
            'iDriverId' => 0
        );
        return $AssignResp;
    }

    $where = "";
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }
    $alertSendAllowed = true;

    if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {

        //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
        //$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
        $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
        $driverIds_arr = explode(",", $filter_driver_ids);

        $message = stripslashes(preg_replace("/[\n\r]/", "", $message));

        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
        $destLoc = $DestLatitude . ',' . $DestLongitude;

        for ($i = 0; $i < count($driverIds_arr); $i++) {

            $sqld = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId,vLang FROM register_driver WHERE iDriverId = '" . $driverIds_arr[$i] . "'";
            $driverTripData = $obj->MySQLSelect($sqld);
            $iAppVersion = $driverTripData[0]['iAppVersion'];
            $eDeviceType = $driverTripData[0]['eDeviceType'];
            $vDeviceToken = $driverTripData[0]['iGcmRegId'];
            $tSessionId = $driverTripData[0]['tSessionId'];
            $vLang = $driverTripData[0]['vLang'];
            /* For PubNub Setting Finished */

            $final_message['tSessionId'] = $tSessionId;
            $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $vLang . "'", 'true');
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode_pub = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$i];
            // $info = $pubnub->publish($channelName, $message);
            pubnubnotification($channelName, $msg_encode_pub);

            if ($eDeviceType != "Android") {
                array_push($deviceTokens_arr_ios, $vDeviceToken);
            }
        }
    }

    if ($alertSendAllowed == true) {
        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        foreach ($result as $item) {
            if ($item['eDeviceType'] == "Android") {
                array_push($registation_ids_new, $item['iGcmRegId']);
            } else {
                array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
            }

            $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
            $tSessionId = $item['tSessionId'];

            $final_message['tSessionId'] = $tSessionId;
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            // Add User Request
            $data_userRequest = array();
            $data_userRequest['iUserId'] = $iUserId;
            $data_userRequest['iDriverId'] = $item['iDriverId'];
            $data_userRequest['tMessage'] = $msg_encode;
            $data_userRequest['iMsgCode'] = $final_message['MsgCode'];
            $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
            $requestId = addToUserRequest2($data_userRequest);

            // Add Driver Request
            $data_driverRequest = array();
            $data_driverRequest['iDriverId'] = $item['iDriverId'];
            $data_driverRequest['iRequestId'] = $requestId;
            $data_driverRequest['iUserId'] = $iUserId;
            $data_driverRequest['iTripId'] = 0;
            $data_driverRequest['iOrderId'] = $iOrderId;
            $data_driverRequest['eStatus'] = "Timeout";
            $data_driverRequest['vMsgCode'] = $final_message['MsgCode'];
            $data_driverRequest['vStartLatlong'] = $sourceLoc;
            $data_driverRequest['vEndLatlong'] = $destLoc;
            $data_driverRequest['tStartAddress'] = $PickUpAddress;
            $data_driverRequest['tEndAddress'] = $DestAddress;
            $data_driverRequest['tDate'] = @date("Y-m-d H:i:s");

            addToDriverRequest2($data_driverRequest);
            // addToUserRequest($passengerId,$item['iDriverId'],$msg_encode,$final_message['MsgCode']);
            // addToDriverRequest($item['iDriverId'],$passengerId,0,"Timeout");
        }
        if (count($registation_ids_new) > 0) {
            // $Rmessage = array("message" => $message);
            $Rmessage = array("message" => $msg_encode);

            $result = send_notification($registation_ids_new, $Rmessage, 0);
            //print_r($result);
        }
        if (count($deviceTokens_arr_ios) > 0) {
            // sendApplePushNotification(1,$deviceTokens_arr_ios,$message,$alertMsg,1);
            sendApplePushNotification(1, $deviceTokens_arr_ios, $msg_encode, $alertMsg, 0);
        }
    }

    $log = PHP_EOL . "Free Driver With ID $driver_id_auto Assigned to Order $iOrderId......!" . PHP_EOL;
    try {
        saveAlgoLog($log);
    } catch (Exception $e) {
        
    }
    $AssignResp = array(
        'status' => 1,
        'iDriverId' => $driver_id_auto
    );
    return $AssignResp;
}

function GetMatFromArrayDrop($DropArrays, $LatLong) {
    $Mat = '';
    foreach ($DropArrays as $DropArray) {
        if ((substr($DropArray['lat'], 0, 5) == substr($LatLong['lat'], 0, 5)) && (substr($DropArray['long'], 0, 5) == substr($LatLong['long'], 0, 5))) {
            $Mat = $DropArray['time'];
        }
    }

    return $Mat;
}

/*
 * **************************Get Driving distnce waypoints******************************************
 */

function GetDrivingDistanceRouting($origion, $destination, $waypoints, $pickMat, $DropArrays) {

    $url = "https://maps.googleapis.com/maps/api/directions/json?origin=$origion&destination=$destination&waypoints=optimize:false|$waypoints&mode=driving&key=AIzaSyBpS6KkKA19pQD7Ai663AbGInvuSZWxICI";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);

    $response_a = json_decode($response, true);
    $RouteLegs = $response_a['routes'][0]['legs'];
    $totaldistance = 0;
    $totaltime = 0;
    $satisfy = "yes";

    //echo "<pre> Drop Arrays";    print_r($DropArrays); echo "</pre>";

    $log = '';
    echo $DCount = count($RouteLegs);
    exit;
    for ($i = 0; $i < $DCount; $i++) {
        $checkTimeStart = '';
        $distance = ($RouteLegs[$i]['distance']['value'] / 1000);
        $time = round(($RouteLegs[$i]['duration']['value'] / 60) + 1);
        $totaltime = $totaltime + $time;
        if ($i = 0) {
            $checkTimeStart = $pickMat;
        } else {
            $LatLongStart = array(
                'lat' => $RouteLegs[$i]['start_location']['lat'],
                'long' => $RouteLegs[$i]['start_location']['lng']
            );

            echo 'Start time<br/> ' . $checkTimeStart = GetMatFromArrayDrop($DropArrays, $LatLongStart);
        }


        $LatLongEnd = array(
            'lat' => $RouteLegs[$i]['end_location']['lat'],
            'long' => $RouteLegs[$i]['end_location']['lng']
        );
        echo "<pre>Start Latlong";
        print_r($LatLongStart);
        echo "</pre>";
        echo "<pre>End lat Long";
        print_r($LatLongEnd);
        echo "</pre>";
        echo 'End Time<br/> ' . $CheckTimeEnd = GetMatFromArrayDrop($DropArrays, $LatLongEnd);


        $timeValueCal = strtotime('+' . $time . ' minutes', strtotime($checkTimeStart));
        $FinalTimeVal = strtotime($CheckTimeEnd);
        if ($timeValueCal < $FinalTimeVal) {
            $log .= PHP_EOL . "-------------------- For Init $i Calculated Time is :$timeValueCal End time is:$FinalTimeVal AND MAT is satisfy" . PHP_EOL;
        } else {
            $satisfy = "no";

            $log .= PHP_EOL . "-------------------- For Init $i Calculated Time is :$timeValueCal End time is:$FinalTimeVal AND MAT is NOT satisfy" . PHP_EOL;
        }

        unset($LatLongStart);
        unset($LatLongEnd);
    }




    if ($satisfy == 'yes') {
        $log .= PHP_EOL . "--------------------MAT is satisfy by using Google way points total time taken is : $totaltime  ----------------" . PHP_EOL;
    } else {
        $log .= PHP_EOL . "--------------------MAT is NOT satisfy by using Google way points total time taken is :   $totaltime ----------------" . PHP_EOL;
    }


    $log .= PHP_EOL . "----------------------------------------------------********************--------------------------------------------------------" . PHP_EOL;
    try {
        saveAlgoLog($log);
    } catch (Exception $e) {
        
    }

    return $satisfy;
}

function GetDrivingDistanceRoutingOLSSD($origion, $destination, $waypoints, $mat, $Prevtime, $DropArrays) {

    $url = "https://maps.googleapis.com/maps/api/directions/json?origin=$origion&destination=$destination&waypoints=optimize:true|$waypoints&mode=driving&key=AIzaSyBpS6KkKA19pQD7Ai663AbGInvuSZWxICI";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    //echo $response; exit;
    $response_a = json_decode($response, true);
    $RouteLegs = $response_a['routes'][0]['legs'];
    $totaldistance = 0;
    $totaltime = 0;
    foreach ($RouteLegs as $RouteLeg):
        $distance = ($RouteLeg['distance']['value'] / 1000);
        $time = round(($RouteLeg['duration']['value'] / 60));




        $totaldistance = $totaldistance + $distance;

        $totaltime = $totaltime + $time;
    endforeach;
    $timeValueInMin = $totaltime + ( 2 * $Prevtime);
    $timeValueNew = strtotime('+' . $timeValueInMin . ' minutes', strtotime(date('Y-m-d H:i:s')));

    $timeValueNewInDate = date('Y-m-d H:i:s', $timeValueNew);
    $InitialTimeVal = strtotime($InitalTime);

    $log .= PHP_EOL . "--------------------New Time With distance time add : $timeValueNewInDate  in seconds $timeValueNew  ----------------" . PHP_EOL;
    $newChecktime = strtotime($mat);
    $log .= PHP_EOL . "--------------------Final time check is:$Finaltime in seconds $newChecktime  ----------------" . PHP_EOL;

    if ($Prevtime < $totaltime) {
        return "yes";
    } else {
        return "no";
    }
//            [0]['distance']['text'];
//    $distvalue = $response_a['routes'][0]['legs'][0]['distance']['value'];
//    $time = $response_a['rows'][0]['elements'][0]['duration']['text'];
//    $timevalue = $response_a['rows'][0]['elements'][0]['duration']['value'];
    //if(substr($dist, $start))
    // return array('distance' => ($distvalue / 1000), 'time' => ($timevalue / 60));
}

/*
 * ************************ Check Time Differennce 
 */

function CheckTimeDiffBtwTwoLocation($lat1, $lat2, $long1, $long2, $InitalTime, $Finaltime, $AddAdditionalTime) {
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
    $timeValueInMin = round(($timevalue / 60) + $AddAdditionalTime);
    $currentDatetime = date('Y-m-d H:i:s');
    $log = PHP_EOL . "-------------------- Calculated Time in seconds $timeValueInMin ----------------" . PHP_EOL;
    $log .= PHP_EOL . "-------------------- Previous Time $InitalTime ----------------" . PHP_EOL;
    $timeValueNew = strtotime('+' . $timeValueInMin . ' minutes', strtotime($InitalTime));

    $timeValueNewInDate = date('Y-m-d H:i:s', $timeValueNew);
    $InitialTimeVal = strtotime($InitalTime);

    $log .= PHP_EOL . "--------------------New Time With distance time add : $timeValueNewInDate  in seconds $timeValueNew  ----------------" . PHP_EOL;
    $newChecktime = strtotime($Finaltime);
    $log .= PHP_EOL . "--------------------Final time check is:$Finaltime in seconds $newChecktime  ----------------" . PHP_EOL;
    $log .= PHP_EOL . "----------------------------------------------------********************--------------------------------------------------------" . PHP_EOL;
    try {
        saveAlgoLog($log);
    } catch (Exception $e) {
        
    }
    if ($timeValueNew <= $newChecktime) {
        return 'yes';
    } else {
        return 'no';
    }
}

//function CheckTimeDiffBtwTwoLocation($lat1, $lat2, $long1, $long2, $InitalTime)
//{
//    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&mode=driving&key=AIzaSyBpS6KkKA19pQD7Ai663AbGInvuSZWxICI";
//    $ch = curl_init();
//    curl_setopt($ch, CURLOPT_URL, $url);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//    $response = curl_exec($ch);
//    curl_close($ch);
//    $response_a = json_decode($response, true);
//    $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
//    $distvalue = $response_a['rows'][0]['elements'][0]['distance']['value'];
//    $time = $response_a['rows'][0]['elements'][0]['duration']['text'];
//    $timevalue = $response_a['rows'][0]['elements'][0]['duration']['value'];
//    //if(substr($dist, $start))
//    $timeValueInMin = round(($timevalue / 60));
//    $currentDatetime = date('Y-m-d H:i:s');
//    $log = PHP_EOL . "--------------------Time Value $currentDatetime with change in Minutes: $timeValueInMin   ----------------" . PHP_EOL;
//    $timeValueNew = strtotime('+'.$timeValueInMin.' minutes', strtotime($currentDatetime));
//    $timeValueNewInDate = date('Y-m-d H:i:s', $timeValueNew);
//    $InitialTimeVal = strtotime($InitalTime);
//    
//     $log .= PHP_EOL . "--------------------Inital time is: $InitalTime   in seconds $InitialTimeVal  ----------------" . PHP_EOL;
//     $log .= PHP_EOL . "--------------------New time check is:$timeValueNewInDate in seconds $timeValueNew  ----------------" . PHP_EOL;
//     
//      try {
//                    saveAlgoLog($log);
//                } catch (Exception $e) {
//                    
//                }
//    if($timeValueNew <= $InitialTimeVal)
//    {
//        return 'yes';
//    }
//    else
//    {
//        return 'no';
//    }
//    
//   
//    
//}

/*
 * ********************************Get Driving distance from google **************************
 */

function GetDrivingDistance($lat1, $lat2, $long1, $long2) {
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
    return array('distance' => ($distvalue / 1000), 'time' => ($timevalue / 60));
}

function checklastorderprocesstime($iDriverId = '') {
    global $generalobj, $obj;
//            if(!empty($iDriverId))
//            {
    $sql = "SELECT `tEndDate` FROM `trips` WHERE `iDriverId`= $iDriverId ORDER BY `iTripId` DESC LIMIT 1 ";
    $result = $obj->MySQLSelect($sql);
    return $result;
//            }
//            else { return 0 ;}
}

function countnooftrips($iDriverId = '') {
    global $generalobj, $obj;
//            if(!empty($iDriverId))
//            {
    $sql = "SELECT `tEndDate` FROM `trips` WHERE `iDriverId`= $iDriverId AND iActive IN ('On Going Trip', 'Active')";
    $result = $obj->MySQLSelect($sql);
    return count($result);
}

function getlastorderlatlong($iDriverId = '') {
    global $generalobj, $obj;
//            if(!empty($iDriverId))
//            {
    $sql = "SELECT `tEndDate` FROM `trips` WHERE `iDriverId`= $iDriverId AND iActive IN ('On Going Trip', 'Active')";
    $result = $obj->MySQLSelect($sql);
    return count($result);
}

function getOnlineDriverArrAnviam($sourceLat, $sourceLon, $address_data = array(), $DropOff = "No", $From_Autoassign = "No", $Check_Driver_UFX = "No", $Check_Date_Time = "", $destLat = "", $destLon = "") {
    global $generalobj, $obj, $RESTRICTION_KM_NEAREST_TAXI, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $LIST_DRIVER_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $COMMISION_DEDUCT_ENABLE, $WALLET_MIN_BALANCE, $RESTRICTION_KM_NEAREST_TAXI, $APP_TYPE, $vTimeZone;

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $LIST_DRIVER_LIMIT_BY_DISTANCE = $From_Autoassign == "Yes" ? $LIST_RESTAURANT_LIMIT_BY_DISTANCE : $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLocationUpdateDate";

    $sourceLocationArr = array($sourceLat, $sourceLon);
    $destinationLocationArr = array($destLat, $destLon);
    $ssql_available = "";
    $allowed_ans = "Yes";
    $allowed_ans_drop = "Yes";
    $vLatitude = 'vLatitude';
    $vLongitude = 'vLongitude';
    if ($Check_Driver_UFX == "No") {
        $ssql_available .= " AND vAvailability = 'Available' AND vTripStatus != 'Active' AND tLocationUpdateDate > '$str_date' ";
    }

    if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
        $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
			* cos( radians( ROUND(" . $vLatitude . ",8) ) )
			* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
			+ sin( radians(" . $sourceLat . ") )
			* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver`
			WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active')
			HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " ORDER BY distance ASC LIMIT 3";

        $Data = $obj->MySQLSelect($sql);
        //echo "<pre>"; print_r($Data);
        $newData = array();
        $j = 0;
        $driver_id_auto = "";
        for ($i = 0; $i < count($Data); $i++) {
            $lastprocessstime = checklastorderprocesstime($Data[$i]['iDriverId']);
            $Data[$i]['lastproccessedtime'] = $lastprocessstime[0]['tEndDate'] ? $lastprocessstime[0]['tEndDate'] : 0;

            $pickupdistime = GetDrivingDistance($sourceLat, $Data[$i]['vLatitude'], $sourceLon, $Data[$i]['vLongitude']);
            $Data[$i]['pickupdistance'] = $pickupdistime['distance'];
            $Data[$i]['pickuptime'] = $pickupdistime['time'];

            $iDriverVehicleId = $Data[$i]['iDriverVehicleId'];
            $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
            $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');

            $distanceusercompany = distanceByLocation($sourceLat, $sourceLon, $destLat, $destLon, "K");
            $Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];

            if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
                $user_available_balance = $generalobj->get_user_available_balance($Data[$i]['iDriverId'], "Driver");
                if ($WALLET_MIN_BALANCE > $user_available_balance) {
                    $Data[$i]['ACCEPT_CASH_TRIPS'] = "No";
                } else {
                    $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
                }
            } else {
                $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
            }
            $nooftrips = countnooftrips($Data[$i]['iDriverId']);
            if (($fRadius > $distanceusercompany) && ($nooftrips < 2)) {
                $driver_id_auto .= $Data[$i]['iDriverId'] . ",";
                $newData[$j] = $Data[$i];
                $j++;
            }
        }
        //print_r($newData);
        $driver_id_auto = substr($driver_id_auto, 0, -1);

        //$returnData['DriverList'] = $Data;
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = $driver_id_auto;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    } else {
        /* $Data = array();
          $returnData['DriverList'] = $Data; */
        $newData = array();
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = "";
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    }

    return $returnData;
}

function getOnlineDriverArrAnviamDriver($sourceLat, $sourceLon, $address_data = array(), $DropOff = "No", $From_Autoassign = "No", $Check_Driver_UFX = "No", $Check_Date_Time = "", $destLat = "", $destLon = "") {
    global $generalobj, $obj, $RESTRICTION_KM_NEAREST_TAXI, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $LIST_DRIVER_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $COMMISION_DEDUCT_ENABLE, $WALLET_MIN_BALANCE, $RESTRICTION_KM_NEAREST_TAXI, $APP_TYPE, $vTimeZone;

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $LIST_DRIVER_LIMIT_BY_DISTANCE = $From_Autoassign == "Yes" ? $LIST_RESTAURANT_LIMIT_BY_DISTANCE : $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLocationUpdateDate";

    $sourceLocationArr = array($sourceLat, $sourceLon);
    $destinationLocationArr = array($destLat, $destLon);
    $ssql_available = "";
    $allowed_ans = "Yes";
    $allowed_ans_drop = "Yes";
    $vLatitude = 'vLatitude';
    $vLongitude = 'vLongitude';
    if ($Check_Driver_UFX == "No") {
        //vAvailability = 'Available'  AND AND vTripStatus != 'Active'
        $ssql_available .= " AND  tLocationUpdateDate > '$str_date' ";
    }

    if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
        $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
			* cos( radians( ROUND(" . $vLatitude . ",8) ) )
			* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
			+ sin( radians(" . $sourceLat . ") )
			* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver`
			WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active')
			HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " ORDER BY distance ASC LIMIT 3";

        $Data = $obj->MySQLSelect($sql);
        //echo "<pre>"; print_r($Data);
        $newData = array();
        $j = 0;
        $driver_id_auto = "";
        for ($i = 0; $i < count($Data); $i++) {
            $lastprocessstime = checklastorderprocesstime($Data[$i]['iDriverId']);
            $Data[$i]['lastproccessedtime'] = $lastprocessstime[0]['tEndDate'] ? $lastprocessstime[0]['tEndDate'] : 0;

            $pickupdistime = GetDrivingDistance($sourceLat, $Data[$i]['vLatitude'], $sourceLon, $Data[$i]['vLongitude']);
            $Data[$i]['pickupdistance'] = $pickupdistime['distance'];
            $Data[$i]['pickuptime'] = $pickupdistime['time'];

            $iDriverVehicleId = $Data[$i]['iDriverVehicleId'];
            $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
            $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');

            $distanceusercompany = distanceByLocation($sourceLat, $sourceLon, $destLat, $destLon, "K");
            $Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];

            if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
                $user_available_balance = $generalobj->get_user_available_balance($Data[$i]['iDriverId'], "Driver");
                if ($WALLET_MIN_BALANCE > $user_available_balance) {
                    $Data[$i]['ACCEPT_CASH_TRIPS'] = "No";
                } else {
                    $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
                }
            } else {
                $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
            }

            $nooftrips = countnooftrips($Data[$i]['iDriverId']);
            if (($fRadius > $distanceusercompany) && ($nooftrips < 2)) {
                $driver_id_auto .= $Data[$i]['iDriverId'] . ",";
                $newData[$j] = $Data[$i];
                $j++;
            }
        }

        $driver_id_auto = substr($driver_id_auto, 0, -1);

        //$returnData['DriverList'] = $Data;
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = $driver_id_auto;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    } else {
        /* $Data = array();
          $returnData['DriverList'] = $Data; */
        $newData = array();
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = "";
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    }

    return $returnData;
}

/* New_Change_Anviam */

function getOnlineDriverArr($sourceLat, $sourceLon, $address_data = array(), $DropOff = "No", $From_Autoassign = "No", $Check_Driver_UFX = "No", $Check_Date_Time = "", $destLat = "", $destLon = "") {
    global $generalobj, $obj, $RESTRICTION_KM_NEAREST_TAXI, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $LIST_DRIVER_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $COMMISION_DEDUCT_ENABLE, $WALLET_MIN_BALANCE, $RESTRICTION_KM_NEAREST_TAXI, $APP_TYPE, $vTimeZone;

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $LIST_DRIVER_LIMIT_BY_DISTANCE = $From_Autoassign == "Yes" ? $LIST_RESTAURANT_LIMIT_BY_DISTANCE : $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLocationUpdateDate";

    $sourceLocationArr = array($sourceLat, $sourceLon);
    $destinationLocationArr = array($destLat, $destLon);
    $ssql_available = "";
    $allowed_ans = "Yes";
    $allowed_ans_drop = "Yes";
    $vLatitude = 'vLatitude';
    $vLongitude = 'vLongitude';
    if ($Check_Driver_UFX == "No") {
        $ssql_available .= " AND vAvailability = 'Available' AND vTripStatus != 'Active' AND tLocationUpdateDate > '$str_date' ";
    }

    if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
        $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
			* cos( radians( ROUND(" . $vLatitude . ",8) ) )
			* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
			+ sin( radians(" . $sourceLat . ") )
			* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver`
			WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active')
			HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " ORDER BY `register_driver`.`" . $param . "` ASC";

        $Data = $obj->MySQLSelect($sql);

        $newData = array();
        $j = 0;
        $driver_id_auto = "";
        for ($i = 0; $i < count($Data); $i++) {
            $iDriverVehicleId = $Data[$i]['iDriverVehicleId'];
            $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
            $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');

            $distanceusercompany = distanceByLocation($sourceLat, $sourceLon, $destLat, $destLon, "K");
            $Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];

            if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
                $user_available_balance = $generalobj->get_user_available_balance($Data[$i]['iDriverId'], "Driver");
                if ($WALLET_MIN_BALANCE > $user_available_balance) {
                    $Data[$i]['ACCEPT_CASH_TRIPS'] = "No";
                } else {
                    $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
                }
            } else {
                $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
            }

            if ($fRadius > $distanceusercompany) {
                $driver_id_auto .= $Data[$i]['iDriverId'] . ",";
                $newData[$j] = $Data[$i];
                $j++;
            }
        }

        $driver_id_auto = substr($driver_id_auto, 0, -1);

        //$returnData['DriverList'] = $Data;
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = $driver_id_auto;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    } else {
        /* $Data = array();
          $returnData['DriverList'] = $Data; */
        $newData = array();
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = "";
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    }

    return $returnData;
}

function getNearRestaurantArr($sourceLat, $sourceLon, $iUserId, $fOfferType = "No", $searchword = "", $vAddress = "", $iServiceId = '', $baselocation= 0) {
//echo 3;
    global $generalobj, $obj, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD;

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

    //$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
    //$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
    //$param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    //$sourceLocationArr = array($sourceLat, $sourceLon);
    //$allowed_ans = 'Yes';//checkAllowedAreaNew($sourceLocationArr, "No");
    //$ssql = "";
/*   
   if ($fOfferType == "Yes") {
        //$ssql .= " AND ( company.fOfferType = 'Flat' OR company.fOfferType = 'Percentage' )";
    }
    if (SITE_TYPE == "Demo" && $searchword == "") {
        //$ResCountry = ($vUserDeviceCountry == "IN")?"('IN')":"('IN','".$vUserDeviceCountry."')";
        //$ssql .=  "AND ( eDemoDisplay = 'Yes' OR eLock = 'No' )";
        if ($vAddress != "") {
            //$ssql .= " AND ( company.vRestuarantLocation like '%$vAddress%' OR company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        } else {
            //$ssql .= " AND ( company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        }
    }

    if ($allowed_ans == 'Yes') {
        if ($baselocation != 0) {
            // $ssql .= " AND company.company_type IN (1,3) AND  company.company_bases LIKE '%$baselocation%' ";
            //$ssql .= "AND ((`company_type` = 1 AND `company_bases` LIKE '%$baselocation%') OR (`company_type` = 3))";
        } else {
            //$ssql .= " AND company.company_type IN (2,3)";
        }
	//$sql = "SELECT * FROM (SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") ) * cos( radians( vRestuarantLocationLat ) ) * cos( radians( vRestuarantLocationLong ) - radians(" . $sourceLon . ") ) + sin( radians(" . $sourceLat . ") ) * sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance,`company`.* FROM `company` WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND iServiceId = '" . $iServiceId . "' $ssql   AND `parent_iCompanyId` !=0 HAVING distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . " ORDER BY distance ASC ) AS T1 GROUP BY T1.`parent_iCompanyId` ORDER BY T1.distance ASC;";
	*/
//echo 2;
        $sql = "SELECT `company`.* FROM `company` WHERE eStatus='Active' ORDER BY listingOrder ASC;";
	//echo 'Working';
        $Data = $obj->MySQLSelect($sql);
		
        if (count($Data) > 0) {
            for ($i = 0; $i < count($Data); $i++) {
                $vAvgRating = $Data[$i]['vAvgRating'];
                $listingOrder = $Data[$i]['listingOrder'];
                $Data[$i]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$i]['vAvgRating'], 1) : 0;
                $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
                $restaurant_status_arr = calculate_restaurant_time_span($Data[$i]['iCompanyId'], $iUserId);
                $Data[$i]['Restaurant_Status'] = $restaurant_status_arr['status'];
                $Data[$i]['Restaurant_Opentime'] = $restaurant_status_arr['opentime'];
                $Data[$i]['Restaurant_Closetime'] = $restaurant_status_arr['closetime'];
                $Data[$i]['restaurantstatus'] = $restaurant_status_arr['restaurantstatus'];  // closed or open
                $Data[$i]['timeslotavailable'] = $restaurant_status_arr['timeslotavailable'];
                $CompanyDetailsArr = getCompanyDetailsShorted($Data[$i]['iCompanyId'], $iUserId, "No", "");
                $Data[$i]['Restaurant_Cuisine'] = $CompanyDetailsArr['Restaurant_Cuisine'];
                $Data[$i]['Restaurant_Cuisine_Id'] = $CompanyDetailsArr['Restaurant_Cuisine_Id'];
                if ($iServiceId == '1') {
                    $Data[$i]['Restaurant_PricePerPerson'] = $CompanyDetailsArr['Restaurant_PricePerPerson'];
                } else {
                    $Data[$i]['Restaurant_PricePerPerson'] = '';
                }
                $Data[$i]['Restaurant_OrderPrepareTime'] = $CompanyDetailsArr['Restaurant_OrderPrepareTime'];
                $Data[$i]['Restaurant_OfferMessage'] = $CompanyDetailsArr['Restaurant_OfferMessage'];
                $Data[$i]['Restaurant_OfferMessage_short'] = $CompanyDetailsArr['Restaurant_OfferMessage_short'];
                $Data[$i]['Restaurant_MinOrderValue'] = $CompanyDetailsArr['Restaurant_MinOrderValue'];
                //$Data[$i]['CompanyFoodData'] =  $CompanyDetailsArr['CompanyFoodData'];
                $Data[$i]['CompanyFoodDataCount'] = $CompanyDetailsArr['CompanyFoodDataCount'];
                $Data[$i]['CompanyFoodData'] = array();
				 
            }

            /* foreach($Data as $row)
              {
              $Data_name[] = $row['restaurantstatus'];
              }
              array_multisort($Data_name, SORT_DESC, $Data); */
            //array_multisort(array_column($Data, 'fPrepareTime'), SORT_ASC,array_column($Data, 'restaurantstatus'),SORT_DESC,$mylist);
        }
        //echo "<pre>";print_r($Data);exit;
       
    //} 
	//else {
       
        return $Data;
    //}
}

function checkRestrictedArea($address_data, $DropOff) {
    global $generalobj, $obj;
    $ssql = "";
    if ($DropOff == "No") {
        $ssql .= " AND (eRestrictType = 'Pick Up' OR eRestrictType = 'All')";
    } else {
        $ssql .= " AND (eRestrictType = 'Drop Off' OR eRestrictType = 'All')";
    }
    if (!empty($address_data)) {
        $pickaddrress = strtolower($address_data['CheckAddress']);
        $pickaddrress = preg_replace('/\d/', '', $pickaddrress);
        $pickaddrress = preg_replace('/\s+/', '', $pickaddrress);
        //$pickArr = explode(',',$pickaddrress);
        $pickArr = array_map('trim', array_filter(explode(',', $pickaddrress)));
        $sqlaa = "SELECT cr.vCountry,ct.vCity,st.vState,replace(rs.vAddress, ' ','') as vAddress FROM `restricted_negative_area` AS rs
			LEFT JOIN country as cr ON cr.iCountryId = rs.iCountryId
			LEFT JOIN state as st ON st.iStateId = rs.iStateId
			LEFT JOIN city as ct ON ct.iCityId = rs.iCityId
			WHERE eType='Allowed'" . $ssql;
        $allowed_data = $obj->MySQLSelect($sqlaa);
        $allowed_ans = 'No';
        if (!empty($allowed_data)) {
            foreach ($allowed_data as $rds) {
                $alwd_country = $alwd_state = $alwd_city = $alwd_address = 'allowed';
                if ($rds['vCountry'] != "") {
                    //if($rds['vCountry'] == $address_data['countryId']){
                    if (in_array(strtolower($rds['vCountry']), $pickArr)) {
                        $alwd_country = 'allowed';
                    } else {
                        $alwd_country = 'Disallowed';
                    }
                }
                if ($rds['vState'] != "") {
                    if (in_array(strtolower($rds['vState']), $pickArr)) {
                        $alwd_state = 'allowed';
                    } else {
                        $alwd_state = 'Disallowed';
                    }
                }
                if ($rds['vCity'] != "") {
                    if (in_array(strtolower($rds['vCity']), $pickArr)) {
                        $alwd_city = 'allowed';
                    } else {
                        $alwd_city = 'Disallowed';
                    }
                }
                if ($rds['vAddress'] != "") {
                    if (strstr(strtolower($pickaddrress), strtolower($rds['vAddress']))) {
                        $alwd_address = 'allowed';
                    } else {
                        $alwd_address = 'Disallowed';
                    }
                }
                if ($alwd_country == 'allowed' && $alwd_state == 'allowed' && $alwd_city == 'allowed' && $alwd_address == 'allowed') {
                    $allowed_ans = 'Yes';
                    break;
                }
            }
        }

        if ($allowed_ans == 'No') {
            //$sqlas = "SELECT * FROM `restricted_negative_area` WHERE (iCountryId='".$address_data['countryId']."' OR iStateId='".$address_data['stateId']."' OR iCityId='".$address_data['cityId']."') AND eType='Disallowed' AND (eRestrictType = 'Pick Up' OR eRestrictType = 'All')";
            $sqlas = "SELECT cr.vCountry,ct.vCity,st.vState,replace(rs.vAddress, ' ','') as vAddress FROM `restricted_negative_area` AS rs
				LEFT JOIN country as cr ON cr.iCountryId = rs.iCountryId
                LEFT JOIN state as st ON st.iStateId = rs.iStateId
                LEFT JOIN city as ct ON ct.iCityId = rs.iCityId
				WHERE eType='Disallowed'" . $ssql;
            $restricted_data = $obj->MySQLSelect($sqlas);
            $allowed_ans = 'Yes';
            if (!empty($restricted_data)) {
                foreach ($restricted_data as $rds) {
                    $alwd_country = $alwd_state = $alwd_city = $alwd_address = 'Disallowed';
                    if ($rds['vCountry'] != "") {
                        if (in_array(strtolower($rds['vCountry']), $pickArr)) {
                            $alwd_country = 'Disallowed';
                        } else {
                            $alwd_country = 'allowed';
                        }
                    }
                    if ($rds['vState'] != "") {
                        if (in_array(strtolower($rds['vState']), $pickArr)) {
                            $alwd_state = 'Disallowed';
                        } else {
                            $alwd_state = 'allowed';
                        }
                    }
                    if ($rds['vCity'] != "") {
                        if (in_array(strtolower($rds['vCity']), $pickArr)) {
                            $alwd_city = 'Disallowed';
                        } else {
                            $alwd_city = 'allowed';
                        }
                    }
                    if ($rds['vAddress'] != "") {
                        if (strstr(strtolower($pickaddrress), strtolower($rds['vAddress']))) {
                            $alwd_address = 'Disallowed';
                        } else {
                            $alwd_address = 'allowed';
                        }
                    }
                    if ($alwd_country == 'Disallowed' && $alwd_state == 'Disallowed' && $alwd_city == 'Disallowed' && $alwd_address == "Disallowed") {
                        $allowed_ans = 'No';
                        break;
                    }
                }
            }
        }
    }
    return $allowed_ans;
}

function getAddressFromLocation($latitude, $longitude, $Google_Server_key) {
    $location_Address = "";

    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $latitude . "," . $longitude . "&key=" . $Google_Server_key;

    try {

        $jsonfile = file_get_contents($url);
        $jsondata = json_decode($jsonfile);
        $address = $jsondata->results[0]->formatted_address;

        $location_Address = $address;
    } catch (ErrorException $ex) {

        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
        exit;
        // echo 'Site not reachable (' . $ex->getMessage() . ')';
    }

    if ($location_Address == "") {
        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
        exit;
    }

    return $location_Address;
}

function getLanguageTitle($vLangCode) {
    global $obj;

    $sql = "SELECT vTitle FROM language_master WHERE vCode = '" . $vLangCode . "' ";
    $db_title = $obj->MySQLSelect($sql);

    return $db_title[0]['vTitle'];
}

function checkSurgePrice($vehicleTypeID, $selectedDateTime = "") {
    $ePickStatus = get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    $eNightStatus = get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');

    $fPickUpPrice = 1;
    $fNightPrice = 1;

    if ($selectedDateTime == "") {
        // $currentTime = @date("Y-m-d H:i:s");
        $currentTime = @date("H:i:s");
        $currentDay = @date("D");
    } else {
        // $currentTime = $selectedDateTime;
        $currentTime = @date("H:i:s", strtotime($selectedDateTime));
        $currentDay = @date("D", strtotime($selectedDateTime));
    }

    if ($ePickStatus == "Active" || $eNightStatus == "Active") {

        $startTime_str = "t" . $currentDay . "PickStartTime";
        $endTime_str = "t" . $currentDay . "PickEndTime";
        $price_str = "f" . $currentDay . "PickUpPrice";

        $pickStartTime = get_value('vehicle_type', $startTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $pickEndTime = get_value('vehicle_type', $endTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $fPickUpPrice = get_value('vehicle_type', $price_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');

        $nightStartTime = get_value('vehicle_type', 'tNightStartTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $nightEndTime = get_value('vehicle_type', 'tNightEndTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $fNightPrice = get_value('vehicle_type', 'fNightPrice', 'iVehicleTypeId', $vehicleTypeID, '', 'true');

        $tempNightHour = "12:00:00";
        if ($currentTime > $pickStartTime && $currentTime < $pickEndTime && $ePickStatus == "Active") {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PICK_SURGE_NOTE";
            $returnArr['SurgePrice'] = $fPickUpPrice . "X";
            $returnArr['SurgePriceValue'] = $fPickUpPrice;
        }
        // else if ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $eNightStatus == "Active") {
        else if ((($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime > $tempNightHour) || ($currentTime < $nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime > $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour)) && $eNightStatus == "Active") {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NIGHT_SURGE_NOTE";
            $returnArr['SurgePrice'] = $fNightPrice . "X";
            $returnArr['SurgePriceValue'] = $fNightPrice;
        } else {
            $returnArr['Action'] = "1";
        }
    } else {
        $returnArr['Action'] = "1";
    }

    return $returnArr;
}

function check_email_send($iDriverId, $tablename, $field) {
    global $obj, $generalobj;
    $sql = "SELECT * FROM " . $tablename . " WHERE " . $field . "= '" . $iDriverId . "'";
    $db_data = $obj->MySQLSelect($sql);
    //print_r($db_data);//exit;
    //$valid=0;
    if ($tablename == 'register_driver') {
        //echo "hi";exit;
        if ($db_data[0]['vNoc'] != NULL && $db_data[0]['vLicence'] != NULL && $db_data[0]['vCerti'] != NULL) {
            //global $generalobj;
            $maildata['USER'] = "Driver";
            $maildata['NAME'] = $db_data[0]['vName'];
            $maildata['EMAIL'] = $db_data[0]['vEmail'];
            $generalobj->send_email_user("PROFILE_UPLOAD", $maildata);
            //header("location:profile.php?success=1&var_msg=" . $var_msg);
            //return;
        }
    } else {
        if ($db_data[0]['vNoc'] != NULL && $db_data[0]['vCerti'] != NULL) {
            $maildata['USER'] = "Company";
            $maildata['NAME'] = $db_data[0]['vName'];
            $maildata['EMAIL'] = $db_data[0]['vEmail'];
            //var_dump($maildata);
            //var_dump(($generalobj));
            $generalobj->send_email_user("PROFILE_UPLOAD", $maildata);
        }
    }
    return true;
}

function checkmemberemailphoneverification($iMemberId, $user_type = "Passenger") {
    global $obj, $DRIVER_EMAIL_VERIFICATION, $DRIVER_PHONE_VERIFICATION, $RIDER_EMAIL_VERIFICATION, $RIDER_PHONE_VERIFICATION, $COMPANY_EMAIL_VERIFICATION, $COMPANY_PHONE_VERIFICATION;
    if ($user_type == "Driver") {
        /* $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_EMAIL_VERIFICATION', '', 'true');
          $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_PHONE_VERIFICATION', '', 'true');
          $eEmailVerified = get_value('register_driver', 'eEmailVerified', 'iDriverId', $iMemberId, '', 'true');
          $ePhoneVerified = get_value('register_driver', 'ePhoneVerified', 'iDriverId', $iMemberId, '', 'true'); */
        $EMAIL_VERIFICATION = $DRIVER_EMAIL_VERIFICATION;
        $PHONE_VERIFICATION = $DRIVER_PHONE_VERIFICATION;
        $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $eEmailVerified = $driverData[0]['eEmailVerified'];
        $ePhoneVerified = $driverData[0]['ePhoneVerified'];
    } else if ($user_type == "Company") {
        /* $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_EMAIL_VERIFICATION', '', 'true');
          $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_PHONE_VERIFICATION', '', 'true');
          $eEmailVerified = get_value('register_driver', 'eEmailVerified', 'iDriverId', $iMemberId, '', 'true');
          $ePhoneVerified = get_value('register_driver', 'ePhoneVerified', 'iDriverId', $iMemberId, '', 'true'); */
        $EMAIL_VERIFICATION = $COMPANY_EMAIL_VERIFICATION;
        $PHONE_VERIFICATION = $COMPANY_PHONE_VERIFICATION;
        $sqld = "SELECT eEmailVerified,ePhoneVerified FROM company WHERE iCompanyId = '" . $iMemberId . "'";
        $companyData = $obj->MySQLSelect($sqld);
        $eEmailVerified = $companyData[0]['eEmailVerified'];
        $ePhoneVerified = $companyData[0]['ePhoneVerified'];
    } else {
        /* $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'RIDER_EMAIL_VERIFICATION', '', 'true');
          $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'RIDER_PHONE_VERIFICATION', '', 'true');
          $eEmailVerified = get_value('register_user', 'eEmailVerified', 'iUserId', $iMemberId, '', 'true');
          $ePhoneVerified = get_value('register_user', 'ePhoneVerified', 'iUserId', $iMemberId, '', 'true'); */
        $EMAIL_VERIFICATION = $RIDER_EMAIL_VERIFICATION;
        $PHONE_VERIFICATION = $RIDER_PHONE_VERIFICATION;
        $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_user WHERE iUserId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $eEmailVerified = $driverData[0]['eEmailVerified'];
        $ePhoneVerified = $driverData[0]['ePhoneVerified'];
    }

    $email = $EMAIL_VERIFICATION == "Yes" ? ($eEmailVerified == "Yes" ? "true" : "false") : "true";
    $phone = $PHONE_VERIFICATION == "Yes" ? ($ePhoneVerified == "Yes" ? "true" : "false") : "true";

    if ($email == "false" && $phone == "false") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_EMAIL_PHONE_VERIFY";
        echo json_encode($returnArr);
        exit;
    } else if ($email == "true" && $phone == "false") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_PHONE_VERIFY";
        echo json_encode($returnArr);
        exit;
    } else if ($email == "false" && $phone == "true") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_EMAIL_VERIFY";
        echo json_encode($returnArr);
        exit;
    }
}

function sendemailphoneverificationcode($iMemberId, $user_type = "Passenger", $VerifyType) {
    global $generalobj, $obj;
    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    } else {
        $tblname = "register_driver";
        $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iDriverId';
        $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    }
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");

    $str = "select * from send_message_templates where vEmail_Code='VERIFICATION_CODE_MESSAGE'";
    $res = $obj->MySQLSelect($str);
    $prefix = $res[0]['vBody_' . $vLangCode];

    //$prefix = $languageLabelsArr['LBL_VERIFICATION_CODE_TXT'];

    $emailmessage = "";
    $phonemessage = "";
    if ($VerifyType == "email" || $VerifyType == "both") {
        $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
        $db_member = $obj->MySQLSelect($sql);

        $Data_Mail['vEmailVarificationCode'] = $random = substr(number_format(time() * rand(), 0, '', ''), 0, 4);
        $Data_Mail['vEmail'] = isset($db_member[0]['vEmail']) ? $db_member[0]['vEmail'] : '';
        $vFirstName = isset($db_member[0]['vName']) ? $db_member[0]['vName'] : '';
        $vLastName = isset($db_member[0]['vLastName']) ? $db_member[0]['vLastName'] : '';
        $Data_Mail['vName'] = $vFirstName . " " . $vLastName;
        $Data_Mail['CODE'] = $Data_Mail['vEmailVarificationCode'];

        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail) {
            $emailmessage = $Data_Mail['vEmailVarificationCode'];
        } else {
            $emailmessage = "LBL_EMAIL_VERIFICATION_FAILED_TXT";
        }
    }

    if ($VerifyType == "phone" || $VerifyType == "both") {
        $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
        $db_member = $obj->MySQLSelect($sql);

        $mobileNo = $db_member[0]['vPhoneCode'] . $db_member[0]['vPhone'];
        $toMobileNum = "+" . $mobileNo;
        $verificationCode = mt_rand(1000, 9999);
        $message = $prefix . ' ' . $verificationCode;
        $result = sendEmeSms($toMobileNum, $message);
        if ($result == 0) {
            $phonemessage = "LBL_MOBILE_VERIFICATION_FAILED_TXT";
        } else {
            $phonemessage = $verificationCode;
        }
    }

    $returnArr['emailmessage'] = $emailmessage;
    $returnArr['phonemessage'] = $phonemessage;
    return $returnArr;
}

function getTripPriceDetails($iTripId, $iMemberId, $eUserType = "Passenger", $PAGE_MODE = "HISTORY") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($eUserType == "Passenger") {
        $tblname = "register_user";
        $vLang = "vLang";
        $iUserId = "iUserId";
        $vCurrency = "vCurrencyPassenger";

        //$currencycode = get_value("trips", $vCurrency, "iTripId", $iTripId, '', 'true');
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $userlangcode = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
    } else {
        $tblname = "register_driver";
        $vLang = "vLang";
        $iUserId = "iDriverId";
        $vCurrency = "vCurrencyDriver";

        //$currencycode = get_value($tblname, $vCurrency, $iUserId, $iMemberId, '', 'true');
        $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $currencycode = $driverData[0]['vCurrencyDriver'];
        $userlangcode = $driverData[0]['vLang'];
        $currencySymbol = $driverData[0]['vSymbol'];
    }
    //$userlangcode = get_value($tblname, $vLang, $iUserId, $iMemberId, '', 'true');
    if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
    if ($currencycode == "" || $currencycode == NULL) {
        $sql = "SELECT vName,vSymbol from currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sql);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
    }


    //$sql = "SELECT * from trips WHERE iTripId = '" . $iTripId . "'";                                                                      
    //$sql = "SELECT tr.*,vt.vVehicleType_".$userlangcode." as vVehicleType,vt.vLogo,vt.iVehicleCategoryId,vt.fFixedFare,vt.eIconType,COALESCE(vc.iParentId, '0') as iParentId,COALESCE(vc.ePriceType, '') as ePriceType,COALESCE(vc.vLogo, '') as vLogoVehicleCategory,COALESCE(vc.vCategory_".$userlangcode.", '') as vCategory from trips as tr LEFT JOIN  vehicle_type as vt ON tr.iVehicleTypeId = vt.iVehicleTypeId  LEFT JOIN vehicle_category as vc ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE tr.iTripId = '" . $iTripId . "'";
    $sql = "SELECT tr.*,vt.vVehicleType_" . $userlangcode . " as vVehicleType,vt.vLogo,vt.iVehicleCategoryId,vt.fFixedFare,vt.eIconType from trips as tr LEFT JOIN  vehicle_type as vt ON tr.iVehicleTypeId = vt.iVehicleTypeId WHERE tr.iTripId = '" . $iTripId . "'";
    $tripData = $obj->MySQLSelect($sql);
    $priceRatio = $tripData[0]['fRatio_' . $currencycode];
    $iActive = $tripData[0]['iActive'];
    // Convert Into Timezone
    $tripTimeZone = $tripData[0]['vTimeZone'];
    if ($tripTimeZone != "") {
        $serverTimeZone = date_default_timezone_get();
        $tripData[0]['tTripRequestDate'] = converToTz($tripData[0]['tTripRequestDate'], $tripTimeZone, $serverTimeZone);
        $tripData[0]['tDriverArrivedDate'] = converToTz($tripData[0]['tDriverArrivedDate'], $tripTimeZone, $serverTimeZone);
        if ($tripData[0]['tStartDate'] != "0000-00-00 00:00:00") {
            $tripData[0]['tStartDate'] = converToTz($tripData[0]['tStartDate'], $tripTimeZone, $serverTimeZone);
        }
        $tripData[0]['tEndDate'] = converToTz($tripData[0]['tEndDate'], $tripTimeZone, $serverTimeZone);
    }
    // Convert Into Timezone

    $returnArr = array_merge($tripData[0], $returnArr);
    if ($tripData[0]['iUserPetId'] > 0) {
        $petDetails_arr = get_value('user_pets', 'iPetTypeId,vTitle as PetName,vWeight as PetWeight, tBreed as PetBreed, tDescription as PetDescription', 'iUserPetId', $tripData[0]['iUserPetId'], '', '');
    } else {
        $petDetails_arr = array();
    }
    $iPackageTypeId = $tripData[0]['iPackageTypeId'];
    if ($iPackageTypeId != 0) {
        $returnArr['PackageType'] = get_value('package_type', 'vName', 'iPackageTypeId', $iPackageTypeId, '', 'true');
    }

    if (count($petDetails_arr) > 0) {
        $petTypeName = get_value('pet_type', 'vTitle_' . $userlangcode, 'iPetTypeId', $petDetails_arr[0]['iPetTypeId'], '', 'true');
        $returnArr['PetDetails']['PetName'] = $petDetails_arr[0]['PetName'];
        $returnArr['PetDetails']['PetWeight'] = $petDetails_arr[0]['PetWeight'];
        $returnArr['PetDetails']['PetBreed'] = $petDetails_arr[0]['PetBreed'];
        $returnArr['PetDetails']['PetDescription'] = $petDetails_arr[0]['PetDescription'];
        $returnArr['PetDetails']['PetTypeName'] = $petTypeName;
    } else {
        $returnArr['PetDetails']['PetName'] = '';
        $returnArr['PetDetails']['PetWeight'] = '';
        $returnArr['PetDetails']['PetBreed'] = '';
        $returnArr['PetDetails']['PetDescription'] = '';
        $returnArr['PetDetails']['PetTypeName'] = '';
    }

    /* User Wallet Information */
    $returnArr['UserDebitAmount'] = strval($tripData[0]['fWalletDebit']);
    /* User Wallet Information */

    /* $vVehicleType = get_value('vehicle_type', "vVehicleType_" . $userlangcode, 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
      $vVehicleTypeLogo = get_value('vehicle_type', "vLogo", 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
      $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
      $vVehicleCategoryData = get_value('vehicle_category', 'iParentId,ePriceType,vLogo,vCategory_' . $userlangcode . ' as vCategory', 'iVehicleCategoryId', $iVehicleCategoryId);
      $vVehicleFare = get_value('vehicle_type','fFixedFare', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
      $iParentId = $vVehicleCategoryData[0]['iParentId']; */
    $vVehicleType = $tripData[0]['vVehicleType'];
    $vVehicleTypeLogo = $tripData[0]['vLogo'];
    $iVehicleCategoryId = $tripData[0]['iVehicleCategoryId'];
    $vVehicleCategoryData[0]['vLogo'] = $tripData[0]['vLogoVehicleCategory'];
    $vVehicleCategoryData[0]['vCategory'] = $tripData[0]['vCategory'];
    $vVehicleFare = $tripData[0]['fFixedFare'];
    $iParentId = $tripData[0]['iParentId'];
    if ($iParentId == 0) {
        $ePriceType = $tripData[0]['ePriceType'];
    } else {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    //$eIconType = get_value('vehicle_type', "eIconType", 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
    $eIconType = $tripData[0]['eIconType'];

    $TripTime = date('h:iA', strtotime($tripData[0]['tTripRequestDate']));
    $tTripRequestDateOrig = $tripData[0]['tTripRequestDate'];

    // Convert Into Timezone
    // $tripTimeZone = $tripData[0]['vTimeZone'];
    // if($tripTimeZone != ""){
    // $serverTimeZone = date_default_timezone_get();
    // $tTripRequestDateOrig = converToTz($tTripRequestDateOrig,$tripTimeZone,$serverTimeZone);
    // }
    // Convert Into Timezone
    $tTripRequestDate = date('dS M Y \a\t h:i a', strtotime($tripData[0]['tTripRequestDate']));
    $tStartDate = $tripData[0]['tStartDate'];
    $tEndDate = $tripData[0]['tEndDate'];
    $totalTime = 0;
    if ($tStartDate != '' && $tStartDate != '0000-00-00 00:00:00' && $tEndDate != '' && $tEndDate != '0000-00-00 00:00:00') {
        if ($tripData[0]['eFareType'] == "Hourly") {
            // $hours 		=	0; 
            // $minutes 	=	0;
            $totalSec = 0;
            $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$iTripId'";
            $db_tripTimes = $obj->MySQLSelect($sql22);

            foreach ($db_tripTimes as $dtT) {
                if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                    $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
                }
            }

            $years = floor($totalSec / (365 * 60 * 60 * 24));
            $months = floor(($totalSec - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
            $hours = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
            $minuts = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
            $seconds = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minuts * 60));

            if ($days > 0) {
                $hours = ($days * 24) + $hours;
            }
            if ($hours > 0) {
                $totalTime = $hours . ':' . $minuts . ':' . $seconds;
            } else if ($minuts > 0) {
                $totalTime = $minuts . ':' . $seconds . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
            }
            if ($totalTime < 1) {
                $totalTime = $seconds . " " . $languageLabelsArr['LBL_SECONDS_TXT'];
            }
        } else {
            $days = dateDifference($tStartDate, $tEndDate, '%a');
            $hours = dateDifference($tStartDate, $tEndDate, '%h');
            $minutes = dateDifference($tStartDate, $tEndDate, '%i');
            $seconds = dateDifference($tStartDate, $tEndDate, '%s');
            $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
            $LBL_MINUTES_TXT = ($minutes > 1) ? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
            $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);
            if ($days > 0) {
                $hours = ($days * 24) + $hours;
            }
            if ($hours > 0) {
                //$totalTime = $hours * 60;
                //$totalTime = $hours.':'.$minutes.':'.$seconds." " .$languageLabelsArr['LBL_HOUR'] ;
                $totalTime = $hours . ':' . $minutes . ':' . $seconds . " " . $LBL_HOURS_TXT;
            } else if ($minutes > 0) {
                //$totalTime = $totalTime + $minutes;
                //$totalTime = $minutes.':'.$seconds. " " . $languageLabelsArr['LBL_MINUTES_TXT'];
                $totalTime = $minutes . ':' . $seconds . " " . $LBL_MINUTES_TXT;
            }
            //$totalTime = $totalTime . ":" . $seconds . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
            if ($totalTime < 1) {
                $totalTime = $seconds . " " . $languageLabelsArr['LBL_SECONDS_TXT'];
            }
        }
    }

    if ($totalTime == 0) {
        $totalTime = "0.00 " . $languageLabelsArr['LBL_MINUTE'];
    }

    $returnArr['carTypeName'] = $vVehicleType;
    $returnArr['carImageLogo'] = $vVehicleTypeLogo;
    if ($eUserType == "Passenger") {
        $TripRating = get_value('ratings_user_driver', 'vRating1', 'iTripId', $iTripId, ' AND eUserType="Driver"', 'true');
        $returnArr['vDriverImage'] = get_value('register_driver', 'vImage', 'iTripId', $tripData[0]['iDriverId'], '', 'true');
        //$driverDetailArr = get_value('register_driver', '*', 'iDriverId', $tripData[0]['iDriverId']);
        $eUnit = $tripData[0]['vCountryUnitRider'];
    } else {
        $TripRating = get_value('ratings_user_driver', 'vRating1', 'iTripId', $iTripId, ' AND eUserType="Passenger"', 'true');
        //$passgengerDetailArr = get_value('register_user', '*', 'iUserId', $tripData[0]['iUserId']);
        $eUnit = $tripData[0]['vCountryUnitDriver'];
        //$eUnit = $tripData[0]['vCountryUnitRider'];
    }

    if ($eUnit == "Miles") {
        $DisplayDistanceTxt = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
    } else {
        $DisplayDistanceTxt = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
    }

    if ($TripRating == "" || $TripRating == NULL) {
        $TripRating = "0";
    }

    $iFare = $tripData[0]['iFare'];
    //$iFare = $tripData[0]['iFare']+$tripData[0]['fTollPrice'];
    $fPricePerKM = $tripData[0]['fPricePerKM'] * $priceRatio;
    $iBaseFare = $tripData[0]['iBaseFare'] * $priceRatio;
    $fPricePerMin = $tripData[0]['fPricePerMin'] * $priceRatio;
    $fCommision = $tripData[0]['fCommision'];
    $fDistance = $tripData[0]['fDistance'];
    if ($eUnit == "Miles") {
        $fDistance = round($fDistance * 0.621371, 2);
    }
    $vDiscount = $tripData[0]['vDiscount']; // 50 $
    $fDiscount = $tripData[0]['fDiscount']; // 50
    $fMinFareDiff = $tripData[0]['fMinFareDiff'] * $priceRatio;
    $fWalletDebit = $tripData[0]['fWalletDebit'];
    $fSurgePriceDiff = $tripData[0]['fSurgePriceDiff'] * $priceRatio;
    $fTripGenerateFare = $tripData[0]['fTripGenerateFare'] * $priceRatio;
    $fPickUpPrice = $tripData[0]['fPickUpPrice'];
    $fNightPrice = $tripData[0]['fNightPrice'];
    $eFlatTrip = $tripData[0]['eFlatTrip'];
    $fFlatTripPrice = $tripData[0]['fFlatTripPrice'] * $priceRatio;
    $fTipPrice = $tripData[0]['fTipPrice'] * $priceRatio;
    $fVisitFee = $tripData[0]['fVisitFee'] * $priceRatio;
    $fMaterialFee = $tripData[0]['fMaterialFee'] * $priceRatio;
    $fMiscFee = $tripData[0]['fMiscFee'] * $priceRatio;
    $fDriverDiscount = $tripData[0]['fDriverDiscount'] * $priceRatio;
    $vVehicleFare = $vVehicleFare * $priceRatio;
    $fCancelPrice = $tripData[0]['fCancellationFare'] * $priceRatio;
    $fTollPrice = $tripData[0]['fTollPrice'] * $priceRatio;
    $fTax1 = $tripData[0]['fTax1'] * $priceRatio;
    $fTax2 = $tripData[0]['fTax2'] * $priceRatio;
    if ($fTollPrice > 0) {
        $eTollSkipped = $tripData[0]['eTollSkipped'];
    } else {
        $eTollSkipped = "Yes";
    }
    $tUserComment = $tripData[0]['tUserComment'];

    $returnArr['tUserComment'] = $tUserComment;
    $returnArr['vVehicleType'] = $vVehicleType;
    $returnArr['eIconType'] = $eIconType;
    $returnArr['vVehicleCategory'] = $vVehicleCategoryData[0]['vCategory'];
    $returnArr['TripTime'] = $TripTime;
    $returnArr['ConvertedTripRequestDate'] = $tTripRequestDate;
    $returnArr['FormattedTripDate'] = $tTripRequestDate;
    $returnArr['tTripRequestDateOrig'] = $tTripRequestDateOrig;
    $returnArr['tTripRequestDate'] = $tTripRequestDate;
    $returnArr['TripTimeInMinutes'] = $totalTime;
    $returnArr['TripRating'] = $TripRating;
    $returnArr['CurrencySymbol'] = $currencySymbol;
    $returnArr['TripFare'] = formatNum($iFare * $priceRatio);
    $returnArr['iTripId'] = $tripData[0]['iTripId'];
    $returnArr['vTripPaymentMode'] = $tripData[0]['vTripPaymentMode'];
    $returnArr['eType'] = $tripData[0]['eType'];
    if ($tripData[0]['eType'] == "UberX" && $tripData[0]['eFareType'] != "Regular") {
        $returnArr['tDaddress'] = "";
    }
    if ($tripData[0]['vBeforeImage'] != "") {
        $returnArr['vBeforeImage'] = $tconfig['tsite_upload_trip_images'] . $tripData[0]['vBeforeImage'];
    }
    if ($tripData[0]['eType'] == "UberX") {
        $returnArr['vLogoVehicleCategoryPath'] = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $iVehicleCategoryId . "/";
        $returnArr['vLogoVehicleCategory'] = $vVehicleCategoryData[0]['vLogo'];
    } else {
        $returnArr['vLogoVehicleCategory'] = "";
        $returnArr['vLogoVehicleCategoryPath'] = "";
    }
    if ($tripData[0]['vAfterImage'] != "") {
        $returnArr['vAfterImage'] = $tconfig['tsite_upload_trip_images'] . $tripData[0]['vAfterImage'];
    }
    $originalFare = $iFare;
    if ($eUserType == "Passenger") {
        $iFare = $iFare;
    } else {
        //$iFare = $tripData[0]['fTripGenerateFare'] - $fCommision;
        //$iFare = $tripData[0]['fTripGenerateFare'] + $tripData[0]['fTipPrice'] - $fCommision;
        // $iFare = $tripData[0]['fTripGenerateFare'] + $tripData[0]['fTipPrice'] - $tripData[0]['fTollPrice'] - $fCommision;
        $iFare = $tripData[0]['fTripGenerateFare'] + $tripData[0]['fTipPrice'] - $fCommision - $tripData[0]['fTax1'] - $tripData[0]['fTax2'];
    }
    $surgePrice = 1;
    if ($tripData[0]['fPickUpPrice'] > 1) {
        $surgePrice = $tripData[0]['fPickUpPrice'];
    } else {
        $surgePrice = $tripData[0]['fNightPrice'];
    }
    $SurgePriceFactor = strval($surgePrice);

    $returnArr['TripFareOfMinutes'] = formatNum($tripData[0]['fPricePerMin'] * $priceRatio);
    $returnArr['TripFareOfDistance'] = formatNum($tripData[0]['fPricePerKM'] * $priceRatio);
    $returnArr['iFare'] = formatNum($iFare * $priceRatio);
    $returnArr['iOriginalFare'] = formatNum($originalFare * $priceRatio);
    $returnArr['TotalFare'] = formatNum($iFare * $priceRatio);
    $returnArr['fPricePerKM'] = formatNum($fPricePerKM);
    $returnArr['iBaseFare'] = formatNum($iBaseFare);
    $returnArr['fPricePerMin'] = formatNum($fPricePerMin);
    $returnArr['fCommision'] = formatNum($fCommision * $priceRatio);
    $returnArr['fDistance'] = formatNum($fDistance);
    $returnArr['fDiscount'] = formatNum($fDiscount * $priceRatio);
    $returnArr['fMinFareDiff'] = formatNum($fMinFareDiff);
    $returnArr['fWalletDebit'] = formatNum($fWalletDebit * $priceRatio);
    $returnArr['fSurgePriceDiff'] = formatNum($fSurgePriceDiff);
    $returnArr['fTripGenerateFare'] = formatNum($fTripGenerateFare);
    $returnArr['fFlatTripPrice'] = formatNum($fFlatTripPrice);
    if ($eTollSkipped == "No") {
        $returnArr['fTollPrice'] = formatNum($fTollPrice);
    }
    if ($fTipPrice > 0) {
        $returnArr['fTipPrice'] = $currencySymbol . formatNum($fTipPrice);
    }
    $returnArr['SurgePriceFactor'] = $SurgePriceFactor;
    $returnArr['fVisitFee'] = formatNum($fVisitFee);
    $returnArr['fMaterialFee'] = formatNum($fMaterialFee);
    $returnArr['fMiscFee'] = formatNum($fMiscFee);
    $returnArr['fDriverDiscount'] = formatNum($fDriverDiscount);
    $returnArr['fCancelPrice'] = formatNum($fCancelPrice);
    $returnArr['fTax1'] = formatNum($fTax1);
    $returnArr['fTax2'] = formatNum($fTax2);
    // echo "<pre>"; print_r($tripData); die;

    $iDriverId = $tripData[0]['iDriverId'];
    $driverDetails = get_value('register_driver', '*', 'iDriverId', $iDriverId);
    $driverDetails[0]['vImage'] = ($driverDetails[0]['vImage'] != "" && $driverDetails[0]['vImage'] != "NONE") ? "3_" . $driverDetails[0]['vImage'] : "";
    $driverDetails[0]['vPhone'] = '+' . $driverDetails[0]['vCode'] . $driverDetails[0]['vPhone'];
    $returnArr['DriverDetails'] = $driverDetails[0];

    $iUserId = $tripData[0]['iUserId'];
    $passengerDetails = get_value('register_user', '*', 'iUserId', $iUserId);
    $passengerDetails[0]['vImgName'] = ($passengerDetails[0]['vImgName'] != "" && $passengerDetails[0]['vImgName'] != "NONE") ? "3_" . $passengerDetails[0]['vImgName'] : "";
    $passengerDetails[0]['vPhone'] = '+' . $passengerDetails[0]['vPhoneCode'] . $passengerDetails[0]['vPhone'];
    $returnArr['PassengerDetails'] = $passengerDetails[0];
    $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    $fUserCountryTax1 = $TaxArr['fTax1'];
    $fUserCountryTax2 = $TaxArr['fTax2'];

    $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
    $sql = "SELECT make.vMake, model.vTitle, dv.*  FROM `driver_vehicle` dv, make, model WHERE dv.iDriverVehicleId='" . $iDriverVehicleId . "' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId`";
    $vehicleDetailsArr = $obj->MySQLSelect($sql);
    $vehicleDetailsArr[0]['vModel'] = $vehicleDetailsArr[0]['vTitle'];
    //if ($eUserType == "Passenger" && $tripData[0]['eType'] == "UberX") {
    if ($tripData[0]['eType'] == "UberX") {

        //$ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations", "ALLOW_SERVICE_PROVIDER_AMOUNT");
        $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";


        $fAmount = "0";
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {


            $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $tripData[0]['iVehicleTypeId'] . "'";
            $serviceProData = $obj->MySQLSelect($sqlServicePro);

            $vehicleTypeData = get_value('vehicle_type', 'eFareType,fPricePerHour,fFixedFare', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId']);
            if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                $fAmount = $currencySymbol . $vehicleTypeData[0]['fFixedFare'];
            } else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                $fAmount = $currencySymbol . $vehicleTypeData[0]['fPricePerHour'] . "/hour";
            }

            if (count($serviceProData) > 0) {
                $fAmount = $serviceProData[0]['fAmount'];
                $vVehicleFare = $fAmount * $priceRatio;
                $vVehicleFare = formatNum($vVehicleFare);
                if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                    $fAmount = $currencySymbol . $fAmount;
                } else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                    $fAmount = $currencySymbol . $fAmount . "/hour";
                }
            }

            $vehicleDetailsArr[0]['fAmount'] = strval($fAmount);
        }
    }
    $returnArr['DriverCarDetails'] = $vehicleDetailsArr[0];

    if ($eUserType == "Passenger") {
        $tripFareDetailsArr = array();
        if ($eFlatTrip == "Yes" && $iActive != "Canceled") {
            $i = 0;
            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_FLAT_TRIP_FARE_TXT']] = $currencySymbol . " " . $returnArr['fFlatTripPrice'];
            if ($fSurgePriceDiff > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }
            if ($fDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                $i++;
            }
            if ($fWalletDebit > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                $i++;
            }
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare'] : "--";
        } elseif ($eFlatTrip == "Yes" && $iActive == "Canceled") {
            $tripFareDetailsArr[0][$languageLabelsArr['LBL_Total_Fare']] = $currencySymbol . " 0.00";
        } elseif ($fCancelPrice > 0) {
            $tripFareDetailsArr[0][$languageLabelsArr['LBL_CANCELLATION_FEE']] = $currencySymbol . $returnArr['fCancelPrice'];
            $tripFareDetailsArr[1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $currencySymbol . $returnArr['fCancelPrice'];
        } else {
            $i = 0;
            $countUfx = 0;
            if ($tripData[0]['eType'] == "UberX") {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $returnArr['vVehicleCategory'] . "-" . $returnArr['vVehicleType'];
                $countUfx = 1;
            }

            if ($tripData[0]['eFareType'] == "Regular") {
                //$tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vVehicleType . " " . $currencySymbol . $returnArr['iBaseFare'];
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iBaseFare'] : "--";
                if ($countUfx == 1) {
                    $i++;
                }
                //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $languageLabelsArr['LBL_KM_DISTANCE_TXT'] . ")"] = ($iActive != "Canceled")?$currencySymbol . $returnArr['TripFareOfDistance']:"--";
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $DisplayDistanceTxt . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                $i++;
            } else if ($tripData[0]['eFareType'] == "Fixed") {
                //  $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = $currencySymbol . ($fTripGenerateFare - $fSurgePriceDiff - $fMinFareDiff);
                $SERVICE_COST = ($tripData[0]['iQty'] > 1) ? $tripData[0]['iQty'] . ' X ' . $currencySymbol . $vVehicleFare : $currencySymbol . $vVehicleFare;
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $SERVICE_COST : "--";
                if ($countUfx == 1) {
                    $i++;
                }
            } else if ($tripData[0]['eFareType'] == "Hourly") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";

                if ($countUfx == 1) {
                    $i++;
                }
            }

            if ($fVisitFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_VISIT_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fVisitFee'] : "--";
                $i++;
            }
            if ($fMaterialFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MATERIAL_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMaterialFee'] : "--";
                $i++;
            }
            if ($fMiscFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MISC_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMiscFee'] : "--";
                $i++;
            }
            if ($fDriverDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDriverDiscount'] : "--";
                $i++;
            }


            // print_r($tripFareDetailsArr);exit;
            // echo $tripData[0]['eFareType'];exit;
            if ($fSurgePriceDiff > 0) {
                $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff;
                if ($eTollSkipped == "No") {
                    $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fTollPrice;
                }
                $normalfare = formatNum($normalfare);
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }
            if ($fMinFareDiff > 0) {
                //$minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
                $minimamfare = $fTripGenerateFare;
                if ($eTollSkipped == "No") {
                    $minimamfare = $fTripGenerateFare - $fTollPrice;
                }
                $minimamfare = formatNum($minimamfare);
                $tripFareDetailsArr[$i + 1][$currencySymbol . $minimamfare . " " . $languageLabelsArr['LBL_MINIMUM']] = $currencySymbol . $returnArr['fMinFareDiff'];
                $returnArr['TotalMinFare'] = ($iActive != "Canceled") ? $minimamfare : "--";
                $i++;
            }
            if ($eTollSkipped == "No") {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TOLL_PRICE_TOTAL']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTollPrice'] : "--";
                $i++;
            }


            if ($fDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                $i++;
            }
            if ($fWalletDebit > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                $i++;
            }

            /* if ($fTipPrice > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIP_AMOUNT']] = ($iActive != "Canceled")?$currencySymbol . $returnArr['fTipPrice']:"--";
              $i++;
              } */
            if ($fTax1 > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax1'] : "--";
                $i++;
            }
            if ($fTax2 > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax2'] : "--";
                $i++;
            }

            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare'] : "--";
        }
        $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";
        $returnArr['FareDetailsNewArr'] = $tripFareDetailsArr;
        $FareDetailsArr = array();
        foreach ($tripFareDetailsArr as $data) {
            $FareDetailsArr = array_merge($FareDetailsArr, $data);
        }
        $returnArr['FareDetailsArr'] = $FareDetailsArr;
        $returnArr['HistoryFareDetailsNewArr'] = $tripFareDetailsArr;
        if ($tripData[0]['eType'] == "UberX") {
            if ($fCancelPrice == 0) {
                array_splice($returnArr['HistoryFareDetailsNewArr'], 0, 1);
            }
            if ($PAGE_MODE == "DISPLAY") {
                array_splice($returnArr['FareDetailsNewArr'], 0, 1);
            }
        }
    } else {
        $tripFareDetailsArr = array();
        if ($eFlatTrip == "Yes") {
            $i = 0;
            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_FLAT_TRIP_FARE_TXT']] = $currencySymbol . " " . $returnArr['fFlatTripPrice'];
            if ($fSurgePriceDiff > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }
            if ($PAGE_MODE == "DISPLAY") {
                if ($fDiscount > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                    $i++;
                }
                if ($fWalletDebit > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                    $i++;
                }
            }
        } else {
            $i = 0;
            $countUfx = 0;
            if ($tripData[0]['eType'] == "UberX" && $PAGE_MODE == "HISTORY") {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $returnArr['vVehicleCategory'] . "-" . $returnArr['vVehicleType'];
                $countUfx = 1;
            }

            if ($tripData[0]['eFareType'] == "Regular") {
                //$tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vVehicleType . " " . $currencySymbol . $returnArr['iBaseFare'];
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iBaseFare'] : "--";
                if ($countUfx == 1) {
                    $i++;
                }
                //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $languageLabelsArr['LBL_KM_DISTANCE_TXT'] . ")"] = ($iActive != "Canceled")?$currencySymbol . $returnArr['TripFareOfDistance']:"--";
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $DisplayDistanceTxt . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                $i++;
            } else if ($tripData[0]['eFareType'] == "Fixed") {
                //$tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = $currencySymbol . ($fTripGenerateFare - $fSurgePriceDiff - $fMinFareDiff);
                $SERVICE_COST = ($tripData[0]['iQty'] > 1) ? $tripData[0]['iQty'] . ' X ' . $currencySymbol . $vVehicleFare : $currencySymbol . $vVehicleFare;
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $SERVICE_COST : "--";
                if ($countUfx == 1) {
                    $i++;
                }
            } else if ($tripData[0]['eFareType'] == "Hourly") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";

                if ($countUfx == 1) {
                    $i++;
                }
            }

            if ($fVisitFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_VISIT_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fVisitFee'] : "--";
                $i++;
            }
            if ($fMaterialFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MATERIAL_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMaterialFee'] : "--";
                $i++;
            }
            if ($fMiscFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MISC_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMiscFee'] : "--";
                $i++;
            }
            if ($fDriverDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDriverDiscount'] : "--";
                $i++;
            }

            if ($fSurgePriceDiff > 0) {
                $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff;
                if ($eTollSkipped == "No") {
                    $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fTollPrice;
                }
                $normalfare = formatNum($normalfare);
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }
            if ($fMinFareDiff > 0) {
                //$minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
                $minimamfare = $fTripGenerateFare;
                if ($eTollSkipped == "No") {
                    $minimamfare = $fTripGenerateFare - $fTollPrice;
                }
                $minimamfare = formatNum($minimamfare);
                $tripFareDetailsArr[$i + 1][$currencySymbol . $minimamfare . " " . $languageLabelsArr['LBL_MINIMUM']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMinFareDiff'] : "--";
                $returnArr['TotalMinFare'] = $minimamfare;
                $i++;
            }
            if ($eTollSkipped == "No") {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TOLL_PRICE_TOTAL']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTollPrice'] : "--";
                $i++;
            }

            if ($PAGE_MODE == "DISPLAY") {
                if ($fDiscount > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                    $i++;
                }
                if ($fWalletDebit > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                    $i++;
                }
                if ($fTax1 > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax1'] : "--";
                    $i++;
                }
                if ($fTax2 > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax2'] : "--";
                    $i++;
                }
            } else {
                if ($fTax1 > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? "-" . $currencySymbol . $returnArr['fTax1'] : "--";
                    $i++;
                }
                if ($fTax2 > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? "-" . $currencySymbol . $returnArr['fTax2'] : "--";
                    $i++;
                }
            }
            /* if ($fDiscount > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled")?"- " . $currencySymbol . $returnArr['fDiscount']:"--";
              $i++;
              }
              if ($fWalletDebit > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled")?"- " . $currencySymbol . $returnArr['fWalletDebit']:"--";
              $i++;
              } */

            /* if ($fTipPrice > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIP_AMOUNT']] = ($iActive != "Canceled")?$currencySymbol . $returnArr['fTipPrice']:"--";
              $i++;
              } */
        }
        $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";
        $returnArr['FareDetailsNewArr'] = $tripFareDetailsArr;
        $FareDetailsArr = array();
        foreach ($tripFareDetailsArr as $data) {
            $FareDetailsArr = array_merge($FareDetailsArr, $data);
        }
        $returnArr['FareDetailsArr'] = $FareDetailsArr;
        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_Commision']] = ($iActive != "Canceled") ? "-" . $currencySymbol . $returnArr['fCommision'] : "--";
        $i++;
        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_EARNED_AMOUNT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare'] : "--";
        $returnArr['HistoryFareDetailsNewArr'] = $tripFareDetailsArr;

        if ($tripData[0]['eType'] == "UberX") {
            array_splice($returnArr['HistoryFareDetailsNewArr'], 0, 1);
        }
    }
    $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";
    //passengertripfaredetails

    $HistoryFareDetailsArr = array();
    foreach ($tripFareDetailsArr as $inner) {
        $HistoryFareDetailsArr = array_merge($HistoryFareDetailsArr, $inner);
    }
    $returnArr['HistoryFareDetailsArr'] = $HistoryFareDetailsArr;


    //drivertripfarehistorydetails
    //echo "<pre>";print_r($returnArr);echo "<pre>";print_r($tripData);exit;
    return $returnArr;
}

function formatNum($number) {
    return strval(number_format($number, 2));
}

function getUserRatingAverage($iMemberId, $eUserType = "Passenger") {
    global $obj, $generalobj;
    if ($eUserType == "Passenger") {
        $iUserId = "iDriverId";
        $checkusertype = "Passenger";
    } else if ($eUserType == "Company") {
        $iUserId = "iCompanyId";
        $checkusertype = "Company";
    } else {
        $iUserId = "iUserId";
        $checkusertype = "Driver";
    }

    $usertotaltrips = get_value("orders", "iOrderId", $iUserId, $iMemberId);
    if (count($usertotaltrips) > 0) {
        for ($i = 0; $i < count($usertotaltrips); $i++) {
            $iOrderId .= $usertotaltrips[$i]['iOrderId'] . ",";
        }

        $iOrderId_str = substr($iOrderId, 0, -1);
        //echo  $iTripId_str;exit;
        $sql = "SELECT count(iRatingId) as ToTalTrips, SUM(vRating1) as ToTalRatings from ratings_user_driver WHERE iOrderId IN (" . $iOrderId_str . ") AND eToUserType = '" . $checkusertype . "' AND vRating1 > 0;";
        $result_ratings = $obj->MySQLSelect($sql);
        $ToTalTrips = $result_ratings[0]['ToTalTrips'];
        $ToTalRatings = $result_ratings[0]['ToTalRatings'];
        //$average_rating = round($ToTalRatings / $ToTalTrips, 2);
        $average_rating = round($ToTalRatings / $ToTalTrips, 1);
    } else {
        $average_rating = 0;
    }
    return $average_rating;
}

function deliverySmsToReceiver($iTripId) {
    global $obj, $generalobj, $tconfig;

    $sql = "SELECT * from trips WHERE iTripId = '" . $iTripId . "'";
    $tripData = $obj->MySQLSelect($sql);

    $SenderName = get_value("register_user", "vName,vLastName", "iUserId", $tripData[0]['iUserId']);
    $SenderName = $SenderName[0]['vName'] . " " . $SenderName[0]['vLastName'];
    $delivery_address = $tripData[0]['tDaddress'];
    $vDeliveryConfirmCode = $tripData[0]['vDeliveryConfirmCode'];
    $page_link = $tconfig['tsite_url'] . "trip_tracking.php?iTripId=" . $iTripId;
    $page_link = get_tiny_url($page_link);

    $message_deliver = $SenderName . " has send you the parcel on below address." . $delivery_address . ". Upon Receiving the parcel, please provide below verification code to Delivery Driver. Verification Code: " . $vDeliveryConfirmCode . ". click on link below to track your parcel. " . $page_link;

    //echo $message_deliver;exit;
    return $message_deliver;
}

function get_tiny_url($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function addToUserRequest($iUserId, $iDriverId, $message, $iMsgCode) {
    global $obj;
    $data['iUserId'] = $iUserId;
    $data['iDriverId'] = $iDriverId;
    $data['tMessage'] = $message;
    $data['iMsgCode'] = $iMsgCode;
    $data['dAddedDate'] = @date("Y-m-d H:i:s");

    $dataId = $obj->MySQLQueryPerform("passenger_requests", $data, 'insert');

    return $dataId;
}

function addToDriverRequest($iDriverId, $iUserId, $iTripId, $eStatus) {
    global $obj;
    $data['iDriverId'] = $iDriverId;
    $data['iUserId'] = $iUserId;
    $data['iTripId'] = $iTripId;
    $data['eStatus'] = $eStatus;
    $data['tDate'] = @date("Y-m-d H:i:s");
    $data['dAddedDate'] = @date("Y-m-d H:i:s");

    $id = $obj->MySQLQueryPerform("driver_request", $data, 'insert');

    return $id;
}

function addToUserRequest2($data) {
    global $obj;
    $dataId = $obj->MySQLQueryPerform("passenger_requests", $data, 'insert');
    return $dataId;
}

function addToDriverRequest2($data) {
    global $obj;
    $data['dAddedDate'] = @date("Y-m-d H:i:s");
    $id = $obj->MySQLQueryPerform("driver_request", $data, 'insert');
    return $id;
}

function addToCompanyRequest2($data) {
    global $obj;
    $data['dAddedDate'] = @date("Y-m-d H:i:s");
    $id = $obj->MySQLQueryPerform("company_request", $data, 'insert');
    return $id;
}

function UpdateDriverRequest($iDriverId, $iUserId, $iTripId, $eStatus) {
    global $obj;

    $sql = "SELECT * FROM `driver_request` WHERE iDriverId = '" . $iDriverId . "' AND iUserId = '" . $iUserId . "' AND iTripId = '0' ORDER BY iDriverRequestId DESC LIMIT 0,1";
    $db_sql = $obj->MySQLSelect($sql);
    $request_count = count($db_sql);

    if ($request_count > 0) {
        $where = " iDriverRequestId = '" . $db_sql[0]['iDriverRequestId'] . "'";
        $Data_Update['eStatus'] = $eStatus;
        $Data_Update['tDate'] = @date("Y-m-d H:i:s");
        $Data_Update['iTripId'] = $iTripId;
        $id = $obj->MySQLQueryPerform("driver_request", $Data_Update, 'update', $where);
    }

    return $request_count;
}

function UpdateDriverRequest2($iDriverId, $iUserId, $iTripId, $eStatus = "", $vMsgCode, $eAcceptAttempted = "No", $iOrderId) {
    global $obj;
    //$sql = "SELECT * FROM `driver_request` WHERE iDriverId = '" . $iDriverId . "' AND iUserId = '" . $iUserId . "' AND iTripId = '0' AND vMsgCode='".$vMsgCode."'";
    $sql = "SELECT * FROM `driver_request` WHERE iDriverId = '" . $iDriverId . "' AND iOrderId = '" . $iOrderId . "' AND iTripId = '0' AND vMsgCode='" . $vMsgCode . "'";
    $db_sql = $obj->MySQLSelect($sql);
    $request_count = count($db_sql);

    if ($request_count > 0) {
        $where = " iDriverRequestId = '" . $db_sql[0]['iDriverRequestId'] . "'";
        if ($eStatus != "") {
            $Data_Update['eStatus'] = $eStatus;
        }
        $Data_Update['tDate'] = @date("Y-m-d H:i:s");
        $Data_Update['iTripId'] = $iTripId;
        $Data_Update['eAcceptAttempted'] = $eAcceptAttempted;
        $id = $obj->MySQLQueryPerform("driver_request", $Data_Update, 'update', $where);
    }
    return $request_count;
}

function getDriverStatus($driverId = '') {
    global $generalobj, $obj;

    $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $driverId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    //$userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];

    $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $driverId . "' ) dl on dl.doc_masterid=dm.doc_masterid  
		where dm.doc_usertype='driver' and dm.status='Active' ";
    $db_document = $obj->MySQLSelect($sql1);
    if (count($db_document) > 0) {
        for ($i = 0; $i < count($db_document); $i++) {
            if ($db_document[$i]['doc_file'] == "") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "Please upload your " . $db_document[$i]['doc_name'];
                echo json_encode($returnArr);
                exit;
            }
            if ($db_document[$i]['status'] != "Active") {
                $returnArr['Action'] = "0";
                if ($db_document[$i]['status'] == "Inactive") {
                    $returnArr['message'] = "Please activate your " . $db_document[$i]['doc_name'];
                    echo json_encode($returnArr);
                    exit;
                }
                if ($db_document[$i]['status'] == "Deleted") {
                    $returnArr['message'] = "Current status is deleted of your" . $db_document[$i]['doc_name'];
                    echo json_encode($returnArr);
                    exit;
                }
            }
        }
    }

    $sql = "SELECT iDriverVehicleId from driver_vehicle WHERE iDriverId = '" . $driverId . "'";
    $db_drv_vehicle = $obj->MySQLSelect($sql);
    if (count($db_drv_vehicle) == 0) {
        $returnArr['Action'] = "0";  # Check For Driver's vehicle added or not #
        $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
        echo json_encode($returnArr);
        exit;
    } else {
        $DriverSelectedVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $driverId, '', 'true');
        if ($DriverSelectedVehicleId == 0) {
            $returnArr['Action'] = "0"; # Check Driver has selected  vehicle or not if #
            $returnArr['message'] = "LBL_SELECT_CAR_MESSAGE_TXT";
            echo json_encode($returnArr);
            exit;
        } else {
            # Check For Driver's selected vehicle's document are upload or not #
            $sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $DriverSelectedVehicleId . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='car' and dm.status='Active'";
            $db_selected_vehicle = $obj->MySQLSelect($sql);
            if (count($db_selected_vehicle) > 0) {
                for ($i = 0; $i < count($db_selected_vehicle); $i++) {
                    if ($db_selected_vehicle[$i]['doc_file'] == "") {
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = "Please upload your " . $db_selected_vehicle[$i]['doc_name'];
                        echo json_encode($returnArr);
                        exit;
                    }
                }
            }
            # Check For Driver's selected vehicle's document are upload or not #
            # Check For Driver's selected vehicle status #
            $DriverSelectedVehicleStatus = get_value('driver_vehicle', 'eStatus', 'iDriverVehicleId', $DriverSelectedVehicleId, '', 'true');
            if ($DriverSelectedVehicleStatus == "Inactive" || $DriverSelectedVehicleStatus == "Deleted") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_SELECTED_VEHICLE_NOT_ACTIVE";
                echo json_encode($returnArr);
                exit;
            }
            # Check For Driver's selected vehicle status #
        }
    }

    $sql = "SELECT rd.eStatus as driverstatus,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE rd.iDriverId='" . $driverId . "' AND cmp.iCompanyId=rd.iCompanyId";
    $Data = $obj->MySQLSelect($sql);

    if ($Data[0]['driverstatus'] != "active" || $Data[0]['cmpEStatus'] != "Active") {

        $returnArr['Action'] = "0";

        if ($Data[0]['cmpEStatus'] != "Active") {
            $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_COMPANY";
        } else if ($Data[0]['driverstatus'] == "Deleted") {
            $returnArr['message'] = "LBL_ACC_DELETE_TXT";
        } else {
            $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_DRIVER";
        }

        echo json_encode($returnArr);
        exit;
    }
}

function fetch_address_geocode($address, $geoCodeResult = "") {
    global $generalobj, $GOOGLE_SEVER_API_KEY_WEB;
    $address = str_replace(" ", "+", "$address");
    //$GOOGLE_SEVER_API_KEY_WEB=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_API_KEY_WEB");
    $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&key=" . $GOOGLE_SEVER_API_KEY_WEB;
    //$url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false";

    if ($geoCodeResult == "") {
        $result = file_get_contents("$url");
        $result = preg_replace("/[\n\r]/", "", $result);
    } else {
        $result = $geoCodeResult;
        $result = stripslashes(preg_replace("/[\n\r]/", "", $result));
    }
    //$result = stripslashes(preg_replace("/[\n\r]/", "", $result));
    $json = json_decode($result);

    $city = $state = $country = $country_code = '';

    foreach ($json->results as $result) {
        foreach ($result->address_components as $addressPart) {
            if (((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types))) || ((in_array('sublocality', $addressPart->types)) && (in_array('political', $addressPart->types)) && (in_array('sublocality_level_1', $addressPart->types)))) {
                $city = $addressPart->long_name;
            } else if ((in_array('administrative_area_level_1', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                $state = $addressPart->long_name;
            } else if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                $country = $addressPart->long_name;
                $country_code = $addressPart->short_name;
            }
        }
    }

    // if(($city != '') && ($state != '') && ($country != '')) 
    // $address = $city.', '.$state.', '.$country;
    // else if (($city != '') && ($state != ''))
    // $address = $city.', '.$state;
    // else if (($state != '') && ($country != ''))
    // $address = $state.', '.$country;
    // else if ($country != '')
    // $address = $country;

    $returnArr = array('city' => $city, 'state' => $state, 'country' => $country, 'country_code' => $country_code);


    return $returnArr;
}

function get_address_geocode($address) {
    global $generalobj, $GOOGLE_SEVER_API_KEY_WEB;
    $address = str_replace(" ", "+", "$address");
    //$GOOGLE_SEVER_API_KEY_WEB=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_API_KEY_WEB");
    $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&key=" . $GOOGLE_SEVER_API_KEY_WEB;
    $result = file_get_contents("$url");
    $result = stripslashes(preg_replace("/[\n\r]/", "", $result));
    $json = json_decode($result);
    $city = $state = $country = $country_code = '';

    foreach ($json->results as $result) {
        foreach ($result->address_components as $addressPart) {
            if (((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types))) || ((in_array('sublocality', $addressPart->types)) && (in_array('political', $addressPart->types)) && (in_array('sublocality_level_1', $addressPart->types)))) {
                $city = $addressPart->long_name;
            } else if ((in_array('administrative_area_level_1', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                $state = $addressPart->long_name;
            } else if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                $country = $addressPart->long_name;
                $country_code = $addressPart->short_name;
            }
        }
    }
    $returnArr = array('city' => $city, 'state' => $state, 'country' => $country, 'country_code' => $country_code);
    return $returnArr;
}

function UploadUserImage($iMemberId, $UserType = "Passenger", $eSignUpType, $vFbId, $vImageURL = "") {
    global $generalobj, $tconfig, $TWITTER_OAUTH_ACCESS_TOKEN, $TWITTER_OAUTH_ACCESS_TOKEN_SECRET, $TWITTER_CONSUMER_KEY, $TWITTER_CONSUMER_SECRET, $GOOGLE_SEVER_API_KEY_WEB;
    $vimage = "";
    if ($UserType == "Passenger") {
        $Photo_Gallery_folder = $tconfig["tsite_upload_images_passenger_path"] . "/" . $iMemberId . "/";
        $OldImage = get_value('register_user', 'vImgName', 'iUserId', $iMemberId, '', 'true');
    } else {
        $Photo_Gallery_folder = $tconfig["tsite_upload_images_driver_path"] . "/" . $iMemberId . "/";
        $OldImage = get_value('register_driver', 'vImage', 'iDriverId', $iMemberId, '', 'true');
    }
    unlink($Photo_Gallery_folder . $OldImage);
    unlink($Photo_Gallery_folder . "1_" . $OldImage);
    unlink($Photo_Gallery_folder . "2_" . $OldImage);
    unlink($Photo_Gallery_folder . "3_" . $OldImage);
    unlink($Photo_Gallery_folder . "4_" . $OldImage);
    if (!is_dir($Photo_Gallery_folder)) {
        mkdir($Photo_Gallery_folder, 0777);
    }
    if ($eSignUpType == "Facebook") {
        if ($vImageURL != "") {
            $vImageURL = str_replace("type=large", "width=256", $vImageURL);
            $baseurl = $vImageURL;
        } else {
            //$baseurl =  "http://graph.facebook.com/".$vFbId."/picture?type=large";
            $baseurl = "http://graph.facebook.com/" . $vFbId . "/picture?width=256";
            //$url = $vFbId."_".time().".jpg";
        }
        $url = time() . ".jpg";
        /* file_get_content */
        $profile_Image = $baseurl;
        $userImage = $url;
        $thumb_image = file_get_contents($baseurl);
        $thumb_file = $Photo_Gallery_folder . $url;
        $image_name = file_put_contents($thumb_file, $thumb_image);
        /* file_get_content  ends */
        if (is_file($Photo_Gallery_folder . $url)) {
            $imgname = $generalobj->img_data_upload($Photo_Gallery_folder, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
            $vimage = $imgname;
        }
    }
    if ($eSignUpType == "Google") {
        if ($vImageURL != "") {
            $baseurl = $vImageURL;
            $url = time() . ".jpg";
        } else {
            //$GOOGLE_SEVER_API_KEY_WEB = $generalobj->getConfigurations("configurations", "GOOGLE_SEVER_API_KEY_WEB");
            //$baseurl1 =  "https://www.googleapis.com/plus/v1/people/114434193354602240754?fields=image&key=AIzaSyB7_FaMl2gU1ItcomolF2S1Fzh8prnvNNw";
            $baseurl1 = "https://www.googleapis.com/plus/v1/people/" . $vFbId . "?fields=image&key=" . $GOOGLE_SEVER_API_KEY_WEB;
            //$url = $vFbId."_".time().".jpg";
            //$url = time().".jpg";
            $url = time() . ".jpg";
            try {
                $jsonfile = file_get_contents($baseurl1);
                $jsondata = json_decode($jsonfile);
                $baseurl = $jsondata->image->url;
                $baseurl = str_replace("?sz=50", "?sz=256", $baseurl);
            } catch (ErrorException $ex) {
                $imgname = "";
                $vimage = $imgname;
            }
        }
        /* file_get_content */
        $profile_Image = $baseurl;
        $userImage = $url;
        $thumb_image = file_get_contents($baseurl);
        $thumb_file = $Photo_Gallery_folder . $url;
        $image_name = file_put_contents($thumb_file, $thumb_image);
        /* file_get_content  ends */
        if (is_file($Photo_Gallery_folder . $url)) {
            $imgname = $generalobj->img_data_upload($Photo_Gallery_folder, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
            //$imgname = $generalobj->general_upload_image($url, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);
            $vimage = $imgname;
        }
    }
    if ($eSignUpType == "Twitter") {
        if ($vImageURL != "") {
            $baseurl = $vImageURL;
        } else {
            require_once('assets/libraries/twitter/TwitterAPIExchange.php');
            /* $TWITTER_OAUTH_ACCESS_TOKEN = $generalobj->getConfigurations("configurations", "TWITTER_OAUTH_ACCESS_TOKEN");  
              $TWITTER_OAUTH_ACCESS_TOKEN_SECRET = $generalobj->getConfigurations("configurations", "TWITTER_OAUTH_ACCESS_TOKEN_SECRET");
              $TWITTER_CONSUMER_KEY = $generalobj->getConfigurations("configurations", "TWITTER_CONSUMER_KEY");
              $TWITTER_CONSUMER_SECRET = $generalobj->getConfigurations("configurations", "TWITTER_CONSUMER_SECRET"); */
            $settings = array(
                'oauth_access_token' => $TWITTER_OAUTH_ACCESS_TOKEN,
                'oauth_access_token_secret' => $TWITTER_OAUTH_ACCESS_TOKEN_SECRET,
                'consumer_key' => $TWITTER_CONSUMER_KEY,
                'consumer_secret' => $TWITTER_CONSUMER_SECRET
            );
            $url = 'https://api.twitter.com/1.1/users/show.json';
            $getfield = '?user_id=' . $vFbId;
            $requestMethod = 'GET';
            $twitter = new TwitterAPIExchange($settings);
            $twitterArr = $twitter->setGetfield($getfield)
                    ->buildOauth($url, $requestMethod)
                    ->performRequest();
            $jsondata = json_decode($twitterArr); //echo "<pre>";print_r($jsondata);exit;   
            $profile_image_url = $jsondata->profile_image_url;
            $baseurl = str_replace("_normal", "", $profile_image_url);
        }
        //$url = $vFbId."_".time().".jpg";
        $url = time() . ".jpg";
        /* file_get_content */
        $profile_Image = $baseurl;
        $userImage = $url;
        $thumb_image = file_get_contents($baseurl);
        $thumb_file = $Photo_Gallery_folder . $url;
        $image_name = file_put_contents($thumb_file, $thumb_image);
        /* file_get_content  ends */
        if (is_file($Photo_Gallery_folder . $url)) {
            $imgname = $generalobj->img_data_upload($Photo_Gallery_folder, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
            $vimage = $imgname;
        }
    }
    return $vimage;
}

function getMemberCountryUnit($iMemberId, $UserType = "Passenger") {
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT;

    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $vCountryfield = "vCountry";
        $iUserId = "iUserId";
    } else {
        $tblname = "register_driver";
        $vCountryfield = "vCountry";
        $iUserId = "iDriverId";
    }
    $sql = "SELECT co.eUnit FROM country as co LEFT JOIN $tblname as rd ON co.vCountryCode = rd.$vCountryfield WHERE $iUserId = '" . $iMemberId . "'";
    $sqlcountryCode = $obj->MySQLSelect($sql);
    $vCountry = $sqlcountryCode[0]['eUnit'];
    //$vCountry = get_value($tblname, $vCountryfield, $iUserId, $iMemberId, '', 'true'); 

    if ($vCountry == "" || $vCountry == NULL) {
        $vCountryCode = $DEFAULT_DISTANCE_UNIT;
    } else {
        $vCountryCode = $vCountry;
    }
    return $vCountryCode;
}

function getVehicleCountryUnit_PricePerKm($vehicleTypeID, $fPricePerKM) {
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT;

    $iLocationid = get_value("vehicle_type", "iLocationid", "iVehicleTypeId", $vehicleTypeID, '', 'true');
    $iCountryId = get_value("location_master", "iCountryId", "iLocationId", $iLocationid, '', 'true');

    if ($iLocationid == "-1") {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    } else {
        $eUnit = get_value("country", "eUnit", "iCountryId", $iCountryId, '', 'true');
    }

    if ($eUnit == "" || $eUnit == NULL) {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    }

    if ($eUnit == "Miles") {
        $PricePerKM = $fPricePerKM * 0.621371;
    } else {
        $PricePerKM = $fPricePerKM;
    }

    return $PricePerKM;
}

function getVehiclePrice_ByUSerCountry($iUserId, $fPricePerKM) {
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT;

    $vCountry = get_value("register_user", "vCountry", "iUserId", $iUserId, '', 'true');
    if ($vCountry == "") {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    } else {
        $eUnit = get_value("country", "eUnit", "vCountryCode", $vCountry, '', 'true');
    }

    if ($eUnit == "" || $eUnit == NULL) {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    }

    if ($eUnit == "Miles") {
        $PricePerKM = $fPricePerKM * 1.60934;
    } else {
        $PricePerKM = $fPricePerKM;
    }

    return $PricePerKM;
}

function TripCollectTip($iMemberId, $iTripId, $fAmount) {
    global $generalobj, $obj;
    $tbl_name = "register_user";
    $currencycode = "vCurrencyPassenger";
    $iUserId = "iUserId";
    $eUserType = "Rider";
    if ($iMemberId == "") {
        $iMemberId = get_value('trips', 'iUserId', 'iTripId', $iTripId, '', 'true');
    }
    $vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId, '', 'true');
    $vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId, '', 'true');
    $userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId, '', 'true');
    $currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $currencyratio = get_value('currency', 'Ratio', 'vName', $userCurrencyCode, '', 'true');
    //$price = $fAmount*$currencyratio;
    $price = round($fAmount / $currencyratio);
    $price_new = $price * 100;
    $price_new = round($price_new);
    if ($vStripeCusId == "" || $vStripeToken == "") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        echo json_encode($returnArr);
        exit;
    }
    $dDate = Date('Y-m-d H:i:s');
    $eFor = 'Deposit';
    $eType = 'Credit';
    $tDescription = "#LBL_AMOUNT_DEBIT#";
    $ePaymentStatus = 'Unsettelled';
    $userAvailableBalance = $generalobj->get_user_available_balance($iMemberId, $eUserType);
    if ($userAvailableBalance > $price) {
        $where = " iTripId = '$iTripId'";
        $data['fTipPrice'] = $price;
        $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);
        $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $tripId, '', 'true');
        $data_wallet['iUserId'] = $iMemberId;
        $data_wallet['eUserType'] = "Rider";
        $data_wallet['iBalance'] = $price;
        $data_wallet['eType'] = "Debit";
        $data_wallet['dDate'] = date("Y-m-d H:i:s");
        $data_wallet['iTripId'] = $iTripId;
        $data_wallet['eFor'] = "Booking";
        $data_wallet['ePaymentStatus'] = "Unsettelled";
        $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING# " . $vRideNo;
        $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);
        //$returnArr["Action"] = "1";
        //echo json_encode($returnArr);exit;
    } else if ($price > 0.51) {
        try {
            $charge_create = Stripe_Charge::create(array(
                        "amount" => $price_new,
                        "currency" => $currencyCode,
                        "customer" => $vStripeCusId,
                        "description" => $tDescription
            ));
            $details = json_decode($charge_create);
            $result = get_object_vars($details);
            //echo "<pre>";print_r($result);exit;
            if ($result['status'] == "succeeded" && $result['paid'] == "1") {
                $where = " iTripId = '$iTripId'";
                $data['fTipPrice'] = $price;
                $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);
                //$returnArr["Action"] = "1";
                //echo json_encode($returnArr);exit;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_TRANS_FAILED";
                echo json_encode($returnArr);
                exit;
            }
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            $error3 = $e->getMessage();
            $returnArr["Action"] = "0";
            $returnArr['message'] = $error3;
            //$returnArr['message']="LBL_TRANS_FAILED";
            echo json_encode($returnArr);
            exit;
        }
    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_REQUIRED_MINIMUM_AMOUT";
        $returnArr['minValue'] = strval(round(51 * $currencyratio));
        echo json_encode($returnArr);
        exit;
    }
    return $iTripId;
}

function GenerateHailTrip($iUserId, $driverId, $selectedCarTypeID, $PickUpLatitude, $PickUpLongitude, $PickUpAddress, $DestLatitude, $DestLongitude, $DestAddress, $fTollPrice = 0, $vTollPriceCurrencyCode = "", $eTollSkipped = "No") {
    global $generalobj, $obj, $APPLY_SURGE_ON_FLAT_FARE;
    $Data['vRideNo'] = rand(10000000, 99999999);
    $Data['iVerificationCode'] = rand(1000, 9999);
    $Data['iUserId'] = $iUserId;
    $Data['iDriverId'] = $driverId;
    $Data['tTripRequestDate'] = @date("Y-m-d H:i:s");
    $Data['iVehicleTypeId'] = $selectedCarTypeID;
    $Data['iDriverVehicleId'] = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $driverId, '', 'true');
    $Data['iActive'] = 'On Going Trip';
    $Data['tStartDate'] = @date("Y-m-d H:i:s");
    $Data['tStartLat'] = $PickUpLatitude;
    $Data['tStartLong'] = $PickUpLongitude;
    $Data['tSaddress'] = $PickUpAddress;
    $Data['tEndLat'] = $DestLatitude;
    $Data['tEndLong'] = $DestLongitude;
    $Data['tDaddress'] = $DestAddress;
    $Data['eFareType'] = get_value('vehicle_type', 'eFareType', 'iVehicleTypeId', $selectedCarTypeID, '', 'true');
    $Data['fVisitFee'] = get_value('vehicle_type', 'fVisitFee', 'iVehicleTypeId', $selectedCarTypeID, '', 'true');
    $Data['vTripPaymentMode'] = "Cash";
    $Data['eType'] = "Ride";
    $Data['eHailTrip'] = "Yes";
    $Data['eFareType'] = "Regular";
    $Data['vCountryUnitRider'] = getMemberCountryUnit($iUserId, "Passenger");
    $Data['vCountryUnitDriver'] = getMemberCountryUnit($driverId, "Driver");
    $Data['fTollPrice'] = $fTollPrice;
    $Data['vTollPriceCurrencyCode'] = $vTollPriceCurrencyCode;
    $Data['eTollSkipped'] = $eTollSkipped;
    $currencyList = get_value('currency', '*', 'eStatus', 'Active');
    for ($i = 0; $i < count($currencyList); $i++) {
        $currencyCode = $currencyList[$i]['vName'];
        $Data['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
    }
    $Data['vCurrencyPassenger'] = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
    $Data['vCurrencyDriver'] = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
    $Data['fRatioPassenger'] = get_value('currency', 'Ratio', 'vName', $Data['vCurrencyPassenger'], '', 'true');
    $Data['fRatioDriver'] = get_value('currency', 'Ratio', 'vName', $Data['vCurrencyDriver'], '', 'true');

    $fPickUpPrice = 1;
    $fNightPrice = 1;
    $sourceLocationArr = array($PickUpLatitude, $PickUpLongitude);
    $destinationLocationArr = array($DestLatitude, $DestLongitude);
    $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $selectedCarTypeID);
    $data_surgePrice = checkSurgePrice($selectedCarTypeID, $Data['tStartDate']);
    if ($data_surgePrice['Action'] == "0") {
        if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
            $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
        } else {
            $fNightPrice = $data_surgePrice['SurgePriceValue'];
        }
    }
    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
        $fPickUpPrice = 1;
        $fNightPrice = 1;
    }
    $Data['eFlatTrip'] = $data_flattrip['eFlatTrip'];
    $Data['fFlatTripPrice'] = $data_flattrip['Flatfare'];
    $Data['fPickUpPrice'] = $fPickUpPrice;
    $Data['fNightPrice'] = $fNightPrice;
    $id = $obj->MySQLQueryPerform("trips", $Data, 'insert');
    return $id;
}

function sendTripMessagePushNotification($iFromMemberId, $UserType, $iToMemberId, $iTripId, $tMessage) {
    global $generalobj, $obj, $FIREBASE_API_ACCESS_KEY;
    //$FIREBASE_API_ACCESS_KEY = $generalobj->getConfigurations("configurations", "FIREBASE_API_ACCESS_KEY");
    if ($UserType == "Passenger") {
        $tblname = "register_driver";
        $condfield = 'iDriverId';
        $field = 'vFirebaseDeviceToken';
        $Fromtblname = "register_user";
        $Fromcondfield = 'iUserId';
        $pemFileIdentifier = 1;
        $vImageName = "vImgName";
    } else {
        $tblname = "register_user";
        $condfield = 'iUserId';
        $field = 'vFirebaseDeviceToken';
        $Fromtblname = "register_driver";
        $Fromcondfield = 'iDriverId';
        $pemFileIdentifier = 0;
        $vImageName = "vImage";
    }
    $vFirebaseDeviceToken = get_value($tblname, $field, $condfield, $iToMemberId, '', 'true');
    $iGcmRegId = get_value($tblname, "iGcmRegId", $condfield, $iToMemberId, '', 'true');
    $eDeviceType = get_value($tblname, "eDeviceType", $condfield, $iToMemberId, '', 'true');
    $eLogout = get_value($tblname, "eLogout", $condfield, $iToMemberId, '', 'true');
    $MemberName = get_value($Fromtblname, 'vName,vLastName', $Fromcondfield, $iFromMemberId);
    $FromMemberImageName = get_value($Fromtblname, $vImageName, $Fromcondfield, $iFromMemberId, '', 'true');
    $FromMemberName = $MemberName[0]['vName'];
    // ." ".$MemberName[0]['vLastName']
    if ($eLogout != "Yes") {
        if ($eDeviceType == "Ios") {
            $msg_encode['Msg'] = $tMessage;
            $msg_encode['MsgType'] = "CHAT";
            $msg_encode['iFromMemberId'] = strval($iFromMemberId);
            $msg_encode['iTripId'] = strval($iTripId);
            $msg_encode['FromMemberName'] = strval($FromMemberName);
            $msg_encode['FromMemberImageName'] = strval($FromMemberImageName);
            $msg_encode = json_encode($msg_encode, JSON_UNESCAPED_UNICODE);
            $deviceTokens_arr_ios = array();
            array_push($deviceTokens_arr_ios, $iGcmRegId);
            sendApplePushNotification($pemFileIdentifier, $deviceTokens_arr_ios, $msg_encode, $tMessage, 0);
        } else {


            $registrationIds = (array) $vFirebaseDeviceToken;
            $msg['aps'] = array
                (
                'iFromMemberId' => $iFromMemberId,
                'iTripId' => $iTripId,
                'FromMemberName' => $FromMemberName,
                'Msg' => $tMessage,
                'MsgType' => "CHAT",
                'FromMemberImageName' => $FromMemberImageName
                    //'title'	=> 'Title Of Notification',
                    //'icon'	=> 'myicon',/*Default Icon*/
                    //'sound' => 'mySound'/*Default sound*/
            );
            $fields = array
                (
                'registration_ids' => $registrationIds,
                'click_action' => ".MainActivity",
                'priority' => "high",
                //'data'          => $msg
                'data' => array("message" => $msg['aps'])
            );

            $headers = array
                (
                'Authorization: key=' . $FIREBASE_API_ACCESS_KEY,
                'Content-Type: application/json',
            );
            //Setup headers:
            // echo "<pre>";print_r($headers);exit;
            //Setup curl, add headers and post parameters.
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    
            //Send the request
            $response = curl_exec($ch); //echo "<pre>";print_r($response);exit;
            $responseArr = json_decode($response);
            //echo "<pre>";print_r($responseArr);exit;
            $success = $responseArr->success;
            //Close request
            curl_close($ch);
            return $success;
        }
    }
}

function UpdateOtherLanguage($vLabel, $vValue, $vLangCode, $tablename) {
    global $generalobj, $obj;
    $sql = "SELECT vCode,vLangCode FROM `language_master` where vCode!='" . $vLangCode . "' ORDER BY `iDispOrder`";
    $db_master = $obj->MySQLSelect($sql);
    $count_all = count($db_master);
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vCode = $db_master[$i]['vCode'];
            $vGmapCode = $db_master[$i]['vLangCode'];
            $url = 'http://api.mymemory.translated.net/get?q=' . urlencode($vValue) . '&de=&langpair=en|' . $vGmapCode;
            $result = file_get_contents($url);
            $finalResult = json_decode($result);
            $getText = $finalResult->responseData;
            $resulttext = $getText->translatedText;
            if ($resulttext == "") {
                $resulttext = $vValue;
            }
            $sql = "SELECT LanguageLabelId FROM $tablename where vLabel = '" . $vLabel . "' AND vCode = '" . $vCode . "'";
            $db_language_label = $obj->MySQLSelect($sql);
            $count = count($db_language_label);
            if ($count > 0) {
                $where = " LanguageLabelId = '" . $db_language_label[0]['LanguageLabelId'] . "'";
                $data_update['vValue'] = $resulttext;
                $obj->MySQLQueryPerform($tablename, $data_update, 'update', $where);
            }
        }
    }
    return $count_all;
}

function get_currency($from_Currency, $to_Currency, $amount) {
    $forignalamount = $amount;
    $amount = urlencode($amount);
    $from_Currency = urlencode($from_Currency);
    $to_Currency = urlencode($to_Currency);
    //$url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
    $url = "https://finance.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
    $ch = curl_init();
    $timeout = 0;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt ($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $rawdata = curl_exec($ch);
    curl_close($ch);
    $data = explode('bld>', $rawdata);
    $data = explode($to_Currency, $data[1]);
    $ftollprice = round($data[0], 2);
    if ($ftollprice == 0 || $ftollprice == 0.00) {
        $ftollprice = $amount;
    }
    //return round($data[0], 2);
    return $ftollprice;
}

function Updateuserlocationdatetime($iMemberId, $user_type = "Passenger", $vTimeZone) {
    global $generalobj, $obj;
    if ($user_type == "Passenger") {
        $tableName = "register_user";
        $iUserId = 'iUserId';
    } else {
        $tableName = "register_driver";
        $iUserId = 'iDriverId';
    }
    $systemTimeZone = date_default_timezone_get();
    $currentdate = @date("Y-m-d H:i:s");
    // $tLocationUpdateDate = converToTz($currentdate,$systemTimeZone,$vTimeZone);
    $tLocationUpdateDate = $currentdate;
    $where = " $iUserId = '$iMemberId' ";
    $Data_update['vTimeZone'] = $vTimeZone;
    $Data_update['tLocationUpdateDate'] = $tLocationUpdateDate;
    $obj->MySQLQueryPerform($tableName, $Data_update, 'update', $where);
    return true;
}

function getusertripsourcelocations($iMemberId, $type = "SourceLocation") {
    global $generalobj, $obj;
    $ssql = "";
    if ($type == "SourceLocation") {
        $fields = "tStartLat,tStartLong,tSaddress";
        $ssql .= "";
    } else {
        $fields = "tEndLat,tEndLong,tDaddress";
        $ssql .= "AND eType != 'UberX'";
    }

    $sql = "SELECT $fields FROM trips where iUserId = '" . $iMemberId . "' AND iActive = 'Finished' $ssql ORDER BY iTripId DESC";
    $db_passenger_source = $obj->MySQLSelect($sql);

    if (count($db_passenger_source) > 0) {
        $db_passenger_source = array_slice($db_passenger_source, 0, 5);
    } else {
        $db_passenger_source = array();
    }

    return $db_passenger_source;
}

function fetchtripstatustimeinterval() {
    global $generalobj, $obj, $FETCH_TRIP_STATUS_TIME_INTERVAL;

    //$FETCH_TRIP_STATUS_TIME_INTERVAL = $generalobj->getConfigurations("configurations", "FETCH_TRIP_STATUS_TIME_INTERVAL");
    $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL);
    $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[0];
    $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN = $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN - 4;
    if ($FETCH_TRIP_STATUS_TIME_INTERVAL_MIN < 15) {
        $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN = 15;
    }
    $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
    $range = rand($FETCH_TRIP_STATUS_TIME_INTERVAL_MIN, $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX);

    return $range;
}

function fetchtripstatustimeMAXinterval() {
    global $generalobj, $obj, $FETCH_TRIP_STATUS_TIME_INTERVAL;

    //$FETCH_TRIP_STATUS_TIME_INTERVAL = $generalobj->getConfigurations("configurations", "FETCH_TRIP_STATUS_TIME_INTERVAL");
    $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL);

    $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];

    return $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX;
}

function CheckAvailableTimes($str) {
    if ($str != "") {
        $str = str_replace("00", "12", $str);
        $strArr = explode(",", $str);
        $returnArr = array();
        for ($i = 0; $i < count($strArr); $i++) {
            $number = $strArr[$i];
            $numberArr = explode("-", $number);
            $number1 = $numberArr[0];
            $number2 = $numberArr[1];
            $number1 = str_pad($number1, 2, '0', STR_PAD_LEFT);
            $number2 = str_pad($number2, 2, '0', STR_PAD_LEFT);
            $finalnumber = $number1 . "-" . $number2;
            $returnArr[] = $finalnumber;
        }
        $vAvailableTimes = implode(",", $returnArr);
    } else {
        $vAvailableTimes = "";
    }
    return $vAvailableTimes;
}

function checkRestrictedAreaNew($Address_Array, $DropOff) {
    //print_r($Address_Array);die;
    global $generalobj, $obj;
    $ssql = "";
    if ($DropOff == "No") {
        $ssql .= " AND (eRestrictType = 'Pick Up' OR eRestrictType = 'All')";
    } else {
        $ssql .= " AND (eRestrictType = 'Drop Off' OR eRestrictType = 'All')";
    }
    if (!empty($Address_Array)) {
        $sqlaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Allowed'" . $ssql;
        $allowed_data = $obj->MySQLSelect($sqlaa);
        $allowed_ans = 'No';
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        $allowed_ans = 'Yes';
                        break;
                    }
                }
            }
        }

        if ($allowed_ans == 'No') {
            $sqlas = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Disallowed'" . $ssql;
            $restricted_data = $obj->MySQLSelect($sqlas);
            $allowed_ans = 'Yes';
            if (!empty($restricted_data)) {
                $polygon_dis = array();
                foreach ($restricted_data as $key => $value) {
                    $latitude = explode(",", $value['tLatitude']);
                    $longitude = explode(",", $value['tLongitude']);
                    for ($x = 0; $x < count($latitude); $x++) {
                        if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                            $polygon_dis[$key][] = array($latitude[$x], $longitude[$x]);
                        }
                    }
                    if ($polygon_dis[$key]) {
                        $address_dis = contains($Address_Array, $polygon_dis[$key]) ? 'IN' : 'OUT';
                        if ($address_dis == 'IN') {
                            $allowed_ans = 'No';
                            break;
                        }
                    }
                }
            }
        }
    }
    return $allowed_ans;
}

function contains($point, $polygon) {
    if ($polygon[0] != $polygon[count($polygon) - 1])
        $polygon[count($polygon)] = $polygon[0];
    $j = 0;
    $oddNodes = false;
    $x = $point[1];
    $y = $point[0];
    $n = count($polygon);
    for ($i = 0; $i < $n; $i++) {
        $j++;
        if ($j == $n) {
            $j = 0;
        }
        if ((($polygon[$i][0] < $y) && ($polygon[$j][0] >= $y)) || (($polygon[$j][0] < $y) && ($polygon[$i][0] >= $y))) {
            if ($polygon[$i][1] + ($y - $polygon[$i][0]) / ($polygon[$j][0] - $polygon[$i][0]) * ($polygon[$j][1] -
                    $polygon[$i][1]) < $x) {
                $oddNodes = !$oddNodes;
            }
        }
    }
    return $oddNodes;
}

function GetVehicleTypeFromGeoLocation($Address_Array) {
    global $generalobj, $obj;

    $Vehicle_Str = "-1";
    if (!empty($Address_Array)) {
        $sqlaa = "SELECT * FROM location_master WHERE eStatus='Active' AND eFor = 'VehicleType'";
        $allowed_data = $obj->MySQLSelect($sqlaa);
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {

                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        $Vehicle_Str .= "," . $val['iLocationId'];
                        //break;
                    }
                }
            }
        }
    }
    return $Vehicle_Str;
}

function DisplayBookingDetails($iCabBookingId) {
    global $generalobj, $obj;
    $returnArr = array();
    $sql = "SELECT * FROM `cab_booking` WHERE iCabBookingId = '" . $iCabBookingId . "'";
    $db_booking = $obj->MySQLSelect($sql);
    $serverTimeZone = date_default_timezone_get();
    $db_booking[0]['dBooking_dateOrig'] = converToTz($db_booking[0]['dBooking_date'], $db_booking[0]['vTimeZone'], $serverTimeZone);
    $seldatetime = $db_booking[0]['dBooking_dateOrig'];
    $selecteddate = date("Y-m-d", strtotime($seldatetime));
    $newdate = explode(" ", $seldatetime);
    $time_in_12_hour_format = date("a", strtotime($seldatetime));
    $timearr = explode(":", $newdate[1]);
    $timearr1 = $timearr[0];
    $timearr1 = $timearr1 % 12;
    $timearr2 = $timearr1 + 1;
    $number1 = str_pad($timearr1, 2, '0', STR_PAD_LEFT);
    $number2 = str_pad($timearr2, 2, '0', STR_PAD_LEFT);
    $selectedtime = $number1 . "-" . $number2 . " " . $time_in_12_hour_format;
    $scheduletime1 = $timearr[0];
    $scheduletime2 = $scheduletime1 + 1;
    $scheduletime1 = str_pad($scheduletime1, 2, '0', STR_PAD_LEFT);
    $scheduletime2 = str_pad($scheduletime2, 2, '0', STR_PAD_LEFT);
    $scheduledate = $selecteddate . " " . $scheduletime1 . "-" . $scheduletime2;
    $userId = $db_booking[0]['iUserId'];
    $sql1 = "SELECT vLang,vCurrencyPassenger FROM `register_user` WHERE iUserId='$userId'";
    $row = $obj->MySQLSelect($sql1);
    $lang = $row[0]['vLang'];
    //if($lang == "" || $lang == NULL) { $lang = "EN"; }
    if ($lang == "" || $lang == NULL) {
        $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $vCurrencyPassenger = $row[0]['vCurrencyPassenger'];
    if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
        $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $UserCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyPassenger);
    $priceRatio = $UserCurrencyData[0]['Ratio'];
    $vSymbol = $UserCurrencyData[0]['vSymbol'];
    $driverId = $db_booking[0]['iDriverId'];
    $sql = "SELECT iDriverVehicleId from driver_vehicle WHERE iDriverId = '" . $driverId . "'";
    $db_drv_vehicle = $obj->MySQLSelect($sql);
    $iDriverVehicleId = $db_drv_vehicle[0]['iDriverVehicleId'];
    $iVehicleTypeId = $db_booking[0]['iVehicleTypeId'];
    $sql2 = "SELECT vc.iVehicleCategoryId, vc.iParentId,vc.vCategory_" . $lang . " as vCategory, vc.vCategoryTitle_" . $lang . " as vCategoryTitle, vc.tCategoryDesc_" . $lang . " as tCategoryDesc, vc.ePriceType, vt.vVehicleType_" . $lang . " as vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM vehicle_category as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vt.iVehicleTypeId='" . $iVehicleTypeId . "'";
    $Data = $obj->MySQLSelect($sql2);
    $iParentId = $Data[0]['iParentId'];
    if ($iParentId == 0) {
        $ePriceType = $Data[0]['ePriceType'];
    } else {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
    if ($Data[0]['eFareType'] == "Fixed") {
        //$fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fFixedFare'];
        $fAmount = $Data[0]['fFixedFare'];
    } else if ($Data[0]['eFareType'] == "Hourly") {
        //$fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fPricePerHour']."/hour";
        $fAmount = $Data[0]['fPricePerHour'];
    }
    $iPrice = $fAmount;
    if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
        $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $serviceProData = $obj->MySQLSelect($sqlServicePro);
        if (count($serviceProData) > 0) {
            $fAmount = $serviceProData[0]['fAmount'];
        } else {
            $fAmount = $iPrice;
        }
        $iPrice = $fAmount;
    }
    $returnArr['selectedtime'] = $selectedtime; // 01-02 am
    $returnArr['selecteddatetime'] = $scheduledate; // 2017-10-25 01-02
    $returnArr['SelectedFareType'] = $Data[0]['eFareType'];
    $returnArr['SelectedQty'] = $db_booking[0]['iQty'];
    $returnArr['SelectedPrice'] = $iPrice;
    $returnArr['SelectedCurrencySymbol'] = $vSymbol;
    $returnArr['SelectedCurrencyRatio'] = $priceRatio;
    $returnArr['SelectedVehicle'] = $Data[0]['vVehicleType'];
    $returnArr['SelectedCategory'] = $Data[0]['vCategory'];
    $returnArr['SelectedCategoryId'] = $Data[0]['iVehicleCategoryId'];
    $returnArr['SelectedCategoryTitle'] = $Data[0]['vCategoryTitle'];
    $returnArr['SelectedCategoryDesc'] = $Data[0]['tCategoryDesc'];
    $returnArr['SelectedAllowQty'] = $Data[0]['eAllowQty'];
    $returnArr['SelectedPriceType'] = $Data[0]['ePriceType'];
    $returnArr['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ALLOW_SERVICE_PROVIDER_AMOUNT;
    return $returnArr;
}

function getTripChatDetails($iTripId) {
    global $obj, $generalobj, $tconfig, $FIREBASE_DEFAULT_URL, $FIREBASE_DEFAULT_TOKEN, $GOOGLE_SENDER_ID;
    require_once('assets/libraries/firebase/src/firebaseInterface.php');
    require_once('assets/libraries/firebase/src/firebaseLib.php');
    //$DEFAULT_URL = 'https://ufxv4app.firebaseio.com/';
    //$DEFAULT_TOKEN = 'xcmWvKUsFF9rP7UmZp9qd14powmT1VH8GW1457aO';
    //$DEFAULT_PATH = '835770094542-chat';
    /* $FIREBASE_DEFAULT_URL = $generalobj->getConfigurations("configurations", "FIREBASE_DEFAULT_URL");
      $FIREBASE_DEFAULT_TOKEN = $generalobj->getConfigurations("configurations", "FIREBASE_DEFAULT_TOKEN");
      $GOOGLE_SENDER_ID = $generalobj->getConfigurations("configurations", "GOOGLE_SENDER_ID"); */
    $DEFAULT_PATH = $GOOGLE_SENDER_ID . "-chat";
    $firebase = new \Firebase\FirebaseLib($FIREBASE_DEFAULT_URL, $FIREBASE_DEFAULT_TOKEN);
    $fetch = $firebase->get($DEFAULT_PATH . '/' . $iTripId . '-Trip'); // reads value from Firebase
    $fetchdeco = json_decode($fetch);

    foreach ($fetchdeco as $Tripobj) {
        $Data['iTripId'] = $Tripobj->iTripId;
        $Data['tMessage'] = $Tripobj->Text;
        $iUserId = $Tripobj->passengerId;
        $iDriverId = $Tripobj->driverId;
        $Data['dAddedDate'] = @date("Y-m-d H:i:s");
        $eUserType = $Tripobj->eUserType;
        $Data['eUserType'] = $eUserType;
        $Data['eStatus'] = "Unread";
        $Data['iFromMemberId'] = ($eUserType == "Passenger") ? $iUserId : $iDriverId;
        $Data['iToMemberId'] = ($eUserType == "Passenger") ? $iDriverId : $iUserId;
        $id = $obj->MySQLQueryPerform("trip_messages", $Data, 'insert');
    }
    $delchat = $firebase->delete($DEFAULT_PATH . '/' . $iTripId . '-Trip');        // deletes value from Firebase
    return $iTripId;
}

function getMemberAverageRating($iMemberId, $eFor = "Passenger", $date = "") {
    global $generalobj, $obj;

    $ssql = "";
    if ($eFor == "Passenger") {
        $UserType = "Driver";
        $iUserId = "iUserId";
        $ssql .= "AND tr.iUserId = '" . $iMemberId . "'";
    } else {
        $UserType = "Passenger";
        $iUserId = "iDriverId";
        $ssql .= "AND tr.iDriverId = '" . $iMemberId . "'";
    }

    if ($date != "") {
        $ssql .= " AND tr.tTripRequestDate LIKE '" . $date . "%' ";
    }

    $sqlcount = "SELECT vRating1 FROM ratings_user_driver as rsu LEFT JOIN trips as tr ON rsu.iTripId=tr.iTripId WHERE rsu.eUserType='" . $UserType . "' AND tr.eHailTrip = 'No' And tr.iActive = 'Finished'" . $ssql;
    $dbtriprating = $obj->MySQLSelect($sqlcount);
    $avgRating = 0;
    $totalRating = 0;
    $count = count($dbtriprating);
    if (count($dbtriprating) > 0) {
        for ($i = 0; $i < count($dbtriprating); $i++) {
            $vRating1 = $dbtriprating[$i]['vRating1'];
            $totalRating = $totalRating + $vRating1;
        }

        $avgRating = round(($totalRating / $count), 2);
    }

    return $avgRating;
}

function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y) {
    $i = $j = $c = 0;
    for ($i = 0, $j = $points_polygon - 1; $i < $points_polygon; $j = $i++) {
        if ((($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
                ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i])))
            $c = !$c;
    }
    return $c;
}

//function checkrestrictedareaonlyy($lat, $long) {
//    //print_r($Address_Array);die;
//    
//    global $generalobj, $obj;
//    $allowed_ans = 1;
//    if (!empty($lat) && !empty($long)) {
//        ############### Check For Allow Location ######################################
//        $sqlaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict'";
//        $allowed_data = $obj->MySQLSelect($sqlaa);
//        if (count($allowed_data) > 0) {
//
//            $polygon = array();
//            foreach ($allowed_data as $key => $val) {
//                $vertices_x = explode(",", $val['tLatitude']);
//               // echo "<pre>"; print_r($vertices_x); echo "</pre> <br/>";
//                $vertices_y = explode(",", $val['tLongitude']);
//              //  echo "<pre>"; print_r($vertices_y); echo "</pre> <br/>";
//                $points_polygon = count($vertices_x);
//                $longitude_x = $lat; // x-coordinate of the point to test
//                $latitude_y = $long; // y-coordinate of the point to test
//                if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
//                    $allowed_ans = 0;
//                }
//            }
//        } else {
//            $allowed_ans = 1;
//        }
//    }
//    return $allowed_ans;
//}

function checkrestrictedareaonlyy($lat, $long) {
    //print_r($Address_Array);die;
    $Address_Array = array($lat, $long);
    global $generalobj, $obj;
    if (!empty($Address_Array)) {
        ############### Check For Allow Location ######################################
        $sqlaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict'";
        $allowed_data = $obj->MySQLSelect($sqlaa);
        if (count($allowed_data) > 0) {
            $allowed_ans = 1;
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        //echo "IN Array";
                        $allowed_ans = 0;
                        break;
                    }
                }
            }
        } else {
            $allowed_ans = 1;
        }
    }
    return $allowed_ans;
}

function checkAllowedAreaNew($Address_Array, $DropOff) {
    //print_r($Address_Array);die;
    global $generalobj, $obj;
    $ssql = "";
    if ($DropOff == "No") {
        $ssql .= " AND (eRestrictType = 'Pick Up' OR eRestrictType = 'All')";
    } else {
        $ssql .= " AND (eRestrictType = 'Drop Off' OR eRestrictType = 'All')";
    }
    if (!empty($Address_Array)) {
        ############### Check For Allow Location ######################################
        $sqlaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Allowed'" . $ssql;
        $allowed_data = $obj->MySQLSelect($sqlaa);
        if (count($allowed_data) > 0) {
            $allowed_ans = 'No';
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        $allowed_ans = 'Yes';
                        break;
                    }
                }
            }
        } else {
            $allowed_ans = 'Yes';
        }
        ############### Check For Allow Location ######################################
        ############### Check For DisAllow Location ######################################
        if ($allowed_ans == 'Yes') {
            $sqldaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Disallowed'" . $ssql;
            $disallowed_data = $obj->MySQLSelect($sqldaa);
            if (count($disallowed_data) > 0) {
                $allowed_ans = 'Yes';
                $polygon = array();
                foreach ($disallowed_data as $key => $val) {
                    $latitude = explode(",", $val['tLatitude']);
                    $longitude = explode(",", $val['tLongitude']);
                    for ($x = 0; $x < count($latitude); $x++) {
                        if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                            $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                        }
                    }
                    //print_r($polygon[$key]);
                    if ($polygon[$key]) {
                        $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                        if ($address == 'IN') {
                            $allowed_ans = 'No';
                            break;
                        }
                    }
                }
            } else {
                $allowed_ans = 'Yes';
            }
        }
        ############### Check For DisAllow Location ######################################
    }
    return $allowed_ans;
}

############### Insert Pushnotification Message Into Firebase  ######################################

function InsertMessageIntoFirebase($UserType, $iMemberId, $Message_arr) {
    global $obj, $generalobj, $tconfig, $FIREBASE_DEFAULT_URL, $FIREBASE_DEFAULT_TOKEN, $GOOGLE_SENDER_ID;
    require_once('assets/libraries/firebase/src/firebaseInterface.php');
    require_once('assets/libraries/firebase/src/firebaseLib.php');
    //$DEFAULT_URL = 'https://ufxv4app.firebaseio.com/';
    //$DEFAULT_TOKEN = 'xcmWvKUsFF9rP7UmZp9qd14powmT1VH8GW1457aO';
    //$DEFAULT_PATH = '835770094542-chat';
    /* $FIREBASE_DEFAULT_URL = $generalobj->getConfigurations("configurations", "FIREBASE_DEFAULT_URL");
      $FIREBASE_DEFAULT_TOKEN = $generalobj->getConfigurations("configurations", "FIREBASE_DEFAULT_TOKEN");
      $GOOGLE_SENDER_ID = $generalobj->getConfigurations("configurations", "GOOGLE_SENDER_ID"); */
    $FIREBASE_DEFAULT_URL = "https://cubetaxiplus-app.firebaseio.com/";
    $FIREBASE_DEFAULT_TOKEN = "FlKf2SLG0J015ZHyxz4T69njoYD8ssDFsYEYjm6g";
    $GOOGLE_SENDER_ID = "835770094542";
    $DEFAULT_PATH = $UserType;
    $firebase = new \Firebase\FirebaseLib($FIREBASE_DEFAULT_URL, $FIREBASE_DEFAULT_TOKEN);
    $insert = $firebase->push($DEFAULT_PATH . '/' . $iMemberId, $Message_arr); // Insert value into Firebase
    $returnJSON = json_decode($insert);

    return $returnJSON;
}

############### Insert Pushnotification Message Into Firebase Ends ######################################
############### Get User Country Tax ###################################################################

function getMemberCountryTax($iMemberId, $UserType = "Passenger") {
    global $generalobj, $obj;
    $returnArr = array();
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $vCountryfield = "vCountry";
        $iUserId = "iUserId";
    } else {
        $tblname = "register_driver";
        $vCountryfield = "vCountry";
        $iUserId = "iDriverId";
    }
    $fTax1 = 0;
    $fTax2 = 0;
    $sql = "SELECT COALESCE(co.fTax1, '0') as fTax1,COALESCE(co.fTax2, '0') as fTax2 FROM country as co LEFT JOIN $tblname as ru ON co.vCountryCode = ru.$vCountryfield WHERE $iUserId = '" . $iMemberId . "'";
    $sqlcountryTax = $obj->MySQLSelect($sql);
    if (count($sqlcountryTax) > 0) {
        $fTax1 = $sqlcountryTax[0]['fTax1'];
        $fTax2 = $sqlcountryTax[0]['fTax2'];
    }
    $returnArr['fTax1'] = $fTax1;
    $returnArr['fTax2'] = $fTax2;
    return $returnArr;
}

############### Get User Country Tax ###################################################################
############### Check FlatTrip Or Not  ###################################################################

function checkFlatTripnew($Source_point_Address, $Destination_point_Address, $iVehicleTypeId) {
    global $generalobj, $obj;
    $returnArr = array();
    /* $sql = "SELECT ls.fFlatfare,lm1.vLocationName as vFromname,lm2.vLocationName as vToname, lm1.tLatitude as fromlat, lm1.tLongitude as fromlong, lm2.tLatitude as tolat, lm2.tLongitude as tolong FROM `location_wise_fare` ls left join location_master lm1 on ls.iToLocationId = lm1.iLocationId left join location_master lm2 on ls.iFromLocationId = lm2.iLocationId  UNION ALL
      SELECT ls.fFlatfare,lm1.vLocationName as vToname,lm2.vLocationName as vFromname, lm1.tLatitude as tolat, lm1.tLongitude as tolong, lm2.tLatitude as fromlat, lm2.tLongitude as fromlong FROM `location_wise_fare` ls left join location_master lm1 on ls.iFromLocationId = lm1.iLocationId left join location_master lm2 on ls.iToLocationId = lm2.iLocationId
      WHERE lm1.eFor = 'FixFare' and lm1.eStatus = 'Active'"; */
    $sql = "SELECT ls.fFlatfare,lm1.vLocationName as vFromname,lm2.vLocationName as vToname, lm1.tLatitude as fromlat, lm1.tLongitude as fromlong, lm2.tLatitude as tolat, lm2.tLongitude as tolong FROM `location_wise_fare` ls left join location_master lm1 on ls.iFromLocationId = lm1.iLocationId left join location_master lm2 on ls.iToLocationId = lm2.iLocationId WHERE lm1.eFor = 'FixFare' AND lm1.eStatus = 'Active' AND ls.eStatus = 'Active' AND ls.iVehicleTypeId = '" . $iVehicleTypeId . "'";
    $location_data = $obj->MySQLSelect($sql);
    //echo"<pre>";
    //print_r($location_data);die;
    $polygon = array();
    foreach ($location_data as $key => $value) {
        $fromlat = explode(",", $value['fromlat']);
        $fromlong = explode(",", $value['fromlong']);
        $tolat = explode(",", $value['tolat']);
        $tolong = explode(",", $value['tolong']);
        for ($x = 0; $x < count($fromlat); $x++) {
            if (!empty($fromlat[$x]) || !empty($fromlong[$x])) {
                $from_polygon[$key][] = array($fromlat[$x], $fromlong[$x]);
            }
        }
        for ($y = 0; $y < count($tolat); $y++) {
            if (!empty($tolat[$y]) || !empty($tolong[$y])) {
                $to_polygon[$key][] = array($tolat[$y], $tolong[$y]);
            }
        }
        if (!empty($Source_point_Address) && !empty($Destination_point_Address)) {
            if (!empty($from_polygon[$key]) && !empty($to_polygon[$key])) {
                /* 				print_r($from_polygon[$key]);
                  echo"<br/>"; */
                $from_source_addresss = contains($Source_point_Address, $from_polygon[$key]) ? 'IN' : 'OUT';
                $to_source_addresss = contains($Destination_point_Address, $to_polygon[$key]) ? 'IN' : 'OUT';
                /* 				echo"<br/>";
                  print_r($to_polygon[$key]);
                  echo"<br/>"; */
                $to_dest_addresss = contains($Destination_point_Address, $to_polygon[$key]) ? 'IN' : 'OUT';
                $from_dest_addresss = contains($Source_point_Address, $from_polygon[$key]) ? 'IN' : 'OUT';
                if (($from_source_addresss == "IN" && $to_source_addresss == "IN") || ($to_dest_addresss == "IN" && $from_dest_addresss == "IN")) {
                    $returnArr['Flatfare'] = $location_data[$key]['fFlatfare'];
                    $returnArr['eFlatTrip'] = "Yes";
                    return $returnArr;
                }
            }
        }
    }
    if (empty($returnArr)) {
        $returnArr['eFlatTrip'] = "No";
        $returnArr['Flatfare'] = 0;
    }
    //print_r($returnArr);
    // die;
    return $returnArr;
}

############### Check FlatTrip Or Not  ###################################################################
############### Get User's  Country Details From TimeZone ####################################################################

function GetUserCounryDetail($iMemberId, $UserType = "Passenger", $vTimeZone, $vUserDeviceCountry = "") {
    global $generalobj, $obj, $DEFAULT_COUNTRY_CODE_WEB;
    $returnArr = array();
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $vCountryfield = "vCountry";
        $iUserId = "iUserId";
    } else if ($UserType == "Driver") {
        $tblname = "register_driver";
        $vCountryfield = "vCountry";
        $iUserId = "iDriverId";
    } else {
        $tblname = "company";
        $vCountryfield = "vCountry";
        $iUserId = "iCompanyId";
    }
    $returnArr['vDefaultCountry'] = '';
    $returnArr['vDefaultCountryCode'] = '';
    $returnArr['vDefaultPhoneCode'] = '';
    $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode FROM country WHERE vTimeZone = '" . $vTimeZone . "' AND eStatus = 'Active'";
    $sqlcountryCode = $obj->MySQLSelect($sql);
    if (count($sqlcountryCode) > 0) {
        $returnArr = $sqlcountryCode[0];
    } else {
        if ($vUserDeviceCountry != "") {
            $vUserDeviceCountry = strtoupper($vUserDeviceCountry);
            $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode FROM country WHERE vCountryCode = '" . $vUserDeviceCountry . "'";
            $sqlusercountryCode = $obj->MySQLSelect($sql);
            if (count($sqlusercountryCode) > 0) {
                $returnArr = $sqlusercountryCode[0];
            } else {
                $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode FROM country WHERE vCountryCode = '" . $DEFAULT_COUNTRY_CODE_WEB . "'";
                $sqlcountryCode = $obj->MySQLSelect($sql);
                $returnArr = $sqlcountryCode[0];
            }
        } else {
            $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode FROM country WHERE vCountryCode = '" . $DEFAULT_COUNTRY_CODE_WEB . "'";
            $sqlcountryCode = $obj->MySQLSelect($sql);
            $returnArr = $sqlcountryCode[0];
        }
    }
    return $returnArr;
}

############### Get User's  Country Details From TimeZone  ###################################################################
############### Get User  Country's Police Number   ###################################################################

function getMemberCountryPoliceNumber($iMemberId, $UserType = "Passenger", $vCountry) {
    global $generalobj, $obj, $SITE_POLICE_CONTROL_NUMBER;
    if ($vCountry != "") {
        if ($UserType == "Passenger") {
            $tblname = "register_user";
            $vCountryfield = "vCountry";
            $iUserId = "iUserId";
        } else if ($UserType == "Driver") {
            $tblname = "register_driver";
            $vCountryfield = "vCountry";
            $iUserId = "iDriverId";
        } else {
            $tblname = "company";
            $vCountryfield = "vCountry";
            $iUserId = "iCompanyId";
        }
        $sql = "SELECT co.vEmergencycode FROM country as co LEFT JOIN $tblname as rd ON co.vCountryCode = rd.$vCountryfield WHERE $iUserId = '" . $iMemberId . "'";
        $db_sql = $obj->MySQLSelect($sql);
        $Country_Police_Number = $db_sql[0]['vEmergencycode'];
        if ($Country_Police_Number == "" || $Country_Police_Number == NULL) {
            $Country_Police_Number = $SITE_POLICE_CONTROL_NUMBER;
        }
    } else {
        $Country_Police_Number = $SITE_POLICE_CONTROL_NUMBER;
    }
    return $Country_Police_Number;
}

############### Get User  Country's Police Number   ###################################################################

function calculate_restaurant_time_span($iCompanyId, $iUserId) {
    global $obj, $generalobj, $tconfig, $vTimeZone;
    //date_default_timezone_set($vTimeZone); 
    $serverTimeZone = date_default_timezone_get();
    $returnArr = array();
    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $Datasql = $obj->MySQLSelect($sql);
    $eStatus = $Datasql[0]['eStatus'];
    $vCountry = $Datasql[0]['vCountry'];
    if ($vCountry == "" || $vCountry == NULL) {
        $vCountry = $DEFAULT_COUNTRY_CODE_WEB;
    }
    $vTimeZone = get_value('country', 'vTimeZone', 'vCountryCode', $vCountry, '', 'true');
    date_default_timezone_set($vTimeZone);

    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = "EN";
    }

    $day = date("l");
    if ($day == "Sunday" || $day == "Saturday") {
        $vFromTimeSlot1 = "vFromSatSunTimeSlot1";
        $vFromTimeSlot2 = "vFromSatSunTimeSlot2";
        $vToTimeSlot1 = "vToSatSunTimeSlot1";
        $vToTimeSlot2 = "vToSatSunTimeSlot2";
    } else {
        $vFromTimeSlot1 = "vFromMonFriTimeSlot1";
        $vFromTimeSlot2 = "vFromMonFriTimeSlot2";
        $vToTimeSlot1 = "vToMonFriTimeSlot1";
        $vToTimeSlot2 = "vToMonFriTimeSlot2";
    }

    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1");
    if ($Datasql[0][$vFromTimeSlot1] == "00:00:00" && $Datasql[0][$vToTimeSlot1] == "00:00:00" && $Datasql[0][$vFromTimeSlot2] == "00:00:00" && $Datasql[0][$vToTimeSlot2] == "00:00:00") {
        $returnArr['status'] = "Closed";
        $returnArr['opentime'] = "";
        $returnArr['closetime'] = "";
        $returnArr['restaurantstatus'] = "closed";
    } else {

        /* $vFromTimeSlot1 = strtotime($Datasql[0]['vFromTimeSlot1']);
          $vToTimeSlot1 = strtotime($Datasql[0]['vToTimeSlot1']);
          $vFromTimeSlot2 = strtotime($Datasql[0]['vFromTimeSlot2']);
          $vToTimeSlot2 = strtotime($Datasql[0]['vToTimeSlot2']); */

        if ($Datasql[0][$vToTimeSlot1] < $Datasql[0][$vFromTimeSlot1]) {
            $endTime = strtotime($Datasql[0][$vToTimeSlot1]);
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime('+1 day', $endTime));
        } else {
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot1]));
        }

        if ($Datasql[0][$vToTimeSlot2] < $Datasql[0][$vFromTimeSlot2]) {
            $endTime2 = strtotime($Datasql[0][$vToTimeSlot2]);
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime('+1 day', $endTime2));
        } else {
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot2]));
        }

        $date = @date("H:i");
        //$currenttime = strtotime($date);

        $status = "closed";
        $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
        $opentime = "";
        $OpenAt = $languageLabelsArr['LBL_RESTAURANT_OPEN_TXT'];
        $closetime = "";
        $timeslotavailable = "No";

        if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1 || isBetween($vFromTimeSlot_2, $vToTimeSlot_2, $date) == 1) {
            $status = "open";
            $timeslotavailable = "Yes";
            $status_display = $languageLabelsArr['LBL_RESTAURANT_OPEN_STAUS_TXT'];
            $currentdate = @date("Y-m-d H:i:s");
            $enddate = @date("Y-m-d");
            if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1) {
                $enddate = $enddate . " " . $vToTimeSlot_1 . ":00";
            } else {
                $enddate = $enddate . " " . $vToTimeSlot_2 . ":00";
            }
            $datediff = strtotime($enddate) - strtotime($currentdate);
            if ($datediff < 900) {
                $closein = $languageLabelsArr['LBL_RESTAURANT_CLOSE_MINS_TXT'];
                $closemins = round($datediff / 60);
                $closetime = $closein . " " . $closemins . " " . $languageLabelsArr['LBL_MINS_SMALL'];
            }
        } else {
            $newdate = @date("Y-m-d");
            //$newdate = $newdate." ".$vFromTimeSlot_2.":00";
            if (isBetween($vFromTimeSlot_1, $vFromTimeSlot_1, $date) == 1) {
                $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
            } else {
                if ($vFromTimeSlot_1 < $vFromTimeSlot_2 && $vFromTimeSlot_1 > $date) {
                    $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                } else {
                    $newdate = ($vFromTimeSlot_2 == "00:00") ? $newdate . " " . $vFromTimeSlot_1 . ":00" : $newdate . " " . $vFromTimeSlot_2 . ":00";
                }
            }
            $currentdate = @date("Y-m-d H:i:s");
            $datediff = strtotime($newdate) - strtotime($currentdate);
            if ($datediff > 0) {
                $opentime = $OpenAt . " " . date("h:i a", strtotime($newdate));
            }
        }

        $eAvailable = $Datasql[0]['eAvailable'];
        $eLogout = $Datasql[0]['eLogout'];
        if ($eAvailable == "No" || $eLogout == "Yes" || $eStatus != "Active") {
            $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
            //$opentime = "";
            $closetime = "";
            $status = "closed";
        }

        $returnArr['status'] = $status_display;
        $returnArr['opentime'] = $opentime;
        $returnArr['closetime'] = $closetime;
        $returnArr['restaurantstatus'] = $status;
        $returnArr['timeslotavailable'] = $timeslotavailable;
    }
    //echo "<pre>";print_r($returnArr);
    date_default_timezone_set($serverTimeZone);
    return $returnArr;
}

function isBetween($from, $till, $input) {
    $f = DateTime::createFromFormat('!H:i', $from);
    $t = DateTime::createFromFormat('!H:i', $till);
    $i = DateTime::createFromFormat('!H:i', $input);
    if ($f > $t)
        $t->modify('+1 day');
    return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
}

function getCompanyDetails($iCompanyId, $iUserId, $CheckNonVegFoodType = "No", $searchword = "", $iServiceId = "") {
    global $obj, $generalobj, $tconfig;

    if ($iUserId != "") {
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];

        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        if ($currencycode == "" || $currencycode == NULL) {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $Ratio = $currencyData[0]['Ratio'];
    }
    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    $LBL_PER_PERSON_TXT = $languageLabelsArr['LBL_PER_PERSON_TXT'];

    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompany = $obj->MySQLSelect($sql);
    if (isset($DataCompany[0]['fPricePerPerson'])) {
        $personprice = $DataCompany[0]['fPricePerPerson'];
        $PersonPrice = round(($personprice * $Ratio), 2);
        $returnArr['fPricePerPersonWithCurrency'] = $currencySymbol . " " . $PersonPrice;
    }
    $fPricePerPerson = $DataCompany[0]['fPricePerPerson'];
    $fPricePerPerson = round(($fPricePerPerson * $Ratio), 2);
    $fPricePerPerson = $currencySymbol . "" . $fPricePerPerson . " " . $LBL_PER_PERSON_TXT;
    $returnArr['Restaurant_PricePerPerson'] = $fPricePerPerson;

    $CompanyTimeSlot = getCompanyTimeSlot($iCompanyId, $languageLabelsArr);
    $returnArr['monfritimeslot_TXT'] = $CompanyTimeSlot['monfritimeslot_TXT'];
    $returnArr['monfritimeslot_Time'] = $CompanyTimeSlot['monfritimeslot_Time_new'];
    $returnArr['satsuntimeslot_TXT'] = $CompanyTimeSlot['satsuntimeslot_TXT'];
    $returnArr['satsuntimeslot_Time'] = $CompanyTimeSlot['satsuntimeslot_Time_new'];
    //echo "<pre>";print_r($CompanyTimeSlot);exit;

    $sql = "SELECT cu.cuisineName_" . $vLanguage . " as cuisineName,cu.cuisineId FROM cuisine as cu LEFT JOIN company_cuisine as ccu ON ccu.cuisineId=cu.cuisineId WHERE ccu.iCompanyId = '" . $iCompanyId . "' AND cu.eStatus = 'Active'";
    $db_cuisine = $obj->MySQLSelect($sql);

    if (count($db_cuisine) > 0) {
        for ($i = 0; $i < count($db_cuisine); $i++) {
            $db_cuisine_str .= $db_cuisine[$i]['cuisineName'] . ", ";
            $db_cuisine_id_str .= $db_cuisine[$i]['cuisineId'] . ",";
        }
        $db_cuisine_str = substr($db_cuisine_str, 0, -2);
        $db_cuisine_id_str = substr($db_cuisine_id_str, 0, -1);
    } else {
        $db_cuisine_str = "";
        $db_cuisine_id_str = "";
    }
    $returnArr['Restaurant_Cuisine'] = $db_cuisine_str;
    $returnArr['Restaurant_Cuisine_Id'] = $db_cuisine_id_str;

    $LBL_MINS_SMALL = $languageLabelsArr['LBL_MINS_SMALL'];
    $fPrepareTime = $DataCompany[0]['fPrepareTime'];
    $fPrepareTime = $fPrepareTime . " " . $LBL_MINS_SMALL;
    $returnArr['Restaurant_OrderPrepareTime'] = $fPrepareTime;

    $fOfferType = $DataCompany[0]['fOfferType'];
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferAmt = $DataCompany[0]['fOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    $fTargetAmt = round(($fTargetAmt * $Ratio), 2);
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fMaxOfferAmt = round(($fMaxOfferAmt * $Ratio), 2);
    if ($fMaxOfferAmt > 0) {
        $MaxDiscountAmount = " ( " . $languageLabelsArr['LBL_MAX_DISCOUNT_TXT'] . " " . $currencySymbol . "" . $fMaxOfferAmt . " )";
    } else {
        $MaxDiscountAmount = "";
    }
    if ($fTargetAmt > 0) {
        $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'] . " " . $languageLabelsArr['LBL_ORDERS_ABOVE_TXT'] . " " . $currencySymbol . "" . $fTargetAmt . " ";
        $ALL_ORDER_TXT = "";
    } else {
        $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'];
        $ALL_ORDER_TXT = $languageLabelsArr['LBL_ALL_ORDER_TXT'];
    }

    if ($fOfferType == "Percentage") {
        if ($fOfferAppyType == "First") {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'] . "" . $MaxDiscountAmount;
            $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
        } elseif ($fOfferAppyType == "All") {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT . " " . $MaxDiscountAmount;
            //$offermsg =  $languageLabelsArr['LBL_GET_TXT']." ".$fOfferAmt."% ".$TargerAmountTXT." ".$MaxDiscountAmount;
            $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
        } else {
            $offermsg = "";
            $offermsg_short = "";
        }
    } else {
        $fOfferAmt = round(($fOfferAmt * $Ratio), 2);
        $DiscountAmount = $currencySymbol . "" . $fOfferAmt;
        if ($fOfferAppyType == "First" && $fOfferAmt > 0) {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
            $offermsg_short = $offermsg;
        } elseif ($fOfferAppyType == "All" && $fOfferAmt > 0) {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
            //$offermsg =  $languageLabelsArr['LBL_GET_TXT']." ".$DiscountAmount." ".$TargerAmountTXT;
            $offermsg_short = $offermsg;
        } else {
            $offermsg = "";
            $offermsg_short = "";
        }
    }
    $returnArr['Restaurant_OfferMessage'] = $offermsg;
    $returnArr['Restaurant_OfferMessage_short'] = $offermsg_short;

    $fMinOrderValue = $DataCompany[0]['fMinOrderValue'];
    $fMinOrderValue = round(($fMinOrderValue * $Ratio), 2);
    $returnArr['fMinOrderValueDisplay'] = $currencySymbol . " " . $fMinOrderValue;
    $returnArr['fMinOrderValue'] = $fMinOrderValue;
    $returnArr['Restaurant_MinOrderValue'] = ($fMinOrderValue > 0) ? $currencySymbol . $fMinOrderValue . " " . $languageLabelsArr['LBL_MIN_ORDER_TXT'] : $languageLabelsArr['LBL_NO_MIN_ORDER_TXT'];
    $fPackingCharge = $DataCompany[0]['fPackingCharge'];
    $fPackingCharge = round(($fPackingCharge * $Ratio), 2);
    $returnArr['fPackingCharge'] = $fPackingCharge;
    //echo "<pre>";print_r($returnArr);
    ## Check NonVeg Item Available of Restaaurant ##
    $eNonVegToggleDisplay = "No";
    $sql = "SELECT count(mi.iMenuItemId) As TotNonVegItems FROM menu_items as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId=mi.iFoodMenuId WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND mi.eStatus='Active' AND mi.eFoodType = 'NonVeg'";
    $db_foodtype_data = $obj->MySQLSelect($sql);
    $TotNonVegItems = $db_foodtype_data[0]['TotNonVegItems'];
    $sql = "SELECT count(mi.iMenuItemId) As TotVegItems FROM menu_items as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId=mi.iFoodMenuId WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND mi.eStatus='Active' AND mi.eFoodType = 'Veg'";
    $db_vegfoodtype_data = $obj->MySQLSelect($sql);
    $TotVegItems = $db_vegfoodtype_data[0]['TotVegItems'];
    if ($TotNonVegItems > 0 && $TotVegItems > 0) {
        $eNonVegToggleDisplay = "Yes";
    }
    $returnArr['eNonVegToggleDisplay'] = $eNonVegToggleDisplay;
    ## Check NonVeg Item Available of Restaaurant ##
    ## Get Company Rattings ## 
    $rsql = "SELECT count(r.iRatingId) as totalratings FROM orders as o LEFT JOIN ratings_user_driver as r on r.iOrderId=o.iOrderId WHERE o.iCompanyId='" . $iCompanyId . "' AND r.eFromUserType='Passenger' AND r.eToUserType='Company'";
    $Rating_data = $obj->MySQLSelect($rsql);
    $ratingcounts = $Rating_data[0]['totalratings'];
    if ($ratingcounts <= 100) {
        $ratings = $ratingcounts . " " . $languageLabelsArr['LBL_RATING'];
    } else {
        $ratings = $ratingcounts . "+ " . $languageLabelsArr['LBL_RATING'];
    }

    $returnArr['RatingCounts'] = $ratings;
    ## End Get Company Rattings ## 
    ## Get Company's menu details ##
    //$sql = "SELECT * FROM food_menu WHERE iCompanyId = '".$iCompanyId."' AND eStatus='Active' ORDER BY iDisplayOrder ASC";
    $sql = "SELECT fm.* FROM food_menu as fm WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND (select count(iMenuItemId) from menu_items as mi where mi.iFoodMenuId=fm.iFoodMenuId AND mi.eStatus='Active' AND mi.eAvailable = 'Yes') > 0 ORDER BY fm.iDisplayOrder ASC";
    $db_food_data = $obj->MySQLSelect($sql);
    $CompanyFoodData = array();
    $MenuItemsDataArr = array();
    if (count($db_food_data) > 0) {
        for ($i = 0; $i < count($db_food_data); $i++) {
            $iFoodMenuId = $db_food_data[$i]['iFoodMenuId'];
            $vMenu = $db_food_data[$i]['vMenu_' . $vLanguage];
            $CompanyFoodData[$i]['iFoodMenuId'] = $iFoodMenuId;
            $CompanyFoodData[$i]['vMenu'] = $vMenu;

            $ssql = "";
            if ($CheckNonVegFoodType == "Yes") {
                $ssql .= " AND eFoodType = 'Veg' ";
            }
            if ($searchword != "") {
                $ssql .= " AND LOWER(vItemType_" . $vLanguage . ") LIKE '%" . $searchword . "%' ";
            }
            $sqlf = "SELECT iMenuItemId,iFoodMenuId,vItemType_" . $vLanguage . " as vItemType,vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName FROM menu_items WHERE iFoodMenuId = '" . $iFoodMenuId . "' AND eStatus='Active' AND eAvailable = 'Yes' $ssql ORDER BY iDisplayOrder ASC";
            $db_item_data = $obj->MySQLSelect($sqlf);
            $CompanyFoodData[$i]['vMenuItemCount'] = count($db_item_data);
            if (count($db_item_data) > 0) {
                for ($j = 0; $j < count($db_item_data); $j++) {
                    //$fPrice= $db_item_data[$j]['fPrice'];
                    //$fOfferAmt = $db_item_data[$j]['fOfferAmt'];
                    if (!empty($vMenu)) {
                        $db_item_data[$j]['vCategoryName'] = $vMenu;
                    } else {
                        $db_item_data[$j]['vCategoryName'] = '';
                    }
                    $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($db_item_data[$j]['iMenuItemId'], $iCompanyId, 1, $iUserId, "Display", "", "");
                    $fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                    $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
                    $db_item_data[$j]['fOfferAmt'] = $fOfferAmt;
                    $db_item_data[$j]['fPrice'] = round($db_item_data[$j]['fPrice'] * $Ratio, 2);
                    if ($fOfferAmt > 0) {
                        /* $fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt)/100);
                          $fDiscountPrice = round($fDiscountPrice*$Ratio,2);
                          $StrikeoutPrice = round($fPrice*$Ratio,2); */
                        $fDiscountPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                        $StrikeoutPrice = round($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
                        $db_item_data[$j]['fDiscountPrice'] = formatNum($fDiscountPrice);
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    } else {
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['fDiscountPrice'] = formatNum($fPrice);
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    }
                    if ($db_item_data[$j]['vImage'] != "") {
                        $db_item_data[$j]['vImage'] = $tconfig["tsite_upload_images_menu_item"] . "/" . $db_item_data[$j]['vImage'];
                    }
                    $MenuItemOptionToppingArr = GetMenuItemOptionsTopping($db_item_data[$j]['iMenuItemId'], $currencySymbol, $Ratio, $vLanguage);
                    $db_item_data[$j]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
                    //echo "<pre>";print_r($MenuItemOptionToppingArr);exit;
                    $CompanyFoodData[$i]['menu_items'][] = $db_item_data[$j];
                    array_push($MenuItemsDataArr, $db_item_data[$j]);
                }
            }
        }
    }

    $CompanyFoodData_New = array();
    $CompanyFoodData_New = $CompanyFoodData;
    for ($i = 0; $i < count($CompanyFoodData); $i++) {
        $vMenuItemCount = $CompanyFoodData[$i]['vMenuItemCount'];
        if ($vMenuItemCount == 0) {
            unset($CompanyFoodData_New[$i]);
        }
    }

    $CompanyFoodData = array_values($CompanyFoodData_New);
    $returnArr['CompanyFoodData'] = $CompanyFoodData;
    $returnArr['CompanyFoodDataCount'] = count($CompanyFoodData);
    if ($searchword != "") {
        $returnArr['MenuItemsDataArr'] = $MenuItemsDataArr;
    } else {
        $returnArr['MenuItemsDataArr'] = array();
    }

    $Recomendation_Arr = getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, "Recommended", $CheckNonVegFoodType);
    $returnArr['Recomendation_Arr'] = $Recomendation_Arr;
    ## Get Company's menu details ##
    return $returnArr;
}

function getCompanyOffer($iCompanyId, $iUserId, $fOfferAppyType, $fOfferType, $fOfferAmt, $fMaxOfferAmt) {
    global $obj, $generalobj, $tconfig;

    if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $LBL_GET_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_GET_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $LBL_ALL_ORDER_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_ALL_ORDER_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $LBL_FIRST_ORDER_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_FIRST_ORDER_TXT', " and vCode='" . $vLanguage . "'", 'true');

    if ($fOfferType == "Percentage") {
        if ($fOfferAppyType == "First") {
            $offermsg = $LBL_GET_TXT . " " . $fOfferAmt . "% " . $LBL_FIRST_ORDER_TXT;
        } elseif ($fOfferAppyType == "All") {
            $offermsg = $LBL_GET_TXT . " " . $fOfferAmt . "% " . $LBL_ALL_ORDER_TXT;
        } else {
            $offermsg = "";
        }
    } else {
        
    }

    return $offermsg;
}

function getCompanyBySearchCuisine($iUserId, $SearchKeyword, $Restaurant_id_str = 0) {
    global $obj, $generalobj, $tconfig;

    $returnArr = array();
    if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $LBL_RESTAURANTS_TXT = get_value('language_label_other', 'vValue', 'vLabel', 'LBL_RESTAURANTS_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $LBL_RESTAURANT_TXT = get_value('language_label_other', 'vValue', 'vLabel', 'LBL_RESTAURANT_TXT', " and vCode='" . $vLanguage . "'", 'true');

    $sql = "SELECT cuisineId, cuisineName_" . $vLanguage . " as cuisineName FROM cuisine WHERE eStatus='Active' AND cuisineName_" . $vLanguage . " LIKE '%" . $SearchKeyword . "%'";
    $CuisineDetail = $obj->MySQLSelect($sql);
    if (count($CuisineDetail) > 0) {
        for ($i = 0; $i < count($CuisineDetail); $i++) {
            $cuisineId = $CuisineDetail[$i]['cuisineId'];
            $cuisineName = $CuisineDetail[$i]['cuisineName'];

            $sqlr = "SELECT count(iCompanyId) as TotalRestaurant FROM company_cuisine WHERE cuisineId = '" . $cuisineId . "' AND iCompanyId IN($Restaurant_id_str)";
            $CuisineTotalRestaurant = $obj->MySQLSelect($sqlr);
            $TotalRestaurant = $CuisineTotalRestaurant[0]['TotalRestaurant'];
            if ($TotalRestaurant > 0) {
                $TotalRestaurantTxt = ( $TotalRestaurant <= 1 ) ? $LBL_RESTAURANT_TXT : $LBL_RESTAURANTS_TXT;
                $returnArr[$i]['cuisineId'] = $cuisineId;
                $returnArr[$i]['cuisineName'] = $cuisineName;
                $returnArr[$i]['TotalRestaurant'] = $TotalRestaurant;
                $returnArr[$i]['TotalRestaurantWithLabel'] = $TotalRestaurant . " " . $TotalRestaurantTxt;
            }
        }
    }

    return $returnArr;
}

function getCompanyTimeSlot($iCompanyId, $languageLabelsArr) {
    global $obj, $generalobj, $tconfig;

    $sql = "SELECT vFromMonFriTimeSlot1,vToMonFriTimeSlot1,vFromMonFriTimeSlot2,vToMonFriTimeSlot2,vFromSatSunTimeSlot1,vToSatSunTimeSlot1,vFromSatSunTimeSlot2,vToSatSunTimeSlot2 FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompanyTime = $obj->MySQLSelect($sql);
    // print_R($DataCompanyTime);die;
    $vFromMonFriTimeSlot1 = substr($DataCompanyTime[0]['vFromMonFriTimeSlot1'], 0, -3);
    $vToMonFriTimeSlot1 = substr($DataCompanyTime[0]['vToMonFriTimeSlot1'], 0, -3);
    $vFromMonFriTimeSlot2 = substr($DataCompanyTime[0]['vFromMonFriTimeSlot2'], 0, -3);
    $vToMonFriTimeSlot2 = substr($DataCompanyTime[0]['vToMonFriTimeSlot2'], 0, -3);
    $vFromSatSunTimeSlot1 = substr($DataCompanyTime[0]['vFromSatSunTimeSlot1'], 0, -3);
    $vToSatSunTimeSlot1 = substr($DataCompanyTime[0]['vToSatSunTimeSlot1'], 0, -3);
    $vFromSatSunTimeSlot2 = substr($DataCompanyTime[0]['vFromSatSunTimeSlot2'], 0, -3);
    $vToSatSunTimeSlot2 = substr($DataCompanyTime[0]['vToSatSunTimeSlot2'], 0, -3);

    $vFromMonFriTimeSlotNew1 = date("g:i a", strtotime($vFromMonFriTimeSlot1));
    $vToMonFriTimeSlotNew1 = date("g:i a", strtotime($vToMonFriTimeSlot1));
    $vFromMonFriTimeSlotNew2 = date("g:i a", strtotime($vFromMonFriTimeSlot2));
    $vToMonFriTimeSlotNew2 = date("g:i a", strtotime($vToMonFriTimeSlot2));
    $vFromSatSunTimeSlotNew1 = date("g:i a", strtotime($vFromSatSunTimeSlot1));
    $vToSatSunTimeSlotNew1 = date("g:i a", strtotime($vToSatSunTimeSlot1));
    $vFromSatSunTimeSlotNew2 = date("g:i a", strtotime($vFromSatSunTimeSlot2));
    $vToSatSunTimeSlotNew2 = date("g:i a", strtotime($vToSatSunTimeSlot2));

    if ($vFromMonFriTimeSlot1 == "00:00" && $vToMonFriTimeSlot1 == "00:00" && $vFromMonFriTimeSlot2 == "00:00" && $vToMonFriTimeSlot2 == "00:00") {
        $monfritimeslot_TXT = "";
        $monfritimeslot_Time = "";
        $monfritimeslot_Time_new = "";
    }
    if ($vFromMonFriTimeSlot1 != "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 != "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }
    if ($vFromMonFriTimeSlot1 == "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 != "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;

        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }
    if ($vFromMonFriTimeSlot1 != "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 == "00:00" && $vToMonFriTimeSlot2 == "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1;
    }
    if ($vFromMonFriTimeSlot1 == "00:00" && $vToMonFriTimeSlot1 == "00:00" && $vFromMonFriTimeSlot2 != "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }

    if ($vFromSatSunTimeSlot1 == "00:00" && $vToSatSunTimeSlot1 == "00:00" && $vFromSatSunTimeSlot2 == "00:00" && $vToSatSunTimeSlot2 == "00:00") {
        $satsuntimeslot_TXT = "";
        $satsuntimeslot_Time = "";
        $satsuntimeslot_Time_new = "";
    }
    if ($vFromSatSunTimeSlot1 != "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 != "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }
    if ($vFromSatSunTimeSlot1 == "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 != "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }
    if ($vFromSatSunTimeSlot1 != "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 == "00:00" && $vToSatSunTimeSlot2 == "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1;
    }
    if ($vFromSatSunTimeSlot1 == "00:00" && $vToSatSunTimeSlot1 == "00:00" && $vFromSatSunTimeSlot2 != "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }

    $returnArr['monfritimeslot_TXT'] = $monfritimeslot_TXT;
    $returnArr['monfritimeslot_Time'] = $monfritimeslot_Time;
    $returnArr['monfritimeslot_Time_new'] = $monfritimeslot_Time_new;
    $returnArr['satsuntimeslot_TXT'] = $satsuntimeslot_TXT;
    $returnArr['satsuntimeslot_Time'] = $satsuntimeslot_Time;
    $returnArr['satsuntimeslot_Time_new'] = $satsuntimeslot_Time_new;

    return $returnArr;
}

function GetMenuItemOptionsTopping($iMenuItemId, $currencySymbol, $Ratio, $vLanguage) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $sql = "SELECT iOptionId,vOptionName,fPrice,nOptions,vOptionCat,vOptionReq,eOptionType,eDefault FROM menuitem_options WHERE iMenuItemId = '" . $iMenuItemId . "' AND eStatus = 'Active'";
    $db_options_data = $obj->MySQLSelect($sql);
    if (count($db_options_data) > 0) {
        for ($i = 0; $i < count($db_options_data); $i++) {
            $fPrice = $db_options_data[$i]['fPrice'];
            $fUserPrice = number_format($fPrice * $Ratio, 2);
            $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
            $db_options_data[$i]['fUserPrice'] = $fUserPrice;
            $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
            if ($db_options_data[$i]['eOptionType'] == "Options") {
                $returnArr['options'][] = $db_options_data[$i];
            }
            if ($db_options_data[$i]['eOptionType'] == "Addon") {
                $returnArr['addon'][] = $db_options_data[$i];
            }
        }
    }
    //echo "<pre>";print_r($returnArr1);exit;
    return $returnArr;
}

function getUserCurrencyLanguageDetails($iUserId = "", $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;

    $returnArr = array();
    if ($iUserId != "") {
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($iOrderId > 0) {
            $sql = "SELECT fRatio_" . $currencycode . " as Ratio FROM orders WHERE iOrderId = '" . $iOrderId . "'";
            $CurrencyData = $obj->MySQLSelect($sql);
            $Ratio = $CurrencyData[0]['Ratio'];
        }

        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        if ($currencycode == "" || $currencycode == NULL) {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $Ratio = $currencyData[0]['Ratio'];
    }
    $returnArr['currencycode'] = $currencycode;
    $returnArr['currencySymbol'] = $currencySymbol;
    $returnArr['Ratio'] = $Ratio;
    $returnArr['vLang'] = $vLanguage;
    return $returnArr;
}

function getDriverCurrencyLanguageDetails($iDriverId = "", $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;

    $returnArr = array();
    if ($iDriverId != "") {
        $sqlp = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iDriverId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyDriver'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($iOrderId > 0) {
            $sql = "SELECT fRatio_" . $currencycode . " as Ratio FROM orders WHERE iOrderId = '" . $iOrderId . "'";
            $CurrencyData = $obj->MySQLSelect($sql);
            $Ratio = $CurrencyData[0]['Ratio'];
        }

        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        if ($currencycode == "" || $currencycode == NULL) {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $Ratio = $currencyData[0]['Ratio'];
    }
    $returnArr['currencycode'] = $currencycode;
    $returnArr['currencySymbol'] = $currencySymbol;
    $returnArr['Ratio'] = $Ratio;
    $returnArr['vLang'] = $vLanguage;
    return $returnArr;
}

function getCompanyCurrencyLanguageDetails($iCompanyId = "", $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;

    $returnArr = array();
    if ($iCompanyId != "") {
        $sqlp = "SELECT co.vCurrencyCompany,co.vLang,cu.vSymbol,cu.Ratio FROM company as co LEFT JOIN currency as cu ON co.vCurrencyCompany = cu.vName WHERE iCompanyId = '" . $iCompanyId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyCompany'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($iOrderId > 0) {
            $sql = "SELECT fRatio_" . $currencycode . " as Ratio FROM orders WHERE iOrderId = '" . $iOrderId . "'";
            $CurrencyData = $obj->MySQLSelect($sql);
            $Ratio = $CurrencyData[0]['Ratio'];
        }

        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        if ($currencycode == "" || $currencycode == NULL) {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $Ratio = $currencyData[0]['Ratio'];
    }
    $returnArr['currencycode'] = $currencycode;
    $returnArr['currencySymbol'] = $currencySymbol;
    $returnArr['Ratio'] = $Ratio;
    $returnArr['vLang'] = $vLanguage;
    return $returnArr;
}

function GetAllMenuItemOptionsTopping($iCompanyId, $currencySymbol, $Ratio, $vLanguage, $eFor = "") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $returnArr['options'] = array();
    $returnArr['addon'] = array();

    $ssql = "";
    if ($eFor == "Display") {
        $ssql .= " AND mo.eStatus = 'Active' ";
    }
    //$languageLabelsArr = getLanguageLabelsArr($vLanguage,"1");

    $sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus = 'Active' AND mi.eAvailable = 'Yes'" . $ssql;
    $db_options_data = $obj->MySQLSelect($sql);
    if (count($db_options_data) > 0) {
        for ($i = 0; $i < count($db_options_data); $i++) {
            $fPrice = $db_options_data[$i]['fPrice'];
            $fUserPrice = number_format($fPrice * $Ratio, 2);
            $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
            $db_options_data[$i]['fUserPrice'] = $fUserPrice;
            $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
            if ($db_options_data[$i]['eOptionType'] == "Options") {
                $returnArr['options'][] = $db_options_data[$i];
            }
            if ($db_options_data[$i]['eOptionType'] == "Addon") {
                $returnArr['addon'][] = $db_options_data[$i];
            }
        }
    }
    //echo "<pre>";print_r($returnArr);exit;
    return $returnArr;
}

function GetUserSelectedAddress($iUserId, $eUserType = "Passenger") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();

    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $sql = "SELECT * from user_address WHERE iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND eStatus = 'Active'";
    $result_Address = $obj->MySQLSelect($sql);
    $ToTalAddress = count($result_Address);
    if ($ToTalAddress > 0) {
        ## Checking First Last Orders Selected Address ##
        $sqlo = "SELECT ord.iUserAddressId,ua.eStatus,ua.vServiceAddress,ua.vBuildingNo,ua.vLandmark,ua.vAddressType,ua.vLatitude,ua.vLongitude from orders as ord LEFT JOIN user_address as ua ON ord.iUserAddressId=ua.iUserAddressId WHERE ord.iUserId = '" . $iUserId . "' ORDER BY ord.iOrderId DESC limit 0,1";
        $last_order_Address = $obj->MySQLSelect($sqlo);
        $iUserAddressId = $last_order_Address[0]['iUserAddressId'];
        if (count($last_order_Address) > 0 && $iUserAddressId > 0) {
            $eStatus = $last_order_Address[0]['eStatus'];
            if ($eStatus == "Active") {
                $vAddressType = $last_order_Address[0]['vAddressType'];
                $vBuildingNo = $last_order_Address[0]['vBuildingNo'];
                $vLandmark = $last_order_Address[0]['vLandmark'];
                $vServiceAddress = $last_order_Address[0]['vServiceAddress'];
                $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
                $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
                $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
                $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
                $PickUpLatitude = $last_order_Address[0]['vLatitude'];
                $PickUpLongitude = $last_order_Address[0]['vLongitude'];
                $returnArr['UserSelectedAddress'] = $PickUpAddress;
                $returnArr['UserSelectedLatitude'] = $PickUpLatitude;
                $returnArr['UserSelectedLongitude'] = $PickUpLongitude;
                $returnArr['UserSelectedAddressId'] = $iUserAddressId;
            } else {
                $returnArr['UserSelectedAddress'] = "";
                $returnArr['UserSelectedLatitude'] = "";
                $returnArr['UserSelectedLongitude'] = "";
                $returnArr['UserSelectedAddressId'] = 0;
            }
        } else {
            $vAddressType = $result_Address[0]['vAddressType'];
            $vBuildingNo = $result_Address[0]['vBuildingNo'];
            $vLandmark = $result_Address[0]['vLandmark'];
            $vServiceAddress = $result_Address[0]['vServiceAddress'];
            $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
            $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
            $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
            $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
            $PickUpLatitude = $result_Address[0]['vLatitude'];
            $PickUpLongitude = $result_Address[0]['vLongitude'];
            $returnArr['UserSelectedAddress'] = $PickUpAddress;
            $returnArr['UserSelectedLatitude'] = $PickUpLatitude;
            $returnArr['UserSelectedLongitude'] = $PickUpLongitude;
            $returnArr['UserSelectedAddressId'] = $result_Address[0]['iUserAddressId'];
        }
        ## Checking First Last Orders Selected Address ##
    } else {
        $returnArr['UserSelectedAddress'] = "";
        $returnArr['UserSelectedLatitude'] = "";
        $returnArr['UserSelectedLongitude'] = "";
        $returnArr['UserSelectedAddressId'] = 0;
    }

    return $returnArr;
}

function GetUserAddressDetail($iUserId, $eUserType = "Passenger", $iUserAddressId) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();

    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $sql = "SELECT * from user_address WHERE iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND iUserAddressId = '" . $iUserAddressId . "'";
    $result_Address = $obj->MySQLSelect($sql);
    $ToTalAddress = count($result_Address);
    if ($ToTalAddress > 0) {
        $vAddressType = $result_Address[0]['vAddressType'];
        $vBuildingNo = $result_Address[0]['vBuildingNo'];
        $vLandmark = $result_Address[0]['vLandmark'];
        $vServiceAddress = $result_Address[0]['vServiceAddress'];
        $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
        $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
        $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
        $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
        $result_Address[0]['UserAddress'] = $PickUpAddress;
        $returnArr = $result_Address[0];
    }

    return $returnArr;
}

function GetTotalUserAddress($iUserId, $eUserType = "Passenger", $passengerLat, $passengerLon, $iCompanyId = 0) {
    global $obj, $generalobj, $tconfig, $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $ToTalAddress = 0;

    if ($iUserId == "" || $iUserId == 0 || $iUserId == NULL) {
        return $ToTalAddress;
    }

    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $sql = "select * from `user_address` where iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND eStatus = 'Active' ORDER BY iUserAddressId DESC";
    $db_userdata = $obj->MySQLSelect($sql);
    $db_userdata_new = array();
    $db_userdata_new = $db_userdata;
    if (count($db_userdata) > 0) {
        for ($i = 0; $i < count($db_userdata); $i++) {
            $isRemoveAddressFromList = "No";
            $passengeraddlat = $db_userdata[$i]['vLatitude'];
            $passengeraddlong = $db_userdata[$i]['vLongitude'];

            if ($iCompanyId == 0) {
                $distance = distanceByLocation($passengerLat, $passengerLon, $passengeraddlat, $passengeraddlong, "K");
                if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
            }

            ## Checking Distance Between Company and User Address ##
            if ($iCompanyId > 0) {
                $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
                $db_companydata = $obj->MySQLSelect($sql);
                $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
                $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];

                $distancewithcompany = distanceByLocation($passengeraddlat, $passengeraddlong, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
                if ($distancewithcompany > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
            }
            ## Checking Distance Between Company and User Address ## 

            if ($isRemoveAddressFromList == "Yes") {
                unset($db_userdata_new[$i]);
            }
        }

        $db_userdata = array_values($db_userdata_new);
        $ToTalAddress = count($db_userdata);
    }

    return $ToTalAddress;
}

function GetUserSelectedLastOrderAddressCompanyLocationWise($iUserId, $eUserType = "Passenger", $passengerLat, $passengerLon, $iCompanyId) {
    global $obj, $generalobj, $tconfig, $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $ToTalAddress = 0;

    if ($iUserId == "" || $iUserId == 0 || $iUserId == NULL) {
        return $ToTalAddress;
    }

    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $sql = "select * from `user_address` where iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND eStatus = 'Active' ORDER BY iUserAddressId DESC";
    $db_userdata = $obj->MySQLSelect($sql);
    $db_userdata_new = array();
    $db_userdata_new = $db_userdata;
    if (count($db_userdata) > 0) {
        for ($i = 0; $i < count($db_userdata); $i++) {
            $isRemoveAddressFromList = "No";
            $passengeraddlat = $db_userdata[$i]['vLatitude'];
            $passengeraddlong = $db_userdata[$i]['vLongitude'];

            if ($iCompanyId == 0) {
                $distance = distanceByLocation($passengerLat, $passengerLon, $passengeraddlat, $passengeraddlong, "K");
                if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
            }

            ## Checking Distance Between Company and User Address ##
            if ($iCompanyId > 0) {
                $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
                $db_companydata = $obj->MySQLSelect($sql);
                $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
                $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];

                $distancewithcompany = distanceByLocation($passengeraddlat, $passengeraddlong, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
                if ($distancewithcompany > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
            }
            ## Checking Distance Between Company and User Address ## 

            if ($isRemoveAddressFromList == "Yes") {
                unset($db_userdata_new[$i]);
            }
        }

        $db_userdata_addressarr = array_values($db_userdata_new);
    }

    $UserSelectedAddressArr = array();
    if (count($db_userdata_addressarr) > 0) {
        for ($i = 0; $i < count($db_userdata_addressarr); $i++) {
            $iUserAddressId = $db_userdata_addressarr[$i]['iUserAddressId'];
            $sqlo = "SELECT ord.iOrderId from orders as ord LEFT JOIN user_address as ua ON ord.iUserAddressId=ua.iUserAddressId WHERE ord.iUserId = '" . $iUserId . "' AND ord.iUserAddressId = '" . $iUserAddressId . "' ORDER BY ord.iOrderId DESC limit 0,1";
            $last_order_Address = $obj->MySQLSelect($sqlo);
            if (count($last_order_Address) > 0) {
                $eStatus = $db_userdata_addressarr[$i]['eStatus'];
                if ($eStatus == "Active") {
                    $vAddressType = $db_userdata_addressarr[$i]['vAddressType'];
                    $vBuildingNo = $db_userdata_addressarr[$i]['vBuildingNo'];
                    $vLandmark = $db_userdata_addressarr[$i]['vLandmark'];
                    $vServiceAddress = $db_userdata_addressarr[$i]['vServiceAddress'];
                    $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
                    $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
                    $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
                    $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
                    $PickUpLatitude = $db_userdata_addressarr[$i]['vLatitude'];
                    $PickUpLongitude = $db_userdata_addressarr[$i]['vLongitude'];
                    $UserSelectedAddressArr['UserSelectedAddress'] = $PickUpAddress;
                    $UserSelectedAddressArr['UserSelectedLatitude'] = $PickUpLatitude;
                    $UserSelectedAddressArr['UserSelectedLongitude'] = $PickUpLongitude;
                    $UserSelectedAddressArr['UserSelectedAddressId'] = $iUserAddressId;
                    break;
                }
            }
        }
    }

    if (count($UserSelectedAddressArr) == 0) {
        $vAddressType = $db_userdata_addressarr[0]['vAddressType'];
        $vBuildingNo = $db_userdata_addressarr[0]['vBuildingNo'];
        $vLandmark = $db_userdata_addressarr[0]['vLandmark'];
        $vServiceAddress = $db_userdata_addressarr[0]['vServiceAddress'];
        $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
        $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
        $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
        $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
        $PickUpLatitude = $db_userdata_addressarr[0]['vLatitude'];
        $PickUpLongitude = $db_userdata_addressarr[0]['vLongitude'];
        $UserSelectedAddressArr['UserSelectedAddress'] = $PickUpAddress;
        $UserSelectedAddressArr['UserSelectedLatitude'] = $PickUpLatitude;
        $UserSelectedAddressArr['UserSelectedLongitude'] = $PickUpLongitude;
        $UserSelectedAddressArr['UserSelectedAddressId'] = $iUserAddressId;
    }

    return $UserSelectedAddressArr;
}

function GenerateUniqueOrderNo() {
    global $generalobj, $obj, $tconfig;
    $random = substr(number_format(time() * rand(), 0, '', ''), 0, 10);
    $str = "select iOrderId from orders where vOrderNo ='" . $random . "'";
    $db_str = $obj->MySQLSelect($str);
    if (count($db_str) > 0) {
        $Generateuniqueorderno = GenerateUniqueOrderNo();
    } else {
        $Generateuniqueorderno = $random;
    }

    return $Generateuniqueorderno;
}

function GenerateUniqueTripNo() {
    global $generalobj, $obj, $tconfig;
    $random = substr(number_format(time() * rand(), 0, '', ''), 0, 10);
    $str = "select iTripId from trips where vRideNo ='" . $random . "'";
    $db_str = $obj->MySQLSelect($str);
    if (count($db_str) > 0) {
        $Generateuniqueorderno = GenerateUniqueTripNo();
    } else {
        $Generateuniqueorderno = $random;
    }

    return $Generateuniqueorderno;
}

function FoodMenuItemBasicPrice($iMenuItemId, $iQty = 1) {
    global $generalobj, $obj, $tconfig;
    $fPrice = 0;
    $str = "select fPrice from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
    $db_price = $obj->MySQLSelect($str);
    if (count($db_price) > 0) {
        $fPrice = $db_price[0]['fPrice'];
        $fPrice = $fPrice * $iQty;
    }

    return $fPrice;
}

function GetFoodMenuItemBasicPrice($iMenuItemId) {
    global $generalobj, $obj, $tconfig;
    $str = "select iFoodMenuId,fPrice,fOfferAmt from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
    $db_price = $obj->MySQLSelect($str);
    $fPrice = $db_price[0]['fPrice'];
    $fOfferAmt = $db_price[0]['fOfferAmt'];
    if ($fOfferAmt > 0) {
        $fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt) / 100);
    } else {
        $fDiscountPrice = $fPrice;
    }
    $fDiscountPrice = round($fDiscountPrice, 2);
    return $fDiscountPrice;
}

function GetFoodMenuItemOptionPrice($iOptionId = "") {
    global $generalobj, $obj, $tconfig;
    if ($iOptionId != "") {
        $str = "select iMenuItemId,fPrice from `menuitem_options` where iOptionId IN(" . $iOptionId . ")";
        $db_price = $obj->MySQLSelect($str);
        $fTotalPrice = 0;
        if (count($db_price) > 0) {
            for ($i = 0; $i < count($db_price); $i++) {
                $fPrice = $db_price[$i]['fPrice'];
                $fTotalPrice = $fTotalPrice + $fPrice;
            }
        }
    } else {
        $fTotalPrice = 0;
    }
    $fTotalPrice = round($fTotalPrice, 2);
    return $fTotalPrice;
}

function GetFoodMenuItemOptionIdPriceString($iOptionId = "") {
    global $generalobj, $obj, $tconfig;
    if ($iOptionId != "") {
        $vOptionIdArr = explode(",", $iOptionId);
        $OptionIdPriceString = "";
        if (count($vOptionIdArr) > 0) {
            for ($i = 0; $i < count($vOptionIdArr); $i++) {
                $OptionId = $vOptionIdArr[$i];
                $str = "select fPrice from `menuitem_options` where iOptionId = '" . $OptionId . "'";
                $db_price = $obj->MySQLSelect($str);
                $fPrice = $db_price[0]['fPrice'];
                $OptionIdPriceString .= $OptionId . "#" . $fPrice . ",";
            }

            $OptionIdPriceString = substr($OptionIdPriceString, 0, -1);
        }
    } else {
        $OptionIdPriceString = "";
    }

    return $OptionIdPriceString;
}

function GetFoodMenuItemAddOnPrice($vAddonId = "") {
    global $generalobj, $obj, $tconfig;
    if ($vAddonId != "") {
        $str = "select iMenuItemId,fPrice from `menuitem_options` where iOptionId IN(" . $vAddonId . ")";
        $db_price = $obj->MySQLSelect($str);
        $fTotalPrice = 0;
        if (count($db_price) > 0) {
            for ($i = 0; $i < count($db_price); $i++) {
                $fPrice = $db_price[$i]['fPrice'];
                $fTotalPrice = $fTotalPrice + $fPrice;
            }
        }
    } else {
        $fTotalPrice = 0;
    }
    $fTotalPrice = round($fTotalPrice, 2);
    return $fTotalPrice;
}

function GetFoodMenuItemAddOnIdPriceString($vAddonId = "") {
    global $generalobj, $obj, $tconfig;
    if ($vAddonId != "") {
        $vAddonIdArr = explode(",", $vAddonId);
        $AddOnIdPriceString = "";
        if (count($vAddonIdArr) > 0) {
            for ($i = 0; $i < count($vAddonIdArr); $i++) {
                $OptionId = $vAddonIdArr[$i];
                $str = "select fPrice from `menuitem_options` where iOptionId = '" . $OptionId . "'";
                $db_price = $obj->MySQLSelect($str);
                $fPrice = $db_price[0]['fPrice'];
                $AddOnIdPriceString .= $OptionId . "#" . $fPrice . ",";
            }

            $AddOnIdPriceString = substr($AddOnIdPriceString, 0, -1);
        }
    } else {
        $AddOnIdPriceString = "";
    }

    return $AddOnIdPriceString;
}

function DisplayFoodMenuItemAddOnIdPriceString($vAddonId = "") {
    global $generalobj, $obj, $tconfig;
    if ($vAddonId != "") {
        $vAddonIdArr = explode(",", $vAddonId);
        $AddOnIdPriceString = "";
        if (count($vAddonIdArr) > 0) {
            for ($i = 0; $i < count($vAddonIdArr); $i++) {
                $OptionId = $vAddonIdArr[$i];
                $str = "select fPrice from `menuitem_options` where iOptionId = '" . $OptionId . "'";
                $db_price = $obj->MySQLSelect($str);
                $fPrice = $db_price[0]['fPrice'];
                $AddOnIdPriceString .= $OptionId . "#" . $fPrice . ",";
            }

            $AddOnIdPriceString = substr($AddOnIdPriceString, 0, -1);
        }
    } else {
        $AddOnIdPriceString = "";
    }

    return $AddOnIdPriceString;
}

function getOrderDetailTotalPrice($iOrderId) {
    global $generalobj, $obj, $tconfig;

    $sql = "SELECT SUM( `fTotalPrice` ) AS totalprice FROM order_details WHERE iOrderId = '" . $iOrderId . "' AND eAvailable = 'Yes'";
    $data = $obj->MySQLSelect($sql);
    $totalprice = $data[0]['totalprice'];

    if ($totalprice == "" || $totalprice == NULL) {
        $totalprice = 0;
    }

    return $totalprice;
}

function getOrderDeliveryCharge($iOrderId, $fSubTotal) {
    global $generalobj, $obj, $tconfig;

    $fDeliveryCharge = 0;
    $sql = "SELECT ord.iUserId,ord.iCompanyId,ua.vLatitude as passengerlat,ua.vLongitude as passengerlong,co.vRestuarantLocationLat as restaurantlat,co.vRestuarantLocationLong as restaurantlong FROM orders as ord LEFT JOIN user_address as ua ON ord.iUserAddressId=ua.iUserAddressId LEFT JOIN company as co ON ord.iCompanyId=co.iCompanyId WHERE ord.iOrderId = '" . $iOrderId . "'";
    $data = $obj->MySQLSelect($sql);

    if (count($data) > 0) {
        $User_Address_Array = array($data[0]['passengerlat'], $data[0]['passengerlong']);
        $iLocationId = GetUserGeoLocationId($User_Address_Array);
        if ($iLocationId > 0) {
            $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '" . $iLocationId . "'";
            $data_location = $obj->MySQLSelect($sql);
            $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius'];
            $distance = distanceByLocation($data[0]['passengerlat'], $data[0]['passengerlong'], $data[0]['restaurantlat'], $data[0]['restaurantlong'], "K");
            if ($distance < $iFreeDeliveryRadius) {
                $fDeliveryCharge = 0;
                return $fDeliveryCharge;
            }
            $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal'];
            if ($fSubTotal > $fFreeOrderPriceSubtotal) {
                $fDeliveryCharge = 0;
                return $fDeliveryCharge;
            }

            $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
            $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];
            $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
            if ($fSubTotal > $fOrderPriceValue) {
                $fDeliveryCharge = $fDeliveryChargeAbove;
                return $fDeliveryCharge;
            } else {
                $fDeliveryCharge = $fDeliveryChargeBelow;
                return $fDeliveryCharge;
            }
        } else {
            $fDeliveryCharge = 0;
            return $fDeliveryCharge;
        }
    }
}

function calculateOrderFarePretip($iOrderId, $preTip) {
    global $generalobj, $obj, $ADMIN_COMMISSION;

    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $couponCode = $data_order[0]['vCouponCode'];
    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iTripId = $data_order[0]['iTripId'];
    $ePaymentOption = $data_order[0]['ePaymentOption'];
    $fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');

    $fSubTotal = getOrderDetailTotalPrice($iOrderId);
    $fOffersDiscount = CalculateOrderDiscountPrice($iOrderId);
    $fDeliveryCharge = getOrderDeliveryCharge($iOrderId, $fSubTotal);
    $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    $fTax = $TaxArr['fTax1'];
    if ($fTax > 0) {
        $ftaxamount = $fSubTotal - $fOffersDiscount + $fPackingCharge;
        $fTax = round((($ftaxamount * $fTax) / 100), 2);
    }

    $totalOrderPrice = $fSubTotal;

    if ($fSubTotal == 0) {
        $fPackingCharge = 0;
        $fDeliveryCharge = 0;
        $fTax = 0;
    }
    //$fTax = 0;
    //$fCommision = 0;
    $fNetTotal = $fSubTotal + $fPackingCharge + $fDeliveryCharge + $fTax;
    $fTotalGenerateFare = $fNetTotal;
    $fOrderFare_For_Commission = $fSubTotal - $fOffersDiscount + $fPackingCharge + $fTax;

    $fCommision = round((($fOrderFare_For_Commission * $ADMIN_COMMISSION) / 100), 2);
    if ($fOffersDiscount > 0) {
        $fNetTotal = $fNetTotal - $fOffersDiscount;
    }

    /* Checking For Passenger Outstanding Amount */
    $fOutStandingAmount = 0;
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    if ($fOutStandingAmount > 0) {
        $fNetTotal = $fNetTotal + $fOutStandingAmount;
        $fTotalGenerateFare = $fTotalGenerateFare + $fOutStandingAmount;
    }
    /* Checking For Passenger Outstanding Amount */

    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
        $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
    }
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $vDiscount = round($discountValue, 1) . ' ' . "%";
            $discountValue = round(($totalOrderPrice * $discountValue), 1) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $totalOrderPrice) {
                $vDiscount = round($totalOrderPrice, 1) . ' ' . $curr_sym;
            } else {
                $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
            }
        }
        //     $fNetTotal = $fNetTotal - $discountValue;
//      if($fNetTotal < 0) {
//				$fNetTotal = 0;
//				//$discountValue = $fNetTotal;
//			}
//			$Order_data[0]['fDiscount'] = $discountValue;
//			$Order_data[0]['vDiscount'] = $vDiscount;   
//		}
        /* Check Coupon Code Total Fare  End */

        /* New code */
        $totalOrderPrice = $totalOrderPrice - $discountValue;
        if ($totalOrderPrice < 0.1) {
            $totalOrderPrice = 0;
            //$discountValue = $fNetTotal;
        }
        $Order_data[0]['fDiscount'] = $discountValue;
        $Order_data[0]['vDiscount'] = $vDiscount;
    }

    $fCommision = 0;
    $fDeliveryCharge = $deliverychargees = getChowcalldeliverycharge();
   // $fTax = round(((($totalOrderPrice + $deliverychargees) * 7 ) / 100), 2);
    $fTax = 0;
    $tipvalue = $preTip;



    $fNetTotal = round(($totalOrderPrice + $fPackingCharge + $deliverychargees + $fTax + $tipvalue), 2);
    $fTotalGenerateFare = $fNetTotal;
    $fSubTotal = $totalOrderPrice;
    /* Check debit wallet For Count Total Fare  Start */
    $CheckUserWallet = $data_order[0]['eCheckUserWallet'];
    if ($ePaymentOption == "Cash" && $CheckUserWallet == "Yes") {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        $user_wallet_debit_amount = 0;
        if ($fNetTotal > $user_available_balance) {
            $fNetTotal = $fNetTotal - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
        } else {
            $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
            $fNetTotal = 0;
        }

        // Update User Wallet
        if ($user_wallet_debit_amount > 0) {
            $vRideNo = $data_order[0]['vOrderNo'];
            $data_wallet['iUserId'] = $iUserId;
            $data_wallet['eUserType'] = "Rider";
            $data_wallet['iBalance'] = $user_wallet_debit_amount;
            $data_wallet['eType'] = "Debit";
            $data_wallet['dDate'] = date("Y-m-d H:i:s");
            $data_wallet['iTripId'] = $iTripId;
            $data_wallet['iOrderId'] = $iOrderId;
            $data_wallet['eFor'] = "Booking";
            $data_wallet['ePaymentStatus'] = "Unsettelled";
            $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING#" . $vRideNo;

            $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate'], $data_wallet['iOrderId']);
            //$obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
        }
    }
    /* Check debit wallet For Count Total Fare  End */
    if ($fNetTotal < 0) {
        $fNetTotal = 0;
        $fTotalGenerateFare = 0;
    }

    $finalFareData['fSubTotal'] = $fSubTotal;
    $finalFareData['fOffersDiscount'] = $fOffersDiscount;
    $finalFareData['fPackingCharge'] = $fPackingCharge;
    $finalFareData['fDeliveryCharge'] = $fDeliveryCharge;
    $finalFareData['fTax'] = $fTax;
    $finalFareData['preTip'] = $tipvalue;
    $finalFareData['fDiscount'] = $Order_data[0]['fDiscount'];
    $finalFareData['vDiscount'] = $Order_data[0]['vDiscount'];
    $finalFareData['fCommision'] = $fCommision;
    $finalFareData['fNetTotal'] = $fNetTotal;
    $finalFareData['fTotalGenerateFare'] = $fTotalGenerateFare;
    $finalFareData['fOutStandingAmount'] = $fOutStandingAmount;
    $finalFareData['fWalletDebit'] = $user_wallet_debit_amount;
    return $finalFareData;
}

function calculateOrderFare($iOrderId) {
    global $generalobj, $obj, $ADMIN_COMMISSION;

    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $couponCode = $data_order[0]['vCouponCode'];
    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iTripId = $data_order[0]['iTripId'];
    $ePaymentOption = $data_order[0]['ePaymentOption'];
    $fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');

    $fSubTotal = getOrderDetailTotalPrice($iOrderId);
    $fOffersDiscount = CalculateOrderDiscountPrice($iOrderId);
    $fDeliveryCharge = getOrderDeliveryCharge($iOrderId, $fSubTotal);
    $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    $fTax = $TaxArr['fTax1'];
    if ($fTax > 0) {
        $ftaxamount = $fSubTotal - $fOffersDiscount + $fPackingCharge;
        $fTax = round((($ftaxamount * $fTax) / 100), 2);
    }
    if ($fSubTotal == 0) {
        $fPackingCharge = 0;
        $fDeliveryCharge = 0;
        $fTax = 0;
    }
    //$fTax = 0;
    //$fCommision = 0;
    $fNetTotal = $fSubTotal + $fPackingCharge + $fDeliveryCharge + $fTax;
    $fTotalGenerateFare = $fNetTotal;
    $fOrderFare_For_Commission = $fSubTotal - $fOffersDiscount + $fPackingCharge + $fTax;

    $fCommision = round((($fOrderFare_For_Commission * $ADMIN_COMMISSION) / 100), 2);
    if ($fOffersDiscount > 0) {
        $fNetTotal = $fNetTotal - $fOffersDiscount;
    }

    /* Checking For Passenger Outstanding Amount */
    $fOutStandingAmount = 0;
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    if ($fOutStandingAmount > 0) {
        $fNetTotal = $fNetTotal + $fOutStandingAmount;
        $fTotalGenerateFare = $fTotalGenerateFare + $fOutStandingAmount;
    }
    /* Checking For Passenger Outstanding Amount */

    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
        $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
    }
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $vDiscount = round($discountValue, 1) . ' ' . "%";
            $discountValue = round(($fNetTotal * $discountValue), 1) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $fNetTotal) {
                $vDiscount = round($fNetTotal, 1) . ' ' . $curr_sym;
            } else {
                $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
            }
        }
        $fNetTotal = $fNetTotal - $discountValue;
        if ($fNetTotal < 0) {
            $fNetTotal = 0;
            //$discountValue = $fNetTotal;
        }
        $Order_data[0]['fDiscount'] = $discountValue;
        $Order_data[0]['vDiscount'] = $vDiscount;
    }
    /* Check Coupon Code Total Fare  End */

    /* Check debit wallet For Count Total Fare  Start */
    $CheckUserWallet = $data_order[0]['eCheckUserWallet'];
    if ($ePaymentOption == "Cash" && $CheckUserWallet == "Yes") {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        $user_wallet_debit_amount = 0;
        if ($fNetTotal > $user_available_balance) {
            $fNetTotal = $fNetTotal - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
        } else {
            $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
            $fNetTotal = 0;
        }

        // Update User Wallet
        if ($user_wallet_debit_amount > 0) {
            $vRideNo = $data_order[0]['vOrderNo'];
            $data_wallet['iUserId'] = $iUserId;
            $data_wallet['eUserType'] = "Rider";
            $data_wallet['iBalance'] = $user_wallet_debit_amount;
            $data_wallet['eType'] = "Debit";
            $data_wallet['dDate'] = date("Y-m-d H:i:s");
            $data_wallet['iTripId'] = $iTripId;
            $data_wallet['iOrderId'] = $iOrderId;
            $data_wallet['eFor'] = "Booking";
            $data_wallet['ePaymentStatus'] = "Unsettelled";
            $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING#" . $vRideNo;

            $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate'], $data_wallet['iOrderId']);
            //$obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
        }
    }
    /* Check debit wallet For Count Total Fare  End */
    if ($fNetTotal < 0) {
        $fNetTotal = 0;
        $fTotalGenerateFare = 0;
    }

    $finalFareData['fSubTotal'] = $fSubTotal;
    $finalFareData['fOffersDiscount'] = $fOffersDiscount;
    $finalFareData['fPackingCharge'] = $fPackingCharge;
    $finalFareData['fDeliveryCharge'] = $fDeliveryCharge;
    $finalFareData['fTax'] = $fTax;
    $finalFareData['fDiscount'] = $Order_data[0]['fDiscount'];
    $finalFareData['vDiscount'] = $Order_data[0]['vDiscount'];
    $finalFareData['fCommision'] = $fCommision;
    $finalFareData['fNetTotal'] = $fNetTotal;
    $finalFareData['fTotalGenerateFare'] = $fTotalGenerateFare;
    $finalFareData['fOutStandingAmount'] = $fOutStandingAmount;
    $finalFareData['fWalletDebit'] = $user_wallet_debit_amount;
    return $finalFareData;
}

//// new added
function getPriceUserCurrency($iMemberId, $eUserType = "Passenger", $fPrice, $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;

    $returnArr = array();
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId, $iOrderId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId, $iOrderId);
    }

    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $Ratio = $UserDetailsArr['Ratio'];
    $fPrice = round(($fPrice * $Ratio), 2);
    $fPricewithsymbol = $currencySymbol . " " . $fPrice;

    $returnArr['fPrice'] = $fPrice;
    $returnArr['fPricewithsymbol'] = $fPricewithsymbol;
    $returnArr['currencySymbol'] = $currencySymbol;
    return $returnArr;
}

function DisplayOrderDetailItemList($iOrderDetailId, $iMemberId, $eUserType = "Passenger", $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;

    $returnArr = array();
    $ssql = "";
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId, $iOrderId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId, $iOrderId);
    }

    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $Ratio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];

    $sql = "select od.*,mi.vItemType_" . $vLang . " as MenuItem from `order_details` as od LEFT JOIN  `menu_items` as mi ON od.iMenuItemId=mi.iMenuItemId where od.iOrderDetailId='" . $iOrderDetailId . "'";
    $data_order_detail = $obj->MySQLSelect($sql);
    $MenuItem = $data_order_detail[0]['MenuItem'];
    $fPrice = $data_order_detail[0]['fOriginalPrice'];
    $iFoodMenuName = DisplayCategoryBYItem($data_order_detail[0]['iFoodMenuId']);
    //$fPrice = $data_order_detail[0]['fOriginalPrice']+$data_order_detail[0]['vOptionPrice']+$data_order_detail[0]['vAddonPrice'];
    $eAvailable = $data_order_detail[0]['eAvailable'];
    $fPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $fPrice, $iOrderId);
    $fPrice = $fPriceArr['fPricewithsymbol'];
    $vsymbol = $fPriceArr['currencySymbol'];
    $fPricewithoutsymbol = $fPriceArr['fPrice'];
    $fTotalprice = $fPricewithoutsymbol * $data_order_detail[0]['iQty'];
    $returnArr['iQty'] = $data_order_detail[0]['iQty'];
    $returnArr['MenuItem'] = $MenuItem;
    $returnArr['fPrice'] = $fPrice;
    $returnArr['fTotPrice'] = $vsymbol . " " . formatnum($fTotalprice);
    $returnArr['eAvailable'] = $eAvailable;
    $returnArr['category'] = $iFoodMenuName;
    $returnArr['iOrderDetailId'] = $iOrderDetailId;

    if ($iOrderId > 0) {
        $sqlo = "select fOfferType,fOfferAppyType from `orders` where iOrderId = '" . $iOrderId . "'";
        $db_orderdata = $obj->MySQLSelect($sqlo);
        $fOfferType = $db_orderdata[0]['fOfferType'];
        $fOfferAppyType = $db_orderdata[0]['fOfferAppyType'];
        $TotalDiscountPrice = "";
        if (($fOfferAppyType == "None" && ($fOfferType == "Flat" || $fOfferType == "")) || $fOfferType == "Percentage") {
            $fTotalDiscountPrice = $data_order_detail[0]['fTotalDiscountPrice'];
            $TotalPrice = $data_order_detail[0]['fTotalPrice'];
            if ($fTotalDiscountPrice > 0) {
                $Strikeprice = ($TotalPrice - $fTotalDiscountPrice) * $Ratio;
                $TotalDiscountPrice = $vsymbol . " " . formatnum($Strikeprice);
            }
        }
        $returnArr['TotalDiscountPrice'] = $TotalDiscountPrice;
    }

    $vOptionId = $data_order_detail[0]['vOptionId'];
    if ($vOptionId != "") {
        $vOptionName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $vOptionId, '', 'true');
        $vOptionPrice = $data_order_detail[0]['vOptionPrice'];
        $vOptionPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $vOptionPrice, $iOrderId);
        $vOptionPrice = $vOptionPriceArr['fPricewithsymbol'];
        $returnArr['vOptionName'] = $vOptionName;
        $returnArr['vOptionPrice'] = $vOptionPrice;
    } else {
        $returnArr['vOptionName'] = "";
        $returnArr['vOptionPrice'] = "";
    }

    $tOptionIdOrigPrice = $data_order_detail[0]['tOptionIdOrigPrice'];
    if ($tOptionIdOrigPrice != "") {
        $tOptionItemsArr = array();
        $tOptionItemsDetailArr = explode(",", $tOptionIdOrigPrice);
        for ($i = 0; $i < count($tOptionItemsDetailArr); $i++) {
            $tOptionItemsStrArr = explode("#", $tOptionItemsDetailArr[$i]);
            $tOptionItemsId = $tOptionItemsStrArr[0];
            $tOptionItemsPrice = $tOptionItemsStrArr[1];
            $tOptionItemsPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $tOptionItemsPrice, $iOrderId);
            $tOptionItemPrice = $tOptionItemsPriceArr['fPricewithsymbol'];
            $tOptionItemName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $tOptionItemsId, '', 'true');
            $tOptionCatName = get_value('menuitem_options', 'vOptionCat', 'iOptionId', $tOptionItemsId, '', 'true');
            $tOptionItemsArr[$i]['vOptionName'] = $tOptionItemName;
            $tOptionItemsArr[$i]['vOptionPrice'] = $tOptionItemPrice;
            $tOptionItemsArr[$i]['vOptionCat'] = $tOptionCatName;
        }
        $returnArr['vOptionItemArr'] = $tOptionItemsArr;
    } else {
        $returnArr['vOptionItemArr'] = array();
    }





    $tAddOnIdOrigPrice = $data_order_detail[0]['tAddOnIdOrigPrice'];
    if ($tAddOnIdOrigPrice != "") {
        $AddonItemsArr = array();
        $AddonItemsDetailArr = explode(",", $tAddOnIdOrigPrice);
        for ($i = 0; $i < count($AddonItemsDetailArr); $i++) {
            $AddonItemsStrArr = explode("#", $AddonItemsDetailArr[$i]);
            $AddonItemsId = $AddonItemsStrArr[0];
            $AddonItemsPrice = $AddonItemsStrArr[1];
            $AddonItemsPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $AddonItemsPrice, $iOrderId);
            $AddonItemPrice = $AddonItemsPriceArr['fPricewithsymbol'];
            $AddonItemName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $AddonItemsId, '', 'true');
            $AddonItemsArr[$i]['vAddOnItemName'] = $AddonItemName;
            $AddonItemsArr[$i]['AddonItemPrice'] = $AddonItemPrice;
        }
        $returnArr['AddOnItemArr'] = $AddonItemsArr;
    } else {
        $returnArr['AddOnItemArr'] = array();
    }

    return $returnArr;
}

function DisplayCategoryBYItem($iFoodMenuId) {
    global $obj, $generalobj;
    if (!empty($iFoodMenuId)) {
        $sql = "SELECT `vMenu_EN` FROM `food_menu` WHERE `iFoodMenuId` = $iFoodMenuId;";
        $data = $obj->MySQLSelect($sql);
        return $data[0]['vMenu_EN'];
    } else {
        return '';
    }
}

function DisplayOrderDetailItemList_ForReorder($iOrderDetailId, $iMemberId, $eUserType = "Passenger", $iCompanyId) {
    global $obj, $generalobj, $tconfig;

    $returnArr = array();
    $ssql = "";
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId);
    }

    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $Ratio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];

    $sql = "select od.*,mi.vItemType_" . $vLang . " as MenuItem,mi.vImage,mi.eFoodType from `order_details` as od LEFT JOIN  `menu_items` as mi ON od.iMenuItemId=mi.iMenuItemId where od.iOrderDetailId='" . $iOrderDetailId . "'";
    $data_order_detail = $obj->MySQLSelect($sql);


    $MenuItem = $data_order_detail[0]['MenuItem'];
    $iMenuItemId = $data_order_detail[0]['iMenuItemId'];
    $iFoodMenuName = DisplayCategoryBYItem($data_order_detail[0]['iFoodMenuId']);
    //$fPrice = GetFoodMenuItemBasicPrice($data_order_detail[0]['iMenuItemId']);
    $fPrice = FoodMenuItemBasicPrice($data_order_detail[0]['iMenuItemId']);
    $eAvailable = $data_order_detail[0]['eAvailable'];
    $fPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $fPrice);
    $fPrice = $fPriceArr['fPrice'];
    $vsymbol = $fPriceArr['currencySymbol'];
    $fPricewithoutsymbol = $fPriceArr['fPrice'];
    $fTotalprice = $fPricewithoutsymbol * $data_order_detail[0]['iQty'];
    $returnArr['iQty'] = $data_order_detail[0]['iQty'];
    $returnArr['MenuItem'] = $MenuItem;
    $returnArr['iMenuItemId'] = $data_order_detail[0]['iMenuItemId'];
    $returnArr['eFoodType'] = $data_order_detail[0]['eFoodType'];
    $returnArr['category'] = $iFoodMenuName;
    $returnArr['iFoodMenuId'] = $data_order_detail[0]['iFoodMenuId'];
    $returnArr['fPrice'] = $fPrice;
    $returnArr['fTotPrice'] = $vsymbol . " " . $fTotalprice;
    $returnArr['eAvailable'] = $eAvailable;
    $returnArr['iOrderDetailId'] = $iOrderDetailId;
    $returnArr['vImage'] = "";
    if ($data_order_detail[0]['vImage'] != "") {
        $returnArr['vImage'] = $tconfig["tsite_upload_images_menu_item"] . "/" . $data_order_detail[0]['vImage'];
    }

    $vOptionId = $data_order_detail[0]['vOptionId'];
    if ($vOptionId != "") {
        $vOptionName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $vOptionId, '', 'true');
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        $vOptionPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $vOptionPrice);
        $vOptionPrice = $vOptionPriceArr['fPrice'];
        $returnArr['vOptionId'] = $vOptionId;
        $returnArr['vOptionName'] = $vOptionName;
        $returnArr['vOptionPrice'] = $vOptionPrice;
    } else {
        $returnArr['vOptionId'] = "";
        $returnArr['vOptionName'] = "";
        $returnArr['vOptionPrice'] = "";
    }

    $tAddOnIdOrigPrice = $data_order_detail[0]['tAddOnIdOrigPrice'];
    if ($tAddOnIdOrigPrice != "") {
        $AddonItemsArr = array();
        $AddonItemsDetailArr = explode(",", $tAddOnIdOrigPrice);
        $AddonItemPrice_Total = 0;
        for ($i = 0; $i < count($AddonItemsDetailArr); $i++) {
            $AddonItemsStrArr = explode("#", $AddonItemsDetailArr[$i]);
            $AddonItemsId = $AddonItemsStrArr[0];
            $AddonItemsPrice = GetFoodMenuItemAddOnPrice($AddonItemsId);
            $AddonItemPrice_Total = $AddonItemPrice_Total + $AddonItemsPrice;
            $AddonItemsPriceArr_Total = getPriceUserCurrency($iMemberId, $eUserType, $AddonItemPrice_Total);
            $AddonItemPrice_Total = $AddonItemsPriceArr_Total['fPrice'];
            $AddonItemName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $AddonItemsId, '', 'true');
            $AddonItemsArr[$i]['vAddonId'] = $AddonItemsId;
            $AddonItemsArr[$i]['vAddOnItemName'] = $AddonItemName;
            $AddonItemsArr[$i]['AddonItemPrice'] = $AddonItemPrice_Total;
        }
        $returnArr['AddOnItemArr'] = $AddonItemsArr;
    } else {
        $returnArr['AddOnItemArr'] = array();
    }

    ## Return Selected  ##
    /* $returnArr['options'] = array();
      $returnArr['addon'] = array();
      $sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '".$iCompanyId."' AND fm.eStatus = 'Active' AND mi.eAvailable = 'Yes' AND mi.iMenuItemId = '".$iMenuItemId."'";
      $db_options_data = $obj->MySQLSelect($sql);
      if(count($db_options_data) > 0){
      for($i=0;$i<count($db_options_data);$i++){
      $fPrice = $db_options_data[$i]['fPrice'];
      $fUserPrice = number_format($fPrice*$Ratio,2);
      $fUserPriceWithSymbol = $currencySymbol." ".$fUserPrice;
      $db_options_data[$i]['fUserPrice'] = $fUserPrice;
      $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
      if($db_options_data[$i]['eOptionType'] == "Options"){
      $returnArr['options'][] = $db_options_data[$i];
      }
      if($db_options_data[$i]['eOptionType'] == "Addon"){
      $returnArr['addon'][] = $db_options_data[$i];
      }
      }
      } */

    ## Get Menu Items Array ##
    $returnArr['menu_items'] = array();
    $sqlf = "SELECT iMenuItemId,iFoodMenuId,vItemType_" . $vLang . " as vItemType,vItemDesc_" . $vLang . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder FROM menu_items WHERE iMenuItemId = '" . $iMenuItemId . "'";
    $db_item_data = $obj->MySQLSelect($sqlf);
    if (count($db_item_data) > 0) {
        $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, $iMemberId, "Display", "", "");
        $fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
        $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
        $db_item_data[0]['fOfferAmt'] = $fOfferAmt;
        if ($fOfferAmt > 0) {
            $fDiscountPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
            $StrikeoutPrice = round($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
            $db_item_data[0]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
            $db_item_data[0]['fDiscountPrice'] = formatNum($fDiscountPrice);
            $db_item_data[0]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
            $db_item_data[0]['currencySymbol'] = $currencySymbol;
        } else {
            $db_item_data[0]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
            $db_item_data[0]['fDiscountPrice'] = formatNum($fPrice);
            $db_item_data[0]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
            $db_item_data[0]['currencySymbol'] = $currencySymbol;
        }
        if ($db_item_data[0]['vImage'] != "") {
            $db_item_data[0]['vImage'] = $tconfig["tsite_upload_images_menu_item"] . "/" . $db_item_data[0]['vImage'];
        }
        $MenuItemOptionToppingArr = GetMenuItemOptionsTopping($db_item_data[0]['iMenuItemId'], $currencySymbol, $Ratio, $vLang);
        $db_item_data[0]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
        //echo "<pre>";print_r($MenuItemOptionToppingArr);exit;
        $returnArr['menu_items'] = $db_item_data[0];
    }

    ## Get Menu Items Array ##

    return $returnArr;
}

function GetUserGeoLocationId($Address_Array) {
    global $generalobj, $obj;

    $iLocationId = "0";
    if (!empty($Address_Array)) {
        $sqlaa = "SELECT * FROM location_master WHERE eStatus='Active' AND eFor = 'UserDeliveryCharge'";
        $allowed_data = $obj->MySQLSelect($sqlaa);
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {

                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        $iLocationId = $val['iLocationId'];
                        break;
                    }
                }
            }
        }
    }
    return $iLocationId;
}

function OrderTotalEarningForChowcallDriver($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone) {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();
    $vConvertFromDate = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $conditonalFields = 'iDriverId';
    $UserDetailsArr = getDriverCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];
    //$priceRatio = $UserDetailsArr['Ratio'];
    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fNetTotal, fCommision, iStatusCode, fRatio_" . $currencycode . " as Ratio,fDriverPaidAmount, posttip,fpretip FROM `orders` WHERE (DATE(tOrderRequestDate) BETWEEN '$vConvertFromDate' AND '$vConvertToDate') AND $conditonalFields='$iGeneralUserId' AND  `iStatusCode` =6";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    $TotalTip = 0;
    $TotalReimbursement = 0;
    $TotalBonus = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $OrderId = $value['iOrderId'];
        $iStatusCode = $value['iStatusCode'];
        /* $fDriverPaidAmount = $value['fDriverPaidAmount'];
          $subquery = "SELECT fDeliveryCharge FROM trips WHERE iOrderId = '" . $OrderId . "'";
          $DriverCharge = $obj->MySQLSelect($subquery);
          if ($iStatusCode == '7' || $iStatusCode == '8') {
          $EarningFare = $fDriverPaidAmount;
          } else {
          $EarningFare = $DriverCharge[0]['fDeliveryCharge'];
          }
          $EarningFare = $EarningFare * $priceRatio; */

        $tipValue = 0;
        $totalEarningDriver = 0;
        //pretip postip and bonus
        $tipValue = $value['fpretip'] + $value['posttip'];
        if ($tipValue > 0) {
            $totalEarningDriver = $totalEarningDriver + $tipValue;
            $TotalTip = $TotalTip + $tipValue;
        }





        //Reimbursement 
        $reimbursement = calculateDriverReimbursement($value['iOrderId']);
        if ($reimbursement > 0) {
            $totalEarningDriver = $totalEarningDriver + $reimbursement;
            $TotalReimbursement = $TotalReimbursement + $reimbursement;
        }

        //bONUS
        $RatingBonus = calculateDriverRatingBonus($value['iOrderId']);
        if ($RatingBonus > 0) {
            $totalEarningDriver = $totalEarningDriver + $RatingBonus;
            $TotalBonus = $TotalBonus + $RatingBonus;
        }

        $ToTalEarning += $totalEarningDriver;
    }

    $result = array(
        'totalEarning' => $ToTalEarning,
        'totaltip' => $TotalTip,
        'totalreimbursement' => $TotalReimbursement,
        'totalbonus' => $TotalBonus,
    );

    return $result;
}

function getOrderFare($iOrderId, $eUserType = "Passenger", $IS_FROM_HISTORY = "No") {
    global $generalobj, $obj;
    $OrderFareDetailsArr = array();
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);

    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($data_order[0]['iUserId'], $iOrderId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($data_order[0]['iDriverId'], $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($data_order[0]['iCompanyId'], $iOrderId);
    }

    $vSymbol = $UserDetailsArr['currencySymbol'];
    $priceRatio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];

    $languageLabelsArr = getLanguageLabelsArr($vLang, "1");


    $returnArr['subtotal'] = $data_order[0]['fSubTotal'] * $priceRatio;
    $returnArr['fOffersDiscount'] = $data_order[0]['fOffersDiscount'] * $priceRatio;
    $returnArr['fPackingCharge'] = $data_order[0]['fPackingCharge'] * $priceRatio;
    $returnArr['fDeliveryCharge'] = $data_order[0]['fDeliveryCharge'] * $priceRatio;
   // $returnArr['fTax'] = $data_order[0]['fTax'] * $priceRatio;
    $returnArr['fTotalGenerateFare'] = $data_order[0]['fTotalGenerateFare'] * $priceRatio;
    $returnArr['fDiscount'] = $data_order[0]['fDiscount'] * $priceRatio;
    $returnArr['fCommision'] = $data_order[0]['fCommision'] * $priceRatio;
    $returnArr['fNetTotal'] = $data_order[0]['fNetTotal'] * $priceRatio;
    $returnArr['fWalletDebit'] = $data_order[0]['fWalletDebit'] * $priceRatio;
    $returnArr['fOutStandingAmount'] = $data_order[0]['fOutStandingAmount'] * $priceRatio;
    $returnArr['fDriverPaidAmount'] = $data_order[0]['fDriverPaidAmount'] * $priceRatio;
    $returnArr['fpretip'] = $data_order[0]['fpretip'] * $priceRatio;
    $returnArr['posttip'] = $data_order[0]['posttip'] * $priceRatio;
    $TotalDriverEarning = round(($data_order[0]['fpretip'] * $priceRatio) + ($data_order[0]['posttip'] * $priceRatio), 2);
    $subtotal = formatNum($returnArr['subtotal']);
    $fOffersDiscount = formatNum($returnArr['fOffersDiscount']);
    $fPackingCharge = formatNum($returnArr['fPackingCharge']);
    $fDeliveryCharge = formatNum($returnArr['fDeliveryCharge']);
    //$fTax = formatNum($returnArr['fTax']);
    $fTotalGenerateFare = formatNum($returnArr['fTotalGenerateFare']);
    $fDiscount = formatNum($returnArr['fDiscount']);
    $fCommision = formatNum($returnArr['fCommision']);
    $fWalletDebit = formatNum($returnArr['fWalletDebit']);
    $fOutStandingAmount = formatNum($returnArr['fOutStandingAmount']);
    $fNetTotal = formatNum($returnArr['fNetTotal']);

    $EarningAmount = $returnArr['fTotalGenerateFare'] - $returnArr['fOffersDiscount'] - $returnArr['fDeliveryCharge'] - $returnArr['fCommision'] - $returnArr['fOutStandingAmount'];


    $arrindex = 0;
    if ($eUserType == "Driver") {
        $tripsql = "SELECT fDeliveryCharge,eDriverPaymentStatus FROM trips WHERE iOrderId='" . $iOrderId . "'";
        $DataTrips = $obj->MySQLSelect($tripsql);
        if ($data_order[0]['iStatusCode'] == '7' || $data_order[0]['iStatusCode'] == '8') {
            if ($DataTrips[0]['eDriverPaymentStatus'] == 'Settelled') {
                $fDeliveryChargeDriver = $returnArr['fDriverPaidAmount'];
            } else {
                $fDeliveryChargeDriver = $DataTrips[0]['fDeliveryCharge'];
            }
        } else {
            $fDeliveryChargeDriver = $DataTrips[0]['fDeliveryCharge'];
        }

        $returnArr['fDeliveryChargeDriver'] = $fDeliveryChargeDriver * $priceRatio;
        $fDeliveryChargesDriver = formatNum($returnArr['fDeliveryChargeDriver']);

        $tipValue = 0;
        $totalEarningDriver = 0;
        //pretip postip and bonus
       // $tipValue = $data_order[0]['fpretip'] + $data_order[0]['posttip'];
       // if ($tipValue > 0) {
            //$totalEarningDriver = $totalEarningDriver + $tipValue;
            //$OrderFareDetailsArr[$arrindex]['Tip'] = $vSymbol . " " . $tipValue;
           // $arrindex++;
        //}





        //Reimbursement 
        $reimbursement = calculateDriverReimbursement($iOrderId);
        if ($reimbursement > 0) {
            $totalEarningDriver = $totalEarningDriver + $reimbursement;
            $OrderFareDetailsArr[$arrindex]['Reimbursement'] = $vSymbol . " " . $reimbursement;
            $arrindex++;
        }

        //bONUS
        $RatingBonus = calculateDriverRatingBonus($iOrderId);
        if ($RatingBonus > 0) {
            $totalEarningDriver = $totalEarningDriver + $RatingBonus;
            $OrderFareDetailsArr[$arrindex]['Bonus'] = $vSymbol . " " . $RatingBonus;
            $arrindex++;
        }
        //pretip post tip bonus
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DELIVERY_EARNING_APP']] = $vSymbol . " " . $totalEarningDriver;

        //$OrderFareDetailsArr[$arrindex]['totaldriverearning'] = $TotalDriverEarning;
        //$OrderFareDetailsArr[$arrindex]['deliverycharge'] = ( $data_order[0]['fDeliveryCharge'] * $priceRatio);
        //$OrderFareDetailsArr[$arrindex]['pretip'] = ($data_order[0]['fpretip'] * $priceRatio);
        //$OrderFareDetailsArr[$arrindex]['posttip'] = ($data_order[0]['posttip'] * $priceRatio);
        // $arrindex++;
    } else if ($eUserType == "Company") {
        if ($data_order[0]['fSubTotal'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $vSymbol . " " . $subtotal;
            $arrindex++;
        }

        if ($data_order[0]['fOffersDiscount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fOffersDiscount;
            $arrindex++;
        }

        if ($data_order[0]['fPackingCharge'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = $vSymbol . " " . $fPackingCharge;
            $arrindex++;
        }

        //if ($data_order[0]['fTax'] > 0) {
           // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $vSymbol . " " . $fTax;
            //$arrindex++;
        //}



        if ($IS_FROM_HISTORY == "No") {
            //if($data_order[0]['fTotalGenerateFare'] > 0){
            //$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']." ".$payment_str] = $vSymbol." ".$fTotalGenerateFare;
            $TotalDisplayAmount = $returnArr['subtotal'] - $returnArr['fOffersDiscount'] + $returnArr['fPackingCharge'];// + $returnArr['fTax'];
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT'] . " " . $payment_str] = $vSymbol . " " . formatnum($TotalDisplayAmount);
            $arrindex++;
            //}
        } else {
            if ($data_order[0]['fCommision'] > 0) {
                $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_Commision']] = "-" . $vSymbol . " " . $fCommision;
                $arrindex++;
            }
            if ($EarningAmount > 0) {
                $EarningAmount = formatNum($EarningAmount);
                if ($data_order[0]['iStatusCode'] == '7' || $data_order[0]['iStatusCode'] == '8') {
                    $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_EXPECTED_EARNING'] . " " . $payment_str] = $vSymbol . " " . $EarningAmount;
                } else {
                    $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_AMT_EARNED'] . " " . $payment_str] = $vSymbol . " " . $EarningAmount;
                }
                $arrindex++;
            }
        }


        /* if ($fNetTotal > 0) {
          $OrderFareDetailsArr[$arrindex]['SubTotal'] = $vSymbol.$fNetTotal;
          $arrindex++;
          } */
    } else {
        if ($data_order[0]['fSubTotal'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $vSymbol . " " . $subtotal;
            $arrindex++;
        }
        //pretip postip and bonus
        if ($data_order[0]['fpretip'] > 0) {
            //$OrderFareDetailsArr[$arrindex]['Pre Tip'] = $vSymbol . " " . $data_order[0]['fpretip'];
            $arrindex++;
        }

        if ($data_order[0]['posttip'] > 0) {
            ///$OrderFareDetailsArr[$arrindex]['Post Tip'] = $vSymbol . " " . $data_order[0]['posttip'];
            $arrindex++;
            //$fNetTotal = $fNetTotal + $data_order[0]['posttip'];
        }

        //pretip post tip bonus

        if ($data_order[0]['fOffersDiscount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fOffersDiscount;
            $arrindex++;
        }

        if ($data_order[0]['fPackingCharge'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = $vSymbol . " " . $fPackingCharge;
            $arrindex++;
        }

        if ($data_order[0]['fDeliveryCharge'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']] = $vSymbol . " " . $fDeliveryCharge;
            $arrindex++;
        }

        if ($data_order[0]['fOutStandingAmount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = $vSymbol . " " . $fOutStandingAmount;
            $arrindex++;
        }

        //if ($data_order[0]['fTax'] > 0) {
            //$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $vSymbol . " " . $fTax;
            //$arrindex++;
        //}

        if ($data_order[0]['fDiscount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fDiscount;
            $arrindex++;
        }

        if ($data_order[0]['fWalletDebit'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "-" . $vSymbol . " " . $fWalletDebit;
            $arrindex++;
        }

        if ($IS_FROM_HISTORY == "No") {
            //if($data_order[0]['fTotalGenerateFare'] > 0){
            //$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']." ".$payment_str] = $vSymbol." ".$fTotalGenerateFare;
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT'] . " " . $payment_str] = $vSymbol . " " . $fNetTotal;
            $arrindex++;
            //}
        } else {
            if ($data_order[0]['fCommision'] > 0) {
                $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_Commision']] = "-" . $vSymbol . " " . $fCommision;
                $arrindex++;
            }
            if ($EarningAmount > 0) {
                $EarningAmount = formatNum($EarningAmount);
                $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_AMT_EARNED'] . " " . $payment_str] = $vSymbol . " " . $EarningAmount;
                $arrindex++;
            }
        }


        /* if ($fNetTotal > 0) {
          $OrderFareDetailsArr[$arrindex]['SubTotal'] = $vSymbol.$fNetTotal;
          $arrindex++;
          } */
    }
    return $OrderFareDetailsArr;
}

function DisplayOrderDetailList($iOrderId, $vTimeZone = 'Asia/Kolkata', $UserType = "Company", $IS_FROM_HISTORY = "No") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();

    $sql = "SELECT o.fSubTotal,o.iOrderId,o.vOrderNo,o.fNetTotal,o.vInstruction,o.iCompanyId,o.iServiceId,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.iStatusCode,o.ePaid,o.ePaymentOption,o.iUserAddressId,o.gatewayused,concat(ru.vName,' ',ru.vLastName) as UserName,ru.vPhone,ru.vPhoneCode,ru.vLang,o.eIsScheduled,o.dScdeliveryTime,o.dScdeliveryDate FROM orders as o LEFT JOIN register_user as ru on ru.iUserId = o.iUserId WHERE o.iOrderId = '" . $iOrderId . "'";
    $db_order = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($db_order);exit;
    if ($UserType == "Driver") {
        $query = "SELECT vImage,eImgSkip,iVehicleTypeId FROM `trips` WHERE iOrderId = '" . $iOrderId . "'";
        $TripsData = $obj->MySQLSelect($query);
        $Vehiclefields = "iVehicleTypeId,vVehicleType";
        $VehicleTypeDataDriver = get_value('vehicle_type', $Vehiclefields, 'iVehicleTypeId', $TripsData[0]['iVehicleTypeId']);
    }
    foreach ($db_order as $key => $value) {
        $ssql1 = '';
        if ($UserType == "Passenger") {
            $iMemberId = $value['iUserId'];
            $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId, $iOrderId);
        } else if ($UserType == "Driver") {
            $iMemberId = $value['iDriverId'];
            $ssql1 .= "AND eAvailable = 'Yes'";
            $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId, $iOrderId);
        } else {
            $iMemberId = $value['iCompanyId'];
            $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId, $iOrderId);
        }
        $vcurSymbol = $UserDetailsArr['currencySymbol'];
        $curpriceRatio = $UserDetailsArr['Ratio'];
        $vLangu = $UserDetailsArr['vLang'];
        $languageLabelsArr = getLanguageLabelsArr($vLangu, "1");
        $iDriverId = $value['iDriverId'];
        $returnArr[$key]['DriverName'] = "";

        if ($iDriverId > 0) {
            $DriverData = get_value('register_driver', 'vName,vLastName', 'iDriverId', $iDriverId);
            $DriverName = $DriverData[0]['vName'] . " " . $DriverData[0]['vLastName'];
            $returnArr[$key]['DriverName'] = $DriverName;
        }
        $returnArr[$key]['iOrderId'] = $iOrderId;
        $returnArr[$key]['iUserId'] = $value['iUserId'];
        $returnArr[$key]['iCompanyId'] = $value['iCompanyId'];
        $returnArr[$key]['vOrderNo'] = $value['vOrderNo'];
        $returnArr[$key]['gatewayused'] = $value['gatewayused'];
        $returnArr[$key]['iStatusCode'] = $value['iStatusCode'];
        $returnArr[$key]['vInstruction'] = $value['vInstruction'];
        $returnArr[$key]['eIsScheduled'] = $value['eIsScheduled'];
        $returnArr[$key]['dScdeliveryTime'] = $value['dScdeliveryTime'];
        $returnArr[$key]['dScdeliveryDate'] = $value['dScdeliveryDate'];
		$returnArr[$key]['fSubTotal'] = $vcurSymbol.' '.getOrderDetailSubTotalPrice($iOrderId);
        $returnArr[$key]['fNetTotal'] = $vcurSymbol.' '.$value['fNetTotal'];
        $StatusDisplay = getOrderStatus($iOrderId);
        if ($StatusDisplay == 'Refunded') {
            $StatusDisplay = 'Cancelled';
        }
        $servFields = 'iServiceId,vServiceName_' . $vLangu . ' as vServiceName';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $value['iServiceId']);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['vServiceName'])) {
                $returnArr[$key]['vServiceCategoryName'] = '';
            } else {
                $returnArr[$key]['vServiceCategoryName'] = $ServiceCategoryData[0]['vServiceName'];
            }
        } else {
            $returnArr[$key]['vServiceCategoryName'] = '';
        }
        $returnArr[$key]['vStatus'] = $StatusDisplay;
        $returnArr[$key]['UserName'] = $value['UserName'];
        $returnArr[$key]['UserPhone'] = '+' . $value['vPhoneCode'] . $value['vPhone'];
        $returnArr[$key]['ePaid'] = $value['ePaid'];
        $returnArr[$key]['ePaymentOption'] = $value['ePaymentOption'];
        $returnArr[$key]['eConfirm'] = checkOrderStatus($iOrderId, "2");
        $returnArr[$key]['eDecline'] = checkOrderStatus($iOrderId, "9");
        $restFields = 'vCompany,vCaddress as vRestuarantLocation,vPhone,vCode,vRestuarantLocationLat,vRestuarantLocationLong';
        $CompanyData = get_value('company', $restFields, 'iCompanyId', $value['iCompanyId']);
        $returnArr[$key]['vCompany'] = $CompanyData[0]['vCompany'];
        if ($UserType == 'Driver') {
            $returnArr[$key]['RestuarantPhone'] = '+' . $CompanyData[0]['vCode'] . $CompanyData[0]['vPhone'];
        }
        $returnArr[$key]['vRestuarantLocation'] = $CompanyData[0]['vRestuarantLocation'];
        if ($UserType == 'Driver') {
            $returnArr[$key]['RestuarantLat'] = $CompanyData[0]['vRestuarantLocationLat'];
            $returnArr[$key]['RestuarantLong'] = $CompanyData[0]['vRestuarantLocationLong'];
        }
        $UserAddressArr = GetUserAddressDetail($value['iUserId'], "Passenger", $value['iUserAddressId']);
        $returnArr[$key]['DeliveryAddress'] = $UserAddressArr['UserAddress'];

        if ($UserType == 'Driver') {
            $returnArr[$key]['UserAddress'] = $UserAddressArr['UserAddress'];
            $userFields = 'vLatitude,vLongitude';
            $userData = get_value('user_address', $userFields, 'iUserAddressId', $value['iUserAddressId']);
            $returnArr[$key]['UserLatitude'] = $userData[0]['vLatitude'];
            $returnArr[$key]['UserLongitude'] = $userData[0]['vLongitude'];
            $isPhotoUploaded = 'No';
            if (!empty($TripsData)) {
                if ($returnArr[$key]['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'None') {
                    $isPhotoUploaded = 'No';
                } else if ($returnArr[$key]['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'No') {
                    $isPhotoUploaded = 'Yes';
                } else if ($returnArr[$key]['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'Yes') {
                    $isPhotoUploaded = 'Yes';
                } else {
                    $isPhotoUploaded = 'No';
                }
                if ($returnArr[$key]['iStatusCode'] == '5') {
                    $returnArr[$key]['PickedFromRes'] = 'Yes';
                } else {
                    $returnArr[$key]['PickedFromRes'] = 'No';
                }
                $SelectdVehicleTypeId = ($VehicleTypeDataDriver[0]['iVehicleTypeId'] != '') ? $VehicleTypeDataDriver[0]['iVehicleTypeId'] : "";
                $SelectdVehicleType = ($VehicleTypeDataDriver[0]['vVehicleType'] != '') ? $VehicleTypeDataDriver[0]['vVehicleType'] : "";
                $returnArr[$key]['iVehicleTypeId'] = $SelectdVehicleTypeId;
                $returnArr[$key]['vVehicleType'] = $SelectdVehicleType;
            }
            $returnArr[$key]['isPhotoUploaded'] = $isPhotoUploaded;
            $eUnit = getMemberCountryUnit($value['iDriverId'], "Driver");
            if ($eUnit == 'KMs') {
                $fDistance = distanceByLocation($userData[0]['vLatitude'], $userData[0]['vLongitude'], $CompanyData[0]['vRestuarantLocationLat'], $CompanyData[0]['vRestuarantLocationLong'], "K");
            } else {
                $fDistance = distanceByLocation($userData[0]['vLatitude'], $userData[0]['vLongitude'], $CompanyData[0]['vRestuarantLocationLat'], $CompanyData[0]['vRestuarantLocationLong'], "");
            }
            $returnArr[$key]['UserDistance'] = round($fDistance, 2) . " " . $eUnit;
        }

        $serverTimeZone = date_default_timezone_get();
        $date = converToTz($value['tOrderRequestDate'], $vTimeZone, $serverTimeZone, "Y-m-d H:i:s");
        $OrderTime = date('d M, Y h:i A', strtotime($date));

        $returnArr[$key]['tOrderRequestDate_Org'] = $date;
        $returnArr[$key]['tOrderRequestDate'] = $OrderTime;

        if ($value['iDriverId'] == '0') {
            $returnArr[$key]['DriverAssign'] = 'No';
        } else {
            $returnArr[$key]['DriverAssign'] = 'Yes';
        }

        $query = "SELECT iOrderDetailId FROM order_details WHERE iOrderId = '" . $iOrderId . "' $ssql1";
        $orderDetailId = $obj->MySQLSelect($query);
        $returnArr[$key]['TotalItems'] = strval(count($orderDetailId));
        if ($UserType == 'Driver') {
            $ePaid = $value['ePaid'];
            $ePaymentOption = $value['ePaymentOption'];
            $returnArr[$key]['vSymbol'] = $vcurSymbol;
            if ($ePaid == 'Yes' && $ePaymentOption == 'Card') {
                $returnArr[$key]['originalTotal'] = formatNum($value['fNetTotal'] * $curpriceRatio);
                $CardNetTotal = 0;
                $returnArr[$key][$languageLabelsArr['LBL_SUBTOTAL_APP_TXT']] = $vcurSymbol . formatNum($CardNetTotal);
            } else {
                $returnArr[$key][$languageLabelsArr['LBL_SUBTOTAL_APP_TXT']] = $vcurSymbol . formatNum($value['fNetTotal'] * $curpriceRatio);
            }
        }
        foreach ($orderDetailId as $k => $val) {
            $ItemLists[] = DisplayOrderDetailItemList($val['iOrderDetailId'], $iMemberId, $UserType, $iOrderId);
        }
        //echo "<pre>";print_r($ItemLists);exit; 
        $all_data_new = array();
        if ($ItemLists != '') {
            foreach ($ItemLists as $k => $item) {
                $iQty = ($item['iQty'] != '') ? $item['iQty'] : '';
                $MenuItem = ($item['MenuItem'] != '') ? $item['MenuItem'] : '';
                $fTotPrice = ($item['fTotPrice'] != '') ? $item['fTotPrice'] : '';
                $TotalDiscountPrice = ($item['TotalDiscountPrice'] != '') ? $item['TotalDiscountPrice'] : '';
                $eAvailable = ($item['eAvailable'] != '') ? $item['eAvailable'] : '';
                $vOptionItemArr = ($item['vOptionItemArr'] != '') ? $item['vOptionItemArr'] : '';
                $AddOnItemArr = ($item['AddOnItemArr'] != '') ? $item['AddOnItemArr'] : '';
                $iOrderDetailId = ($item['iOrderDetailId'] != '') ? $item['iOrderDetailId'] : '';
                $category = ($item['category'] != '') ? $item['category'] : '';
                $all_data_new[$k]['iOrderDetailId'] = $iOrderDetailId;
                $all_data_new[$k]['iQty'] = $iQty;
                $all_data_new[$k]['MenuItem'] = $MenuItem;
                $all_data_new[$k]['fTotPrice'] = $fTotPrice;
                $all_data_new[$k]['TotalDiscountPrice'] = $TotalDiscountPrice;
                $all_data_new[$k]['eAvailable'] = $eAvailable;
                $all_data_new[$k]['category'] = $category;


                $vOptionName = ($item['vOptionName'] != '') ? $item['vOptionName'] : '';

                $AllOPtions = array();
                $ToptionTitle = array();
                if (!empty($vOptionItemArr)) {
                    foreach ($vOptionItemArr as $optionkey => $optionvalue) {
                        $ToptionTitle[] = $optionvalue['vOptionName'];
                        $AllOPtions[] = array(
                            'vOptionName' => $optionvalue['vOptionName'],
                            'vOptionCat' => 'Options',
                            'vOptionPrice' => $optionvalue['vOptionPrice'],
                        );
                    }
                    $OptionTitle = implode(",", $ToptionTitle);
                } else {
                    $OptionTitle = '';
                }
                $all_data_new[$k]['Options'] = $AllOPtions;

                $AllAddOns = array();
                $addonTitleArr = array();
                if (!empty($AddOnItemArr)) {
                    foreach ($AddOnItemArr as $addonkey => $addonvalue) {
                        $addonTitleArr[] = $addonvalue['vAddOnItemName'];
                        $AllAddOns[] = array(
                            'vOptionName' => $addonvalue['vAddOnItemName'],
                            'vOptionCat' => 'Toppings',
                            'vOptionPrice' => $addonvalue['AddonItemPrice'],
                        );
                    }
                    $addonTitle = implode(",", $addonTitleArr);
                } else {
                    $addonTitle = '';
                }

                $all_data_new[$k]['Addons'] = $AllAddOns;
                if ($OptionTitle != '' && $addonTitle == '') {
                    $all_data_new[$k]['SubTitle'] = $OptionTitle;
                } else if ($OptionTitle == '' && $addonTitle != '') {
                    $all_data_new[$k]['SubTitle'] = $addonTitle;
                } else if ($OptionTitle != '' && $addonTitle != '') {
                    $all_data_new[$k]['SubTitle'] = $OptionTitle . "," . $addonTitle;
                } else {
                    $all_data_new[$k]['SubTitle'] = '';
                }
            }
        }
        $returnArr[$key]['itemlist'] = $all_data_new;
    }
    $orderData = getOrderFare($iOrderId, $UserType, $IS_FROM_HISTORY);
    $returnArr[$key]['FareDetailsArr'] = $orderData;

    return $returnArr;
}

function CheckPromoCode($promoCode, $iUserId) {
    global $generalobj, $obj;

    $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
    $Ratio = $UserDetailsArr['Ratio'];

    $curr_date = @date("Y-m-d");

    $promoCode = strtoupper($promoCode);
    $sql = "SELECT * FROM coupon where eStatus = 'Active' AND vCouponCode = '" . $promoCode . "' ORDER BY iCouponId ASC LIMIT 0,1";
    $data = $obj->MySQLSelect($sql);

    if (count($data) > 0) {
        //$sql="select iOrderId from orders where vCouponCode = '".$promoCode."' and iStatusCode = '6' and iUserId='$iUserId'";
        $sql = "select iOrderId from orders where vCouponCode = '" . $promoCode . "' and iStatusCode NOT IN(11,12) and iUserId='$iUserId'";
        $data_coupon = $obj->MySQLSelect($sql);
        // echo "<pre>";print_r($data_coupon);exit;
        ### Get Coupon Discount Price Details ##
        $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $promoCode, '', 'true');
        $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $promoCode, '', 'true');
        $discountValue = round(($discountValue * $Ratio), 2);
        ### Get Coupon Discount Price Details ##

        if (!empty($data_coupon)) {
            $returnArr['Action'] = "01"; // code is already used one time
            $returnArr["message"] = "LBL_PROMOCODE_ALREADY_USED";
            echo json_encode($returnArr);
            exit;
        } else {
            $eValidityType = $data[0]['eValidityType'];
            $iUsageLimit = $data[0]['iUsageLimit'];
            $iUsed = $data[0]['iUsed'];
            if ($iUsageLimit <= $iUsed) {
                $returnArr['Action'] = "0"; // code is invalid due to Usage Limit
                $returnArr["message"] = "LBL_PROMOCODE_COMPLETE_USAGE_LIMIT";
                echo json_encode($returnArr);
                exit;
            }
            if ($eValidityType == "Permanent") {
                
            } else {
                $dActiveDate = $data[0]['dActiveDate'];
                $dExpiryDate = $data[0]['dExpiryDate'];
                if ($dActiveDate <= $curr_date && $dExpiryDate >= $curr_date) {
                    
                } else {
                    $returnArr['Action'] = "0"; // code is invalid due to expiration
                    $returnArr["message"] = "LBL_PROMOCODE_EXPIRED";
                    echo json_encode($returnArr);
                    exit;
                }
            }
        }
    } else {
        $returnArr['Action'] = "0"; // code is invalid
        $returnArr["message"] = "LBL_INVALID_PROMOCODE";
        echo json_encode($returnArr);
        exit;
    }

    return $promoCode;
}

function getOrderStatus($iOrderId) {
    global $generalobj, $obj;

    $sql = "SELECT os.vStatus_Track FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "'";
    $OrderStatus = $obj->MySQLSelect($sql);

    $vStatus = $OrderStatus[0]['vStatus_Track'];

    return $vStatus;
}

function createOrderLog($iOrderId, $iStatusCode) {
    global $generalobj, $obj;

    $sql = "SELECT * FROM order_status_logs WHERE iOrderId = '" . $iOrderId . "' AND iStatusCode = '" . $iStatusCode . "'";
    $OrderStatuslog = $obj->MySQLSelect($sql);

    if (count($OrderStatuslog) == 0) {
        $data['iOrderId'] = $iOrderId;
        $data['iStatusCode'] = $iStatusCode;
        $data['dDate'] = @date("Y-m-d H:i:s");
        $data['vIP'] = get_client_ip();

        $id = $obj->MySQLQueryPerform("order_status_logs", $data, 'insert');
    } else {
        $id = $OrderStatuslog[0]['iOrderLogId'];
    }

    return $id;
}

function UpdateCardPaymentPendingOrder() {
    global $generalobj, $obj;
    $currentdate = @date("Y-m-d H:i:s");
    $checkdate = date('Y-m-d H:i:s', strtotime("-120 minutes", strtotime($currentdate)));

    $sql = "SELECT iOrderId FROM orders WHERE dDeliveryDate < '" . $checkdate . "' AND iStatusCode = 12 AND ePaymentOption = 'Card'";
    $db_order = $obj->MySQLSelect($sql);
    if (count($db_order) > 0) {
        for ($i = 0; $i < count($db_order); $i++) {
            $iOrderId = $db_order[$i]['iOrderId'];

            $sql = "delete from order_details where iOrderId='" . $iOrderId . "'";
            $obj->sql_query($sql);

            $sqld = "delete from orders where iOrderId='" . $iOrderId . "'";
            $obj->sql_query($sqld);
        }
    }

    return true;
}

function checkOrderStatus($iOrderId, $iStatusCode) {
    global $generalobj, $obj;
    $orderexist = "No";
    $sql = "SELECT count(iOrderLogId) as TotOrderLogId from order_status_logs WHERE iOrderId ='" . $iOrderId . "' AND iStatusCode IN($iStatusCode)";
    $db_status = $obj->MySQLSelect($sql);
    $TotOrderLogId = $db_status[0]['TotOrderLogId'];

    if ($TotOrderLogId > 0) {
        $orderexist = "Yes";
    }

    return $orderexist;
}

function checkOrderRequestStatus($iOrderId) {
    global $generalobj, $obj, $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL;

    $sql = "SELECT * from driver_request WHERE iOrderId ='" . $iOrderId . "'";
    $db_driver_request = $obj->MySQLSelect($sql);

    if (count($db_driver_request) > 0) {
        $sql = "SELECT iDriverId from orders WHERE iOrderId ='" . $iOrderId . "'";
        $db_order_driver = $obj->MySQLSelect($sql);
        $iDriverId = $db_order_driver[0]['iDriverId'];
        if ($iDriverId > 0) {
            $returnArr['Action'] = "1";
            $returnArr["message"] = "LBL_REQUEST_FAILED_TXT";
            $returnArr["message1"] = "DRIVER_ASSIGN";
        } else {
            $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL + 5;
            $currentdate = @date("Y-m-d H:i:s");
            $checkdate = date('Y-m-d H:i:s', strtotime("+" . $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL . " seconds", strtotime($currentdate)));
            $checkdate1 = date('Y-m-d H:i:s', strtotime("-" . $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL . " seconds", strtotime($currentdate)));

            $sql = "SELECT iDriverRequestId from driver_request WHERE iOrderId ='" . $iOrderId . "' AND ( dAddedDate > '" . $checkdate1 . "' AND dAddedDate < '" . $checkdate . "')";
            $db_status = $obj->MySQLSelect($sql);
            if (count($db_status) > 0) {
                $returnArr['Action'] = "0";
                $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
                $returnArr["message1"] = "REQ_PROCESS";
            } else {
                $returnArr['Action'] = "1";
                $returnArr["message"] = "LBL_REQUEST_FAILED_TXT";
                $returnArr["message1"] = "REQ_FAILED";
            }
        }
    } else {
        $returnArr['Action'] = "1";
        $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
        $returnArr["message1"] = "REQ_NOT_FOUND";
    }

    return $returnArr;
}

function get_day_name($timestamp) {
    $date = date('d M Y', $timestamp);

    if ($date == date('d M Y')) {
        $date = 'Today';
    } else if ($date == date('d M Y', strtotime("-1 days"))) {
        $date = 'Yesterday';
    }
    return $date;
}

function checkDistanceBetweenUserCompany($iUserAddressId, $iCompanyId) {
    global $generalobj, $obj, $LIST_RESTAURANT_LIMIT_BY_DISTANCE;

    $sql = "select vLatitude,vLongitude from `user_address` where iUserAddressId = '" . $iUserAddressId . "'";
    $db_userdata = $obj->MySQLSelect($sql);
    $passengeraddlat = $db_userdata[0]['vLatitude'];
    $passengeraddlong = $db_userdata[0]['vLongitude'];

    $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
    $db_companydata = $obj->MySQLSelect($sql);
    $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
    $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];

    $distance = distanceByLocation($passengeraddlat, $passengeraddlong, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
    if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
        $returnArr['Action'] = "0";
        $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
        echo json_encode($returnArr);
        exit;
    }
}

function getremainingtimeorderrequest($iOrderId) {
    global $generalobj, $obj, $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL;

    $sql = "SELECT * from driver_request WHERE iOrderId ='" . $iOrderId . "' ORDER BY iDriverRequestId DESC LIMIT 0,1";
    $db_driver_request = $obj->MySQLSelect($sql);

    $datedifference = 0;
    if (count($db_driver_request) > 0) {
        $currentdate = @date("Y-m-d H:i:s");
        $currentdate = strtotime($currentdate);
        $dAddedDate = $db_driver_request[0]['dAddedDate'];
        $dAddedDate = strtotime($dAddedDate);
        $datedifference = $currentdate - $dAddedDate;
    }

    $Remaining_Time_In_Seconds = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL - $datedifference;
    $Remaining_Time_In_Seconds = $Remaining_Time_In_Seconds + 10;

    if ($datedifference > 30) {
        $Remaining_Time_In_Seconds = 0;
    }

    return $Remaining_Time_In_Seconds;
}

function getTotalOrderDetailItemsCount($iOrderId) {
    global $generalobj, $obj, $tconfig;

    $sql = "SELECT count(iOrderDetailId) as TotalOrderItems FROM order_details WHERE iOrderId = '" . $iOrderId . "'";
    $data = $obj->MySQLSelect($sql);
    $TotalOrderItems = $data[0]['TotalOrderItems'];

    if ($TotalOrderItems == "" || $TotalOrderItems == NULL) {
        $TotalOrderItems = 0;
    }

    return $TotalOrderItems;
}

function OrderTotalEarningForRestaurant($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone) {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();
    $vConvertFromDate = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");

    $conditonalFields = 'iCompanyId';
    $UserDetailsArr = getCompanyCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];
    //$priceRatio = $UserDetailsArr['Ratio'];

    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fCommision, iStatusCode, fNetTotal, fOffersDiscount, fRatio_" . $currencycode . " as Ratio,fRestaurantPaidAmount,fDeliveryCharge FROM `orders` WHERE (DATE(tOrderRequestDate) BETWEEN '$vConvertFromDate' AND '$vConvertToDate') AND $conditonalFields='$iGeneralUserId' AND  `iStatusCode` IN (6, 7, 8, 11, 9)";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    $TotalEarningFare = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $iStatusCode = $value['iStatusCode'];
        $fRestaurantPaidAmount = $value['fRestaurantPaidAmount'];
        if ($iStatusCode == '7' || $iStatusCode == '8') {
            $EarningFare = $fRestaurantPaidAmount;
        } else {
            $EarningFare = $value['fTotalGenerateFare'] - $value['fCommision'] - $value['fOffersDiscount'] - $value['fDeliveryCharge'];
        }
        $EarningFare = $EarningFare * $priceRatio;
        $ToTalEarning += $EarningFare;
        $TotalEarningFare = $ToTalEarning;
        //$TotalEarningFare = $ToTalEarning * $priceRatio;
    }

    return $TotalEarningFare;
}

function OrderTotalEarningForDriver($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone) {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();
    $vConvertFromDate = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $conditonalFields = 'iDriverId';
    $UserDetailsArr = getDriverCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];
    //$priceRatio = $UserDetailsArr['Ratio'];
    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fNetTotal, fCommision, iStatusCode, fRatio_" . $currencycode . " as Ratio,fDriverPaidAmount FROM `orders` WHERE (DATE(tOrderRequestDate) BETWEEN '$vConvertFromDate' AND '$vConvertToDate') AND $conditonalFields='$iGeneralUserId' AND  `iStatusCode` IN (6, 7, 8, 11, 9)";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $OrderId = $value['iOrderId'];
        $iStatusCode = $value['iStatusCode'];
        $fDriverPaidAmount = $value['fDriverPaidAmount'];
        $subquery = "SELECT fDeliveryCharge FROM trips WHERE iOrderId = '" . $OrderId . "'";
        $DriverCharge = $obj->MySQLSelect($subquery);
        if ($iStatusCode == '7' || $iStatusCode == '8') {
            $EarningFare = $fDriverPaidAmount;
        } else {
            $EarningFare = $DriverCharge[0]['fDeliveryCharge'];
        }
        $EarningFare = $EarningFare * $priceRatio;
        $ToTalEarning += $EarningFare;
        $TotalEarningFare = $ToTalEarning;
    }
    return $TotalEarningFare;
}

function OrderTotalEarningForPassanger($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone) {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();
    $vConvertFromDate = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $conditonalFields = 'iUserId';
    $UserDetailsArr = getUserCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];
    //$priceRatio = $UserDetailsArr['Ratio'];
    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fCommision, fNetTotal, iStatusCode, fRatio_" . $currencycode . " as Ratio FROM `orders` WHERE (DATE(tOrderRequestDate) BETWEEN '$vConvertFromDate' AND '$vConvertToDate') AND $conditonalFields='$iGeneralUserId' AND  `iStatusCode` IN (6, 7, 8, 11, 9)";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $EarningFare = $value['fNetTotal'];
        $EarningFare = $EarningFare * $priceRatio;
        $ToTalEarning += $EarningFare;
        $TotalEarningFare = $ToTalEarning;
    }
    return $TotalEarningFare;
}

########################### Get Passenger Outstanding Amount#############################################################

function GetPassengerOutstandingAmount($iUserId) {
    global $generalobj, $obj;

    $sql = "SELECT SUM( `fCancellationFare` ) AS fCancellationFare FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND ePaidByPassenger = 'No'";
    $tripoutstandingdata = $obj->MySQLSelect($sql);
    $fCancellationFare = $tripoutstandingdata[0]['fCancellationFare'];

    if ($fCancellationFare == "" || $fCancellationFare == NULL) {
        $fCancellationFare = 0;
    }

    return $fCancellationFare;
}

########################### Get Passenger  Outstanding Amount#############################################################
########################### Get Total Order Discount Amount From order detail for menu item wise##########################

function getOrderDetailTotalDiscountPrice($iOrderId) {
    global $generalobj, $obj, $tconfig;

    $sql = "SELECT SUM( `fTotalDiscountPrice` ) AS TotalDiscountPrice FROM order_details WHERE iOrderId = '" . $iOrderId . "' AND eAvailable = 'Yes'";
    $data = $obj->MySQLSelect($sql);
    $TotalDiscountPrice = $data[0]['TotalDiscountPrice'];

    if ($TotalDiscountPrice == "" || $TotalDiscountPrice == NULL) {
        $TotalDiscountPrice = 0;
    }

    return $TotalDiscountPrice;
}

########################### Get Total Order Discount Amount From order detail for menu item wise##########################
########################### Get Total Order Discount Amount From order detail for menu item wise##########################

function getOrderDetailSubTotalPrice($iOrderId) {
    global $generalobj, $obj, $tconfig;

    //$sql = "SELECT SUM( `fOriginalPrice` * `iQty` ) AS TotalOriginalPrice FROM order_details WHERE iOrderId = '".$iOrderId."' AND eAvailable = 'Yes'";
    $sql = "SELECT SUM( `fTotalPrice` ) AS TotalPrice FROM order_details WHERE iOrderId = '" . $iOrderId . "' AND eAvailable = 'Yes'";
    $data = $obj->MySQLSelect($sql);
    $TotalPrice = $data[0]['TotalPrice'];

    if ($TotalPrice == "" || $TotalPrice == NULL) {
        $TotalPrice = 0;
    }

    return $TotalPrice;
}

########################### Get Total Order Discount Amount From order detail for menu item wise##########################
########################### Calculate Order Discount Amount By Company Offer and menu item wise###########################

function CalculateOrderDiscountPrice($iOrderId) {
    global $obj, $generalobj, $tconfig;
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $iCompanyId = $data_order[0]['iCompanyId'];
    //$fSubTotal = $data_order[0]['fSubTotal'];
    $fSubTotal = getOrderDetailSubTotalPrice($iOrderId);
    $iUserId = $data_order[0]['iUserId'];
    $TotOrders = 1;
    if ($iUserId > 0) {
        $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='" . $iUserId . "' AND iCompanyId = '" . $iCompanyId . "' AND iStatusCode NOT IN(12)";
        $db_order = $obj->MySQLSelect($sql);
        $TotOrders = $db_order[0]['TotOrders'];
    }

    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompany = $obj->MySQLSelect($sql);
    $fMinOrderValue = $DataCompany[0]['fMinOrderValue'];
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferType = $DataCompany[0]['fOfferType'];
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    $fOfferAmt = $DataCompany[0]['fOfferAmt'];
    if ($fOfferAppyType == "None") {
        $TotalDiscountPrice = getOrderDetailTotalDiscountPrice($iOrderId);
    } else if ($fOfferAppyType == "All") {
        if ($fSubTotal >= $fTargetAmt) {
            if ($fOfferType == "Percentage") {
                $fDiscount = (($fSubTotal * $fOfferAmt) / 100);
                $fDiscount = round($fDiscount, 2);
                $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscount;
                $TotalDiscountPrice = $fDiscount;
            } else {
                $fDiscount = $fOfferAmt;
                $fDiscount = round($fDiscount, 2);
                $TotalDiscountPrice = $fDiscount;
            }
        } else {
            $TotalDiscountPrice = 0;
        }
    } else {
        if ($TotOrders <= 1) {
            if ($fSubTotal >= $fTargetAmt) {
                if ($fOfferType == "Percentage") {
                    $fDiscount = (($fSubTotal * $fOfferAmt) / 100);
                    $fDiscount = round($fDiscount, 2);
                    $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscount;
                    $TotalDiscountPrice = $fDiscount;
                } else {
                    $fDiscount = $fOfferAmt;
                    $fDiscount = round($fDiscount, 2);
                    $TotalDiscountPrice = $fDiscount;
                }
            } else {
                $TotalDiscountPrice = 0;
            }
        } else {
            $TotalDiscountPrice = getOrderDetailTotalDiscountPrice($iOrderId);
        }
    }

    return round($TotalDiscountPrice, 2);
}

########################### Calculate Order Discount Amount By Company Offer and menu item wise###########################
########################### Get Menu Item Price By Restaurant Offer Wise##################################################

function getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, $iQty = 1, $iUserId = 0, $eFor = "Display", $vOptionId = "", $vAddonId = "") {
    global $obj, $generalobj, $tconfig;

    $TotOrders = 0;
    if ($iUserId > 0) {
        $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='" . $iUserId . "' AND iCompanyId = '" . $iCompanyId . "' AND iStatusCode NOT IN(12)";
        $db_order = $obj->MySQLSelect($sql);
        $TotOrders = $db_order[0]['TotOrders'];
    }

    $str = "select iFoodMenuId,fPrice,fOfferAmt from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
    $db_price = $obj->MySQLSelect($str);
    $fPrice = $db_price[0]['fPrice'];
    if ($vOptionId != "") {
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        $fPrice = $fPrice + $vOptionPrice;
    }
    if ($vAddonId != "") {
        $vAddonPrice = GetFoodMenuItemAddOnPrice($vAddonId);
        $fPrice = $fPrice + $vAddonPrice;
    }
    $fPrice = $fPrice * $iQty;
    $fOriginalPrice = $fPrice;

    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompany = $obj->MySQLSelect($sql);
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferType = $DataCompany[0]['fOfferType'];
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];

    if ($fOfferAppyType == "None") {
        $fOfferAmt = $db_price[0]['fOfferAmt'];
        if ($fOfferAmt > 0) {
            $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
            $fDiscountPrice = round($fDiscountPrice, 2);
            $fPrice = $fPrice - $fDiscountPrice;
        } else {
            $fOfferAmt = 0;
            $fDiscountPrice = 0;
        }
        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    } else if ($fOfferAppyType == "All") {
        $fOfferAmt = $DataCompany[0]['fOfferAmt'];
        if ((($fTargetAmt == 0 || $fTargetAmt == "") && $eFor == "Display") || $eFor == "Calculate") {
            if ($fOfferType == "Percentage") {
                if ($fOfferAmt > 0) {
                    $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                    $fDiscountPrice = round($fDiscountPrice, 2);
                    $fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscountPrice;
                    $fPrice = $fOriginalPrice - $fDiscountPrice;
                } else {
                    $fOfferAmt = 0;
                    $fDiscountPrice = 0;
                }
            } else {
                if ($eFor == "Calculate") {
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = $fOfferAmt * $iQty;
                        $fDiscountPrice = ($fDiscountPrice < 0) ? 0 : $fDiscountPrice;
                        $fPrice = $fOriginalPrice;
                    } else {
                        $fOfferAmt = 0;
                        $fDiscountPrice = 0;
                    }
                } else {
                    $fOfferAmt = 0;
                    $fDiscountPrice = 0;
                }
            }
        } else {
            $fOfferAmt = 0;
            $fDiscountPrice = 0;
        }
        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    } else {
        if ($TotOrders == 0) {
            $fOfferAmt = $DataCompany[0]['fOfferAmt'];
            if ((($fTargetAmt == 0 || $fTargetAmt == "") && $eFor == "Display") || $eFor == "Calculate") {
                if ($fOfferType == "Percentage") {
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                        $fDiscountPrice = round($fDiscountPrice, 2);
                        //$fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0))?$fMaxOfferAmt:$fDiscountPrice;
                        $fPrice = $fOriginalPrice - $fDiscountPrice;
                    } else {
                        $fOfferAmt = 0;
                        $fDiscountPrice = 0;
                    }
                } else {
                    if ($eFor == "Calculate") {
                        if ($fOfferAmt > 0) {
                            $fDiscountPrice = $fOfferAmt;
                            $fDiscountPrice = ($fDiscountPrice < 0) ? 0 : $fDiscountPrice;
                            $fPrice = $fOriginalPrice;
                        } else {
                            $fOfferAmt = 0;
                            $fDiscountPrice = 0;
                        }
                    } else {
                        $fOfferAmt = 0;
                        $fDiscountPrice = 0;
                    }
                }
            } else {
                $fOfferAmt = 0;
                $fDiscountPrice = 0;
            }
        } else {
            $fOfferAmt = $db_price[0]['fOfferAmt'];
            if ($fOfferAmt > 0) {
                $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                $fDiscountPrice = round($fDiscountPrice, 2);
                $fPrice = $fOriginalPrice - $fDiscountPrice;
            } else {
                $fOfferAmt = 0;
                $fDiscountPrice = 0;
            }
        }
        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    }
    //echo "<pre>";print_r($returnArr);exit;
    return $returnArr;
}

########################### Get Menu Item Price By Restaurant Offer Wise##################################################
############################# Get Menu Item Option / AddOn Name ##################################################################

function GetMenuItemOptionsToppingName($iOptionId = "") {
    global $generalobj, $obj, $tconfig;
    $vOptionName = "";
    if ($iOptionId != "") {
        $str = "select vOptionName from `menuitem_options` where iOptionId IN(" . $iOptionId . ")";
        $db_options_data = $obj->MySQLSelect($str);
        if (count($db_options_data) > 0) {
            for ($i = 0; $i < count($db_options_data); $i++) {
                $vOptionName .= $db_options_data[$i]['vOptionName'] . ", ";
            }
        }
        $vOptionName = substr($vOptionName, 0, -2);
    }

    return $vOptionName;
}

############################# Get Menu Item Option Name ##################################################################
############################# Get Order Status Code Text ##################################################################

function GetOrderStatusLogText($iOrderId, $UserType = "Passenger") {
    global $generalobj, $obj, $tconfig;

    $sql = "SELECT ord.iUserId,ord.iDriverId,ord.iCompanyId,ord.iStatusCode,os.vStatus_Track,os.vStatus,osl.dDate FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode LEFT JOIN order_status_logs as osl ON osl.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "' ORDER BY osl.dDate DESC LIMIT 0,1";
    $data_order = $obj->MySQLSelect($sql);

    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iDriverId = $data_order[0]['iDriverId'];
    $sql = "SELECT CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
    $Data_vehicle = $obj->MySQLSelect($sql);
    $drivername = $Data_vehicle[0]['driverName'];
    $iStatusCode = $data_order[0]['iStatusCode'];
    $dDate = $data_order[0]['dDate'];
    $vStatus = $data_order[0]['vStatus'];
    //$StatusDate = date('l, dS M Y',strtotime($dDate));
    $StatusDate = date('F d, Y h:iA', strtotime($dDate)); //h:iA

    if ($UserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, $iOrderId);
    } else if ($UserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iDriverId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId, $iOrderId);
    }

    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1");
    $Displaytext = "";
    if ($iStatusCode == "8") {
        $Displaytext = $languageLabelsArr['LBL_CANCELLED_ON'] . " " . $StatusDate;
    }

    if ($iStatusCode == "6") {
        $Displaytext = $languageLabelsArr['LBL_ORDER_DELIVERED_ON'] . " " . $StatusDate;
		//. " " . $languageLabelsArr['LBL_BY'] . " " . $drivername
    }

    return $Displaytext;
}

############################# Get Order Status Code Text ##################################################################
############################# Check Menu Item Availability When Order Placed By User#######################################

function checkmenuitemavailability($OrderDetails = array()) {
    global $obj, $generalobj, $tconfig;
    $isAllItemAvailable = "Yes";
    $isAllItemOptionsAvailable = "Yes";
    $isAllItemToppingssAvailable = "Yes";
    if (count($OrderDetails) > 0) {
        for ($i = 0; $i < count($OrderDetails); $i++) {
            $iMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $str = "select eAvailable,eStatus from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
            $db_menu_item = $obj->MySQLSelect($str);
            $eStatus = $db_menu_item[0]['eStatus'];
            $eAvailable = $db_menu_item[0]['eAvailable'];
            if ($eAvailable == "No" || $eStatus != "Active") {
                $isAllItemAvailable = "No";
                break;
            }
        }

        for ($j = 0; $j < count($OrderDetails); $j++) {
            $vOptionId = $OrderDetails[$j]['vOptionId'];
            if ($vOptionId != "") {
                $str = "select eStatus from menuitem_options where iOptionId IN(" . $vOptionId . ")";
                $db_menu_item_option = $obj->MySQLSelect($str);
                $eStatus1 = $db_menu_item_option[0]['eStatus'];
                if ($eStatus1 != "Active") {
                    $isAllItemOptionsAvailable = "No";
                    break;
                }
            }
        }
        for ($k = 0; $k < count($OrderDetails); $k++) {
            $vAddonId = $OrderDetails[$k]['vAddonId'];
            if ($vAddonId != "") {
                $str = "select eStatus from menuitem_options where iOptionId IN(" . $vAddonId . ")";
                $db_menu_item_Addon = $obj->MySQLSelect($str);
                $eStatus2 = $db_menu_item_Addon[0]['eStatus'];
                if ($eStatus2 != "Active") {
                    $isAllItemToppingssAvailable = "No";
                    break;
                }
            }
        }
    }

    $returnArr['isAllItemAvailable'] = $isAllItemAvailable;
    $returnArr['isAllItemOptionsAvailable'] = $isAllItemOptionsAvailable;
    $returnArr['isAllItemToppingssAvailable'] = $isAllItemToppingssAvailable;

    return $returnArr;
}

############################# Check Menu Item Availability When Order Placed By User#######################
############# Get Text For Order Refund Or Cancelled ###############

function GetOrderStatusLogTextForCancelled($iOrderId, $UserType = "Passenger") {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT ord.iUserId,ord.iDriverId,ord.iCompanyId,ord.fRefundAmount,ord.iStatusCode,os.vStatus_Track,os.vStatus,osl.dDate,ord.fCancellationCharge,ord.fRestaurantPaidAmount,ord.fDriverPaidAmount FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode LEFT JOIN order_status_logs as osl ON osl.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "' ORDER BY osl.dDate DESC LIMIT 0,1";
    $data_order = $obj->MySQLSelect($sql);
    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iDriverId = $data_order[0]['iDriverId'];
    $sql = "SELECT CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
    $Data_vehicle = $obj->MySQLSelect($sql);
    $drivername = $Data_vehicle[0]['driverName'];
    $iStatusCode = $data_order[0]['iStatusCode'];
    $dDate = $data_order[0]['dDate'];
    $vStatus = $data_order[0]['vStatus'];
    $fRefundAmount = $data_order[0]['fRefundAmount'];
    $fCancellationCharge = $data_order[0]['fCancellationCharge'];
    $fRestaurantPaidAmount = $data_order[0]['fRestaurantPaidAmount'];
    $fDriverPaidAmount = $data_order[0]['fDriverPaidAmount'];
    //$StatusDate = date('l, dS M Y',strtotime($dDate));
    $StatusDate = date('F d, Y h:iA', strtotime($dDate)); //h:iA
    if ($UserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, $iOrderId);
    } else if ($UserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iDriverId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId, $iOrderId);
    }
    $Ratio = $UserDetailsArr['Ratio'];
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1");
    $Displaytext = "";
    if ($UserType == "Passenger") {
        if ($iStatusCode == "8") {
            $fCancellationChargeNew = $fCancellationCharge * $Ratio;
            $fCancellationCharge = formatNum($fCancellationChargeNew);
            $CancellationCharge = $currencySymbol . $fCancellationCharge;
            $CancellationChargeTxt = $languageLabelsArr["LBL_CANCELLATION_CHARGE"] . ":" . $CancellationCharge;
            $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"] . "\n" . $CancellationChargeTxt;
        }
        if ($iStatusCode == "7") {
            //$Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"];
            $fCancellationChargeNew = $fCancellationCharge * $Ratio;
            $fCancellationCharge = formatNum($fCancellationChargeNew);
            $CancellationCharge = $currencySymbol . $fCancellationCharge;
            $CancellationChargeTxt = $languageLabelsArr["LBL_CANCELLATION_CHARGE"] . ":" . $CancellationCharge;
            $fRefundAmountnew = $fRefundAmount * $Ratio;
            $fRefundAmount = formatNum($fRefundAmountnew);
            $RefundAmount = $currencySymbol . $fRefundAmount;
            $RefundAmountTxt = $languageLabelsArr["LBL_REFUND_APP_TXT"] . ":" . $RefundAmount;
            $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"] . "\n" . $CancellationChargeTxt . "\n" . $RefundAmountTxt;
        }
    } else if ($UserType == "Company") {
        if ($iStatusCode == "8" || $iStatusCode == "7") {
            $fRestaurantPaidAmountNew = $fRestaurantPaidAmount * $Ratio;
            $fRestaurantPaidAmount = formatNum($fRestaurantPaidAmountNew);
            $fRestaurantPaidAmount = $currencySymbol . $fRestaurantPaidAmount;
            if ($data_order[0]['fRestaurantPaidAmount'] > 0) {
                $fRestaurantPaidAmountTxt = $languageLabelsArr["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] . ":" . $fRestaurantPaidAmount;
            } else {
                $fRestaurantPaidAmountTxt = $languageLabelsArr["LBL_AMT_GENERATE_PENDING"];
            }
            if ($iStatusCode == "8") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"] . "\n" . $fRestaurantPaidAmountTxt;
            } else if ($iStatusCode == "7") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"] . "\n" . $fRestaurantPaidAmountTxt;
            }
        }
    } else {
        if ($iStatusCode == "8" || $iStatusCode == "7") {
            $fDriverPaidAmountNew = $fDriverPaidAmount * $Ratio;
            $fDriverPaidAmount = formatNum($fDriverPaidAmount);
            $fDriverPaidAmount = $currencySymbol . $fDriverPaidAmount;
            if ($data_order[0]['fDriverPaidAmount'] > 0) {
                $fDriverPaidAmountTxt = $languageLabelsArr["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] . ":" . $fDriverPaidAmount;
            } else {
                $fDriverPaidAmountTxt = $languageLabelsArr["LBL_AMT_GENERATE_PENDING"];
            }
            if ($iStatusCode == "8") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"] . "\n" . $fDriverPaidAmountTxt;
            } else if ($iStatusCode == "7") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"] . "\n" . $fDriverPaidAmountTxt;
            }
        }
    }
    //$returnArr['Displaytext'] = $Displaytext;
    return $Displaytext;
}

############# ENd Text For Order Refund Or Cancelled ###############
############# Update Company LAt Long For Demo Mode ###############

function updatecompanylatlong($latitude, $longitude, $iCompanyId) {
    global $obj, $generalobj, $tconfig, $GOOGLE_SEVER_API_KEY_WEB;

    if (SITE_TYPE == "Demo") {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?key=" . $GOOGLE_SEVER_API_KEY_WEB . "&language=en&latlng=" . $latitude . "," . $longitude;
        $jsonfile = file_get_contents($url);
        $jsondata = json_decode($jsonfile);
        $location_Address = $jsondata->results[0]->formatted_address;
        $latitude_new = $jsondata->results[0]->geometry->location->lat;
        $longitude_new = $jsondata->results[0]->geometry->location->lng;
        if ($location_Address == "" || $location_Address == NULL) {
            $FilterArray = array(0.0015, 0.0020, 0.0025, 0.0030, 0.0035, 0.0040);
            $k = array_rand($FilterArray);
            $num = $FilterArray[$k];
            $latitude_new = $latitude + $num;
            $longitude_new = $longitude + $num;
            $location_Address = getAddressFromLocation($latitude_new, $longitude_new, $GOOGLE_SEVER_API_KEY_WEB);
        }
        $where = " iCompanyId = '" . $iCompanyId . "'";
        $Data['vRestuarantLocation'] = $location_Address;
        $Data['vCaddress'] = $location_Address;
        $Data['vRestuarantLocationLat'] = $latitude_new;
        $Data['vRestuarantLocationLong'] = $longitude_new;
        $Data['eLock'] = "Yes";
        $id = $obj->MySQLQueryPerform("company", $Data, 'update', $where);
    }


    return $iCompanyId;
}

############# Update Company LAt Long For Demo Mode ###############
################# Display Recommended and Best Seller Menu Items#############################

function getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, $DisplayType = "Recommended", $CheckNonVegFoodType = "No") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, 0);
    $vLanguage = $UserDetailsArr['vLang'];
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $currencycode = $UserDetailsArr['currencycode'];
    $Ratio = $UserDetailsArr['Ratio'];
    $ssql1 = "";
    if ($DisplayType == "Recommended") {
        $ssql1 .= " AND mi.eRecommended = 'Yes' ";
    } else {
        $ssql1 .= " AND mi.eBestSeller = 'Yes' ";
    }

    $sql = "SELECT fm.* FROM food_menu as fm WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND (select count(iMenuItemId) from menu_items as mi where mi.iFoodMenuId=fm.iFoodMenuId AND mi.eStatus='Active' AND mi.eAvailable = 'Yes' $ssql1) > 0  ORDER BY fm.iDisplayOrder ASC";
    $db_food_data = $obj->MySQLSelect($sql);
    $MenuItemsDataArr = array();
    if (count($db_food_data) > 0) {
        for ($i = 0; $i < count($db_food_data); $i++) {
            $iFoodMenuId = $db_food_data[$i]['iFoodMenuId'];
            $vMenu = $db_food_data[$i]['vMenu_' . $vLanguage];

            $ssql = "";
            if ($CheckNonVegFoodType == "Yes") {
                $ssql .= " AND mi.eFoodType = 'Veg' ";
            }

            $sqlf = "SELECT mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_" . $vLanguage . " as vItemType,mi.vItemDesc_" . $vLanguage . " as vItemDesc, mi.fPrice, mi.eFoodType, mi.fOfferAmt,mi.vImage, mi.iDisplayOrder, mi.vHighlightName FROM menu_items as mi WHERE mi.iFoodMenuId = '" . $iFoodMenuId . "' AND mi.eStatus='Active' AND mi.eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC";
            $db_item_data = $obj->MySQLSelect($sqlf);
            if (count($db_item_data) > 0) {
                for ($j = 0; $j < count($db_item_data); $j++) {
                    if (!empty($vMenu)) {
                        $db_item_data[$j]['vCategoryName'] = $vMenu;
                    } else {
                        $db_item_data[$j]['vCategoryName'] = '';
                    }
                    $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($db_item_data[$j]['iMenuItemId'], $iCompanyId, 1, $iUserId, "Display", "", "");
                    $fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                    $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
                    $db_item_data[$j]['fOfferAmt'] = $fOfferAmt;
                    $db_item_data[$j]['fPrice'] = round($db_item_data[$j]['fPrice'] * $Ratio, 2);
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                        $StrikeoutPrice = round($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
                        $db_item_data[$j]['fDiscountPrice'] = formatNum($fDiscountPrice);
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    } else {
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['fDiscountPrice'] = formatNum($fPrice);
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    }
                    if ($db_item_data[$j]['vImage'] != "") {
                        $db_item_data[$j]['vImage'] = $tconfig["tsite_upload_images_menu_item"] . "/" . $db_item_data[$j]['vImage'];
                    }
                    $MenuItemOptionToppingArr = GetMenuItemOptionsTopping($db_item_data[$j]['iMenuItemId'], $currencySymbol, $Ratio, $vLanguage);
                    $db_item_data[$j]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
                    array_push($MenuItemsDataArr, $db_item_data[$j]);
                }
            }
        }
    }

    /*   $sqlf = "SELECT mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_".$vLanguage." as vItemType,mi.vItemDesc_".$vLanguage." as vItemDesc,mi.fPrice,mi.eFoodType,mi.fOfferAmt,mi.vImage,mi.iDisplayOrder FROM menu_items as mi LEFT JOIN food_menu as f on f.iFoodMenuId=mi.iFoodMenuId LEFT JOIN company as c on c.iCompanyId=f.iCompanyId WHERE mi.eStatus='Active' AND mi.eAvailable = 'Yes' AND f.iCompanyId = '".$restaId."'  $ssql ORDER BY RAND()";
      $db_item_data = $obj->MySQLSelect($sqlf);
      for($j=0;$j<count($db_item_data);$j++){
      $fPrice= round($db_item_data[$j]['fPrice']*$Ratio,2);
      $db_item_data[$j]['fPrice'] = formatNum($fPrice);
      if($db_item_data[$j]['vImage'] != ""){
      $db_item_data[$j]['vImage'] = $tconfig["tsite_upload_images_menu_item"]."/".$db_item_data[$j]['vImage'];
      }
      } */
    //$returnArr['Recomendation_Arr'] = $MenuItemsDataArr;
    //echo "<pre>";print_r($returnArr);exit;
    return $MenuItemsDataArr;
}

################# Display Recommended and Best Seller Menu Items#############################
########################## Check Cancel Order Status ########################################

function checkCancelOrderStatus($iOrderId) {
    global $generalobj, $obj;

    $sql = "SELECT iStatusCode from orders WHERE iOrderId ='" . $iOrderId . "'";
    $db_status = $obj->MySQLSelect($sql);
    $iStatusCode = $db_status[0]['iStatusCode'];

    if ($iStatusCode == 8) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    return $iOrderId;
}

########################## Check Cancel Order Status ########################################

function getServiceCategoryCounts() {
    global $generalobj, $obj;
    $sqlN = "SELECT count(iServiceId) as TotalSerivce FROM service_categories WHERE eStatus='Active'";
    $datar = $obj->MySQLSelect($sqlN);
    $serviceCatCount = $datar[0]['TotalSerivce'];
    return $serviceCatCount;
}

######################### pubnub notification #################################################

function pubnubnotification($channelName, $msg_encode_pub) {
    global $generalobj, $obj;
    //$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
    //$PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
    //$PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
    //$PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY"); 
    //$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
    //$info = $pubnub->publish($channelName, $msg_encode_pub);
    return true;
}

function calculateDriverReimbursement($iOrderId) {
    $Rvalue = 0;
    global $generalobj, $obj;
    $sql = "SELECT `trips`.`tStartLat`, `trips`.`tStartLong`, `trips`.`tEndLat`,`trips`.`tEndLong` FROM `orders`  JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` WHERE `orders`.`iOrderId` = '$iOrderId' AND `orders`.`iStatusCode` = 6 ;";
    //AND `trips`.`iDriverId` ='$iDriverId'
    $data = $obj->MySQLSelect($sql);
    if (count($data) > 0) {
        //GetDrivingDistanceFromGoogless
        $Getdistance = GetDrivingDistanceByPCode($data[0]['tStartLat'], $data[0]['tEndLat'], $data[0]['tStartLong'], $data[0]['tEndLong']);
        //$Getdistance['distance'] 
        if ($Getdistance > 0) {
            // $distance = round(($Getdistance['distance'] * 0.62137119) , 2);
            /* $distance = $Getdistance;
              if(($distance > 0) && ($distance < 4))
              {
              $Rvalue = 1;
              }
              elseif(($distance > 4) && ($distance < 5))
              {
              $Rvalue = 1.25;
              }
              elseif($distance > 5)
              {
              $Rvalue = 1.5;
              }
              else
              {
              $Rvalue = 0;
              } */

            $Rvalue = 1;
        }
    }

    return $Rvalue;
}

function calculateDriverReimbursementOfDriverwithrange($OrderstartDate, $OrderendDate, $iDriverId) {

    $totalRval = 0;
    global $generalobj, $obj;
    $ssql = '';
    $ssql .= " AND Date(`orders`.`tOrderRequestDate`) >='" . $OrderstartDate . "'";
    $ssql .= " AND Date(`orders`.`tOrderRequestDate`) <='" . $OrderendDate . "'";
    $sql = "SELECT `trips`.`tStartLat`, `trips`.`tStartLong`, `trips`.`tEndLat`,`trips`.`tEndLong` FROM `orders`  JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` WHERE `orders`.`iDriverId` = '$iDriverId' AND `orders`.`iStatusCode` = 6 $ssql;";
    //AND `trips`.`iDriverId` ='$iDriverId'
    $data = $obj->MySQLSelect($sql);
    if (count($data) > 0) {

        foreach ($data as $datas):
            //GetDrivingDistanceFromGoogless
            $Getdistance = GetDrivingDistanceByPCode($datas['tStartLat'], $datas['tEndLat'], $datas['tStartLong'], $datas['tEndLong']);
            $Rvalue = 0;
            if ($Getdistance > 0) {
                //$distance = round(($Getdistance * 0.62137119) , 2);
                /* $distance = $Getdistance;
                  if(($distance > 0) && ($distance < 4))
                  {
                  $Rvalue = 1;
                  }
                  elseif(($distance > 4) && ($distance < 5))
                  {
                  $Rvalue = 1.25;
                  }
                  elseif($distance > 5)
                  {
                  $Rvalue = 1.5;
                  }
                  else
                  {
                  $Rvalue = 0;
                  } */
                $Rvalue = 1;
            }

            $totalRval = $totalRval + $Rvalue;
        endforeach;
        unset($datas);
        unset($data);
    }

    return $totalRval;
}

function calculateDriverRatingBonus($iOrderId) {
    $RatingBonus = 0;
/*    global $generalobj, $obj;
    $sql = "SELECT `vRating1` FROM `ratings_user_driver` WHERE `eFromUserType`='Passenger' AND `eToUserType` ='Driver' AND `iOrderId` ='$iOrderId';";
    $data = $obj->MySQLSelect($sql);

    if (count($data) > 0) {
        $rating = $data[0]['vRating1'];

        if (($rating >= 4) && ($rating < 5)) {
            $RatingBonus = 0.15;
        } elseif ($rating == 5) {
            $RatingBonus = 0.25;
        } else {
            $RatingBonus = 0;
        }
    } */
    return $RatingBonus;
}

function calculateTotalDriverRatingBonus($OrderstartDate, $OrderendDate, $iDriverId) {
    global $obj, $generalobj;
    $TotalRVal = 0;
 /*   $ssql = '';
    $ssql .= " AND Date(`tOrderRequestDate`) >='" . $OrderstartDate . "'";
    $ssql .= " AND Date(`tOrderRequestDate`) <='" . $OrderendDate . "'";
    $ssql1 = " AND iDriverId ='" . $iDriverId . "'";
    $iUserId = "iDriverId";
    $usertotaltrips = "SELECT `iOrderId` FROM `orders` WHERE `iStatusCode` = 6 $ssql $ssql1;";
    $allOrders = $obj->MySQLSelect($usertotaltrips);
    $iOrderId = '';
    if (count($allOrders) > 0) {
        for ($i = 0; $i < count($allOrders); $i++) {
            $iOrderId .= $allOrders[$i]['iOrderId'] . ",";
        }

        $iOrderId_str = substr($iOrderId, 0, -1);
        //echo  $iTripId_str;exit;

        $sql = "SELECT `vRating1` FROM `ratings_user_driver` WHERE `eFromUserType`='Passenger' AND `eToUserType` ='Driver' AND `iOrderId` IN (" . $iOrderId_str . ");";
        $data = $obj->MySQLSelect($sql);

        if (count($data) > 0) {
            $RatingBonus = 0;
            foreach ($data as $datas):
                $rating = $datas['vRating1'];

                if (($rating >= 4) && ($rating < 5)) {
                    $RatingBonus = 0.15;
                } elseif ($rating == 5) {
                    $RatingBonus = 0.25;
                } else {
                    $RatingBonus = 0;
                }

                $TotalRVal = $TotalRVal + $RatingBonus;
            endforeach;
            unset($datas);
            unset($data);
        }
    }*/
    return $TotalRVal;
}

function GetAverageRatingOfDriver($OrderstartDate, $OrderendDate, $iDriverId) {
    global $obj, $generalobj;
    $ssql = '';
    $ssql .= " AND Date(`tOrderRequestDate`) >='" . $OrderstartDate . "'";
    $ssql .= " AND Date(`tOrderRequestDate`) <='" . $OrderendDate . "'";
    $ssql1 = " AND iDriverId ='" . $iDriverId . "'";
    $iUserId = "iDriverId";
    $usertotaltrips = "SELECT `iOrderId` FROM `orders` WHERE `iStatusCode` = 6 $ssql $ssql1;";
    $allOrders = $obj->MySQLSelect($usertotaltrips);
    $iOrderId = '';
    if (count($allOrders) > 0) {
        for ($i = 0; $i < count($allOrders); $i++) {
            $iOrderId .= $allOrders[$i]['iOrderId'] . ",";
        }

        $iOrderId_str = substr($iOrderId, 0, -1);
        //echo  $iTripId_str;exit;
        $sql = "SELECT count(iRatingId) as ToTalTrips, SUM(vRating1) as ToTalRatings from ratings_user_driver WHERE iOrderId IN (" . $iOrderId_str . ") AND `eFromUserType`='Passenger' AND `eToUserType` ='Driver';";
        $result_ratings = $obj->MySQLSelect($sql);
        $ToTalTrips = $result_ratings[0]['ToTalTrips'];
        $ToTalRatings = $result_ratings[0]['ToTalRatings'];
        //$average_rating = round($ToTalRatings / $ToTalTrips, 2);
        $average_rating = round($ToTalRatings / $ToTalTrips, 1);
    } else {
        $average_rating = 0;
    }
    return $average_rating;
}

function GetDrivingDistanceFromGoogless($lat1, $lat2, $long1, $long2) {
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
    return array('distance' => ($distvalue / 1000), 'time' => ($timevalue / 60));
}

function GetDrivingDistanceByPCode($lat1, $lat2, $lon1, $lon2) {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    return $miles;
}

function getDeliverySector($getAdressarray) {

    global $generalobj, $obj;
    $ssql = "";
    $result = array();
    if (!empty($getAdressarray)) {
        $sqlaa = "SELECT `iLocationId`, `iCountryId`, `vLocationName`, `tLatitude`, `tLongitude`, `eStatus`, `eFor` FROM `location_master` WHERE `eStatus` = 'Active' AND `eFor` = 'DeliverySector';";
        $allowed_data = $obj->MySQLSelect($sqlaa);
        //print_r($allowed_data);
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($getAdressarray, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {

                        $result = array(
                            'sector' => $val['vLocationName']
                        );
                        break;
                    }
                }
            }
        }
    }

    if (empty($result)) {
        $result = array('sector' => 'NA');
    }

    return $result;
}

function getChowcalldeliverycharge() {
    global $generalobj, $obj;
    $deliverycharge = 0;
    $sql = "SELECT `vValue` FROM `configurations` WHERE `vName` = 'EATCAYMAN_DELIVERY_CHARGE';";
    $result = $obj->MySQLSelect($sql);
    if (count($result > 0)) {
        $deliverycharge = $result[0]['vValue'] ? $result[0]['vValue'] : 0;
    }
    return $deliverycharge;
}

function CheckMultiOrderActive() {
    global $generalobj, $obj;
    $IsActive = 'No';
    $sql = "SELECT `vValue` FROM `configurations` WHERE `vName` = 'EATCAYMAN_AUTO_ROUTING';";
    $result = $obj->MySQLSelect($sql);
    if (count($result > 0)) {
        $IsActive = $result[0]['vValue'] ? $result[0]['vValue'] : 'No';
    }
    return $IsActive;
}

//function CC($country, $phone) {
//  $function = 'format_phone_' . $country;
//  if(function_exists($function)) {
//    return $function($phone);
//  }
//  return $phone;
//}

function CCFormatPhoneNumber($phone) {
    // note: making sure we have something
    if (!isset($phone{3})) {
        return '';
    }
    // note: strip out everything but numbers 
    $phone = preg_replace("/[^0-9]/", "", $phone);
    $length = strlen($phone);
    switch ($length) {
        case 7:
            return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
            break;
        case 10:
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
            break;
        case 11:
            return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1($2) $3-$4", $phone);
            break;
        default:
            return $phone;
            break;
    }
}

function CCordernumber() {
    global $generalobj, $obj;
    $currentdate = date('Y-m-d');
    $IsActive = 'No';
    $sql = "SELECT COUNT(`iOrderId`) as cccount FROM `orders` WHERE DATE(`tOrderRequestDate`) = '$currentdate';";
    $result = $obj->MySQLSelect($sql);

    $count = 0;

    if (count($result) > 0) {
        $count = $result[0]['cccount'];
    }

    if ($count == 0) {
        $count = 1;
    } else {
        $count++;
    }

    $orderNumber = date('ymd') . sprintf('%04d', $count);

    return $orderNumber;
}

function getCompanyDetailsShorted($iCompanyId, $iUserId, $CheckNonVegFoodType = "No", $searchword = "", $iServiceId = "") {
    global $obj, $generalobj, $tconfig;

//    if ($iUserId != "") {
//        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
//        $passengerData = $obj->MySQLSelect($sqlp);
//        $currencycode = $passengerData[0]['vCurrencyPassenger'];
//        $vLanguage = $passengerData[0]['vLang'];
//        $currencySymbol = $passengerData[0]['vSymbol'];
//        $Ratio = $passengerData[0]['Ratio'];
//
//        if ($vLanguage == "" || $vLanguage == NULL) {
//            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
//        }
//        if ($currencycode == "" || $currencycode == NULL) {
//            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
//            $currencyData = $obj->MySQLSelect($sqlp);
//            $currencycode = $currencyData[0]['vName'];
//            $currencySymbol = $currencyData[0]['vSymbol'];
//            $Ratio = $currencyData[0]['Ratio'];
//        }
//    } else {
    $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
    $currencyData = $obj->MySQLSelect($sqlp);
    $currencycode = $currencyData[0]['vName'];
    $currencySymbol = $currencyData[0]['vSymbol'];
    $Ratio = $currencyData[0]['Ratio'];
    // }
    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    $LBL_PER_PERSON_TXT = $languageLabelsArr['LBL_PER_PERSON_TXT'];

    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompany = $obj->MySQLSelect($sql);
    if (isset($DataCompany[0]['fPricePerPerson'])) {
        $personprice = $DataCompany[0]['fPricePerPerson'];
        $PersonPrice = round(($personprice * $Ratio), 2);
        $returnArr['fPricePerPersonWithCurrency'] = $currencySymbol . " " . $PersonPrice;
    }
    $fPricePerPerson = $DataCompany[0]['fPricePerPerson'];
    $fPricePerPerson = round(($fPricePerPerson * $Ratio), 2);
    $fPricePerPerson = $currencySymbol . "" . $fPricePerPerson . " " . $LBL_PER_PERSON_TXT;
    $returnArr['Restaurant_PricePerPerson'] = $fPricePerPerson;

//    $CompanyTimeSlot = getCompanyTimeSlot($iCompanyId, $languageLabelsArr);
//    $returnArr['monfritimeslot_TXT'] = $CompanyTimeSlot['monfritimeslot_TXT'];
//    $returnArr['monfritimeslot_Time'] = $CompanyTimeSlot['monfritimeslot_Time_new'];
//    $returnArr['satsuntimeslot_TXT'] = $CompanyTimeSlot['satsuntimeslot_TXT'];
//    $returnArr['satsuntimeslot_Time'] = $CompanyTimeSlot['satsuntimeslot_Time_new'];
    //echo "<pre>";print_r($CompanyTimeSlot);exit;

    $sql = "SELECT cu.cuisineName_" . $vLanguage . " as cuisineName,cu.cuisineId FROM cuisine as cu LEFT JOIN company_cuisine as ccu ON ccu.cuisineId=cu.cuisineId WHERE ccu.iCompanyId = '" . $iCompanyId . "' AND cu.eStatus = 'Active'";
    $db_cuisine = $obj->MySQLSelect($sql);

    if (count($db_cuisine) > 0) {
        for ($i = 0; $i < count($db_cuisine); $i++) {
            $db_cuisine_str .= $db_cuisine[$i]['cuisineName'] . ", ";
            $db_cuisine_id_str .= $db_cuisine[$i]['cuisineId'] . ",";
        }
        $db_cuisine_str = substr($db_cuisine_str, 0, -2);
        $db_cuisine_id_str = substr($db_cuisine_id_str, 0, -1);
    } else {
        $db_cuisine_str = "";
        $db_cuisine_id_str = "";
    }
    $returnArr['Restaurant_Cuisine'] = $db_cuisine_str;
    $returnArr['Restaurant_Cuisine_Id'] = $db_cuisine_id_str;

    $LBL_MINS_SMALL = $languageLabelsArr['LBL_MINS_SMALL'];
    $fPrepareTime = $DataCompany[0]['fPrepareTime'];
    $fPrepareTime = $fPrepareTime . " " . $LBL_MINS_SMALL;
    $returnArr['Restaurant_OrderPrepareTime'] = $fPrepareTime;

    $fOfferType = $DataCompany[0]['fOfferType'];
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferAmt = $DataCompany[0]['fOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    $fTargetAmt = round(($fTargetAmt * $Ratio), 2);
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fMaxOfferAmt = round(($fMaxOfferAmt * $Ratio), 2);
    if ($fMaxOfferAmt > 0) {
        $MaxDiscountAmount = " ( " . $languageLabelsArr['LBL_MAX_DISCOUNT_TXT'] . " " . $currencySymbol . "" . $fMaxOfferAmt . " )";
    } else {
        $MaxDiscountAmount = "";
    }
    if ($fTargetAmt > 0) {
        $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'] . " " . $languageLabelsArr['LBL_ORDERS_ABOVE_TXT'] . " " . $currencySymbol . "" . $fTargetAmt . " ";
        $ALL_ORDER_TXT = "";
    } else {
        $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'];
        $ALL_ORDER_TXT = $languageLabelsArr['LBL_ALL_ORDER_TXT'];
    }

    if ($fOfferType == "Percentage") {
        if ($fOfferAppyType == "First") {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'] . "" . $MaxDiscountAmount;
            $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
        } elseif ($fOfferAppyType == "All") {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT . " " . $MaxDiscountAmount;
            //$offermsg =  $languageLabelsArr['LBL_GET_TXT']." ".$fOfferAmt."% ".$TargerAmountTXT." ".$MaxDiscountAmount;
            $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
        } else {
            $offermsg = "";
            $offermsg_short = "";
        }
    } else {
        $fOfferAmt = round(($fOfferAmt * $Ratio), 2);
        $DiscountAmount = $currencySymbol . "" . $fOfferAmt;
        if ($fOfferAppyType == "First" && $fOfferAmt > 0) {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
            $offermsg_short = $offermsg;
        } elseif ($fOfferAppyType == "All" && $fOfferAmt > 0) {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
            //$offermsg =  $languageLabelsArr['LBL_GET_TXT']." ".$DiscountAmount." ".$TargerAmountTXT;
            $offermsg_short = $offermsg;
        } else {
            $offermsg = "";
            $offermsg_short = "";
        }
    }
    $returnArr['Restaurant_OfferMessage'] = $offermsg;
    $returnArr['Restaurant_OfferMessage_short'] = $offermsg_short;

    $fMinOrderValue = $DataCompany[0]['fMinOrderValue'];
    $fMinOrderValue = round(($fMinOrderValue * $Ratio), 2);
    $returnArr['fMinOrderValueDisplay'] = $currencySymbol . " " . $fMinOrderValue;
    $returnArr['fMinOrderValue'] = $fMinOrderValue;
    $returnArr['Restaurant_MinOrderValue'] = ($fMinOrderValue > 0) ? $currencySymbol . $fMinOrderValue . " " . $languageLabelsArr['LBL_MIN_ORDER_TXT'] : $languageLabelsArr['LBL_NO_MIN_ORDER_TXT'];
    $fPackingCharge = $DataCompany[0]['fPackingCharge'];
    $fPackingCharge = round(($fPackingCharge * $Ratio), 2);
    $returnArr['fPackingCharge'] = $fPackingCharge;
    //echo "<pre>";print_r($returnArr);
    ## Check NonVeg Item Available of Restaaurant ##
//    $eNonVegToggleDisplay = "No";
//    $sql = "SELECT count(mi.iMenuItemId) As TotNonVegItems FROM menu_items as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId=mi.iFoodMenuId WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND mi.eStatus='Active' AND mi.eFoodType = 'NonVeg'";
//    $db_foodtype_data = $obj->MySQLSelect($sql);
//    $TotNonVegItems = $db_foodtype_data[0]['TotNonVegItems'];
//    $sql = "SELECT count(mi.iMenuItemId) As TotVegItems FROM menu_items as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId=mi.iFoodMenuId WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND mi.eStatus='Active' AND mi.eFoodType = 'Veg'";
//    $db_vegfoodtype_data = $obj->MySQLSelect($sql);
//    $TotVegItems = $db_vegfoodtype_data[0]['TotVegItems'];
//    if ($TotNonVegItems > 0 && $TotVegItems > 0) {
//        $eNonVegToggleDisplay = "Yes";
//    }
//    $returnArr['eNonVegToggleDisplay'] = $eNonVegToggleDisplay;
    ## Check NonVeg Item Available of Restaaurant ##
    ## Get Company Rattings ## 
    $rsql = "SELECT count(r.iRatingId) as totalratings FROM orders as o LEFT JOIN ratings_user_driver as r on r.iOrderId=o.iOrderId WHERE o.iCompanyId='" . $iCompanyId . "' AND r.eFromUserType='Passenger' AND r.eToUserType='Company'";
    $Rating_data = $obj->MySQLSelect($rsql);
    $ratingcounts = $Rating_data[0]['totalratings'];
    if ($ratingcounts <= 100) {
        $ratings = $ratingcounts . " " . $languageLabelsArr['LBL_RATING'];
    } else {
        $ratings = $ratingcounts . "+ " . $languageLabelsArr['LBL_RATING'];
    }

    $returnArr['RatingCounts'] = $ratings;
    ## End Get Company Rattings ## 
    ## Get Company's menu details ##
    //$sql = "SELECT * FROM food_menu WHERE iCompanyId = '".$iCompanyId."' AND eStatus='Active' ORDER BY iDisplayOrder ASC";
//    $sql = "SELECT fm.* FROM food_menu as fm WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND (select count(iMenuItemId) from menu_items as mi where mi.iFoodMenuId=fm.iFoodMenuId AND mi.eStatus='Active' AND mi.eAvailable = 'Yes') > 0 ORDER BY fm.iDisplayOrder ASC";
//    $db_food_data = $obj->MySQLSelect($sql);
//    $CompanyFoodData = array();
//    $MenuItemsDataArr = array();
//    if (count($db_food_data) > 0) {
//        for ($i = 0; $i < count($db_food_data); $i++) {
//            $iFoodMenuId = $db_food_data[$i]['iFoodMenuId'];
//            $vMenu = $db_food_data[$i]['vMenu_' . $vLanguage];
//            $CompanyFoodData[$i]['iFoodMenuId'] = $iFoodMenuId;
//            $CompanyFoodData[$i]['vMenu'] = $vMenu;
//
//            $ssql = "";
//            if ($CheckNonVegFoodType == "Yes") {
//                $ssql .= " AND eFoodType = 'Veg' ";
//            }
//            if ($searchword != "") {
//                $ssql .= " AND LOWER(vItemType_" . $vLanguage . ") LIKE '%" . $searchword . "%' ";
//            }
//            $sqlf = "SELECT iMenuItemId,iFoodMenuId,vItemType_" . $vLanguage . " as vItemType,vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName FROM menu_items WHERE iFoodMenuId = '" . $iFoodMenuId . "' AND eStatus='Active' AND eAvailable = 'Yes' $ssql ORDER BY iDisplayOrder ASC";
//            $db_item_data = $obj->MySQLSelect($sqlf);
//            $CompanyFoodData[$i]['vMenuItemCount'] = count($db_item_data);
//            if (count($db_item_data) > 0) {
//                for ($j = 0; $j < count($db_item_data); $j++) {
//                    //$fPrice= $db_item_data[$j]['fPrice'];
//                    //$fOfferAmt = $db_item_data[$j]['fOfferAmt'];
//                    if (!empty($vMenu)) {
//                        $db_item_data[$j]['vCategoryName'] = $vMenu;
//                    } else {
//                        $db_item_data[$j]['vCategoryName'] = '';
//                    }
//                    $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($db_item_data[$j]['iMenuItemId'], $iCompanyId, 1, $iUserId, "Display", "", "");
//                    $fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
//                    $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
//                    $db_item_data[$j]['fOfferAmt'] = $fOfferAmt;
//                    $db_item_data[$j]['fPrice'] = round($db_item_data[$j]['fPrice'] * $Ratio, 2);
//                    if ($fOfferAmt > 0) {
//                        /* $fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt)/100);
//                          $fDiscountPrice = round($fDiscountPrice*$Ratio,2);
//                          $StrikeoutPrice = round($fPrice*$Ratio,2); */
//                        $fDiscountPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
//                        $StrikeoutPrice = round($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
//                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
//                        $db_item_data[$j]['fDiscountPrice'] = formatNum($fDiscountPrice);
//                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
//                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
//                    } else {
//                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
//                        $db_item_data[$j]['fDiscountPrice'] = formatNum($fPrice);
//                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
//                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
//                    }
//                    if ($db_item_data[$j]['vImage'] != "") {
//                        $db_item_data[$j]['vImage'] = $tconfig["tsite_upload_images_menu_item"] . "/" . $db_item_data[$j]['vImage'];
//                    }
//                    $MenuItemOptionToppingArr = GetMenuItemOptionsTopping($db_item_data[$j]['iMenuItemId'], $currencySymbol, $Ratio, $vLanguage);
//                    $db_item_data[$j]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
//                    //echo "<pre>";print_r($MenuItemOptionToppingArr);exit;
//                    $CompanyFoodData[$i]['menu_items'][] = $db_item_data[$j];
//                    array_push($MenuItemsDataArr, $db_item_data[$j]);
//                }
//            }
//        }
//    }
//
//    $CompanyFoodData_New = array();
//    $CompanyFoodData_New = $CompanyFoodData;
//    for ($i = 0; $i < count($CompanyFoodData); $i++) {
//        $vMenuItemCount = $CompanyFoodData[$i]['vMenuItemCount'];
//        if ($vMenuItemCount == 0) {
//            unset($CompanyFoodData_New[$i]);
//        }
//    }
//
//    $CompanyFoodData = array_values($CompanyFoodData_New);
//    $returnArr['CompanyFoodData'] = $CompanyFoodData;
//    $returnArr['CompanyFoodDataCount'] = count($CompanyFoodData);
//    if ($searchword != "") {
//        $returnArr['MenuItemsDataArr'] = $MenuItemsDataArr;
//    } else {
//        $returnArr['MenuItemsDataArr'] = array();
//    }
//
//    $Recomendation_Arr = getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, "Recommended", $CheckNonVegFoodType);
//    $returnArr['Recomendation_Arr'] = $Recomendation_Arr;
    ## Get Company's menu details ##


    $sql = "SELECT count(DISTINCT(fm.iFoodMenuId)) As totalcount FROM menu_items as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId=mi.iFoodMenuId WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND mi.eStatus='Active'";
    $db_food_data = $obj->MySQLSelect($sql);
    $returnArr['CompanyFoodData'] = '';
    $returnArr['CompanyFoodDataCount'] = $db_food_data[0]['totalcount'];


    return $returnArr;
}

function getSelectedPaymentGatewayValue() {
    global $generalobj, $obj;
    $SPGQuery = 'SELECT `vValue` FROM `configurations` WHERE `vName` = "SELECTED_PAYMENT_GATEWAY";';
    $SPGDetails = $obj->MySQLSelect($SPGQuery);
    return $SPGDetails[0]['vValue'];
}

function getRequiredCustomerDetailsFromId($iUserId = null) {
    global $generalobj, $obj;
    $CustQuery = "SELECT CONCAT(`vName`, ' ', `vLastName`) AS name,`vCountry`,`vState`,`vPhone`,`vEmail`,`tDestinationAddress`, `vZip`  FROM `register_user` WHERE `iUserId` = '$iUserId';";
    $CustDetails = $obj->MySQLSelect($CustQuery);

    $CustData = $CustDetails[0];
    return $CustData;
}

function getCustomerAccountsDetails($iUserId = null) {
    global $generalobj, $obj;
    $CustQuery = "SELECT `iAccountId`  FROM `register_user` WHERE `iUserId` = '$iUserId';";
    $CustDetails = $obj->MySQLSelect($CustQuery);

    return $CustDetails[0]['iAccountId'];
}



function getCardConnectDetails()
{
    $result = array();
        global $generalobj, $obj;
    $CCPQuery = 'SELECT  `vName`,`vValue` FROM `configurations` WHERE `vName` LIKE "CARDCONNECT%" AND `eStatus` = "Active" AND `eType` = "Payment";';
    $CCPDetails = $obj->MySQLSelect($CCPQuery);
    foreach($CCPDetails as $CCPDetail)
    {
        $result[$CCPDetail['vName']] = $CCPDetail['vValue'];
    }
    
    return $result;
}




function checkPostipExistfororder($iOrderId = null)
{
    $result = 'N';
    global $generalobj, $obj;
    $OrderQuery = 'SELECT `posttipvalue` FROM `orders` WHERE `iOrderId` = "'.$iOrderId.'";';
    $OrderDetails = $obj->MySQLSelect($OrderQuery);
    
    if($OrderDetails[0]['posttipvalue'] != 'no')
    {
        $result = 'Y';
    }
        
    return $result;
    
}
function generatecheckdigit($upc_code)
{
    $odd_total  = 0;
    $even_total = 0;
 
    for($i=0; $i<11; $i++)
    {
        if((($i+1)%2) == 0) {
            /* Sum even digits */
            $even_total += $upc_code[$i];
        } else {
            /* Sum odd digits */
            $odd_total += $upc_code[$i];
        }
    }
 
    $sum = (3 * $odd_total) + $even_total;
 
    /* Get the remainder MOD 10*/
    $check_digit = $sum % 10;
 
    /* If the result is not zero, subtract the result from ten. */
    return ($check_digit > 0) ? 10 - $check_digit : $check_digit;
}
?>