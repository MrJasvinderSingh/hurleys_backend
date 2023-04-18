<?php
include_once('include_config.php');
include_once(TPATH_CLASS.'configuration.php');
                                    
updatecompanylatlong("22.691585","72.863363",44);   exit;
function updatecompanylatlong($latitude,$longitude,$iCompanyId){
    global $obj, $generalobj, $tconfig,$GOOGLE_SEVER_API_KEY_WEB; 
    
    if(SITE_TYPE == "Live"){
      $url = "https://maps.googleapis.com/maps/api/geocode/json?key=".$GOOGLE_SEVER_API_KEY_WEB."&language=en&latlng=".$latitude.",".$longitude;
      $jsonfile = file_get_contents($url);
      $jsondata = json_decode($jsonfile);
      $location_Address = $jsondata->results[0]->formatted_address;   
      $latitude_new = $jsondata->results[0]->geometry->location->lat;
      $longitude_new = $jsondata->results[0]->geometry->location->lng; 
      if($location_Address == "" || $location_Address == NULL){
         $FilterArray = array(0.0015,0.0020,0.0025,0.0030,0.0035,0.0040);
         $k = array_rand($FilterArray);
         $num = $FilterArray[$k];
         $latitude_new = $latitude+$num;
         $longitude_new = $longitude+$num;
         $location_Address =  getAddressFromLocation($latitude_new, $longitude_new, $GOOGLE_SEVER_API_KEY_WEB);
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
  exit;
########################################################################################################################


$input = array("a", "b", "c", "d", "e","f", "g", "h", "i", "j");

$output = array_slice($input, 0, 2);      // returns "c", "d", and "e"
$output1 = array_slice($input, 2, 2);  // returns "d"
$output2 = array_slice($input, 4, 2); 
$output3 = array_slice($input, 6, 2);
$output4 = array_slice($input, 8, 2);
echo "<pre>";print_r($output);echo "<br /><hr />";
echo "<pre>";print_r($output1);echo "<br /><hr />";
echo "<pre>";print_r($output2);echo "<br /><hr />";
echo "<pre>";print_r($output3);echo "<br /><hr />";
echo "<pre>";print_r($output4);echo "<br /><hr />";
exit;
########################################################################################################################
//$json  = '{"status":"transaction status","statuscode":"transaction status code","token":"provided token","description":"transaction description","amount":"successful amount", "mobile":"mobile subsribers number","transaction_type":"the type of a transaction"}';
//$event = json_decode($json,true);
//echo "<pre>";print_r($event);exit;

$OrderDetails =  array('0' => array('iMenuItemId' => '38',"iFoodMenuId"=>'34',"vOptionId"=>'344',"vAddonId"=>'347,348',"iQty"=>'1'),'1' => array('iMenuItemId' => '39',"iFoodMenuId"=>'35',"vOptionId"=>'349',"vAddonId"=>'',"iQty"=>'1'));             
  
echo $isAllItemAvailable = checkmenuitemavailability($OrderDetails);
function checkmenuitemavailability($OrderDetails = array()){
    global $obj, $generalobj, $tconfig;
   $isAllItemAvailable = "Yes";
   if(count($OrderDetails) > 0){
      for($i=0 ; $i < count($OrderDetails) ; $i++){
        $iMenuItemId = $OrderDetails[$i]['iMenuItemId'];
        $str = "select eAvailable,eStatus from menu_items where iMenuItemId ='".$iMenuItemId."'";
        $db_menu_item = $obj->MySQLSelect($str);
        $eStatus = $db_menu_item[0]['eStatus']; 
        $eAvailable = $db_menu_item[0]['eAvailable']; 
        if($eAvailable == "No" || $eStatus != "Active"){
          $isAllItemAvailable = "No";
          break; 
        }
      }
   }
   return $isAllItemAvailable;
} 
echo "<pre>";print_r($OrderDetails);exit;



##########################################################################################################################
echo $fOffersDiscount = CalculateOrderDiscountPrice("123");exit;
getMenuItemPriceByCompanyOffer("19","11","1","0");
########################### Get Menu Item Price By Restaurant Offer Wise##################################################

function getMenuItemPriceByCompanyOffer($iMenuItemId,$iCompanyId,$iQty=1,$iUserId=0){      
   global $obj, $generalobj, $tconfig;
   
   $TotOrders = 0; 
   if($iUserId > 0){
     $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='".$iUserId."' AND iCompanyId = '".$iCompanyId."' AND iStatusCode = '6'";
     $db_order = $obj->MySQLSelect($sql);
     $TotOrders = $db_order[0]['TotOrders']; 
   }
   
   $str = "select iFoodMenuId,fPrice,fOfferAmt from menu_items where iMenuItemId ='".$iMenuItemId."'";
   $db_price = $obj->MySQLSelect($str);
   $fPrice = $db_price[0]['fPrice'];
   $fPrice = $fPrice * $iQty; 
   $fOriginalPrice = $fPrice;  
   
   $sql = "SELECT * FROM `company` WHERE iCompanyId = '".$iCompanyId."'";
   $DataCompany = $obj->MySQLSelect($sql);
   $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
   $fOfferType = $DataCompany[0]['fOfferType'];
   $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
   $fTargetAmt = $DataCompany[0]['fTargetAmt'];
   
   
   if($fOfferAppyType == "None"){
     $fOfferAmt = $db_price[0]['fOfferAmt'];
     if($fOfferAmt > 0){
        $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty)/100);
        $fDiscountPrice = round($fDiscountPrice,2);
        $fPrice = $fPrice-$fDiscountPrice;
     }else{
        $fOfferAmt = 0;
        $fDiscountPrice = 0;
     }
     $returnArr['fOriginalPrice']=$fOriginalPrice;
     $returnArr['fDiscountPrice']=$fDiscountPrice;
     $returnArr['fPrice']=$fPrice;
     $returnArr['fOfferAmt']=$fOfferAmt;
   }else if($fOfferAppyType == "All"){     
     $fOfferAmt = $DataCompany[0]['fOfferAmt'];  
     if($fTargetAmt == 0 || $fTargetAmt == ""){
       if($fOfferType == "Percentage"){  
         if($fOfferAmt > 0){
            $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty)/100);
            $fDiscountPrice = round($fDiscountPrice,2);   
            $fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0))?$fMaxOfferAmt:$fDiscountPrice;
            $fPrice = $fOriginalPrice-$fDiscountPrice;
         }else{
            $fOfferAmt = 0;
            $fDiscountPrice = 0;
         }
       }else{
         if($fOfferAmt > 0){
            $fDiscountPrice = $fOfferAmt * $iQty;
            $fDiscountPrice = ($fDiscountPrice < 0)?0:$fDiscountPrice;
            $fPrice = $fOriginalPrice-$fDiscountPrice;
         }else{
            $fOfferAmt = 0;
            $fDiscountPrice = 0;
         }
       }
     }else{
       $fOfferAmt = 0;
       $fDiscountPrice = 0;
     }  
     $returnArr['fOriginalPrice']=$fOriginalPrice;
     $returnArr['fDiscountPrice']=$fDiscountPrice;
     $returnArr['fPrice']=$fPrice;
     $returnArr['fOfferAmt']=$fOfferAmt;
   }else{    
     if($TotOrders == 0){         
         $fOfferAmt = $DataCompany[0]['fOfferAmt'];  
         if($fPrice > $fTargetAmt){
             if($fOfferType == "Percentage"){
               if($fOfferAmt > 0){
                  $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty)/100);
                  $fDiscountPrice = round($fDiscountPrice,2);
                  $fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0))?$fMaxOfferAmt:$fDiscountPrice;
                  $fPrice = $fOriginalPrice-$fDiscountPrice;
               }else{
                  $fOfferAmt = 0;
                  $fDiscountPrice = 0;
               }
             }else{
               if($fOfferAmt > 0){
                  $fDiscountPrice = $fOfferAmt * $iQty;
                  $fDiscountPrice = ($fDiscountPrice < 0)?0:$fDiscountPrice;
                  $fPrice = $fOriginalPrice-$fDiscountPrice;
               }else{
                  $fOfferAmt = 0;
                  $fDiscountPrice = 0;
               }
             }
         }else{
            $fOfferAmt = 0;
            $fDiscountPrice = 0;
         }     
     }else{
         $fOfferAmt = $db_price[0]['fOfferAmt'];
         if($fOfferAmt > 0){
            $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty)/100);
            $fDiscountPrice = round($fDiscountPrice,2);
            $fPrice = $fOriginalPrice-$fDiscountPrice;
         }else{
            $fOfferAmt = 0;
            $fDiscountPrice = 0;
         }
     }   
     $returnArr['fOriginalPrice']=$fOriginalPrice;
     $returnArr['fDiscountPrice']=$fDiscountPrice;
     $returnArr['fPrice']=$fPrice; 
     $returnArr['fOfferAmt']=$fOfferAmt;
   }
   echo "<pre>";print_r($returnArr);exit; 
   return $returnArr;
}
//echo "<pre>";print_r($returnArr);exit;
########################### Get Menu Item Price By Restaurant Offer Wise##################################################
########################### Get Total Order Discount Amount From order detail for menu item wise##########################
function getOrderDetailTotalDiscountPrice($iOrderId){
  global $generalobj, $obj, $tconfig;
  
  $sql = "SELECT SUM( `fTotalDiscountPrice` ) AS TotalDiscountPrice FROM order_details WHERE iOrderId = '".$iOrderId."' AND eAvailable = 'Yes'";
	$data = $obj->MySQLSelect($sql);
	$TotalDiscountPrice = $data[0]['TotalDiscountPrice'];
  
  if($TotalDiscountPrice == "" || $TotalDiscountPrice == NULL){
    $TotalDiscountPrice = 0;
  }
  
  return $TotalDiscountPrice;
}
########################### Get Total Order Discount Amount From order detail for menu item wise##########################
########################### Get Total Order Discount Amount From order detail for menu item wise##########################
function getOrderDetailSubTotalPrice($iOrderId){
  global $generalobj, $obj, $tconfig;
  
  $sql = "SELECT SUM( `fOriginalPrice` * `iQty` ) AS TotalOriginalPrice FROM order_details WHERE iOrderId = '".$iOrderId."' AND eAvailable = 'Yes'";
	$data = $obj->MySQLSelect($sql);
	$TotalOriginalPrice = $data[0]['TotalOriginalPrice'];
  
  if($TotalOriginalPrice == "" || $TotalOriginalPrice == NULL){
    $TotalOriginalPrice = 0;
  }
  
  return $TotalOriginalPrice;
}
########################### Get Total Order Discount Amount From order detail for menu item wise##########################
########################### Calculate Order Discount Amount By Company Offer and menu item wise###########################
function CalculateOrderDiscountPrice($iOrderId){
  global $obj, $generalobj, $tconfig;
  $sql="select * from orders where iOrderId='".$iOrderId."'";
	$data_order = $obj->MySQLSelect($sql);
  $iCompanyId = $data_order[0]['iCompanyId']; 
  //$fSubTotal = $data_order[0]['fSubTotal'];
  $fSubTotal = getOrderDetailSubTotalPrice($iOrderId);
  $iUserId = $data_order[0]['iUserId']; 
  $TotOrders = 0; 
  if($iUserId > 0){
     $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='".$iUserId."' AND iCompanyId = '".$iCompanyId."' AND iStatusCode = '6'";
     $db_order = $obj->MySQLSelect($sql);
     $TotOrders = $db_order[0]['TotOrders']; 
  }
  
  $sql = "SELECT * FROM `company` WHERE iCompanyId = '".$iCompanyId."'";
  $DataCompany = $obj->MySQLSelect($sql);
  $fMinOrderValue = $DataCompany[0]['fMinOrderValue'];
  $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
  $fOfferType = $DataCompany[0]['fOfferType'];
  $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
  $fTargetAmt = $DataCompany[0]['fTargetAmt'];
  $fOfferAmt = $DataCompany[0]['fOfferAmt'];
  if($fOfferAppyType == "None"){
     $TotalDiscountPrice = getOrderDetailTotalDiscountPrice($iOrderId);
  }else if($fOfferAppyType == "All"){
    if($fSubTotal > $fTargetAmt){
       if($fOfferType == "Percentage"){
         $fDiscount = (($fSubTotal * $fOfferAmt)/100);
         $fDiscount = round($fDiscount,2);
         $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0))?$fMaxOfferAmt:$fDiscount;
         $TotalDiscountPrice = $fDiscount;
       }else{
         $fDiscount = $fOfferAmt;
         $fDiscount = round($fDiscount,2);
         $TotalDiscountPrice = $fDiscount;
       }
    }else{
      $TotalDiscountPrice = 0;
    }
  }else{
    if($TotOrders == 0){
      if($fSubTotal > $fTargetAmt){
         if($fOfferType == "Percentage"){
           $fDiscount = (($fSubTotal * $fOfferAmt)/100);
           $fDiscount = round($fDiscount,2);
           $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0))?$fMaxOfferAmt:$fDiscount;
           $TotalDiscountPrice = $fDiscount;
         }else{
           $fDiscount = $fOfferAmt;
           $fDiscount = round($fDiscount,2);
           $TotalDiscountPrice = $fDiscount;
         }
      }else{
        $TotalDiscountPrice = 0;
      }
    }else{
      $TotalDiscountPrice = getOrderDetailTotalDiscountPrice($iOrderId);
    }
  }
  
  return $TotalDiscountPrice;
}
########################### Calculate Order Discount Amount By Company Offer and menu item wise###########################




$PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL = 30;
$iOrderId = 108;
  $sql = "SELECT * from driver_request WHERE iOrderId ='".$iOrderId."' ORDER BY iDriverRequestId DESC LIMIT 0,1";
   $db_driver_request = $obj->MySQLSelect($sql);
   
   $datedifference = 0;
   if(count($db_driver_request) > 0){
     $currentdate = @date("Y-m-d H:i:s");
     $currentdate = strtotime($currentdate);
     $dAddedDate = $db_driver_request[0]['dAddedDate']; 
     $dAddedDate = strtotime($dAddedDate);
     $datedifference =  $currentdate-$dAddedDate;
   }
   
    if($datedifference > 30){
      $datedifference = 0;
   }
   
   $Remaining_Time_In_Seconds = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL - $datedifference;  
   
   echo $Remaining_Time_In_Seconds;exit;
UpdateCardPaymentPendingOrder();

function UpdateCardPaymentPendingOrder(){
    global $generalobj, $obj;
    $currentdate = @date("Y-m-d H:i:s");
    $checkdate = date('Y-m-d H:i:s', strtotime("-120 minutes", strtotime($currentdate)));
    
echo    $sql = "SELECT iOrderId FROM orders WHERE dDeliveryDate < '".$checkdate."' AND iStatusCode = 12 AND ePaymentOption = 'Card'";
    $db_order = $obj->MySQLSelect($sql);  
    if(count($db_order) > 0){
       for($i=0;$i<count($db_order);$i++){
         $iOrderId = $db_order[$i]['iOrderId'];
         
         $sql="delete from order_details where iOrderId='".$iOrderId."'";
		     $obj->sql_query($sql);
         
         $sqld="delete from orders where iOrderId='".$iOrderId."'";
		     $obj->sql_query($sqld);
         exit;       
       }
    }
    
    return true;
}  
  exit;
