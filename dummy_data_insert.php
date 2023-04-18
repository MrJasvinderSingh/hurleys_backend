<?php

	include_once("common.php");
  
  if($_SERVER["HTTP_HOST"] == "192.168.1.131"){
    $hst_arr = explode("/",$_SERVER["REQUEST_URI"]);
    $hst_var = $hst_arr[1];
  }else{
    $HTTP_HOST = $_SERVER['HTTP_HOST'];
    $HTTP_HOST = explode(".",$HTTP_HOST);
    $hst_var = $HTTP_HOST[0]; 
  }
  if($hst_var == "deliverall"){
    $host_system = "deliverall";
  }else{
    $host_system = "foodapp";
  }
  require_once(TPATH_CLASS . "/Imagecrop.class.php");
  $thumb = new thumbnail();

	$meta_arr = $generalobj->getsettingSeo(2);

	$sql = "SELECT * from language_master where eStatus = 'Active'" ;

	$db_lang = $obj->MySQLSelect($sql);

	$sql = "SELECT * from country where eStatus = 'Active'" ;

	$db_code = $obj->MySQLSelect($sql);
  
  $sql = "SELECT * FROM country WHERE eStatus = 'Active' ORDER BY  vCountry ASC";
	$db_country = $obj->MySQLSelect($sql);

	//echo "<pre>";print_r($db_lang);

	$script="Contact Us";

	$add = "";

	$vName = base64_decode($_REQUEST['1']);

	$vName = explode(" ",$vName);

	$vName0 = $vName['0'];

	if($vName[1] == "")

	$_vLastName = "";	

	else

	$_vLastName = $vName[1];



	$vEmail = base64_decode($_REQUEST['2']);

	$vPhone = base64_decode($_REQUEST['3']);
  
  $vRestuarantLocation = $_REQUEST['vRestuarantLocation'];
  
  $vRestuarantLocationLat = $_REQUEST['vRestuarantLocationLat'];
  
  $vRestuarantLocationLong = $_REQUEST['vRestuarantLocationLong'];
  
  function recurse_copy($src,$dst) {
      $dir = opendir($src);
      @mkdir($dst);
      while(false !== ( $file = readdir($dir)) ) {
      if (( $file != '.' ) && ( $file != '..' )) {
      if ( is_dir($src . '/' . $file) ) {
      recurse_copy($src . '/' . $file,$dst . '/' . $file);
      }
      else {
      copy($src . '/' . $file,$dst . '/' . $file);
      }
      }
      }
      closedir($dir);
   }

     

	if(isset($_POST['action']) && $_POST['action'] == 'send_mail')

	{

		unset($_POST['action']);

		$maildata = array();

		$maildata['EMAIL'] = $_POST['vEmail'];

		$maildata['NAME'] = $_POST['vName']." ".$_POST['vLastName'];

		$maildata['PASSWORD'] = '123456';

		//$generalobj->send_email_user("DRIVER_REGISTRATION_ADMIN",$maildata);

		$generalobj->send_email_user("DRIVER_REGISTRATION_USER",$maildata);

	}

	if(isset($_POST['action']) && $_POST['action'] == 'add_dummy')

	{

		unset($_POST['action']);

		$email = $_POST['vEmail'];

		$msg= $generalobj->checkDuplicateFront('vEmail', 'register_driver' , Array('vEmail'),$tconfig["tsite_url"]."dummy_data_insert.php?error=1&var_msg=Email already Exists", "Email already Exists","" ,"");
    $msg= $generalobj->checkDuplicateFront('vEmail', 'company' , Array('vEmail'),$tconfig["tsite_url"]."dummy_data_insert.php?error=1&var_msg=Email already Exists", "Email already Exists","" ,"");
    $msg= $generalobj->checkDuplicateFront('vEmail', 'register_user' , Array('vEmail'),$tconfig["tsite_url"]."dummy_data_insert.php?error=1&var_msg=Email already Exists", "Email already Exists","" ,"");
		#echo "<pre>";print_r($_POST); die;

		//Insert Driver
    
    $vCountry = $_POST['vCountry']; 
    $sql = "SELECT vPhoneCode,vTimeZone FROM country WHERE vCountryCode = '".$vCountry."'";
  	$db_country_code = $obj->MySQLSelect($sql);

		$eReftype1 = "Driver";

		$Data1['vRefCode'] = $generalobj->ganaraterefercode($eReftype1);

		$Data1['iRefUserId'] = '';

		$Data1['eRefType'] = '';

		$Data1['vName'] = $_POST['vName'];

		$Data1['vLastName'] = (isset($_POST['vLastName']) && $_POST['vLastName'] != '')?$_POST['vLastName']:'';

		$Data1['vLang'] = 'EN';

		$Data1['vPassword'] = $generalobj->encrypt_bycrypt('123456');

		$Data1['vEmail'] = $_POST['vEmail'];

		$Data1['dBirthDate'] = '1992-02-02';

		$Data1['vPhone'] = (isset($_POST['vPhone']) && $_POST['vPhone'] != '')?$_POST['vPhone']:'9876543210';

		$Data1['vCaddress'] = "test address";

		$Data1['vCadress2'] = "test address";

		$Data1['vCity'] = "test city";

		$Data1['vZip'] = "121212";

		$Data1['vCountry'] = $vCountry;

		$Data1['vCode'] = $db_country_code[0]['vPhoneCode'];;

		$Data1['vFathersName'] = 'test';

		$Data1['vCompany'] = 'test';

		$Data1['tRegistrationDate']=Date('Y-m-d H:i:s');

		$Data1['eStatus'] = 'Active';

		$Data1['vCurrencyDriver'] = 'USD';

		$Data1['iCompanyId'] = 1;

		$Data1['eEmailVerified'] = 'Yes';

		$Data1['ePhoneVerified'] = 'Yes';

		//echo "<pre>";print_r($Data1); echo "</pre>";

		$id = $obj->MySQLQueryPerform('register_driver',$Data1,'insert');
    $generalobj->InsertIntoUserWallet($id,"Driver","10000","Credit",0,"Deposit","#LBL_AMOUNT_CREDIT#","Unsettelled",$Data1['tRegistrationDate'],0);

		//Add Driver Vehicle

		if($id != "") {

			if($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX'){
      
       $Drive_vehicle['iDriverId'] = $id;
			 $Drive_vehicle['iCompanyId'] = "1";
			 $Drive_vehicle['iMakeId'] = "3";
			 $Drive_vehicle['iModelId'] = "1";
			 $Drive_vehicle['iYear'] = Date('Y');
			 $Drive_vehicle['eStatus'] = "Active";
			 $Drive_vehicle['eCarX'] = "Yes";
			 $Drive_vehicle['eCarGo'] = "Yes";
       $Drive_vehicle['vLicencePlate'] = "My Services";
       $Drive_vehicle['eType'] = "UberX";
       
       $query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'UberX'";
			 $result = $obj->MySQLSelect($query);
       $Drive_vehicle['vCarType'] = $result[0]['countId'];
       $iDriver_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle,'insert');
       
       if($APP_TYPE == 'UberX'){ 
    			$sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_VehicleId."' WHERE iDriverId='".$id."'";
    			$obj->sql_query($sql);
       }
       
       $days =  array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
			 foreach ($days as $value) {
					$data_avilability['iDriverId'] = $id;
					$data_avilability['vDay'] = $value;
					$data_avilability['vAvailableTimes'] = '08-09,09-10,10-11,11-12,12-13,13-14,14-15,15-16,16-17,17-18,18-19,19-20,20-21,21-22';
					$data_avilability['dAddedDate'] = @date('Y-m-d H:i:s');
					$data_avilability['eStatus'] = 'Active';
					$data_avilability_add = $obj->MySQLQueryPerform('driver_manage_timing',$data_avilability,'insert');
			 }  
       
       if($APP_TYPE == 'Ride-Delivery-UberX'){
              $query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'Ride'";
    					$result_ride = $obj->MySQLSelect($query);
              $Drive_vehicle_ride['iDriverId'] = $id;
    					$Drive_vehicle_ride['iCompanyId'] = "1";
    					$Drive_vehicle_ride['iYear'] = "2014";
    					$Drive_vehicle_ride['vLicencePlate'] = "CK201";
    					$Drive_vehicle_ride['eStatus'] = "Active";
    					$Drive_vehicle_ride['eCarX'] = "Yes";
    					$Drive_vehicle_ride['eCarGo'] = "Yes";	
              $Drive_vehicle_ride['eType'] = "Ride";
              $Drive_vehicle_delivery = $Drive_vehicle_ride;
              $Drive_vehicle_ride['iMakeId'] = "3";
    					$Drive_vehicle_ride['iModelId'] = "1"; 	
    					$Drive_vehicle_ride['vCarType'] = $result_ride[0]['countId'];
    					$iDriver_Ride_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle_ride,'insert');
              
              $sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_Ride_VehicleId."' WHERE iDriverId='".$id."'";
    					$obj->sql_query($sql);
              
              $query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'Deliver'";
    					$result_delivery = $obj->MySQLSelect($query);
              $Drive_vehicle_delivery['iMakeId'] = "5";
    					$Drive_vehicle_delivery['iModelId'] = "9";
              $Drive_vehicle_delivery['eType'] = "Delivery";
              $Drive_vehicle_delivery['vCarType'] = $result_delivery[0]['countId'];
              $iDriver_Delivery_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle_delivery,'insert');
        }
      
              /*
				$query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`";

				$result = $obj->MySQLSelect($query);

				

				$Drive_vehicle['iDriverId'] = $id;

				$Drive_vehicle['iCompanyId'] = "1";

				$Drive_vehicle['iMakeId'] = "3";

				$Drive_vehicle['iModelId'] = "1";

				$Drive_vehicle['iYear'] = Date('Y');

				$Drive_vehicle['vLicencePlate'] = "My Services";

				$Drive_vehicle['eStatus'] = "Active";

				$Drive_vehicle['eCarX'] = "Yes";

				$Drive_vehicle['eCarGo'] = "Yes";		

				$Drive_vehicle['vCarType'] = $result[0]['countId'];

				$iDriver_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle,'insert');

				$sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_VehicleId."' WHERE iDriverId='".$id."'";

				$obj->sql_query($sql);

				

				if($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes"){

					$sql="select iVehicleTypeId,iVehicleCategoryId,eFareType,fFixedFare,fPricePerHour from vehicle_type where 1=1";

					$data_vehicles = $obj->MySQLSelect($sql);

					//echo "<pre>";print_r($data_vehicles);exit;

					

					if($data_vehicles[$i]['eFareType'] != "Regular")

					{

						for($i=0 ; $i < count($data_vehicles); $i++){

							$Data_service['iVehicleTypeId'] = $data_vehicles[$i]['iVehicleTypeId'];

							$Data_service['iDriverVehicleId'] = $iDriver_VehicleId;

							

							if($data_vehicles[$i]['eFareType'] == "Fixed"){

								$Data_service['fAmount'] = $data_vehicles[$i]['fFixedFare'];

							}

							else if($data_vehicles[$i]['eFareType'] == "Hourly"){

								$Data_service['fAmount'] = $data_vehicles[$i]['fPricePerHour'];

							}

							$data_service_amount = $obj->MySQLQueryPerform('service_pro_amount',$Data_service,'insert');

						}

					}

				}
				$days =  array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
				foreach ($days as $value) {
					$data_avilability['iDriverId'] = $id;
					$data_avilability['vDay'] = $value;
					$data_avilability['vAvailableTimes'] = '08-09,09-10,10-11,11-12,12-13,13-14,14-15,15-16,16-17,17-18,18-19,19-20,20-21,21-22';
					$data_avilability['dAddedDate'] = Date('Y-m-d H:i:s');
					$data_avilability['eStatus'] = 'Active';
					$data_avilability_add = $obj->MySQLQueryPerform('driver_manage_timing',$data_avilability,'insert');
				}
       */
			} else {

			$query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`";

			$result = $obj->MySQLSelect($query);

			$Drive_vehicle['iDriverId'] = $id;

			$Drive_vehicle['iCompanyId'] = "1";

			$Drive_vehicle['iMakeId'] = "5";

			$Drive_vehicle['iModelId'] = "9";

			$Drive_vehicle['iYear'] = "2014";

			$Drive_vehicle['vLicencePlate'] = "CK201";

			$Drive_vehicle['eStatus'] = "Active";

			$Drive_vehicle['eCarX'] = "Yes";

			$Drive_vehicle['eCarGo'] = "Yes";		

			$Drive_vehicle['vCarType'] = $result[0]['countId'];

			$iDriver_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle,'insert');

			$sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_VehicleId."' WHERE iDriverId='".$id."'";

			$obj->sql_query($sql);

			}

		}	



		



		//Insert Company


   #################### Insert Restaurant Records For Food App #########################################################################################
   if($host_system == "foodapp"){
		//$Data2['vName'] = $_POST['vName'];
		//$Data2['vLastName'] = $_POST['vLastName'];
   $Data2['vContactName'] = $_POST['vName']." ".$_POST['vLastName']; 
   $Data2['vPassword'] = $generalobj->encrypt_bycrypt('123456');
   $Data2['vCompany'] = $_POST['vCompany'];
   $Data2['iServiceId'] = 1;
   $Data2['vCaddress'] = $vRestuarantLocation;
   $Data2['vEmail'] = "rest-".$_POST['vEmail'];
   $Data2['vPhone'] = (isset($_POST['vPhone']) && $_POST['vPhone'] != '')?$_POST['vPhone']:'9876543210';
   $Data2['eStatus'] = 'Active';
   $Data2['vLang'] = 'EN';
   $Data2['vCurrencyCompany'] = 'USD';
   $Data2['vCountry'] = $_POST['vCountry'];
   $vCountry = $_POST['vCountry']; 
   $sql = "SELECT vPhoneCode,vTimeZone FROM country WHERE vCountryCode = '".$vCountry."'";
	 $db_country_code = $obj->MySQLSelect($sql);
	 $Data2['vCode'] = $db_country_code[0]['vPhoneCode'];
   $Data2['vRestuarantLocation'] = $vRestuarantLocation;
   $Data2['vRestuarantLocationLat'] = $vRestuarantLocationLat;
   $Data2['vRestuarantLocationLong'] = $vRestuarantLocationLong;
   $Data2['eEmailVerified'] = "Yes";
   $Data2['ePhoneVerified'] = "Yes";
   $Data2['vFromMonFriTimeSlot1'] = "00:01:01";
   $Data2['vToMonFriTimeSlot1'] = "14:00:00";
   $Data2['vFromMonFriTimeSlot2'] = "14:00:01";
   $Data2['vToMonFriTimeSlot2'] = "23:59:59";
   $Data2['vFromSatSunTimeSlot1'] = "00:01:01";
   $Data2['vToSatSunTimeSlot1'] = "14:00:00";
   $Data2['vFromSatSunTimeSlot2'] = "14:00:01";
   $Data2['vToSatSunTimeSlot2'] = "23:59:59";
   $Data2['fMinOrderValue'] = 10;
   $Data2['fPackingCharge'] = 2;
   $Data2['vRadius'] = 2;
   $Data2['fPrepareTime'] = 15;
   $Data2['fOfferAppyType'] = "All";
   $Data2['fOfferType'] = "Percentage";
   $Data2['fOfferAmt'] = 10;
   $Data2['tRegistrationDate'] = @date("Y-m-d H:i:s");
   $Data2['fPricePerPerson'] = 50;
   $Data2['eAvailable'] = "Yes";
   $Data2['iMaxItemQty'] = 25;
   $Data2['vTimeZone'] = $db_country_code[0]['vTimeZone'];
   $Data2['eLogout'] = "No";
   $Data2['vAvgRating'] = 4.5;
   $Company_id = $obj->MySQLQueryPerform('company',$Data2,'insert');
   
   $img_path = $tconfig["tsite_upload_images_compnay_path"];
   $temp_gallery = $img_path . '/1/';
   $Photo_Gallery_folder = $img_path . '/' . $Company_id . '/';
   if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
   }
   recurse_copy($temp_gallery,$Photo_Gallery_folder);
   
   $sql = "SELECT vImage,vCoverImage FROM company WHERE iCompanyId = '1'";
	 $db_image = $obj->MySQLSelect($sql);
   $vImage = $db_image[0]['vImage'];
   $vCoverImage = $db_image[0]['vCoverImage']; 
   $update_sql = "UPDATE company set vImage = '".$vImage."', vCoverImage = '".$vCoverImage."' WHERE iCompanyId ='".$Company_id."'";
	 $result = $obj->sql_query($update_sql);
   
   $cuisinedata['iCompanyId'] = $Company_id;
   $cuisinedata['cuisineId'] = 3;
   $cuisinedata_id = $obj->MySQLQueryPerform('company_cuisine',$cuisinedata,'insert');
  
   $FoodMenuData['iCompanyId'] = $Company_id;
   $FoodMenuData['vMenu_EN'] = "Burger";
   $FoodMenuData['vMenuDesc_EN'] = "Burger";
   $FoodMenuData['iDisplayOrder'] = "1";
   $FoodMenuData['eStatus'] = "Active";
   $FoodMenuData_id = $obj->MySQLQueryPerform('food_menu',$FoodMenuData,'insert');
   
   $sql = "SELECT * FROM menu_items WHERE iMenuItemId = '1'";
	 $db_menu_items = $obj->MySQLSelect($sql);
   
   array_shift($db_menu_items[0]);
   
   $Data_Menu_Items = array();
	 $Data_Menu_Items = $db_menu_items[0];
	 $Data_Menu_Items['iFoodMenuId'] = $FoodMenuData_id;
   $Data_Menu_Items['vItemType_ES'] = "Veg Burger";
   $Data_Menu_Items['vItemType_EN'] = "Veg Burger";
   $Data_Menu_Items['vItemType_FN'] = "Veg Burger";   
   $Data_Menu_Items['eStatus'] = "Active";
   $Data_Menu_Items['eAvailable'] = "Yes";
   $Data_Menu_Items['eRecommended'] = "Yes";
   $Data_Menu_Items['eBestSeller'] = "Yes";
   $Data_Menu_Items['vHighlightName'] = "BestSeller";
	 $Data_Menu_Items_id = $obj->MySQLQueryPerform("menu_items",$Data_Menu_Items,'insert');
   
   $sql = "SELECT * FROM menuitem_options WHERE iMenuItemId = '1'";
	 $db_menu_items_options = $obj->MySQLSelect($sql);
   
   for($i=0 ; $i < count($db_menu_items_options) ; $i++)
	 {
			array_shift($db_menu_items_options[$i]);
      
      $Data_Menu_Items_Options = array();
  	  $Data_Menu_Items_Options = $db_menu_items_options[$i];
  	  $Data_Menu_Items_Options['iMenuItemId'] = $Data_Menu_Items_id;
  	  $Data_Menu_Items_Options_id = $obj->MySQLQueryPerform("menuitem_options",$Data_Menu_Items_Options,'insert');
	 }
   

		//echo "<pre>";print_r($Data2); echo "</pre>";



		//$id = $obj->MySQLQueryPerform('company',$Data2,'insert');
    
    $sql = "SELECT * FROM food_menu WHERE iCompanyId = '1' AND eStatus = 'Active' AND iFoodMenuId NOT IN(1)";
    $db_comp_food_menu = $obj->MySQLSelect($sql);
    
    if(count($db_comp_food_menu) > 0){
       for($i=0 ; $i < count($db_comp_food_menu) ; $i++)
    	 {
          $iFoodMenuId = $db_comp_food_menu[$i]['iFoodMenuId']; 
          array_shift($db_comp_food_menu[$i]);
          
          $Data_Food_Menu = array();
          $Data_Food_Menu = $db_comp_food_menu[$i];
          $Data_Food_Menu['iCompanyId'] = $Company_id;
          $Data_Food_Menu_id = $obj->MySQLQueryPerform("food_menu",$Data_Food_Menu,'insert');  // Insert Food Menu
          if($Data_Food_Menu_id > 0){
             $sqlm = "SELECT * FROM menu_items WHERE iFoodMenuId = '".$iFoodMenuId."'";
    	       $db_food_menu_items = $obj->MySQLSelect($sqlm);
             if(count($db_food_menu_items) > 0){
                for($j=0 ; $j < count($db_food_menu_items) ; $j++){
                    $iMenuItemId = $db_food_menu_items[$j]['iMenuItemId'];
                    array_shift($db_food_menu_items[$j]);
                    
                    $Data_Food_Menu_Items = array();
                    $Data_Food_Menu_Items = $db_food_menu_items[$j];
                    $Data_Food_Menu_Items['iFoodMenuId'] = $Data_Food_Menu_id;
                    $Data_Food_Menu_Items_id = $obj->MySQLQueryPerform("menu_items",$Data_Food_Menu_Items,'insert');  // Insert Food Menu Items
                    if($Data_Food_Menu_Items_id > 0){
                       $sqlo = "SELECT * FROM menuitem_options WHERE iMenuItemId = '".$iMenuItemId."'";
    	                 $db_food_menu_item_options = $obj->MySQLSelect($sqlo);
                       if(count($db_food_menu_item_options) > 0){
                         for($k=0 ; $k < count($db_food_menu_item_options) ; $k++){
                           array_shift($db_food_menu_item_options[$k]);
                           
                           $Data_Food_Menu_Item_Options = array();
                           $Data_Food_Menu_Item_Options = $db_food_menu_item_options[$k];
                           $Data_Food_Menu_Item_Options['iMenuItemId'] = $Data_Food_Menu_Items_id;
                           $Data_Food_Menu_Item_Options_id = $obj->MySQLQueryPerform("menuitem_options",$Data_Food_Menu_Item_Options,'insert');
                         }  
                       }  
                    }
                }
             }
          }
       }
    }

   ###################### Insert Restaurant Records For Food App #########################################################################################
  }else{
   ###################### Insert Restaurant Records For DeliverAll App #########################################################################################
   $sql = "SELECT iServiceId FROM `service_categories` WHERE eStatus = 'Active'";
   $db_service_categories = $obj->MySQLSelect($sql);
   if(count($db_service_categories) > 0){
     for($kk=0;$kk<count($db_service_categories);$kk++){
        $iServiceId = $db_service_categories[$kk]['iServiceId'];
        $Data2['iServiceId'] = $iServiceId;
        $Data2['vContactName'] = $_POST['vName']." ".$_POST['vLastName']; 
        $Data2['vPassword'] = $generalobj->encrypt_bycrypt('123456');
        $Data2['vCompany'] = $_POST['vCompany'];
        $Data2['vCaddress'] = $vRestuarantLocation;
        if($iServiceId == 1){
          $Data2['vEmail'] = "rest-".$_POST['vEmail'];
        }else if($iServiceId == 2){
          $Data2['vEmail'] = "groc-".$_POST['vEmail'];
        }else if($iServiceId == 3){
          $Data2['vEmail'] = "liq-".$_POST['vEmail'];
        }
        $Data2['vPhone'] = (isset($_POST['vPhone']) && $_POST['vPhone'] != '')?$_POST['vPhone']:'9876543210';
        $Data2['eStatus'] = 'Active';
        $Data2['vLang'] = 'EN';
        $Data2['vCurrencyCompany'] = 'USD';
        $Data2['vCountry'] = $_POST['vCountry'];
        $vCountry = $_POST['vCountry'];
        $sql = "SELECT vPhoneCode,vTimeZone FROM country WHERE vCountryCode = '".$vCountry."'";
    	  $db_country_code = $obj->MySQLSelect($sql);
    	  $Data2['vCode'] = $db_country_code[0]['vPhoneCode'];
        $Data2['vRestuarantLocation'] = $vRestuarantLocation;
        $Data2['vRestuarantLocationLat'] = $vRestuarantLocationLat;
        $Data2['vRestuarantLocationLong'] = $vRestuarantLocationLong;
        $Data2['eEmailVerified'] = "Yes";
        $Data2['ePhoneVerified'] = "Yes";
        $Data2['vFromMonFriTimeSlot1'] = "00:01:01";
        $Data2['vToMonFriTimeSlot1'] = "14:00:00";
        $Data2['vFromMonFriTimeSlot2'] = "14:00:01";
        $Data2['vToMonFriTimeSlot2'] = "23:59:59";
        $Data2['vFromSatSunTimeSlot1'] = "00:01:01";
        $Data2['vToSatSunTimeSlot1'] = "14:00:00";
        $Data2['vFromSatSunTimeSlot2'] = "14:00:01";
        $Data2['vToSatSunTimeSlot2'] = "23:59:59";
        $Data2['fMinOrderValue'] = 10;
        $Data2['fPackingCharge'] = 2;
        $Data2['vRadius'] = 2;
        $Data2['fPrepareTime'] = 15;
        $Data2['fOfferAppyType'] = "All";
        $Data2['fOfferType'] = "Percentage";
        $Data2['fOfferAmt'] = 10;
        $Data2['tRegistrationDate'] = @date("Y-m-d H:i:s");
        $Data2['fPricePerPerson'] = 50;
        $Data2['eAvailable'] = "Yes";
        $Data2['iMaxItemQty'] = 25;
        $Data2['vTimeZone'] = $db_country_code[0]['vTimeZone'];
        $Data2['eLogout'] = "No";
        $Data2['vAvgRating'] = 4.5;
        $Company_id = $obj->MySQLQueryPerform('company',$Data2,'insert'); 
        if($iServiceId == 1){
           $Company_Image_Id = 1;
           $Company_Cuisine_Id = 3;
           $Company_Food_Menu_Id = 2;
        }else if($iServiceId == 2){
           //$Company_Image_Id = 33;
           $Company_Image_Id = 109;
           $Company_Cuisine_Id = 3;
           $Company_Food_Menu_Id = 55;
        }else if($iServiceId == 3){
           //$Company_Image_Id = 28;
           $Company_Image_Id = 111;
           $Company_Cuisine_Id = 3;
           $Company_Food_Menu_Id = 39;
        }
        $img_path = $tconfig["tsite_upload_images_compnay_path"];
        $temp_gallery = $img_path . '/'.$Company_Image_Id.'/';
        $Photo_Gallery_folder = $img_path . '/' . $Company_id . '/';
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }
        recurse_copy($temp_gallery,$Photo_Gallery_folder);
        $sql = "SELECT vImage,vCoverImage FROM company WHERE iCompanyId = '".$Company_Image_Id."'";
    	  $db_image = $obj->MySQLSelect($sql);
        $vImage = $db_image[0]['vImage'];
        $vCoverImage = $db_image[0]['vCoverImage']; 
        $update_sql = "UPDATE company set vImage = '".$vImage."', vCoverImage = '".$vCoverImage."' WHERE iCompanyId ='".$Company_id."'";
    	  $result = $obj->sql_query($update_sql);  
        $sql = "SELECT * FROM `cuisine` WHERE iServiceId = '".$iServiceId."' AND eStatus = 'Active'";
        $db_cuisine = $obj->MySQLSelect($sql);
        if(count($db_cuisine) > 0){
          for($t=0;$t<count($db_cuisine);$t++){
             $Data_Company_Cuisine['iCompanyId'] = $Company_id;
             $Data_Company_Cuisine['cuisineId'] = $db_cuisine[$t]['cuisineId'];
             $Data_Company_Cuisine_id = $obj->MySQLQueryPerform("company_cuisine",$Data_Company_Cuisine,'insert');  // Insert Company Cuisine
          }
        }  
        $sql = "SELECT * FROM `food_menu` WHERE iCompanyId = '".$Company_Image_Id."' AND eStatus = 'Active'";
    	  $db_comp_food_menu = $obj->MySQLSelect($sql);
        if(count($db_comp_food_menu) > 0){
           for($i=0 ; $i < count($db_comp_food_menu) ; $i++)
        	 {
              $iFoodMenuId = $db_comp_food_menu[$i]['iFoodMenuId']; 
              array_shift($db_comp_food_menu[$i]);
              $Data_Food_Menu = array();
              $Data_Food_Menu = $db_comp_food_menu[$i];
              $Data_Food_Menu['iCompanyId'] = $Company_id;
              $Data_Food_Menu_id = $obj->MySQLQueryPerform("food_menu",$Data_Food_Menu,'insert');  // Insert Food Menu
              if($Data_Food_Menu_id > 0){
                 $sqlm = "SELECT * FROM menu_items WHERE iFoodMenuId = '".$iFoodMenuId."'";
        	       $db_food_menu_items = $obj->MySQLSelect($sqlm);
                 if(count($db_food_menu_items) > 0){
                    for($j=0 ; $j < count($db_food_menu_items) ; $j++){
                        $iMenuItemId = $db_food_menu_items[$j]['iMenuItemId'];
                        array_shift($db_food_menu_items[$j]);
                        $Data_Food_Menu_Items = array();
                        $Data_Food_Menu_Items = $db_food_menu_items[$j];
                        $Data_Food_Menu_Items['iFoodMenuId'] = $Data_Food_Menu_id;
                        $Data_Food_Menu_Items_id = $obj->MySQLQueryPerform("menu_items",$Data_Food_Menu_Items,'insert');  // Insert Food Menu Items
                        if($Data_Food_Menu_Items_id > 0){
                           $sqlo = "SELECT * FROM menuitem_options WHERE iMenuItemId = '".$iMenuItemId."'";
        	                 $db_food_menu_item_options = $obj->MySQLSelect($sqlo);
                           if(count($db_food_menu_item_options) > 0){
                             for($l=0 ; $l < count($db_food_menu_item_options) ; $l++){
                               array_shift($db_food_menu_item_options[$l]);
                               $Data_Food_Menu_Item_Options = array();
                               $Data_Food_Menu_Item_Options = $db_food_menu_item_options[$l];
                               $Data_Food_Menu_Item_Options['iMenuItemId'] = $Data_Food_Menu_Items_id;
                               $Data_Food_Menu_Item_Options_id = $obj->MySQLQueryPerform("menuitem_options",$Data_Food_Menu_Item_Options,'insert');
                             }  
                           }  
                        }
                    }
                 }
              }
           }
        }
     }
   }
   ###################### Insert Restaurant Records For DeliverAll App #########################################################################################
  }

		



		//Insert rider



		$eReftype = "Rider";



		$DataR['vRefCode'] = $generalobj->ganaraterefercode($eReftype);



		$DataR['iRefUserId'] = '';



		$DataR['eRefType'] = '';



		$DataR['vName'] = $_POST['vName'];



		$DataR['vLang'] = 'EN';



		$DataR['vLastName'] = $_POST['vLastName'];



		//$Data['vLoginId'] = "";



		$DataR['vPassword'] = $generalobj->encrypt_bycrypt('123456');



		$DataR['vEmail'] = "user-".$_POST['vEmail'];



		$DataR['vPhone'] = (isset($_POST['vPhone']) && $_POST['vPhone'] != '')?$_POST['vPhone']:'9876543210';



		$DataR['vCountry']= $vCountry;



		$DataR['vPhoneCode'] = $db_country_code[0]['vPhoneCode'];



		//$DataR['vExpMonth'] = $_POST['vExpMonth'];



		//$DataR['vExpYear'] = $_POST['vExpYear'];



		$DataR['vZip'] = '121212';



		//$DataR['iDriverVehicleId	'] = "";



		$DataR['vInviteCode'] = "";



		$DataR['vCreditCard'] = "";



		$DataR['vCvv'] = "";



		$DataR['vCurrencyPassenger'] = "USD";



		$DataR['dRefDate'] =  Date('Y-m-d H:i:s');



		$DataR['eStatus'] = 'Active'; 

		$DataR['eEmailVerified'] = 'Yes';

		$DataR['ePhoneVerified'] = 'Yes';

		

      

		$id = $obj->MySQLQueryPerform("register_user",$DataR,'insert');



		$add = "Yes";



	}



?>



<!DOCTYPE html>



<html lang="en">



	<head>



		<meta charset="UTF-8">



		<meta name="viewport" content="width=device-width,initial-scale=1">



		<!--<title><?=$COMPANY_NAME?> | Contact Us</title>-->



		<title>Dummy</title>



		<!-- Default Top Script and css -->



		<?php include_once("top/top_script.php");?>



		<?php include_once("top/validation.php");?>



		<!-- End: Default Top Script and css-->

     <script src="https://maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
     <script type="text/javascript" src="assets/map/gmaps.js"></script>
     
	</head>



	<body>



		<!-- home page -->



		<div id="main-uber-page">



			<!-- Top Menu -->



			<!-- End: Top Menu-->



			<!-- contact page-->



			



			<div class="page-contant">



				<div class="page-contant-inner">



					<div class="footer-text-center">			



						<? if($add == "Yes"){?>



							<!-- <h3 style="padding-top:15px;"> Company Details </h3>



								<h5>



								<p>Name: <?php echo $_POST['vName']." ".$_POST['vLastName']; ?></p>



								<p>Email: company_<?php echo $_POST['vEmail']; ?></p>



								<p>Password: 123456 </p>



							</h5> -->



							<h3 style="padding-top:15px;"> Driver Details </h3>



							<h5>



								<p>Name: <?php echo $_POST['vName']." ".$_POST['vLastName']; ?></p>



								<p>Email: <?php echo $_POST['vEmail']; ?></p>



								<p>Password: 123456 </p>



							</h5>



							<h3 style="padding-top:15px;"> Rider Details </h3>



							<h5>



								<p>Name: <?php echo $_POST['vName']." ".$_POST['vLastName']; ?></p>



								<p>Email: user-<?php echo $_POST['vEmail']; ?></p>



								<p>Password: 123456 </p>



							</h5>
              
              <h3 style="padding-top:15px;"> Restaurant Details </h3>



							<h5>



								<p>Restaurant  Name: <?php echo $_POST['vCompany']; ?></p>


                <?php if($host_system == "foodapp"){?>
								<p>Email: rest-<?php echo $_POST['vEmail']; ?></p>
                <?php }else{
                $sql = "SELECT eStatus FROM `service_categories` WHERE iServiceId = '1'";
                $db_food_service = $obj->MySQLSelect($sql);
                $food_service_status = $db_food_service[0]['eStatus']; 
                $sql = "SELECT eStatus FROM `service_categories` WHERE iServiceId = '2'";
                $db_grocery_service = $obj->MySQLSelect($sql);
                $grocery_service_status = $db_grocery_service[0]['eStatus'];
                $sql = "SELECT eStatus FROM `service_categories` WHERE iServiceId = '3'";
                $db_wine_service = $obj->MySQLSelect($sql);
                $wine_service_status = $db_wine_service[0]['eStatus'];

                ?>
                <?php if($food_service_status == "Active"){?>
                <p>Food Company Email : rest-<?php echo $_POST['vEmail']; ?></p>
                <?}?>
                <?php if($grocery_service_status == "Active"){?>
                <p>Grocery Company Email : groc-<?php echo $_POST['vEmail']; ?></p>
                <?}?>
                <?php if($wine_service_status == "Active"){?>
                <p>Wine Company Email : liq-<?php echo $_POST['vEmail']; ?></p>
                <?php }}?>

								<p>Password: 123456 </p>



							</h5>



							



							<form method="post" action="">



								<input type="hidden" name="vName" id="vName" value="<?=$_POST['vName'];?>">



								<input type="hidden" name="vLastName" id="vLastName" value="<?=$_POST['vLastName'];?>">



								<input type="hidden" name="vEmail" id="vEmail" value="<?=$_POST['vEmail'];?>">



								<input type="hidden" name="vPhone" id="vPhone" value="<?=$_POST['vPhone'];?>">



								<input type="hidden" name="action" id="action" value="send_mail">



								<div class="contact-form">



									<b>



										<!--<input type="submit" class="submit-but" value="Send Email to Driver" name="send_email" />-->



									</b>



								</div>



							</form>



						<? } ?>



					</div>



					



					<h2 class="header-page">Add Dummy Data



						<p>It will automatically create dummy record for company , driver, driver vehicle , rider .</p>



					</h2>



					<!-- contact page -->



					<div style="clear:both;"></div>



					<?php



						if ($_REQUEST['error']) {



						?>



						<div class="row" id="showError">



							<div class="col-sm-12 alert alert-danger">



								<button aria-hidden="true" data-dismiss="alert" class="close" type="button" onclick="hideError();" >×</button>



								<?=$_REQUEST['var_msg']; ?>



							</div>



						</div>



						<?php 



						}



					?>



                    <div style="clear:both;"></div>



					<form name="frmsignup" id="frmsignup" method="post" action="">



						<input type="hidden" name="action" value="add_dummy" >



						<div class="contact-form">



							<b>



								<strong>



									<em>First Name :*</em><br/>



									<input type="text" name="vName" placeholder="<?=$langage_lbl['LBL_CONTECT_US_FIRST_NAME_HEADER_TXT']; ?>" class="contact-input required" value="<?=$vName0?>" />



								</strong>



								<strong>



									<em>Last Name :*</em><br/>



									<input type="text" name="vLastName" placeholder="<?=$langage_lbl['LBL_CONTECT_US_LAST_NAME_HEADER_TXT']; ?>" class="contact-input required" value="<?=$_vLastName?>" />



								</strong>



								<strong>



									<em>Email address: *</em><br/>



									<input type="text" placeholder="<?=$langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?>" name="vEmail" value="<?=$vEmail?>" autocomplete="off" class="contact-input required"/>



								</strong>



								<strong>



									<em>Phone Number:</em><br/>



									<input type="text" placeholder="777-777-7777" value="<?=$vPhone?>" name="vPhone" class="contact-input" />



								</strong>
                
                <strong>



									<em>Restaurant Name: *</em><br/>

                               
									<input type="text" name="vCompany" class="contact-input required" id="vCompany" placeholder="Restaurant Name" value="" />


								</strong>
                
                
								<strong>



									<em>Restaurant Location: *</em><br/>

                                    <input type="hidden" name="vRestuarantLocationLat" id="vRestuarantLocationLat" value="">
                   					<input type="hidden" name="vRestuarantLocationLong" id="vRestuarantLocationLong" value="">
									<input type="text" name="vRestuarantLocation" class="contact-input required" id="vRestuarantLocation" placeholder="Restaurant Location" value="" />


								</strong>
                
                <strong>



									<em>Country: *</em><br/>

                        <select class="contact-input required" required name='vCountry' id="vCountry" >
									<option value="">Select Country</option>
									<? for($i=0;$i<count($db_country);$i++){ ?>
									<option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?if($DEFAULT_COUNTRY_CODE_WEB==$db_country[$i]['vCountryCode']){?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
									<? } ?>
								</select>     


								</strong>




							</b>



							<b>



								<input type="submit" onClick="return submit_form();"  class="submit-but floatLeft" value="ADD" name="SUBMIT" />



							</b> 



						</div>

					</form>

					<div style="clear:both;"></div>

				</div>

			</div>

			<script>

				function submit_form()

				{

					if( validatrix() ){

						//alert("Submit Form");

						document.frmsignup.submit();

						}else{

						console.log("Some fields are required");

						return false;

					}

					return false; //Prevent form submition

				}

			</script>

			<script type="text/javascript">

				function hideError() {

					$('#showError').fadeOut();

				}

			</script>

<script>

			var from = document.getElementById('vRestuarantLocation');
			autocomplete_from1 = new google.maps.places.Autocomplete(from);
			google.maps.event.addListener(autocomplete_from1, 'place_changed', function() {
					var placeaddress = autocomplete_from1.getPlace();   
					
					$('#vRestuarantLocationLat').val(placeaddress.geometry.location.lat());
					$('#vRestuarantLocationLong').val(placeaddress.geometry.location.lng());
					
				});  
/*var map;
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

    autocomplete.addListener('place_changed', function() {

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

    if($("#vRestuarantLocation").val() != ""){
        var myLatLng = new google.maps.LatLng($("#vRestuarantLocationLat").val(), $("#vRestuarantLocationLong").val());
        marker.setPosition(myLatLng);
        map.setCenter(myLatLng);
        map.setZoom(17);
        marker.setVisible(true);
    }
}

google.maps.event.addDomListener(window, 'load', initialize);*/
</script>
		<!-- End: Footer Script -->

		</body>

	</html>



