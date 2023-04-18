<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
require_once(TPATH_CLASS . "class.general_admin.php");
$generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();



function updateallrestaurantitems($iCompanyId, $cmenupremium, $rmenupremium)
{
global $obj;

$FoodMenudata = '';
$FoodMenuQ = "SELECT `iFoodMenuId` FROM `food_menu` WHERE `iCompanyId` = $iCompanyId;";
$FoodMenus = $obj->MySQLSelect($FoodMenuQ);

if(count($FoodMenus) > 0)
{
foreach($FoodMenus as $FoodMenu)
{
$FoodMenudata .= $FoodMenu['iFoodMenuId'].',';
}
}

echo $FoodMenudata = substr($FoodMenudata, 0, -1);

$MenuItemIds = '';
$MenuItemsQ = "SELECT `iMenuItemId`,`fbPrice`,`fPrice` FROM `menu_items` WHERE `iFoodMenuId` IN ($FoodMenudata);";
$MenuItems = $obj->MySQLSelect($MenuItemsQ);
 $ChowcallPre = 0;
if($cmenupremium != 0)
{
$ChowcallPre = $cmenupremium / 100;
}
$RestaurantPre = 0;
if($rmenupremium !=0)
{
$RestaurantPre = $rmenupremium / 100;
}

if(count($MenuItems) > 1)
{

foreach($MenuItems as $MenuItem)
{
$MenuItemIds .= $MenuItem['iMenuItemId'].',';
// $where = " `iCompanyId` = '" . $id . "'";
//       $company_id =$obj->MySQLQueryPerform($tbl_name,$CompanyData,'update',$where);
$MenuUpdateQuery = "UPDATE `menu_items` set `fPrice` = ROUND( (`fbPrice` + (`fbPrice` * $ChowcallPre) + (`fbPrice` * $RestaurantPre)), 2), `cpremium` = ROUND((`fbPrice` * $ChowcallPre) , 2), `rpremium` = ROUND((`fbPrice` * $RestaurantPre) , 2) WHERE`iMenuItemId` = $MenuItem[iMenuItemId]";
$ExceuteUpdateQuery = $obj->sql_query($MenuUpdateQuery);
}

}


$MenuItemIds = substr($MenuItemIds, 0, -1);

$MenuOPtionsQ = "SELECT `iOptionId` FROM `menuitem_options` WHERE `iMenuItemId` IN ($MenuItemIds);";
$MenuOPtions = $obj->MySQLSelect($MenuOPtionsQ);
if(count($MenuOPtions) > 1)
{
foreach($MenuOPtions as $MenuOPtion)
{

$MenuOptionUpdateQuery = "UPDATE `menuitem_options` set `fPrice` = ROUND( (`fbPrice` + (`fbPrice` * $ChowcallPre) + (`fbPrice` * $RestaurantPre)), 2), `cpremium` = ROUND((`fbPrice` * $ChowcallPre) , 2), `rpremium` = ROUND((`fbPrice` * $RestaurantPre) , 2) WHERE`iOptionId` = $MenuOPtion[iOptionId]";
$ExceuteUpdateQueryMO = $obj->sql_query($MenuOptionUpdateQuery);
}

}


}



function checkchildrestaurant($id = null)
{
    global $obj;
    $CheckChildquery = "SELECT vCompany from company WHERE parent_iCompanyId = '$id' AND iCompanyId != '$id' AND `estatus` != 'Deleted';";
    $Haschild = 0;
    $CheckChilds = $obj->MySQLSelect($CheckChildquery);
    if(count($CheckChilds) > 0)
    {
        $Haschild = 1;
    }
    
    return $Haschild;
}

$resbasesQ= "SELECT `iLocationId`,`vLocationName` FROM `baselocations` WHERE `eStatus` = 'Active' ;";
$resbaseDatas =   $obj->MySQLSelect($resbasesQ);
$resbases = array();
$allbase = '';
foreach($resbaseDatas as $resbaseData)
{
    $resbases[$resbaseData['iLocationId']] = $resbaseData['vLocationName'];
    $allbase .= $resbaseData['iLocationId'].',';
    
}
$allbase = substr($allbase, 0, -1);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'company';
$script = 'Company';

$sql = "select iCountryId,vCountry,vCountryCode from country ORDER BY vCountry ASC ";
$db_country = $obj->MySQLSelect($sql);

$sql = "select vCode,vTitle from language_master where eStatus = 'Active' order by vTitle asc";
$db_lang = $obj->MySQLSelect($sql);


// set all variables with either post (when submit) either blank (when insert)
$type = isset($_POST['type'])?$_POST['type']:0;
$parent_iCompanyId = isset($_POST['parent_iCompanyId'])?$_POST['parent_iCompanyId']:0;
$vCompany = stripslashes(isset($_POST['vCompany'])?$_POST['vCompany']:'');
$iServiceIdNew = isset($_POST['iServiceId'])?$_POST['iServiceId']:'1';
$vEmail = isset($_POST['vEmail'])?$_POST['vEmail']:'';
$vPassword = isset($_POST['vPassword'])?$_POST['vPassword']:'';
$vPass = ($vPassword != "") ? $generalobj->encrypt_bycrypt($vPassword) : '';
$vContactName = isset($_POST['vContactName'])?$_POST['vContactName']:'';
$vCode = isset($_POST['vCode'])?$_POST['vCode']:'';
$vPhone = isset($_POST['vPhone'])?$_POST['vPhone']:'';
$vCountry = isset($_POST['vCountry'])?$_POST['vCountry']:$DEFAULT_COUNTRY_CODE_WEB;
$vState = isset($_POST['vState'])?$_POST['vState']:'';
$vCity = isset($_POST['vCity'])?$_POST['vCity']:'';
$vRestuarantLocation = isset($_POST['vRestuarantLocation'])?$_POST['vRestuarantLocation']:'';
$vRestuarantLocationLat = isset($_POST['vRestuarantLocationLat'])?$_POST['vRestuarantLocationLat']:'';
$vRestuarantLocationLong = isset($_POST['vRestuarantLocationLong'])?$_POST['vRestuarantLocationLong']:'';
$vCaddress = isset($_POST['vCaddress'])?$_POST['vCaddress']:'';
$vZip = isset($_POST['vZip'])?$_POST['vZip']:'';
$vLang = isset($_POST['vLang'])?$_POST['vLang']:'';
$vAcctHolderName = isset($_POST['vAcctHolderName'])?$_POST['vAcctHolderName']:'';
$vAcctNo = isset($_POST['vAcctNo'])?$_POST['vAcctNo']:'';
$vBankName = isset($_POST['vBankName'])?$_POST['vBankName']:'';
$vBankLocation = isset($_POST['vBankLocation'])?$_POST['vBankLocation']:'';
$vSwiftCode = isset($_POST['vSwiftCode'])?$_POST['vSwiftCode']:'';

$cuisineId = isset($_POST['cuisineId'])?$_POST['cuisineId']:'';

$vFromMonFriTimeSlot1 = isset($_POST['vFromMonFriTimeSlot1'])?$_POST['vFromMonFriTimeSlot1']:'';
$vToMonFriTimeSlot1 = isset($_POST['vToMonFriTimeSlot1'])?$_POST['vToMonFriTimeSlot1']:'';
$vFromMonFriTimeSlot2 = isset($_POST['vFromMonFriTimeSlot2'])?$_POST['vFromMonFriTimeSlot2']:'';
$vToMonFriTimeSlot2 = isset($_POST['vToMonFriTimeSlot2'])?$_POST['vToMonFriTimeSlot2']:'';
$vFromSatSunTimeSlot1 = isset($_POST['vFromSatSunTimeSlot1'])?$_POST['vFromSatSunTimeSlot1']:'';
$vToSatSunTimeSlot1 = isset($_POST['vToSatSunTimeSlot1'])?$_POST['vToSatSunTimeSlot1']:'';
$vFromSatSunTimeSlot2 = isset($_POST['vFromSatSunTimeSlot2'])?$_POST['vFromSatSunTimeSlot2']:'';
$vToSatSunTimeSlot2 = isset($_POST['vToSatSunTimeSlot2'])?$_POST['vToSatSunTimeSlot2']:'';