echo $currentdate = @date("Y-m-d H:i:s");echo "<br />";
echo $checkdate = date('Y-m-d H:i:s', strtotime("-120 minutes", strtotime($currentdate)));  

echo $fDeliveryCharge = getOrderDeliveryCharge("9","101.5");exit; 

function getOrderDeliveryCharge($iOrderId,$fSubTotal){
  global $generalobj, $obj, $tconfig;
  
  $fDeliveryCharge = 0;
  $sql = "SELECT ord.iUserId,ord.iCompanyId,ua.vLatitude as passengerlat,ua.vLongitude as passengerlong,co.vRestuarantLocationLat as restaurantlat,co.vRestuarantLocationLong as restaurantlong FROM orders as ord LEFT JOIN user_address as ua ON ord.iUserAddressId=ua.iUserAddressId LEFT JOIN company as co ON ord.iCompanyId=co.iCompanyId WHERE ord.iOrderId = '".$iOrderId."'";
	$data = $obj->MySQLSelect($sql);
  
  if(count($data) > 0){
     $User_Address_Array = array($data[0]['passengerlat'],$data[0]['passengerlong']);
     $iLocationId = GetUserGeoLocationId($User_Address_Array);
     if($iLocationId > 0){
       $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '".$iLocationId."'";
	     $data_location = $obj->MySQLSelect($sql);
       $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius']; 
       $distance = distanceByLocation($data[0]['passengerlat'], $data[0]['passengerlong'], $data[0]['restaurantlat'], $data[0]['restaurantlong'], "K");
       if($distance < $iFreeDeliveryRadius){
          $fDeliveryCharge = 0;
          return $fDeliveryCharge;
       }
       $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal']; 
       if($fSubTotal > $fFreeOrderPriceSubtotal){
          $fDeliveryCharge = 0;
          return $fDeliveryCharge;
       }
       
       $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
       $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];
       $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
       if($fSubTotal > $fOrderPriceValue){
          $fDeliveryCharge = $fDeliveryChargeAbove;
          return $fDeliveryCharge;
       }else{
          $fDeliveryCharge = $fDeliveryChargeBelow;
          return $fDeliveryCharge;
       }
     }else{
       $fDeliveryCharge = 0;
       return $fDeliveryCharge;
     }
     
  }
}


