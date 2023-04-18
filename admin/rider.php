<?php
include_once('../common.php');
ini_set('max_execution_time', '900');

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL); 
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$script = 'Rider';
 global $lang_label, $obj, $tconfig, $generalobj;
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
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
//End Sorting
$rdr_ssql = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}
// Start Search Parameters

$option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
$searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

$ssql = '';
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
$showxlsfile = '';
$userremains = 'no';
$ssql1 = "AND (vEmail != '' OR vPhone != '')";
if ($_POST['action'] == "uploadfile") {
	require_once($tconfig["tsite_libraries_v"].'phpexcel/Classes/PHPExcel.php');
	
	$csv_folder = $tconfig["tpanel_path"].'admin/uploadcsv';
	if(!is_dir($csv_folder)){
		mkdir($csv_folder, 0777);
	}  
	$file_object = $_FILES['userfile']['tmp_name'];  
	$file_name   = $_FILES['userfile']['name'];
	
	$ext = pathinfo($file_name, PATHINFO_EXTENSION);
	$filename = uniqid().'.'.$ext;
	$uploaddir = $csv_folder.'/'.$filename;
	$file = move_uploaded_file ($file_object,$uploaddir);
	$userfile = $file[0];
	
	$excelReader = PHPExcel_IOFactory::createReaderForFile($uploaddir);
	$excelObj = $excelReader->load($uploaddir);
	$worksheet = $excelObj->getSheet(0);
	$lastRow = $worksheet->getHighestRow();
	
	$server = '23.188.0.162';
	//$conn = sqlsrv_connect($server, array("database"=>'test', "UID"=>'anviamdb',"PWD"=>'anviam123'));
	$conn = sqlsrv_connect($server, array("database"=>'FrontOff', "UID"=>'HurleyAccess',"PWD"=>'yQs#M6=V'));

	if (!$conn) {
		//echo '<pre>';die( print_r( sqlsrv_errors(), true));
	}
	$sql = "SELECT * from [dbo].[LOYAL_CUST]";
	//$sql = "SELECT * from [dbo].[LOYAL_CUST]";
	$stmt = sqlsrv_query( $conn, $sql);
	if( $stmt === false ) {
		//echo '<pre>'; die( print_r( sqlsrv_errors(), true));
	}
	$emailsOfSqlsrv = array();
	while($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)){
		if(trim($row['EMAIL_ADDR']) != ''){
			array_push($emailsOfSqlsrv,trim($row['EMAIL_ADDR']));
		}
	}
	//echo '<pre>';print_r($emailsOfSqlsrv);
	//die;
	$dataArray = array(
					array(
						'First Name',
						'Last Name',
						'Email',
						'Phone Number',
						'Password',
						'Card Number',
						'Loyalty Points',
						'Redeem Value',
						'Address',
					)
				);
	
	for ($row = 1; $row <= $lastRow; $row++) {
		if($row != 1){
			$tblname 		= "register_user";
			$firstname 		= trim($worksheet->getCell('A'.$row)->getValue());
			$lastname 		= trim($worksheet->getCell('B'.$row)->getValue());
			$email 			= trim($worksheet->getCell('C'.$row)->getValue());
			$phonenumber 	= trim($worksheet->getCell('D'.$row)->getValue());
			$password 		= trim($worksheet->getCell('E'.$row)->getValue());
			$card_number 	= trim($worksheet->getCell('F'.$row)->getValue());
			$address 		= trim($worksheet->getCell('G'.$row)->getValue());
			$points 		= trim($worksheet->getCell('H'.$row)->getValue());
			//echo $firstname.'----'.$lastname.'----'.$email.'----'.$phonenumber.'-----'.$password;die;
			if($email != ''){
				if(in_array($email,$emailsOfSqlsrv)){
					$sql = "SELECT * from [dbo].[LOYAL_CUST] where EMAIL_ADDR='".$email."'";
					$stmt = sqlsrv_query( $conn, $sql);
					if( $stmt === false ) {
						//$returnArr['Action'] = "0";
						//$returnArr['message'] = "LBL_SERVER_COMM_ERROR";
						//echo json_encode($returnArr);
						//exit;
					}

					$row1 = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
					$card_number = $redeem_points_value = '';
					$loyality_points = 0;
					if($row1){
						$card_number = $row1['LOYAL_CUS_NUM'];
						$loyality_points = $row1['PNTS'];
						$redeem_points_value = $row1['RDMPT_VAL'];
					}
						
						
					$sql = "SELECT * FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0)";
					$check_passenger = $obj->MySQLSelect($sql);
					if(count($check_passenger) == 0) {
						$eRefType = "Rider";
						$vImage = 'vImgName';
						$iMemberId = 'iUserId';
						$Password_passenger 					= $generalobj->encrypt_bycrypt($password);
						
						$Data_passenger['vName'] 				= $firstname;
						$Data_passenger['vLastName'] 			= $lastname;
						$Data_passenger['vEmail'] 				= $email;
						$Data_passenger['vPhone'] 				= $phonenumber;
						$Data_passenger['vPassword'] 			= $Password_passenger;
						$Data_passenger['dRefDate'] 			= @date('Y-m-d H:i:s');
						$Data_passenger['tRegistrationDate'] 	= @date('Y-m-d H:i:s');
						$Data_passenger['eStatus'] 				= 'Active';
						$Data_passenger['eEmailVerified'] 		= 'Yes';
						$Data_passenger['ePhoneVerified'] 		= 'Yes';
						$Data_passenger['csv_otp'] 				= 0;
						$Data_passenger['card_number'] 			= $card_number;
						$Data_passenger['tDestinationAddress'] 	= $address;
						$Data_passenger['loyality_points'] 		= $loyality_points;
						$Data_passenger['redeem_points_value'] 	= $redeem_points_value;
						$Data_passenger['vRefCode'] 			= $generalobj->ganaraterefercode($eRefType);
						$id = $obj->MySQLQueryPerform($tblname, $Data_passenger, 'insert');
						/*
						$maildata['EMAIL'] = $email;
						$maildata['NAME'] = $firstname.' '.$lastname;
						$maildata['PASSWORD'] = "OTP: " . $password;
						$maildata['SOCIALNOTES'] = '';
						$generalobj->send_email_user("MEMBER_REGISTRATION_USER_CSV", $maildata);*/
					}
				}else{
					sqlsrv_configure('WarningsReturnAsErrors',0);
					$Password_passenger = $generalobj->encrypt_bycrypt($password);
					$sql = "SELECT * from [dbo].[LOYAL_CUST] where EMAIL_ADDR='".$email."'";
					$stmt = sqlsrv_query( $conn, $sql);
					if( $stmt === false ) {
						//$returnArr['Action'] = "0";
						//$returnArr['message'] = "LBL_SERVER_COMM_ERROR";
						//echo json_encode($returnArr);
						//exit;
					}
					if($card_number == ''){
						$LOYAL_CUS_NUM 	= getNewCardNumber($conn);
					}else{
						$pad_length     = 20;
						$pad_char     	= 0;
						$LOYAL_CUS_NUM 	= str_pad($card_number, $pad_length, $pad_char, STR_PAD_LEFT);
					}
					$registration_points = $generalobj->getConfigurations("configurations","REGISTRATION_POINTS_HURLEYS");
					$PNTS 			= $registration_points;
					$RDMPT_VAL 		= 0.00;
					$PND_RDMPT_VAL	= 0.00;
					$UPD_DATE 		= Date('Y-m-d H:i:s');
					$TYPE 			= 3;
					$CUST_NAME 		= $firstname.' '.$lastname;
					$SEGMENT_1 		= 0;
					$SEGMENT_2 		= 0;
					$SEGMENT_3 		= 0;
					$SEGMENT_4 		= 0;
					$SEGMENT_5 		= 0;
					$SEGMENT_6 		= 0;
					$SEGMENT_7 		= 0;
					$SEGMENT_8 		= 0;
					$SEGMENT_9 		= 0;
					$SEGMENT_10 	= 0;
					$SEGMENT_11 	= 0;
					$SEGMENT_12 	= 0;
					$SEGMENT_13 	= 0;
					$SEGMENT_14 	= 0;
					$SEGMENT_15 	= 0;
					$SEGMENT_16 	= 0;
					$MSG_NO 		= 0;
					$SCHM_ID 		= 1;
					$SAVING 		= 0.00;
					$STATUS 		= 0;
					$MAIN_SORT 		= 0;
					$SECOND_SORT 	= 0;
					$SEND_EMAIL_FG 	= 0;
					$EMAIL_ADDR 	= $email;
					$SECONDARY_ID 	= $phonenumber;
					$RCPT_PRN_OPT 	= 0;
					$row1 = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
					if(!$row1){
						
						
						$params = array($LOYAL_CUS_NUM,$PNTS,$RDMPT_VAL,$PND_RDMPT_VAL,$UPD_DATE,$TYPE,$CUST_NAME,$SEGMENT_1,$SEGMENT_2,$SEGMENT_3,$SEGMENT_4,$SEGMENT_5,$SEGMENT_6,$SEGMENT_7,$SEGMENT_8,$SEGMENT_9,$SEGMENT_10,$SEGMENT_11,$SEGMENT_12,$SEGMENT_13,$SEGMENT_14,$SEGMENT_15,$SEGMENT_16,$MSG_NO,$SCHM_ID,$SAVING,$STATUS,$MAIN_SORT,$SECOND_SORT,$SEND_EMAIL_FG,$EMAIL_ADDR,$SECONDARY_ID,$RCPT_PRN_OPT);
						$vals = '';
						foreach($params as $val){
							$vals .= '?,';
						}
						
						$values = trim($vals,',');
						$sql = "INSERT INTO [dbo].[LOYAL_CUST] VALUES ($values)";
						$stmt = sqlsrv_query( $conn, $sql,$params);
						if( $stmt === false ) {//echo '<pre>'; die( print_r( sqlsrv_errors(), true));
							//$returnArr['Action'] = "0";
							//$returnArr['message'] = "LBL_SERVER_COMM_ERROR";
							//echo json_encode($returnArr);
							//exit;
						}
					}
					//sqlsrv_close( $conn );
					$sql = "SELECT * FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0)";
					$check_passenger = $obj->MySQLSelect($sql);
					$iMemberId = 'iUserId';
					if(count($check_passenger) == 0) {
						$eRefType = "Rider";
						$vImage = 'vImgName';
						
						
						$Data_passenger 						= array();
						$Data_passenger['vName'] 				= $firstname;
						$Data_passenger['vLastName'] 			= $lastname;
						$Data_passenger['vEmail'] 				= $email;
						$Data_passenger['vPhone'] 				= $phonenumber;
						$Data_passenger['vPassword'] 			= $Password_passenger;
						$Data_passenger['dRefDate'] 			= @date('Y-m-d H:i:s');
						$Data_passenger['tRegistrationDate'] 	= @date('Y-m-d H:i:s');
						$Data_passenger['eStatus'] 				= 'Active';
						$Data_passenger['eEmailVerified'] 		= 'Yes';
						$Data_passenger['ePhoneVerified'] 		= 'Yes';
						$Data_passenger['csv_otp'] 				= 0;
						$Data_passenger['card_number'] 			= $LOYAL_CUS_NUM;
						$Data_passenger['tDestinationAddress'] 	= $address;
						$Data_passenger['loyality_points'] 		= $PNTS;
						$Data_passenger['redeem_points_value'] 	= $RDMPT_VAL;
						$Data_passenger['vRefCode'] 			= $generalobj->ganaraterefercode($eRefType);
						$id = $obj->MySQLQueryPerform($tblname, $Data_passenger, 'insert');
						/*
						$maildata['EMAIL'] = $email;
						$maildata['NAME'] = $firstname.' '.$lastname;
						$maildata['PASSWORD'] = "OTP: " . $password;
						$maildata['SOCIALNOTES'] = '';
						$generalobj->send_email_user("MEMBER_REGISTRATION_USER_CSV", $maildata);*/
					}/*else{
						$Data_passenger = array();
						$Data_passenger['card_number'] 			= $LOYAL_CUS_NUM;
						$Data_passenger['loyality_points'] 		= $PNTS;
						$Data_passenger['redeem_points_value'] 	= $RDMPT_VAL;
						$where = " $iMemberId = '".$GeneralMemberId."'";
						$Update_Member_id = $obj->MySQLQueryPerform($tblname, $Data_passenger, 'update',$where);
					}*/
					
					$userremains = 'yes';
					$arr = array($firstname,$lastname,$email,$phonenumber,$password,$LOYAL_CUS_NUM,$PNTS,$RDMPT_VAL,$address);
					array_push($dataArray,$arr);
				}
			}
		}
	}//echo '<pre>';print_r($dataArray);
	sqlsrv_close( $conn );
	$_SESSION['success'] = '1';
	$_SESSION['var_msg'] = 'Users imported successfully.'; 
	if($userremains == 'yes'){
		$doc = new PHPExcel();
		// set active sheet 
		$doc->setActiveSheetIndex(0);		 
		// read data to active sheet
		$doc->getActiveSheet()->fromArray($dataArray);		 
		//save our workbook as this file name
		$filename = 'users_'.time().'.xls';

		//print_r($_FILES);die;
		$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
		//force user to download the Excel file without writing it to server's HD
		$savepath 		= $tconfig['tsite_upload_images_passenger_path'].'/'.$filename;
		$showxlsfile 	= $tconfig["tsite_upload_images_passenger"].'/'.$filename;//echo $showxlsfile;die;
		$objWriter->save($savepath);
		
		$_SESSION['var_msg'] .= ' To download list of users which we did not find in our system please click <a href = "'.$showxlsfile.'" ><strong>link</strong></a> to download file';//die;
	}
     
	header("Location:".$tconfig["tsite_url_main_admin"]."rider.php");
	exit;
}
if ($_POST['action'] == "addmoney") {
    $eUserType = $_REQUEST['eUserType'];
	 $iUserId = $_REQUEST['iUserId-id'];   
    $iBalance = $_REQUEST['iBalance'];
    $eFor = $_REQUEST['eFor'];
    $eType = $_REQUEST['eType'];
    $iTripId = 0;
    $tDescription = '#LBL_AMOUNT_CREDIT#';  
    $ePaymentStatus = 'Unsettelled';
    $dDate = Date('Y-m-d H:i:s');
 
    $generalobj->InsertIntoUserWallet($iUserId, $eUserType, $iBalance, $eType, $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate);	
	$_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RIDER_NAME_TXT_ADMIN"].' in add balance successfully';   
    header("Location:".$tconfig["tsite_url_main_admin"]."rider.php"); exit;
   //exit;
}

