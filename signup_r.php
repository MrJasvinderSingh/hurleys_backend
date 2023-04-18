<?php include_once("common.php");
$POST_CAPTCHA = $_POST['POST_CAPTCHA'];
$SESS_CAPTCHA = $_SESSION['SESS_CAPTCHA'];

//echo "<pre>";print_r($_REQUEST);exit;
if($POST_CAPTCHA == $SESS_CAPTCHA)
{
	if($_POST) {
		$Data = array();	
		$table_name="company";
		$msg= $generalobj->checkDuplicateFront('vEmail', 'company' , Array('vEmail'),$tconfig["tsite_url"]."sign-up-restaurant.php?error=1&var_msg=Email already Exists", "Email already Exists","" ,"");

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
		$Data['vContactName'] = $_POST['vContactName'];
		$Data['iServiceId'] = $_POST['iServiceId'];

		//$cuisineId = isset($_POST['cuisineId'])?$_POST['cuisineId']:'';

		$table='company';
		$user_type='company';
	
	
		if(SITE_TYPE=='Demo') {
			$Data['eStatus'] = 'Active';
		}
		$id = $obj->MySQLQueryPerform($table,$Data,'insert');

        /*foreach ($cuisineId as $key => $value) {
            $cusdata['iCompanyId'] = $id;
            $cusdata['cuisineId'] = $value;
            $cusine_id = $obj->MySQLQueryPerform('company_cuisine',$cusdata,'insert');
        }*/
		
		if($id != "") {
			$_SESSION['sess_iUserId'] = $id;
			$_SESSION['sess_iCompanyId'] = $id;
			$_SESSION["sess_vName"] = $Data['vCompany'];
			$_SESSION["sess_company"] = $Data['vCompany'];
			$_SESSION["sess_vEmail"] = $Data['vEmail'];
			$_SESSION["sess_user"] =$user_type;
			$_SESSION["sess_new"]=1;
			$_SESSION["sess_from"] = "web";

			$maildata['EMAIL'] = $_SESSION["sess_vEmail"];
			$maildata['NAME'] = $_SESSION["sess_vName"];
			$maildata['PASSWORD'] = $langage_lbl['LBL_PASSWORD'].": ". $_REQUEST['vPassword'];
			$maildata['SOCIALNOTES'] ='';
			$generalobj->send_email_user("COMPANY_REGISTRATION_USER",$maildata);
			$generalobj->send_email_user("COMPANY_REGISTRATION_ADMIN",$maildata);
			header("Location:dashboard.php?first=yes");
			exit;
		}
	}
} else {
	$_SESSION['postDetail'] = $_REQUEST;
	header("Location:".$tconfig["tsite_url"]."sign-up-restaurant.php?error=1&var_msg=Captcha did not match.");
	exit;
}
?>
