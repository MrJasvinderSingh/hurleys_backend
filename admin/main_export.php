<?php

include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : "";
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : "";
$type = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : '';
$ssql = "";
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

if ($section == 'admin') {
    
    $ord = ' ORDER BY ad.vFirstName ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY ad.vFirstName ASC";
      else
      $ord = " ORDER BY ad.vFirstName DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY ad.vEmail ASC";
      else
      $ord = " ORDER BY ad.vEmail DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY ag.vGroup ASC";
      else
      $ord = " ORDER BY ag.vGroup DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY ad.eStatus ASC";
      else
      $ord = " ORDER BY ad.eStatus DESC";
    }
    //End Sorting

    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (concat(ad.vFirstName,' ',ad.vLastName) LIKE '%".$keyword."%' OR ad.vEmail LIKE '%".$keyword."%' OR ag.vGroup LIKE '%".$keyword."%' OR ad.eStatus LIKE '%".$keyword."%')";
        }
    }
	if($option == "ad.eStatus"){	
	 $eStatussql = " AND ad.eStatus = '".ucfirst($keyword)."'";
	}else{
	 $eStatussql = " AND ad.eStatus != 'Deleted'";
	}

    $sql = "SELECT CONCAT(ad.vFirstName,' ',ad.vLastName) as Name,ad.vEmail as Email,ag.vGroup as `Admin Roles`, ad.eStatus as Status FROM administrators AS ad LEFT JOIN admin_groups AS ag ON ad.iGroupId=ag.iGroupId where 1=1 $eStatussql $ssql $ord";

    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'Name'){
                    $val = $generalobjAdmin->clearName($val);
                }
                if($key == 'Email'){
                    $val = $generalobjAdmin->clearEmail($val);
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array('Name', 'Email', 'Admin Roles', 'Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Admin Users");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Id') {
                $pdf->Cell(10, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if($column == 'Name'){
                    $values = $generalobjAdmin->clearName($key);
                }
                if($column == 'Email'){
                    $values = $generalobjAdmin->clearEmail($key);
                }

                if ($column == 'Id') {
                    $pdf->Cell(10, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}



if ($section == 'company') {
    
    $ord = ' ORDER BY c.iCompanyId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY c.vCompany ASC";
      else
      $ord = " ORDER BY c.vCompany DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY c.vEmail ASC";
      else
      $ord = " ORDER BY c.vEmail DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY c.eStatus ASC";
      else
      $ord = " ORDER BY c.eStatus DESC";
    }
    //End Sorting
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array("(", "+", ")");
        $removespacekeyword =  preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if(is_numeric($keyword_new)){
          $keyword_new = $keyword_new;
        } else {
          $keyword_new = $keyword;
        } 
        if ($option != '') {
            $option_new = $option;
            if($option == 'MobileNumber'){
                $option_new = "CONCAT(c.vCode,'',c.vPhone)";
            }
            if($eStatus != ''){
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                 $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";
            }
        } else {
            if($eStatus != ''){
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%".$generalobjAdmin->clean($keyword_new)."%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
            } else {
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%".$generalobjAdmin->clean($keyword_new)."%'))";
            }
        }
    } else if($eStatus != '' && $keyword == '') {
         $ssql.= " AND c.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }
    
    $cmp_ssql = "";

    if($eStatus != '') { 
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND c.eStatus != 'Deleted'"; 
    }

     $sql = "SELECT c.vCompany AS Name, c.vEmail AS Email,(SELECT count(iFoodMenuId) FROM food_menu WHERE iCompanyId = c.iCompanyId) as `Item Categories`, CONCAT(c.vCode,' ',c.vPhone) AS Mobile,c.eStatus AS Status FROM company AS c WHERE 1 = 1 $eStatus_sql $ssql $cmp_ssql $ord";
    
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename. ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        if($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX' ) {
            $result[0] = change_key( $result[0] , 'Total Drivers', 'Total Providers' );
        }

        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'Email'){
                    $val = $generalobjAdmin->clearEmail($val);
                }
                if($key == 'Mobile'){
                    $val = $generalobjAdmin->clearPhone($val);
                }
                if($key == 'Name'){
                    $val = $generalobjAdmin->clearCmpName($val);
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
       
        $heading = array('Name', 'Email', 'Item Categories', 'Mobile', 'Status');

        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Store");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Item Categories') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == 'Mobile') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(55, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if($column == 'Email'){
                    $values = $generalobjAdmin->clearEmail($key);
                }
                if($column == 'Mobile'){
                    $values = $generalobjAdmin->clearPhone($key);
                }
                if($column == 'Name'){
                    $values = $generalobjAdmin->clearCmpName($key);
                }
                if ($column == 'Item Categories') {
                    $pdf->Cell(30, 10, $values, 1);
                } else if ($column == 'Mobile') {
                    $pdf->Cell(30, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else {
                    $pdf->Cell(55, 10, $values, 1);
                }
            }
        }
        $pdf->Output('D');
       
    }
}


if ($section == 'rider') {
    $ord = ' ORDER BY iUserId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vName ASC";
      else
      $ord = " ORDER BY vName DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vEmail ASC";
      else
      $ord = " ORDER BY vEmail DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY tRegistrationDate ASC";
      else
      $ord = " ORDER BY tRegistrationDate DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }
    $rdr_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
    }
    
    if($keyword != ''){
        $keyword_new = $keyword;
        $chracters = array("(", "+", ")");
        $removespacekeyword =  preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if(is_numeric($keyword_new)){
          $keyword_new = $keyword_new;
        } else {
          $keyword_new = $keyword;
        }
        if($option != '') {
            $option_new = $option;
            if($option == 'RiderName'){
              $option_new = "CONCAT(vName,' ',vLastName)";
            }
            if($option == 'MobileNumber'){
                $option_new = "CONCAT(vPhoneCode,'',vPhone)";
            }
            if($eStatus != ''){
                $ssql.= " AND ".stripslashes($option_new)." LIKE '%".$generalobjAdmin->clean($keyword_new)."%' AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            }else {
                $ssql.= " AND ".stripslashes($option_new)." LIKE '%".$generalobjAdmin->clean($keyword_new)."%'";
            }
        } else {
            if($eStatus != ''){
                $ssql.= " AND (concat(vName,' ',vLastName) LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR vEmail LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR (CONCAT(vPhoneCode,'',vPhone) LIKE '%".$generalobjAdmin->clean($keyword_new)."%')) AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                $ssql.= " AND (concat(vName,' ',vLastName) LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR vEmail LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR (CONCAT(vPhoneCode,'',vPhone) LIKE '%".$generalobjAdmin->clean($keyword_new)."%'))";
            }
        }
    } else if( $eStatus != '' && $keyword == '') {
         $ssql.= " AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }

    $ssql1 = "AND (vEmail != '' OR vPhone != '')";

    $sql = "SELECT CONCAT(vName,' ',vLastName) as Name,vEmail as Email,CONCAT(vPhoneCode,' ',vPhone) AS Mobile,eStatus as Status FROM register_user WHERE 1=1 $eStatus_sql $ssql $ssql1 $rdr_ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'Name'){
                    $val = $generalobjAdmin->clearName($val);
                }
                if($key == 'Email'){
                    $val = $generalobjAdmin->clearEmail($val);
                }
                if($key == 'Mobile'){
                    $val = $generalobjAdmin->clearPhone($val);
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array('Name', 'Email', 'Mobile', 'Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Riders");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
           if ($column_heading == 'Email') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else if ($column_heading == 'Mobile') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if($column == 'Name'){
                    $values = $generalobjAdmin->clearName($key);
                }
                if($column == 'Email'){
                    $values = $generalobjAdmin->clearEmail($key);
                }
                if($column == 'Mobile'){
                    $values = $generalobjAdmin->clearPhone($key);
                }
                if ($column == 'Email') {
                    $pdf->Cell(55, 10, $values, 1);
                } else if ($column == 'Mobile') {
                    $pdf->Cell(45, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}

//make 
if ($section == 'make') {
	$ord = ' ORDER BY vMake ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vMake ASC";
      else
      $ord = " ORDER BY vMake DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }

    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vMake LIKE '%".$keyword."%' OR eStatus LIKE '%".($keyword)."%')";
        }
    }
	if($option == "eStatus"){	
	 $eStatussql = " AND eStatus = '".($keyword)."'";
	}else{
	 $eStatussql = " AND eStatus != 'Deleted'";
	}

    $sql = "SELECT vMake as Make, eStatus as Status FROM make where 1=1 $eStatussql $ssql $ord";

    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Make','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Make");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(70, 10, $column_heading, 1);
            } else {
                $pdf->Cell(80, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(70, 10, $key, 1);
                } else {
                    $pdf->Cell(80, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}
//make

//Cancel Reason 
if ($section == 'cancel_reason') {
    $ord = ' ORDER BY vTitle ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vTitle ASC";
      else
      $ord = " ORDER BY vTitle DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }

    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vTitle_EN LIKE '%".$keyword."%' OR eStatus LIKE '%".($keyword)."%')";
        }
    }
    if($option == "eStatus"){   
     $eStatussql = " AND eStatus = '".($keyword)."'";
    }else{
     $eStatussql = " AND eStatus != 'Deleted'";
    }

    $sql = "SELECT iCancelReasonId as Id, vTitle_EN as Title, eStatus as Status FROM cancel_reason where 1=1 $eStatussql $ssql";
    // filename for download
    if ($type == 'XLS') {
       $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Id','Title','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Cancel Reason");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Id') {
                $pdf->Cell(50, 10, $column_heading, 1);
            }else if ($column_heading == 'Status') {
                $pdf->Cell(70, 10, $column_heading, 1);
            } else {
                $pdf->Cell(50, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) { 
                if ($column_heading == 'Id') {
                    $pdf->Cell(50, 10, $column_heading, 1);
                }else if ($column == 'Status') {
                    $pdf->Cell(70, 10, $key, 1);
                } else {
                    $pdf->Cell(50, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}
//Cancel Reason


//cuisine 
if ($section == 'cuisine') {
    $ord = ' ORDER BY c.cuisineName_'.$default_lang.' ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY c.cuisineName_".$default_lang." ASC";
      else
      $ord = " ORDER BY c.cuisineName_".$default_lang." DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY c.eStatus ASC";
      else
      $ord = " ORDER BY c.eStatus DESC";
    }

    $ssql = '';
    if($keyword != ''){
        if($option != '') {
            if($eStatus != ''){
                $ssql.= " AND ".stripslashes($option)." LIKE '%".$generalobjAdmin->clean($keyword)."%' AND c.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%'";
            }
        } else {
            if($eStatus != ''){
                $ssql.= " AND (c.cuisineName_".$default_lang." LIKE '%".$keyword."%' OR sc.vServiceName_".$default_lang." LIKE '%".$keyword."%') AND c.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                $ssql.= " AND (c.cuisineName_".$default_lang." LIKE '%".$keyword."%' OR sc.vServiceName_".$default_lang." LIKE '%".$keyword."%') ";
            }
        }
    } else if($eStatus != '' && $keyword == '') {
         $ssql.= " AND c.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }


    if($eStatus != ''){ 
        $eStatussql = "";
    }else{
        $eStatussql = " AND c.eStatus != 'Deleted'";
    }

    $sql = "SELECT c.cuisineName_".$default_lang." as `Service Category`,sc.vServiceName_".$default_lang." as `Service Type`, c.eStatus as Status FROM cuisine as c LEFT JOIN service_categories as sc on sc.iServiceId=c.iServiceId where 1=1 $eStatussql $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('cuisine','Service Type','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Service Categories");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(70, 10, $column_heading, 1);
            } else {
                $pdf->Cell(80, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(70, 10, $key, 1);
                } else {
                    $pdf->Cell(80, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}
//Cuisine

////////// Package Start //////////////

if ($section == 'package_type') {
    $ord = ' ORDER BY vName ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vName ASC";
      else
      $ord = " ORDER BY vName DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }

    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vName LIKE '%".$keyword."%' OR eStatus LIKE '%".($keyword)."%')";
        }
    }
    if($option == "eStatus"){   
     $eStatussql = " AND eStatus = '".($keyword)."'";
    }else{
     $eStatussql = " AND eStatus != 'Deleted'";
    }

    $sql = "SELECT vName as Name, eStatus as Status FROM package_type where 1=1 $eStatussql $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Name','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Package Type");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(70, 10, $column_heading, 1);
            } else {
                $pdf->Cell(80, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(70, 10, $key, 1);
                } else {
                    $pdf->Cell(80, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}

////////// Package End ////////////// 

//model
if ($section == 'model') {
    $ord = ' ORDER BY mo.vTitle ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY mo.vTitle ASC";
      else
      $ord = " ORDER BY mo.vTitle DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY mk.vMake ASC";
      else
      $ord = " ORDER BY mk.vMake DESC";
    }


    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY mo.eStatus ASC";
      else
      $ord = " ORDER BY mo.eStatus DESC";
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (mo.vTitle LIKE '%".$keyword."%' OR mo.eStatus LIKE '%".$keyword."%' OR mk.vMake LIKE '%".$keyword."%')";
        }
    }
    
    if($option == "eStatus"){   
     $eStatussql = " AND mo.eStatus = '".ucfirst($keyword)."'";
    }else{
     $eStatussql = " AND mo.eStatus != 'Deleted'";
    }
    $sql = "SELECT mo.vTitle AS Title, mk.vMake AS Make, mo.eStatus AS Status FROM model  AS mo LEFT JOIN make AS mk ON mk.iMakeId = mo.iMakeId WHERE 1=1 $eStatussql $ssql $ord";
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Title', 'Make', 'Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Model");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Id') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(60, 10, $column_heading, 1);
            } else {
                $pdf->Cell(70, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Id') {
                    $pdf->Cell(45, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(60, 10, $key, 1);
                } else {
                    $pdf->Cell(70, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}

//model

//country
if ($section == 'country') {
    
    $ord = ' ORDER BY vCountry ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vCountry ASC";
      else
      $ord = " ORDER BY vCountry DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vPhoneCode ASC";
      else
      $ord = " ORDER BY vPhoneCode DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY eUnit ASC";
      else
      $ord = " ORDER BY eUnit DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }
    //End Sorting
    
    
    if ($keyword != '') {
        if ($option != '') {
            if($eStatus != ''){
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if($eStatus != ''){
                $ssql .= " AND (vCountry LIKE '%".stripslashes($keyword)."%' OR vPhoneCode LIKE '%".stripslashes($keyword)."%' OR vCountryCodeISO_3 LIKE '%".stripslashes($keyword)."%') AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                 $ssql .= " AND (vCountry LIKE '%".stripslashes($keyword)."%' OR vPhoneCode LIKE '%".stripslashes($keyword)."%' OR vCountryCodeISO_3 LIKE '%".stripslashes($keyword)."%')";
            }
        }
    } else if( $eStatus != '' && $keyword == '' ) {
         $ssql.= " AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }

    if($eStatus != '') { 
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND eStatus != 'Deleted'"; 
    }

    $sql = "SELECT vCountry as Country,vPhoneCode as PhoneCode, eUnit as Unit, eStatus as Status FROM country where 1 = 1 $eStatus_sql $ssql";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Country','PhoneCode','Unit','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Country");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(44, 10, $column_heading, 1);
            } else {
                $pdf->Cell(44, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(44, 10, $key, 1);
                } else {
                    $pdf->Cell(44, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}

//State
if ($section == 'state') {
    
    $ord = ' ORDER BY s.vState ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY c.vCountry ASC";
      else
      $ord = " ORDER BY c.vCountry DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY s.vState ASC";
      else
      $ord = " ORDER BY s.vState DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY s.vStateCode ASC";
      else
      $ord = " ORDER BY s.vStateCode DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY s.eStatus ASC";
      else
      $ord = " ORDER BY s.eStatus DESC";
    }
    //End Sorting
    
    if($keyword != ''){
        if($option != '') {
            if (strpos($option, 's.eStatus') !== false) {
                $ssql.= " AND ".stripslashes($option)." LIKE '".stripslashes($keyword)."'";
            }else {
                $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%'";
            }
        }else {
            $ssql.= " AND (c.vCountry LIKE '%".$keyword."%' OR s.vState LIKE '%".$keyword."%' OR s.vStateCode LIKE '%".$keyword."%' OR s.eStatus LIKE '%".$keyword."%')";
        }
    }

    $sql = "SELECT s.vState AS State,s.vStateCode AS `State Code`,c.vCountry AS Country,s.eStatus
            FROM state AS s
            LEFT JOIN country AS c ON c.iCountryId = s.iCountryId
            WHERE s.eStatus !=  'Deleted' $ssql $ord";
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('State','State Code', 'Country','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "State");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else {
                $pdf->Cell(40, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
               if ($column == 'Status') {
                    $pdf->Cell(40, 10, $key, 1);
                } else {
                    $pdf->Cell(40, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
        //$pdf->Output();
    }
}

//State

if ($section == 'city') {
    $ord = ' ORDER BY vCity ASC';
    if($sortby == 1){
      if($order == 0)
        $ord = " ORDER BY st.vState ASC";
      else
        $ord = " ORDER BY st.vState DESC";
    }

    if($sortby == 2){
      if($order == 0)
        $ord = " ORDER BY ct.vCity ASC";
      else
        $ord = " ORDER BY ct.vCity DESC";
    }


    if($sortby == 3){
      if($order == 0)
        $ord = " ORDER BY c.vCountry ASC";
      else
        $ord = " ORDER BY c.vCountry DESC";
    }

    if($sortby == 4){
        if($order == 0)
            $ord = " ORDER BY ct.eStatus ASC";
        else
            $ord = " ORDER BY ct.eStatus DESC";
    }
    
    if($keyword != ''){
        if($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql.= " AND ".stripslashes($option)." LIKE '".stripslashes($keyword)."'";
            }else {
                $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%'";
            }
        }else {
            $ssql.= " AND (ct.vCity LIKE '%".$keyword."%' OR st.vState LIKE '%".$keyword."%' OR c.vCountry LIKE '%".$keyword."%' OR ct.eStatus LIKE '%".$keyword."%')";
        }
    }

    $sql = "SELECT ct.vCity AS City,st.vState AS State,c.vCountry AS Country, ct.eStatus AS Status FROM city AS ct left join country AS c ON c.iCountryId =ct.iCountryId left join state AS st ON st.iStateId=ct.iStateId WHERE  ct.eStatus != 'Deleted' $ssql $ord";
    
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('City','State','Country','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "City");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else {
                $pdf->Cell(35, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(35, 10, $key, 1);
                } else {
                    $pdf->Cell(35, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}

//city

//faq

if ($section == 'faq') {

    $ord = ' ORDER BY f.vTitle_'.$default_lang.' ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY f.vTitle_".$default_lang." ASC";
      else
      $ord = " ORDER BY f.vTitle_".$default_lang." DESC";
    }

    if($sortby == 2){
      if($order == 0)
        $ord = " ORDER BY fc.vTitle ASC";
      else
        $ord = " ORDER BY fc.vTitle DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY f.iDisplayOrder ASC";
      else
      $ord = " ORDER BY f.iDisplayOrder DESC";
    } 

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY f.eStatus ASC";
      else
      $ord = " ORDER BY f.eStatus DESC";
    }

    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (f.vTitle_".$default_lang." LIKE '%".$keyword."%' OR fc.vTitle LIKE '%".$keyword."%' OR f.iDisplayOrder LIKE '%".$keyword."%' OR f.eStatus LIKE '%".$keyword."%')";
        }
    }                                   
    
    $tbl_name  = 'faqs';
    $sql = "SELECT f.vTitle_".$default_lang." as `Title`, fc.vTitle as `Category` ,f.iDisplayOrder as `DisplayOrder` ,f.eStatus  as `Status` FROM ".$tbl_name." f, faq_categories fc WHERE f.iFaqcategoryId = fc.iUniqueId AND fc.vCode = '".$default_lang."' $ssql $ord"; 

    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Title','Category','Order','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;

        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "FAQ");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Title') {
                $pdf->Cell(80, 10, $column_heading, 1);
            }  else if ($column_heading == 'Category') {
                $pdf->Cell(45, 10, $column_heading, 1);
            }  else if ($column_heading == 'Order') {
                $pdf->Cell(28, 10, $column_heading, 1);             
            } else if ($column_heading == 'Status') {
                $pdf->Cell(28, 10, $column_heading, 1);
            } else {
                $pdf->Cell(28, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Title') {
                    $pdf->Cell(80, 10, $key, 1);
                }  else if ($column == 'Category') {
                    $pdf->Cell(45, 10, $key, 1);
                }  else if ($column == 'Order') {
                    $pdf->Cell(28, 10, $key, 1);    
                }  else if ($column == 'Status') {
                    $pdf->Cell(28, 10, $key, 1);
                } else {
                    $pdf->Cell(28, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
        }
}
//faq

// help Detail
if ($section == 'help_detail') {

    $ord = ' ORDER BY f.vTitle_'.$default_lang.' ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY f.vTitle_".$default_lang." ASC";
      else
      $ord = " ORDER BY f.vTitle_".$default_lang." DESC";
    }

    if($sortby == 2){
      if($order == 0)
        $ord = " ORDER BY fc.vTitle ASC";
      else
        $ord = " ORDER BY fc.vTitle DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY f.iDisplayOrder ASC";
      else
      $ord = " ORDER BY f.iDisplayOrder DESC";
    } 

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY f.eStatus ASC";
      else
      $ord = " ORDER BY f.eStatus DESC";
    }

    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (f.vTitle_".$default_lang." LIKE '%".$keyword."%' OR fc.vTitle LIKE '%".$keyword."%' OR f.iDisplayOrder LIKE '%".$keyword."%' OR f.eStatus LIKE '%".$keyword."%')";
        }
    }                                   
    
    $tbl_name       = 'help_detail';
    $sql = "SELECT f.vTitle_".$default_lang." as `Title`, fc.vTitle as `Category` ,f.iDisplayOrder as `DisplayOrder` ,f.eStatus  as `Status` FROM ".$tbl_name." f, help_detail_categories fc WHERE f.iHelpDetailCategoryId = fc.iUniqueId AND fc.vCode = '".$default_lang."' $ssql $ord"; 
    
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Title','Category','Order','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        //print_r($result);die;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Help Detail");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Title') {
                $pdf->Cell(80, 10, $column_heading, 1);
            }  else if ($column_heading == 'Category') {
                $pdf->Cell(45, 10, $column_heading, 1);
            }  else if ($column_heading == 'Order') {
                $pdf->Cell(28, 10, $column_heading, 1);             
            } else if ($column_heading == 'Status') {
                $pdf->Cell(28, 10, $column_heading, 1);
            } else {
                $pdf->Cell(28, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Title') {
                    $pdf->Cell(80, 10, $key, 1);
                }  else if ($column == 'Category') {
                    $pdf->Cell(45, 10, $key, 1);
                }  else if ($column == 'Order') {
                    $pdf->Cell(28, 10, $key, 1);    
                }  else if ($column == 'Status') {
                    $pdf->Cell(28, 10, $key, 1);
                } else {
                    $pdf->Cell(28, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
        //$pdf->Output();
        }
}
//help detail end

//faq category
if ($section == 'faq_category') {
    $ord = ' ORDER BY vTitle ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vImage ASC";
      else
      $ord = " ORDER BY vImage DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vTitle ASC";
      else
      $ord = " ORDER BY vTitle DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY iDisplayOrder ASC";
      else
      $ord = " ORDER BY iDisplayOrder DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vTitle LIKE '%".$keyword."%' OR iDisplayOrder LIKE '%".$keyword."%' OR eStatus LIKE '%".$keyword."%')";
        }
    }

     $sql = "SELECT vTitle as `Title`, iDisplayOrder as `Order`, eStatus as `Status` FROM faq_categories where vCode = '".$default_lang."' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Title','Order','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "FAQ Category");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(44, 10, $column_heading, 1);
            } else {
                $pdf->Cell(44, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(44, 10, $key, 1);
                } else {
                    $pdf->Cell(44, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
       // $pdf->Output();
    }
}
//faq category

//Help Detail category
if ($section == 'help_detail_category') {
    $ord = ' ORDER BY vTitle ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vImage ASC";
      else
      $ord = " ORDER BY vImage DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vTitle ASC";
      else
      $ord = " ORDER BY vTitle DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY iDisplayOrder ASC";
      else
      $ord = " ORDER BY iDisplayOrder DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vTitle LIKE '%".$keyword."%' OR iDisplayOrder LIKE '%".$keyword."%' OR eStatus LIKE '%".$keyword."%')";
        }
    }

     $sql = "SELECT vTitle as `Title`, iDisplayOrder as `Order`, eStatus as `Status` FROM help_detail_categories where vCode = '".$default_lang."' $ssql $ord";
    // die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Title','Order','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Help Detail Category");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(44, 10, $column_heading, 1);
            } else {
                $pdf->Cell(44, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(44, 10, $key, 1);
                } else {
                    $pdf->Cell(44, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
       // $pdf->Output();
    }
}
//Help Detail category

//pages
if ($section == 'page') {
    $ord = ' ORDER BY vPageName ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vPageName ASC";
      else
      $ord = " ORDER BY vPageName DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vPageTitle_".$default_lang." ASC";
      else
      $ord = " ORDER BY vPageTitle_".$default_lang." DESC";
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vPageName LIKE '%".$keyword."%' OR vPageTitle_".$default_lang." LIKE '%".$keyword."%' OR eStatus LIKE '%".$keyword."%')";
        }
    }
    
    $sql = "SELECT vPageName as `Name`, vPageTitle_".$default_lang." as `PageTitle` FROM pages where ipageId NOT IN('5','20','21','20') AND eStatus != 'Deleted' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Name','PageTitle');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Pages");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Name') {
                $pdf->Cell(57, 10, $column_heading, 1);
            } else if ($column_heading == 'PageTitle') {
                $pdf->Cell(100, 10, $column_heading, 1);
            } else {
                $pdf->Cell(20, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Name') {
                    $pdf->Cell(57, 10, $key, 1);
                }  else if ($column == 'PageTitle') {
                    $pdf->Cell(100, 10, $key, 1);
                } else {
                    $pdf->Cell(20, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
        //$pdf->Output();
    }
}
//pages

//languages
if ($section == 'languages') {
    $checktext = isset($_REQUEST['checktext'])?stripslashes($_REQUEST['checktext']):"";

    $catdata = serviceCategories;
    $allservice_cat_data = json_decode($catdata,true);

    $selectedlanguage = isset($_REQUEST['selectedlanguage'])?stripslashes($_REQUEST['selectedlanguage']):$allservice_cat_data[0]['iServiceId'];

    $table_name = 'language_label_'.$selectedlanguage;

    $ord = ' ORDER BY vValue ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vLabel ASC";
      else
      $ord = " ORDER BY vLabel DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vValue ASC";
      else
      $ord = " ORDER BY vValue DESC";
    }

    if($keyword != ''){
        if($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql.= " AND ".addslashes($option)." LIKE '".addslashes($keyword)."'";
            }else {
                if($checktext == 'Yes' && $option == 'vValue'){
                    $ssql.= " AND ".addslashes($option)." LIKE '".addslashes($keyword)."'";
                } else {
                    $ssql.= " AND ".addslashes($option)." LIKE '%".addslashes($keyword)."%'";
                }
            }
        } else {
            $ssql.= " AND (vLabel  LIKE '%".addslashes($keyword)."%' OR vValue  LIKE '%".addslashes($keyword)."%') ";
        }
    }

    if($pageid != "") {
        $ssql.= " AND lPage_id = '".$pageid."'";
    }   

    $sql = "SELECT vLabel as `Code`,vValue as `Value in English Language`  FROM ".$table_name." WHERE vCode = '".$default_lang."' and eStatus='Active' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Code','Value in English Language');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Languages");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
           if ($column_heading == 'Status') {
                $pdf->Cell(88, 10, $column_heading, 1);
            } else {
                $pdf->Cell(88, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(88, 10, $key, 1);
                } else {
                    $pdf->Cell(88, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');        
    }
}

//language label other
if ($section == 'language_label_other') {
    $checktext = isset($_REQUEST['checktext'])?stripslashes($_REQUEST['checktext']):"";

    $ord = ' ORDER BY vValue ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vLabel ASC";
      else
      $ord = " ORDER BY vLabel DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vValue ASC";
      else
      $ord = " ORDER BY vValue DESC";
    }

    if($keyword != ''){
        if($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql.= " AND ".stripslashes($option)." LIKE '".stripslashes($keyword)."'";
            }else {
                if($checktext == 'Yes' && $option == 'vValue'){
                    $ssql.= " AND ".stripslashes($option)." LIKE '".stripslashes($keyword)."'";
                } else{
                    $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%'";
                }
            }
        } else {  
           $ssql.= " AND (vLabel  LIKE '%".$keyword."%' OR vValue  LIKE '%".$keyword."%')";
        }
    }

    if($pageid != "") {
        $ssql.= " AND lPage_id = '".$pageid."'";
    }  
    
    $tbl_name = 'language_label_other';
    $sql = "SELECT vLabel as `Code`,vValue as `Value in English Language`  FROM ".$tbl_name." WHERE vCode = '".$default_lang."' and eStatus='Active' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Code','Value in English Language');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Admin Language Label");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
           if ($column_heading == 'Status') {
                $pdf->Cell(88, 10, $column_heading, 1);
            } else {
                $pdf->Cell(88, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(88, 10, $key, 1);
                } else {
                    $pdf->Cell(88, 10, $key, 1);
                }
            }
        }
       $pdf->Output('D');        
    }
}
//language label other

