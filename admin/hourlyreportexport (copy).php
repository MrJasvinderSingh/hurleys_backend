<?php
include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');
require('fpdf/fpdf.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}


$generalobjAdmin->check_member_login();
$default_lang 	= $generalobj->get_default_lang();

$script =  "HourlyReport";


class PDF extends FPDF
{
    
  // Page header
function Header()
{
      // Logo
    $this->Image('img/logo_pdf_1.png',10,6,30);
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(70);
    // Title
    $this->Cell(40,10,'Hourly Report',1,0,'C');
    // Line break
    $this->Ln(20);
  }  
    
function ImprovedTable($header, $data, $AllOrderC, $Gtotal, $TPUT, $TDDT, $TTDT, $TDriver, $Ord_DRv, $Tcust)
{
    for($i=0;$i<count($header);$i++)
    if($i == 0)
    {
        $this->Cell(30,7,$header[$i],1,0,'C');
    }
    else
    {
        $this->Cell(20,7,$header[$i],1,0,'C');
    }
    $this->Ln();
    // Data
    $this->SetFont('Arial','',14);
    foreach($data as $result)
    {
        $this->Cell(30,6,$result['time'],'LR',0,'C');
        $this->Cell(20,6,$result['orders'],'LR',0,'C');
        $this->Cell(20,6,$result['customers'],'LR',0,'C');
        $this->Cell(20,6,$result['sales'],'LR',0,'C');
        $this->Cell(20,6,$result['drivers'],'LR',0,'C');
        $this->Cell(20,6,$result['orddrv'],'LR',0,'C');
        $this->Cell(20,6,$result['put'],'LR',0,'C');
        $this->Cell(20,6,$result['ddt'],'LR',0,'C');
        $this->Cell(20,6,$result['tdt'],'LR',0,'C');
        
        $this->Ln();
    }
        $this->Cell(190,0,'','T');
        $this->Ln();
        $this->Cell(30,6,"Total",'LR',0,'C');
        $this->Cell(20,6,$AllOrderC,'LR',0,'C');
        $this->Cell(20,6,$Tcust,'LR',0,'C');
        $this->Cell(20,6,$Gtotal,'LR',0,'C');
        $this->Cell(20,6,$TDriver,'LR',0,'C');
        $this->Cell(20,6,$Ord_DRv,'LR',0,'C');
        $this->Cell(20,6,$TPUT,'LR',0,'C');
        $this->Cell(20,6,$TDDT,'LR',0,'C');
        $this->Cell(20,6,$TTDT,'LR',0,'C');
        
        $this->Ln();
    // Closing line
         $this->Cell(190,0,'','T');
}
}



// Start Search Parameters
$ssql='';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : date('Y-m-d');
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : date('Y-m-d');
$exportfromat  = isset($_REQUEST['exportfromat']) ? $_REQUEST['exportfromat'] : 'xls';

$Today=Date('Y-m-d');
$tdate=date("d")-1;
$mdate=date("d");
$Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));

$curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
$curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
$prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")-1));
$prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")-1));

$currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$tdate,date("Y")));
$currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m")+1,date("d")-$mdate,date("Y")));
$prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d")-$tdate,date("Y")));
$prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$mdate,date("Y")));

$monday = date( 'Y-m-d', strtotime( 'sunday this week -1 week' ) );
$sunday = date( 'Y-m-d', strtotime( 'saturday this week' ) );

