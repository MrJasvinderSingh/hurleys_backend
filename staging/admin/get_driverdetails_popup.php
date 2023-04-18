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
$processing_status_array = array('1','2','4','5');
if(isset($_POST['driverid'])){
	$driverid = $_POST['driverid'];
	$Startdate = date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d')))).' 00:00:01';
	$Enddate = date('Y-m-d H:i:s');
	$sql1 = "SELECT count(*) as total,CONCAT(register_driver.vName,' ',register_driver.vLastName) AS FULLNAME,register_driver.vPhone FROM orders LEFT JOIN register_driver  ON register_driver.iDriverId = orders.iDriverId WHERE register_driver.iDriverId = $driverid AND orders.tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate'";
	$DBProcessingOrders1 = $obj->MySQLSelect($sql1);
	//echo $DBProcessingOrders1[0]['total'];
	
	$sql = "SELECT orders.iCompanyId,orders.iUserId,orders.iUserAddressId,orders.iOrderId,orders.iStatusCode,orders.iDriverId,CONCAT(register_driver.vName,' ',register_driver.vLastName) AS FULLNAME,register_driver.vPhone, orders.vOrderNo,orders.cookingtime,orders.deliverytime,company.vCompany,company.vPhone as resturant_phone FROM orders LEFT JOIN register_driver  ON register_driver.iDriverId = orders.iDriverId LEFT JOIN company ON company.iCompanyId = orders.iCompanyId WHERE orders.iDriverId = $driverid limit 3";
	//$sql = "SELECT orders.iDriverId,CONCAT(register_driver.vName,' ',register_driver.vLastName) AS FULLNAME,register_driver.vPhone, orders.vOrderNo,orders.cookingtime,orders.deliverytime FROM orders LEFT JOIN register_driver  ON register_driver.iDriverId = orders.iDriverId WHERE orders.iDriverId = $driverid AND `orders.iStatusCode` IN (4,5)";
	// WHERE date > (NOW() - INTERVAL 24 HOUR)
	$DBProcessingOrders = $obj->MySQLSelect($sql);
	//print_r($DBProcessingOrders);die;
	if(count($DBProcessingOrders) > 0) {
		$i = 0;
		foreach ($DBProcessingOrders as $val) {
			$driverid = $val['iDriverId'];
			if($val['iStatusCode'] == '4'){
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
			$date = date('H:i', strtotime($val['tOrderRequestDate']));
			$timedifference = GetTimeDiffInMinutes($val['tOrderRequestDate']);
			$UserSelectedAddressArr = GetUserAddressDetail($val['iUserId'], "Passenger", $val['iUserAddressId']);
			$date = date('H:i', strtotime($val['tOrderRequestDate']));
			if($i == 0){
				$orderdata .= '<div class="col-md-12 fontfourten"><div class="col-md-6">Driver Name</div><div class="col-md-6">'.$val['FULLNAME'].'&nbsp;</div>';
				
				if(count($DBProcessingOrders1) > 0){
					$orderdata .= '<div class="col-md-6">Driver Phone</div><div class="col-md-6">'.$val['vPhone'].'&nbsp;</div>';
					$orderdata .= '<div class="col-md-6">Total Orders delivered</div><div class="col-md-6">'.$DBProcessingOrders1[0]['total'].'&nbsp;</div></div><div class="col-md-12"><hr style="float:left;"></div>';
				}else{
					$orderdata .= '<div class="col-md-6">Driver Phone</div><div class="col-md-6">'.$val['vPhone'].'&nbsp;</div></div><div class="col-md-12"><hr style="float:left;"></div>';
				}
				$orderdata .= '<div class="col-md-12"><div class="col-md-6"><strong>Order details</strong></div><div class="col-md-6"><div class="col-md-12"><strong>Restaurant details</strong></div></div></div>';
			}
			$assign = '';
			//if($val['iStatusCode'] == 4 || $val['iStatusCode'] == 5)
			if($val['iStatusCode'] == 4)
            {
				$oncl = "return confirm('Are you sure?')";
				$assign = '<form action="" method="post"><button type="submit" class=" btn btn-default btn-xs"  onclick="'.$oncl.'">Unassign</button><input type="hidden" value="1" name="removedrive" ><input type="hidden" name="driverid" value="'.$val['iDriverId'].'"><input type="hidden" value="'.$val['iOrderId'].'" name="orderid" ></form>';
            }
			$cancelorder = '';
			if(in_array($val['iStatusCode'],$processing_status_array)){
				$payment_to_driver = $generalobjAdmin->getPaymentToDriver($val['iOrderId']);
				$payment_to_restaurant = $generalobjAdmin->getPaymentToRestaurant($val['iOrderId']);
				$MIN_ORDER_CANCELLATION_CHARGES = $generalobj->getConfigurations("configurations","MIN_ORDER_CANCELLATION_CHARGES");
				$cancelorder = 	'<form role="form" name="delete_form" id="delete_form1" method="post" action="">
									<input type="hidden" name="cancel_reason" value="canceled by admin" />
									<input type="hidden" name="fCancellationCharge" value="'.$MIN_ORDER_CANCELLATION_CHARGES.'" />
									<input type="hidden" name="fDeliveryCharge" value="'.$payment_to_driver.'" />
									<input type="hidden" name="fRestaurantPayAmount" value="'.$payment_to_restaurant.'">
									<input type="hidden" name="hdn_del_id" value="'.$val['iOrderId'].'">
									<input type="hidden" name="iUserId" value="'.$val['iUserId'].'">
									<input type="hidden" name="iDriverId" value="'.$val['iDriverId'].'">
									<input type="hidden" name="iCompanyId" value="'.$val['iCompanyId'].'">
									<input type="hidden" name="action" value="cancel">
									<input type="hidden" name="cancelorder" value="cancel">
									<button type="submit" class="btn btn-info" id="cnl_booking1" title="Cancel Booking">Cancel Order</button>
								</form>';
				//$cancelorder = '<input type="hidden" data-restcharge="'.$payment_to_restaurant.'" data-fCancellationCharge = "'.$MIN_ORDER_CANCELLATION_CHARGES.'" data-iuserid="'.$val['iUserId'].'" data-fDeliveryCharge = "'.$payment_to_driver.'" data-iCompanyId="'.$val['iCompanyId'].'" data-iDriverId="'.$val['iDriverId'].'" class="getcanceldata" />';
			
			}
			if($val['vCaddress'] != ''){
				$address = $val['vCaddress'];
			}else{
				$address = '--';
			}
			
			$orderdata .= '<div class="col-md-6"><div class="col-md-6">#Number</div><div class="col-md-6">'.$val['vOrderNo'].'&nbsp;</div>';
			$orderdata .= '<div class="col-md-6">Status</div><div class="col-md-6">'.$status.'&nbsp;</div>';
			$orderdata .= '<div class="col-md-6">Pickup Time</div><div class="col-md-6">'.date("H:i",strtotime($val['cookingtime'])).'&nbsp;</div>';
			$orderdata .= '<div class="col-md-6">Delivery Time</div><div class="col-md-6">'.date("H:i",strtotime($val['deliverytime'])).'&nbsp;</div></div>';	
			
			
			//$orderdata .= '<div class="col-md-6"><div class="col-md-12"><strong>Restaurant details</strong></div>';
			$orderdata .= '<div class="col-md-6"><div class="col-md-6">Name</div><div class="col-md-6">'.$val['vCompany'].'&nbsp;</div>';
			$orderdata .= '<div class="col-md-6">Address</div><div class="col-md-6">'.$address.'&nbsp;</div>';
			$orderdata .= '<div class="col-md-6">Phone no.</div><div class="col-md-6">'.$val['resturant_phone'].'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-3">'.$assign.'&nbsp;</div><div class="col-md-3">'.$cancelorder.'&nbsp;</div></div>';	
			$orderdata .= '<div class="col-md-12"><hr style="width:100%;"></div>';
			$i++;			
		}
	}else if(count($DBProcessingOrders1) > 0) {
		$orderdata .= '<div class="col-md-12 fontfourten"><div class="col-md-6">Driver Name</div><div class="col-md-6">'.$DBProcessingOrders1[0]['FULLNAME'].'&nbsp;</div>';
		$orderdata .= '<div class="col-md-6">Driver Phone</div><div class="col-md-6">'.$DBProcessingOrders1[0]['vPhone'].'&nbsp;</div>';
		$orderdata .= '<div class="col-md-6">Total Orders delivered</div><div class="col-md-6">'.$DBProcessingOrders1[0]['total'].'&nbsp;</div></div><div class="col-md-12"><hr style="float:left;"></div>';
		
	}
}
echo $orderdata;

exit;
?>

