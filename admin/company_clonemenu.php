<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$script = 'clonemenu';


$sql = "SELECT `iCompanyId`,`vCompany` FROM `company` WHERE `parent_iCompanyId` = 0 AND `eStatus` = 'Active';";
$companies = $obj->MySQLSelect($sql);

if(!empty($_REQUEST['parentRestaurant']) && !empty($_REQUEST['childRestaurant']))
{
 //   $_SESSION['success'] = '1';
//$_SESSION['var_msg'] = 'Menu Clonned Successfully.';
clonemenuitems($_REQUEST['parentRestaurant'], $_REQUEST['childRestaurant']);
}



function clonemenuitems($FromRestaurant, $ToRestaurant)
{
    $iCompanyId = $FromRestaurant;
$toCompanyId	= $ToRestaurant;
global $obj;
$message = '';
$FoodMenudata = '';
$FoodMenuQ = "SELECT * FROM `food_menu` WHERE `iCompanyId` = '$iCompanyId';"; //  AND `eStatus` = 'Active'
$FoodMenus = $obj->MySQLSelect($FoodMenuQ);

if(count($FoodMenus) > 0) {
	foreach($FoodMenus as $FoodMenu) {
		$FoodMenudata .= $FoodMenu['iFoodMenuId'].',';
                
                $checksql =  "SELECT iFoodMenuId, vMenu_EN FROM  `food_menu` WHERE `iCompanyId` = '$toCompanyId' AND `vMenu_EN` = '".$FoodMenu['vMenu_EN']."'  ;";
                $checkData = $obj->MySQLSelect($checksql);
                if(count($checkData) == 0 )
                {
		 $sql = "INSERT INTO food_menu (iCompanyId, vMenu_EN, vMenu_ES, vMenu_FN, vMenuDesc_EN, vMenuDesc_FN, vMenuDesc_ES, iDisplayOrder, vImage, eStatus) Values ('".$toCompanyId."', '".$FoodMenu['vMenu_EN']."', '".$FoodMenu['vMenu_ES']."', '".$FoodMenu['vMenu_FN']."', '".$FoodMenu['vMenuDesc_EN']."', '".$FoodMenu['vMenuDesc_FN']."', '".$FoodMenu['vMenuDesc_ES']."', '".$FoodMenu['iDisplayOrder']."', '".$FoodMenu['vImage']."', '".$FoodMenu['eStatus']."') ";
		
		$newid = $obj->MySQLInsert($sql);
                
                if(!empty($newid))
                {
                    $message.= " <br/> Food Menu ($FoodMenu[vMenu_EN]) is Inserted with ID $newid <br/>";
                $message.=  generateMenuitems($FoodMenu['iFoodMenuId'], $newid);
                }
                else
                {
                     $message.= " <br/> Food Menu ($FoodMenu[vMenu_EN]) is not inserted <br/>";
                }
                }
                else
                {
                    $message.= " <br/> Food Menu ($FoodMenu[vMenu_EN]) is  Already Exist<br/>";
                     $message.=  generateMenuitems($FoodMenu['iFoodMenuId'], $checkData[0]['iFoodMenuId']);
                }
	}
}



$_SESSION['success'] = '1';
//$_SESSION['var_msg'] = 'Menu Clonned Successfully.';
$_SESSION['var_msg'] = $message;
}