$fMinOrderValue = isset($_POST['fMinOrderValue'])?$_POST['fMinOrderValue']:'';
$fPackingCharge = isset($_POST['fPackingCharge'])?$_POST['fPackingCharge']:'';
$iMaxItemQty = isset($_POST['iMaxItemQty'])?$_POST['iMaxItemQty']:'';
$fPrepareTime = isset($_POST['fPrepareTime'])?$_POST['fPrepareTime']:'';
$fOfferAppyType = isset($_POST['fOfferAppyType'])?$_POST['fOfferAppyType']:'';
$fOfferType = isset($_POST['fOfferType'])?$_POST['fOfferType']:'';
$fTargetAmt = isset($_POST['fTargetAmt'])?$_POST['fTargetAmt']:'';
$fOfferAmt = isset($_POST['fOfferAmt'])?$_POST['fOfferAmt']:'';
$fMaxOfferAmt = isset($_POST['fMaxOfferAmt'])?$_POST['fMaxOfferAmt']:'';
//$fTax =  isset($_POST['fTax'])?$_POST['fTax']:'';

$fPricePerPerson = isset($_POST['fPricePerPerson'])?$_POST['fPricePerPerson']:'';
$listingOrder = isset($_POST['listingOrder'])?$_POST['listingOrder']:'';
$backlink = isset($_POST['backlink'])?$_POST['backlink']:'';
$previousLink = isset($_POST['backlink'])?$_POST['backlink']:'';

$cpremium = isset($_POST['cpremium'])?$_POST['cpremium']:0;

