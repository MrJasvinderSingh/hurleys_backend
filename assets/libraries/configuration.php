<?php                                                         
$base_url_folder = 'hurleys_backend';
$tconfig["tsite_folder"] = ($_SERVER["HTTP_HOST"] == "localhost")?"/":"/";

if($_SERVER["HTTP_HOST"] == "localhost"){
  $hst_arr = explode("/",$_SERVER["REQUEST_URI"]);
  $hst_var = $hst_arr[1];
  $tconfig["tsite_folder"] = "/".$hst_arr[1]."/"; 
}elseif($_SERVER["HTTP_HOST"] == "13.127.108.115"){
	$tconfig["tsite_folder"] = "/".$base_url_folder."/";
}

if($_SERVER["HTTPS"] == "on"){
  $http = "https://";
} else {
  $http = "http://";
}


$tconfig["tsite_url"] = $http.$_SERVER["HTTP_HOST"].$tconfig["tsite_folder"];
$tconfig["tsite_url_main_admin"] = $http.$_SERVER["HTTP_HOST"].$tconfig["tsite_folder"].'admin/';
$tconfig["tsite_url_admin"] = $http.$_SERVER["HTTP_HOST"].$tconfig["tsite_folder"].'appadmin/';
$tconfig["tpanel_path"] = $_SERVER["DOCUMENT_ROOT"]."".$tconfig["tsite_folder"];
$tconfig["tsite_libraries"] = $tconfig["tsite_url"]."assets/libraries/";
$tconfig["tsite_libraries_v"] = $tconfig["tpanel_path"]."assets/libraries/";
$tconfig["tsite_img"] = $tconfig["tsite_url"]."assets/img";   

$tconfig["tsite_home_images"] = $tconfig["tsite_img"]."/home/";   
$tconfig["tsite_upload_images"] = $tconfig["tsite_img"]."/images/";   
$tconfig["tsite_upload_images_panel"] = $tconfig["tpanel_path"]."assets/img/images/";


//Start ::Company folder
$tconfig["tsite_upload_images_compnay_path"] = $tconfig["tpanel_path"]."webimages/upload/Company";
$tconfig["tsite_upload_images_compnay"] = $tconfig["tsite_url"]."webimages/upload/Company";

$tconfig["tsite_upload_images_pushimage_path"] = $tconfig["tpanel_path"]."webimages/upload/pushimage";
$tconfig["tsite_upload_images_pushimage"] = $tconfig["tsite_url"]."webimages/upload/pushimage";
//End ::Company folder


/* To upload compnay documents */
$tconfig["tsite_upload_compnay_doc_path"] = $tconfig["tpanel_path"]."webimages/upload/documents/company";
$tconfig["tsite_upload_compnay_doc"] = $tconfig["tsite_url"]."webimages/upload/documents/company";
$tconfig["tsite_upload_documnet_size1"] = "250";
$tconfig["tsite_upload_documnet_size2"] = "800";

//Start ::Driver folder
$tconfig["tsite_upload_images_driver_path"] = $tconfig["tpanel_path"]."webimages/upload/Driver";
$tconfig["tsite_upload_images_driver"] = $tconfig["tsite_url"]."webimages/upload/Driver";

/* To upload driver documents */
$tconfig["tsite_upload_driver_doc_path"] = $tconfig["tpanel_path"]."webimages/upload/documents/driver";
$tconfig["tsite_upload_driver_doc"] = $tconfig["tsite_url"]."webimages/upload/documents/driver";

//Start ::Passenger Profile Image
$tconfig["tsite_upload_images_passenger_path"] = $tconfig["tpanel_path"]."webimages/upload/Passenger";
$tconfig["tsite_upload_images_passenger"] = $tconfig["tsite_url"]."webimages/upload/Passenger";


//Start ::Hotel Passenger Profile Image
$tconfig["tsite_upload_images_hotel_passenger_path"] = $tconfig["tpanel_path"]."webimages/upload/Hotel_Passenger";
$tconfig["tsite_upload_images_hotel_passenger"] = $tconfig["tsite_url"]."webimages/upload/Hotel_Passenger";

/* To upload images for static pages */
 $tconfig["tsite_upload_page_images"] = $tconfig["tsite_img"]."/page/";
$tconfig["tsite_upload_page_images_panel"] = $tconfig["tpanel_path"]."assets/img/page";

/* To upload passenger Docunment */
$tconfig["tsite_upload_vehicle_doc"] = $tconfig["tpanel_path"]."webimages/upload/documents/vehicles";
$tconfig["tsite_upload_vehicle_doc_panel"] = $tconfig["tsite_url"]."webimages/upload/documents/vehicles/";

/* To upload driver documents */
//$tconfig["tsite_upload_driver_doc"] = $tconfig["tsite_upload_vehicle_doc"]."driver/";
//$tconfig["tsite_upload_driver_doc_panel"] = $tconfig["tsite_upload_vehicle_doc_panel"]."driver/";


/* To upload images for Appscreenshort pages */
$tconfig["tsite_upload_apppage_images"] = $tconfig["tpanel_path"]."webimages/upload/Appscreens/";
$tconfig["tsite_upload_apppage_images_panel"] = $tconfig["tsite_url"]."webimages/upload/Appscreens/";