//vehicle_type
if ($section == 'vehicle_type') {

    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : "";
    $ord = ' ORDER BY vt.vVehicleType_'.$default_lang.' ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vt.vVehicleType_".$default_lang." ASC";
      else
      $ord = " ORDER BY vt.vVehicleType_".$default_lang." DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vt.fDeliveryCharge ASC";
      else
      $ord = " ORDER BY vt.fDeliveryCharge DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY vt.fRadius ASC";
      else
      $ord = " ORDER BY vt.fRadius DESC";
    }

    if($keyword != ''){
        if($option != '') {

          if($iVehicleCategoryId != '') {
                $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%' AND vt.iVehicleCategoryId = '".$iVehicleCategoryId."'";
          } else {
            $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%'";
          }

        } else {
          
          if($iVehicleCategoryId != '') {
            $ssql.= " AND (vt.vVehicleType_".$default_lang." LIKE '%".$keyword."%' OR vt.fDeliveryCharge LIKE '%".$keyword."%' OR vt.fDeliveryChargeCancelOrder LIKE '%".$keyword."%' OR vt.fRadius LIKE '%".$keyword."%' OR vt.iPersonSize  LIKE '%".$keyword."%') AND vt.iVehicleCategoryId = '".$iVehicleCategoryId."'";
          } else {
            $ssql.= " AND (vt.vVehicleType_".$default_lang." LIKE '%".$keyword."%' OR vt.fDeliveryCharge LIKE '%".$keyword."%' OR vt.fDeliveryChargeCancelOrder LIKE '%".$keyword."%' OR vt.fRadius LIKE '%".$keyword."%' OR vt.iPersonSize   LIKE '%".$keyword."%')";
          }

        }

    } else if( $iVehicleCategoryId != '' && $keyword == '') {
         $ssql.= " AND vt.iVehicleCategoryId = '".$iVehicleCategoryId."'";
    } else if( $eType != '' && $keyword == '') {
      $ssql.= " AND vt.eType = '".$eType."'";
    }

    $sql = "SELECT vt.vVehicleType_".$default_lang." as Type,vt.fDeliveryCharge as `Delivery Charges Completed Orders`,vt.fDeliveryChargeCancelOrder as `Delivery Charges Cancelled Orders`,vt.fRadius as Radius,lm.vLocationName as location,vt.iLocationid as locationId  from  vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid where 1 = 1 $ssql $ord";

    // filename for download
    if ($type == 'XLS') {
       $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $data = array_keys($result[0]);
        $arr = array_diff($data, array("locationId"));
        echo implode("\t", $arr) . "\r\n";
         $i = 0;
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'locationId'){
                    $val = "";
                }
                if($key == 'location' && $value['locationId'] == '-1'){
                    $val = "All Location";
                }
                echo $val."\t";
            }
            echo "\r\n";
            $i++;
        }
    } else {
        if($APP_TYPE == 'UberX') {
            $heading = array('Type','Subcategory','Location Name');
        } else {
            $heading = array('Type','Delivery Charges Completed Orders','Delivery Charges Cancelled Orders','Radius','Location Name');
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        //print_r($result);die;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Vehicle Type");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Type' && $APP_TYPE == 'UberX') {
                $pdf->Cell(80, 10, $column_heading, 1);
            } else if ($column_heading == 'Type' && $APP_TYPE != 'UberX'){
                 $pdf->Cell(30, 10, $column_heading, 1);
            }  else if ($column_heading == 'Delivery Charges Completed Orders') {
                $pdf->Cell(58, 10, $column_heading, 1);
            } else if ($column_heading == 'Delivery Charges Cancelled Orders') {
                $pdf->Cell(58, 10, $column_heading, 1);
            } else if ($column_heading == 'Radius') {
                $pdf->Cell(20, 10, $column_heading, 1);             
            } else if ($column_heading == 'Location Name') {
                $pdf->Cell(35, 10, $column_heading, 1);
            }

        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
               if ($column == 'Type' && $APP_TYPE == 'UberX') {
                    $pdf->Cell(80, 10, $key, 1);
                } else if ($column == 'Type' && $APP_TYPE != 'UberX'){
                    $pdf->Cell(30, 10, $key, 1);
                } else if ($column == 'Delivery Charges Completed Orders') {
                    $pdf->Cell(58, 10, $key, 1);
                } else if ($column == 'Delivery Charges Cancelled Orders') {
                    $pdf->Cell(58, 10, $key, 1);
                } else if ($column == 'Radius') {
                    $pdf->Cell(20, 10, $key, 1);    
                } else if ($column == 'location' && $row['locationId'] == "-1") {
                    $pdf->Cell(35, 10, 'All Location', 1);
                } 

            }
        }
        $pdf->Output('D');
        //$pdf->Output();
        }
}
//vehicle_type


