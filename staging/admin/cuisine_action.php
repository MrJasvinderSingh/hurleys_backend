<?
	include_once('../common.php');

	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();

	$id 		= isset($_REQUEST['id'])?$_REQUEST['id']:'';
	$success	= isset($_REQUEST['success'])?$_REQUEST['success']:0;
	$action 	= ($id != '')?'Edit':'Add';
	
	$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $iServiceIdNew = isset($_POST['iServiceId'])?$_POST['iServiceId']:'';

	$tbl_name 	= 'cuisine';
	$script = 'Cuisine';

	$vTitle_store = array();
	$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
	$db_master = $obj->MySQLSelect($sql);
	$count_all = count($db_master);
	if ($count_all > 0) {
	    for ($i = 0; $i < $count_all; $i++) {
	        $vValue = 'cuisineName_' . $db_master[$i]['vCode'];
	        array_push($vTitle_store, $vValue);
	        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
	    }
	}
	// set all variables with either post (when submit) either blank (when insert)
	$eStatus_check = isset($_POST['eStatus'])?$_POST['eStatus']:'off';
	$eStatus = ($eStatus_check == 'on')?'Active':'Inactive';

	if(isset($_POST['submit'])) {

		if(SITE_TYPE=='Demo')
		{
			header("Location:cuisine_action.php?id=".$id.'&success=2');
			exit;
		}

		for ($i = 0; $i < count($vTitle_store); $i++) {
			 $vValue = 'cuisineName_' . $db_master[$i]['vCode'];
			$q = "INSERT INTO ";
			$where = '';

			if($id != '' ){
				$q = "UPDATE ";
				$where = " WHERE `cuisineId` = '".$id."'";
			}

			$query = $q ." `".$tbl_name."` SET
			`iServiceId`= '".$iServiceIdNew."',
			" . $vValue . " = '" . $_POST[$vTitle_store[$i]] . "'"
			.$where;
			
			$obj->sql_query($query);
			$id = ($id != '')?$id:$obj->GetInsertId();
		}

	    if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = 'Service Category Insert Successfully.';
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = 'Service Category Updated Successfully.';
        }
		 header("location:".$backlink);

	}

	// for Edit
	if($action == 'Edit') {
		$sql = "SELECT * FROM ".$tbl_name." WHERE cuisineId = '".$id."'";
		$db_data = $obj->MySQLSelect($sql);

    	$vLabel = $id;
	    if (count($db_data) > 0) {
	        for ($i = 0; $i < count($db_master); $i++) {

	            foreach ($db_data as $key => $value) {
	            	$cuisineId = $value['cuisineId'];
	                $vValue = 'cuisineName_' . $db_master[$i]['vCode'];
	                $$vValue = $value[$vValue];
	                $eStatus = $value['eStatus'];
	                $iServiceIdNew= $value['iServiceId'];
	            }
	        }
	    }
	}
