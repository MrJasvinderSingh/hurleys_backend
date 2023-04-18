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
  
  $iServiceId = 1;
  $Company_id = 1;
  $sql = "SELECT * FROM `cuisine` WHERE iServiceId = '".$iServiceId."' AND eStatus = 'Active'";
  $db_cuisine = $obj->MySQLSelect($sql);
  
  if(count($db_cuisine) > 0){
    for($t=0;$t<count($db_cuisine);$t++){
       $Data_Company_Cuisine['iCompanyId'] = $Company_id;
       $Data_Company_Cuisine['cuisineId'] = $db_cuisine[$t]['cuisineId'];
       echo "<pre>";print_r($Data_Company_Cuisine);
       //$Data_Company_Cuisine_id = $obj->MySQLQueryPerform("company_cuisine",$Data_Company_Cuisine,'insert');  // Insert Food Menu
    }
  }  
  
   exit;

	###################### Insert Restaurant Records For DeliverAll App #########################################################################################
   
  /* $sql = "SELECT iServiceId FROM `service_categories` WHERE eStatus = 'Active'";
   $db_service_categories = $obj->MySQLSelect($sql);
   if(count($db_service_categories) > 0){
     for($kk=0;$kk<count($db_service_categories);$kk++){
        $iServiceId = $db_service_categories[$kk]['iServiceId'];
        $Data2['iServiceId'] = $iServiceId;
        $Data2['vContactName'] = $_POST['vName']." ".$_POST['vLastName']; 
        $Data2['vPassword'] = $generalobj->encrypt_bycrypt('123456');
        $Data2['vCompany'] = $_POST['vCompany'];
        $Data2['vCaddress'] = $vRestuarantLocation;
        $Data2['vEmail'] = "groc-".$_POST['vEmail'];
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
        //$Company_id = $obj->MySQLQueryPerform('company',$Data2,'insert'); 
        
        if($iServiceId == 1){
           $Company_Image_Id = 1;
           $Company_Cuisine_Id = 3;
           $Company_Food_Menu_Id = 2;
        }else if($iServiceId == 2){
           $Company_Image_Id = 33;
           $Company_Cuisine_Id = 3;
           $Company_Food_Menu_Id = 55;
        }else if($iServiceId == 3){
           $Company_Image_Id = 28;
           $Company_Cuisine_Id = 3;
           $Company_Food_Menu_Id = 39;
        }
        
        $iServiceId = 1;
        $Company_Image_Id = 1;
        
        
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
              //$Data_Food_Menu_id = $obj->MySQLQueryPerform("food_menu",$Data_Food_Menu,'insert');  // Insert Food Menu
              $Data_Food_Menu_id = 11111;
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
                        //$Data_Food_Menu_Items_id = $obj->MySQLQueryPerform("menu_items",$Data_Food_Menu_Items,'insert');  // Insert Food Menu Items
                        $Data_Food_Menu_Items_id = 121111;
                        if($Data_Food_Menu_Items_id > 0){
               echo            $sqlo = "SELECT * FROM menuitem_options WHERE iMenuItemId = '".$iMenuItemId."'";
        	                 $db_food_menu_item_options = $obj->MySQLSelect($sqlo);
                           if(count($db_food_menu_item_options) > 0){
                             for($l=0 ; $l < count($db_food_menu_item_options) ; $l++){
                               array_shift($db_food_menu_item_options[$l]);
                               
                               $Data_Food_Menu_Item_Options = array();
                               $Data_Food_Menu_Item_Options = $db_food_menu_item_options[$l];
                               $Data_Food_Menu_Item_Options['iMenuItemId'] = $Data_Food_Menu_Items_id;
                               //$Data_Food_Menu_Item_Options_id = $obj->MySQLQueryPerform("menuitem_options",$Data_Food_Menu_Item_Options,'insert');
                             }  
                           }  
                        }
                    }
                 }
              }
           }
        }
            
     }
   } */
   
   ###################### Insert Restaurant Records For DeliverAll App #########################################################################################
  
?>