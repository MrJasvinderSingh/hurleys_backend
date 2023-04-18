<?php            
include_once('../common.php');
include_once('../generalFunctions.php');
//ini_set('display_errors',1); 
//error_reporting(E_ALL);
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$abc = 'admin,company';

//echo "<pre>"; print_r($_REQUEST); exit;
//-----------------------------------------------

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
//$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';


$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);

// Start Search Parameters
$OrderstartDate = date('Y-m-d', strtotime('sunday this week -1 week'));
$OrderendDate = date('Y-m-d', strtotime('saturday this week'));


$ssql = '';
$ssql1 = '';

   


if ($startDate != '') {
    $OrderstartDate = $startDate;
}
if ($endDate != '') {
    $OrderendDate = $endDate;
}
$ssql .= " AND Date(`tOrderRequestDate`) >='" . $OrderstartDate . "'";
$ssql .= " AND Date(`tOrderRequestDate`) <='" . $OrderendDate . "'";
$result = array();

 if (($action == 'search')&&($searchDriver != '')) {
        $ssql1 = " AND iDriverId ='" . $searchDriver . "'";
        
        $Sdriv = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE eStatus != 'Deleted' $ssql1 order by vName";
$Sdriver = $obj->MySQLSelect($Sdriv);
        
        
        $OrderQuery = "SELECT COUNT(`iOrderId`) AS totalorders, SUM(`fpretip`) as totalpretip,SUM(`posttip`) AS totalpostip   FROM `orders` WHERE `iStatusCode` = 6 $ssql $ssql1;";
    $OrderDatas = $obj->MySQLSelect($OrderQuery);
    if ( (count($OrderDatas) > 0) && ($OrderDatas[0]['totalorders'] != 0)) {
        $tip = $OrderDatas[0]['totalpretip'] + $OrderDatas[0]['totalpostip'];
        $reimbursement = calculateDriverReimbursementOfDriverwithrange ($OrderstartDate, $OrderendDate, $searchDriver);
        $bonus = calculateTotalDriverRatingBonus ($OrderstartDate, $OrderendDate, $searchDriver);
        $totalEarning = $tip + $reimbursement + $bonus;
        $adtq = "SELECT ROUND(AVG (TIMESTAMPDIFF ( SECOND, `tTripRequestDate`,`tEndDate`))) as adt FROM `trips` where `iDriverId` = '".$Sdriver[0]['iDriverId']."' AND `tTripRequestDate` BETWEEN '".$OrderstartDate." 00:00:01' AND '".$OrderendDate." 23:59:59';"; 
        $Adtdata = $obj->MySQLSelect($adtq);
       
        $AdtValue = 0;
        if(count($Adtdata) != 0) { $AdtValue = round($Adtdata[0]['adt']/60); }
        $result[] = array(
            'name' => $Sdriver[0]['driverName'],
            'orders' => $OrderDatas[0]['totalorders'],
            'tip' => $tip,
            'rating' =>GetAverageRatingOfDriver($OrderstartDate, $OrderendDate, $searchDriver),
            'reimbursement'=>$reimbursement,
            'bonus'=>$bonus,
            'ttlearning'=>$totalEarning,
            'adt'=> $AdtValue
        );
    }
    
//    else {
//        $result[] = array(
//            'name' => $Sdriver[0]['driverName'],
//            'orders' => 0,
//            'tip' => 0,
//            'rating' => 0,
//        );
//    }
        
    }
    else
    {
foreach ($db_drivers as $driver):
$OrderQuery = "SELECT COUNT(`iOrderId`) AS totalorders, SUM(`fpretip`) as totalpretip,SUM(`posttip`) AS totalpostip   FROM `orders` WHERE `iStatusCode` = 6 $ssql  AND `iDriverId` = '$driver[iDriverId]';";
    $OrderDatas = $obj->MySQLSelect($OrderQuery);
    
    
    if ( (count($OrderDatas) > 0) && ($OrderDatas[0]['totalorders'] != 0)) {
        
        $reimbursement = calculateDriverReimbursementOfDriverwithrange ($OrderstartDate, $OrderendDate, $driver['iDriverId']);
        $bonus = calculateTotalDriverRatingBonus ($OrderstartDate, $OrderendDate, $driver['iDriverId']);
          $tip = $OrderDatas[0]['totalpretip'] + $OrderDatas[0]['totalpostip'];
           $totalEarning = $tip + $reimbursement + $bonus;
           
           $adtq = "SELECT ROUND(AVG (TIMESTAMPDIFF ( SECOND, `tTripRequestDate`,`tEndDate`))) as adt FROM `trips` where `iDriverId` = '".$driver['iDriverId']."' AND `tTripRequestDate` BETWEEN '".$OrderstartDate." 00:00:01' AND '".$OrderendDate." 23:59:59';"; 
        $Adtdata = $obj->MySQLSelect($adtq);
        
        $AdtValue = 0;
        if(count($Adtdata) != 0) { $AdtValue = round($Adtdata[0]['adt']/60); }
        $result[] = array(
            'name' => $driver['driverName'],
            'orders' => $OrderDatas[0]['totalorders'],
            'tip' => $OrderDatas[0]['totalpretip'] + $OrderDatas[0]['totalpostip'],
            'rating' => GetAverageRatingOfDriver($OrderstartDate, $OrderendDate, $driver['iDriverId']),
            'reimbursement'=>$reimbursement,
            'bonus'=>$bonus,
            'ttlearning'=>$totalEarning,
            'adt'=> $AdtValue
        );
    } 
    
//    else {
//        $result[] = array(
//            'name' => $driver['driverName'],
//            'orders' => 0,
//            'tip' => 0,
//            'rating' => 0,
//        );
//    }
unset($OrderDatas);

endforeach;
    }
	
  //echo "<pre>";print_r($db_trip);die;
  $header .= "Name"."\t";
  $header .= "Number of Orders"."\t";
  $header .= "Tip"."\t";
  $header .= "Reimbursement"."\t";
  $header .= "Bonus"."\t";
  $header .= "TTL Earnings"."\t";
  $header .= "DDT"."\t";
  
 for($j=0;$j<count($result);$j++)
  {

        $data .= $result[$j]['name']."\t";
        $data .= $result[$j]['orders']."\t";
        $data .= $result[$j]['tip']."\t";
        $data .= $result[$j]['reimbursement']."\t";
        $data .= $result[$j]['bonus']."\t";
        $data .= $result[$j]['ttlearning']."\t";
        $data .= $result[$j]['adt'];
        $data .= "\n";
  }

$data = str_replace( "\r" , "" , $data );
#echo "<br>".$data; exit;
ob_clean();
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=driverpayout_report.xls");
header("Pragma: no-cache");
header("Expires: 0");
print "$header\n$data";
exit;
?>