$passengerLat = "23.0127772";
$passengerLon = "72.5038952";
$Address_Array = array($passengerLat,$passengerLon);
echo $iLocationId = GetUserGeoLocationId($Address_Array);    exit;

function GetUserGeoLocationId($Address_Array){
		global $generalobj, $obj;
		
    $iLocationId = "0";     
    if(!empty($Address_Array)){
			$sqlaa = "SELECT * FROM location_master WHERE eStatus='Active'";
			$allowed_data = $obj->MySQLSelect($sqlaa);   
			if(!empty($allowed_data)){
				$polygon = array();
				foreach($allowed_data as $key => $val) {
					$latitude = explode(",",$val['tLatitude']);
					$longitude = explode(",",$val['tLongitude']);
					for ($x = 0; $x < count($latitude); $x++) {
						if(!empty($latitude[$x]) || !empty($longitude[$x])) {
							$polygon[$key][] = array($latitude[$x],$longitude[$x]);
						}
					}
					//print_r($polygon[$key]);
					if($polygon[$key]){
						
            $address = contains($Address_Array,$polygon[$key]) ? 'IN' : 'OUT';
						if($address == 'IN'){
							$iLocationId = $val['iLocationId'];
              break;
						}
					}
				}    
			} 
		}     
		return $iLocationId;
}