// End Search Parameters

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if($eStatus != '') { 
    $estatusquery = "";
} else {
    $estatusquery = " AND eStatus != 'Deleted'";
}
$sql = "SELECT COUNT(iUserId) AS Total FROM register_user WHERE 1=1 $estatusquery $ssql $ssql1 $rdr_ssql";

$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
if(!empty($eStatus)) { 
    $esql = "";
} else {
    $esql = " AND eStatus != 'Deleted'";
}
    $sql = "SELECT iUserId,CONCAT(vName,' ',vLastName) AS name, vEmail, vPhone AS mobile,vPhoneCode,tRegistrationDate,eStatus FROM register_user WHERE 1=1 $esql $ssql $ssql1 $rdr_ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = count($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key=>$val)
{
    if($key != "tpages" && $key != 'page')
    $var_filter.= "&$key=".stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;

function getNewCardNumber($conn){
	global $obj;
	$pad_length     = 20;
	$pad_char     	= 0;
	/*
	$card_number 	= date('ymdHis');
	
	$GeneratedCardnumber = substr($card_number, 0, -1);
	$LOYAL_CUS_NUM 	= str_pad($GeneratedCardnumber, $pad_length, $pad_char, STR_PAD_LEFT);*/
	
	$cardnumberid = $LOYAL_CUS_NUM = $GeneratedCardnumber = '';
	$query = "SELECT card_number,id FROM `card_number`";
    $result = $obj->MySQLSelect($query);
	if(count($result) > 0){
		$cardnumberid = $result[0]['id'];
		$GeneratedCardnumber = $result[0]['card_number'] + 1;
		$LOYAL_CUS_NUM 	= str_pad($GeneratedCardnumber, $pad_length, $pad_char, STR_PAD_LEFT);
	}
	
	$sql = "SELECT * from [dbo].[LOYAL_CUST] where LOYAL_CUS_NUM='".$LOYAL_CUS_NUM."'";
	$stmt1 = sqlsrv_query( $conn, $sql);
	if( $stmt1 === false ) {
		//$returnArr['Action'] = "0";
		//$returnArr['message'] = "LBL_SERVER_COMM_ERROR";
		//echo json_encode($returnArr);
		//exit;
		//echo '<pre>'; die( print_r( sqlsrv_errors(), true));
	}

	$row1 = sqlsrv_fetch_array( $stmt1, SQLSRV_FETCH_ASSOC);
	if($row1){
		getNewCardNumber($conn);
	}else{
		if($cardnumberid != ''){
			$where = " id = '".$cardnumberid."'";
			$data = array('card_number'=>$GeneratedCardnumber);
			$Update_card = $obj->MySQLQueryPerform('card_number', $data, 'update',$where);
		}
		return $LOYAL_CUS_NUM;
	}
}
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> | <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN'];?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php');?>
    </head>
    <!-- END  HEAD-->
    
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>

            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2><?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN'];?></h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                              <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="10%" class=" padding-right10"><select name="option" id="option" class="form-control">
                                          <option value="">All</option>
                                          <option  value="RiderName" <?php if ($option == "RiderName") { echo "selected"; } ?> >Name</option>
                                          <option value="vEmail" <?php if ($option == 'vEmail') {echo "selected"; } ?> >E-mail</option>
                                          <option value="MobileNumber" <?php if ($option == 'MobileNumber') {echo "selected"; } ?> >Mobile</option>
                                          <!-- <option value="eStatus" <?php if ($option == 'eStatus') {echo "selected"; } ?> >Status</option> -->
                                    </select>
                                    </td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eStatus" id="estatus_value" class="form-control">
                                            <option value="" >Select Status</option>
                                            <option value='Active' <?php if ($eStatus == 'Active') { echo "selected"; } ?> >Active</option>
                                            <option value="Inactive" <?php if ($eStatus == 'Inactive') {echo "selected"; } ?> >Inactive</option>
                                            <option value="Deleted" <?php if ($eStatus == 'Deleted') {echo "selected"; } ?> >Delete</option>
                                        </select>
                                    </td>
                                    <td>
                                      <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                      <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='rider.php'"/>
                                    </td>
									<td><a class="add-btn" href="javascript:void(0);" style="text-align: center;" data-toggle="modal" data-target="#importusers">Import <?=$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];?>s</a></td>
                                    <td width="8%"><a class="add-btn" href="rider_action.php" style="text-align: center;">Add <?=$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];?></a></td>
                                </tr>
                              </tbody>
                        </table>
                        
                      </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                    <div class="changeStatus col-lg-12 option-box-left">
                                    <span class="col-lg-2 new-select001">
                                            <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <option value='Active' <?php if ($option == 'Active') { echo "selected"; } ?> >Make Active</option>
                                                    <option value="Inactive" <?php if ($option == 'Inactive') {echo "selected"; } ?> >Make Inactive</option>
                                                    <?php if($eStatus != 'Deleted') { ?>
                                                    <option value="Deleted" <?php if ($option == 'Delete') {echo "selected"; } ?> >Make Delete</option>
                                                    <?php } ?>
                                            </select>
                                    </span>
                                    </div>
                                    <?php if(!empty($data_drv)){ ?> 
                                    <div class="panel-heading">
                                        <form name="_export_form" id="_export_form" method="post" >
                                            <button type="button" onClick="showExportTypes('rider')" >Export</button>
                                        </form>
                                   </div>
                                   <?php } ?>
                                    </div>
                                    <div style="clear:both;"></div>
                                        <div class="table-responsive">
                                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>
                                                        <th width="22%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> Name <?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="22%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)">Email <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="22%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)">Sign Up Date <?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="22%">Mobile</th>
														 <!--<th  class="align-Left">Wallet Balance</th>-->
                                                        <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php if($sortby == '4'){ echo $order; }else { ?>0<?php } ?>)">Status <?php if ($sortby == 4) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="8%" align="center" style="text-align:center;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
													<?php if(!empty($data_drv)) { 
                                                        for($i=0;$i<count($data_drv);$i++) {?>
														<tr class="gradeA">
														<td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iUserId']; ?>" />&nbsp;</td>
															<td>
																<? if($APP_TYPE == "Ride"){?>
																	<a href="javascript:void(0);" onClick="show_rider_details('<?=$data_drv[$i]['iUserId'];?>')" style="text-decoration: underline;"><?= $generalobjAdmin->clearName($data_drv[$i]['name']); ?></a>
																<? }else{?>
																	<?= $generalobjAdmin->clearName($data_drv[$i]['name']); ?>
																<? }?>
															</td>
															<td><? echo $generalobjAdmin->clearEmail($data_drv[$i]['vEmail']); ?></td>
															<td data-order="<?=$data_drv[$i]['iUserId']; ?>"><? echo $generalobjAdmin->DateTime($data_drv[$i]['tRegistrationDate']) ?></td>
															<td class="center">
                                                                <?php if(!empty($data_drv[$i]['mobile'])){?>
                                                                (+<?= $data_drv[$i]['vPhoneCode']?>) <?= $generalobjAdmin->clearPhone($data_drv[$i]['mobile']);?>
                                                                <?php } ?>           
                                                                </td>
															<!--<td>
																
															<?php 
															/*$user_available_balance = $generalobj->get_user_available_balance($data_drv[$i]['iUserId'], "Rider");
															 
															
															if($data_drv[$i]['eStatus'] != "Deleted"){ 
																echo $generalobj->trip_currency($user_available_balance);
																?>
															<button type="button" onClick="Add_money_driver('<?=$data_drv[$i]['iUserId'];?>')" class="btn btn-success btn-xs">Add Balance</button>
															<?php }else{
																echo $generalobj->trip_currency($user_available_balance);
																}*/ ?>
															
															</td>-->
															<td width="10%" align="center">
																<? if($data_drv[$i]['eStatus'] == 'Active') {
																	$dis_img = "img/active-icon.png";
																	}else if($data_drv[$i]['eStatus'] == 'Inactive'){
																	$dis_img = "img/inactive-icon.png";
																	}else if($data_drv[$i]['eStatus'] == 'Deleted'){
																	$dis_img = "img/delete-icon.png";
																}?>
																<img src="<?=$dis_img;?>" alt="<?=$data_drv[$i]['eStatus']?>" data-toggle="tooltip" title="<?php echo $data_drv[$i]['eStatus']; ?>" >
															</td>
															
															<td align="center" style="text-align:center;" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions for-five openPops_<?= $data_drv[$i]['iUserId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="rider_action.php?id=<?= $data_drv[$i]['iUserId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a></li>
                                                                            
                                                                            <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iUserId']; ?>','Inactive')"  data-toggle="tooltip" title="Make Active">
                                                                                <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                            </a></li>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iUserId']; ?>','Active')" data-toggle="tooltip" title="Make Inactive">
                                                                                <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >	
                                                                            </a></li>
                                                                            <?php if($eStatus != 'Deleted') { ?>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['iUserId']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                                <img src="img/delete-icon.png" alt="Delete" >
                                                                            </a></li>
                                                                            <?php } ?>
                                                                            <!-- <?php  if (SITE_TYPE == 'Demo') { ?>
																			<li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="resetTripStatus('<?php echo $data_drv[$i]['iUserId']; ?>')"  data-toggle="tooltip" title="Reset">
                                                                                <img src="img/reset-icon.png" alt="Reset">
                                                                            </a></li><?php } ?> -->
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </td>
															
														</tr>
													<? } 
                                                    } else { ?>
                                                    <tr class="gradeA">
                                                        <td colspan="8"> No Records Found.</td>
                                                    </tr>
                                                    <?php } ?>
													
												</tbody>
                                                </table>
                                            </form>
                                            <?php include('pagination_n.php'); ?>
                                    </div>
                                </div> <!--TABLE-END-->
                            </div>
                        </div>
                    <div class="admin-notes">
                            <h4>Notes:</h4>
                            <ul>
                                    <li>
                                            <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN'];?> module will list all <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN'];?> on this page.
                                    </li>
                                    <li>
                                            Administrator can Activate / Deactivate / Delete any <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN'];?>
                                    </li>
                                    <li>
                                            Administrator can export data in XLS or PDF format.
                                    </li>
                            </ul>
                    </div>
                    </div>
                </div>
                <!--END PAGE CONTENT -->
            </div>
            <!--END MAIN WRAPPER -->
            
