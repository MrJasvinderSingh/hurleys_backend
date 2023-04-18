<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once('common.php');
include_once('generalFunctions.php');
require_once('assets/libraries/pubnub/autoloader.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

//$generalobjAdmin->check_member_login();
$default_lang = $generalobj->get_default_lang();

$order_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$processing_status_array = array('1', '2', '4', '5');
$all_status_array = array( '1', '2', '4', '5','6', '7', '8', '9', '11', '12');

if ($_REQUEST['iStatusCode'] != '') {
    $all_status_array = array($_REQUEST['iStatusCode']);
}
if ($order_type == 'processing') {
    $iStatusCode = '(' . implode(',', $processing_status_array) . ')';
} else {
    $iStatusCode = '(' . implode(',', $all_status_array) . ')';
}

$ord = ' ORDER BY o.iOrderId DESC';

$per_page = 100;//$DISPLAY_RECORD_NUMBER; // 
$year  = date('Y');
$month  = date('m');
$day  = date('d');
$sql = "SELECT o.fSubTotal,o.print,o.iServiceid,sc.vServiceName_" . $default_lang . " as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.cookingtime,o.deliverytime,o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fpretip,o.posttip,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE o.iStatusCode IN $iStatusCode and MONTH(tOrderRequestDate) = $month and DAY(tOrderRequestDate) = $day and YEAR(tOrderRequestDate) = $year $ord LIMIT $per_page";

$DBProcessingOrders = $obj->MySQLSelect($sql);
if(count($DBProcessingOrders) == 0){
	$sql = "SELECT o.fSubTotal,o.print,o.iServiceid,sc.vServiceName_" . $default_lang . " as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.cookingtime,o.deliverytime,o.iOrderId,o.vOrderNo,o.iUserId,o.iUserAddressId,o.dDeliveryDate,o.tOrderRequestDate,o.ePaymentOption,o.tOrderRequestDate,o.fpretip,o.posttip,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE o.iStatusCode IN $iStatusCode $ord LIMIT $per_page";

$DBProcessingOrders = $obj->MySQLSelect($sql);
}

?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_PROCESSING_ORDERS']; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<meta http-equiv="refresh" content="30"/>
        <?php include_once('global_files.php'); ?>

    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
			
            <!--PAGE CONTENT -->
            <div>
                <div class="inner">
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
									<table class="table table-striped table-bordered table-hover" style='border: 3px solid #cecece;'>
										<thead>
											<tr>
												<th class="text-left">Order No/ Username/Time</th>
												<th class="text-left">Order Details</th>
												<th class="text-left">Action</th>
											</tr>
										</thead>
										<tbody>
											<?php
											if (!empty($DBProcessingOrders)) {

												for ($i = 0; $i < count($DBProcessingOrders); $i++) {
													$db_order_data = $generalobj->getOrderPriceDetailsForWeb($DBProcessingOrders[$i]['iOrderId'],'','');
													$db_menu_item_list = $db_order_data['itemlist'];
													$color = '';
													if($DBProcessingOrders[$i]['print'] == '1'){
														$color = 'style="background-color: #5ff486;"';
													}
													?>

													<tr class="gradeA">
														<td class="text-left" <?= $color; ?>>
															<?= $DBProcessingOrders[$i]['vOrderNo']; ?><br /><?= $generalobjAdmin->clearName($DBProcessingOrders[$i]['riderName']); ?><br/>
															<?php echo date('g:i a',strtotime($DBProcessingOrders[$i]['tOrderRequestDate'])); ?> on <?php echo date('j F, Y',strtotime($DBProcessingOrders[$i]['tOrderRequestDate'])); ?>
														</td>

														<td class="text-left" <?= $color; ?>>
															<table>
																<? 
																foreach ($db_menu_item_list as $key => $val) { ?>
																	<tr>
																		<td><?= $val['MenuItem']; ?> X <?php echo $val['iQty']; ?><br/>
																			<?if($val['SubTitle'] != ''){?>
																			<small style="font-size: 10px;">(<?= $val['SubTitle'];?>)</small>
																			<? } ?>
																		</td>
																		<td align="right"><?=$val['fTotPrice']?></td>
																	</tr>
																<?php 
																}	 ?>
															</table>
														</td>
														<td <?= $color; ?>>
															<a class="btn btn-primary" href="print_order.php?iOrderId=<?= $DBProcessingOrders[$i]['iOrderId'] ?>" target="_blank">
																<b>Print</b>
															</a>
														</td>
													</tr>
													<div class="clear"></div>
													<?php 
												}
											} else {
												?>
												<tr class="gradeA">
													<td colspan="8"> No Records Found.</td>
												</tr>
											<?php 
											} ?>
										</tbody>
									</table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>

        <? include_once('footer.php');?>
        <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
        <link rel="stylesheet" href="css/select2/select2.min.css" />
        <script src="js/plugins/select2.min.js"></script>
        <script src="../assets/js/jquery-ui.min.js"></script>
        <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
        </body>
    <!-- END BODY-->
</html>