$Rarr = DisplayOrderDetailItemList("30","1",$eUserType="Passenger"); 
echo "<pre>";print_r($Rarr);exit; 

function DisplayOrderDetailItemList($iOrderDetailId,$iMemberId,$eUserType="Passenger"){      
   global $obj, $generalobj, $tconfig;
             
   $returnArr = array();
   if($eUserType=="Passenger"){
      $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId);
   }else if($eUserType=="Driver"){
      $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId);
   }else{
      $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId);
   } 
   
   $currencySymbol = $UserDetailsArr['currencySymbol'];
   $Ratio = $UserDetailsArr['Ratio'];
   $vLang = $UserDetailsArr['vLang']; 
   
   $sql="select od.*,mi.vItemType_".$vLang." as MenuItem from `order_details` as od LEFT JOIN  `menu_items` as mi ON od.iMenuItemId=mi.iMenuItemId where od.iOrderDetailId='".$iOrderDetailId."'";
	 $data_order_detail = $obj->MySQLSelect($sql);
   $MenuItem = $data_order_detail[0]['MenuItem'];
   $fPrice = $data_order_detail[0]['fPrice'];
   $fPriceArr = getPriceUserCurrency($iMemberId,$eUserType,$fPrice); 
   $fPrice = $fPriceArr['fPricewithsymbol'];
   $returnArr['iQty'] = $data_order_detail[0]['iQty'];
   $returnArr['MenuItem'] = $MenuItem;
   $returnArr['fPrice'] = $fPrice;
   
   $vOptionId = $data_order_detail[0]['vOptionId'];
   if($vOptionId != ""){
       $vOptionName = get_value('menuitem_options','vOptionName','iOptionId',$vOptionId,'','true');
       $vOptionPrice = $data_order_detail[0]['vOptionPrice'];
       $vOptionPriceArr = getPriceUserCurrency($iMemberId,$eUserType,$vOptionPrice);
       $vOptionPrice = $vOptionPriceArr['fPricewithsymbol'];
       $returnArr['vOptionName'] = $vOptionName;
       $returnArr['vOptionPrice'] = $vOptionPrice;
   }else{
       $returnArr['vOptionName'] = "";
       $returnArr['vOptionPrice'] = "";
   }
   
   $tAddOnIdOrigPrice = $data_order_detail[0]['tAddOnIdOrigPrice'];
   if($tAddOnIdOrigPrice != ""){
      $AddonItemsArr = array();
      $AddonItemsDetailArr = explode(",",$tAddOnIdOrigPrice);
      for($i=0;$i<count($AddonItemsDetailArr);$i++){
         $AddonItemsStrArr = explode("#",$AddonItemsDetailArr[$i]);
         $AddonItemsId = $AddonItemsStrArr[0];
         $AddonItemsPrice = $AddonItemsStrArr[1]; 
         $AddonItemsPriceArr = getPriceUserCurrency($iMemberId,$eUserType,$AddonItemsPrice);
         $AddonItemPrice = $AddonItemsPriceArr['fPricewithsymbol'];
         $AddonItemName = get_value('menuitem_options','vOptionName','iOptionId',$AddonItemsId,'','true');  
         $AddonItemsArr[$i]['vAddOnItemName'] = $AddonItemName;
         $AddonItemsArr[$i]['AddonItemPrice'] = $AddonItemPrice;
      }
      $returnArr['AddOnItemArr'] = $AddonItemsArr; 
   }else{
      $returnArr['AddOnItemArr'] = array();
   }
   
   return $returnArr; 
}