<form name="pageForm" id="pageForm" action="action/rider.php" method="post" >
<input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
<input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
<input type="hidden" name="iUserId" id="iMainId01" value="" >
<input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
<input type="hidden" name="status" id="status01" value="" >
<input type="hidden" name="statusVal" id="statusVal" value="" >
<input type="hidden" name="option" value="<?php echo $option; ?>" >
<input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
<input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
<input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
<input type="hidden" name="method" id="method" value="" >
</form>

			<div  class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
					<div class="modal-dialog" >
						<div class="modal-content">
							<div class="modal-header">
								<h4>
								<!--<i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>-->
								<i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i>
								Rider Details
								<button type="button" class="close" data-dismiss="modal">x</button>
								</h4>
							</div>
							<div class="modal-body" style="max-height: 450px;overflow: auto;">
								<div id="imageIcons">
								  <div align="center">                                                                       
									<img src="default.gif"><br/>                                                            
									<span>Retrieving details,please Wait...</span>                       
								  </div>    
								 </div>
								 <div id="rider_detail" ></div>
							</div>
						</div>
					</div>
				</div>
				<!--<div  class="modal fade" id="rider_add_wallet_money" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
					<div class="modal-dialog" >
						<div class="modal-content nimot-class">
							<div class="modal-header">
								<h4><i style="margin:2px 5px 0 2px;" class= "fa fa-google-wallet"></i>Add Balance
								<button type="button" class="close" data-dismiss="modal">x</button>
								</h4>
							</div>
							<form class="form-horizontal" id="add_money_frm" method="POST" enctype="multipart/form-data" 	action="" name="add_money_frm">
								<input type="hidden" id="action" name="action" value="addmoney">
								<input type="hidden"  name="eTransRequest" id="eTransRequest" value="">
								<input type="hidden"  name="eType" id="eType" value="Credit">
								<input type="hidden"  name="eFor" id="eFor" value="Deposit">
								<input type="hidden"  name="iUserId-id" id="iRider-Id" value="">							
								<input type="hidden"  name="eUserType" id="eUserType" value="Rider">			
								<div class="col-lg-12">
									<div class="input-group input-append" >
										<h5><?= $langage_lbl['LBL_ADD_WALLET_DESC1_TXT']; ?></h5>
                                        <div class="ddtt">
										<h4><?= $langage_lbl['LBL_ENTER_AMOUNT']; ?></h4>
										<input type="text" name="iBalance" id="iBalance" class="form-control iBalance add-ibalance" onKeyup="checkzero(this.value);">
										</div>
										<div id="iLimitmsg"></div>										
									</div>
								</div>
								<div class="nimot-class-but">
									<input type="button" onClick="check_add_money();" class="save"  id="add_money" name="Save" value="Save">
									<button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Close</button>
								</div>
							</form>
                            <div style="clear:both;"></div>
						</div>
					</div>
					
				</div>-->