//coupon
if ($section == 'coupon') {

    $ord = ' ORDER BY vCouponCode ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vCouponCode ASC";
      else
      $ord = " ORDER BY vCouponCode DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY dActiveDate ASC";
      else
      $ord = " ORDER BY dActiveDate DESC";
    }
    if($sortby == 5){
      if($order == 0)
      $ord = " ORDER BY dExpiryDate ASC";
      else
      $ord = " ORDER BY dExpiryDate DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY eValidityType ASC";
      else
      $ord = " ORDER BY eValidityType DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }

    if($sortby == 6){
      if($order == 0)
      $ord = " ORDER BY iUsageLimit ASC";
      else
      $ord = " ORDER BY iUsageLimit DESC";
    }

    if($sortby == 7){
      if($order == 0)
      $ord = " ORDER BY iUsed ASC";
      else
      $ord = " ORDER BY iUsed DESC";
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vCouponCode LIKE '%".$keyword."%'  OR eValidityType LIKE '%".$keyword."%' OR eStatus LIKE '%".$keyword."%')";
        }
    }                                   
    
    $sql = "SELECT vCouponCode as `Gift Certificate`,fDiscount as `Discount`,eValidityType as `ValidityType`,DATE_FORMAT(dActiveDate,'%d/%m/%Y') AS `Active Date`,DATE_FORMAT(dExpiryDate,'%d/%m/%Y') AS `ExpiryDate`,iUsageLimit as `Usage Limit`,iUsed as `Used`,eStatus as `Status` FROM coupon WHERE eStatus != 'Deleted' $ssql $ord"; 
    
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
		
        while($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Gift Certificate','Discount','ValidityType','Active Date','ExpiryDate','Usage Limit','Used','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Coupon");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Gift Certificate') {
                $pdf->Cell(24, 10, $column_heading, 1);
            }  else if ($column_heading == 'Discount') {
                $pdf->Cell(20, 10, $column_heading, 1);
             }   else if ($column_heading == 'Validity Type') {
                $pdf->Cell(26, 10, $column_heading, 1);
            }  else if ($column_heading == 'Active Date') {
                $pdf->Cell(28, 10, $column_heading, 1);             
            } else if ($column_heading == 'ExpiryDate') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Usage Limit') {
                $pdf->Cell(24, 10, $column_heading, 1); 
            } else if ($column_heading == 'Used') {
                $pdf->Cell(22, 10, $column_heading, 1);
            }   
             else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            }               
            else {
                $pdf->Cell(25, 10, $column_heading, 1); 
            } 
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Gift Certificate') {
                    $pdf->Cell(24, 10, $key, 1);
                }  else if ($column == 'Discount') {
                    
                        $key = $key.' $';
                    $pdf->Cell(20, 10, $key, 1);
                }   else if ($column == 'ValidityType') {                        
                        if($key=='Defined'){
                        $key='Custom';
                         $pdf->Cell(25, 10, $key, 1);   
                    }else{
                     $pdf->Cell(25, 10, $key, 1);       
                    }
                 
                    
                } else if ($column == 'Active Date') {
                    $pdf->Cell(28, 10, $key, 1);
                }   else if ($column == 'ExpiryDate') {
                    $pdf->Cell(25, 10, $key, 1);
                } else if ($column == 'Usage Limit') {
                    $pdf->Cell(24, 10, $key, 1);
                }
                else if ($column == 'Used') {
                    $pdf->Cell(22, 10, $key, 1);
                }
                else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $key, 1);
                }
                
                else {
                    $pdf->Cell(25, 10, $key, 1);
                } 
            }
        }
        $pdf->Output('D');

    }
}
//coupon


