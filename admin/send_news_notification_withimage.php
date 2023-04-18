<?php
   include_once('../common.php');
   include_once(TPATH_CLASS.'/class.general.php');
   include_once(TPATH_CLASS.'/configuration.php');
   include_once('../generalFunctions.php');
   date_default_timezone_set('America/Cayman');
    //ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
   function send_notification_fun($registation_ids_new,$deviceTokens_arr_ios,$message,$vTitle,$etype,$userType,$imagepush,$vexpireIn,$ipoints) {
       $message = stripslashes($message);
   		$alertMsg = $message;
                     
   		 /*echo "registation_ids_new => <pre>";
   		 print_r($registation_ids_new);
   		 echo "deviceTokens_arr_ios => <pre>";
   		 print_r($deviceTokens_arr_ios);
   		 exit;   */
   		if(!empty($registation_ids_new)){
   			$newArr = array();
   			$newArr = array_chunk($registation_ids_new, 999);
   			foreach($newArr as $newRegistration_ids){
   				$Rmessage         = array("message" => array('message'=>$message,"title" => $vTitle, 'etype'=>$etype,'image'=>$imagepush,'type'=>'image'));
   				$result = send_notification($newRegistration_ids, $Rmessage,0);
   			}
   		}
   		if(!empty($deviceTokens_arr_ios)){
   			$Rmessage         = array("message" => $message,"title" => $vTitle, 'etype'=>$etype,'image'=>$imagepush,'type'=>'image','vexpireIn'=>$vexpireIn,'ipoints'=>$ipoints,"apns"=>array("payload"=>array("aps"=>array("mutable-content"=>1)),"fcm_options"=>array("image"=>$imagepush)));
   			if($userType == "rider") {
   				$result = sendApplePushNotification(0,$deviceTokens_arr_ios,$Rmessage,$alertMsg,0,'admin');
   			}else if($userType == "company") {
   				$result = sendApplePushNotification(2,$deviceTokens_arr_ios,$Rmessage,$alertMsg,0,'admin');
   			}else {
   				$result = sendApplePushNotification(1,$deviceTokens_arr_ios,$Rmessage,$alertMsg,0,'admin');
   			}
   		}
   		$_SESSION['success'] = '1';
   		$_SESSION['var_msg'] = 'Push Notification sent successfully.';
   		header("location:news.php");
   		exit;
   }
   
   if (!isset($generalobjAdmin)) {
        require_once(TPATH_CLASS . "class.general_admin.php");
        $generalobjAdmin = new General_admin();
   }
   $generalobjAdmin->check_member_login();
   
   $csql = "select vCompany,iCompanyId from company where eStatus = 'Active' order by vCompany";
   $db_cmp_list = $obj->MySQLSelect($csql);
   
   $dsql = "select concat(vName,' ',vLastName) as DriverName,iDriverId from register_driver where eStatus = 'Active' order by vName";
   $db_drv_list = $obj->MySQLSelect($dsql);
   
   $rsql = "select concat(vName,' ',vLastName) as riderName,iUserId from register_user where eStatus = 'Active' order by vName";
   $db_rdr_list = $obj->MySQLSelect($rsql);
   
   
   $sql_cmp = "select vCompany,iCompanyId from company where eStatus = 'Active' AND `eLogout` = 'No' order by vCompany";
   $db_login_cmp_list = $obj->MySQLSelect($sql_cmp);
   
   $sql_drv = "select concat(vName,' ',vLastName) as DriverName,iDriverId from register_driver where eStatus = 'Active' AND `eLogout` = 'No' order by vName";
   $db_login_drv_list = $obj->MySQLSelect($sql_drv);
   
   $sql_rdr = "select concat(vName,' ',vLastName) as riderName,iUserId from register_user where eStatus = 'Active' AND `eLogout` = 'No' order by vName";
   $db_login_rdr_list = $obj->MySQLSelect($sql_rdr);
   
   
   $sql_inactive_cmp = "select vCompany,iCompanyId from company where eStatus = 'Inactive' order by vCompany";
   $db_inactive_cmp_list = $obj->MySQLSelect($sql_inactive_cmp);
   
   $sql_inactive_drv = "select concat(vName,' ',vLastName) as DriverName,iDriverId from register_driver where eStatus = 'Inactive' order by vName";
   $db_inactive_drv_list = $obj->MySQLSelect($sql_inactive_drv);
   
   $sql_inactive_rdr = "select concat(vName,' ',vLastName) as riderName,iUserId from register_user where eStatus = 'Inactive' order by vName";
   $db_inactive_rdr_list = $obj->MySQLSelect($sql_inactive_rdr);
   
   $tbl_name = 'pushnotification_log';
   $script = 'Push Notification Image';
   
   // set all variables with either post (when submit) either blank (when insert)
   $eUserType = isset($_POST['eUserType']) ? $_POST['eUserType'] : '';
   $vTitle = isset($_POST['vTitle']) ? $_POST['vTitle'] : '';
   $etype = isset($_POST['etype']) ? $_POST['etype'] : '';
   $iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
   $iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
   $iRiderId = isset($_POST['iRiderId']) ? $_POST['iRiderId'] : '';
   $tMessage = isset($_POST['tMessage']) ? $_POST['tMessage'] : '';
   $vexpireIn = isset($_POST['vexpireIn']) ? $_POST['vexpireIn'] : '';
   $ipoints = isset($_POST['ipoints']) ? $_POST['ipoints'] : '';
   $dExpiryDate = isset($_POST['dExpiryDate']) ? $_POST['dExpiryDate'] : '';
   $iPushnotificationId = isset($_POST['iPushnotificationId']) ? $_POST['iPushnotificationId'] : '';
   $dDate = date("Y-m-d H:i:s");
   $ipAddress = $_SERVER['REMOTE_HOST'];
   
   if (isset($_POST['submit'])) {
   		if(SITE_TYPE =='Demo'){
   			$_SESSION['success'] = 3;
   			$_SESSION['var_msg'] = "Sending push notification has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.";
   			header("Location:send_notifications_withimage.php");exit;
   		}

   		if($eUserType == 'driver'){
   			$set_table = 'register_driver';
   			$set_userId = 'iDriverId';
   			if(!empty($iDriverId)) {
   				$userArr = $iDriverId;
   			}else {
   				foreach($db_drv_list as $dbd) {
   					$userArr[] = $dbd['iDriverId'];
   				}
   			}
   		} else if($eUserType == 'company'){
   			$set_table = 'company';
   			$set_userId = 'iCompanyId';
   			if(!empty($iCompanyId)){
   				$userArr = $iCompanyId;
   			}else {
   				foreach($db_cmp_list as $dbr) {
   					$userArr[] = $dbr['iCompanyId'];
   				}
   			}
   		} else if($eUserType == 'rider'){
   			$set_table = 'register_user';
   			$set_userId = 'iUserId';
   			if(count(array_filter($iRiderId)) > 0){
   				$userArr = $iRiderId;
   			}else {
   				foreach($db_rdr_list as $dbr) {
   					$userArr[] = $dbr['iUserId'];
   				}
   			}
   		} else if($eUserType == 'logged_driver'){
   			$eUserType = 'driver';
   			$set_table = 'register_driver';
   			$set_userId = 'iDriverId';
   			if(!empty($iDriverId)) {
   				$userArr = $iDriverId;
   			}else {
   				foreach($db_login_drv_list as $dbd) {
   					$userArr[] = $dbd['iDriverId'];
   				}
   			}
   		} else if($eUserType == 'logged_company'){
   			$eUserType = 'company';
   			$set_table = 'company';
   			$set_userId = 'iCompanyId';
   			if(!empty($iCompanyId)) {
   				$userArr = $iCompanyId;
   			}else {
   				foreach($db_login_cmp_list as $dbd) {
   					$userArr[] = $dbd['iCompanyId'];
   				}
   			}
   		} else if($eUserType == 'logged_rider'){
   			$eUserType = 'rider';
   			$set_table = 'register_user';
   			$set_userId = 'iUserId';
   			if(count(array_filter($iRiderId)) > 0){
   				$userArr = $iRiderId;
   			}else {
   				foreach($db_login_rdr_list as $dbr) {
   					$userArr[] = $dbr['iUserId'];
   				}
   			}
   		} else if($eUserType == 'inactive_driver'){
   			$eUserType = 'driver';
   			$set_table = 'register_driver';
   			$set_userId = 'iDriverId';
   			if(!empty($iDriverId)) {
   				$userArr = $iDriverId;
   			}else {
   				foreach($db_inactive_drv_list as $dbd) {
   					$userArr[] = $dbd['iDriverId'];
   				}
   			}
   		} else if($eUserType == 'inactive_company'){
   			$eUserType = 'company';
   			$set_table = 'company';
   			$set_userId = 'iCompanyId';
   			if(!empty($iCompanyId)) {
   				$userArr = $iCompanyId;
   			}else {
   				foreach($db_inactive_cmp_list as $dbd) {
   					$userArr[] = $dbd['iCompanyId'];
   				}
   			}
   		} else if($eUserType == 'inactive_rider'){
   			$eUserType = 'rider';
   			$set_table = 'register_user';
   			$set_userId = 'iUserId';
   			if(count(array_filter($iRiderId)) > 0){
   				$userArr = $iRiderId;
   			}else {
   				foreach($db_inactive_rdr_list as $dbr) {
   					$userArr[] = $dbr['iUserId'];
   				}
   			}
   		} 
   		$deviceTokens_arr_ios = array();
   		$registation_ids_new = array();
   		$img1 = '';
   		if ($_FILES['file']['name'] != "") {
   			$image_object = $_FILES['file']['tmp_name'];
   			$image_name = $_FILES['file']['name'];
   			// Get Image Dimension
		    // $fileinfo = @getimagesize($_FILES["file"]["tmp_name"]);
		    // $width = $fileinfo[0];
		    // $height = $fileinfo[1];
   			// if ($width > "250" || $height > "800") {
		    //     $response = array(
		    //         "type" => "error",
		    //         "message" => "Image dimension should be within 300X200"
		    //     );
		    // } 
   			$img_path = $tconfig["tsite_upload_images_pushimage_path"];
   			$Photo_Gallery_folder = $img_path . '/' . $id . '/';
   			if (!is_dir($Photo_Gallery_folder)) {
   				mkdir($Photo_Gallery_folder, 0777);
   			}
   			$img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
   		}
   		$imagepush = $tconfig["tsite_url"]."webimages/upload/DefaultImg/offer_logo.png";
   		
   		
   		
   		if($img1 != ''){
   			$imagepush = $tconfig["tsite_upload_images_pushimage"].'/'.$img1;
   		}else{
   			if($iPushnotificationId != 0){
   				$sql = "SELECT iUserId,image FROM `pushnotification_log` where iPushnotificationId = $iPushnotificationId";
   				$datapush = $obj->MySQLSelect($sql);
   				$img1 = $datapush[0]['image'];
				$imagepush = $tconfig["tsite_upload_images_pushimage"].'/'.$img1;
   			}
   		}
   		//echo '<pre>';print_r($db_rdr_list);print_r($userArr);die;
   		//echo $tconfig["tsite_upload_images_pushimage"].'/'.$img1;die;
   		if(count(array_filter($iRiderId)) == 0){
   			if($iPushnotificationId == 0){
   				$update = 'INSERT INTO ';
   				$where  = '';
   			}else{
   				$update = 'UPDATE ';
   				$where  = ' where iPushnotificationId = '.$iPushnotificationId;
   			}
   			$q = $update;
   			if($img1 != ''){
   				$query = $q . " `" . $tbl_name . "` SET
   				`eUserType` = '" . $eUserType . "',
   				`vTitle` = '" . $vTitle . "',
   				`etype` = '" . $etype . "',
   				`iUserId` = '0',
   				`tMessage` = '" . $tMessage . "',
   				`vexpireIn` = '" . $vexpireIn . "',
   				`ipoints` = '" . $ipoints . "',
   				`dDateTime` = '" . $dDate . "',
   				`image` = '" . $img1 . "',
   				`dExpiryDate` = '" . $dExpiryDate . "',
   				`IP_ADDRESS` = '" . $ipAddress . "'".$where;
   			}else{
   				$query = $q . " `" . $tbl_name . "` SET
   				`eUserType` = '" . $eUserType . "',
   				`vTitle` = '" . $vTitle . "',
   				`etype` = '" . $etype . "',
   				`iUserId` = '0',
   				`tMessage` = '" . $tMessage . "',
   				`vexpireIn` = '" . $vexpireIn . "',
   				`ipoints` = '" . $ipoints . "',
   				`dDateTime` = '" . $dDate . "',
   				`dExpiryDate` = '" . $dExpiryDate . "',
   				`IP_ADDRESS` = '" . $ipAddress . "'".$where;
   			}
   			foreach($userArr as $usAr){
   				$gcmIds = get_value($set_table, 'eDeviceType,iGcmRegId', $set_userId,$usAr);
   				//print_r($gcmIds);die;
   				if($gcmIds[0]['iGcmRegId'] != '' && strlen($gcmIds[0]['iGcmRegId']) > 15){
   					if($gcmIds[0]['eDeviceType'] == 'Android') {
   						if(trim($gcmIds[0]['iGcmRegId']) != ''){
   							array_push($registation_ids_new, $gcmIds[0]['iGcmRegId']);
   						}
   					}else {
   						if(trim($gcmIds[0]['iGcmRegId']) != ''){
   							array_push($deviceTokens_arr_ios, $gcmIds[0]['iGcmRegId']);
   						}
   					}
   				}
   			}
   			$responce = $obj->sql_query($query);
   		}else{
   			foreach($userArr as $usAr){
   				if($iPushnotificationId == 0){
   					$update = 'INSERT INTO ';
   					$where  = '';
   				}else{
   					$update = 'UPDATE ';
   					$where  = ' where iPushnotificationId = '.$iPushnotificationId;
   				}
   				
   				$sql = "SELECT iUserId FROM `pushnotification_log` where iPushnotificationId = $iPushnotificationId and iUserId = $usAr";
   				$data = $obj->MySQLSelect($sql);
   				if(count($data) == 0){
   					$update = 'INSERT INTO ';
   					$where  = '';
   				}
   				//send_notification_fun($usAr);
   				
   				$q = $update;
   				if(count(array_filter($iRiderId))){
   					$q = $update."  ";
   					if($img1 != ''){
   						$query = $q . " `" . $tbl_name . "` SET
   						`eUserType` = '" . $eUserType . "',
   						`vTitle` = '" . $vTitle . "',
   						`etype` = '" . $etype . "',
   						`iUserId` = '" . $usAr . "',
   						`tMessage` = '" . $tMessage . "',
   						`vexpireIn` = '" . $vexpireIn . "',
   						`ipoints` = '" . $ipoints . "',
   						`dDateTime` = '" . $dDate . "',
   						`image` = '" . $img1 . "',
   						`dExpiryDate` = '" . $dExpiryDate . "',
   						`IP_ADDRESS` = '" . $ipAddress . "'".$where;
   					}else{
   						$query = $q . " `" . $tbl_name . "` SET
   						`eUserType` = '" . $eUserType . "',
   						`vTitle` = '" . $vTitle . "',
   						`etype` = '" . $etype . "',
   						`iUserId` = '" . $usAr . "',
   						`tMessage` = '" . $tMessage . "',
   						`vexpireIn` = '" . $vexpireIn . "',
   						`ipoints` = '" . $ipoints . "',
   						`dDateTime` = '" . $dDate . "',
   						`dExpiryDate` = '" . $dExpiryDate . "',
   						`IP_ADDRESS` = '" . $ipAddress . "'".$where;
   					}
   					$responce = $obj->sql_query($query);
   				}
   				
   				$gcmIds = get_value($set_table, 'eDeviceType,iGcmRegId', $set_userId,$usAr);
   				//print_r($gcmIds);die;
   				if($gcmIds[0]['iGcmRegId'] != '' && strlen($gcmIds[0]['iGcmRegId']) > 15){
   					if($gcmIds[0]['eDeviceType'] == 'Android') {
   						if(trim($gcmIds[0]['iGcmRegId']) != ''){
   							array_push($registation_ids_new, $gcmIds[0]['iGcmRegId']);
   						}
   					}else {
   						if(trim($gcmIds[0]['iGcmRegId']) != ''){
   							array_push($deviceTokens_arr_ios, $gcmIds[0]['iGcmRegId']);
   						}
   					}
   				}
   			}
   		}
   		//$tMessage=str_replace('\r\n','\n',$tMessage);
   		$deviceTokens_arr_ios = array_unique($deviceTokens_arr_ios);
   		$registation_ids_new = array_unique($registation_ids_new);
   		$tMessage = trim(stripslashes($obj->SqlEscapeString($tMessage)));
   		$tMessage = str_replace(array('\r', '\n'), array(chr(13), chr(10)), $tMessage);
   		// echo "<br>";
   		// $tMessage = nl2br($tMessage,false); die;                                            
   		send_notification_fun($registation_ids_new,$deviceTokens_arr_ios,$tMessage,$vTitle,$etype,$eUserType,$imagepush,$vexpireIn,$ipoints);
   }
   
   if(isset($_GET['id']) && $_GET['id'] != ''){
   	$id = base64_decode($_GET['id']);
   	$sql = "SELECT * FROM `pushnotification_log` where status = 'active' and iPushnotificationId = $id";
   	$db_customnotif = $obj->MySQLSelect($sql);//echo '<pre>';print_r($db_customnotif);die;
   	$captionoffer = 'Edit';
   	$vTitle = $db_customnotif[0]['vTitle'];
   	$etype = $db_customnotif[0]['etype'];
   	$tMessage = $db_customnotif[0]['tMessage'];
   	$image = $db_customnotif[0]['image'];
   	$ipoints = $db_customnotif[0]['ipoints'];
   	$vexpireIn = $db_customnotif[0]['vexpireIn'];
   	$dExpiryDate = $db_customnotif[0]['dExpiryDate'];
   	$iPushnotificationId = $db_customnotif[0]['iPushnotificationId'];
   	$iUserId = $db_customnotif[0]['iUserId'];
   }else{
   	$captionoffer = 'Add';
   	$vTitle = '';
   	$etype = '';
   	$tMessage = '';
   	$image = '';
   	$ipoints = '';
   	$vexpireIn = '';
   	$dExpiryDate = '';
   	$iPushnotificationId = 0;
   	$iUserId = 0;
   }
 
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8" />
      <title><?=$SITE_NAME?> | News </title>
      <meta content="width=device-width, initial-scale=1.0" name="viewport" />
      <?
         include_once('global_files.php');
         ?>
      <!-- On OFF switch -->
      <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
      <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
   </head>
   <!-- END  HEAD-->
   <!-- BEGIN BODY-->
   <body class="padTop53 " >
      <!-- MAIN WRAPPER -->
      <div id="wrap">
         <?
            include_once('header.php');
            include_once('left_menu.php');
            ?>
         <!--PAGE CONTENT -->
         <div id="content">
            <div class="inner">
               <div class="row">
                  <div class="col-lg-12">
                     <h2><?php echo $captionoffer; ?> News </h2>
                     <a href="news.php" class="add-btn">Back</a>
                  </div>
               </div>
               <hr />
               <div class="body-div">
                  <div class="form-group">
                     <?php include('valid_msg.php'); ?>
                     <form id="_notification_form" name="_notification_form" method="post" action="javascript:void(0);" enctype ="multipart/form-data">
                        <input type='hidden' value='<?php echo $iPushnotificationId; ?>' name = 'iPushnotificationId' id='iPushnotificationId'/>
                        
                     
                     
                        <div class="row">
                           <div class="col-lg-12">
                              <label>Image (2000 height x 1400 width)</label>
                           </div>
						   <?php 
						   $required = '';
                              if($image == ''){
								   $required = 'required="required"';
							  }
							  ?>
                           <div class="col-lg-6">
                              <input type='file' name ='file' id='imagefile'  class="selectFile" <?php echo $required; ?> accept="image/*"/>
                              <span class="error_img btn-danger" > </span>
                           </div>
                           <?php 
                              if($image != ''){
                              $img_path = $tconfig["tsite_upload_images_pushimage"];
                              $Photo_Gallery_folder = $img_path . '/'.$image;
                              ?>
                           <div class="col-lg-12" style='text-align: left;float: left;padding: 15px;'>
                              <img src = '<?php echo $Photo_Gallery_folder; ?>' width='200px;'/>
                           </div>
                           <?php 
                              }
                              ?>
                        </div>
                        <div class="row">
                           <div class="col-lg-12">
                              <label>Title <span class="red"> *</span></label>
                           </div>
                           <div class="col-lg-6">
                              <input type="text" class="form-control" name ='vTitle' id="vTitle" value='<?php echo $vTitle; ?>' required> 
                              <input type="hidden" class="form-control" name ='etype' id="etype" value="News">
                              <input type="hidden" class="form-control" (400 height x 250 width)name ='eUserType' id="eUserType" value="rider"><input type="hidden" class="form-control" name ='iRiderId[]' id="iRiderId" value="">
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-lg-12">
                              <input type="submit" class="btn btn-default" name="submit" id="submit" onClick="submit_form();" value="Send News" >
                           </div>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
         <!--END PAGE CONTENT -->
      </div>
      <!--END MAIN WRAPPER -->
      <?php include_once('footer.php'); ?>
      <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
      <link rel="stylesheet" href="css/select2/select2.min.css" type="text/css" >
      <style>
         .error {
         color:red;
         font-weight: normal;
         }
         .select2-container--default .select2-search--inline .select2-search__field{
         width:500px !important;
         }
      </style>
      <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
      <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
      <script type="text/javascript" src="js/plugins/select2.min.js"></script>
      <script>
         $(function () {
             $('#datepicker').datepicker({  
                 minDate:new Date()
              });
         });
         
        
         
         	function submit_form(){
         		var joinTxt = '';
         		if( $("#_notification_form").valid() ) {
         			var userType = $("#eUserType").val();
         			if(userType == 'rider'){
         				if($("#iRiderId").val() == '' || $("#iRiderId").val() == null){
         					joinTxt = 'All <?php echo $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>';
         				}else {
         					var len = $('#iRiderId option:selected').length;
         					joinTxt = 'Selected '+len+' <?php echo $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>(s)';
         				}
         			} else if(userType == 'company') {
         				if($("#iCompanyId").val() == '' || $("#iCompanyId").val() == null){
         					joinTxt = '<?php echo $langage_lbl_admin['LBL_COMPANY_NAME_PUSH_NOTIFICATION'] ?>';
         				}else {
         					var len = $('#iCompanyId option:selected').length;
         					joinTxt = 'Selected '+len+' <?php echo $langage_lbl_admin['LBL_COMPANY_NAME_PUSH_NOTIFICATION'] ?>(s)';
         				}
         			} else if(userType == 'driver') {
         				if($("#iDriverId").val() == '' || $("#iDriverId").val() == null){
         					joinTxt = '<?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>';
         				}else {
         					var len = $('#iDriverId option:selected').length;
         					joinTxt = 'Selected '+len+' <?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>(s)';
         				}
         			} else if(userType == 'logged_company') {
         				if($("#login_iCompanyId").val() == '' || $("#login_iCompanyId").val() == null){
         					joinTxt = 'All Logged In <?php echo $langage_lbl_admin['LBL_COMPANY_NAME_PUSH_NOTIFICATION'] ?>';
         				}else {
         					var len = $('#login_iCompanyId option:selected').length;
         					joinTxt = 'Selected '+len+' Logged In <?php echo $langage_lbl_admin['LBL_COMPANY_NAME_PUSH_NOTIFICATION'] ?>(s)';
         				}
         			} else if(userType == 'logged_driver') {
         				if($("#login_iDriverId").val() == '' || $("#login_iDriverId").val() == null){
         					joinTxt = 'All Logged In <?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>';
         				}else {
         					var len = $('#login_iDriverId option:selected').length;
         					joinTxt = 'Selected '+len+' Logged In <?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>(s)';
         				}
         			} else if(userType == 'logged_rider') {
         				if($("#login_iRiderId").val() == '' || $("#login_iRiderId").val() == null){
         					joinTxt = 'All Logged In <?php echo $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>';
         				}else {
         					var len = $('#login_iRiderId option:selected').length;
         					joinTxt = 'Selected '+len+' Logged In <?php echo $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>(s)';
         				}
         			} else if(userType == 'inactive_company') {
         				if($("#inactive_iCompanyId").val() == '' || $("#inactive_iCompanyId").val() == null){
         					joinTxt = 'All Inactive <?php echo $langage_lbl_admin['LBL_COMPANY_NAME_PUSH_NOTIFICATION'] ?>';
         				}else {
         					var len = $('#inactive_iCompanyId option:selected').length;
         					joinTxt = 'Selected '+len+' Inactive <?php echo $langage_lbl_admin['LBL_COMPANY_NAME_PUSH_NOTIFICATION'] ?>(s)';
         				}
         			} else if(userType == 'inactive_driver') {
         				if($("#inactive_iDriverId").val() == '' || $("#inactive_iDriverId").val() == null){
         					joinTxt = 'All Inactive <?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>';
         				}else {
         					var len = $('#inactive_iDriverId option:selected').length;
         					joinTxt = 'Selected '+len+' Inactive <?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>(s)';
         				}
         			} else if(userType == 'inactive_rider') {
         				if($("#inactive_iRiderId").val() == '' || $("#inactive_iRiderId").val() == null){
         					joinTxt = 'All Inactive <?php echo $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>';
         				}else {
         					var len = $('#inactive_iRiderId option:selected').length;
         					joinTxt = 'Selected '+len+' Inactive <?php echo $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>(s)';
         				}
         			}
                  $("#_notification_form").attr('action','');
         			$("#_notification_form").submit();
         			// if(confirm("Confirm to send news to "+joinTxt+"?")){
         			// 	$("#_notification_form").attr('action','');
         			// 	$("#_notification_form").submit();
         			// }else {
         				
         			// }
         		}
         	}
         	
         	
         	$(function () {
         	  $("select.filter-by-text").each(function(){
         		  $(this).select2({
         				placeholder: $(this).attr('data-text'),
         				allowClear: true
         		  }); //theme: 'classic'
         		});
         	});
         	
         	function showUsers(userType) {
         		if(userType == 'driver'){
         			$("#driverRw").show();
         			$("#companyRw").hide();
         			$("#riderRw").hide();
         			$("#logindriverRw").hide();
         			$("#logincompanyRw").hide();
         			$("#loginriderRw").hide();
         			$("#inactive_driverRw").hide();
         			$("#inactive_companyRw").hide();
         			$("#inactive_riderRw").hide();
         		} else if(userType == 'company') {
         			$("#companyRw").show();
         			$("#riderRw").hide();
         			$("#driverRw").hide();
         			$("#logincompanyRw").hide();
         			$("#logindriverRw").hide();
         			$("#loginriderRw").hide();
         			$("#inactive_driverRw").hide();
         			$("#inactive_companyRw").hide();
         			$("#inactive_riderRw").hide();
         		} else if(userType == 'rider') {
         			$("#riderRw").show();
         			$("#companyRw").hide();
         			$("#driverRw").hide();
         			$("#logincompanyRw").hide();
         			$("#logindriverRw").hide();
         			$("#loginriderRw").hide();
         			$("#inactive_driverRw").hide();
         			$("#inactive_companyRw").hide();
         			$("#inactive_riderRw").hide();
         		}  else if(userType == 'logged_company') {
         			$("#logincompanyRw").show();
         			$("#companyRw").hide();
         			$("#riderRw").hide();
         			$("#driverRw").hide();
         			$("#loginriderRw").hide();
         			$("#logindriverRw").hide();
         			$("#inactive_driverRw").hide();
         			$("#inactive_companyRw").hide();
         			$("#inactive_riderRw").hide();
         		} else if(userType == 'logged_driver') {
         			$("#logindriverRw").show();
         			$("#companyRw").hide();
         			$("#riderRw").hide();
         			$("#driverRw").hide();
         			$("#loginriderRw").hide();
         			$("#logincompanyRw").hide();
         			$("#inactive_driverRw").hide();
         			$("#inactive_companyRw").hide();
         			$("#inactive_riderRw").hide();
         		} else if(userType == 'logged_rider') {
         			$("#loginriderRw").show();
         			$("#riderRw").hide();
         			$("#driverRw").hide();
         			$("#companyRw").hide();
         			$("#logindriverRw").hide();
         			$("#logincompanyRw").hide();
         			$("#inactive_driverRw").hide();
         			$("#inactive_companyRw").hide();
         			$("#inactive_riderRw").hide();
         		} else if(userType == 'inactive_company') {
         			$("#inactive_companyRw").show();
         			$("#companyRw").hide();
         			$("#riderRw").hide();
         			$("#driverRw").hide();
         			$("#logindriverRw").hide();
         			$("#logincompanyRw").hide();
         			$("#loginriderRw").hide();
         			$("#inactive_riderRw").hide();
         			$("#inactive_driverRw").hide();
         		}  else if(userType == 'inactive_driver') {
         			$("#inactive_driverRw").show();
         			$("#companyRw").hide();
         			$("#riderRw").hide();
         			$("#driverRw").hide();
         			$("#logindriverRw").hide();
         			$("#logincompanyRw").hide();
         			$("#loginriderRw").hide();
         			$("#inactive_riderRw").hide();
         			$("#inactive_companyRw").hide();
         		} else if(userType == 'inactive_rider') {
         			$("#inactive_riderRw").show();
         			$("#loginriderRw").hide();
         			$("#riderRw").hide();
         			$("#companyRw").hide();
         			$("#driverRw").hide();
         			$("#logindriverRw").hide();
         			$("#logincompanyRw").hide();
         			$("#inactive_driverRw").hide();
         			$("#inactive_companyRw").hide();
         		}
         	}
         	