<div class="modal" id='importusers' tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import <?=$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];?>s</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" style="top: 20px;position: absolute;right: 20px;">x</span>
        </button>
      </div>
	  <form method="post" action='' id = 'importauserscsv'  enctype="multipart/form-data">
		  <div class="modal-body">
			
			<div class="form-group">
				<label for="recipient-name" class="col-form-label">Upload xls file:</label>
				<input type="file" class="form-control csvinput" name='userfile' required>
			</div>
			<div class="form-group">
				<input type='hidden' name='action' value='uploadfile' />
			</div>
			 
		  </div>
		  <div class="modal-footer">
			<button type="submit" class="btn btn-primary importButton">Import</button>
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		  </div>
	  </form>
    </div>
  </div>
</div>
<?php include_once('footer.php'); ?>
<script>
//$(document).on('click','.importButton',function() {
//	$('#importusers').submit(function( event ) { alert('adf'); });
//});
$('#importauserscsv').validate({
  rules: {
    userfile: {
      required: true,
      extension: "xlsx|xls|csv"
    }
  }
});
    /*$(document).ready(function() {
        $('#eStatus_options').hide(); 
        $('#option').each(function(){
            if (this.value == 'eStatus') {
                $('#eStatus_options').show(); 
                $('.searchform').hide(); 
            }
        });
    });
    $(function() {
        $('#option').change(function(){
            if($('#option').val() == 'eStatus') {
                $('#eStatus_options').show();
                $("input[name=keyword]").val("");
                $('.searchform').hide(); 
            } else {
                $('#eStatus_options').hide();
                $("#estatus_value").val("");
                $('.searchform').show();
            } 
        });
    });*/

    $("#setAllCheck").on('click',function(){
        if($(this).prop("checked")) {
            jQuery("#_list_form input[type=checkbox]").each(function() {
                if($(this).attr('disabled') != 'disabled'){
                    this.checked = 'true';
                }
            });
        }else {
            jQuery("#_list_form input[type=checkbox]").each(function() {
                this.checked = '';
            });
        }
    });
    
    $("#Search").on('click', function(){
        //$('html').addClass('loading');
        var action = $("#_list_form").attr('action');
        //alert(action);
        var formValus = $("#frmsearch").serialize();
        window.location.href = action+"?"+formValus;
    });
    
    $('.entypo-export').click(function(e){
         e.stopPropagation();
         var $this = $(this).parent().find('div');
         $(".openHoverAction-class div").not($this).removeClass('active');
         $this.toggleClass('active');
    });
    
    $(document).on("click", function(e) {
        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
          $(".show-moreOptions").removeClass("active");
        }
    });
	
	function show_rider_details(userid){
				$("#rider_detail").html('');
				$("#imageIcons").show();
				$("#detail_modal").modal('show');
				
				if(userid != ""){
					var request = $.ajax({
							type: "POST",
							url: "ajax_rider_details.php",
							data: "iUserId="+userid,
							datatype: "html",
							success: function(data){
								$("#rider_detail").html(data);
								$("#imageIcons").hide();
							}
						});
				}
}
			