$rpremium = isset($_POST['rpremium'])?$_POST['rpremium']:0;
$company_type = isset($_POST['company_type'])?$_POST['company_type']:0;
$company_bases = isset($_POST['company_bases'])?$_POST['company_bases']:'';
if($company_type == 2){
	$company_bases = '';
}else{
	$company_bases = implode(',',$company_bases);
}
if (isset($_POST['submitBtn'])) {

	if (SITE_TYPE == 'Demo') {
		header("Location:company_action.php?id=" . $id . '&success=2');
		exit;
	}
	//Add Custom validation
	require_once("library/validation.class.php");
	$validobj = new validation();
	$validobj->add_fields($_POST['vCompany'], 'req', 'Restaurant Name is required');
	$validobj->add_fields($_POST['vEmail'], 'req', 'Email Address is required.');
	//$validobj->add_fields($_POST['vEmail'], 'email', 'Please enter valid Email Address.');
	if ($action == "Add") {
		$validobj->add_fields($_POST['vPassword'], 'req', 'Password is required.');
	}
	$validobj->add_fields($_POST['vPhone'], 'req', 'Phone Number is required.');
	$validobj->add_fields($_POST['vCaddress'], 'req', 'Address is required.');
	$validobj->add_fields($_POST['vZip'], 'req', 'Zip Code is required.');
	$validobj->add_fields($_POST['vLang'], 'req', 'Language is required.');
	$validobj->add_fields($_POST['vCountry'], 'req', 'Country is required.');

	$error = $validobj->validate();

	//Other Validations
	if ($vEmail != "") {
		if ($id != "") {
			$msg1 = $generalobj->checkDuplicateAdminNew('iCompanyId', 'company', Array('vEmail'), $id, "");
		} else {
			$msg1 = $generalobj->checkDuplicateAdminNew('vEmail', 'company', Array('vEmail'), "", "");
		}
		if ($msg1 == 1) {
			$error .= 'Email Address is already exists.<br>';
		}
	}
	$error .= $validobj->validateFileType($_FILES['vImage'], 'jpg,jpeg,png,gif,bmp', '* Image file is not valid.');

	if ($error) {

		$success = 3;
		$newError = $error;

	} else {

		$sql = "select vPhoneCode from country where vCountryCode = '$vCountry'";
		$db_country_data = $obj->MySQLSelect($sql);

		if($vCode == ""){
			$vCode = $db_country_data[0]['vPhoneCode'];
		}

		$CompanyData ['vCompany'] = $vCompany;
		$CompanyData ['parent_iCompanyId'] = $parent_iCompanyId;
		$CompanyData ['vEmail'] = $vEmail;
		if($vPass != ""){
			$CompanyData ['vPassword'] = $vPass;
		}
		$CompanyData ['type'] = $type;
		$CompanyData ['vContactName'] = $vContactName;
		$CompanyData ['vCode'] = $vCode;
		$CompanyData ['vPhone'] = $vPhone;
		$CompanyData ['vCountry'] = $vCountry;
		$CompanyData ['vState'] = $vState;
		$CompanyData ['vCity'] = $vCity;
		$CompanyData ['vRestuarantLocation'] = $vRestuarantLocation;
		$CompanyData ['vRestuarantLocationLat'] = $vRestuarantLocationLat;
		$CompanyData ['vRestuarantLocationLong'] = $vRestuarantLocationLong;
		$CompanyData ['vCaddress'] = $vCaddress;
		$CompanyData ['vZip'] = $vZip;
		$CompanyData ['vLang'] = $vLang;
		$CompanyData ['vAcctHolderName'] = $vAcctHolderName;
		$CompanyData ['vAcctNo'] = $vAcctNo;
		$CompanyData ['vBankName'] = $vBankName;
		$CompanyData ['vBankLocation'] = $vBankLocation;
		$CompanyData ['vSwiftCode'] = $vSwiftCode;
		$CompanyData ['iServiceId'] = $iServiceIdNew;

		$vFromMonFriTimeSlot1_arr = explode(" ", $vFromMonFriTimeSlot1);
		$CompanyData ['vFromMonFriTimeSlot1'] = $vFromMonFriTimeSlot1_arr[0];

		$vToMonFriTimeSlot1_arr = explode(" ", $vToMonFriTimeSlot1);
		$CompanyData ['vToMonFriTimeSlot1'] = $vToMonFriTimeSlot1_arr[0];

		$vFromMonFriTimeSlot2_arr = explode(" ", $vFromMonFriTimeSlot2);
		$CompanyData ['vFromMonFriTimeSlot2'] = $vFromMonFriTimeSlot2_arr[0];

		$vToMonFriTimeSlot2_arr = explode(" ", $vToMonFriTimeSlot2);
		$CompanyData ['vToMonFriTimeSlot2'] = $vToMonFriTimeSlot2_arr[0];

		$vFromSatSunTimeSlot1_arr = explode(" ", $vFromSatSunTimeSlot1);
		$CompanyData ['vFromSatSunTimeSlot1'] = $vFromSatSunTimeSlot1_arr[0];

		$vToSatSunTimeSlot1_arr = explode(" ", $vToSatSunTimeSlot1);
		$CompanyData ['vToSatSunTimeSlot1'] = $vToSatSunTimeSlot1_arr[0];

		$vFromSatSunTimeSlot2_arr = explode(" ", $vFromSatSunTimeSlot2);
		$CompanyData ['vFromSatSunTimeSlot2'] = $vFromSatSunTimeSlot2_arr[0];

		$vToSatSunTimeSlot2_arr = explode(" ", $vToSatSunTimeSlot2);
		$CompanyData ['vToSatSunTimeSlot2'] = $vToSatSunTimeSlot2_arr[0];

		$CompanyData ['fMinOrderValue'] = $fMinOrderValue;
		$CompanyData ['fPackingCharge'] = $fPackingCharge;
		$CompanyData ['iMaxItemQty'] = $iMaxItemQty;
		$CompanyData ['fPrepareTime'] = $fPrepareTime;
		$CompanyData ['fOfferAppyType'] = $fOfferAppyType;
		$CompanyData ['fOfferType'] = $fOfferType;
		$CompanyData ['fTargetAmt'] = $fTargetAmt;
		$CompanyData ['fOfferAmt'] = $fOfferAmt;
		$CompanyData ['fMaxOfferAmt'] = $fMaxOfferAmt;
		//$CompanyData ['fTax'] = $fTax;
		$CompanyData ['cpremium'] = $cpremium;
		$CompanyData ['rpremium'] = $rpremium;
		$CompanyData ['company_type'] = $company_type;
		$CompanyData ['company_bases'] = $company_bases;

		if($fOfferAppyType == 'None'){
			$CompanyData ['fTargetAmt'] = 0;
			$CompanyData ['fOfferAmt'] = 0;
			$CompanyData ['fMaxOfferAmt'] = 0;
		}

		if($fOfferType == 'Flat'){
			$CompanyData ['fMaxOfferAmt'] = 0;
		}

		$CompanyData ['listingOrder'] = $listingOrder;
		$CompanyData ['fPricePerPerson'] = $fPricePerPerson;
		if ($action == 'Add') {
			$CompanyData ['tRegistrationDate'] = date("Y-m-d H:i:s");
		}

		if ($id != '') {

			$cQuery = 'SELECT vEmail,vPhone FROM company WHERE  `iCompanyId` = "' . $id . '"';
			$CompanyOldData = $obj->MySQLSelect($cQuery);
			$OldEmail = $CompanyOldData[0]['vEmail'];
			$OldPhone = $CompanyOldData[0]['vPhone'];

			if($OldEmail != '' && $vEmail != ''){
				if($OldEmail != $vEmail){
					$CompanyData['eAvailable'] = 'No';
					$CompanyData['eEmailVerified'] = 'No';
				}
			}

			if($OldPhone != '' && $vPhone != ''){
				if($OldPhone != $vPhone){
					$CompanyData['eAvailable'] = 'No';
					$CompanyData['ePhoneVerified'] = 'No';
				}
			}

			if($company_type == 1 && empty($company_bases)) {
				$CompanyData['company_bases'] = $allbase;
			} elseif($company_type != 1 ) {
				$CompanyData['company_bases'] = '';
			}

			//Newcode to change parent id on Update only
			//echo $parent_iCompanyId; exit; 
			if($parent_iCompanyId == 0) {
			   
				$Haschild = checkchildrestaurant($id);  
				   //echo "yes".$Haschild; exit;
				if($Haschild != 0) {
					$CompanyData['parent_iCompanyId'] = 0;
				} else {
					$CompanyData['parent_iCompanyId'] = $id;
				}
				
			} elseif($parent_iCompanyId != 0) {
				
				$Haschild = checkchildrestaurant($id); 
				if($Haschild != 0) {
					$CompanyData['parent_iCompanyId'] = 0;
				} else {
					$WhereParent = " `iCompanyId` = '" . $parent_iCompanyId . "'";
					$UpdateParentCompanyData['parent_iCompanyId'] = 0;
					$company_id = $obj->MySQLQueryPerform($tbl_name, $UpdateParentCompanyData, 'update', $WhereParent);
				}
				
				
			}
			//Newcode to change parent id on Update only close
			$where = " `iCompanyId` = '" . $id . "'";
			$company_id = $obj->MySQLQueryPerform($tbl_name, $CompanyData, 'update', $where);



		} else {
			//echo '<pre>';print_r($CompanyData);die;
			$company_id = $obj->MySQLQueryPerform($tbl_name, $CompanyData, 'insert');
			//Newcode to change parent id on Insert only
			if($company_type == 1 && empty($company_bases)) {
				$CompanyData['company_bases'] = $allbase;
			} elseif($company_type != 1 ) {
				$CompanyData['company_bases'] = '';
			}

			if($parent_iCompanyId == 0) {
				$WhereParent = " `iCompanyId` = '" . $company_id . "'";
				$UpdateParentCompanyData['parent_iCompanyId'] = $company_id;
				$company_id = $obj->MySQLQueryPerform($tbl_name, $UpdateParentCompanyData, 'update', $WhereParent);
			} elseif($parent_iCompanyId != 0) {
				$WhereParent = " `iCompanyId` = '" . $parent_iCompanyId . "'";
				$UpdateParentCompanyData['parent_iCompanyId'] = 0;
				$company_id = $obj->MySQLQueryPerform($tbl_name, $UpdateParentCompanyData, 'update', $WhereParent);
			}
			
			//Newcode to change parent id on Insert only close        
		}

		$id = ($id != '') ? $id : $company_id;

		$q = "SELECT count(ccId) as total_cuisine FROM company_cuisine WHERE iCompanyId ='".$id."'";
		$CuisineOldData = $obj->MySQLSelect($q);
		if($CuisineOldData[0]['total_cuisine'] > 0){
			$q1 = "DELETE FROM company_cuisine WHERE `iCompanyId`='".$id."'";
			$oldid = $obj->sql_query($q1);
		}
		foreach ($cuisineId as $key => $value) {
			$cusdata['iCompanyId'] = $id;
			$cusdata['cuisineId'] = $value;
			$cusine_id = $obj->MySQLQueryPerform('company_cuisine', $cusdata, 'insert');
		}

		if ($_FILES['vImage']['name'] != "") {

			$image_object = $_FILES['vImage']['tmp_name'];
			$image_name = $_FILES['vImage']['name'];
			$img_path = $tconfig["tsite_upload_images_compnay_path"];
			$temp_gallery = $img_path . '/';
			$check_file = $img_path . '/' . $id. '/' .$oldImage;

			if ($oldImage != '' && file_exists($check_file)) {
				@unlink($img_path . '/' . $id. '/' . $oldImage);
				@unlink($img_path . '/' . $id. '/1_' . $oldImage);
				@unlink($img_path . '/' . $id. '/2_' . $oldImage);
				@unlink($img_path . '/' . $id. '/3_' . $oldImage);
			}

			$Photo_Gallery_folder = $img_path . '/' . $id . '/';
			if (!is_dir($Photo_Gallery_folder)) {
				mkdir($Photo_Gallery_folder, 0777);
			}
			$img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);

			if($img1 != ''){
				if(is_file($Photo_Gallery_folder.$img1)) {
					include_once(TPATH_CLASS."/SimpleImage.class.php");
					/* $img = new SimpleImage();
					  list($width, $height, $type, $attr)= getimagesize($Photo_Gallery_folder.$img1);
					  if($width < $height){
					  $final_width = $width;
					  }else{
					  $final_width = $height;
					  }
					  $img->load($Photo_Gallery_folder.$img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder.$img1); */
					$img1 = $generalobj->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
				}
			}
			$vImgName = $img1;
			$sql = "UPDATE ".$tbl_name." SET `vImage` = '" . $vImgName . "' WHERE `iCompanyId` = '" . $id . "'";
			$obj->sql_query($sql);
		}

		if ($_FILES['vCoverImage']['name'] != "") {

			$image_object = $_FILES['vCoverImage']['tmp_name'];
			$image_name = $_FILES['vCoverImage']['name'];
			$img_path = $tconfig["tsite_upload_images_compnay_path"];
			$temp_gallery = $img_path . '/';
			$check_file = $img_path . '/' . $id. '/' .$oldvCoverImageImage;

			if ($oldvCoverImageImage != '' && file_exists($check_file)) {
				@unlink($img_path . '/' . $id. '/' . $oldvCoverImageImage);
				@unlink($img_path . '/' . $id. '/1_' . $oldvCoverImageImage);
				@unlink($img_path . '/' . $id. '/2_' . $oldvCoverImageImage);
				@unlink($img_path . '/' . $id. '/3_' . $oldvCoverImageImage);
			}

			$Photo_Gallery_folder = $img_path . '/' . $id . '/';
			if (!is_dir($Photo_Gallery_folder)) {
				mkdir($Photo_Gallery_folder, 0777);
			}
			$img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);

			if($img1 != ''){
				if(is_file($Photo_Gallery_folder.$img1)) {
					include_once(TPATH_CLASS."/SimpleImage.class.php");
					$img = new SimpleImage();
					$img1 = $generalobj->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_cover_size3"], $tconfig["tsite_upload_images_cover_size4"], $tconfig["tsite_upload_images_cover_size5"], "");
				}
			}
			$vImgName = $img1;
			$sql = "UPDATE ".$tbl_name." SET `vCoverImage` = '" . $vImgName . "' WHERE `iCompanyId` = '" . $id . "'";
			$obj->sql_query($sql);
		}

		if ($action == "Add") {
			$_SESSION['success'] = '1';
			$_SESSION['var_msg'] = 'Record Inserted Successfully.';
		} else {
			updateallrestaurantitems($id, $cpremium, $rpremium);
			$_SESSION['success'] = '1';
			$_SESSION['var_msg'] = 'Record Updated Successfully.';
		}
		header("location:".$backlink);
	}
}
// for Edit