$Pmonday = date( 'Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date( 'Y-m-d', strtotime('saturday this week -1 week'));



$starttime = date('Y-m-d').' 07:00:00';
$endtime = date('Y-m-d').' 24:00:00';

 $responses = array();

 $AllOrderCount = 0;
 
 $TotalNewCustomers = 0;
 
 $grandtotalNewcustomer = 0;
 
   $GTotalNewCustQ = "SELECT count(DISTINCT(`register_user`.`iUserId`)) as totalc  FROM `register_user` JOIN `orders` ON `orders`.`iUserId` = `register_user`.`iUserId` WHERE DATE(`register_user`.`tRegistrationDate`) BETWEEN '2018-11-01' AND '$endDate' AND `orders`.`iStatusCode` = 6 HAVING COUNT(`orders`.`iOrderId`) >= 1;";
 $GTotalNewCustData = $obj->MySQLSelect($GTotalNewCustQ);
 
 if(count($GTotalNewCustData) > 0)
 {
     $grandtotalNewcustomer = $GTotalNewCustData[0]['totalc'];
 }
 
 $TotalNewCustQ = "SELECT count(DISTINCT(`register_user`.`iUserId`)) as totalc  FROM `register_user` JOIN `orders` ON `orders`.`iUserId` = `register_user`.`iUserId` WHERE DATE(`register_user`.`tRegistrationDate`) BETWEEN '$startDate' AND '$endDate' AND `orders`.`iStatusCode` = 6 HAVING COUNT(`orders`.`iOrderId`) >= 1;";
 $TotalNewCustData = $obj->MySQLSelect($TotalNewCustQ);
 
 if(count($TotalNewCustData) > 0)
 {
     $TotalNewCustomers = round(($TotalNewCustData[0]['totalc'] / $grandtotalNewcustomer) * 100 , 2);
 }
 

 
 $GranDtotal = 0;
 $Totalquery = "SELECT COUNT(`iOrderId`) as tcount, SUM(`fNetTotal`) as grandtotal FROM `orders` WHERE `iStatusCode` = 6 AND DATE(`tOrderRequestDate`) BETWEEN '$startDate' AND '$endDate';";
 $TotalData = $obj->MySQLSelect($Totalquery);
 
 

 
 if(count($TotalData) > 0)
 {
     $AllOrderCount = $TotalData[0]['tcount'];
     $GranDtotal = round($TotalData[0]['grandtotal'] , 2);
 }
 
 $TotalPUT = 0;
 $TotalDDT = 0;
 $TotalTDT = 0;

   $TDTTotalq = "SELECT (AVG (TIMESTAMPDIFF ( SECOND, `orders`.`tOrderRequestDate`, `trips`.`tStartDate`))) as put, (AVG (TIMESTAMPDIFF ( SECOND, `trips`.`tStartDate`, `trips`.`tEndDate`))) as ddt ,  (AVG (TIMESTAMPDIFF ( SECOND, `orders`.`tOrderRequestDate`, `trips`.`tEndDate`))) as tdt FROM `orders` JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` WHERE `orders`.`iStatusCode` = 6 AND DATE(`orders`.`tOrderRequestDate`) BETWEEN '$startDate' AND '$endDate' ;";
   $TDTTotalData = $obj->MySQLSelect($TDTTotalq);
   
   
   if (!empty($TDTTotalData[0]['tdt'])) {
			$tdt= $TDTTotalData[0]['tdt'];
			$TotalTDT =  round(($tdt/60));
}

 if (!empty($TDTTotalData[0]['put'])) {
                                    $put = $TDTTotalData[0]['put'];
                                    $TotalPUT =  round(($put/60));
                            }
    if (!empty($TDTTotalData[0]['ddt'])) {
			$ddt = $TDTTotalData[0]['ddt'];
			$TotalDDT =  round(($ddt/60));
}
/*
 * ********************************GrandTotalDrivers*********************************************
 */
$GrandTotalDrivers  = 0;
//For Unique Drivers $GranddriverSql = "SELECT SUM(T1.`TDrivers`) AS totalDrivers FROM  (SELECT count(DISTINCT(`iDriverId`)) AS TDrivers, DATE(`logtime`) AS DATIME FROM `driverslocationlogs` WHERE DATE(`logtime`) BETWEEN '$startDate' AND '$endDate' AND `eStatus` = 'Available' GROUP BY  DATE(`logtime`)) AS T1;"; 

// For Unique Time
$GranddriverSql = "SELECT COUNT(*)  AS totalminutes FROM `driverslocationlogs` WHERE DATE(`logtime`) BETWEEN '$startDate' AND '$endDate' AND time(`logtime`) BETWEEN '07:00:00' AND '24:00:00' AND `eStatus` = 'Available';";
                             $GrandDriverDatas = $obj->MySQLSelect($GranddriverSql);
                            
                             
                             $totalDrivers = 0;
                             
                             if(count($GrandDriverDatas) > 0)
                             {
                                 $GrandTotalDrivers = round(((2 * $GrandDriverDatas[0]['totalminutes'])/60) , 2);
                             }
  /*
 * ********************************GrandTotalDrivers Close*********************************************
 */                           
                             
 while (strtotime($starttime) < strtotime($endtime)) {
               // echo date('Y-m-d H:i:s') . $starttime ."\r\n\r\n";
               // echo date('Y-m-d h:I:S') . $endtime ."\r\n\r\n";
                 $querystarttime  = date ("H:i",strtotime($starttime)).':00';
                $queryendtime  = date ("H:i", strtotime("+1 hour", strtotime($starttime))).':00';
                 

                
                $startDateValue = date ("Y-m-d",strtotime($starttime));
                 $Orderssql = "SELECT `iOrderId`, `vOrderNo`,`tOrderRequestDate` `fSubTotal`,`fOffersDiscount`,`fDeliveryCharge`,`fDeliveryCharge`,`fTax`, `fpretip`,`posttip`,`fDiscount`, `fNetTotal`,`fTotalGenerateFare`,`dDate`,`dDeliveryDate` FROM `orders` WHERE `iStatusCode` = 6 AND DATE(`tOrderRequestDate`) BETWEEN '$startDate' AND '$endDate'  AND TIME(`tOrderRequestDate`) BETWEEN '$querystarttime' AND '$queryendtime';";
                
                $ordersDatas = $obj->MySQLSelect($Orderssql);
                
                
                
                // Total Driver Count
                
                //$driverSql = "SELECT count(DISTINCT(`iDriverId`)) AS totalDrivers FROM `driver_log_report` WHERE `dLoginDateTime` BETWEEN '$startDate $querystarttime' AND '$endDate $queryendtime' OR `dLogoutDateTime` BETWEEN '$startDate $querystarttime' AND '$endDate $queryendtime';"; 
  
                        //$driverSql = "SELECT count(DISTINCT(`iDriverId`)) AS totalDrivers FROM `driver_log_report` WHERE `dLoginDateTime` < '$startDate $querystarttime' AND `dLogoutDateTime` > '$endDate $queryendtime' OR `dLogoutDateTime` = '0000-00-00 00:00:00' ;";
                        
                        // $driverSql = "SELECT count(DISTINCT(`iDriverId`)) AS totalDrivers FROM `driverslocationlogs` WHERE  `logtime` < '$startDate $querystarttime' AND `logtime` > '$endDate $queryendtime' ;";
                         
                         //For Unique Drivers $driverSql = "SELECT SUM(T1.`TDrivers`) AS totalDrivers FROM  (SELECT count(DISTINCT(`iDriverId`)) AS TDrivers, DATE(`logtime`) AS DATIME  FROM `driverslocationlogs` WHERE DATE(`logtime`) BETWEEN '$startDate' AND '$endDate' AND TIME(`logtime`)  BETWEEN '$querystarttime' AND '$queryendtime' AND `eStatus` = 'Available' GROUP BY  DATE(`logtime`)) AS T1;"; 
                
                // FOr Driver time
                
                $driverSql = "SELECT COUNT(*)  AS totalminutes FROM `driverslocationlogs` WHERE DATE(`logtime`) BETWEEN '$startDate' AND '$endDate' AND time(`logtime`) BETWEEN '$querystarttime' AND '$queryendtime' AND `eStatus` = 'Available';";
                             $DriverDatas = $obj->MySQLSelect($driverSql);
                            
                             
                             $totalDrivers = 0;
                             
                             if(count($DriverDatas) > 0)
                             {
                                 $totalDrivers =  round(((2 * $DriverDatas[0]['totalminutes'])/60) , 2);
                                 
                             }
                //Total Driver Count Close
                
                             
                             
                 $NewCustomers = 0;
 
 
 $NewCustQ = "SELECT count(DISTINCT(`register_user`.`iUserId`)) as totalc FROM `register_user` JOIN `orders` ON `orders`.`iUserId` = `register_user`.`iUserId` WHERE DATE(`register_user`.`tRegistrationDate`) BETWEEN '$startDate' AND '$endDate' AND time(`register_user`.`tRegistrationDate`) BETWEEN '$querystarttime' AND '$queryendtime' AND `orders`.`iStatusCode` = 6  HAVING COUNT(`orders`.`iOrderId`) >= 1;";
 $NewCustData = $obj->MySQLSelect($NewCustQ);
 
 if(count($NewCustData) > 0)
 {
     $NewCustomers = round(($NewCustData[0]['totalc'] / $grandtotalNewcustomer) * 100 , 2);
 }
            
                    if(count($ordersDatas) > 0)
                    {
                        
                        $totalAmount =0;
                        $alliorderid = '';
                        
                        foreach($ordersDatas as $ordersData):
                            $alliorderid .= $ordersData['iOrderId'].',';
                            $totalAmount = $totalAmount + $ordersData['fNetTotal'];
                        endforeach;
                       
                        
                                     
                                     
                            $alliorderid = substr($alliorderid, 0, -1);
                       // echo $alliorderid; exit;
                            
                            //PUT Caculation
                            $totalDT = 0;
                            $putvalue = 0;
                            $ddtvalue = 0;
                            
                              $totalDTq = "SELECT (AVG (TIMESTAMPDIFF ( SECOND, `orders`.`tOrderRequestDate`, `trips`.`tStartDate`))) as put, (AVG (TIMESTAMPDIFF ( SECOND, `trips`.`tStartDate`, `trips`.`tEndDate`))) as ddt ,  (AVG (TIMESTAMPDIFF ( SECOND, `orders`.`tOrderRequestDate`, `trips`.`tEndDate`))) as tdt FROM `orders` JOIN `trips` ON `trips`.`iOrderId` = `orders`.`iOrderId` WHERE `orders`.`iOrderId` IN ($alliorderid);";
    $totalDTData = $obj->MySQLSelect($totalDTq);
                            
                            
                            if (!empty($totalDTData[0]['put'])) {
                                    $put = $totalDTData[0]['put'];
                                    $putvalue =  round(($put/60));
                            }
                            if (!empty($totalDTData[0]['ddt'])) {
                                    $ddt = $totalDTData[0]['ddt'];
                                    $ddtvalue =  round(($ddt/60));
                            }
                            if (!empty($totalDTData[0]['tdt'])) {
                                    $tdt = $totalDTData[0]['tdt'];
                                    $totalDT =  round(($tdt/60));
                            }
                            
                            
               
                        $tottalOrders = count($ordersDatas);
                        $responses[] = array(
                         'time'=> date('g:i A', strtotime($starttime)) ,
                          'orders'=>$tottalOrders,
                          'customers'=>$NewCustomers,  
                          'sales'=>$totalAmount,
                          'drivers'=> $totalDrivers,
                          'orddrv'=>round(($tottalOrders/$totalDrivers), 2 ),
                          'put'=>$putvalue,  
                          'ddt'=>$ddtvalue,
                          'tdt'=> $totalDT 
                        );
//.' - '.date('d g:i:s A', strtotime($queryendtime))
                    }
                    else {
                         $responses[] = array(
                          'time'=> date('g:i A', strtotime($starttime)),
                          'orders'=>0,
                             'customers'=>0,  
                          'sales'=>0,
                          'drivers'=> $totalDrivers,
                          'orddrv'=>0,
                          'put'=>0,  
                          'ddt'=>0,
                          'tdt'=> 0 
                          );
                    }
                 
                $starttime = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($starttime)));
                unset($ordersDatas);  unset($ordersData); 
                
 }
 $Ttl_Order_DRV = round(($AllOrderCount/$GrandTotalDrivers), 2);
       
 

 
 if($exportfromat == 'xls')
 {
 //echo "<pre>";print_r($db_trip);die;
  $header .= "Time"."\t";
  $header .= "Order #"."\t";
  $header .= "%New_Customers"."\t";
  $header .= "Sales"."\t";
  $header .= "Drivers"."\t";
  $header .= "Ord/Drv"."\t";
  $header .= "PUT"."\t";
  $header .= "DDT"."\t";
  $header .= "TDT"."\t";
 foreach($responses as $result) 
  {

        $data .= $result['time']."\t";
        $data .= $result['orders']."\t";
        $data .= $result['customers']."\t";
        $data .= $result['sales']."\t";
        $data .= $result['drivers']."\t";
        $data .= $result['orddrv']."\t";
        $data .= $result['put']."\t";
        $data .= $result['ddt']."\t";
        $data .= $result['tdt'];
        $data .= "\n";
  }
     $data .= "Total"."\t";
     $data .= $AllOrderCount."\t";
      $data .= $TotalNewCustomers."\t";
     $data .= $GranDtotal."\t";
     $data .= $GrandTotalDrivers."\t";
     $data .= $Ttl_Order_DRV."\t";
     $data .= $TotalPUT."\t";
     $data .= $TotalDDT."\t";
     $data .= $TotalTDT;
     $data .= "\n";

$data = str_replace( "\r" , "" , $data );
#echo "<br>".$data; exit;
$filenamee = $startDate . '-'.$endDate;
ob_clean();
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=hourly_report$filenamee.xls");
header("Pragma: no-cache");
header("Expires: 0");
print "$header\n$data";
exit;
 }
 
 else {

    // error_reporting(E_ALL);
// Instanciation of inherited class
$pdf = new PDF();
// Column headings
$header = array('Time', 'Order #', '%NCust', 'Sales', 'Drivers','Ord/Drv','PUT','DDT','TDT');

$pdf->AddPage();

$pdf->ImprovedTable($header,$responses, $AllOrderCount , $GranDtotal, $TotalPUT, $TotalDDT,$TotalTDT, $GrandTotalDrivers, $Ttl_Order_DRV, $TotalNewCustomers);
$pdf->Output('D', 'hourlyreport'); 
exit;       
}
 
?>