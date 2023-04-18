<?php
	include_once('common.php');
	$ssql = "";
	if(isset($_REQUEST['iUserId']) && $_REQUEST['iUserId'] != "") {
		$ssql = " AND iUserId!='".$_REQUEST['iUserId']."'";
	}
	
	if(isset($_REQUEST['vPhone']))
	{
		$vPhone=$_REQUEST['vPhone'];
		$sql = "SELECT vPhone,eStatus FROM register_user WHERE vPhone = '".$vPhone."'".$ssql;
		$db_user = $obj->MySQLSelect($sql);
			
		if(count($db_user)>0)
		{
			if((ucfirst($db_user[0]['eStatus']) == 'Deleted') || (ucfirst($db_user[0]['eStatus']) == 'Inactive')){ 
				echo 'deleted';
			} else {
				echo 'false';
			}
				//echo 'false';
		}
		else
		{	
				echo 'true';
		}
	}
?>