if ($action == 'Edit') {
$sql = "SELECT * FROM " . $tbl_name . " WHERE iCompanyId = '" . $id . "'";
$db_data = $obj->MySQLSelect($sql);

$sql1 = "SELECT cuisineId FROM `company_cuisine` WHERE iCompanyId = '" . $id . "'";
$db_cusinedata = $obj->MySQLSelect($sql1);
foreach ($db_cusinedata as $key => $value) {
$cusineselecteddata[] = $value['cuisineId'];
}
if (count($db_data) > 0) {
foreach ($db_data as $key => $value) {
$vCompany = $generalobjAdmin->clearCmpName($value['vCompany']);
$type = $value['type'];
$parent_iCompanyId = $value['parent_iCompanyId'];
$vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
$vPassword = $value['vPassword'];
$vContactName = $generalobjAdmin->clearName($value['vContactName']);
$vCode = $value['vCode'];
$vPhone = $generalobjAdmin->clearPhone($value['vPhone']);
$vCountry = $value['vCountry'];
$vCity = $value['vCity'];
$vState = $value['vState'];
$vRestuarantLocation = $value['vRestuarantLocation'];
$vRestuarantLocationLat = $value['vRestuarantLocationLat'];
$vRestuarantLocationLong = $value['vRestuarantLocationLong'];
$vCaddress = $value['vCaddress'];
$vZip = $value['vZip'];
$vLang = $value['vLang'];
$oldImage = $value['vImage'];
$oldvCoverImageImage = $value['vCoverImage'];
$vAcctHolderName = $value['vAcctHolderName'];
$vAcctNo = $value['vAcctNo'];
$vBankName = $value['vBankName'];
$vBankLocation = $value['vBankLocation'];
$vSwiftCode = $value['vSwiftCode'];
$vFromMonFriTimeSlot1 = $value['vFromMonFriTimeSlot1'];
$vToMonFriTimeSlot1 = $value['vToMonFriTimeSlot1'];
$vFromMonFriTimeSlot2 = $value['vFromMonFriTimeSlot2'];
$vToMonFriTimeSlot2 = $value['vToMonFriTimeSlot2'];
$vFromSatSunTimeSlot1 = $value['vFromSatSunTimeSlot1'];
$vToSatSunTimeSlot1 = $value['vToSatSunTimeSlot1'];
$vFromSatSunTimeSlot2 = $value['vFromSatSunTimeSlot2'];
$vToSatSunTimeSlot2 = $value['vToSatSunTimeSlot2'];
$fMinOrderValue = $value['fMinOrderValue'];
$fPackingCharge = $value['fPackingCharge'];
$iMaxItemQty = $value['iMaxItemQty'];
$fPrepareTime = $value['fPrepareTime'];
$fOfferAppyType = $value['fOfferAppyType'];
$fOfferType = $value['fOfferType'];
$fTargetAmt = $value['fTargetAmt'];
$fOfferAmt = $value['fOfferAmt'];
$fPricePerPerson = $value['fPricePerPerson'];
$listingOrder = $value['listingOrder'];
//$fTax = $value['fTax'];
$fMaxOfferAmt = $value['fMaxOfferAmt'];
$iServiceIdNew = $value['iServiceId'];
$cpremium = $value['cpremium'];
$rpremium = $value['rpremium'];
$company_type = $value['company_type'];
$company_bases = $value['company_bases'];
}
}
}
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
foreach ($allservice_cat_data as $k => $val) {
$iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_".$default_lang." as servicename,eStatus FROM service_categories WHERE iServiceId IN (".$serviceIds.") AND eStatus = 'Active'";
$service_cat_list = $obj->MySQLSelect($service_category);

$selectcuisine_sql = "SELECT cuisineId,cuisineName_".$default_lang." FROM cuisine WHERE  iServiceId = '".$iServiceIdNew."' AND eStatus = 'Active'";
$db_cuisine = $obj->MySQLSelect($selectcuisine_sql);



$parentrestaurantlist = "SELECT `iCompanyId`,`vCompany` FROM `company` WHERE `parent_iCompanyId` = 0 OR `parent_iCompanyId` = `iCompanyId`   AND `eStatus` = 'Active';";
$parentResDatas =   $obj->MySQLSelect($parentrestaurantlist);
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?
        include_once('global_files.php');
        ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
        <script type='text/javascript' src='../assets/map/gmaps.js'></script>
        <link rel="stylesheet" href="css/select2/select2.min.css" type="text/css" >
        <script type="text/javascript" src="js/plugins/select2.min.js"></script>
        <script type='text/javascript' src='../assets/js/bootbox.min.js'></script>
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
                            <h2><?= $action; ?> <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> <?= $vCompany; ?></h2>
                            <a class="back_link" href="company.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">

                        <?php //echo "<pre>"; print_r($_SESSION); !empty($_POST) ? print_r($_POST) : ""; echo "</pre>";  ?>
                        <div class="form-group">
                            <? if ($success == 2) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                "Edit / Delete Record Feature" has been disabled on the Demo <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Panel. This feature will be enabled on the main script we will provide you.
                            </div><br/>
                            <?} ?>
                            <? if ($success == 3) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php print_r($error); ?>
                            </div><br/>
                            <?} ?>
                            <form name="_company_form" id="_company_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="action" id="action" value="<?php echo $action; ?>"/>
                                <input type="hidden" name="id" id="iCompanyId" value="<?php echo $id; ?>"/>
                                <input type="hidden" name="oldImage" value="<?= $oldImage; ?>"/>
                                <input type="hidden" name="oldvCoverImageImage" value="<?= $oldvCoverImageImage; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="company.php"/>
                                <?php if($action == 'Edit'){ ?>
                                <input type="hidden" name="iServiceId"  value="<?php echo $iServiceIdNew; ?>">
                                <?php } ?>
                                <!-- Changes For Restaurant -->
                                <div class="row">
                                    <div class="col-lg-6">  
                                        <div class="row" style='display:none'>
                                            <div class="col-lg-12">
                                                <label><?php echo 'Parent Restaurant'?></label>
                                            </div>
                                            <div class="col-lg-12">
<!--                                                onchange="changeServiceType(this.value)" -->
                                                <select class="form-control" name = 'parent_iCompanyId' id="parent_iCompanyId" >
                                                    <option value="0">Select or Leave</option>
                                                    <?php for($i=0;$i<count($parentResDatas);$i++){ ?>
                                                    <option value = "<?= $parentResDatas[$i]['iCompanyId'] ?>" <?if($parent_iCompanyId==$parentResDatas[$i]['iCompanyId']){?>selected<? } ?>><?= $parentResDatas[$i]['vCompany'] ?></option>
                                                    <? } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Name<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vCompany"  id="vCompany" value="<?= $vCompany; ?>" placeholder="<?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Name">
                                            </div>
                                        </div>
                                        <?php
                                        if($action == 'Add'){
                                        if(count($allservice_cat_data) <= 1){
                                        ?>
                                        <input name="iServiceId" type="hidden" class="create-account-input" value="<?php echo $service_cat_list[0]['iServiceId']; ?>" id="iServiceId"/>
                                        <?php } else { ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Service Type<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name = 'iServiceId' id="iServiceId" onchange="changeServiceType(this.value)">
                                                    <option value="">Select</option>
                                                    <? for($i=0;$i<count($service_cat_list);$i++){ ?>
                                                    <option value = "<?= $service_cat_list[$i]['iServiceId'] ?>" <?if($iServiceIdNew==$service_cat_list[$i]['iServiceId']){?>selected<? } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
                                                    <? } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php
                                        }
                                        }
                                        ?>
