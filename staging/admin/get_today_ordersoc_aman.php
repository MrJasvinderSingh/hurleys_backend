<?php
error_reporting(-1);
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



function GetTimeDiffInMinutesBwTwoTime($starttime, $Enddate) {
   // $starttime = date('Y-m-d H:i:s');
    $start_date = new DateTime($starttime);
    $since_start = $start_date->diff(new DateTime($Enddate));
    $minutes = $since_start->days * 24 * 60;
    $minutes += $since_start->h * 60;
    $minutes += $since_start->i;
    return $minutes;
}


function getLastOrderStatustime($iOrderId, $iStatusCode)
{
    global $obj;
    $ORderlogSql = "SELECT `dDate` FROM `order_status_logs` WHERE `iOrderId` = '$iOrderId' AND `iStatusCode` = '$iStatusCode';";
    $QueryPrderSlogs = $obj->MySQLSelect($ORderlogSql);
    
    $resultDT = '';
    
    if(count($QueryPrderSlogs) > 0)
    {
        $resultDT = $QueryPrderSlogs[0]['dDate'];
    }
    
    return $resultDT;
    
}


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
$sql = "SELECT o.fSubTotal,o.iServiceid,sc.vServiceName_".$default_lang." as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fNetTotal,o.cookingtime,o.mattime,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem,CONCAT('<b>Phone: </b> +',u.vPhoneCode,' ',u.vPhone)  as user_phone,ua.vLatitude,ua.vLongitude, CONCAT('<b>Phone: </b> +',d.vCode,' ',d.vPhone) as driver_phone,CONCAT('<b>Phone: </b> +',c.vCode,' ',c.vPhone) as resturant_phone FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN  user_address ua ON ua.iUserAddressId = o.iUserAddressId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid $where order by o.tOrderRequestDate ASC  limit 100";
// WHERE date > (NOW() - INTERVAL 24 HOUR)

$Age = 0;
        
$ETA =   0;      
$EDT = 0;
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

        $assign = '';
        $oncl = "return confirm('Are you sure?')";
//        if(($delive == 0)){
//            if($val['iStatusCode'] == 2){
//                $assign = '<a href="javascript:void(0)" class="custom-order btn btn-default btn-xs" data-toggle="modal" data-id="'.$val["iOrderId"].'">AS</a>';
//
//            }
//            elseif($val['iStatusCode'] == 4 || $val['iStatusCode'] == 5)
//            {
//                $assign = '<form action="" method="post"><button type="submit" class=" btn btn-default btn-xs"  onclick="'.$oncl.'">UA</button><input type="hidden" value="1" name="removedrive" ><input type="hidden" name="driverid" value="'.$val['iDriverId'].'"><input type="hidden" value="'.$val['iOrderId'].'" name="orderid" ></form>';
//            }
//        }
        $UserSelectedAddressArr = GetUserAddressDetail($val['iUserId'], "Passenger", $val['iUserAddressId']);
        $date = date('H:i', strtotime($val['tOrderRequestDate']));
        $DriverValOrder = $val['driverName'] ? '<br/>Driver: '.$val['driverName'] : '';
        
        $Etaflag = 0;
        if($val['iStatusCode'] == '6'){
             $Age = GetTimeDiffInMinutesBwTwoTime($val['tOrderRequestDate'], $val['dDeliveryDate']);
            $EDTTime = 0;
            $ETA = $Age;
         }
         else
         {
              $AgeTime = GetTimeDiffInMinutes($val['tOrderRequestDate']);
              $EDTTime = GetTimeDiffInMinutesBwTwoTime($val['tOrderRequestDate'], $val['mattime']);
              $Age  = $AgeTime;
                $EDT = $EDTTime;
                $ETA = $EDT;
                }
