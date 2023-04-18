<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

if (isset($_REQUEST['vPromotionName'])) {
    if ($_REQUEST['vPromotionName'] != "") {
        $user_name = $_REQUEST['vPromotionName'];
        $sql = "SELECT vPromotionName FROM promotions WHERE vPromotionName = '" . $user_name . "' ";
        $db_comp = $obj->MySQLSelect($sql);

        if (count($db_comp) > 0) {
            echo 0;
        } else {
            echo 1;
        }
    } else {
        echo 2;
    }
}
?>