<?php  
	include_once('common.php');
  $act = $_REQUEST['act'];
  $UserType = $_REQUEST['UserType'];
  $iMemberId = $_REQUEST['iMemberId']; 
	
  if($UserType == "Passenger"){
			$tblname = "register_user";
			$fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName, vLang';
			$condfield = 'iUserId';
	}else{
			$tblname = "register_driver";
			$fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName, vLang';
			$condfield = 'iDriverId';
	}
    
  
	$sql = "SELECT * FROM $tblname WHERE vEmailVarificationCode = '".$act."' AND $condfield = '".$iMemberId."'";
	$db_rec = $obj->MySQLSelect($sql);
	
	if(count($db_rec) > 0){
		if($db_rec[0]['eEmailVerified'] == "No"){
			$Data['eEmailVerified'] = "Yes";
  		$where = " ".$condfield." = '".$iMemberId."'";
  		$res = $obj->MySQLQueryPerform($tblname,$Data,'update',$where);
      $msg = $langage_lbl['LBL_EMAIL_VERIFY_SUCC'];
			//header("Location: ".$tconfig["tsite_url"]."index.php?file=m-verification&var_msg=".$msg."&msg_code=1");
			//exit;  
		}else{
			$msg = $langage_lbl['LBL_ALREADY_VERIFY'];
			//header("Location: ".$tconfig["tsite_url"]."index.php?file=m-verification&var_msg=".$msg."&msg_code=1");
			//exit;
		}
	}else{
		 $msg = $langage_lbl['LBL_ERROR_VERIFY_EMAIL1'];
     //$msg = LBL_ERROR_VERIFY_EMAIL1;
		//header("Location: ".$tconfig["tsite_url"]."index.php?file=m-verification&var_msg=".$msg."&msg_code=0");
		//exit;    
	}
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>
<?=$meta['meta_title'];?>
</title>
<meta name="keywords" value="<?=$meta['meta_keyword'];?>"/>
<meta name="description" value="<?=$meta['meta_desc'];?>"/>
<!-- Default Top Script and css -->
<?php include_once("top/top_script.php");?>
<!-- End: Default Top Script and css-->
</head>
<body>
<div id="main-uber-page">
  <!-- Left Menu -->
  <?php include_once("top/left_menu.php");?>
  <!-- End: Left Menu-->
  <!-- home page -->
  <!-- Top Menu -->
  <?php include_once("top/header_topbar.php");?>
  <!-- End: Top Menu-->
  <!-- contact page-->
  <div class="page-contant">
    <div class="page-contant-inner">
      <h2 class="header-page trip-detail">
        <?=$langage_lbl['LBL_VERIFIED_EMAIL_TXT'];?>
      </h2>
      <!-- trips detail page -->
      <div class="static-page">
        <?=$msg;?>
      </div>
    </div>
  </div>
  <!-- home page end-->
  <!-- footer part -->
  <?php include_once('footer/footer_home.php');?>
  <!-- End:contact page-->
  <div style="clear:both;"></div>
</div>
<!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<!-- End: Footer Script -->
</body>
</html>