function Add_money_driver(riderid){		
	//alert(riderid);
	$("#rider_add_wallet_money").modal('show');
	$(".add-ibalance").val("");
	if(riderid != ""){				
	var riderid = $('#iRider-Id').val(riderid);
	 
	}			
}
function changeOrder(iAdminId) {
		$('#is_dltSngl_modal').modal('show');
		$(".action_modal_submit").unbind().click(function () {
			var action = $("#pageForm").attr('action');
			var page = $("#pageId").val();
			$("#pageId01").val(page);
			$("#iMainId01").val(iAdminId);
			$("#method").val('delete');
			var formValus = $("#pageForm").serialize();
			window.location.href = action+"?"+formValus;   
		});
	}
	
	function check_add_money() {

	var iBalance = $(".add-ibalance").val();
	if (iBalance == '') {
		alert("Please Enter Amount");
		return false;
	} else if (iBalance == 0) {
		alert("You Can Not Enter Zero Number");
		return false;
	} else {
		$("#add_money").val('Please wait ...').attr('disabled','disabled');
		$('#add_money_frm').submit();
	}
}	                

$(".iBalance").keydown(function (e) {	
	if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
		(e.keyCode == 65 && e.ctrlKey === true) ||
		(e.keyCode == 67 && e.ctrlKey === true) ||
		(e.keyCode == 88 && e.ctrlKey === true) ||
		(e.keyCode >= 35 && e.keyCode <= 39)) {
			return;
	}
	if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
		e.preventDefault();
	}
});		
		
function checkzero(userlimit)
{		
    if(userlimit != ""){
        if (userlimit == 0)
        {       
            $('#iLimitmsg').html('<span class="red">You Can Not Enter Zero Number</span>');
        } else if(userlimit <= 0) {
          $('#iLimitmsg').html('<span class="red">You Can Not Enter Negative Number</span>');
      } else {
         $('#iLimitmsg').html('');
        } 
    } else{
         $('#iLimitmsg').html('');
    } 
                    
}
</script>
    </body>
    <!-- END BODY-->
</html>