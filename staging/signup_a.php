<?php include_once("common.php");
$POST_CAPTCHA = $_POST['POST_CAPTCHA'];
$SESS_CAPTCHA = $_SESSION['SESS_CAPTCHA'];

if($POST_CAPTCHA == $SESS_CAPTCHA)
{
	if($_POST) {
		$table_name="register_driver";
		$msg= $generalobj->checkDuplicateFront('vEmail', 'register_driver' , Array('vEmail'),$tconfig["tsite_url"]."sign-up.php?error=1&var_msg=Email already Exists", "Email already Exists","" ,"");

		$eReftype = "Driver";
		$Data['vRefCode'] = $generalobj->ganaraterefercode($eReftype);
		$Data['iRefUserId'] = $_POST['iRefUserId'];
		$Data['eRefType'] = $_POST['eRefType']; 
		$Data['dRefDate']=Date('Y-m-d H:i:s');
		$Data['vName'] = $_POST['vFirstName'];
		$Data['vLastName'] = $_POST['vLastName'];
		$Data['vCadress2'] = $_POST['vCadress2'];
		$Data['vBackCheck'] = $_POST['vBackCheck'];
		$Data['vFathersName'] = $_POST['vFather'];
		$Data['vInviteCode'] = $_POST['vInviteCode'];
		$Data['vLang'] = $_SESSION['sess_lang'];
		$Data['vPassword'] = $generalobj->encrypt_bycrypt($_REQUEST['vPassword']);
		$Data['vEmail'] = $_POST['vEmail'];
		$Data['vPhone'] = $_POST['vPhone'];
		$Data['vCaddress'] = $_POST['vCaddress'];
		$Data['vCity'] = $_POST['vCity'];
		$Data['vCountry'] = $_POST['vCountry'];
		$Data['vState'] = $_POST['vState'];
		$Data['vZip'] = $_POST['vZip'];
		$Data['vCode'] = $_POST['vCode'];
		$Data['vCompany'] = $_POST['vCompany'];
		$Data['tRegistrationDate']=Date('Y-m-d H:i:s');
		$Data['vCurrencyDriver'] = $_POST['vCurrencyDriver'];
		$Data['eGender'] = $_POST['eGender'];
		$Data['iCompanyId'] = 1;
	
		$table='register_driver';
		$user_type='driver';
	
		if(SITE_TYPE=='Demo')
		{
			$Data['eStatus'] = 'Active';
		}

		$id = $obj->MySQLQueryPerform($table,$Data,'insert');

		if($id != "")
		{
			$_SESSION['sess_iUserId'] = $id;
			$_SESSION['sess_iCompanyId'] = 1;
			$_SESSION["sess_vName"] = $Data['vName'].' '.$Data['vLastName'];
			$_SESSION["sess_vCurrency"]= $Data['vCurrencyDriver'];			
			$_SESSION["sess_company"] = $Data['vCompany'];
			$_SESSION["sess_vEmail"] = $Data['vEmail'];
			$_SESSION["sess_user"] =$user_type;
			$_SESSION["sess_new"]=1;
			$_SESSION["sess_from"] = "web";

			$maildata['EMAIL'] = $_SESSION["sess_vEmail"];
			$maildata['NAME'] = $_SESSION["sess_vName"];
			$maildata['PASSWORD'] = $langage_lbl['LBL_PASSWORD'].": ". $_REQUEST['vPassword'];
			$maildata['SOCIALNOTES'] ='';

			$generalobj->send_email_user("DRIVER_REGISTRATION_USER",$maildata);
			$generalobj->send_email_user("DRIVER_REGISTRATION_ADMIN",$maildata);
			
			header("Location:profile.php?first=yes");
			exit;		
			
		}
	}
} else {
	$_SESSION['postDetail'] = $_REQUEST;
	header("Location:".$tconfig["tsite_url"]."sign-up.php?error=1&var_msg=Captcha did not match.");
	exit;
}
?>