//driver 
if ($section == 'driver') {
    
    $ord = ' ORDER BY rd.iDriverId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY rd.vName ASC";
      else
      $ord = " ORDER BY rd.vName DESC";
    }                         

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY rd.vEmail ASC";
      else
      $ord = " ORDER BY rd.vEmail DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY rd.tRegistrationDate ASC";
      else
      $ord = " ORDER BY rd.tRegistrationDate DESC";
    }

    if($sortby == 5){
      if($order == 0)
      $ord = " ORDER BY rd.eStatus ASC";
      else
      $ord = " ORDER BY rd.eStatus DESC";
    }

    if($sortby == 6){
      if($order == 0)
      $ord = " ORDER BY `count` ASC";
      else
      $ord = " ORDER BY `count` DESC";
    }
    

    if($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array("(", "+", ")");
        $removespacekeyword =  preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if(is_numeric($keyword_new)){
          $keyword_new = $keyword_new;
        } else {
          $keyword_new = $keyword;
        }  
        if($option != '') {
            $option_new = $option;
            if($option == 'MobileNumber'){
              $option_new = "CONCAT(rd.vCode,'',rd.vPhone)";
            }
            if($option == 'DriverName'){
              $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
            } 
            if($eStatus != ''){
                $ssql.= " AND ".stripslashes($option_new)." LIKE '%".$generalobjAdmin->clean($keyword_new)."%' AND rd.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
                } else {
                $ssql.= " AND ".stripslashes($option_new)." LIKE '%".$generalobjAdmin->clean($keyword_new)."%'";
                }
        } else {
          if($eStatus != ''){
            $ssql.= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR rd.vEmail LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%".$generalobjAdmin->clean($keyword_new)."%')) AND rd.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
          } else {
            $ssql.= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR rd.vEmail LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%".$generalobjAdmin->clean($keyword_new)."%'))";
          }
        }
    } else if($eStatus != '' && $keyword == '') {
         $ssql.= " AND rd.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }
    
    $dri_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
    }

    if($eStatus != '') { 
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND rd.eStatus != 'Deleted'"; 
    }

    $ssql1 = "AND (rd.vEmail != '' OR rd.vPhone != '')";

    $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS `Driver Name`,rd.vEmail as `Email Id`,(SELECT count(dv.iDriverVehicleId) FROM driver_vehicle AS dv WHERE dv.iDriverId=rd.iDriverId AND dv.eStatus != 'Deleted' AND dv.iMakeId != 0 AND dv.iModelId != 0) AS `Counts`, rd.tRegistrationDate as `Signup Date`,CONCAT(rd.vCode,' ',rd.vPhone) as `Mobile`,rd.eStatus as `status` FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId 
    WHERE 1 = 1  $eStatus_sql $ssql $ssql1 $dri_ssql $ord"; 

    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'Driver Name'){
                    $val = $generalobjAdmin->clearName($val);
                }
                if($key == 'Email Id'){
                    $val = $generalobjAdmin->clearEmail($val);
                }
                if($key == 'Phone'){
                    $val = $generalobjAdmin->clearPhone($val);
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array($langage_lbl_admin['LBL_DRIVER_NAME_ADMIN'],'Email Id','Counts','Signup Date','Mobile','Status');
        $result = $obj->ExecuteQuery($sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;

        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']);
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == $langage_lbl_admin['LBL_DRIVER_NAME_ADMIN']) {
                $pdf->Cell(29, 10, $column_heading, 1);
            }  else if ($column_heading == 'Counts') {
                $pdf->Cell(20, 10, $column_heading, 1);
             }   else if ($column_heading == 'Email Id') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Signup Date') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Mobile') {
                $pdf->Cell(35, 10, $column_heading, 1); 
            } else if ($column_heading == 'status') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else {
                $pdf->Cell(20, 10, $column_heading, 1); 
            }         }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if($column == 'Driver Name'){
                    $values = $generalobjAdmin->clearName($key);
                }
                if($column == 'Email Id'){
                    $values = $generalobjAdmin->clearEmail($key);
                }
                if($column == 'Mobile'){
                    $values = $generalobjAdmin->clearPhone($key);
                }
                if ($column == 'Driver Name') {
                    $pdf->Cell(29, 10, $values, 1 , 0 ,"1");
                }  else if ($column == 'Email Id') {                   
                    $pdf->Cell(50, 10, $values, 1); 
                }  else if ($column == 'Counts') {
                   $pdf->Cell(20, 10, $values, 1);   
                }   else if ($column == 'Signup Date') {
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'Mobile') {
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'status') {
                    $pdf->Cell(20, 10, $values, 1);
                }
            }
        }
        $pdf->Output('D');
        }
}
//driver

