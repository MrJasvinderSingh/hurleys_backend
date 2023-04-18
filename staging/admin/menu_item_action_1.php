<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$tbl_name = 'menu_items';
$tbl_name1 = 'menuitem_options';
$script = 'MenuItems';
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);

function check_diff($arr1, $arr2){
    $check = (is_array($arr1) && count($arr1)>0) ? true : false;
    $result = ($check) ? ((is_array($arr2) && count($arr2) > 0) ? $arr2 : array()) : array();
    if($check){
        foreach($arr1 as $key => $value){
            if(isset($result[$key])){
                $result[$key] = array_diff($value,$result[$key]);
            }else{
                $result[$key] = $value;
            }
        }
    }
    return $result;
}
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$message_print_id = $id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$iFoodMenuId = isset($_POST['iFoodMenuId']) ? $_POST['iFoodMenuId'] : '0';
$fPrice = isset($_POST['fPrice']) ? $_POST['fPrice'] : '';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
//$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '';
$eFoodType = isset($_POST['eFoodType']) ? $_POST['eFoodType'] : '';

$vHighlightName = isset($_POST['vHighlightName']) ? $_POST['vHighlightName'] : '';

$fOfferAmt = isset($_POST['fOfferAmt']) ? $_POST['fOfferAmt'] : '';
$cookingtime = isset($_POST['cookingtime']) ? $_POST['cookingtime'] : '';
$eStatus_check = isset($_POST['eStatus'])?$_POST['eStatus']:'on';
$eStatus = ($eStatus_check == 'on')?'Active':'Inactive';

$eAvailable_check = isset($_POST['eAvailable'])?$_POST['eAvailable']:'off';
$eAvailable = ($eAvailable_check == 'on')?'Yes':'No';

$eRecommended_check = isset($_POST['eRecommended'])?$_POST['eRecommended']:'off';
$eRecommended = ($eRecommended_check == 'on')?'Yes':'No';

/*$eRecommended_check = isset($_POST['eRecommended'])?$_POST['eRecommended']:'on';
$eRecommended = ($eRecommended_check == 'on')?'Yes':'No';

$eAvailable_check = isset($_POST['eAvailable'])?$_POST['eAvailable']:'on';
$eAvailable = ($eAvailable_check == 'on')?'Yes':'No';*/
$oldImage		= isset($_POST['oldImage'])?$_POST['oldImage']:'';
$vImageTest = isset($_POST['vImageTest'])?$_POST['vImageTest']:'';

$BaseOptions  = isset($_POST['BaseOptions'])?$_POST['BaseOptions']:'';
$OptPrice     = isset($_POST['OptPrice'])?$_POST['OptPrice']:'';
$optType     = isset($_POST['optType'])?$_POST['optType']:'';
$OptionId  = isset($_POST['OptionId'])?$_POST['OptionId']:'';
$eDefault  = isset($_POST['eDefault'])?$_POST['eDefault']:'';


foreach ($BaseOptions as $key => $value) {
    $base_array[$key]['vOptionName'] = $value;
    $base_array[$key]['fPrice']= $OptPrice[$key];
    $base_array[$key]['eOptionType'] = $optType[$key];
    $base_array[$key]['iOptionId'] = $OptionId[$key];
    $base_array[$key]['eDefault'] = $eDefault[$key];
    $base_array[$key]['eStatus'] = 'Active';
}

$AddonOptions  = isset($_POST['AddonOptions'])?$_POST['AddonOptions']:'';
$AddonPrice     = isset($_POST['AddonPrice'])?$_POST['AddonPrice']:'';
$optTypeaddon     = isset($_POST['optTypeaddon'])?$_POST['optTypeaddon']:'';
$addonId  = isset($_POST['addonId'])?$_POST['addonId']:'';

foreach ($AddonOptions as $key => $value) {
    $addon_array[$key]['vOptionName'] = $value;
    $addon_array[$key]['fPrice']= $AddonPrice[$key];
    $addon_array[$key]['eOptionType'] = $optTypeaddon[$key];
    $addon_array[$key]['iOptionId'] = $addonId[$key];
    $addon_array[$key]['eStatus'] = 'Active';
}

$vTitle_store = array();
$vItemDesc_store =array();

$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vItemType_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vItemDesc_'.$db_master[$i]['vCode'];

        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';

        array_push($vItemDesc_store ,$vValue_desc);   
        $$vValue_desc  = isset($_POST[$vValue_desc])?$_POST[$vValue_desc]:'';
    }
}
 