function generateMenuitems($fromFoodMenu, $toFoodMenu)
{
    global $obj;
    $msrMi = '';
 $MenuItemsQ = "SELECT * FROM `menu_items` WHERE `iFoodMenuId` = '$fromFoodMenu';"; // AND `eStatus` = 'Active'
$MenuItems = $obj->MySQLSelect($MenuItemsQ);
if(count($MenuItems) > 0) {
	foreach($MenuItems as $MenuItem) {
            
            
            $checksql =  "SELECT iMenuItemId, vItemType_EN FROM `menu_items` WHERE `iFoodMenuId` = '$toFoodMenu' AND `vItemType_EN` = '".$MenuItem['vItemType_EN']."'  ;";
                $checkData = $obj->MySQLSelect($checksql);
                if(count($checkData) == 0 )
                {
	$sql = "INSERT INTO menu_items (iFoodMenuId, vItemType_ES, vItemType_EN, vItemType_FN, vItemDesc_EN, vItemDesc_FN, vItemDesc_ES, fbPrice, cpremium, rpremium,fPrice, eFoodType, fOfferAmt, vImage, iDisplayOrder, eStatus, cookingtime, eAvailable, eBestSeller, eRecommended, vHighlightName) Values ('".$toFoodMenu."', '".$MenuItem['vItemType_ES']."', '".$MenuItem['vItemType_EN']."', '".$MenuItem['vItemType_FN']."', '".preg_replace("/[^a-zA-Z0-9 ]+/", "", $MenuItem['vItemDesc_EN'])."', '".$MenuItem['vItemDesc_FN']."', '".$MenuItem['vItemDesc_ES']."', '".$MenuItem['fbPrice']."', '".$MenuItem['cpremium']."', '".$MenuItem['rpremium']."','".$MenuItem['fPrice']."', '".$MenuItem['eFoodType']."', '".$MenuItem['fOfferAmt']."', '".$MenuItem['vImage']."', '".$MenuItem['iDisplayOrder']."', '".$MenuItem['eStatus']."', '".$MenuItem['cookingtime']."', '".$MenuItem['eAvailable']."', '".$MenuItem['eBestSeller']."', '".$MenuItem['eRecommended']."', '".$MenuItem['vHighlightName']."')";
		$newMenuItemid = $obj->MySQLInsert($sql);
                
                if(!empty($newMenuItemid))
                {
                     $msrMi.= " Menu Item  ($MenuItem[vItemType_EN]) is Inserted with ID $newMenuItemid <br/>";
                     $msrMi.= generateMenuItemsOPtions($MenuItem['iMenuItemId'], $newMenuItemid);
                }
                else
                {
                    $msrMi.= " Menu Item  ($MenuItem[vItemType_EN]) is not inserted <br/>";
                }
                }
                else
                {
//                    $tblMenu_name = 'menu_items';
//                    $WhereMenuItemId = " `iMenuItemId` = '" . $checkData[0]['iMenuItemId'] . "'";
//                    $UpdateMenuData['fbPrice'] = $MenuItem['fbPrice'];
//                    $UpdateMenuData['cpremium'] = $MenuItem['cpremium'];
//                    $UpdateMenuData['rpremium'] = $MenuItem['rpremium'];
//                    $UpdateMenuData['fPrice'] = $MenuItem['fPrice'];
//                    $menuitem_id = $obj->MySQLQueryPerform($tblMenu_name, $UpdateMenuData, 'update', $WhereMenuItemId);
                     $msrMi.= "Menu Item ($MenuItem[vItemType_EN]) is  Already Exist.<br/>";
                     //And Price Updated
                     $msrMi.= generateMenuItemsOPtions($MenuItem['iMenuItemId'], $checkData[0]['iMenuItemId']);
                }
	}

}

return $msrMi;
}


function generateMenuItemsOPtions($FromMenuItem, $ToMenuItem)
{
    global $obj;
    $MenuItemIds = substr($MenuItemIds, 0, -1);
$msrMo = '';
 $MenuOPtionsQ = "SELECT * FROM `menuitem_options` WHERE `iMenuItemId` = '$FromMenuItem';"; // AND `eStatus` = 'Active'
$MenuOPtions = $obj->MySQLSelect($MenuOPtionsQ);

if(count($MenuOPtions) > 0) {
	foreach($MenuOPtions as $MenuOPtion) {
            $checksql =  "SELECT iOptionId,vOptionName FROM `menuitem_options` WHERE `iMenuItemId` = '$ToMenuItem' AND `vOptionName` = '".$MenuOPtion['vOptionName']."'  ;";
                $checkData = $obj->MySQLSelect($checksql);
                if(count($checkData) == 0 )
                {
		$sql = "INSERT INTO menuitem_options (iMenuItemId, vOptionCat, vOptionReq, vOptionName, fbPrice, cpremium, rpremium, fPrice, nOptions, eOptionType, eDefault, eStatus) Values ('".$ToMenuItem."', '".$MenuOPtion['vOptionCat']."', '".$MenuOPtion['vOptionReq']."', '".$MenuOPtion['vOptionName']."', '".$MenuOPtion['fbPrice']."', '".$MenuOPtion['cpremium']."', '".$MenuOPtion['rpremium']."', '".$MenuOPtion['fPrice']."', '".$MenuOPtion['nOptions']."', '".$MenuOPtion['eOptionType']."', '".$MenuOPtion['eDefault']."', '".$MenuOPtion['eStatus']."')";
		
		$NewOPtionId = $obj->MySQLInsert($sql);
                
                if(!empty($NewOPtionId))
                {
                     $msrMo .= " Menu Item Option ($MenuOPtion[vOptionName]) is Inserted with ID $NewOPtionId <br/>";
                    
                }
                else
                {
                    $msrMo .= " Menu Item Option ($MenuOPtion[vOptionName]) is not inserted <br/>";
                }
                }
                else
                {
//                    
//                    $tblMenuOPtion_name = 'menuitem_options';
//                    $WhereMenuOptionId = " `iOptionId` = '" . $checkData[0]['iOptionId'] . "'";
//                    $UpdateMenuOPtionData['fbPrice'] = $MenuOPtion['fbPrice'];
//                    $UpdateMenuOPtionData['cpremium'] = $MenuOPtion['cpremium'];
//                    $UpdateMenuOPtionData['rpremium'] = $MenuOPtion['rpremium'];
//                    $UpdateMenuOPtionData['fPrice'] = $MenuOPtion['fPrice'];
//                    $menuoption_id = $obj->MySQLQueryPerform($tblMenuOPtion_name, $UpdateMenuOPtionData, 'update', $WhereMenuOptionId);
                     $msrMo .= "Menu Item Option ($MenuOPtion[vOptionName]) is  Already Exist.<br/>";
                     // And Price Updated
                }
	}
}

return $msrMo;
}