//vehicles 
if ($section == 'vehicles') {  
    $ord = ' ORDER BY dv.iDriverVehicleId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY m.vMake ASC";
      else
      $ord = " ORDER BY m.vMake DESC";
    }
    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY rd.vName ASC";
      else
      $ord = " ORDER BY rd.vName DESC";
    }

    if($sortby == 5){
      if($order == 0)
      $ord = " ORDER BY dv.eStatus ASC";
      else
      $ord = " ORDER BY dv.eStatus DESC";
    }
    //End Sorting

    $dri_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
    }

    // Start Search Parameters
    $option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
    $keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
    $searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
    $iDriverId = isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:"";
    $ssql = '';
    if($keyword != ''){
        if($option != '') {
           if($eStatus != ''){
                $ssql.= " AND ".stripslashes($option)." LIKE '%".$generalobjAdmin->clean($keyword)."%' AND dv.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            }else {
                $ssql.= " AND ".stripslashes($option)." LIKE '%".$generalobjAdmin->clean($keyword)."%'";
            }
        }else {
            if($eStatus != ''){
                $ssql.= " AND (m.vMake LIKE '%".$generalobjAdmin->clean($keyword)."%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%".$generalobjAdmin->clean($keyword)."%') AND dv.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                $ssql.= " AND (m.vMake LIKE '%".$generalobjAdmin->clean($keyword)."%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%".$generalobjAdmin->clean($keyword)."%')";
            }
        }
    } else if($eStatus != '' && $keyword == '') {
         $ssql.= " AND dv.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }
    // End Search Parameters

    if($iDriverId != "") {
        $query1="SELECT COUNT(iDriverVehicleId) as total FROM driver_vehicle where iDriverId ='".$iDriverId."'";
        $totalData = $obj->MySQLSelect($query1);
        $total_vehicle = $totalData[0]['total'];
        if($total_vehicle > 1){
           $ssql .= " AND dv.iDriverId='".$iDriverId."'";
        }
    }

    if(!empty($eStatus)){
        $eQuery= "";
    } else {
        $eQuery= " AND dv.eStatus != 'Deleted' AND rd.eStatus != 'Deleted'";
    }

    $sql = "SELECT CONCAT(m.vMake,' ', md.vTitle) AS Taxis,CONCAT(rd.vName,' ',rd.vLastName) AS Driver, dv.eStatus as Status FROM driver_vehicle dv, register_driver rd, make m, model md WHERE 1=1 AND dv.iDriverId = rd.iDriverId  AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId $eQuery $ssql $dri_ssql";

    // filename for download
    if ($type == 'XLS') {
         $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
           $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'Taxis'){
                    $val;
                }
                if($key == 'Driver'){
                    $val = $generalobjAdmin->clearName($val);
                }
                if($key == 'Status'){
                    $val ;
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array('Taxis','Driver','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Taxis");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Taxis') {
                $pdf->Cell(70, 10, $column_heading, 1);
            }  else if ($column_heading == 'Driver') {
                $pdf->Cell(50, 10, $column_heading, 1);
            }   else if ($column_heading == 'Status') {
                $pdf->Cell(50, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Taxis') {
                    $pdf->Cell(70, 10, $key, 1);
                }  else if ($column == 'Driver') {                 
                  $pdf->Cell(50, 10, $generalobjAdmin->clearName($key), 1); //}
                }   else if ($column == 'Status') {
                    $pdf->Cell(50, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
        }
}
//vehicles

//email_template
if ($section == 'email_template') {
    $ord = ' ORDER BY vSubject_'.$default_lang.' ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vSubject_".$default_lang." ASC";
      else
      $ord = " ORDER BY vSubject_".$default_lang." DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vEmail_Code ASC";
      else
      $ord = " ORDER BY vEmail_Code DESC";
    }

     if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    } 
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vSubject_".$default_lang." LIKE '%".$keyword."%' OR vEmail_Code LIKE '%".$keyword."%')";
        }
    }
    $default_lang   = $generalobj->get_default_lang();
    $tbl_name       = 'email_templates';
    $sql = "SELECT vSubject_".$default_lang." as `Email Subject`, vEmail_Code as `Email Code` FROM ".$tbl_name." WHERE eStatus = 'Active' $ssql $ord"; 
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Email Subject','Email Code');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Email Templates");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Email Subject') {
                $pdf->Cell(98, 10, $column_heading, 1);
            } else if ($column_heading == 'Email Code') {
                $pdf->Cell(98, 10, $column_heading, 1);
            } else {
                $pdf->Cell(8, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Email Subject') {
                    $pdf->Cell(98, 10, $key, 1);
                } else if ($column == 'Email Code') {
                    $pdf->Cell(98, 10, $key, 1);
                } else {
                    $pdf->Cell(8, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}
//email_template

//Restricted Area
if ($section == 'restrict_area') {

    $ord = ' ORDER BY lm.vLocationName ASC';
    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY lm.vLocationName ASC";
      else
      $ord = " ORDER BY lm.vLocationName DESC";
    }

    if($sortby == 5){
      if($order == 0)
      $ord = " ORDER BY ra.eRestrictType ASC";
      else
      $ord = " ORDER BY ra.eRestrictType DESC";
    }

    if($sortby == 6){
      if($order == 0)
      $ord = " ORDER BY ra.eStatus ASC";
      else
      $ord = " ORDER BY ra.eStatus DESC";
    }

    if($sortby == 7){
      if($order == 0)
      $ord = " ORDER BY ra.eType ASC";
      else
      $ord = " ORDER BY ra.eType DESC";
    }
    //End Sorting
    
    if($keyword != ''){
        if($option != '') {
            if (strpos($option, 'ra.eStatus') !== false) {
                $ssql.= " AND ".stripslashes($option)." LIKE '".stripslashes($generalobjAdmin->clean($keyword))."'";
            }else {
                $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($generalobjAdmin->clean($keyword))."%'";
            }
        }else {
            $ssql.= " AND (lm.vLocationName LIKE '%".$generalobjAdmin->clean($keyword)."%' OR ra.eStatus LIKE '%".$generalobjAdmin->clean($keyword)."%' OR ra.eRestrictType LIKE '%".$generalobjAdmin->clean($keyword)."%' OR ra.eType LIKE '%".$generalobjAdmin->clean($keyword)."%')";
        }
    }
    $sql = "SELECT lm.vLocationName as Address, ra.eRestrictType AS Area, ra.eType AS Type, ra.eStatus AS Status FROM restricted_negative_area AS ra LEFT JOIN location_master AS lm ON lm.iLocationId=ra.iLocationId WHERE 1=1 $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Address','Area','Type','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Address");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Area') {
                $pdf->Cell(40, 10, $column_heading, 1);
            }else if ($column_heading == 'Address') {
                $pdf->Cell(80, 10, $column_heading, 1);
            } else {
                $pdf->Cell(40, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Area') {
                    $pdf->Cell(40, 10, $key, 1);
                }else if ($column == 'Address') {
                    $pdf->Cell(80, 10, $key, 1);
                } else {
                    $pdf->Cell(40, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}


//visit location 
if ($section == 'visitlocation') {
    $ord = ' ORDER BY iVisitId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vSourceAddresss ASC";
      else
      $ord = " ORDER BY vSourceAddresss DESC";
    }

     if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY tDestAddress ASC";
      else
      $ord = " ORDER BY tDestAddress DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
           $ssql.= " AND (vSourceAddresss LIKE '%".$keyword."%' OR tDestAddress LIKE '%".$keyword."%' OR eStatus LIKE '%".$keyword."%')";
        }
    }

    $sql = "SELECT vSourceAddresss as SourceAddress, tDestAddress as DestAddress,eStatus as Status FROM visit_address where eStatus != 'Deleted' $ssql $ord";
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
        $heading = array('SourceAddress','DestAddress','Status');
    } else {
        $heading = array('SourceAddress','DestAddress','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;

        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Visit Location");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'SourceAddress') {
                $pdf->Cell(75, 10, $column_heading, 1);
        }   else if ($column_heading == 'DestAddress') {
                $pdf->Cell(75, 10, $column_heading, 1);
            }   else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            }                           
            else {
                $pdf->Cell(45, 10, $column_heading, 1); 
            } 
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'SourceAddress') {                         
                    $pdf->Cell(75, 10, $generalobjAdmin->clearCmpName($key), 1);
                }   else if ($column == 'DestAddress') {                 
                  $pdf->Cell(75, 10, $generalobjAdmin->clearName($key), 1); //}
                }   else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $key, 1);
                }                               
                else {
                    $pdf->Cell(45, 10, $key, 1);
                } 
            }
        }
        $pdf->Output('D');
    }
}

//hotel rider

