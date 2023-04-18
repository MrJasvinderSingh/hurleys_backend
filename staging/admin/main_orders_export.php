<?php
error_reporting(-1);
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$ssql='';
$section = isset($_REQUEST['section']) ? $_REQUEST['section']: '';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id']: '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchServiceType= isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';
$type = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : '';
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
require('fpdf/fpdf.php');

$date = new DateTime();
$timestamp_filename = $date->getTimestamp();
$default_lang   = $generalobj->get_default_lang();

function change_key( $array, $old_key, $new_key ) {

    if( ! array_key_exists( $old_key, $array ) )
        return $array;

    $keys = array_keys( $array );
    $keys[ array_search( $old_key, $keys ) ] = $new_key;

    return array_combine( $keys, $array );
}

function cleanData(&$str) {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if (strstr($str, '"'))
        $str = '"' . str_replace('"', '""', $str) . '"';
}

function GetTimeDiffInMinutesBwTwoTime($starttime, $Enddate) {
    // $starttime = date('Y-m-d H:i:s');
    if ($starttime != '' && $Enddate != '') {
        $start_date = new DateTime($starttime);
        $since_start = $start_date->diff(new DateTime($Enddate));
        $minutes = $since_start->days * 24 * 60;
        $minutes += $since_start->h * 60;
        $minutes += $since_start->i;
        return $minutes;
    } else {
        return '--';
    }
}

function getAllOrderStatusFromIorderid($iOrderId)
{
    global $obj;
    $SqlOrderStatus = "SELECT `dDate`,`iStatusCode` FROM `order_status_logs` WHERE `iOrderId` = '$iOrderId'  AND `iStatusCode` in (1,2,5,6) ORDER BY `iStatusCode` ASC;"; 
        $AllOrderstatus = $obj->MySQLSelect($SqlOrderStatus);
        $placed = 0;
        $acceptedtime = 0;
        $pickeduptime = 0;
        $deliveredtime = 0;
        
        if(count($AllOrderstatus) > 0)
        {
            
            foreach($AllOrderstatus as $OrderStat):
                if($OrderStat['iStatusCode'] == 2)
                {
                    $acceptedtime = $OrderStat['dDate'];
                }
                elseif($OrderStat['iStatusCode'] == 5)
                {
                    $pickeduptime = $OrderStat['dDate'];
                }
                elseif($OrderStat['iStatusCode'] == 6)
                {
                    $deliveredtime = $OrderStat['dDate'];
                }
                else
                {
                    $placed = $OrderStat['dDate'];
                }
                
            endforeach;
        }
        
        $response = array(
            '1'=>$placed,
            '2'=>$acceptedtime,
            '5'=>$pickeduptime,
            '6'=>$deliveredtime
        );
        
        return $response;
        
}

