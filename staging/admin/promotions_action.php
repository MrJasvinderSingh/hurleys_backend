<?php
include_once('../common.php');

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$script = "Promotion";
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$id=$_GET['id'];

//For Currency
/*$sql="select vSymbol from  currency where eDefault='Yes'";
$db_currency=$obj->MySQLSelect($sql);*/

$iPromotionId = isset($_REQUEST['iPromotionId']) ? $_REQUEST['iPromotionId'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($iPromotionId != '') ? 'Edit' : 'Add';

$tbl_name = 'promotions';

// set all variables with either post (when submit) either blank (when insert)
$iPromotionId = isset($_REQUEST['iPromotionId']) ? $_REQUEST['iPromotionId'] : '';
$vPromotionName = isset($_REQUEST['vPromotionName']) ? $_REQUEST['vPromotionName'] : '';
$vPromotionTitle = isset($_REQUEST['vPromotionTitle']) ? $_REQUEST['vPromotionTitle'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

if (isset($_POST['submit'])) {

      if(!empty($iPromotionId)){
          if(SITE_TYPE=='Demo')
          {
            header("Location:promotions_action.php?iPromotionId=" . $iPromotionId . '&success=2');
            exit;
          }
          
      }
	  require_once("library/validation.class.php");
    $validobj = new validation();
	$validobj->add_fields($_POST['vPromotionName'], 'req', 'promotion name is required');
	$validobj->add_fields($_POST['vPromotionTitle'], 'req', 'Promotion title is required');
	$error = $validobj->validate();

if ($error) {
        $success = 3;
        $newError = $error;
    } 
	else 
	{
		$q = "INSERT INTO ";
		$where = '';
		if ($action == 'Edit') {
			$str = " ";
		} else {
			$str = " , eStatus = 'Inactive' ";
		}
	 
		if(SITE_TYPE=='Demo')
		{
			$str = " , eStatus = 'active' ";
		}

	
	 
		if ($ipromotionId != '') {
			$q = "UPDATE ";
			$where = " WHERE `ipromotionId` = '" . $ipromotionId . "'";
		}        
		$query = $q . " `" . $tbl_name . "` SET
		`vPromotionName` = '" . $vPromotionName . "',
		`vPromotionTitle` = '" . $vPromotionTitle. "',
		`eStatus` = '" . $eStatus . "'" . $where;
		$obj->sql_query($query);

		if ($action == "Add") {
			$_SESSION['success'] = '1';
			$_SESSION['var_msg'] = 'Promotion Insert Successfully.';
		} else {
			$_SESSION['success'] = '1';
			$_SESSION['var_msg'] = 'Promotion Updated Successfully.';
		}
		header("Location:".$backlink);exit;
  }
}
// for Edit

if ($action == 'Edit') {
     $sql = "SELECT * FROM " . $tbl_name . " WHERE iPromotionId = '" . $iPromotionId . "'";
     $db_data = $obj->MySQLSelect($sql);
     $vPass = $generalobj->decrypt($db_data[0]['vPassword']);
     $vLabel = $id;
     if (count($db_data) > 0) {
          foreach ($db_data as $key => $value) {
               $vPromotionName = $value['vPromotionName'];
               $vPromotionTitle = $value['vPromotionTitle'];
               $eStatus = $value['eStatus'];
               
          }
     }
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
<meta charset="UTF-8" />
<title>Admin | Promotions <?= $action; ?> </title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />

<link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
<? include_once('global_files.php'); ?>
<!-- On OFF switch -->
<link href="../assets/css/jquery-ui.css" rel="stylesheet" />
<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />

</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
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
          <h2>
            <?= $action; ?>
            Promotions
            <?= $vName; ?>
          </h2>
          <a href="javascript:void(0);" class="back_link">
          <input type="button" value="Back to Listing" class="add-btn">
          </a> </div>
      </div>
      <hr />
      <div class="body-div promotion-action-part">
        <div class="form-group"> 
        <span style="color:red; font-size:small;" id="promotion_status"></span>
          <? if ($success == 3) {?>
          <div class="alert alert-danger alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
			<?php print_r($error); ?>
           </div>
          <br/>
          <? } ?>
          <form name="_promotion_form" id="_promotion_form" method="post" action="" enctype="multipart/form-data" class="">
            <input type="hidden" name="ipromotionId" value="<?php if(isset($db_data[0]['ipromotionId'])){echo $db_data[0]['ipromotionId'];} ?>">
			<input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
			<input type="hidden" name="backlink" id="backlink" value="admin.php"/>
			<input type="hidden" name="vpromotionNameval" id="vpromotionNameval" value="<?= $vPromotionName; ?>"/>
			
            <div class="row promotion-action-n1">
              <div class="col-lg-12">
                <label>promotion Name :<span class="red"> *</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="vPromotionName"  id="vPromotionName" value="<?= $vPromotionName; ?>" placeholder="Promotion name" >

				      </div>
            </div>
            <div class="row">
              <div class="col-lg-12">
                <label>Promotion Title :<span class="red"> *</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" name="vPromotionTitle" class="form-control" id="vPromotionTitle" placeholder="Promotion Title" value="<?=$vPromotionTitle;?>" >
              </div>
            </div>
            <div class="row promotion-action-n2">
              <div class="col-lg-12">
                <label>Promotion Image :<span class="red"> *</span></label>
              </div>
              <div class="col-lg-6">
                <input type="file" class="form-control" name="vpromotionTmage" id="vpromotionTmage" value="" >
                
              </div>
            </div>
          
            <div class="row promotion-action-n3">
              <div class="col-lg-12">
                <label>Status<span class="red"> *</span></label>
              </div>
              <div class="col-lg-6">
                <select id="eStatus" name="eStatus" class="form-control ">
                  <option value="Active" <?php if($db_data[0]['eStatus'] == "Active"){ ?>selected <?php } ?> >Active</option>
                  <option value="Inactive" <?php if($db_data[0]['eStatus'] == "Inactive"){?>selected <?php } ?> >Inactive</option>
                </select>
              </div>
            </div>
            <div class="row promotion-action-n4">
              <div class="col-lg-12">
                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Promotion">
                 <input type="reset" value="Reset" class="btn btn-default">
                <a href="promotions.php" class="btn btn-default back_link">Cancel</a>
			  </div>
            </div>
          </form>
        </div>
        <div class="clear"></div>
      </div>
    </div>
  </div>
  <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<?
          include_once('footer.php');
          ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
			function validate_promotion(username)
	        {
				var request = $.ajax({
				type: "POST",
				url: 'ajax_validate_promotions.php',
				data: 'vPromotionName=' +username,
				success: function (data)
				{
					if(data==0)
					{
						$('#promotion_status').html('<i class="icon icon-remove alert-danger alert"> 	Promotion Already Exist</i>');
						$('input[type="submit"]').attr('disabled','disabled');
						return false;
					}
					else if(data==1)
					{
						$('#promotion_status').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
						$('vPromotionName[type="submit"]').removeAttr('disabled');
					}
					else if(data==2)
					{
						$('#promotion_status').html('<i class="icon icon-remove alert-danger alert"> Please Enter Promotion</i>');
						$('vPromotionName[type="submit"]').removeAttr('disabled');
					}
				}
	            });
	        }
		  </script>

<?php if ($action == 'Edit') { ?>
<script>
	window.onload = function () {
		showhidedate('<?php echo $eValidityType; ?>');
	};
</script>
<?}else{ ?>
<script>
	window.onload = function () {     
		$('input:radio[name=eValidityType][value=Permanent]').attr('checked', true);
	};
</script>
<?php } ?>
<script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>

<script type="text/javascript">	 		
$(document).ready(function() {
	var referrer;
	if($("#previousLink").val() == "" ){
		referrer =  document.referrer;	
	}else { 
		referrer = $("#previousLink").val();
	}
	if(referrer == "") {
		referrer = "promotions.php";
	}else {
		$("#backlink").val(referrer);
	}
	$(".back_link").attr('href',referrer);
});



</script>
<?php if ($action != 'Edit'){?>
<script>
	randomStringToInput(document.getElementById("vPromotionName"));
</script>
<?php }?>
</body>
</html>