if ($section == 'hotel_rider') {

    $ord = ' ORDER BY vName ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vName ASC";
      else
      $ord = " ORDER BY vName DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vEmail ASC";
      else
      $ord = " ORDER BY vEmail DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY tRegistrationDate ASC";
      else
      $ord = " ORDER BY tRegistrationDate DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }
    $rdr_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
    }
    
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (concat(vFirstName,' ',vLastName) LIKE '%".$keyword."%' OR vEmail LIKE '%".$keyword."%' OR vPhone LIKE '%".$keyword."%' OR eStatus LIKE '%".$keyword."%')";
        }
    }

    $sql = "SELECT  CONCAT(vName,' ',vLastName) as Name,vEmail as Email,CONCAT(vPhoneCode,' ',vPhone) AS Mobile,eStatus as Status FROM hotel WHERE eStatus != 'Deleted' $ssql $rdr_ssql $ord";
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'Name'){
                    $val = $generalobjAdmin->clearName($val);
                }
                if($key == 'Email'){
                    $val = $generalobjAdmin->clearEmail($val);
                }
                if($key == 'Mobile'){
                    $val = $generalobjAdmin->clearPhone($val);
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array('Name', 'Email', 'Mobile', 'Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Hotel Riders");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Email') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else if ($column_heading == 'Mobile') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if($column == 'Name'){
                    $values = $generalobjAdmin->clearName($key);
                }
                if($column == 'Email'){
                    $values = $generalobjAdmin->clearEmail($key);
                }
                if($column == 'Mobile'){
                    $values = $generalobjAdmin->clearPhone($key);
                }
                if ($column == 'Email') {
                    $pdf->Cell(55, 10, $values, 1);
                } else if ($column == 'Mobile') {
                    $pdf->Cell(45, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}

if ($section == 'sub_service_category') {   
    global $tconfig;
	$sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : '';

    $ord = ' ORDER BY iDisplayOrder ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vCategory_".$default_lang." ASC";
      else
      $ord = " ORDER BY vCategory_".$default_lang." DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY Servicetypes ASC";
      else
      $ord = " ORDER BY Servicetypes DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY iDisplayOrder ASC";
      else
      $ord = " ORDER BY iDisplayOrder DESC";
    }

    if ($keyword != '') {
        if ($option != '') {
            if($eStatus != ''){
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'  AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if($eStatus != ''){
                $ssql.= " AND (vCategory_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%') AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                $ssql.= " AND (vCategory_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%')";
            }
        }
    } else if( $eStatus != '' && $keyword == '' ) {
         $ssql.= " AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }

    if($parent_ufx_catid != "0") {
        $sql = "SELECT vCategory_".$default_lang." as SubCategory, (SELECT vCategory_".$default_lang." FROM vehicle_category WHERE iVehicleCategoryId='".$sub_cid."') as Category, (select count(iVehicleTypeId) from vehicle_type where vehicle_type.iVehicleCategoryId = vehicle_category.iVehicleCategoryId) as `Service Types`, iDisplayOrder as `Display Order`, eStatus as Status FROM vehicle_category WHERE 1 = 1 $ssql $ord";
    } else {
        $sql = "SELECT vCategory_".$default_lang." as SubCategory, (SELECT vCategory_".$default_lang." FROM vehicle_category WHERE iVehicleCategoryId='".$sub_cid."') as Category,(select count(iVehicleTypeId) from vehicle_type where vehicle_type.iVehicleCategoryId = vehicle_category.iVehicleCategoryId) as `Service Types`, iDisplayOrder as `Display Order`,eStatus as Status FROM vehicle_category WHERE iParentId='".$sub_cid."' $ssql $ord";
    } 
    // filename for download
    if ($type == 'XLS') {
        $filename = $section . "_" . date('Ymd') . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'SubCategory'){
                    $val = $generalobjAdmin->clearName($val);
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array('SubCategory', 'Category' ,'Service Types','Display Order','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Sub Category");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
           if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if($column_heading == 'Service Types'){
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if($column_heading == 'Display Order'){
                $pdf->Cell(20, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
				
                $values = $key;
				$id= "";
				 if($column == 'iVehicleCategoryId'){
					$id2 = $key;					 
				 }
				
                if($column == 'SubCategory'){
					
                    $values = $generalobjAdmin->clearName($key);
                }
				
				if($column == 'Display Order'){
                    
                    $values = $generalobjAdmin->clearName($key);
                }
                
                if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else if($column == 'Service Types'){
                    $pdf->Cell(25, 10, $values, 1);
                } else if($column == 'Display Order'){
                    $pdf->Cell(20, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}

if ($section == 'service_category') {
    global $tconfig;
	$sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : '';   
    
    $ord = ' ORDER BY iDisplayOrder ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vc.vCategory_".$default_lang." ASC";
      else
      $ord = " ORDER BY vc.vCategory_".$default_lang." DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY vc.eStatus ASC";
      else
      $ord = " ORDER BY vc.eStatus DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY SubCategories ASC";
      else
      $ord = " ORDER BY SubCategories DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY iDisplayOrder ASC";
      else
      $ord = " ORDER BY iDisplayOrder DESC";
    }

    if ($keyword != '') {
        if ($option != '') {
            if($eStatus != ''){
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vc.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if($eStatus != ''){
                $ssql.= " AND vc.(vCategory_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%') AND vc.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
            } else {
                $ssql.= " AND vc.(vCategory_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%')";
            }
        }
    } else if( $eStatus != '' && $keyword == '' ) {
         $ssql.= " AND vc.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }


   $sql = "SELECT vc.vCategory_".$default_lang." as Category ,(select count(iVehicleCategoryId) from vehicle_category where iParentId=vc.iVehicleCategoryId) as SubCategories,vc.iDisplayOrder as `Display Order`,vc.eStatus as Status FROM vehicle_category as vc WHERE  vc.iParentId='0' $ssql $ord"; 

    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'Category'){
                    $val = $generalobjAdmin->clearName($val);
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array('Category','SubCategories','Display Order','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Category");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
           if ($column_heading == 'Category') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else if ($column_heading == 'Total') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Display Order') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
				/* echo $column;
				echo "<br>";
				echo $key; */
                $values = $key;			 
				
                if($column == 'Category'){
					
                    $values = $generalobjAdmin->clearName($key);
                }
				
				
               if($column == 'Total'){
					
                    $values = $key;
                } 
                
                if ($column == 'Category') {
                    $pdf->Cell(55, 10, $values, 1);
                }  			
				else if ($column == 'Total') {
                    $pdf->Cell(45, 10, $values, 1);
                } else if ($column == 'Display Order') {
                    $pdf->Cell(45, 10, $values, 1);
                }  else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}

//mask_number
if ($section == 'mask_number') {
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (mask_number LIKE '%".$keyword."%' OR eStatus LIKE '%".$keyword."%')";
        }
    }

     $sql = "SELECT masknum_id as `Id`, mask_number as `Masking Number`,adding_date as `Added Date`, eStatus as `Status` FROM masking_numbers where 1 = 1 $ssql";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Id', 'Masking Number','Added Date','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Masking Numbers");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Id') {
                $pdf->Cell(18, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else {
                $pdf->Cell(55, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Id') {
                    $pdf->Cell(18, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(55, 10, $key, 1);
                } else {
                    $pdf->Cell(55, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}
//mask_number



//document master
//driver 
if ($section == 'Document_Master') {
    $eType_value = isset($_REQUEST['eType_value'])?stripslashes($_REQUEST['eType_value']):"";    
    $ord = ' ORDER BY dm.doc_name ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY c.vCountry ASC";
      else
      $ord = " ORDER BY c.vCountry DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY dm.doc_usertype ASC";
      else
      $ord = " ORDER BY dm.doc_usertype DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY dm.doc_name ASC";
      else
      $ord = " ORDER BY dm.doc_name DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY dm.status ASC";
      else
      $ord = " ORDER BY dm.status DESC";
    }

    if($keyword != ''){
        if($option != '') {
            if (strpos($option, 'status') !== false) {
                $ssql.= " AND ".stripslashes($option)." LIKE '".stripslashes($keyword)."'";
            }else {
                $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%'";
            }
        }else {
            $ssql.= " AND (c.vCountry LIKE '%".$keyword."%' OR dm.doc_usertype LIKE '%".$keyword."%' OR dm.doc_name LIKE '%".$keyword."%' OR dm.status LIKE '%".$keyword."%')";
        }
    }
    if($option == "dm.status"){ 
         $eStatussql = " AND dm.status = '$keyword'";
    }else{
     $eStatussql = " AND dm.status != 'Deleted'";
    }
    	
    $dri_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $dri_ssql = " And dm.doc_instime > '" . WEEK_DATE . "'";
    }

    $sql = "SELECT c.vCountry as Country, dm.doc_name as `Document Name`,dm.doc_usertype as `Document For`, dm.status as Status FROM `document_master` AS dm LEFT JOIN `country` AS c ON c.vCountryCode=dm.country WHERE 1=1 $eStatussql $ssql $ord "; 

    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
            if($val == 'UberX'){
               $val = 'Other Services';
            }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {

        $heading = array('Country','Document Name','Document For','Status');

        $result = $obj->ExecuteQuery($sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;

        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Documents");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Country') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Document For') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Document Name') {
                $pdf->Cell(50, 10, $column_heading, 1);              
            } else if ($column_heading == 'Status') {
                $pdf->Cell(35, 10, $column_heading, 1);
            }else {
                $pdf->Cell(20, 10, $column_heading, 1); 
            }         }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;

                if ($column == 'Country') {                            
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'Document For') {                   
                    $pdf->Cell(35, 10, $values, 1); 
                } else if ($column == 'Document Name') {
                   $pdf->Cell(50, 10, $values, 1);   
                } else if ($column == 'Status') {
                    $pdf->Cell(35, 10, $values, 1);
                } else {
                    $pdf->Cell(20, 10, $key, 1);
                } 
            }
        }
        $pdf->Output('D');
        }
}
//document master

// review page 
if ($section == 'review') {
	$reviewtype = isset($_REQUEST['reviewtype']) ? $_REQUEST['reviewtype'] : 'Driver';
    $adm_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $adm_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
    }
    $ord = ' ORDER BY iRatingId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY o.vOrderNo ASC";
      else
      $ord = " ORDER BY o.vOrderNo DESC";
    }
    if($sortby == 2)
    {
        if($reviewtype=='Driver')
        {
            if($order == 0)
            $ord = " ORDER BY rd.vName ASC";
            else
            $ord = " ORDER BY rd.vName DESC";

        } else if($reviewtype=='Company'){

        if($order == 0)
        $ord = " ORDER BY c.vCompany ASC";
        else
        $ord = " ORDER BY c.vCompany DESC";

      } else {

            if($order == 0)
            $ord = " ORDER BY ru.vName ASC";
            else
            $ord = " ORDER BY ru.vName DESC";

        }
    }
    if($sortby == 6)
    {
        if($reviewtype=='Driver')
      {
        if($order == 0)
        $ord = " ORDER BY ru.vName ASC";
        else
        $ord = " ORDER BY ru.vName DESC";

      } else if($reviewtype=='Company'){

        if($order == 0)
        $ord = " ORDER BY ru.vName ASC";
        else
        $ord = " ORDER BY ru.vName DESC";

      } else {
        if($order == 0)
        $ord = " ORDER BY rd.vName ASC";
        else
        $ord = " ORDER BY rd.vName DESC";
      }
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY r.vRating1 ASC";
      else
      $ord = " ORDER BY r.vRating1 DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY r.tDate ASC";
      else
      $ord = " ORDER BY r.tDate DESC";
    }

    if($sortby == 5){
      if($order == 0)
      $ord = " ORDER BY r.vMessage ASC";
      else
      $ord = " ORDER BY r.vMessage DESC";
    }
    //End Sorting
    $ssql = '';
    if($keyword != ''){
        if($option != '') {
            if (strpos($option, 'r.eStatus') !== false) {
                $ssql.= " AND ".stripslashes($option)." LIKE '".$generalobjAdmin->clean($keyword)."'";
            }else {
                $option_new = $option;
                if($option == 'drivername'){
                  $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
                } 
                if($option == 'ridername'){
                  $option_new = "CONCAT(ru.vName,' ',ru.vLastName)";
                }
                $ssql.= " AND ".stripslashes($option_new)." LIKE '%".$generalobjAdmin->clean($keyword)."%'";
            }
        }else {
            if($reviewtype == 'Driver') {
              $ssql.= " AND (o.vOrderNo LIKE '%".$generalobjAdmin->clean($keyword)."%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%".$generalobjAdmin->clean($keyword)."%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%".$generalobjAdmin->clean($keyword)."%' OR r.vRating1 LIKE '%".$generalobjAdmin->clean($keyword)."%')";

            } else if($reviewtype == 'Company') {

              $ssql.= " AND (o.vOrderNo LIKE '%".$generalobjAdmin->clean($keyword)."%' OR  c.vCompany LIKE '%".$generalobjAdmin->clean($keyword)."%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%".$generalobjAdmin->clean($keyword)."%' OR r.vRating1 LIKE '%".$generalobjAdmin->clean($keyword)."%')";

            } else {

              $ssql.= " AND (o.vOrderNo LIKE '%".$generalobjAdmin->clean($keyword)."%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%".$generalobjAdmin->clean($keyword)."%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%".$generalobjAdmin->clean($keyword)."%' OR r.vRating1 LIKE '%".$generalobjAdmin->clean($keyword)."%')";

            }
        }
    }
// End Search Parameters
    $chkusertype ="";
    if($reviewtype == "Driver") {
      $chkusertype = "Driver";
    } else if($reviewtype == "Company") {
      $chkusertype = "Company";
    } else {
      $chkusertype = "Passenger";
    }
		
		if($reviewtype == "Driver") {	
			$sql = "SELECT o.vOrderNo as `Order Number`, CONCAT(ru.vName,' ',ru.vLastName) as `From User Name`,CONCAT(rd.vName,' ',rd.vLastName) as `To Driver Name` ,rd.vAvgRating as AverageRate,r.vRating1 as Rate,r.tDate as `Date`,r.vMessage as Comment FROM ratings_user_driver as r LEFT JOIN orders as o ON r.iOrderId=o.iOrderId LEFT JOIN company as c ON c.iCompanyId=o.iCompanyId LEFT JOIN register_driver as rd ON rd.iDriverId=o.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=o.iUserId WHERE 1=1 AND r.eToUserType='".$chkusertype."' And ru.eStatus!='Deleted' $ssql $adm_ssql $ord";
		} else if($reviewtype == "Company") { 
            $sql = "SELECT o.vOrderNo as `Order Number`,CONCAT(ru.vName,' ',ru.vLastName) as `From User Name`,c.vCompany as `To Restaurant Name`,r.vRating1 as Rate,r.tDate as `Date`,c.vAvgRating as AverageRate,r.vMessage as Comment FROM ratings_user_driver as r LEFT JOIN orders as o ON r.iOrderId=o.iOrderId LEFT JOIN company as c ON c.iCompanyId=o.iCompanyId LEFT JOIN register_driver as rd ON rd.iDriverId=o.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=o.iUserId WHERE 1=1 AND r.eToUserType='".$chkusertype."' AND ru.eStatus!='Deleted' $ssql $adm_ssql $ord";
        } else {
			$sql = "SELECT o.vOrderNo as `Order Number`,CONCAT(rd.vName,' ',rd.vLastName) as `From Delivery Driver Name`,CONCAT(ru.vName,' ',ru.vLastName) as `To User Name`,ru.vAvgRating as AverageRate,vRating1 as Rate,r.tDate as `Date`,r.vMessage as Comment FROM ratings_user_driver as r LEFT JOIN orders as o ON r.iOrderId=o.iOrderId LEFT JOIN company as c ON c.iCompanyId=o.iCompanyId LEFT JOIN register_driver as rd ON rd.iDriverId=o.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=o.iUserId WHERE 1=1 AND r.eToUserType='".$chkusertype."' And ru.eStatus!='Deleted'  $ssql $adm_ssql $ord";
		}	
		
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename. ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {	
                if($key == 'RiderNumber'){
                    $val = $generalobjAdmin->clearName($val);
                }
				if($reviewtype == "Driver")
				{
					if($key == 'DriverName'){
						$val = $val;
					}
				}else{
					if($key == 'RiderName'){
						$val = $val;
					}
				
				}
				
                if($key == 'AverageRate'){
                    $val = $val;
                }
				if($reviewtype == "Driver")
				{
					if($key == 'RiderName'){
						$val = $val;
					}
				}else{
					if($key == 'DriverName'){
						$val = $val;
					}				
				}	
				
				if($key == 'Rate'){
                    $val = $val;
                }
				
				if($key == 'Date'){
                    $val = $generalobjAdmin->DateTime($val);
                }
				
				if($key == 'Comment'){
                    $val =$val;
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
		if($reviewtype == "Driver")
		{
			$heading = array('RiderNumber', 'DriverName', 'AverageRate', 'RiderName', 'Rate','Date','Comment');
		}else{
		$heading = array('RiderNumber', 'RiderName', 'AverageRate', 'DriverName', 'Rate','Date','Comment');
		
		}	
		
			$result = $obj->ExecuteQuery($sql);
			while ($row = mysqli_fetch_assoc($result)) {
				$resultset[] = $row;
			}
			$result = $resultset;
			$pdf = new FPDF('P', 'mm', 'Letter');
			$pdf->AddPage();
			$pdf->SetFillColor(36, 96, 84);

			$pdf->SetFont('Arial', 'b', 15);
			$pdf->Cell(100, 16, "Review");
			$pdf->Ln();
			$pdf->SetFont('Arial', 'b', 9);
			$pdf->Ln();
			foreach ($heading as $column_heading) {
				if ($column_heading == 'RiderNumber') {
					$pdf->Cell(22, 10, $column_heading, 1);
				} else if ($column_heading == 'DriverName') {
					$pdf->Cell(40, 10, $column_heading, 1);
				} else if ($column_heading == 'AverageRate') {
					$pdf->Cell(21, 10, $column_heading, 1);
				} else if ($column_heading == 'RiderName') {
					$pdf->Cell(25, 10, $column_heading, 1);
				} else if ($column_heading == 'Rate') {
					$pdf->Cell(10, 10, $column_heading, 1);
				}
				else if ($column_heading == 'Date') {
					$pdf->Cell(42, 10, $column_heading, 1);
				}
				else {
					$pdf->Cell(45, 10, $column_heading, 1);
				}
			}
			$pdf->SetFont('Arial', '', 9);
			foreach ($result as $row) {
				$pdf->Ln();
				foreach ($row as $column => $key) {
					$values = $key;               
					if($column == 'DriverName'){
						$values = $generalobjAdmin->clearName($key);
					}
					if($column == 'Date'){
						$values = $generalobjAdmin->DateTime($key);
					}
					
					
					$generalobjAdmin->DateTime($val);
					
					if ($column == 'RiderNumber') {
						$pdf->Cell(22, 10, $values, 1);
					} else if ($column == 'DriverName') {
						$pdf->Cell(40, 10, $values, 1);
					} else if ($column == 'AverageRate') {
						$pdf->Cell(21, 10, $values, 1);
					} else if ($column == 'RiderName') {
						$pdf->Cell(25, 10, $values, 1);
					}else if ($column == 'Rate') {
						$pdf->Cell(10, 10, $values, 1);
					} 
					else if ($column == 'Date') {
						$pdf->Cell(42, 10, $values, 1);
					} 
					else {
						$pdf->Cell(45, 10, $values, 1);
					}
				}
			}
		  $pdf->Output('D');
			// $pdf->Output();
			  
    }
	
}

//sms_template
if ($section == 'sms_template') {
    $ord = " ORDER BY vEmail_Code ASC";
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY vEmail_Code ASC";
      else
      $ord = " ORDER BY vEmail_Code DESC";
    }

    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY vSubject_".$default_lang." ASC";
      else
      $ord = " ORDER BY vSubject_".$default_lang." DESC";
    }

    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND vEmail_Code LIKE '%".$keyword."%' OR vSubject_".$default_lang." LIKE '%".$keyword."%'";
        }
    }
    $default_lang   = $generalobj->get_default_lang();
    $tbl_name       = 'send_message_templates';
    $sql = "SELECT vSubject_".$default_lang." as `SMS Title`,vEmail_Code as `SMS Code` FROM ".$tbl_name." WHERE eStatus = 'Active' $ssql $ord"; 
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('SMS Title','SMS Code');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "SMS Templates");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'SMS Title') {
                $pdf->Cell(82, 10, $column_heading, 1);
            } else if ($column_heading == 'SMS Code') {
                $pdf->Cell(82, 10, $column_heading, 1);
            } else {
                $pdf->Cell(82, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'SMS Title') {
                    $pdf->Cell(82, 10, $key, 1);
                } else if ($column == 'SMS Code') {
                    $pdf->Cell(82, 10, $key, 1);
                } else {
                    $pdf->Cell(82, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}
//sms_template

// locationwise fare
if ($section == 'locationwise_fare') {
   $ord = ' ORDER BY ls.iLocatioId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY lm1.vLocationName ASC";
      else
      $ord = " ORDER BY lm1.vLocationName DESC";
    }

     if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY lm2.vLocationName ASC";
      else
      $ord = " ORDER BY lm2.vLocationName DESC";
    }

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY ls.fFlatfare ASC";
      else
      $ord = " ORDER BY ls.fFlatfare DESC";
    } 

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY ls.eStatus ASC";
      else
      $ord = " ORDER BY ls.eStatus DESC";
    }
    if($sortby == 5){
      if($order == 0)
      $ord = " ORDER BY vt.vVehicleType ASC";
      else
      $ord = " ORDER BY vt.vVehicleType DESC";
    }

    if($keyword != ''){
        if($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql.= " AND ".stripslashes($option)." LIKE '".stripslashes($keyword)."'";
            }else {
                $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%'";
            }
        }else {
            $ssql.= " AND lm1.vLocationName LIKE '%".$keyword."%' OR lm2.vLocationName LIKE '%".$keyword."%' OR ls.fFlatfare LIKE '%".$keyword."%' OR ls.eStatus LIKE '%".$keyword."%' OR vt.vVehicleType LIKE '%".$keyword."%'";
        }
    }

    if($option == "eStatus"){   
        $eStatussql = " AND ls.eStatus = '".ucfirst($keyword)."'";
    }else{
        $eStatussql = " AND ls.eStatus != 'Deleted'";
    }

    $sql = "SELECT lm2.vLocationName as `Source LocationName`,lm1.vLocationName as `Destination LocationName`,ls.fFlatfare as `Flat Fare`,vt.vVehicleType as `Vehicle Type`,ls.eStatus as `Status` FROM `location_wise_fare` ls left join location_master lm1 on ls.iToLocationId = lm1.iLocationId left join location_master lm2 on ls.iFromLocationId = lm2.iLocationId left join vehicle_type as vt on vt.iVehicleTypeId=ls.iVehicleTypeId  WHERE 1 = 1 $eStatussql $ssql $ord";

    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array('Source LocationName','Destination LocationName','Flat Fare','Vehicle Type','Status');
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Locationwise Fare");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Source LocationName') {
                $pdf->Cell(65, 10, $column_heading, 1);
            } else if ($column_heading == 'Destination LocationName') {
                $pdf->Cell(65, 10, $column_heading, 1);
            } else if ($column_heading == 'Flat Fare') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else {
                $pdf->Cell(30, 10, $column_heading, 1); 
            }
        }
        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Source LocationName') {
                    $pdf->Cell(65, 10, $key, 1);
                } else if ($column == 'Destination LocationName') {
                    $pdf->Cell(65, 10, $key, 1);
                } else if ($column == 'Flat Fare') {
                    $pdf->Cell(20, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(20, 10, $key, 1);
                } else {
                    $pdf->Cell(30, 10, $key, 1);
                }
            }
        }
        $pdf->Output('D');
    }
}
// locationwise fare