if (isset($_POST['btnsubmit'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location:menu_item_action.php?id=" . $id . "&success=2");
        exit;
    }
    
    $img_path = $tconfig["tsite_upload_images_menu_item_path"];
	$temp_gallery = $img_path . '/';
    $image_object = $_FILES['vImage']['tmp_name'];
    $image_name = $_FILES['vImage']['name'];
	$vImgName = "";
	
	if($image_name != "") {
            $oldFilePath = $temp_gallery.$oldImage;
          if ($oldImage != '' && file_exists($oldFilePath)) {
               unlink($img_path . '/' . $oldImage);
              /* unlink($img_path . '/1_' . $oldImage);
               unlink($img_path . '/2_' . $oldImage);
               unlink($img_path . '/3_' . $oldImage);*/
          }
          $filecheck = basename($_FILES['vImage']['name']);
          $fileextarr = explode(".", $filecheck);
          $ext = strtolower($fileextarr[count($fileextarr) - 1]);
          $flag_error = 0;
          if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
               $flag_error = 1;
               $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
          }
          if ($flag_error == 1) {
               $generalobj->getPostForm($_POST, $var_msg, "food_menu_action.php?success=0&var_msg=" . $var_msg);
               exit;
          } else {
			  
               $Photo_Gallery_folder = $img_path . '/';

			//$img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);
            $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
			$oldImage = $img1;
          }
    }
    
    if ($id != "") {
        $sql = "SELECT iDisplayOrder FROM `menu_items` where iMenuItemId = '$id'";
        $displayOld = $obj->MySQLSelect($sql);
        $oldDisplayOrder = $displayOld[0]['iDisplayOrder'];

        if ($oldDisplayOrder > $iDisplayOrder) {
            $sql = "SELECT * FROM `menu_items` where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $iDisplayOrder + 1;
                for ($i = 0; $i < count($db_orders); $i++) {
                    $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '" . $db_orders[$i]['iMenuItemId'] . "'";
                    $obj->sql_query($query);
                    echo $j;
                    $j++;
                }
            }
        } else if ($oldDisplayOrder < $iDisplayOrder) {
            $sql = "SELECT * FROM `menu_items` where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder > '$oldDisplayOrder' AND iDisplayOrder <= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $iDisplayOrder;
                for ($i = 0; $i < count($db_orders); $i++) {
                    $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '" . $db_orders[$i]['iMenuItemId'] . "'";
                    $obj->sql_query($query);
                    echo $j;
                    $j++;
                }
            }
        }
    } else {
        $sql = "SELECT * FROM `menu_items` where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
        $db_orders = $obj->MySQLSelect($sql);

        if (!empty($db_orders)) {
            $j = $iDisplayOrder + 1;
            for ($i = 0; $i < count($db_orders); $i++) {
                $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '" . $db_orders[$i]['iMenuItemId'] . "'";
                $obj->sql_query($query);
                $j++;
            }
        }
    }

    for ($i = 0; $i < count($vTitle_store); $i++) {

        $vValue = 'vItemType_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vItemDesc_'.$db_master[$i]['vCode'];

        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iMenuItemId` = '" . $id . "'";
        }

       $query = $q . " `" . $tbl_name . "` SET
			`iFoodMenuId` = '" . $iFoodMenuId . "',
			`vImage` = '" . $oldImage . "',
			`iDisplayOrder` = '" . $iDisplayOrder . "',
			`fPrice` = '" . $fPrice . "',
            `fOfferAmt` = '" . $fOfferAmt . "',
             `cookingtime` = '" . $cookingtime . "',   
            `eFoodType` = '" . $eFoodType . "',
            `vHighlightName` = '" . $vHighlightName . "',
            `eAvailable` = '" . $eAvailable ."',
            `eRecommended`= '" . $eRecommended ."',
            " . $vValue_desc . " = '" . $_POST[$vItemDesc_store[$i]] . "',
			" . $vValue . " = '" . $_POST[$vTitle_store[$i]] . "'"
                . $where;
       $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }


    if(!empty($id)){
        $q = "SELECT * FROM menuitem_options WHERE iMenuItemId ='".$id."' AND eOptionType='Options'";
        $baseOptionOldData = $obj->MySQLSelect($q);

        if(count($baseOptionOldData) > 0) {
            $BaseOptionsDiffres=check_diff($baseOptionOldData,$base_array);
            foreach ($BaseOptionsDiffres as $k => $BaseOptionsVal) {
                if(!empty($BaseOptionsVal['iOptionId'])){
                    $newoptioidsArr[$k]['iOptionId'] = $BaseOptionsVal['iOptionId'];
                    $newoptioidsArr[$k]['iMenuItemId'] = $BaseOptionsVal['iMenuItemId'];
                }
            }
            
            if(count($newoptioidsArr) > 0) {
                foreach ($newoptioidsArr as $ky => $optionidArr) {
                    $q = "UPDATE ";
                    $where = " WHERE `iOptionId` = '" . $optionidArr['iOptionId'] . "' AND `iMenuItemId` = '" . $optionidArr['iMenuItemId'] . "'";

                    $baseupdatequery = $q . " `" . $tbl_name1 . "` SET
                        `eStatus` = 'Inactive'"
                        . $where;
                    $obj->sql_query($baseupdatequery);   
                }
            }

            if(count($base_array) > 0){
                foreach ($base_array as $key => $value) {
                    if($value['iOptionId'] == ''){
                        $q = "INSERT INTO ";
                        $where = '';
                    } else {
                        $q = "UPDATE ";
                        $where = " WHERE `iOptionId` = '" . $value['iOptionId'] . "'";
                    }
                    $basequery = $q . " `" . $tbl_name1 . "` SET
                        `iMenuItemId`= '" . $id . "',
                        `vOptionName` = '" . $value['vOptionName'] . "',
                        `fPrice` = '" . $value['fPrice'] . "',
                        `eDefault` = '" . $value['eDefault'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                        `eOptionType` = '" . $value['eOptionType'] . "'"
                        . $where;
                    $obj->sql_query($basequery);
                }
            }
        } else {
            if(count($base_array) > 0){
                foreach ($base_array as $key => $value) {
                    $q = "INSERT INTO ";
                    $where = '';
                    $basequery = $q . " `" . $tbl_name1 . "` SET
                        `iMenuItemId`= '" . $id . "',
                        `vOptionName` = '" . $value['vOptionName'] . "',
                        `fPrice` = '" . $value['fPrice'] . "',
                        `eDefault` = '" . $value['eDefault'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                        `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($basequery);
                }
            }
        }
    }

    if(!empty($id)){
        $q = "SELECT * FROM menuitem_options WHERE iMenuItemId ='".$id."' AND eOptionType='Addon'";
        $addonOptionOldData = $obj->MySQLSelect($q);

        if(count($addonOptionOldData) > 0){
            $addonOptionDiffres=check_diff($addonOptionOldData,$addon_array);
            foreach ($addonOptionDiffres as $j => $AddonOptionsVal) {
                if(!empty($AddonOptionsVal['iOptionId'])){
                    $newoptioidsAddonArr[$j]['iOptionId'] = $AddonOptionsVal['iOptionId'];
                    $newoptioidsAddonArr[$j]['iMenuItemId'] = $AddonOptionsVal['iMenuItemId'];
                }
            }
            
            if(count($newoptioidsAddonArr) > 0) {
                foreach ($newoptioidsAddonArr as $ky => $addonoptionidArr) {
                    $q = "UPDATE ";
                    $where = " WHERE `iOptionId` = '" . $addonoptionidArr['iOptionId'] . "' AND `iMenuItemId` = '" . $addonoptionidArr['iMenuItemId'] . "'";

                    $addonupdatequery = $q . " `" . $tbl_name1 . "` SET
                        `eStatus` = 'Inactive'"
                        . $where;
                    $obj->sql_query($addonupdatequery);   
                }
            }

            if(count($addon_array) > 0){
                foreach ($addon_array as $key => $value) {
                    if($value['iOptionId'] == ''){
                        $q = "INSERT INTO ";
                        $where = '';
                    } else{
                        $q = "UPDATE ";
                        $where = " WHERE `iOptionId` = '" . $value['iOptionId'] . "'";
                    }
                    $addonquery = $q . " `" . $tbl_name1 . "` SET
                        `iMenuItemId`= '" . $id . "',
                        `vOptionName` = '" . $value['vOptionName'] . "',
                        `fPrice` = '" . $value['fPrice'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                        `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($addonquery);
                }
            }
        } else {
            if(count($addon_array) > 0){
                foreach ($addon_array as $key => $value) {
                    $q = "INSERT INTO ";
                    $where = '';
                    $addonquery = $q . " `" . $tbl_name1 . "` SET
                        `iMenuItemId`= '" . $id . "',
                        `vOptionName` = '" . $value['vOptionName'] . "',
                        `fPrice` = '" . $value['fPrice'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                        `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($addonquery);
                }
            }
        }
    }
    //header("Location:menu_item_action.php?id=" . $id . '&success=1');
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Item Insert Successfully.';
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Item Updated Successfully.';
    }
    header("Location:".$backlink);exit;
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT mi.*,f.iCompanyId FROM menu_items as mi LEFT JOIN food_menu as f on f.iFoodMenuId=mi.iFoodMenuId WHERE mi.iMenuItemId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    
    $sql1 = "SELECT * FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Options' AND eStatus = 'Active'";
    $db_optionsdata = $obj->MySQLSelect($sql1);
 $sql1Noption = "SELECT count(nOptions) as totalNoption FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Options' AND eStatus = 'Active'";
    $nOptionscount = $obj->MySQLSelect($sql1Noption);
   
    $sql2 = "SELECT * FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Addon' AND eStatus = 'Active'";
    $db_addonsdata = $obj->MySQLSelect($sql2);

    $vLabel = $id;
    if (count($db_data) > 0) {
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vItemType_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vValue_desc = 'vItemDesc_'.$db_master[$i]['vCode'];
                $$vValue_desc = $value[$vValue_desc];
                $iFoodMenuId = $value['iFoodMenuId'];
                $oldImage = $value['vImage'];
                $iDisplayOrder = $value['iDisplayOrder'];
                $fPrice = $value['fPrice'];
                $eAvailable = $value['eAvailable'];
                $eStatus = $value['eStatus'];
                $eRecommended= $value['eRecommended'];
                $fOfferAmt = $value['fOfferAmt'];
                $cookingtime = $value['cookingtime'];
                $iCompanyId = $value['iCompanyId'];
                $eFoodType = $value['eFoodType'];
                $vHighlightName = $value['vHighlightName'];
            } 
        }
    }
}