<!-- Restaurant types -->
					<!--<div class="row">
                                            <div class="col-lg-12">
                                                <label>Restaurant Type</label>
                                            </div>
                                            <div class="col-lg-12 stylerestypes">
												<div class='col-sm-2'>
													<span><label for="basefor">Base</label></span>
													<input type="radio" value='1' name="company_type" id="basefor" <?php if($company_type == 1){ echo 'checked'; } ?> />
												</div>
												<div class='col-sm-2'>
													<span><label for="cityfor">City</label></span>
													<input type="radio" value='2' name="company_type"  <?php if($company_type == 2){ echo 'checked'; } ?> id="cityfor"/>
												</div>
                                                <div class='col-sm-2'>
													<span><label for="bothfor">Both</label></span>
													<input type="radio" value='3' name="company_type"  <?php if($company_type == 3){ echo 'checked'; } else if($company_type == 0){ echo 'checked'; } ?> id="bothfor"/>
												</div>
												<div class='col-sm-6'>
													<span>&nbsp;</span>
												</div>
                                            </div>
                                        </div>-->
										<input type="hidden" value='3' name="company_type" id="bothfor"/>
										<div class="row checkrestaurant_type <?php if($company_type == 1) { echo 'active'; }else{ echo 'inactive'; }?>">
                                            <div class="col-lg-12">
                                                <label>Bases</label>
                                            </div>
                                            <div class="col-lg-12">
												<?php
												$i=1;
												foreach($resbases as $key=>$val){
													$companybase  = array();
													if($company_type == 1 && $company_bases != ''){
														$companybase = explode(',',$company_bases);
													}
													$checked = '';
													if(in_array($key,$companybase)){ $checked = 'checked'; }
												?>
													<div class='col-sm-3'>
														<span style="float:left;width:70%;"><label for="bases<?php echo $key; ?>"><?php echo $val; ?></label></span>
                                                                                                                <input type="checkbox" value='<?php echo $key; ?>' id="bases<?php echo $key; ?>" class="checkboxvarbases" name='company_bases[]' <?php echo $checked; ?> />
													</div>
												<?php 
													$i++;
												}
												?>
                                            </div>
                                        </div>


<!--<div class="row">
                                            <div class="col-lg-12">
                                                <label>Type<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name = 'type' id="type">
                                                    <option value = "online" <?php echo $type == 'online' ? 'selected' : '' ?>>Online</option>
                                                    <option value = "tablet" <?php echo $type == 'tablet' ? 'selected' : '' ?>>Tablet</option>
                                                    <option value = "wallet" <?php echo $type == 'wallet' ? 'selected' : '' ?>>Wallet</option>
                                                   
                                                </select>
                                                <small>Online: Tablet in office, Tablet : Using Chowcall Res. App, Wallet: Driver Pay for it.</small>
                                            </div>
                                        </div>-->
										<input type='hidden' value='3' name='type' />