//FoodMenu 
if ($section == 'FoodMenu') {
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $ord = ' ORDER BY f.iFoodMenuId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY f.vMenu_".$default_lang." ASC";
      else
      $ord = " ORDER BY f.vMenu_".$default_lang." DESC";
    }
    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY c.vCompany ASC";
      else
      $ord = " ORDER BY c.vCompany DESC";
    }
    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY f.iDisplayOrder ASC";
      else
      $ord = " ORDER BY f.iDisplayOrder DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY MenuItems ASC";
      else
      $ord = " ORDER BY MenuItems DESC";
    }

    if($sortby == 5){
      if($order == 0)
      $ord = " ORDER BY f.eStatus ASC";
      else
      $ord = " ORDER BY f.eStatus DESC";
    }

    
    $ssql = '';
    if($keyword != '') {
        if($option != '') {
            $option_new = $option;
            if($eStatus != ''){
                $ssql.= " AND ".stripslashes($option_new)." LIKE '%".$generalobjAdmin->clean($keyword)."%' AND f.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
                } else {
                $ssql.= " AND ".stripslashes($option_new)." LIKE '%".$generalobjAdmin->clean($keyword)."%'";
                }
        } else {
          if($eStatus != ''){
            $ssql.= " AND (c.vCompany LIKE '%".$generalobjAdmin->clean($keyword)."%' OR f.vMenu_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%') AND f.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
          } else {
            $ssql.= " AND (c.vCompany LIKE '%".$generalobjAdmin->clean($keyword)."%' OR f.vMenu_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%')";
          }
        }
    } else if($eStatus != '' && $keyword == '') {
         $ssql.= " AND f.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }

        

    if($eStatus != '') { 
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND f.eStatus != 'Deleted'"; 
    }

    $sql = "SELECT f.vMenu_".$default_lang." as Title,c.vCompany as Store,f.iDisplayOrder as `Display Order`,(select count(iMenuItemId) from menu_items where iFoodMenuId = f.iFoodMenuId) as `Items`, f.eStatus as Status  FROM  `food_menu` as f LEFT JOIN company c ON f.iCompanyId = c.iCompanyId  WHERE 1=1 $eStatus_sql $ssql $ord";
    
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'Title'){
                    $val = $generalobjAdmin->clearName($val);
                }
                if($key == 'Store'){
                    $val = $generalobjAdmin->clearEmail($val);
                }
                if($key == 'Display Order'){
                    $val = $generalobjAdmin->clearPhone($val);
                }
                if($key == 'Status'){
                    $val = $generalobjAdmin->clearCmpName($val);
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array('Title','Store','Display Order','Items','Status');
        $result = $obj->ExecuteQuery($sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Item Categories");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Title') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Store') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Display Order') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == 'Items') {
                $pdf->Cell(30, 10, $column_heading, 1);
            }else if ($column_heading == 'Status') {
                $pdf->Cell(35, 10, $column_heading, 1);              
            } else {
                $pdf->Cell(20, 10, $column_heading, 1); 
            }         }
            $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if($column == 'Title'){
                    $values = $generalobjAdmin->clearName($key);
                }
                if($column == 'Store'){
                    $values = $generalobjAdmin->clearEmail($key);
                }
                if($column == 'Display Order'){
                    $values = $generalobjAdmin->clearPhone($key);
                }
                if($column == 'Status'){
                    $values = $generalobjAdmin->clearCmpName($key);
                }
                
                if ($column == 'Title') {
                    $pdf->Cell(50, 10, $values, 1 , 0 ,"1");
                } else if ($column == 'Store') {                            
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'Display Order') {                   
                    $pdf->Cell(30, 10, $values, 1); 
                }  else if ($column == 'Items') {                   
                    $pdf->Cell(30, 10, $values, 1); 
                } else if ($column == 'Status') {
                   $pdf->Cell(35, 10, $values, 1);   
                } else {
                    $pdf->Cell(20, 10, $key, 1);
                } 
            }
        }
        $pdf->Output('D');
        }
}
//FoodMenu