if ($section == 'orders') {
    
    if($startDate!=''){
		$ssql.=" AND Date(o.tOrderRequestDate) >='".$startDate."'";
	}
	if($endDate!=''){
		$ssql.=" AND Date(o.tOrderRequestDate) <='".$endDate."'";
	}
	if($serachTripNo!=''){
		$ssql.=" AND o.vOrderNo ='".$serachTripNo."'";
	}
	if($searchCompany!=''){
		$ssql.=" AND c.iCompanyId ='".$searchCompany."'";
	}

	if($searchRider!=''){
		$ssql.=" AND o.iUserId ='".$searchRider."'";
	}
		
	if ($searchDriver != '') {
		$ssql .= " AND o.iDriverId ='" . $searchDriver . "'";
	}
	
	$ord = ' ORDER BY o.iOrderId DESC';
	if($sortby == 1) {

	  if($order == 0)
	  $ord = " ORDER BY o.tOrderRequestDate ASC";
	  else
	  $ord = " ORDER BY o.tOrderRequestDate DESC";
	}

	if($sortby == 2){
	  if($order == 0)
	  $ord = " ORDER BY riderName ASC";
	  else
	  $ord = " ORDER BY riderName DESC";
	}

	if($sortby == 3){
	  if($order == 0)
	  $ord = " ORDER BY c.vCompany ASC";
	  else
	  $ord = " ORDER BY c.vCompany DESC";
	}

	if($sortby == 4){
	  if($order == 0)
	  $ord = " ORDER BY driverName ASC";
	  else
	  $ord = " ORDER BY driverName DESC";
	}
	
	$order_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
	$processing_status_array = array('1','2','4','5');
	$all_status_array = array('1','2','4','5','6','7','8','9','11','12');

	if($_REQUEST['iStatusCode'] != ''){
		$all_status_array = array($_REQUEST['iStatusCode']);
	}
	if($order_type=='processing')
	{	
		$iStatusCode = '('.implode(',',$processing_status_array).')';
	}
	else
	{	
		$iStatusCode = '('.implode(',',$all_status_array).')';
	}
	$sql = "SELECT o.fSubTotal,o.iServiceid,sc.vServiceName_".$default_lang." as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.cookingtime,o.deliverytime,o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fpretip,o.posttip,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem,CONCAT('+',u.vPhoneCode,' ',u.vPhone)  as user_phone,CONCAT('+',d.vCode,' ',d.vPhone) as driver_phone,CONCAT('+',c.vCode,' ',c.vPhone) as resturant_phone FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE o.iStatusCode IN $iStatusCode $ssql $ord";

	$DBProcessingOrders = $obj->MySQLSelect($sql);
    // filename for download
    if ($type == 'XLS') {
		$result = $obj->MySQLSelect($sql);
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        
        //echo implode("\t", array_keys($result[0])) . "\r\n";
        echo $langage_lbl_admin["LBL_ORDER_NO_ADMIN"]."#";
		echo "\t ".$langage_lbl_admin["LBL_TRIP_DATE_ADMIN"];
		echo "\t ".$langage_lbl_admin["LBL_RIDER_NAME_TXT_ADMIN"]." Name";
		echo "\t ".$langage_lbl_admin["LBL_RIDER_NAME_TXT_ADMIN"]." Phone";
		echo "\t "."Store Name";
		echo "\t "."Store Phone";
		//echo "\t "."Delivery Driver";
		//echo "\t "."Driver Phone";
		///echo "\t "."PreTip";
		echo "\t "."Sub Total";
		//echo "\t "."PostTip";
		echo "\t "."Order Total";
		echo "\t "."Order Status";
		//echo "\t "."Pick Up Time";
		//echo "\t "."Delivery Time";
		echo "\t "."Payment Mode";echo "\r\n";
		if(count($result) > 0){
			foreach($result as $value){
				$OrderSResponse =  getAllOrderStatusFromIorderid($value['iOrderId']);
				$pickupminutes = 0;
				$deliveryminutes = 0;
				 
				 
				if(!empty($OrderSResponse[5])) {
				 $pickupminutes = GetTimeDiffInMinutesBwTwoTime($OrderSResponse[2], $OrderSResponse[5]);
				}


				if(!empty($OrderSResponse[6])) {
				 $deliveryminutes = GetTimeDiffInMinutesBwTwoTime($OrderSResponse[1], $OrderSResponse[6]);
				}
				echo $value['vOrderNo']."\t";
				echo $generalobjAdmin->DateTime($value['tOrderRequestDate'])."\t";
				
				echo $generalobjAdmin->clearName($value['riderName']);echo "\t";
				if(!empty($value['user_phone'])) { 
					echo $value['user_phone'];
				}
				echo "\t";
				
				echo $generalobjAdmin->clearCmpName($value['vCompany']);echo "\t";
				if(!empty($value['resturant_phone'])){ 
					echo $value['resturant_phone'];
				}
				echo "\t";
				
				if(!empty($value['driverName'])){ 
					//echo $generalobjAdmin->clearName($value['driverName']);
				}
				//echo "\t";
				if(!empty($value['driver_phone'])){ 
					//echo $value['driver_phone'];
				}
				//echo "\t";
				//echo $generalobj->trip_currency($value['fpretip'])."\t";
				echo $generalobj->trip_currency($value['fNetTotal'])."\t";
				//echo $generalobj->trip_currency($value['posttip'])."\t";
				echo $generalobj->trip_currency( ($value['fNetTotal'] + $value['posttip']))."\t";
				echo $value['vStatus']."\t";
				//echo $pickupminutes."\t";
				//echo $deliveryminutes."\t";
				echo $value['ePaymentOption']."\t";
				echo "\r\n";
			}
		}
		
    } else {
        $heading = array($langage_lbl_admin["LBL_ORDER_NO_ADMIN"]."#", $langage_lbl_admin["LBL_TRIP_DATE_ADMIN"], //$langage_lbl_admin["LBL_RIDER_NAME_TXT_ADMIN"]." Name", $langage_lbl_admin["LBL_RIDER_NAME_TXT_ADMIN"]." Phone", 'Store Name', 'Store Phone', 'Delivery Driver', 'Driver Phone', 'PreTip', 'Sub Total', 'PostTip', 'Order Total', 'Order Status', 'Pick Up Time', 'Delivery Time', 'Payment Mode');
		$langage_lbl_admin["LBL_RIDER_NAME_TXT_ADMIN"]." Name", $langage_lbl_admin["LBL_RIDER_NAME_TXT_ADMIN"]." Phone", 'Store Name', 'Store Phone', 'Sub Total', 'Order Total', 'Order Status', 'Payment Mode');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
		class ConductPDF extends FPDF {
			function vcell($c_width,$c_height,$x_axis,$text){
				$w_w=$c_height/3;
				$w_w_1=$w_w+2;
				$w_w1=$w_w+$w_w+$w_w+3;
				$len=strlen($text);// check the length of the cell and splits the text into 7 character each and saves in a array 

				$lengthToSplit = 11;
				if($len>$lengthToSplit){
					$w_text=str_split($text,$lengthToSplit);
					$this->SetX($x_axis);
					$this->Cell($c_width,$w_w_1,$w_text[0],'','','');
					if(isset($w_text[1])) {
						$this->SetX($x_axis);
						$this->Cell($c_width,$w_w1,$w_text[1],'','','');
					}
					$this->SetX($x_axis);
					$this->Cell($c_width,$c_height,'','LTRB',0,'L',0);
				}
				else{
					$this->SetX($x_axis);
					$this->Cell($c_width,$c_height,$text,'LTRB',0,'L',0);
				}
			}
		}
        $pdf = new ConductPDF('L','mm',array(220,370));
        $pdf->AddPage();
        $pdf->SetFillColor(32, 92, 80);

        $pdf->SetFont('Arial', 'b', 15);
        //$pdf->vcell(100, 16, "Admin Users");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
		$i=1;
        foreach ($heading as $column_heading) {
			$x_axis=$pdf->getx();
			if($i < 6){
				$pdf->vcell(20, 10, $x_axis, $column_heading);
			}else{
				$pdf->vcell(23, 10, $x_axis, $column_heading);
			}
			$i++;
        }
        $pdf->SetFont('Arial', '', 9);
		if(count($result) > 0){
			foreach ($result as $value) {
				$OrderSResponse =  getAllOrderStatusFromIorderid($value['iOrderId']);
				$pickupminutes = 0;
				$deliveryminutes = 0;
				 
				 
				if(!empty($OrderSResponse[5])) {
				 $pickupminutes = GetTimeDiffInMinutesBwTwoTime($OrderSResponse[2], $OrderSResponse[5]);
				}


				if(!empty($OrderSResponse[6])) {
				 $deliveryminutes = GetTimeDiffInMinutesBwTwoTime($OrderSResponse[1], $OrderSResponse[6]);
				}

				$pdf->Ln();
				$x_axis=$pdf->getx();
				$pdf->vcell(20, 10, $x_axis, $value['vOrderNo']);
				$x_axis=$pdf->getx();
				$pdf->vcell(20, 10, $x_axis, $generalobjAdmin->DateTime($value['tOrderRequestDate']));
				$x_axis=$pdf->getx();
				$pdf->vcell(20, 10, $x_axis, $generalobjAdmin->clearName($value['riderName']));
				$x_axis=$pdf->getx();
				if(!empty($value['user_phone'])) { 
					$pdf->vcell(20, 10, $x_axis, $value['user_phone']);
				}else{
					$pdf->vcell(20, 10, $x_axis, '');
				}
				$x_axis=$pdf->getx();
				$pdf->vcell(20, 10, $x_axis, $generalobjAdmin->clearCmpName($value['vCompany']));
				$x_axis=$pdf->getx();
				if(!empty($value['resturant_phone'])){ 
					$pdf->vcell(30, 10, $x_axis, $value['resturant_phone']);
				}else{
					$pdf->vcell(30, 10, $x_axis, '');
				}
				$x_axis=$pdf->getx();
				if(!empty($value['driverName'])){ 
					//$pdf->vcell(23, 10, $x_axis, $generalobjAdmin->clearName($value['driverName']));
				}else{
					//$pdf->vcell(23, 10, $x_axis, '');
				}
				//$x_axis=$pdf->getx();
				if(!empty($value['driver_phone'])){ 
					//$pdf->vcell(23, 10, $x_axis, $value['driver_phone']);
				}else{
					//$pdf->vcell(23, 10, $x_axis, '');
				}
				$x_axis=$pdf->getx();
				//$pdf->vcell(23, 10, $x_axis, $generalobj->trip_currency( $value['fpretip'] ));
				//$x_axis=$pdf->getx();
				$pdf->vcell(30, 10, $x_axis, $generalobj->trip_currency( $value['fNetTotal'] ));
				//$x_axis=$pdf->getx();
				//$pdf->vcell(23, 10, $x_axis, $generalobj->trip_currency( $value['posttip'] ));
				$x_axis=$pdf->getx();
				$pdf->vcell(30, 10, $x_axis, $generalobj->trip_currency( ($value['fNetTotal'] + $value['posttip'])));
				$x_axis=$pdf->getx();
				$pdf->vcell(30, 10, $x_axis, $value['vStatus'], 1);
				$x_axis=$pdf->getx();
				//$pdf->vcell(23, 10, $x_axis, $pickupminutes);
				//$x_axis=$pdf->getx();
				//$pdf->vcell(23, 10, $x_axis, $deliveryminutes);
				//$x_axis=$pdf->getx();
				$pdf->vcell(30, 10, $x_axis, $value['ePaymentOption']);
			}
		}
		$pdf->Output('D');
    }
}
?>