//Start ::Vehicle Type
$tconfig["tsite_upload_images_vehicle_type_path"] = $tconfig["tpanel_path"]."webimages/icons/VehicleType";
$tconfig["tsite_upload_images_vehicle_type"] = $tconfig["tsite_url"]."webimages/icons/VehicleType";
$tconfig["tsite_upload_images_vehicle_type_size1_android"] = "60";
$tconfig["tsite_upload_images_vehicle_type_size2_android"] = "90";
$tconfig["tsite_upload_images_vehicle_type_size3_both"] = "120";
$tconfig["tsite_upload_images_vehicle_type_size4_android"] = "180";
$tconfig["tsite_upload_images_vehicle_type_size5_both"] = "240";
$tconfig["tsite_upload_images_vehicle_type_size5_ios"] = "360";


$tconfig["tsite_upload_images_member_size1"] = "64";
$tconfig["tsite_upload_images_member_size2"] = "150";
$tconfig["tsite_upload_images_member_size3"] = "256";
$tconfig["tsite_upload_images_member_size4"] = "512"; 


//Start ::Vehicle category
$tconfig["tsite_upload_images_vehicle_category_path"] = $tconfig["tpanel_path"]."webimages/icons/VehicleCategory";
$tconfig["tsite_upload_images_vehicle_category"] = $tconfig["tsite_url"]."webimages/icons/VehicleCategory";
$tconfig["tsite_upload_images_vehicle_category_size1_android"] = "60";
$tconfig["tsite_upload_images_vehicle_category_size2_android"] = "90";
$tconfig["tsite_upload_images_vehicle_category_size3_both"] = "120";
$tconfig["tsite_upload_images_vehicle_category_size4_android"] = "180";
$tconfig["tsite_upload_images_vehicle_category_size5_both"] = "240";
$tconfig["tsite_upload_images_vehicle_category_size5_ios"] = "360";


$tconfig["tsite_upload_images_cover_size1"] = "512";
$tconfig["tsite_upload_images_cover_size2"] = "800";
$tconfig["tsite_upload_images_cover_size3"] = "1000";
$tconfig["tsite_upload_images_cover_size4"] = "1200";   
$tconfig["tsite_upload_images_cover_size5"] = "1500"; 
/* To upload images for trips */
$tconfig["tsite_upload_trip_images_path"] = $tconfig["tpanel_path"]."webimages/upload/beforeafter/";
$tconfig["tsite_upload_trip_images"] = $tconfig["tsite_url"]."webimages/upload/beforeafter/"; 

/* To upload images for order proof */
$tconfig["tsite_upload_order_images_path"] = $tconfig["tpanel_path"]."webimages/upload/order_proof/";
$tconfig["tsite_upload_order_images"] = $tconfig["tsite_url"]."webimages/upload/order_proof/"; 
/* To upload images for order posttip proof */
$tconfig["tsite_upload_order_images_path_sign"] = $tconfig["tpanel_path"]."webimages/upload/order_proof/sign/";
/* To upload images for order Invoice proof */
$tconfig["tsite_upload_order_images_path_invoice"] = $tconfig["tpanel_path"]."webimages/upload/order_proof/invoices/";
$tconfig["tsite_upload_order_images_invoice"] = $tconfig["tsite_url"]."webimages/upload/order_proof/invoices/";
/* For Back-up Database*/
$tconfig["tsite_upload_files_db_backup_path"] = $tconfig["tpanel_path"]."webimages/upload/backup/";
$tconfig["tsite_upload_files_db_backup"] = $tconfig["tsite_url"]."webimages/upload/backup/"; 

/* To upload preference images */
$tconfig["tsite_upload_preference_image"] = $tconfig["tpanel_path"]."webimages/upload/preferences/";
$tconfig["tsite_upload_preference_image_panel"] = $tconfig["tsite_url"]."webimages/upload/preferences/";
/*Home Page Image Size*/
$tconfig["tsite_upload_images_home"] = "300";

/* To upload images for trip delivery signatures */
$tconfig["tsite_upload_trip_signature_images_path"] = $tconfig["tpanel_path"]."webimages/upload/trip_signature/";
$tconfig["tsite_upload_trip_signature_images"] = $tconfig["tsite_url"]."webimages/upload/trip_signature/"; 

/* To upload images for serive categories */
$tconfig["tsite_upload_service_categories_images_path"] = $tconfig["tpanel_path"]."webimages/upload/ServiceCategories/";
$tconfig["tsite_upload_service_categories_images"] = $tconfig["tsite_url"]."webimages/upload/ServiceCategories/"; 

//Start ::Food Menu
$tconfig["tsite_upload_images_food_menu_path"] = $tconfig["tpanel_path"]."webimages/upload/FoodMenu";
$tconfig["tsite_upload_images_food_menu"] = $tconfig["tsite_url"]."webimages/upload/FoodMenu";
$tconfig["tsite_upload_images_food_menu_size1_android"] = "60";
$tconfig["tsite_upload_images_food_menu_size2_android"] = "90";
$tconfig["tsite_upload_images_food_menu_size3_both"] = "120";
$tconfig["tsite_upload_images_food_menu_size4_android"] = "180";
$tconfig["tsite_upload_images_food_menu_size5_both"] = "240";
$tconfig["tsite_upload_images_food_menu_size5_ios"] = "360";