//MenuItems
if ($section == 'MenuItems') {
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $ord = ' ORDER BY mi.iMenuItemId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY mi.vItemType_".$default_lang." ASC";
      else
      $ord = " ORDER BY mi.vItemType_".$default_lang." DESC";
    }
    if($sortby == 2){
      if($order == 0)
      $ord = " ORDER BY c.vCompany ASC";
      else
      $ord = " ORDER BY c.vCompany DESC";
    }
    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY f.vMenu_".$default_lang." ASC";
      else
      $ord = " ORDER BY f.vMenu_".$default_lang." DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY mi.iDisplayOrder ASC";
      else
      $ord = " ORDER BY mi.iDisplayOrder DESC";
    }

    if($sortby == 4){
      if($order == 0)
      $ord = " ORDER BY mi.eStatus ASC";
      else
      $ord = " ORDER BY mi.eStatus DESC";
    }
    
    $ssql = '';
    if($keyword != '') {
        if($option != '') {
            if($eStatus != ''){
                $ssql.= " AND ".stripslashes($option)." LIKE '%".$generalobjAdmin->clean($keyword)."%' AND mi.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
                } else {
                $ssql.= " AND ".stripslashes($option)." LIKE '%".$generalobjAdmin->clean($keyword)."%'";
                }
        } else {
          if($eStatus != ''){
            $ssql.= " AND (f.vMenu_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%' OR c.vCompany LIKE '%".$generalobjAdmin->clean($keyword)."%' OR mi.vItemType_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%') AND mi.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
          } else {
            $ssql.= " AND (f.vMenu_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%' OR c.vCompany LIKE '%".$generalobjAdmin->clean($keyword)."%' OR mi.vItemType_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%')";
          }
        }
    } else if($eStatus != '' && $keyword == '') {
         $ssql.= " AND mi.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
    }


    if($eStatus != '') { 
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND mi.eStatus != 'Deleted'"; 
    }

    $sql = "SELECT mi.vItemType_".$default_lang." as Item, f.vMenu_".$default_lang." as Category, c.vCompany as Store, mi.iDisplayOrder as `Display Order`,mi.eStatus as Status  FROM  `menu_items` as mi LEFT JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId LEFT JOIN company as c on c.iCompanyId=f.iCompanyId WHERE 1=1 $eStatus_sql $ssql $ord";
    
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        
        foreach($result as $value){
            foreach($value as $key=>$val) {
                if($key == 'Item'){
                    $val = $generalobjAdmin->clearName($val);
                }
                if($key == 'Category'){
                    $val = $generalobjAdmin->clearName($val);
                }
                if($key == 'Store'){
                    $val = $generalobjAdmin->clearEmail($val);
                }
                if($key == 'Display Order'){
                    $val = $generalobjAdmin->clearPhone($val);
                }
                if($key == 'Status'){
                    $val = $generalobjAdmin->clearCmpName($val);
                }
                echo $val."\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array('Item','Category','Store','Display Order','Status');
        $result = $obj->ExecuteQuery($sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        
        $pdf->SetFont('Arial', 'b', 15);
        $pdf->Cell(100, 16, "Items");
        $pdf->Ln();
        $pdf->SetFont('Arial', 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Item') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Category') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Store') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Display Order') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(20, 10, $column_heading, 1);              
            } else {
                $pdf->Cell(20, 10, $column_heading, 1); 
            }         }
            $pdf->SetFont('Arial', '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if($column == 'Item'){
                    $values = $generalobjAdmin->clearName($key);
                }
                if($column == 'Category'){
                    $values = $generalobjAdmin->clearName($key);
                }
                if($column == 'Store'){
                    $values = $generalobjAdmin->clearEmail($key);
                }
                if($column == 'Display Order'){
                    $values = $generalobjAdmin->clearPhone($key);
                }
                if($column == 'Status'){
                    $values = $generalobjAdmin->clearCmpName($key);
                }
                
                if ($column == 'Item') {
                    $pdf->Cell(50, 10, $values, 1);
                } else if ($column == 'Category') {                            
                    $pdf->Cell(50, 10, $values, 1);
                } else if ($column == 'Store') {                            
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'Display Order') {                   
                    $pdf->Cell(25, 10, $values, 1); 
                } else if ($column == 'Status') {
                   $pdf->Cell(20, 10, $values, 1);   
                } else {
                    $pdf->Cell(20, 10, $key, 1);
                } 
            }
        }
        $pdf->Output('D');
        }
}
//MenuItems
?>