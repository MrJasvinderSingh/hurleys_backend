<?php
include_once 'common.php';
$generalobj->go_to_home();
$action = isset($_GET['action'])?$_GET['action']:'';
$iscompany = isset($_GET['iscompany'])?$_GET['iscompany']:'0';
$type = "Driver";

if($iscompany == "1"){
	$_SESSION['postDetail']['user_type'] = "company";
	$type = "Company";
}
$forpsw =  isset($_REQUEST['forpsw'])?$_REQUEST['forpsw']:'';
$forgetPWd =  isset($_REQUEST['forgetPWd'])?$_REQUEST['forgetPWd']:'';
$depart = '';
if(isset($_REQUEST['depart'])) {
	$_SESSION['sess_depart'] = $_REQUEST['depart'];
	$depart = $_SESSION['sess_depart'];
} else {
	if(isset($_REQUEST['depart'])) { unset($_SESSION['sess_depart']); }
}

$err_msg = "";
if(isset($_SESSION['sess_error_social'])){
	$err_msg = $_SESSION['sess_error_social'];
	unset($_SESSION['sess_error_social']);
	unset($_SESSION['fb_user']);			//facebook
	unset($_SESSION['oauth_token']);		//twitter
	unset($_SESSION['oauth_token_secret']); //twitter
	unset($_SESSION['access_token']);		//fa-google
}