<!-- Restaurant type close -->


                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Email<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vEmail" id="vEmail" value="<?= $vEmail; ?>" placeholder="Email">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Password<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="password" class="form-control" name="vPassword"  id="vPassword" value="" placeholder="Password">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Location<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" id="vRestuarantLocation" class="form-control" name="vRestuarantLocation"  id="vRestuarantLocation" value="<?= $vRestuarantLocation; ?>" placeholder="<?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Location" required>
                                            </div>
                                            <input type="hidden" name="vRestuarantLocationLat" id="vRestuarantLocationLat" value="<?= $vRestuarantLocationLat ?>">
                                            <input type="hidden" name="vRestuarantLocationLong" id="vRestuarantLocationLong" value="<?= $vRestuarantLocationLong ?>">
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="map" style="width:100%;height:200px;"></div>
                                            </div>
                                        </div> 
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Address<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vCaddress"  id="vCaddress" value="<?= $vCaddress; ?>" placeholder="Address" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Zip Code<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vZip"  id="vZip" value="<?= $vZip; ?>" placeholder="Zip Code" >
                                            </div>
                                        </div> 
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Country <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name = 'vCountry' id="vCountry" onChange="setState(this.value, '');changeCode(this.value);" required>
                                                    <option value="">Select</option>
                                                    <? for($i=0;$i<count($db_country);$i++){ ?>
                                                    <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?if($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                                        <? } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>State </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <select class="form-control" name = 'vState' id="vState" onChange="setCity(this.value, '');" >
                                                        <option value="">Select</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>City </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <select class="form-control" name = 'vCity' id="vCity"  >
                                                        <option value="">Select</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Contact Person Name<span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <input type="text" class="form-control" name="vContactName"  id="vContactName" value="<?= $vContactName; ?>" placeholder="Person Name" required>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Phone Number<span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-12" style="float: left;">
                                                    <input type="text" class="form-select-2" id="code" name="vCode" value="<?= $vCode ?>"  readonly style="width: 10%;height: 36px;text-align: center;"/ >
                                                           <input type="text" class="form-control" name="vPhone"  id="vPhone" value="<?= $vPhone; ?>" placeholder="Phone" style="margin-top: 5px; width:90%;" required>
                                                </div>
                                            </div>
                                            <?php if ($id) { ?>
                                                <div class= "row col-md-12" id="hide-profile-div">
                                                    <? $class = "col-lg-12"; ?>
                                                    <div class="<?= $class ?>">
                                                        <b>
                                                            <?php if ($oldImage == 'NONE' || $oldImage == '') { ?>
                                                                <img src="../assets/img/profile-user-img.png" alt="" >
                                                                <? } else { 
                                                                if(file_exists('../webimages/upload/Company/' .$id. '/3_' .$oldImage)){ ?>
                                                                <img src = "<?php echo $tconfig["tsite_upload_images_compnay"] . '/' . $id . '/3_' . $oldImage ?>"  style="width: 60%;height: auto"/>
                                                            <?php } else { ?>
                                                                <img src="../assets/img/profile-user-img.png" alt="" >
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </b>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Logo</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="file" class="form-control" name="vImage"  id="vImage" style="padding-bottom: 39px;">
                                            </div>
                                        </div>

                                        <!--  <?php if ($id) { ?>
                                                                                 <div class= "row col-md-12" id="hide-profile-div">
                                                                                     <? $class = "col-lg-12"; ?>
                                                                                      <div class="<?= $class ?>">
                                                                                           <b>
                                            <?php if ($oldvCoverImageImage == 'NONE' || $oldvCoverImageImage == '') { ?>
                                                                                                                                         <img src="../assets/img/profile-user-img.png" alt="" >
                                                                                                                                 <? } else { 
                                                                                                                                     if(file_exists('../webimages/upload/Company/' .$id. '/3_' .$oldvCoverImageImage)){ ?>
                                                                                                                                         <img src = "<?php echo $tconfig["tsite_upload_images_compnay"] . '/' . $id . '/3_' . $oldvCoverImageImage ?>" style="width: 60%;height: auto"/>
                                            <?php } else { ?>
                                                                                                                                         <img src="../assets/img/profile-user-img.png" alt="" >
                                                <?php
                                            }
                                        }
                                        ?>
                                                       </b>
                                                  </div>
                                             </div>
                                        <?php } ?>
                                         <div class="row">
                                              <div class="col-lg-12">
                                                   <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Cover Photo</label>
                                              </div>
                                              <div class="col-lg-12">
                                                   <input type="file" class="form-control" name="vCoverImage"  id="vCoverImage" style="padding-bottom: 39px;">
                                              </div>
                                         </div>-->
										 
					
										
                                        <?php if (count($db_lang) <= 1) { ?>
                                            <input name="vLang" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode']; ?>"/>
                                        <?php } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Language<span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <select  class="form-control" name = 'vLang' >
                                                        <option value="">--select--</option>
                                                        <? for ($i = 0; $i < count($db_lang); $i++) { ?>
                                                        <option value = "<?= $db_lang[$i]['vCode'] ?>" <?= ($db_lang[$i]['vCode'] == $vLang) ? 'selected' : ''; ?>>
                                                            <?= $db_lang[$i]['vTitle'] ?>
                                                        </option>
                                                        <? } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <input type="submit" class="btn btn-default" name="submitBtn" id="submitBtn" value="<?= $action; ?> <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>" >
                                                <input type="reset" value="Reset" class="btn btn-default">
                                                <a href="company.php" class="btn btn-default back_link">Cancel</a>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Available <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Item Types<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control"  id="js-cuisine-multiple" name="cuisineId[]" multiple="multiple">
                                                    <?php foreach ($db_cuisine as $cuisinedata) { ?>
                                                        <option name="<?= $cuisinedata['cuisineId'] ?>" value="<?= $cuisinedata['cuisineId'] ?>" <?php echo (isset($cusineselecteddata) && in_array($cuisinedata['cuisineId'], $cusineselecteddata)) ? 'selected="selected"' : ""; ?>><?= $cuisinedata["cuisineName_" . $default_lang] ?></option>
                                                    <?php } ?>    
                                                </select>
                                                <div class="CuisineClass">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Slot1: Monday to Friday<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vFromMonFriTimeSlot1'>
                                                            <input type='text' class="form-control TimeField" name="vFromMonFriTimeSlot1" value="<?= $vFromMonFriTimeSlot1; ?>" required/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                        <span class="FromError1"></span>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vToMonFriTimeSlot1'>
                                                            <input type='text' class="form-control TimeField" name="vToMonFriTimeSlot1" value="<?= $vToMonFriTimeSlot1; ?>" required/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                        <span class="ToError1"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Slot2 : Monday to Friday</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vFromMonFriTimeSlot2'>
                                                            <input type='text' class="form-control" name="vFromMonFriTimeSlot2" value="<?= $vFromMonFriTimeSlot2; ?>"/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vToMonFriTimeSlot2'>
                                                            <input type='text' class="form-control" name="vToMonFriTimeSlot2" value="<?= $vToMonFriTimeSlot2; ?>"/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Slot1 : Saturday &amp; Sunday<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vFromSatSunTimeSlot1'>
                                                            <input type='text' class="form-control TimeField" name="vFromSatSunTimeSlot1" value="<?= $vFromSatSunTimeSlot1; ?>" required/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                        <span class="FromError2"></span>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vToSatSunTimeSlot1'>
                                                            <input type='text' class="form-control TimeField" name="vToSatSunTimeSlot1" value="<?= $vToSatSunTimeSlot1; ?>" required/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span class="ToError2"></span>
                                                </div>   
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Slot2 : Saturday &amp; Sunday</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vFromSatSunTimeSlot2'>
                                                            <input type='text' class="form-control" name="vFromSatSunTimeSlot2" value="<?= $vFromSatSunTimeSlot2; ?>"/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vToSatSunTimeSlot2'>
                                                            <input type='text' class="form-control" name="vToSatSunTimeSlot2" value="<?= $vToSatSunTimeSlot2; ?>"/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Minimum Amount Per Order (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the price if you want to deliver order only after XX price.'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fMinOrderValue"  id="fMinOrderValue" value="<?= $fMinOrderValue; ?>" placeholder="Minimum Order" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Additional Packing Charges (In <?= $db_currency[0]['vName'] ?>)</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fPackingCharge"  id="fPackingCharge" value="<?= $fPackingCharge; ?>" placeholder="Packing Charges" >
                                            </div>
                                        </div>
                                        <!-- Menu Premium Fields -->
                                        <!--<div class="row">
                                            <div class="col-lg-12">
                                                <label>Chowcall Menu Premium (%)</label>
                                            </div>
                                            <div class="col-lg-12">-->
                                                <input type="hidden" class="form-control" name="cpremium"  id="cpremium" value="<?= $cpremium; ?>" placeholder="Chowcall Menu Premium" >
                                            <!--</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Restaurant Menu Premium (%)</label>
                                            </div>
                                            <div class="col-lg-12">-->
                                                <input type="hidden" class="form-control" name="rpremium"  id="rpremium" value="<?= $rpremium; ?>" placeholder="Restaurant Menu Premium" >
                                            <!--</div>
                                        </div>-->
                                        <!-- Menu Premium fields Close -->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Max Quantity/Item Place By User Per Order<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="iMaxItemQty"  id="iMaxItemQty" value="<?= $iMaxItemQty; ?>" placeholder="Max qty place by user" >
                                            </div>
                                        </div>

                                        <div class="row estimateval">
                                            <div class="col-lg-12">
                                                <label>Estimated Prepration Time (in minutes)
                                                    <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fPrepareTime"  id="fPrepareTime" value="<?= $fPrepareTime; ?>" placeholder="Estimated Order Time" >
                                            <!--</div>
                                        </div>-->

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Offer Applies On<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name="fOfferAppyType"  id="fOfferAppyType">
                                                    <option value="None" <?if($fOfferAppyType == 'None'){echo 'selected';}?>>None</option>
                                                    <option value="First" <?if($fOfferAppyType == 'First'){echo 'selected';}?>>First Order</option>
                                                    <option value="All" <?if($fOfferAppyType == 'All'){echo 'selected';}?>>All Order</option>
                                                </select>
                                                <small>[Note: The discount will be applied on Base price including options and others.]</small>
                                            </div>
                                        </div>
                                        <div class="row" id="fOfferTypeDiv">
                                            <div class="col-lg-12">
                                                <label>Offer Type</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name="fOfferType"  id="fOfferType">
                                                     <option value="">Select Offer Type</option>
													<option value="Flat" <?if($fOfferType == 'Flat'){echo 'selected';}?>>Flat Offer</option>
                                                    <option value="Percentage" <?if($fOfferType == 'Percentage'){echo 'selected';}?>>Percentage Offer</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row" id="fOfferAmtDiv">
                                            <div class="col-lg-12">
                                                <label>Offer Discount <span class="addnote"></span><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fOfferAmt"  id="fOfferAmt" value="<?= $fOfferAmt; ?>" placeholder="Offer Amount">
                                            </div>
                                        </div>

                                        <div class="row" id="fTargetAmtDiv">
                                            <div class="col-lg-12">
                                                <label>Target Amount (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='"Set the minimum total order amount to avail the offer. E.g. "Get $7 off on order above $50" OR "Get 20% off on order above $50", so $50 is the target amount to get the off."'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fTargetAmt"  id="fTargetAmt" value="<?= $fTargetAmt; ?>" placeholder="Target Amount">
                                                <small>[Note: "If the offer type is 'Flat Offer' then set target amount ($11) greater than offer discount price($10), Ex. Get $10 off on orders above $11".]</small>
                                            </div>

                                        </div>

                                        <div class="row" id="fMaxOfferAmtDiv" style="display: none;">
                                            <div class="col-lg-12">
                                                <label>Max Off Amount (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the amount to limit user to get the maximum off amount on each order. E.g. If offer is 50% off, and maximum off amount is $250, then on order of $2000 user can get $250 off, but not $1000 off.'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fMaxOfferAmt"  id="fMaxOfferAmt" value="<?= $fMaxOfferAmt; ?>" placeholder="Max Off Amount" >
                                            </div>
                                        </div>
										
                                        <div class="row servicecatresponsive"  style="display:none;">
                                            <div class="col-lg-12">
                                                <label>Cost Per Person (In <?= $db_currency[0]['vName'] ?>)<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fPricePerPerson"  id="fPricePerPerson" value="<?= $fPricePerPerson; ?>" placeholder="Cost Per Person">
                                            </div>
                                        </div>
										<div class="row">
                                            <div class="col-lg-12">
                                                <label>Eatery Order (In Listing)<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="listingOrder"  id="listingOrder" value="<?= $listingOrder; ?>" placeholder="Order in Listing in app">
                                            </div>
                                        </div>
                                        <!-- <div class="row">
                                            <div class="col-lg-12">
                                                <label>Tax</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fTax"  id="fTax" value="<?= $fTax; ?>">
                                            </div>
                                        </div> -->

                                        <!--div class="row">
                                            <div class="col-lg-12">
                                                <label>Account Holder Name</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text"  class="form-control" name="vAcctHolderName"  id="vAcctHolderName" value="<?= $vAcctHolderName ?>" placeholder="Account Holder Name" >
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Account Number</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text"  class="form-control" name="vAcctNo"  id="vAcctNo" value="<?= $vAcctNo ?>" placeholder="Account Number" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Name of Bank</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text"  class="form-control" name="vBankName"  id="vBankName" value="<?= $vBankName ?>" placeholder="Name of Bank" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Bank Location</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vBankLocation"  id="vBankLocation" value="<?= $vBankLocation ?>" placeholder="Bank Location" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>BIC/SWIFT Code</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text"  class="form-control" name="vSwiftCode"  id="vSwiftCode" value="<?= $vSwiftCode ?>" placeholder="BIC/SWIFT Code" >
                                            </div>
                                        </div -->

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
        <?
        include_once('footer.php');
        ?>

        <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
        <script type="text/javascript" src="js/moment.min.js"></script>
        <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
        <script>

		$('[data-toggle="tooltip"]').tooltip();
		var successMSG1 = '<?php echo $success; ?>';
		if (successMSG1 != '') {
			setTimeout(function () {
				$(".msgs_hide").hide(1000)
			}, 5000);
		}

		$(document).ready(function () {
			var referrer;
			if ($("#previousLink").val() == "") {
				referrer = document.referrer;
			} else {
				referrer = $("#previousLink").val();
			}
			if (referrer == "") {
				referrer = "company.php";
			} else {
				$("#backlink").val(referrer);
			}
			$(".back_link").attr('href', referrer);
		});

		function setCity(id, selected)
		{
			var fromMod = 'company';
			var request = $.ajax({
				async: true,
				type: "POST",
				url: 'change_stateCity.php',
				data: {stateId: id, selected: selected, fromMod: fromMod},
				success: function (dataHtml)
				{
					$("#vCity").html(dataHtml);
				}
			});
		}

		function setState(id, selected)
		{
			var fromMod = 'company';
			var request = $.ajax({
				async: true,
				type: "POST",
				url: 'change_stateCity.php',
				data: {countryId: id, selected: selected, fromMod: fromMod},
				success: function (dataHtml)
				{
					$("#vState").html(dataHtml);
					if (selected == '')
						setCity('', selected);
				}
			});
		}

		setState('<?php echo $vCountry; ?>', '<?php echo $vState; ?>');
		setCity('<?php echo $vState; ?>', '<?php echo $vCity; ?>');

		function changeServiceType(iServiceid) {
			var iCompanyId = "<? echo $id?>";
			$.ajax({
				async: true,
				type: "POST",
				url: 'ajax_get_cuisine.php',
				data: {iServiceid: iServiceid, iCompanyId: iCompanyId},
				success: function (response)
				{
					//console.log(response);
					$("#js-cuisine-multiple").html('');
					$("#js-cuisine-multiple").html(response);
				}
			});
		}

		/*function changeServiceTypesecond(iServiceid){
		 var iCompanyId = "<? echo $id?>";
		 $.ajax({
		 type: "POST",
		 url: 'ajax_get_cuisine.php',
		 data: {iServiceid:iServiceid,iCompanyId:iCompanyId},
		 success: function (response)
		 {
		 //console.log(response);
		 $("#js-cuisine-multiple").html('');
		 $("#js-cuisine-multiple").html(response);
		 }
		 });
		 }
		 */
		function changeCode(id) {
			var request = $.ajax({
				async: true,
				type: "POST",
				url: 'change_code.php',
				data: 'id=' + id,
				success: function (data)
				{
					document.getElementById("code").value = data;
					//window.location = 'profile.php';
				}
			});
		}
		changeCode('<?php echo $vCountry; ?>');

		var map;
		function initialize() {
			map = new google.maps.Map(document.getElementById('map'), {
				center: {lat: -33.8688, lng: 151.2195},
				zoom: 13
			});
			var input = document.getElementById('vRestuarantLocation');
			// map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

			var autocomplete = new google.maps.places.Autocomplete(input);
			autocomplete.bindTo('bounds', map);

			var marker = new google.maps.Marker({
				map: map,
				anchorPoint: new google.maps.Point(0, -29)
			});

			autocomplete.addListener('place_changed', function () {

				marker.setVisible(false);
				var place = autocomplete.getPlace();
				if (!place.geometry) {
					window.alert("Autocomplete's returned place contains no geometry");
					return;
				}

				// If the place has a geometry, then present it on a map.
				if (place.geometry.viewport) {
					map.fitBounds(place.geometry.viewport);
				} else {
					map.setCenter(place.geometry.location);
					map.setZoom(17);
				}
				/*        marker.setIcon(({
				 size: new google.maps.Size(71, 71),
				 origin: new google.maps.Point(0, 0),
				 anchor: new google.maps.Point(17, 34),
				 scaledSize: new google.maps.Size(35, 35)
				 }));*/
				marker.setPosition(place.geometry.location);
				marker.setVisible(true);

				var address = '';
				if (place.address_components) {
					address = [
						(place.address_components[0] && place.address_components[0].short_name || ''),
						(place.address_components[1] && place.address_components[1].short_name || ''),
						(place.address_components[2] && place.address_components[2].short_name || '')
					].join(' ');
				}
				$("#vRestuarantLocation").val(place.formatted_address);
				$("#vRestuarantLocationLat").val(place.geometry.location.lat());
				$("#vRestuarantLocationLong").val(place.geometry.location.lng());
			});

			if ($("#vRestuarantLocation").val() != "") {
				var myLatLng = new google.maps.LatLng($("#vRestuarantLocationLat").val(), $("#vRestuarantLocationLong").val());
				marker.setPosition(myLatLng);
				map.setCenter(myLatLng);
				map.setZoom(17);
				marker.setVisible(true);
			}
		}

		google.maps.event.addDomListener(window, 'load', initialize);

		$(function () {
			$('#vFromMonFriTimeSlot1').datetimepicker({
				format: 'HH:mm A',
				ignoreReadonly: true,
			});
			$('#vToMonFriTimeSlot1').datetimepicker({
				format: 'HH:mm A',
				ignoreReadonly: true,
				useCurrent: false //Important! See issue #1075
			});

			/* $("#vFromTimeSlot1").on("dp.change", function (e) {
			 $('#vToTimeSlot1').data("DateTimePicker").minDate(e.date);
			 });
			 $("#vToTimeSlot1").on("dp.change", function (e) {
			 $('#vFromTimeSlot1').data("DateTimePicker").maxDate(e.date);
			 });*/

			$('#vFromMonFriTimeSlot2').datetimepicker({
				format: 'HH:mm A',
				ignoreReadonly: true,
			});
			$('#vToMonFriTimeSlot2').datetimepicker({
				format: 'HH:mm A',
				ignoreReadonly: true,
				useCurrent: false
			});


			/*$("#vFromTimeSlot2").on("dp.change", function (e) {
			 $('#vToTimeSlot2').data("DateTimePicker").minDate(e.date);
			 });
			 $("#vToTimeSlot2").on("dp.change", function (e) {
			 $('#vFromTimeSlot2').data("DateTimePicker").maxDate(e.date);
			 });*/

			$('#vFromSatSunTimeSlot1').datetimepicker({
				format: 'HH:mm A',
				ignoreReadonly: true,
			});
			$('#vToSatSunTimeSlot1').datetimepicker({
				format: 'HH:mm A',
				ignoreReadonly: true,
				useCurrent: false //Important! See issue #1075
			});

			$('#vFromSatSunTimeSlot2').datetimepicker({
				format: 'HH:mm A',
				ignoreReadonly: true,
			});
			$('#vToSatSunTimeSlot2').datetimepicker({
				format: 'HH:mm A',
				ignoreReadonly: true,
				useCurrent: false
			});


			/*Offer Apply Type*/
			var fOfferAppyType = $('#fOfferAppyType').val();
			var fOfferTypeval = $('#fOfferType').val();
			if (fOfferAppyType == 'None') {
				$("#fOfferTypeDiv").hide();
				$("#fTargetAmtDiv").hide();
				$("#fOfferAmtDiv").hide();
				$("#fMaxOfferAmtDiv").hide();
				$('#fOfferAmt').removeAttr('required');
			} else {
				$("#fOfferTypeDiv").show();
				$("#fTargetAmtDiv").show();
				$("#fOfferAmtDiv").show();

				if (fOfferAppyType != 'None' && fOfferTypeval == 'Percentage') {
					$("#fMaxOfferAmtDiv").show();
				}

				if (fOfferAppyType != 'None' && fOfferTypeval == 'Flat') {
					$('#fTargetAmt').attr('required', 'required');
				} else {
					$('#fTargetAmt').removeAttr('required');
				}

				$('#fOfferAmt').attr('required', 'required');
			}

			$('#fOfferAppyType').on('change', function () {
				var fOfferAppyTypechange = this.value;
				var fOfferTypevalonchange = $('#fOfferType').val();
				if (fOfferAppyTypechange == 'None') {
					$("#fOfferTypeDiv").hide();
					$("#fTargetAmtDiv").hide();
					$("#fOfferAmtDiv").hide();
					$("#fMaxOfferAmtDiv").hide();
					$('#fOfferAmt').removeAttr('required');
					$('#fTargetAmt').removeAttr('required');
				} else {
					$("#fOfferTypeDiv").show();
					$("#fTargetAmtDiv").show();
					$("#fOfferAmtDiv").show();

					if (fOfferAppyTypechange != 'None' && fOfferTypevalonchange == 'Percentage') {
						$("#fMaxOfferAmtDiv").show();
					}

					if (fOfferAppyTypechange != 'None' && fOfferTypevalonchange == 'Flat') {
						$('#fTargetAmt').attr('required', 'required');
					} else {
						$('#fTargetAmt').removeAttr('required');
					}

					$('#fOfferAmt').attr('required', 'required');
				}
			});

			var sid1 = "<?php echo $iServiceIdNew; ?>";
			if (sid1 != '') {
				changeServiceType(sid1);
			} else {
				var iServiceidvar = $("#iServiceId").val();
				changeServiceType(iServiceidvar);
			}


			var sid = "<?php echo $iServiceIdNew; ?>";
			if (sid == null || sid == "") {
				var sid = $("#iServiceId").val();
			}

			if (sid == '1' && sid != '') {
				$(".servicecatresponsive").show();
			}

			$("#iServiceId").change(function () {
				var iServiceid = $(this).val();
				if (iServiceid == '2' || iServiceid == '3') {
					$(".servicecatresponsive").hide();
					$("#fPricePerPerson").rules("remove", "required");
					// $("#food").hide();
					// $("#WineGrocery").show();
				} else if (iServiceid == '1') {
					$(".servicecatresponsive").show();
					// $("#WineGrocery").hide();
					// $("#food").show();
				}
			});


			/*Offer Type*/

			var fOfferType1 = $('#fOfferType').val();
			var fOfferAppyType1 = $('#fOfferAppyType').val();
			if (fOfferAppyType1 != 'None' && fOfferType1 == 'Percentage') {
				$("#fMaxOfferAmtDiv").show();
				$('#fTargetAmt').removeAttr('required');
				$(".addnote").html("(%)");
			} else {
				$("#fMaxOfferAmtDiv").hide();
				$(".addnote").html("(In <?= $db_currency[0]['vName'] ?>)");
				if (fOfferAppyType1 != 'None' && fOfferType1 == 'Flat') {
					$('#fTargetAmt').attr('required', 'required');
				} else {
					$('#fTargetAmt').removeAttr('required');
				}
			}

			$('#fOfferType').on('change', function () {
				var fOfferAppyType2 = $('#fOfferAppyType').val();
				var fOfferType2 = this.value;
				if (fOfferAppyType2 != 'None' && fOfferType2 == 'Percentage') {
					$("#fMaxOfferAmtDiv").show();
					$('#fTargetAmt').removeAttr('required');
					$(".addnote").html("(%)");
				} else {
					$("#fMaxOfferAmtDiv").hide();

					if (fOfferAppyType2 != 'None' && fOfferType2 == 'Flat') {
						$('#fTargetAmt').attr('required', 'required');
					} else {
						$('#fTargetAmt').removeAttr('required');
					}

					$(".addnote").html("(In <?= $db_currency[0]['vName'] ?>)");
				}
			});

		});


		/*var sid1 = "<?php echo $iServiceIdNew; ?>";
		 if(sid1 != ''){
		 changeServiceTypesecond(sid1);
		 }else{
		 var iServiceidvar = $("#iServiceId").val();
		 changeServiceTypesecond(iServiceidvar);
		 }*/

		$("#iServiceId").change(function () {
			var iServiceid = $(this).val();
			if (iServiceid == '2' || iServiceid == '3') {
				//$(".estimateval").show();
				// $("#food").hide();
				// $("#WineGrocery").show();
			} else if (iServiceid == '1') {
				//$(".estimateval").show();
				// $("#WineGrocery").hide();
				// $("#food").show();
			}
		});

		var sid = "<?php echo $iServiceIdNew; ?>";
		if (sid == '1' && sid != '') {
			//$(".estimateval").show();
			// $("#food").show();
			// $("#WineGrocery").hide();
		} else if ((sid == '2' || sid == '3') && sid != '') {
			//$(".estimateval").show();
			// $("#food").hide();
			// $("#WineGrocery").show();
		}/*else if(sid == '' || sid == null || sid == 0){
		 $(".estimateval").hide();
		 }*/

		$(document).ready(function () {
                     
			$('#js-cuisine-multiple').select2();
			$("#submitBtn").on("click", function (event) {
				// alert("Submit click");
				var isvalidate = $("#_company_form")[0].checkValidity();
                                
                                var basetype =  $("input[name='company_type']:checked"). val();
                                if(basetype == 1)
                                {
                              var checkedCount = $("input[type=checkbox][name^=company_bases]:checked").length;
                                if(checkedCount===0)
                                {
                                    alert('Please select any base location.');
                                    $('#basefor').focus();
                                return false;
                                }
                             }
				// alert(isvalidate);
				if ((isvalidate) && (checkbases)) {
					//event.preventDefault();
                                         
					var vEmail = $("#vEmail").val();
					var vPhone = $("#vPhone").val();
					var iCompanyId = '<?php echo $id ?>';
					if (iCompanyId != '') {
                                            
						$.ajax({
							async: true,
							type: "POST",
							url: '../ajax_check_Email_Country.php',
							dataType: 'html',
							data: {vEmail: vEmail, vPhone: vPhone, iCompanyId: iCompanyId},
							success: function (dataHtml5)
							{
								if ($.trim(dataHtml5) != '') {
									alert($.trim(dataHtml5));
									$("#_company_form").submit();
									return true;
								} else {
									$("#_company_form").submit();
									return true;
								}
							},
							error: function (dataHtml5)
							{

							}
						});
					} else {
						//  alert( "Validation checked and submitted");
                                               
						$("#_company_form").submit();

						return true;
					}
				}
			});
			$('.stylerestypes').find('input[name="company_type"]').change(function(){
                        //alert();
				if($(this).val() == '1'){
					$('.checkrestaurant_type').removeClass('inactive');
					$('.checkrestaurant_type').addClass('active');
				}else{
					$('.checkrestaurant_type').removeClass('active');
					$('.checkrestaurant_type').addClass('inactive');
				}
			});
		});
                
                
                
                
        </script>
    </body>
    <!-- END BODY-->
</html>