/*
			   $(function () {
                $("#imagefile").change(function (e) {
                    //Get reference of FileUpload.
                    var fileUpload = $("#imagefile")[0];
             
                    //Check whether the file is valid Image.
                    var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(.jpg|.png|.gif|.jpeg)$");
                    if (regex.test(fileUpload.value.toLowerCase())) {
                        //Check whether HTML5 is supported.
                        if (typeof (fileUpload.files) != "undefined") {
                            //Initiate the FileReader object.
                            var reader = new FileReader();
                            //Read the contents of Image File.
                            reader.readAsDataURL(fileUpload.files[0]);
                            reader.onload = function (e) {
                                //Initiate the JavaScript Image object.
                                var image = new Image();
                                //Set the Base64 string return from FileReader as source.
                                image.src = e.target.result;
                                image.onload = function () {
                                    //Determine the Height and Width.
                                    var height = this.height;
                                    var width = this.width;
                                    if (height > 400 || width > 250) {
                                        //alert("Height and Width must not exceed 800px * 250px");
                                        $('.error_img').fadeIn().html("Height and Width must not exceed 400px * 250px");
                                          setTimeout(function() {
                                             $('.error_img').fadeOut("slow");
                                          }, 2000 );
                                      
                                        $("#imagefile").val('');
                                        return false;

                                       $("#_notification_form").attr('action','');
                     				          $("#_notification_form").submit();
                                    }
                                    
                                     
                                    //alert("Uploaded image has valid Height and Width.");
                                    return true;
                                };
                            }
                        } else {
                           $('.error_img').fadeIn().html("This browser does not support HTML5.");
                                          setTimeout(function() {
                                             $('.error_img').fadeOut("slow");
                                          }, 2000 );
                          
                           
                            $("#imagefile").val('');
                            return false;
                            $("#_notification_form").attr('action','');
                     				$("#_notification_form").submit();
                        }
                    } else {
                      $('.error_img').fadeIn().html("Please select a valid Image file");
                                          setTimeout(function() {
                                             $('.error_img').fadeOut("slow");
                                          }, 2000 );
                    
                        
                        $("#imagefile").val('');
                        return false;
                        $("#_notification_form").attr('action','');
                     		$("#_notification_form").submit();
                    }
                });
});*/
      </script>
   </body>
   <!-- END BODY-->
</html>