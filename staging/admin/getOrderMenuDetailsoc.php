<?php
include_once('../common.php');
include_once('../generalFunctions.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$default_lang = $generalobj->get_default_lang();
$iOrderId = isset($_REQUEST['iOrderId']) ? $_REQUEST['iOrderId'] : '';
$script = "All Orders";
$tbl_name = 'orders';

$db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, '', '');
?>

<!--<div class="clearfix"><div class="col-md-12 text-center"><strong style="text-align:center;width: 100%; text-decoration:underline;">ORDER DETAILS</strong></div></div>-->
<?php $db_menu_item_list = $db_order_data['itemlist']; ?>
<?php if (!empty($db_menu_item_list)) { ?>
            <?php foreach ($db_menu_item_list as $key => $val) { ?>
                <div class="col-md-12 clearfix">
                    <div class="col-md-6"><?= $val['MenuItem']; ?> X <?php echo $val['iQty']; ?><br/>
                        
                    </div>
                   <div class="col-md-6"><?php //= $val['fTotPrice'] ?> <?if($val['SubTitle'] != ''){?>
                        <small style="font-size: 12px;">(<?= $val['SubTitle']; ?>)</small></div>
                        <? } ?>
                </div>
            <?php } ?>
<?php } ?>