$qry_cat = "SELECT c.iServiceId FROM `food_menu` AS f LEFT JOIN company AS c ON c.iCompanyId = f.iCompanyId WHERE c.iCompanyId = '".$iCompanyId."' and  c.eStatus!='Deleted'";
$db_chk = $obj->MySQLSelect($qry_cat);
$EditServiceIdNew = $db_chk[0]['iServiceId'];


$sql_cat = "SELECT fm.iFoodMenuId,fm.vMenu_EN,c.vCompany,c.iCompanyId FROM food_menu AS fm
			LEFT JOIN `company` AS c ON c.iCompanyId = fm.iCompanyId WHERE fm.eStatus = 'Active'";
$db_menu = $obj->MySQLSelect($sql_cat);

// For Restaurants

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata,true);
foreach ($allservice_cat_data as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_".$default_lang." as servicename,eStatus FROM service_categories WHERE iServiceId IN (".$serviceIds.") AND eStatus = 'Active'";
$service_cat_list = $obj->MySQLSelect($service_category);

$sql = "SELECT c.iCompanyId,c.vCompany,c.iServiceId,c.vEmail FROM `food_menu` AS f LEFT JOIN company AS c ON c.iCompanyId = f.iCompanyId WHERE c.eStatus!='Deleted' GROUP BY f.iCompanyId";
$db_company = $obj->MySQLSelect($sql);

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title>Admin | Menu Items <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <?
    include_once('global_files.php');
    ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <? include_once('header.php');
    include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2> Menu Items </h2>
                    <a href="javascript:void(0);" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                   </a>
                </div>
            </div>
            <hr />
            <div class="body-div">
                <div class="form-group">
                    <? if ($success == 1) {?>
                    <div class="alert alert-success alert-dismissable msgs_hide">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        Record Updated successfully.
                    </div><br/>
                    <? } elseif ($success == 2) { ?>
                    <div class="alert alert-danger alert-dismissable ">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                    </div><br/>
                    <? } elseif ($success == 3) { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?php echo $_REQUEST['var_msg']; ?> 
                    </div><br/>	
                    <? } ?>
                    <? if($_REQUEST['var_msg'] !=Null) { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        Record  Not Updated .
                    </div><br/>
                    <? } ?>
                    <form name="menuItem_form" id="menuItem_form" method="post" action="" enctype="multipart/form-data" >
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input  type="hidden" name="oldImage" value="<?php echo $oldImage; ?>">
                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="menu_item.php"/>
                        <? if($action == 'Edit'){?>
                        <input name="iServiceId" type="hidden" class="create-account-input" value="<?php echo $service_cat_list[0]['iServiceId'];?>"/>
                        <? } ?>
                        <div class="row">
                            <div class="col-lg-6">
								<?php 
										if($action == 'Add'){
										if(count($allservice_cat_data)<=1){ 
										?>
										<input name="iServiceId" type="hidden" id="iServiceId" class="create-account-input" value="<?php echo $service_cat_list[0]['iServiceId'];?>" />
										<?php } else { ?>
									
										<div class="row">
											<div class="col-lg-12">
                                                <label>Service Type<span class="red"> *</span></label>
                                            </div>
											<div class="col-lg-12">
												  <select class="form-control" name = 'iServiceId' id="iServiceId" required onchange="changeserviceCategory(this.value)" id="iServiceId">
													   <option value="">Select</option>
													   <?php //foreach($db_company as $dbcm) { ?>
													   <? for($i=0;$i<count($service_cat_list);$i++){ ?>
													   <option value = "<?= $service_cat_list[$i]['iServiceId'] ?>" <?if($iServiceIdNew == $service_cat_list[$i]['iServiceId'] && $action == 'Add') { ?> selected <?php } else if($iServiceIdNew==$service_cat_list[$i]['iServiceId']){?>selected<? } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
													   <? } ?>
													   <?php //} ?>
												  </select>
											 </div>
										 </div>
										<?php 
											} 
										}
										?>
                                <div class="row">
                                     <div class="col-lg-12">
                                          <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];?><span class="red"> *</span></label>
                                     </div>
                                     <div class="col-lg-12">
                                          <select name="iCompanyId" class="form-control" id="iCompanyId" required onchange="changeMenuCategory(this.value)" <?if($action == 'Edit'){?> disabled <?}?> >
                                              <option value="" >Select <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];?></option>
                                               <?php foreach($db_company as $dbc) { ?>
                                               <option value="<?php echo $dbc['iCompanyId']; ?>"<?if($dbc['iCompanyId'] == $iCompanyId){?> selected<? } ?>><?php echo $dbc['vCompany'] ?> ( <?php echo  $dbc['vEmail']?> )</option>
                                               <?php } ?>
                                          </select>
                                     </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_MENU_CATEGORY_WEB_TXT']?><span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <select  class="form-control" name = 'iFoodMenuId' required onChange="changeDisplayOrder(this.value, '<?php echo $id; ?>');" id="iFoodMenuId">
                                            <option value=""><?php echo $langage_lbl_admin['LBL_SELECT_CATEGORY']?></option>
                                            <?php foreach ($db_menu as $dbmenu) { ?>
        										<option value = "<?= $dbmenu['iFoodMenuId'] ?>" <?= ($dbmenu['iFoodMenuId'] == $iFoodMenuId) ? 'selected' : ''; ?> <?php if(count($dbmenu['menuItems']) > 0){ ?> <?php } ?> ><?= $dbmenu['vMenu_'.$_SESSION['sess_lang']]; ?></option>
        									<?  } ?>
                                        </select>
                                    </div>
                                </div>

                                <?php if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $vValue = 'vItemType_' . $vCode;
                                        $vValue_desc = 'vItemDesc_'.$vCode;

                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_MENU_ITEM_FRONT']?>  (<?= $vTitle; ?>) <?= $required_msg;?></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?>Value" <?= $required; ?>>

                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                              <label><?php echo $langage_lbl['LBL_MENU_ITEM_DESCRIPTION']?>(<?=$vTitle;?>)</label>
                                            </div>
                                            <div class="col-lg-12">
                                              <textarea class="form-control" name="<?=$vValue_desc;?>" id="<?=$vValue_desc;?>" ><?=$$vValue_desc;?></textarea>
                                            </div>
                                        </div>
                                <?  }
                                } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl['LBL_DISPLAY_ORDER_FRONT']?><span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12" id="showDisplayOrder001">
                                        <?php if ($action == 'Add') { ?>
                                            <input type="hidden" name="total" value="<?php echo $count + 1; ?>" >
                                            <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                <?php for ($i = 1; $i <= $count + 1; $i++) { ?>
                                                    <option value="<?php echo $i ?>" 
                                                    <?php
                                                    if ($i == $count + 1)
                                                        echo 'selected';
                                                    ?>> <?php echo $i ?> </option>
                                                        <?php } ?>
                                            </select>
                                        <?php }else { ?>
                                            <input type="hidden" name="total" value="<?php echo $iDisplayOrder; ?>">
                                            <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                <?php for ($i = 1; $i <= $count; $i++) { ?>
                                                    <option value="<?php echo $i ?>"
                                                    <?php if ($i == $iDisplayOrder)  echo 'selected';  ?>
                                                    > <?php echo $i ?> </option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </div>
                                </div>

                                <!--    <div class="row">
                                  <div class="col-lg-12">
                                    <label>Status</label>
                                  </div>
                                  <div class="col-lg-12">
                                    <div class="make-switch" data-on="success" data-off="warning" id="mySwitch">
                                      <input type="checkbox" name="eStatus" <?=($id != '' && $eStatus == 'Inactive')?'':'checked';?> id="eStatus"/>
                                    </div>
                                  </div>
                                </div> -->

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_MENU_ITEM_IMAGE']?></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="imageupload">
                                            <div class="file-tab">
                                                <span id="single_img001">
                                                <?php $imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $oldImage;
                                                    $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $oldImage;

                                                    if ($oldImage != "" && file_exists($imgpth)) { ?>
                                                        <img src="<?php echo $imgUrl; ?>" alt="Image preview" class="thumbnail" style="max-width: 250px; max-height: 250px">
                                                <?php } ?>
                                                </span>
                                                <div>
                                                    <input type="hidden" name="vImageTest" value="" >
                                                    <input name="vImage" onchange="preview_mainImg(event);" type="file" >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="row" >
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_PRICE_FOR_MENU_ITEM']?> (In <?=$db_currency[0]['vName']?>) <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <input type="text" class="form-control" name="fPrice"  id="fPrice" value="<?= $fPrice; ?>" required>
                                        <small>[<?php echo $langage_lbl_admin['LBL_NOTE_FRONT']?> <?php echo $langage_lbl_admin['LBL_NOTE_FOR_PRICE_MENU_ITEM']?>]</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                         
                                        <select id="nOPtions" name="nOPtions">
                                            <option value="1" selected="selected">One</option>
                                            <option value="2">Two</option>
                                            <option value="2">Three</option>
                                        </select>
                                        <?php $noption =  $nOptionscount[0]['totalNoption'];
                                        if($noption == 0 ){ $noption = 1; }
                                        for($noi = 1; $noi <= $noption; $noi++){ ?>
                                       
                                        <div class="panel panel-default" id='optionPanel<?php echo $noi; ?>'>
                                            <div class="panel-heading">
                                                <div class="row" style="padding-bottom:0; ">
                                                    <div class="col-lg-6"><h5><b><?php echo $langage_lbl_admin['LBL_OPTIONS_MENU_ITEM']?></b> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Use this feature if you want to provide different options or combos for same item (i.e Pizza Base or Combos). Price   will be additional price which will added in base price. '></i></h5></div>
                                                    <div class="col-lg-6 text-right"><button class="btn btn-success" type="button"  onclick="options_fields('<?php echo $noi; ?>');"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button></div>
                                                </div>
                                            </div>
                                           
                                            <div class="panel-body" style="padding: 25px;">
                                                <div id="options_fields<?php echo $noi; ?>"> 
                                                    <? if (count($db_optionsdata) > 0) {
                                                    $opt = 0; 
                                                    foreach($db_optionsdata as $k => $option){
                                                    $opt++;?>
                                          <?php if($option['eDefault'] == 'Yes') { ?>
                                            <div class="form-group eDefault"> 
                                              <div class="col-sm-5">
                                                  <div class="form-group"> 
                                                      <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="<?=$option['vOptionName']?>" placeholder="Option Name" >
                                                  </div>
                                              </div>
                                              <div class="col-sm-5">
                                                  <div class="form-group"> 
                                                      <input type="text" class="form-control" id="OptPrice" name="OptPrice[]"  value="<?=$option['fPrice']?>" placeholder="Price" readonly required="required">
                                                      <input type="hidden" name="optType[]" value="Options" />
                                                      <input type="hidden" name="OptionId[]" value="<?=$option['iOptionId']?>" /><input type="hidden" name="eDefault[]" value="Yes"/>
                                                  </div>
                                              </div>
                                            <div class="clear"></div>
                                          </div>
                                                </div>
                                         <?php } else {?>
                                                    <div class="form-group removeclass<?=$opt?>"> 
                                                        <div class="col-sm-5">
                                                            <div class="form-group"> 
                                                                <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="<?=$option['vOptionName']?>" placeholder="Option Name" >
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-5">
                                                            <div class="form-group"> 
                                                                <input type="text" class="form-control" id="OptPrice" name="OptPrice[]" required="required" value="<?=$option['fPrice']?>" placeholder="Price">
                                                                <input type="hidden" name="optType[]" value="Options" />
                                                      <input type="hidden" name="OptionId[]" value="<?=$option['iOptionId']?>" /><input type="hidden" name="eDefault[]" value="No"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <div class="input-group-btn"> 
                                                                        <button class="btn btn-danger" type="button" onclick="remove_options_fields('<?=$opt?>');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                          <? }
                                          } 
                                        }?>
                                                </div>
                                            </div>
                                        
                                         <?php } ?>
                                         <div id='allnewoptions'></div>

						<div class="panel panel-default servicecatresponsive">
                                            <div class="panel-heading">
                                                <div class="row" style="padding-bottom:0;">
                                                    <div class="col-lg-6"><h5><b><?php echo $langage_lbl_admin['LBL_ADDON_FRONT']?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Addon/Topping Price will be additional amount which will added in base price'></i></b></h5></div>
                                                    <div class="col-lg-6 text-right"><button class="btn btn-success" type="button"  onclick="addon_fields();"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button></div>
                                                </div>
                                            </div>
                                            <div class="panel-body" style="padding: 25px;">
                                                <div id="addon_fields">
                                                    <? if (count($db_addonsdata) > 0) { 
                                                        $a = 0;
                                                        foreach($db_addonsdata as $k => $addon){
                                                        $a++;?>
                                                        <div class="form-group removeclassaddon<?=$a?>"> 
                                                            <div class="col-sm-5">
                                                                <div class="form-group"> 
                                                                    <input type="text" class="form-control" id="AddonOptions" name="AddonOptions[]" value="<?=$addon['vOptionName']?>" placeholder="Topping Name" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <div class="form-group"> 
                                                                    <input type="text" class="form-control" id="AddonPrice" name="AddonPrice[]" value="<?=$addon['fPrice']?>" placeholder="Price" required>
                                                                    <input type="hidden" name="optTypeaddon[]" value="Addon" />
                                                                    <input type="hidden" name="addonId[]" value="<?=$addon['iOptionId']?>" />
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-btn"> 
                                                                            <button class="btn btn-danger" type="button" onclick="remove_addon_fields('<?=$a?>');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="clear"></div>
                                                        </div>
                                                    <? } } ?>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="row" >
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_OFFER_AMOUNT_MENU_ITEM']?>(%) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set Offer amount on an item, if you want to show discounted/strikeout amount. E.g If Item Price is $100 but you want to sell it for $80, then set Offer Amount = 20%, hence the final price of this item is $80'></i></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <input type="text" class="form-control" name="fOfferAmt"  id="fOfferAmt" value="<?= $fOfferAmt; ?>" />
                                        <small><?php echo $langage_lbl_admin['LBL_NOTE_FRONT']?> The % discount will be applied on Item price which includes (Base + options + toppings) prices.<br/>
                                        The item discount will only apply if the Global Offer " Offer Applies On" is set “NONE” from the restaurant setting. If your current offer type is “All order/ First Order” then, this discount won't apply.]</small>
                                    </div>
                                </div>
                                <div class="row" >
                                    <div class="col-lg-12">
                                        <label>Cooking Time <span class="red">*</span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='The preperation time required for the dish to cook.'></i></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <input type="text" class="form-control" name="cookingtime"  id="cookingtime" value="<?= $cookingtime; ?>" required />
                                        <small>The preperation time required for the dish to cook.</small>
                                    </div>
                                </div>	
								<div class="row servicecatresponsive">
                                    <div class="col-lg-12">
                                        <label>Food Type<span class="red">*</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <select class="form-control" name="eFoodType"  id="eFoodType">
											<option value="">--Select--</option>
                                            <option value="Veg" <?if($eFoodType == 'Veg'){echo 'selected';}?>>Veg Food</option>
                                            <option value="NonVeg" <?if($eFoodType == 'NonVeg'){echo 'selected';}?>>Non Veg Food</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                  <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_ITEM_IN_STOCK_WEB']?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="If this item is set On by the restaurant then it will be available for user\'s to order it, Set it off when the item is out of stock"></i></label>
                                  </div>
                                  <div class="col-lg-12">
                                        <div class="make-switch" data-on="success" data-off="warning" id="mySwitch">
                                      <input type="checkbox" name="eAvailable" <?=($id != '' && $eAvailable == 'No')?'':'checked';?> id="eAvailable" />
                                    </div>
                                  </div>
                                </div>
                                <div class="row">
                                  <div class="col-lg-12">
                                    <label><?php echo $langage_lbl_admin['LBL_IS_ITEM_RECOMMENDED']?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Suggest the user's to order this item. The recommended items will be highlighted in the user app with the image and display at the top section"></i></label>
                                  </div>
                                  <div class="col-lg-12">
                                    <div class="make-switch" data-on="success" data-off="warning" data-on-text="Yes" data-off-text="No" id="mySwitch1" >
                                      <input type="checkbox" name="eRecommended" <?=($id != '' && $eRecommended == 'No')?'':'checked';?> id="eRecommended" />
                                    </div>
                                  </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_ITEM_TAG_NAME']?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Set the tag name to this item. Like, Best Seller, Most Popular"></i></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <select class="form-control" name="vHighlightName"  id="vHighlightName">
                                            <option value="">Select Tag</option>
                                            <option value="LBL_BESTSELLER" <?if($vHighlightName == 'LBL_BESTSELLER'){echo 'selected';}?>><?php echo $langage_lbl_admin['LBL_BESTSELLER']?></option>
                                            <option value="LBL_NEWLY_ADDED" <?if($vHighlightName == 'LBL_NEWLY_ADDED'){echo 'selected';}?>><?php echo $langage_lbl_admin['LBL_NEWLY_ADDED']?></option>
                                            <option value="LBL_PROMOTED" <?if($vHighlightName == 'LBL_PROMOTED'){echo 'selected';}?>><?php echo $langage_lbl_admin['LBL_PROMOTED']?></option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> <?php echo $langage_lbl_admin['LBL_MENU_ITEM_FRONT'];?>" >
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            <div style="clear:both;"></div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<? include_once('footer.php');?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<!-- <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script> -->
<link href="../assets/css/imageUpload/bootstrap-imageupload.css" rel="stylesheet">
<!--For Faretype-->			
<script>
    $('[data-toggle="tooltip"]').tooltip();
    var successMSG1 = '<?php echo $success; ?>';
    if (successMSG1 != '') {
        setTimeout(function () {
            $(".msgs_hide").hide(1000)
        }, 5000);
    }
</script>
<!--For Faretype End--> 
<script>
function changeDisplayOrder(foodId, menuId, parentId)
{
    var itemParentId = '';
    if (parentId != '') {
        itemParentId = parentId
    }

    $.ajax({
        type: "POST",
        url: 'ajax_display_order.php',
        data: {iFoodMenuId: foodId, page: 'items', iMenuItemId: menuId},
        success: function (response)
        {
            $("#showDisplayOrder001").html('');
            $("#showDisplayOrder001").html(response);
        }
    });

    $.ajax({
        type: 'post',
        url: 'ajax_display_order.php',
        data: {method: 'getParentItems', page: 'items', iFoodMenuId: foodId, itemParentId: itemParentId},
        success: function (response) {
            $("#iParentId").html(response);
        },
        error: function (response) {
        }
    });
}

function changeMenuCategory(iCompanyId){
    var iFoodMenuId = '<?php echo $iFoodMenuId;?>';
    $.ajax({
        type: "POST",
        url: 'ajax_get_food_category.php',
        data: {iCompanyId: iCompanyId,iFoodMenuId:iFoodMenuId},
        success: function (response)
        {
            //console.log(response);
           $("#iFoodMenuId").html('');
            $("#iFoodMenuId").html(response);
        }
    });
}

var action = "<?= $action?>";
if(action == 'Add'){
  var iServiceIdNew = $("#iServiceId").val();
} else {
  var iServiceIdNew = "<?= $EditServiceIdNew ?>";
}

$(document).ready(function () {
    changeMenuCategory('<?php echo $iCompanyId; ?>','<?php echo $iFoodMenuId; ?>');
    changeDisplayOrder('<?php echo $iFoodMenuId; ?>', '<?php echo $id; ?>', '<?php echo $menuiParentId; ?>');

    var servicecounts = '<? echo count($service_cat_list)?>';
    if(servicecounts > '1'){
      changeserviceCategory(iServiceIdNew);
    }
});

function changeserviceCategory(iServiceId){
    var iCompanyId = '<?php echo $iCompanyId;?>';
    $.ajax({
        type: "POST",
        url: 'ajax_get_company_filter.php',
        data: {iServiceIdNew: iServiceId,iCompanyId:iCompanyId},
        success: function (response)
        {
            //console.log(response);
           $("#iCompanyId").html('');
            $("#iCompanyId").html(response);
        }
    });
}

function preview_mainImg(event)
{
    $("#single_img001").html('');
    $('#single_img001').append("<img src='" + URL.createObjectURL(event.target.files[0]) + "' class='thumbnail' style='max-width: 250px; max-height: 250px' >");
    $(".changeImg001").text('Change');
    $(".remove_main").show();
}

<? if(count($db_optionsdata) > 0){ ?>
var optionid ='<?=count($db_optionsdata)?>';
<?} else { ?>
var optionid = 0;
<?}?>


function options_fields(id) {
          var container_div = document.getElementById('options_fields' + id);
          var count = container_div.getElementsByTagName('div').length;
          if(count == 0){
            optionid = 0;
          }
    optionid++;
    var objTo = document.getElementById('options_fields')
    var divtest = document.createElement("div");
    divtest.setAttribute("class", "form-group removeclass"+optionid);
          if(optionid == '1' && id == '1'){
            var divtest1 = document.createElement("div");
            divtest1.setAttribute("class", "form-group eDefault");
            divtest1.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" value="Regular" placeholder="Option Name" required="required"></div></div><div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="OptPrice" name="OptPrice[]" required="required" value="0" placeholder="Price" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="Yes"/></div></div><div class="clear"></div>';
            objTo.appendChild(divtest1);
            divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="" placeholder="Option Name"></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="" required="required" placeholder="Price (In <?=$db_currency[0]['vName']?>)"><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_options_fields('+ optionid +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></div></div></div></div><div class="clear"></div>';
          } else {
            divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" value="" required="required" placeholder="Option Name"></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="" required="required" placeholder="Price (In <?=$db_currency[0]['vName']?>)" ><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_options_fields('+ optionid +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></div></div></div></div><div class="clear"></div>';
          }
          objTo.appendChild(divtest);
}
function remove_options_fields(rid) {
        var container_div = document.getElementById('options_fields');
        var count = container_div.getElementsByTagName('div').length;
        if(count == 16){
          $('.eDefault').remove();
          $('.removeclass'+rid).remove();
          var optionid = 0;
        } else {
          $('.removeclass'+rid).remove();
        }
}

<? if(count($db_addonsdata) > 0){ ?>
var addonid ='<?=count($db_addonsdata)?>';
<?} else { ?>
var addonid = 0;
<?}?>
function addon_fields() {
    addonid++;
    var objTo = document.getElementById('addon_fields')
    var divtest = document.createElement("div");
    divtest.setAttribute("class", "form-group removeclassaddon"+addonid);
    divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="AddonOptions" name="AddonOptions[]" value="" placeholder="Topping Name" required></div></div><div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="AddonPrice" name="AddonPrice[]" value="" placeholder="Price (In <?=$db_currency[0]['vName']?>)" required><input type="hidden" name="addonId[]" value="" /><input type="hidden" name="optTypeaddon[]" value="Addon" /></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_addon_fields('+ addonid +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></div></div></div></div><div class="clear"></div>';
    
    objTo.appendChild(divtest)
}
function remove_addon_fields(rid) {
   $('.removeclassaddon'+rid).remove();
}

$(document).ready(function() {
    var referrer;
    if($("#previousLink").val() == "" ){
        referrer =  document.referrer;  
        //alert(referrer);
    }else { 
        referrer = $("#previousLink").val();
    }
    if(referrer == "") {
        referrer = "menu_item.php";
    }else {
        $("#backlink").val(referrer);
    }
    $(".back_link").attr('href',referrer);
});

if(iServiceIdNew == '2' || iServiceIdNew == '3'){
    $('#eFoodType').removeAttr('required');
     $(".servicecatresponsive").hide();
} else if(iServiceIdNew == '1') {
    $(".servicecatresponsive").show();
    $("#eFoodType").attr("required", true);
} else if(iServiceIdNew == '') {
    $('#eFoodType').removeAttr('required');
    $(".servicecatresponsive").hide();
}

$(document).ready(function(){
    $("#iServiceId").change(function(){
		var iServiceid = $(this).val();
		if(iServiceid == '2' || iServiceid == '3'){
			$(".servicecatresponsive").hide();
			 $('#eFoodType').removeAttr('required');
		}else if(iServiceid == '1'){
			$(".servicecatresponsive").show();
            $("#eFoodType").attr("required", true);
		} else if(iServiceid == '') {
            $('#eFoodType').removeAttr('required');
            $(".servicecatresponsive").hide();
        }
    });
});


$(document).ready(function(){

$('#nOPtions').on('change', function (e) {
    
        alert();
        });


    });
</script>

</body>
<!-- END BODY-->
</html>