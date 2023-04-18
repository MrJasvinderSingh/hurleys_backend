<?

	include_once('../common.php');
	global $obj;
	if(!isset($generalobjAdmin)) {
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	
	$sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId'])?$_SESSION['sess_iAdminUserId']:'';
	$sess_iGroupId = isset($_SESSION['sess_iGroupId'])?$_SESSION['sess_iGroupId']:'';
	if($sess_iAdminUserId == "" && basename($_SERVER['PHP_SELF']) != "index.php") {
		echo 'loggedout';
		die;
	}
	
	$tbl_name = "custom_notifications";
	$response = array();
	if(isset($_POST) && $_POST['type'] == 'add'){
		$id 				= $_POST['id'];
		$data['message'] 	= $_POST['message'];
		$data['lat'] 		= $_POST['lat'];
		$data['lon'] 		= $_POST['lon'];
		if($id != ''){
			$where = " id = '$id'";
			$obj->MySQLQueryPerform($tbl_name, $data, 'update', $where);
		}else{
			$id = $obj->MySQLQueryPerform($tbl_name, $data, 'insert');
		}
	}
	if(isset($_POST) && $_POST['type'] == 'delete'){
		
		$id = $_POST['id'];
		if($id != ''){
			$where = " id = '$id'";
			$data['status'] = 'deleted';
			$obj->MySQLQueryPerform($tbl_name, $data, 'update', $where);
		}
	}
	$response = array('id'=>$id);
	echo json_encode($response);
?>