$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata,true);
foreach ($allservice_cat_data as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_".$default_lang." as servicename,eStatus FROM service_categories WHERE iServiceId IN (".$serviceIds.") AND eStatus = 'Active'";
$service_cat_list = $obj->MySQLSelect($service_category);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title>Admin | Service Category <?=$action;?></title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />

		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		<? include_once('global_files.php');?>
		<!-- On OFF switch -->
		<link href="../assets/css/jquery-ui.css" rel="stylesheet" />
		<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
	</head>
	<!-- END  HEAD-->
	<!-- BEGIN BODY-->
	<body class="padTop53 " >

		<!-- MAIN WRAPPER -->
		<div id="wrap">
			<? include_once('header.php'); ?>
			<? include_once('left_menu.php'); ?>
			<!--PAGE CONTENT -->
			<div id="content">
				<div class="inner">
					<div class="row">
						<div class="col-lg-12">
							<h2><?=$action;?> Service Category</h2>
							<a href="cuisine.php" class="back_link">
								<input type="button" value="Back to Listing" class="add-btn">
							</a>
						</div>
					</div>
					<hr />
					<div class="body-div">
						<div class="form-group">
							<? if($success == 1) { ?>
								<div class="alert alert-success alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									Record Updated successfully.
								</div><br/>
								<? }elseif ($success == 2) { ?>
									<div class="alert alert-danger alert-dismissable">
											 <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
											 "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
									</div><br/>
								<? }?>
							<form method="post" name="_cuisine_form" id="_cuisine_form" action="">
								<input type="hidden" name="id" value="<?=$id;?>"/>
								<input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
								<input type="hidden" name="backlink" id="backlink" value="cuisine.php"/>
								<? if($count_all > 0) {
                                    for($i=0;$i<$count_all;$i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $vValue = 'cuisineName_'.$vCode;
                                        $required = ($eDefault == 'Yes')?'required':'';
                                        $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':''; ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Service Category Label (<?=$vTitle;?>)<?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?>Value" <?= $required; ?>>
                                            </div>
            								    <? if($vCode == $default_lang  && count($db_master) > 1){ ?>
            										<div class="col-lg-6">
            											<button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode();">Convert To All Language</button>
            										</div>
            								    <?php } ?>
                                        </div>
                                    <? }
                                } ?>
                                <?php if(count($allservice_cat_data)<=1){
									if(isset($service_cat_list[0]['iServiceId'])){
										$serviceiddd = $service_cat_list[0]['iServiceId'];
									}else{
										$serviceiddd = 1;
									}
									?>
								<input name="iServiceId" type="hidden" class="create-account-input" value="<?php echo $serviceiddd;?>"/>
								<?php } else { ?>
								<div class="row">
                                    <div class="col-lg-12">
                                        <label>Service Type<span class="red"> *</span></label>
                                    </div>
									<div class="col-lg-6">
										<select class="form-control" name = 'iServiceId' id="iServiceId" required>
										   <option value="">Select</option>
										   <? for($i=0;$i<count($service_cat_list);$i++){ ?>
										   <option value = "<?= $service_cat_list[$i]['iServiceId'] ?>" <?if($iServiceIdNew == $service_cat_list[$i]['iServiceId']) { ?> selected <?php } else if($iServiceIdNew==$service_cat_list[$i]['iServiceId']){?>selected<? } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
										   <? } ?>
										</select>
									 </div>
								</div>
								<?php } ?>
<!-- 								<div class="row">
									<div class="col-lg-12">
										<label>Status</label>
									</div>
									<div class="col-lg-6">
										<div class="make-switch" data-on="success" data-off="warning">
											<input type="checkbox" name="eStatus" <?=($id != '' && $eStatus == 'Inactive')?'':'checked';?>/>
										</div>
									</div>
								</div> -->
								<div class="row">
									<div class="col-lg-12">
										<input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?=$action;?> Service Category">
										<input type="reset" value="Reset" class="btn btn-default">
                                        <a href="cuisine.php" class="btn btn-default back_link">Cancel</a>
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
		<div class="row loding-action" id="imageIcon" style="display:none;">
          <div align="center">                                                                       
            <img src="default.gif">                                                              
            <span>Language Translation is in Process. Please Wait...</span>                       
          </div>                                                                                 
        </div>

		<? include_once('footer.php');?>
		<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
	</body>
	<!-- END BODY-->
</html>
<script>
$(document).ready(function(){
    $('#imageIcon').hide();
});
$(document).ready(function() {
	var referrer;
	if($("#previousLink").val() == "" ){
		referrer =  document.referrer;
	} else {
		referrer = $("#previousLink").val();
	}

	if(referrer == "") {
		referrer = "cuisine.php";
	} else {
		$("#backlink").val(referrer);
	}
	$(".back_link").attr('href',referrer); 
});
function getAllLanguageCode(){
      var def_lang = '<?=$default_lang?>';
	  var def_lang_name = '<?=$def_lang_name?>';
      var getEnglishText = $('#cuisineName_'+def_lang).val();
      var error = false;
      var msg = '';
      
      if(getEnglishText==''){
          msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please Enter '+def_lang_name+' Value</strong></div> <br>';
          error = true;
      }
      
      if(error==true){
              $('#errorMessage').html(msg);
              return false;
      }else{
        $('#imageIcon').show();
        $.ajax({
                url: "ajax_get_all_language_translate.php",
                type: "post",
                data: {'englishText':getEnglishText},
                dataType:'json',
                success:function(response){
                     $.each(response,function(name, Value){
                        var key = name.split('_');
                        $('#cuisineName_'+key[1]).val(Value);
                     });
                     $('#imageIcon').hide();
                }
        });
      }
}
</script>