$iMemberId = "1";
$eUserType = "Company";
$fPrice = 12.50;
$priceArr = getPriceUserCurrency($iMemberId,$eUserType,$fPrice);
echo "<pre>";print_r($priceArr);exit;


function getPriceUserCurrency($iMemberId,$eUserType="Passenger",$fPrice){      
   global $obj, $generalobj, $tconfig;
             
   $returnArr = array();
   if($eUserType=="Passenger"){
      $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId);
   }else if($eUserType=="Driver"){
      $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId);
   }else{
      $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId);
   } 
   
   $currencySymbol = $UserDetailsArr['currencySymbol'];
   $Ratio = $UserDetailsArr['Ratio'];
   $fPrice = round(($fPrice*$Ratio),2);
   $fPricewithsymbol = $currencySymbol." ".$fPrice;
   
   $returnArr['fPrice']=$fPrice;
   $returnArr['fPricewithsymbol']=$fPricewithsymbol;
   $returnArr['currencySymbol']=$currencySymbol;
   return $returnArr; 
}


$iOptionId = "8,9,10";
echo $price = GetFoodMenuItemAddOnIdPriceString($iOptionId);
exit;

function GetFoodMenuItemAddOnIdPriceString($vAddonId=""){
  global $generalobj, $obj, $tconfig;
  if($vAddonId != ""){
    $vAddonIdArr = explode(",",$vAddonId);
    $AddOnIdPriceString = "";
    if(count($vAddonIdArr) > 0){
      for($i=0;$i<count($vAddonIdArr);$i++){
          $OptionId = $vAddonIdArr[$i];
          $str = "select fPrice from `menuitem_options` where iOptionId = '".$OptionId."'";
          $db_price = $obj->MySQLSelect($str);
          $fPrice = $db_price[0]['fPrice'];  
          $AddOnIdPriceString .= $OptionId."#".$fPrice.","; 
      }
      
      $AddOnIdPriceString = substr($AddOnIdPriceString,0,-1);
    }  
  }else{
    $AddOnIdPriceString = "";
  }
  
  return $AddOnIdPriceString; 
}

