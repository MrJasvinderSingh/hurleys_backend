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
if(isset($_POST['orderId'])){
	$orderid = $_POST['orderId'];
	$sql = "SELECT o.pickuptime,o.cookingtime,o.mattime,o.deliverytime,o.fSubTotal,o.iServiceid,sc.vServiceName_".$default_lang." as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,c.vCaddress,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem,CONCAT('+',u.vPhoneCode,' ',u.vPhone)  as user_phone,u.tDestinationAddress,CONCAT('+',d.vCode,' ',d.vPhone) as driver_phone,CONCAT('+',c.vCode,' ',c.vPhone) as resturant_phone FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid where o.iOrderId = $orderid order by o.tOrderRequestDate ASC  limit 100";
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
			$statuscolor = '';
			//print_r($val);
			$date = date('H:i A', strtotime($val['tOrderRequestDate']));
			$timedifference = GetTimeDiffInMinutes($val['tOrderRequestDate']);
			$UserSelectedAddressArr = GetUserAddressDetail($val['iUserId'], "Passenger", $val['iUserAddressId']);
			$date = date('H:i A', strtotime($val['tOrderRequestDate']));
			$DriverValOrder = $val['driverName'] ? '<br/>Driver: '.$val['driverName'] : '';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Order No.</div><div class="col-md-6">'.$val['vOrderNo'].'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Order Status</div><div class="col-md-6">'.$status.'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Order Placed Date Time</div><div class="col-md-6">'.$val['tOrderRequestDate'].'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Estimated Pickup Time</div><div class="col-md-6">'.date("H:i A",strtotime($val['cookingtime'])).'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Estimated Delivery Time</div><div class="col-md-6">'.date("H:i A",strtotime($val['dDeliveryDate'])).'&nbsp;</div></div>';
			
			$orderdata .= '<div class="col-md-12"><div class="col-md-12"><strong style="padding: 10px 0;float: left;width: 100%;">Customer details</strong></div></div><div class="col-md-12"><div class="col-md-6">Customer Name</div><div class="col-md-6">'.$val['riderName'].'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Customer Address</div><div class="col-md-6">'.$val['tDestinationAddress'].'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Customer Phone no.</div><div class="col-md-6">'.$val['user_phone'].'&nbsp;</div></div>';
			
			$orderdata .= '<div class="col-md-12"><div class="col-md-12"><strong style="padding: 10px 0;float: left;width: 100%;">Restaurant details</strong></div></div><div class="col-md-12"><div class="col-md-6">Restaurant Name</div><div class="col-md-6">'.$val['vCompany'].'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Restaurant Address</div><div class="col-md-6">'.$val['vCaddress'].'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Restaurant Phone no.</div><div class="col-md-6">'.$val['resturant_phone'].'&nbsp;</div></div>';
			
			$orderdata .= '<div class="col-md-12"><div class="col-md-12"><strong style="padding: 10px 0;float: left;width: 100%;">Driver details</strong></div></div><div class="col-md-12"><div class="col-md-6">Drive Name</div><div class="col-md-6">'.$val['driverName'].'&nbsp;</div></div>';
			$orderdata .= '<div class="col-md-12"><div class="col-md-6">Drive Phone no.</div><div class="col-md-6">'.$val['driver_phone'].'&nbsp;</div></div>';
			
			//$orderdata .= '<tr style="'.$cls.'  padding:2px; "><td attr="'.$val['iOrderId'].'" style=" width: 60%; text-align: left; vertical-align: middle;cursor:pointer;" class="tdoperationcls"><strong>' . substr($val['vOrderNo'], -3) . '</strong>-<strong>'.substr($val['vCompany'], 0,10 ).'</strong> <br/><p>'.substr($UserSelectedAddressArr['UserAddress'], 0,20).'</p> </td><td style="text-align: left; vertical-align: middle; width: 25%;">'.$status.'<br/>'.$date.'</td><td style="width: 15%; vertical-align: middle;">'.$assign.'</td></tr>';
			
		}
	}
}
echo $orderdata;

exit;
?>