if($action == 'driver' && $iscompany != "1"){
	$meta_arr = $generalobj->getsettingSeo(9);		
} elseif($action == 'rider') {	
	$meta_arr = $generalobj->getsettingSeo(8);		
} elseif($action == 'driver' &&  $iscompany == "1") { 
 	$meta_arr = $generalobj->getsettingSeo(10);  
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $meta_arr['meta_title'];?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
</head>
<body>
<!-- Login page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
	<!-- Top Menu -->
    <?php include_once("top/header_topbar.php");?>
    <!-- End: Top Menu-->
	<!-- Login inner page-->

	<div class="page-contant">
		<div class="page-contant-inner login-part">
			<h2 class="header-page" id="label-id"><?=$langage_lbl['LBL_SIGN_IN_TXT'];?>
				<?if(SITE_TYPE =='Demo'){?>
				<p><?=$langage_lbl['LBL_SINCE_IT_IS_DEMO'];?></p>
				<? } ?>
			</h2>
			<!-- login in page -->
			<div class="login-form">
			<div class="login-err">
			<p id="errmsg" style="display:none;" class="text-muted btn-block btn btn-danger btn-rect error-login-v"></p>
			<p style="display:none;" class="btn-block btn btn-rect btn-success error-login-v" id="success" ></p>
			</div>
					<div class="login-form-left">
					<? if($action == 'rider'){
						$action_url ='mytrip.php';
					} else if($action == 'driver' && $iscompany != "1"){
						$action_url ='profile.php';
					} else {
						$action_url ='dashboard.php';
					}?>
					<form action="<?=$action_url;?>" class="form-signin" method="post" id="login_box" onSubmit="return chkValid('<?=$action?>','<?=$iscompany?>');" >	
						<b>
							<input type="hidden" name="action" value="<?echo $action?>"/>
							<input type="hidden" name="type_usr" value="<?echo $type?>"/>

                            <label>Email or Mobile No.<?//=$langage_lbl['LBL_EMAIL_MOBILE_NO_TXT_MSG']; ?></label>
							<input name="vEmail" type="text" placeholder="Enter Email ID or Mobile Number<?//=$langage_lbl['ENTER_EMAIL_ID_OR_MOBILE_TXT']; ?>" class="login-input" id="vEmail" value="<?=(SITE_TYPE == 'Demo') ? (($action == 'rider') ? $rider_email:$driver_email) : '';?>" required />
						</b>
                        <b>
                            <label><?=$langage_lbl['LBL_COMPANY_DRIVER_PASSWORD']; ?></label>
							<input name="vPassword" type="password" placeholder="<?=$langage_lbl['LBL_PASSWORD_LBL_TXT']; ?>" class="login-input" id="vPassword" value="<?=(SITE_TYPE == 'Demo') ? '123456' : ''?>" required />
						</b> 
						<b>
							<input type="submit" class="submit-but" value="<?=$langage_lbl['LBL_SIGN_IN_TXT'];?>" />
							<a onClick="change_heading('forgot')"><?=$langage_lbl['LBL_FORGET_PASS_TXT'];?></a>
						</b> 
					</form>					
                    <form action="" method="post" class="form-signin" id="frmforget" onSubmit="return forgotPass();" style="display: none;">
                     
						<input type="hidden" name="action" id="action" value="<?=$action?>">
						<input type="hidden" name="type_usr" value="<?echo $type?>"/>
						<b>
	                        <label><?=$langage_lbl['LBL_EMAIL_LBL_TXT']; ?></label>
							<input name="femail" type="text" placeholder="<?=$langage_lbl['LBL_EMAIL_LBL_TXT']; ?>" class="login-input" id="femail" value="" required />
						</b>
						<b>
							<input type="submit" class="submit-but" value="<?=$langage_lbl['LBL_Recover_Password']; ?>" />
							<a onClick="change_heading('login')"><?=$langage_lbl['LBL_LOGIN'];?></a>
						</b>	 
					</form>	
					</div>					
				
				<div class="login-form-right login-form-right1">
				<div class="login-form-right1-inner">
					      <h3><?=$langage_lbl['LBL_DONT_HAVE_ACCOUNT'];?></h3>
					      <? if($action == 'rider') {
					      	$loginlink = 'sign-up-rider';
					      } else if($action == 'driver' && $iscompany != "1") {
					      	$loginlink = 'sign-up';
					      } else {
					      	$loginlink = 'sign-up-restaurant';
					      }?>
					      <span><a  class="company" href="<?=$loginlink?>"><?=$langage_lbl['LBL_LOGIN_NEW_SIGN_UP'];?></a></span> 
			</div>
			<? if($iscompany == "0"){ ?>
			<div class="login-form-right1-inner">
			<?php ?>
				<?php  if($action=='driver'){ 
				if($DRIVER_TWITTER_LOGIN == "Yes" || $DRIVER_GOOGLE_LOGIN == "Yes" || $DRIVER_FACEBOOK_LOGIN == "Yes"){ ?>
             	<h3><?=$langage_lbl['LBL_REGISTER_WITH_ONE_CLICK'];?></h3>
			<?php } ?>
					<span class="login-socials">
					<?php if($DRIVER_FACEBOOK_LOGIN == "Yes"){ ?>						
						<a href="facebook/<?=$action?>" class="fa fa-facebook"></a>
					<?php } 
					if($DRIVER_TWITTER_LOGIN == "Yes"){ ?>
						
						<a href="twitter/<?=$action?>" class="fa fa-twitter"></a>
					<?php } if($DRIVER_GOOGLE_LOGIN == "Yes"){ ?>
						
						<a href="google/<?=$action?>" class="fa fa-google"></a>
					<?php } ?>
						
					</span>
			<?php } 
			if($action=='rider'){
			if($PASSENGER_FACEBOOK_LOGIN == "Yes" || $PASSENGER_TWITTER_LOGIN == "Yes" || $PASSENGER_GOOGLE_LOGIN == "Yes"){ ?>
             	<h3><?=$langage_lbl['LBL_REGISTER_WITH_ONE_CLICK'];?></h3>
			<?php } ?>
				<span class="login-socials">
				<?php if($PASSENGER_FACEBOOK_LOGIN == "Yes"){?>
				
					<a href="facebook-rider/<?=$action?>" class="fa fa-facebook"></a>
				<?php } 
				if($PASSENGER_TWITTER_LOGIN == "Yes"){ ?>
					
					<a href="twitter/<?=$action?>" class="fa fa-twitter"></a>
				<?php } if($PASSENGER_GOOGLE_LOGIN == "Yes"){ ?>
					
					<a href="google/<?=$action?>" class="fa fa-google"></a>
				<?php } ?>
					
				</span>
			<?php } ?>
			  
			</div>
			<? } ?>
			</div>   
			</div>
				
			<div style="clear:both;"></div>
		</div>
	</div>

	<!-- Login inner page end-->

	<!-- footer part -->
    <?php include_once('footer/footer_home.php');?>
    <!-- footer part end -->

    <div style="clear:both;"></div>
</div>
<!-- Login page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<!-- End: Footer Script -->
<script>
	<?php if($forgetPWd==1){ ?>
			$('#frmforget').show();
			$('#login_box').hide();
			$('#label-id').text("<?=$langage_lbl['LBL_FORGOR_PASSWORD'];?>");
	<?php } ?>
	
	function change_heading(type)
	{
		$('.error-login-v').hide();
		if(type=='forgot'){
			
			$('#frmforget').show();
			$('#login_box').hide();
			$('#label-id').text("<?=addslashes($langage_lbl['LBL_FORGOR_PASSWORD']);?>");
		}				
		else{
			$('#frmforget').hide();
			$('#login_box').show();
			$('#label-id').text("<?=addslashes($langage_lbl['LBL_SIGN_IN_TXT']);?>");
		}
	}
	function chkValid(login_type,iscompany='')
	{
		var id = document.getElementById("vEmail").value;
		var pass = document.getElementById("vPassword").value;
		if(id == '' || pass == '')
		{
			document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_EMAIL_PASS_ERROR_MSG']);?>';
			document.getElementById("errmsg").style.display = '';
			return false;
		}
		else
		{
			var request = $.ajax({
				type: "POST",
				url: 'ajax_login_action.php',
				data: $("#login_box").serialize(),

				success: function(data)
				{
					if(data == 1){
						document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_ACC_DELETE_TXT']);?>';
						document.getElementById("errmsg").style.display = '';
						return false;
					}
					else if(data == 2){
						document.getElementById("errmsg").style.display = 'none';
						departType = '<?php echo $depart; ?>';
						if(login_type == 'rider' && departType == 'mobi')
							window.location = "mobi";
						else if(login_type == 'rider')
							window.location = "profile_rider.php";
						else if(login_type == 'driver' && iscompany == "1")
							window.location = "dashboard.php";
						else if(login_type == 'driver' && iscompany == "0")
							window.location = "profile.php";

						return true; // success registration
					}
					else if(data == 3) {
						document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_INVALID_EMAIL_MOBILE_PASS_ERROR_MSG']);?>';
						document.getElementById("errmsg").style.display = '';
					   return false;

					}else if(data == 4) {
						document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG']);?>';
						document.getElementById("errmsg").style.display = '';
					   return false;

					}
					else {
						document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_INVALID_EMAIL_MOBILE_PASS_ERROR_MSG']);?>';
						document.getElementById("errmsg").style.display = '';
						//setTimeout(function() {document.getElementById('errmsg1').style.display='none';},2000);
						return false;
					}
				}
			});

			request.fail(function(jqXHR, textStatus) {
				alert( "Request failed: " + textStatus );
				return false;
			});
			return false;
		}
	}
	function forgotPass()
	{
		$('.error-login-v').hide();
		var site_type='<?echo SITE_TYPE;?>';
		var id = document.getElementById("femail").value;
		if(id == '')
		{
			document.getElementById("errmsg").style.display = '';
			document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR_TXT_IPHONE']);?>';
		}
		else {
			var request = $.ajax({
				type: "POST",
				url: 'ajax_fpass_action.php',
				data: $("#frmforget").serialize(),
				dataType: 'json',
				beforeSend:function()
				{
					//alert(id);
					},
				success: function(data)
				{

					if(data.status == 1)
					{
						change_heading('login');
						document.getElementById("success").innerHTML = data.msg;
						document.getElementById("success").style.display = '';
						
					}
					else
					{
						document.getElementById("errmsg").innerHTML = data.msg;
						document.getElementById("errmsg").style.display = '';
					}
					
				}
			});

			request.fail(function(jqXHR, textStatus) {
				alert( "Request failed: " + textStatus );
			});

			
		}
		return false;
	}

	function fbconnect()
	{
		javscript:window.location='fbconnect.php';
	}
	
	$(document).ready(function(){
		var err_msg = '<?=$err_msg?>';
		// alert(err_msg);
		if(err_msg != ""){
			document.getElementById("errmsg").innerHTML = err_msg;
			document.getElementById("errmsg").style.display = '';
			return false;
		}
	});
</script>
<?php 
if($forpsw == 1){ ?>
	<script>
		change_heading('forgot');
	</script>
<?php } ?>
</body>
</html>