$addressarr = GetUserSelectedAddress("1","Passenger");
echo "<pre>";print_r($addressarr);exit;
function GetUserSelectedAddress($iUserId,$eUserType="Passenger"){
  global $obj, $generalobj, $tconfig;
  $returnArr = array();
  
  if($eUserType == "Passenger"){
    $UserType = "Rider";
  }else{
    $UserType = "Driver";
  }
  
  $sql = "SELECT * from user_address WHERE iUserId = '".$iUserId."' AND eUserType = '".$UserType."' AND eStatus = 'Active'";
	$result_Address = $obj->MySQLSelect($sql);
	$ToTalAddress = count($result_Address); 
  if($ToTalAddress > 0){
     ## Checking First Last Orders Selected Address ##
     $sqlo = "SELECT ord.iUserAddressId,ua.eStatus,ua.vServiceAddress,ua.vBuildingNo,ua.vLandmark,ua.vAddressType,ua.vLatitude,ua.vLongitude from orders as ord LEFT JOIN user_address as ua ON ord.iUserAddressId=ua.iUserAddressId WHERE ord.iUserId = '".$iUserId."' ORDER BY ord.iOrderId DESC limit 0,1";
	   $last_order_Address = $obj->MySQLSelect($sqlo);
     $iUserAddressId = $last_order_Address[0]['iUserAddressId'];
     if(count($last_order_Address) > 0 && $iUserAddressId > 0){
       $eStatus = $last_order_Address[0]['eStatus'];
       if($eStatus == "Active"){
         $vAddressType = $last_order_Address[0]['vAddressType'];
  			 $vBuildingNo = $last_order_Address[0]['vBuildingNo'];
  			 $vLandmark = $last_order_Address[0]['vLandmark'];
  			 $vServiceAddress = $last_order_Address[0]['vServiceAddress'];
         $PickUpAddress = ($vAddressType != "")? $vAddressType."\n" :"";
				 $PickUpAddress .= ($vBuildingNo != "")? $vBuildingNo."," :"";
				 $PickUpAddress .= ($vLandmark != "")? $vLandmark."\n" :"";
				 $PickUpAddress .= ($vServiceAddress != "")? $vServiceAddress :"";
         $PickUpLatitude = $last_order_Address[0]['vLatitude'];
         $PickUpLongitude = $last_order_Address[0]['vLongitude'];
         $returnArr['UserSelectedAddress'] = $PickUpAddress;
         $returnArr['UserSelectedLatitude'] = $PickUpLatitude;
         $returnArr['UserSelectedLongitude'] = $PickUpLongitude;
       }else{
         $returnArr['UserSelectedAddress'] = "";
         $returnArr['UserSelectedLatitude'] = "";
         $returnArr['UserSelectedLongitude'] = "";
       }
     }else{
         $vAddressType = $result_Address[0]['vAddressType'];
  			 $vBuildingNo = $result_Address[0]['vBuildingNo'];
  			 $vLandmark = $result_Address[0]['vLandmark'];
  			 $vServiceAddress = $result_Address[0]['vServiceAddress'];
         $PickUpAddress = ($vAddressType != "")? $vAddressType."\n" :"";
				 $PickUpAddress .= ($vBuildingNo != "")? $vBuildingNo."," :"";
				 $PickUpAddress .= ($vLandmark != "")? $vLandmark."\n" :"";
				 $PickUpAddress .= ($vServiceAddress != "")? $vServiceAddress :"";
         $PickUpLatitude = $result_Address[0]['vLatitude'];
         $PickUpLongitude = $result_Address[0]['vLongitude'];
         $returnArr['UserSelectedAddress'] = $PickUpAddress;
         $returnArr['UserSelectedLatitude'] = $PickUpLatitude;
         $returnArr['UserSelectedLongitude'] = $PickUpLongitude;
     }  
     ## Checking First Last Orders Selected Address ##
  }else{
     $returnArr['UserSelectedAddress'] = "";
     $returnArr['UserSelectedLatitude'] = "";
     $returnArr['UserSelectedLongitude'] = "";
  }
  
  return $returnArr;
}

$fPrice = 454.00;  
$Ratio = 1.0000;
echo $fUserPrice = number_format($fPrice*$Ratio,2);exit;
GetMenuItemOptionsTopping("1","$","1.0000","EN");

