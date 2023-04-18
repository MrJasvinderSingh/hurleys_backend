<?php

include_once('../../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$tbl_name 	= 'militarylocations';
if (isset($_POST["import"])) {
    
    $fileName = $_FILES["file"]["tmp_name"];
    
    if ($_FILES["file"]["size"] > 0) {
        
        $file = fopen($fileName, "r");
        
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            $q = "INSERT INTO ";
            $query1 = $q ." `".$tbl_name."` SET
			`base` = '".$column[0]."',
			`region` = '".$column[1]."',
			`building` = '".$column[2]."',
			`latitude` = '".$column[3]."',
			`longitude` = '".$column[4]."',
			`remarks` = '".$column[5]."',
                        `eStatus` = '".$column[5]."',
                        `created` = '".date('Y-m-d H:i:s')."',   
                        `modified` = '".date('Y-m-d H:i:s')."'"
                    
			.$where; //die;
			$obj->sql_query($query1);
            $result =  $obj->GetInsertId();
            if (! empty($result)) {
                $type = "success";
                $message = "CSV Data Imported into the Database";
                
            } else {
                $type = "error";
                $message = "Problem in Importing CSV Data";
            }
        }
         $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = 'Import completed successfully.';  
        header("Location:".$tconfig["tsite_url_main_admin"]."militarylocations.php?".$parameters); exit;
    }
}

