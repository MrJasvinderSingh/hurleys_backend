<?php
include_once('../common.php');
include_once('../generalFunctions.php');
if(!isset($generalobjAdmin)){
    require_once(TPATH_CLASS."class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$default_lang 	= $generalobj->get_default_lang();
$orderdata = '';
$orderHeader = '';
$orderFooter = '';
if(isset($_POST['orderId'])){
	$iOrderId = $_POST['orderId'];
	$sql = "SELECT o.vTimeZone,o.pickuptime,o.cookingtime,o.mattime,o.deliverytime,o.fSubTotal,o.iServiceid,sc.vServiceName_".$default_lang." as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,c.vCaddress,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem, u.vPhone as user_phone,u.tDestinationAddress,ua.vLatitude,ua.vLongitude,ua.vServiceAddress,d.vPhone as driver_phone,c.vPhone as resturant_phone FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN  user_address ua ON ua.iUserAddressId = o.iUserAddressId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid where o.iOrderId = $iOrderId order by o.tOrderRequestDate ASC  limit 100";
	// WHERE date > (NOW() - INTERVAL 24 HOUR)
	$DBProcessingOrders = $obj->MySQLSelect($sql);
	
	if(count($DBProcessingOrders) > 0) {
		foreach ($DBProcessingOrders as $val) {
			$driverid = $val['iDriverId'];
			if($val['iStatusCode'] == '1'){
				$status = 'PL';
			}else if($val['iStatusCode'] == '2'){
				$status = 'AC';
			}else if($val['iStatusCode'] == '4'){
				$status = 'AS';
			}else if($driverid != 0){
				$sqq1 = "select iActive from trips where iDriverId = $driverid and iActive not in ('Canceled','Finished')";
				$driverorders = $obj->MySQLSelect($sqq1);
				$active = $ongoing = 0;
				if(count($driverorders) > 0){
					foreach($driverorders as $value){
						if($value['iActive'] == 'Active'){
							$active++;
						}
						if($value['iActive'] == 'On Going Trip'){
							$ongoing++;
						}
					}
					if($active == 0 && $ongoing > 0){
						$status = 'ER';
					}else{
						$status = 'PU';
					}
				}else{
					$status = 'PU';
				}
			}else{
				$status = 'PU';
			}
			if($val['iStatusCode'] == '6'){
				$status = 'DL';
				$statuscolor = '#99b3ff';
			}
			if($val['iStatusCode'] == '8'){
				$status = 'CL';
				$statuscolor = '#a3a3c2';
			}
                        
                        $NewOPtions = '';
                        if($val['iStatusCode'] == 1 )
                        {
                            $NewOPtions = '<option value="1">Accept Order</option>';
                        }
                        if($val['iStatusCode'] == 2 || $val['iStatusCode'] == 4 || $val['iStatusCode'] == 5 )
                        {
                            $NewOPtions .= '<option value="112">Revert State</option>';
                        }
                        if($val['iStatusCode'] == 4 )
                        {
                            $NewOPtions .= '<option value="5">Order Picked Up</option>';
                        }
                        
                        if($val['iStatusCode'] == 5)
                        {
                            $NewOPtions .= '<option value="6">Order Delivered</option>';
                        }
                        $orderFooter = '';
                        
                        
                        $exculdelist = array(6,7,8,9,11);
                       
                        if( !in_array($val['iStatusCode'], $exculdelist))
                        {
                            
                        
                        $orderFooter .= '<form action="#" method="post" class="orderstatuschangeform'.$val['iOrderId'].'"><input type="hidden" name="iOrderId" value="'.$val['iOrderId'].'"/><select name="iStatusCode" id="iStatusCode">'.$NewOPtions.'<option value="8">Cancel Order</option></select><button type="submit" class="order_button_oc ChangeorderStatusButton'.$val['iOrderId'].'" style="margin-bottom: 10px;padding: 7px 16px;margin-left: 5px;">APPLY</button></form>'
                                . '<script>  $(document).ready(function () {
                    $(".ChangeorderStatusButton'.$val['iOrderId'].'").click(function(e) {
                        e.preventDefault();
                        $.ajax({
                            type: "POST",
                            url: "chowcallupdateorderstatusoc.php",
                            dataType: "html",
                            data: $(".orderstatuschangeform'.$val['iOrderId'].'").serialize(), 
                            success: function (response)
                            {
                            
                                $(".Orderstatuschangemessage").text(response); 
                                setInterval(function () {  $(".Orderstatuschangemessage").text(" "); }, 5000);
                                location.reload();
                            }
                        });
                    });
                }); </script>';
                        
                        }
                        
                        if(($val['iStatusCode'] == 2) || ($val['iStatusCode'] == 4) || ($val['iStatusCode'] == 5 )){
                            $OldDriverId = $val["iDriverId"] ? $val["iDriverId"] : '0';
                $orderFooter .= '<a href="javascript:void(0)" class="custom-order order_button_oc" data-toggle="modal" data-id="'.$val["iOrderId"].'" data-status="'.$val["iStatusCode"].'" data-olddriver="'.$OldDriverId.'" >ASSIGN</a>';

            }
//            elseif()
//            {
//                $orderFooter .= '<form action="" method="post"><button type="submit" class=" btn btn-default btn-xs"  onclick="'.$oncl.'">UA</button><input type="hidden" value="1" name="removedrive" ><input type="hidden" name="driverid" value="'.$val['iDriverId'].'"><input type="hidden" value="'.$val['iOrderId'].'" name="orderid" ></form>';
//            }
			$statuscolor = '';
			//print_r($val);
			$date = date('H:i A', strtotime($val['tOrderRequestDate']));
			$timedifference = GetTimeDiffInMinutes($val['tOrderRequestDate']);
			$UserSelectedAddressArr = GetUserAddressDetail($val['iUserId'], "Passenger", $val['iUserAddressId']);
			$date = date('H:i A', strtotime($val['tOrderRequestDate']));
			$DriverValOrder = $val['driverName'] ? '<br/>Driver: '.$val['driverName'] : '';
                        $orderHeader = ' #'.$val['vOrderNo'];
			//$orderdata .= '<div class="col-md-12"><div class="col-md-6">Order No.</div><div class="col-md-6">'.$val['vOrderNo'].'&nbsp;</div></div>';
			//$orderdata .= '<div class="col-md-12"><div class="col-md-6">Order Status</div><div class="col-md-6">'.$status.'&nbsp;</div></div>';
//			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Order Placed Date Time</div><div class="col-md-6">'.$val['tOrderRequestDate'].'&nbsp;</div></div>';
//			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Estimated Pickup Time</div><div class="col-md-6">'.date("H:i A",strtotime($val['cookingtime'])).'&nbsp;</div></div>';
//			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Estimated Delivery Time</div><div class="col-md-6">'.date("H:i A",strtotime($val['dDeliveryDate'])).'&nbsp;</div></div>';
                        
                         $OrderSmsg = array(
            '0'=>'',
            '1'=>'Placed',
            '2'=>'Confirmed',
            '4'=>'Assigned',
            '5'=>'Picked Up',
            '6'=>'Delivered',
            '8'=>'Cancelled'                 
            );
                        
                         $sqlOS = "SELECT os.vStatus,os.vStatus_Track,osl.dDate,osl.iStatusCode,ord.iUserId,ord.iCompanyId,ord.iDriverId,ord.cookingtime,ord.deliverytime,ord.iStatusCode as OrderCurrentStatusCode,ord.iUserAddressId,ord.vOrderNo,ord.tOrderRequestDate,ord.fNetTotal FROM order_status_logs as osl LEFT JOIN order_status as os ON osl.iStatusCode = os.iStatusCode LEFT JOIN orders as ord ON osl.iOrderId=ord.iOrderId WHERE osl.iOrderId = '" . $iOrderId . "' AND osl.iStatusCode NOT IN(7,9,11,12) ORDER BY osl.iOrderLogId ASC";
    $OrderStatus = $obj->MySQLSelect($sqlOS);
    $vTimeZone = $val['vTimeZone'];
    
    $ETA_ORDER = $val['deliverytime'];
    for($i=0; $i<count($OrderStatus); $i++)
    {
    $dDate = $OrderStatus[$i]['dDate'];
    
    // $serverTimeZone = date_default_timezone_get();
  //   $dDate = converToTz($dDate, $vTimeZone, $serverTimeZone);
   // $orderdata .="<pre>". print_r($OrderStatus)."</pre>";
    $orderdata .= '<div class="col-md-12"><div class="col-md-6">'.$OrderSmsg[$OrderStatus[$i]['iStatusCode']].'</div><div class="col-md-6">'.date("H:i A",strtotime($dDate)).'&nbsp;</div></div>';
        if( $OrderStatus[$i]['iStatusCode'] == 6)
        {
            $ETA_ORDER = $dDate;
        }
    }
    
    
    
    $orderdata .= '<div class="col-md-12"><div class="col-md-6">EDT</div><div class="col-md-6">'.date("H:i A",strtotime($ETA_ORDER)).'&nbsp;</div></div>';
                        
			$orderdata .= '<div class="clearfix"><div class="col-md-12 text-center"><button class="order_button_oc allorderdetailsmenuButton'.$val['iOrderId'].'" data-toggle="collapse" data-target="#allorderdetailsmenu'.$val['iOrderId'].'">Order Details</button></div></div><div id="allorderdetailsmenu'.$val['iOrderId'].'" class="collapse"></div>'
                                . '<script>  $(document).ready(function () {
                    $(".allorderdetailsmenuButton'.$val['iOrderId'].'").click(function(e) {
                        e.preventDefault();
                        $.ajax({
                            type: "POST",
                            url: "getOrderMenuDetailsoc.php",
                            dataType: "html",
                            data: {iOrderId:'.$val['iOrderId'].'}, 
                            success: function (response)
                            {
                            
                                $("#allorderdetailsmenu'.$val['iOrderId'].'").html(response); 
                                  //  $(".allorderdetailsmenuButton'.$val['iOrderId'].'").hide();
                               
                            }
                        });
                    });
                }); </script>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-12 text-center"><strong style="padding: 10px 0;text-align:center;width: 100%; text-decoration:underline;">CUSTOMER</strong></div></div><div class="col-md-12"><div class="col-md-6">Customer</div><div class="col-md-6">'.$val['riderName'].'&nbsp;</div></div>';
			
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Phone Number</div><div class="col-md-6">'.CCFormatPhoneNumber($val['user_phone']).'&nbsp;</div></div>';
                        
                        $getSectorAdressarray = array($val['vLatitude'], $val['vLongitude']);
    $sector = getDeliverySector($getSectorAdressarray);
                        $orderdata .= '<div class="col-md-12"><div class="col-md-6">Zone</div><div class="col-md-6">'.$sector['sector'].'&nbsp;</div></div>';
                        
                        $orderdata .= '<div class="col-md-12"><div class="col-md-6">Location</div><div class="col-md-6">'.$val['vServiceAddress'].'&nbsp;</div></div>';
			
			$orderdata .= '<div class="col-md-12"><div class="col-md-12 text-center"><strong style="padding: 5px 0 0 0; display:inline-block; text-align:center;width: 100%; text-decoration:underline; margin-top:5px;">RESTAURANT</strong></div></div><div class="col-md-12"><div class="col-md-6">Restaurant</div><div class="col-md-6">'.$val['vCompany'].'&nbsp;</div></div>';
                        $orderdata .= '<div class="col-md-12"><div class="col-md-6">Phone Number</div><div class="col-md-6">'.CCFormatPhoneNumber($val['resturant_phone']).'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Location</div><div class="col-md-6">'.$val['vCaddress'].'&nbsp;</div></div>';
			
			
			$orderdata .= '<div class="col-md-12"><div class="col-md-12 text-center"><strong style="padding: 5px 0 0 0; display:inline-block; text-align:center;width: 100%; text-decoration:underline;">DRIVER</strong></div></div><div class="col-md-12"><div class="col-md-6">Driver</div><div class="col-md-6">'.$val['driverName'].'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Phone Number</div><div class="col-md-6">'.CCFormatPhoneNumber($val['driver_phone']).'&nbsp;</div></div>';
			
			//$orderdata .= '<tr style="'.$cls.'  padding:2px; "><td attr="'.$val['iOrderId'].'" style=" width: 60%; text-align: left; vertical-align: middle;cursor:pointer;" class="tdoperationcls"><strong>' . substr($val['vOrderNo'], -3) . '</strong>-<strong>'.substr($val['vCompany'], 0,10 ).'</strong> <br/><p>'.substr($UserSelectedAddressArr['UserAddress'], 0,20).'</p> </td><td style="text-align: left; vertical-align: middle; width: 25%;">'.$status.'<br/>'.$date.'</td><td style="width: 15%; vertical-align: middle;">'.$assign.'</td></tr>';
			
		}
	}
}
//'totalodersStatus'=>$totalorderstatus
$array = array('res'=>$orderdata,'appendpopdataheader'=>$orderHeader,'appendpopdatafooter'=>$orderFooter);
echo json_encode($array);
exit;
?>