function GetMenuItemOptionsTopping($iMenuItemId,$currencySymbol,$Ratio,$vLanguage){  
  global $obj, $generalobj, $tconfig;
  $returnArr = array();
  $sql = "SELECT iOptionId,vOptionName,fPrice,eOptionType FROM menuitem_options WHERE iMenuItemId = '".$iMenuItemId."'";
	$db_options_data = $obj->MySQLSelect($sql);
  if(count($db_options_data) > 0){
    for($i=0;$i<count($db_options_data);$i++){
        $fPrice = $db_options_data[$i]['fPrice'];
        $fUserPrice = round(($fPrice*$Ratio),2);
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
  }
  echo "<pre>";print_r($returnArr);exit;
  return $returnArr;
} 

function getUserCurrencyLanguageDetails($iUserId=""){      
   global $obj, $generalobj, $tconfig;
   
   $returnArr = array();
   if($iUserId != ""){
     $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '".$iUserId."'";
     $passengerData = $obj->MySQLSelect($sqlp);
     $currencycode = $passengerData[0]['vCurrencyPassenger'];
     $vLanguage = $passengerData[0]['vLang'];
     $currencySymbol = $passengerData[0]['vSymbol'];
     $Ratio = $passengerData[0]['Ratio']; 
     
     if($vLanguage == "" || $vLanguage == NULL){
    		$vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
     }
     if($currencycode == "" || $currencycode == NULL){
       $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
       $currencyData = $obj->MySQLSelect($sqlp);
       $currencycode = $currencyData[0]['vName'];
       $currencySymbol = $currencyData[0]['vSymbol'];
       $Ratio = $currencyData[0]['Ratio'];
     }
   }else{
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
function getDriverCurrencyLanguageDetails($iDriverId=""){      
   global $obj, $generalobj, $tconfig;
   
   $returnArr = array();
   if($iDriverId != ""){
     $sqlp = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '".$iDriverId."'";
     $passengerData = $obj->MySQLSelect($sqlp);
     $currencycode = $passengerData[0]['vCurrencyDriver'];
     $vLanguage = $passengerData[0]['vLang'];
     $currencySymbol = $passengerData[0]['vSymbol'];
     $Ratio = $passengerData[0]['Ratio']; 
     
     if($vLanguage == "" || $vLanguage == NULL){
    		$vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
     }
     if($currencycode == "" || $currencycode == NULL){
       $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
       $currencyData = $obj->MySQLSelect($sqlp);
       $currencycode = $currencyData[0]['vName'];
       $currencySymbol = $currencyData[0]['vSymbol'];
       $Ratio = $currencyData[0]['Ratio'];
     }
   }else{
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
function getCompanyCurrencyLanguageDetails($iCompanyId=""){      
   global $obj, $generalobj, $tconfig;
   
   $returnArr = array();
   if($iCompanyId != ""){
     $sqlp = "SELECT co.vCurrencyCompany,co.vLang,cu.vSymbol,cu.Ratio FROM company as co LEFT JOIN currency as cu ON co.vCurrencyCompany = cu.vName WHERE iCompanyId = '".$iCompanyId."'";
     $passengerData = $obj->MySQLSelect($sqlp);
     $currencycode = $passengerData[0]['vCurrencyCompany'];
     $vLanguage = $passengerData[0]['vLang'];
     $currencySymbol = $passengerData[0]['vSymbol'];
     $Ratio = $passengerData[0]['Ratio']; 
     
     if($vLanguage == "" || $vLanguage == NULL){
    		$vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
     }
     if($currencycode == "" || $currencycode == NULL){
       $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
       $currencyData = $obj->MySQLSelect($sqlp);
       $currencycode = $currencyData[0]['vName'];
       $currencySymbol = $currencyData[0]['vSymbol'];
       $Ratio = $currencyData[0]['Ratio'];
     }
   }else{
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

		function clean($str) {
    global $obj;  
		$str = trim($str);
		//$str = mysqli_real_escape_string($str);
    $str = $obj->SqlEscapeString($str);
		$str = htmlspecialchars($str);
		$str = strip_tags($str);
		return($str);       
	}

  function contains($point, $polygon)
	{
	    if($polygon[0] != $polygon[count($polygon)-1])
	        $polygon[count($polygon)] = $polygon[0];
	    $j = 0;
	    $oddNodes = false;
	    $x = $point[1];
	    $y = $point[0];
	    $n = count($polygon);
	    for ($i = 0; $i < $n; $i++)
	    {
	        $j++;
	        if ($j == $n)
	        {
	            $j = 0;
	        }
	        if ((($polygon[$i][0] < $y) && ($polygon[$j][0] >= $y)) || (($polygon[$j][0] < $y) && ($polygon[$i][0] >=
	            $y)))
	        {
	            if ($polygon[$i][1] + ($y - $polygon[$i][0]) / ($polygon[$j][0] - $polygon[$i][0]) * ($polygon[$j][1] -
	                $polygon[$i][1]) < $x)
	            {
	                $oddNodes = !$oddNodes;
	            }
	        }
	    }
	    return $oddNodes;
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
?>