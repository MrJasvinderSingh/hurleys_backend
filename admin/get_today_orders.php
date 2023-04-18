<?php
include_once('../common.php');
include_once('../generalFunctions.php');
if(!isset($generalobjAdmin)){
    require_once(TPATH_CLASS."class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$default_lang 	= $generalobj->get_default_lang();
$where = '';
$delive = 0;
//$Startdate = date('Y-m-d', strtotime('-10 days', strtotime(date('Y-m-d')))).' 00:00:01';
$Startdate = date('Y-m-d').' 00:00:01';
$Enddate = date('Y-m-d H:i:s');
if($_POST['type'] == 'delivered'){
    $delive++;
    //
    $where = " where o.iStatusCode = 6 AND o.tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate'";
}else if($_POST['type'] == 'cancelled'){
    $delive++;
    $where = " where o.iStatusCode in (7,8,9,11) AND o.tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate'";
}
else if($_POST['type'] == 'active'){
   
    $where = " where o.iStatusCode not in (6,7,8,9,11,12) AND o.tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate'";
}elseif($_POST['type'] == 'all'){
    $where = " where  o.tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate'";
    
}
else
{   
    $where = " where o.iStatusCode not in (6,7,8,9,11,12) AND o.tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate'";
}
$sql = "SELECT o.fSubTotal,o.iServiceid,sc.vServiceName_".$default_lang." as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem,CONCAT('<b>Phone: </b> +',u.vPhoneCode,' ',u.vPhone)  as user_phone,CONCAT('<b>Phone: </b> +',d.vCode,' ',d.vPhone) as driver_phone,CONCAT('<b>Phone: </b> +',c.vCode,' ',c.vPhone) as resturant_phone FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid $where order by o.tOrderRequestDate ASC  limit 100";
// WHERE date > (NOW() - INTERVAL 24 HOUR)
$DBProcessingOrders = $obj->MySQLSelect($sql);
$orderdata = '';
if(count($DBProcessingOrders) > 0) {
    foreach ($DBProcessingOrders as $val) {
        $driverid = $val['iDriverId'];
        if($val['iStatusCode'] == '1' ){
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
        $statuscolor = '';
        
        $date = date('H:i A', strtotime($val['tOrderRequestDate']));
        $timedifference = GetTimeDiffInMinutes($val['tOrderRequestDate']);
        
        if(($delive == 0)){
            $statuscolor = '#cccccc';
       /* if($timedifference > 60){
            $statuscolor = 'red';
        }else if($timedifference > 45){
            $statuscolor = 'yellow';
        }else if($timedifference > 10){
            $statuscolor = 'red';
        }else if($timedifference > 5){
            $statuscolor = 'yellow';
        }else if($timedifference > 2){
            $statuscolor = 'yellow';
        }
        */
        }
        if($val['iStatusCode'] == '6'){
            $status = 'DL';
            $statuscolor = '#99b3ff';
        }
        //|| $val['iStatusCode'] == '12'
        if($val['iStatusCode'] == '8' || $val['iStatusCode'] == '11' ){
            $status = 'CL';
            $statuscolor = '#a3a3c2';
        }
        $cls = '';
        if($statuscolor != ''){
            $cls = "background-color: $statuscolor;";
        }
        $assign = '';
        $oncl = "return confirm('Are you sure?')";
        if(($delive == 0)){
            if($val['iStatusCode'] == 2){
                $assign = '<a href="javascript:void(0)" class="custom-order btn btn-default btn-xs" data-toggle="modal" data-id="'.$val["iOrderId"].'">Assign</a>';

            }
            elseif($val['iStatusCode'] == 4 || $val['iStatusCode'] == 5)
            {
                $assign = '<form action="" method="post"><button type="submit" class=" btn btn-default btn-xs"  onclick="'.$oncl.'">Unassign</button><input type="hidden" value="1" name="removedrive" ><input type="hidden" name="driverid" value="'.$val['iDriverId'].'"><input type="hidden" value="'.$val['iOrderId'].'" name="orderid" ></form>';
            }
        }
        $UserSelectedAddressArr = GetUserAddressDetail($val['iUserId'], "Passenger", $val['iUserAddressId']);
        $date = date('H:i', strtotime($val['tOrderRequestDate']));
        $DriverValOrder = $val['driverName'] ? '<br/>Driver: '.$val['driverName'] : '';
        $orderdata .= '<tr style="'.$cls.'  padding:2px; "><td attr="'.$val['iOrderId'].'" style="  text-align: left; vertical-align: middle;cursor:pointer; font-size: 18px;" class="tdoperationcls"><strong>' . substr($val['vOrderNo'], -3) . '</strong>-<strong>'.substr($val['vCompany'], 0,10 ).'</strong> <br/><p  style="font-size: 14px;">'.substr($UserSelectedAddressArr['UserAddress'], 0,20).'</p> </td><td style="text-align: left; vertical-align: middle;">'.$status.'<br/>'.$date.'</td><td style=" vertical-align: middle; text-align:center;">'.$assign.'</td></tr>';
        
    }
}
//$Startdate = date('Y-m-d', strtotime('-10 days', strtotime(date('Y-m-d')))).' 00:00:01';
//$Enddate = date('Y-m-d H:i:s');
$TTLOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode not in (7,8,9,11,12)  AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";

$TTLOrderO = $obj->MySQLSelect($TTLOrderQ);

$PLOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode in (1) AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$PLOrderO = $obj->MySQLSelect($PLOrderQ);


$ACOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 2 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$ACOrderO = $obj->MySQLSelect($ACOrderQ);


$ASOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 4 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$ASOrderO = $obj->MySQLSelect($ASOrderQ);


$PUOrderQ = "SELECT COUNT(`orders`.`iOrderId`) AS 'tcount' FROM `orders` JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` where `orders`.`iStatusCode` = 5 AND `trips`.`tStartDate` <= 0 AND `orders`.`tOrderRequestDate` BETWEEN '$Startdate' AND '$Enddate';";
$PUOrderO = $obj->MySQLSelect($PUOrderQ);


$EROrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 5 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$EROrderO = $obj->MySQLSelect($EROrderQ);


$DELOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 6 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$DELOrderO = $obj->MySQLSelect($DELOrderQ);

$CANCOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode in(7,8,9,11) AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$CANCOrderO = $obj->MySQLSelect($CANCOrderQ);

$totalorderstatus = '<td>'.$TTLOrderO[0]['tcount'].'</td>
			<td>'.$PLOrderO[0]['tcount'].'</td>
			<td>'.$ACOrderO[0]['tcount'].'</td>
			<td>'.$ASOrderO[0]['tcount'].'</td>
			<td>'.$PUOrderO[0]['tcount'].'</td>
			<td>'.$EROrderO[0]['tcount'].'</td>
			<td>'.$DELOrderO[0]['tcount'].'</td>
			<td>'.$CANCOrderO[0]['tcount'].'</td>';
$array = array('res'=>$orderdata,'totalodersStatus'=>$totalorderstatus);
echo json_encode($array);

exit;
?>