//         elseif($val['iStatusCode'] == '4'){
//             $AgeTime = GetTimeDiffInMinutes($val['tOrderRequestDate']);
//             $EDTPickup = GetTimeDiffInMinutesBwTwoTime($val['tOrderRequestDate'], $val['cookingtime']);
//             $EDTTime = GetTimeDiffInMinutesBwTwoTime($val['tOrderRequestDate'], $val['mattime']);
//             $timeDiff = $EDTTime - $EDTPickup;
//              $Age  = $AgeTime;
//                $EDT = $EDTTime;
//                $ETA = $EDT - $Age;
//                if($Age > $EDTPickup)
//                {
//                    $ETA = $timeDiff;
//                    while($EDTPickup < $Age)
//                    {
//                      $EDTPickup = $EDTPickup + 5;  
//                        $ETA = $ETA + 5;
//                        $Etaflag = 1;
//                    }
//                }
//                
//                if($Etaflag)
//                {
//                    $ETA = $ETA  - $Age;
//                }
//                
//         }
//         elseif($val['iStatusCode'] == '5'){
//             $AgeTime = GetTimeDiffInMinutes($val['tOrderRequestDate']);
//             $StatusDBDate = getLastOrderStatustime($val['iOrderId'], 5);
//             $EDTPickup = GetTimeDiffInMinutesBwTwoTime($val['tOrderRequestDate'], $val['cookingtime']);
//             $EDTPickupwithDiff = GetTimeDiffInMinutesBwTwoTime($val['tOrderRequestDate'], $StatusDBDate);
//             $EDTTime = GetTimeDiffInMinutesBwTwoTime($val['tOrderRequestDate'], $val['mattime']);
//             
//             $Addmoretime = 0;
//             
//             if($EDTPickupwithDiff > $EDTPickup)
//             {
//                 $Addmoretime = $EDTPickupwithDiff - $EDTPickup;
//             }
//             
//              $Age  = $AgeTime;
//                $EDT = $EDTTime + $Addmoretime ;
//                $ETA = $EDT - $Age;
//                if($Age > $EDT)
//                {
//                    while($ETA < $Age)
//                    {
//                      $ETA = $ETA + 5;
//                        $Etaflag = 1;
//                    }
//                }
//                if($Etaflag)
//                {
//                    $ETA = $ETA  - $Age;
//                }
//         }
//         else
//         {
//              $AgeTime = GetTimeDiffInMinutes($val['tOrderRequestDate']);
//              $EDTTime = GetTimeDiffInMinutesBwTwoTime($val['tOrderRequestDate'], $val['mattime']);
//              $Age  = $AgeTime;
//                $EDT = $EDTTime;
//                $ETA = $EDT - $Age;
//                if($Age > $EDT)
//                {
//                    while($ETA < $Age)
//                    {
//                        $ETA = $ETA + 5;
//                        $Etaflag = 1;
//                    }
//                }
//              
//         }
         
         if($Age > 45 && $Age < 60)
               {
                    $statuscolor = 'yellow';
               }
         if($Age > 60)
               {
                    $statuscolor = 'red';
               }      

               
                       $cls = '';
        if($statuscolor != ''){
            $cls = "background-color: $statuscolor;";
        }
        //$Age = date('H:i', strtotime('+'.$AgeTime.' minutes', strtotime(date('Y-m-d H:i:s'))));
        //$Age = round($AgeTime);
      //  $EDT = round($EDTTime);
      //  $hours = floor($minutes / 60).':'.($minutes -   floor($minutes / 60) * 60);
      //  
       // $ETA = date('H:i', strtotime('+'.$ETATime.' minutes', strtotime(date('Y-m-d H:i:s'))));
        
        $getSectorAdressarray = array($val['vLatitude'], $val['vLongitude']);
    $sector = getDeliverySector($getSectorAdressarray);
//         if($val['iStatusCode'] == '6'){
//             $Age = $ETA;
//         }
         $Age = floor($Age / 60).':'.sprintf("%02d",($Age -   floor($Age / 60) * 60));
         $ETA = floor($ETA / 60).':'.sprintf("%02d",($ETA -   floor($ETA / 60) * 60));
         
         
        $orderdata .= '<tr style="'.$cls.'  padding:2px; "><td attr="'.$val['iOrderId'].'" style="  text-align: left; vertical-align: middle;cursor:pointer; font-size: 16px;" class="tdoperationcls">' . substr($val['vOrderNo'], -3) . '-'.substr($val['vCompany'], 0,10 ).'<br/>'.$sector['sector'].'-'.substr($UserSelectedAddressArr['UserAddress'], 0,10).'</td><td style="text-align: left; vertical-align: middle; font-size: 16px;">'.$status.'<br/>&nbsp;</td><td style="font-size: 16px;">AGE-'.$Age.' <br/> EDT-'.$ETA.'</td> </tr>';
        //<td style=" vertical-align: middle; text-align:center;">'.$assign.'</td>
        //&nbsp; = '.$date.'
        //<td style=" vertical-align: middle; text-align:center;">'.$assign.'</td> 
        //<tr><td style=" vertical-align: middle; text-align:center;" colspan="2">'.$assign.'</td></tr>
        
        
    }
}
//$Startdate = date('Y-m-d', strtotime('-10 days', strtotime(date('Y-m-d')))).' 00:00:01';
//$Enddate = date('Y-m-d H:i:s');
$TTLOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode not in (7,8,9,11,12)  AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";

$TTLOrderO = $obj->MySQLSelect($TTLOrderQ);

$PLOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 1 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$PLOrderO = $obj->MySQLSelect($PLOrderQ);


$ACOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 2 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$ACOrderO = $obj->MySQLSelect($ACOrderQ);


$ASOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 4 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$ASOrderO = $obj->MySQLSelect($ASOrderQ);


//$PUOrderQ = "SELECT COUNT(`orders`.`iOrderId`) AS 'tcount' FROM `orders` JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` where `orders`.`iStatusCode` = 5 AND `trips`.`tStartDate` <= 0 AND `orders`.`tOrderRequestDate` BETWEEN '$Startdate' AND '$Enddate';";
$PUOrderQ = "SELECT COUNT(`iOrderId`) AS 'tcount' FROM `orders` where iStatusCode = 5 AND tOrderRequestDate BETWEEN '$Startdate' AND '$Enddate';";
$PUOrderO = $obj->MySQLSelect($PUOrderQ);


$EROrderQ = "SELECT count(*) FROM `trips` WHERE `iActive` = 'On Going Trip' AND `tTripRequestDate`  BETWEEN '$Startdate' AND '$Enddate';";
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