//Start ::Menu Items
$tconfig["tsite_upload_images_menu_item_path"] = $tconfig["tpanel_path"]."webimages/upload/MenuItem";
$tconfig["tsite_upload_images_menu_item"] = $tconfig["tsite_url"]."webimages/upload/MenuItem";
$tconfig["tsite_upload_images_menu_item_size1_android"] = "60";
$tconfig["tsite_upload_images_menu_item_size2_android"] = "90";
$tconfig["tsite_upload_images_menu_item_size3_both"] = "120";
$tconfig["tsite_upload_images_menu_item_size4_android"] = "180";
$tconfig["tsite_upload_images_menu_item_size5_both"] = "240";
$tconfig["tsite_upload_images_menu_item_size5_ios"] = "360";

### ==================================== label configuration =========================================
$service_categories_ids_arr = [1];

$Lsql = "SELECT vCode,vTitle FROM language_master WHERE eDefault = 'Yes'";
$Data_lang_arr = $obj->MySQLSelect($Lsql);

$langugaeCode = $Data_lang_arr[0]['vCode'];
$enablesevicescategory = implode(",", $service_categories_ids_arr);

$Ssql="SELECT iServiceId,vServiceName_".$langugaeCode." as vServiceName,vImage FROM  `service_categories` WHERE iServiceId IN (".$enablesevicescategory.") AND eStatus='Active' order by iDisplayOrder ASC";
$ServiceData = $obj->MySQLSelect($Ssql);

$serviceCategoriesTmp = array();
foreach ($ServiceData as $key => $value) {
	if($value['vImage'] != ''){
		$value['vImage'] = $tconfig["tsite_upload_service_categories_images"].$value['vImage'];
	}
	$serviceCategoriesTmp[] = $value;
}

$iServiceId = isset($_REQUEST["iServiceId"]) ? $_REQUEST["iServiceId"] : $ServiceData[0]['iServiceId'];
if(empty($_REQUEST["iServiceId"])){
	$iServiceId = $ServiceData[0]['iServiceId'];
	$_REQUEST["iServiceId"] = $iServiceId;
}

define('serviceCategories', json_encode($serviceCategoriesTmp));

//if($_SESSION['sess_from'] == 'web'){
	if($_SESSION['sess_user'] == 'company'){
		$query = "SELECT iServiceId FROM company WHERE iCompanyId = '".$_SESSION['sess_iUserId']."'";
		$dbQueryData=$obj->MySQLSelect($query);
		if(count($dbQueryData) > 0){
			$iServiceIdWeb = $dbQueryData[0]['iServiceId'];
		} else {
			$iServiceIdWeb = $ServiceData[0]['iServiceId'];
		}
	} else {
		$iServiceIdWeb = $ServiceData[0]['iServiceId'];
	}

	$sql="SELECT vLabel,vValue,LanguageLabelId FROM language_label_".$iServiceIdWeb." WHERE vCode='".$_SESSION['sess_lang']."'";
	$db_lbl=$obj->MySQLSelect($sql);
	foreach ($db_lbl as $key => $value) {
		if(isset($_SESSION['sess_editingToken']) && $_SESSION['sess_editingToken'] == $db_config[0]['vValue']){
			$langage_lbl[$value['vLabel']] = "<em class='label-dynmic'><i class='fa fa-edit label-i' data-id='".$value['LanguageLabelId']."' data-value='main'></i>".$value['vValue']."</em>";
		}else {
			$langage_lbl[$value['vLabel']] = $value['vValue'];
		}
	} 

	if(empty($langage_lbl)){
		$sql="select vLabel,vValue,LanguageLabelId from language_label where vCode='".$_SESSION['sess_lang']."'";
		$db_lbl=$obj->MySQLSelect($sql);
		foreach ($db_lbl as $key => $value) {
		 if(isset($_SESSION['sess_editingToken']) && $_SESSION['sess_editingToken'] == $db_config[0]['vValue']){
		  $langage_lbl[$value['vLabel']] = "<em class='label-dynmic'><i class='fa fa-edit label-i' data-id='".$value['LanguageLabelId']."' data-value='other'></i>".$value['vValue']."</em>";
		 }else {
		  $langage_lbl[$value['vLabel']] = $value['vValue'];
		 }
		}

	}
//}

if($ServiceData[0]['iServiceId'] > 0){
	$iServiceIdWeb = $ServiceData[0]['iServiceId'];

	$sql="select vLabel,vValue from language_label_".$iServiceIdWeb." where vCode='EN'";
	$db_lbl_admin=$obj->MySQLSelect($sql);
	    
	foreach ($db_lbl_admin as $key => $value) {
	     $langage_lbl_admin[$value['vLabel']] = $value['vValue'];            
	}
}
### ==================================== label configuration =========================================
?>
