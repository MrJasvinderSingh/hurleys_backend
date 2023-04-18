<?php
//$APP_TYPE = 'UberX';
//echo $APP_TYPE; exit;
if($APP_TYPE == 'UberX'){
	include('left_menu_ufx.php');
	//include('left_menu_uberapp.php');
}else{
	include('left_menu_uberapp.php');
}
 ?>