// $WhereParent = " `iCompanyId` = '" . $parent_iCompanyId . "'";
//	$UpdateParentCompanyData['parent_iCompanyId'] = 0;
//	$company_id = $obj->MySQLQueryPerform($tbl_name, $UpdateParentCompanyData, 'update', $WhereParent);

?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <?php include_once('global_files.php'); ?>
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
                            <h2>Copy All Menu items from Parent Restaurant</h2>
                        </div>
                    </div>
                    <hr />
                </div>
                <?php include('valid_msg.php'); ?>
                <form name="cloneform" id="cloneform" action="company_clonemenu.php" method="post">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                        <tbody>
                            <tr>
                                <td width="10%"><label for="textfield"><strong>Select From:</strong></label></td>
                                <td width="10%" class="padding-right10"><select name="parentRestaurant" id="parentRestaurant" class="form-control" required="required">
                                    <option value="">Select Parent</option>
                                    <?php if(count($companies) > 0) {
     foreach ($companies as $company):
         echo '<option  value="'.$company['iCompanyId'].'" >'.$company['vCompany'].'</option>';
     endforeach;
                                    
                                     } ?>
                                    </select>
                                </td>
                                <td width="5%">&nbsp;</td>
                                 <td width="10%"><label for="textfield"><strong>Select To:</strong></label></td>
                                <td width="10%" class="padding-right10"><select name="childRestaurant" id="childRestaurant" class="form-control" required="required">
                                    <option value="">Select option</option>
                                   
                                    </select>
                                </td>
                                <td>
                                    <input type="submit" value="Clone Menu" class="btnalt button11" id="clonemenu" name="clonemenu" title="Click" />
                                    <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'company_clonemenu.php'"/>
                                </td>
                                <td width="30%">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>

                </form>
               
                <div class="admin-notes">
                    <h4>Notes:</h4>
                    <ul>
                        <li>Clonning module will copy parent restaurant items to child restaurant.</li>
                        
                    </ul>
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->

	

    <?php include_once('footer.php'); ?>
    <script>
        
        $('#parentRestaurant').on('change', function() {
  parentvalue = this.value;
  
  $.ajax({
    url:'company_clone.php',
    type:'POST',
    data: {parentvalue : parentvalue},
    dataType: 'json',
    success: function( json ) {
         $('#childRestaurant').empty();
         //$('#childRestaurant').append($('<option></option>').attr('value', '0').text('Select option'));
        $.each(json, function(i, value) {
           $('#childRestaurant').append($('<option></option>').attr('value', value.iCompanyId).text(value.vCompany));
                    //.append($('<option>').text(value.iCompanyId).attr('value', value.vCompany));
        });
        
//        $('#select').empty();
//            $('#select').append($('<option>').text("Select"));
//            $.each(json, function(i, obj){
//                    $('#select').append($('<option>').text(obj.text).attr('value', obj.val));
//            });
    }
});
  
});
      
		
    </script>
</body>
<!-- END BODY-->
</html>