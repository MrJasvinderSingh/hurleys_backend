<?php
	include_once('../common.php');
	include_once("../generalFunctions.php");

	if (!isset($generalobjAdmin)) {
		require_once(TPATH_CLASS . "class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}

	include_once('../send_invoice_receipt.php');
	$generalobjAdmin->check_member_login();

	$iOrderId = isset($_REQUEST['iOrderId'])?$_REQUEST['iOrderId']:'';
	$script="All Orders";
	$tbl_name = 'orders';

	$db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId,'','');

	$sql = "SELECT iTripId,vImage FROM trips WHERE iOrderId = '".$iOrderId."'";
	$TripData = $obj->MySQLSelect($sql);
	
	$sqlOrder = "SELECT vImageSign FROM $tbl_name WHERE iOrderId = '".$iOrderId."'";
	$OrdersData = $obj->MySQLSelect($sqlOrder);

	$getratings = $generalobj->getrating($iOrderId);
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
	
	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title>Admin | Invoice</title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<meta content="" name="keywords" />
		<meta content="" name="description" />
		<meta content="" name="author" />
		<? include_once('global_files.php');?>		
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>"></script>
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
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
				<div class="inner" id="page_height" style="">
					<div class="row">
						<div class="col-lg-12">
							<h2>Invoice</h2>
							 <a href='print_order.php?iOrderId=<?= $iOrderId ?>' class="add-btn" target="_blank">Print</a>
                            <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();">
                            <div style="clear:both;"></div>
						</div>
					</div>
					<hr />
					<?php if ($_REQUEST['success'] ==1) { ?>
						<div class="alert alert-success paddiing-10">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							Email send successfully.
						</div>
					<?php }?>
					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										<b>Your <?php echo @date('h:i A',@strtotime($db_order_data['DeliveryDate']));?> on <?=@date('d M Y',@strtotime($db_order_data['DeliveryDate']));?>
									</div>
									<div class="panel-body rider-invoice-new">
										<div class="row">
											<div class="col-sm-6 rider-invoice-new-left">							<div id="map-canvas" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div>
												<span class="location-from"><i class="icon-map-marker"></i>
												<b>
													<?=@date('h:i A',@strtotime($db_order_data['OrderRequestDatenew']));?>
													<p><?=$db_order_data['CompanyName']?>
													<? if(!empty($getratings['CompanyRate'])) { ?>
														(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['CompanyRate']?>) 
													<? } ?>
													</p>
	            								<p><?=$db_order_data['vRestuarantLocation'];?></p>
		            							</b>
			            						</span>
												<span class="location-to"><i class="icon-map-marker"></i>
													<b><?=@date('h:i A',@strtotime($db_order_data['DeliveryDate']));?>
													<p>
														<?=$db_order_data['UserName'];?> 
														<? if(!empty($getratings['UserRate'])) { ?>
															(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate']?>) 
														<? } ?>
													</p>
													<p><?=$db_order_data['DeliveryAddress'];?></p>
												</b>
												</span>

                                                <div class="rider-invoice-bottom">
													<div class="col-sm-4">
														<?php echo $langage_lbl_admin['LBL_ORDER_NO_TXT'];?> <br />
														<b>	
														<?php echo $db_order_data['vOrderNo']; ?>
						                    			</b><br/>
													</div>									
													<div class="col-sm-4">
														<?php echo $langage_lbl_admin['LBL_ORDER_STATUS_TXT'];?><br /> 
														<b><?=$db_order_data['vStatus'];?> </b> <br/>
													</div>														
													<div class="col-sm-4">
														<?php echo $langage_lbl_admin['LBL_PAYMENT_TYPE_TXT'];?><br />
														<b><?= $db_order_data['ePaymentOption'];?></b>
													</div>

													<br><br><br>
													<?php if($db_order_data['DriverName'] != '') { ?>
														<div class="col-sm-4">
															Driver Name<br />
															<b>	<?php echo $db_order_data['DriverName']; ?>
															<? if(!empty($getratings['DriverRate'])) { ?>
																(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['DriverRate']?>) 
															<? } ?>
															</b><br/>
														</div>	
													<?php } ?>
													<?php if($db_order_data['DriverVehicle'] != '') { ?>	
													<div class="col-sm-4">
														Driver Vehicle<br /> 
														<b><?=$db_order_data['DriverVehicle'];?> </b> <br/>
													</div>
													<?php } ?>
													<?php if($db_order_data['UserName'] != '') { ?>					
													<div class="col-sm-4">
														Username<br />
														<b><?= $db_order_data['UserName'];?>
															<? if(!empty($getratings['UserRate'])) { ?>
																(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate']?>) 
															<? } ?>
														</b>
													</div>
													<?php } ?>
												</div>												
											</div>
											
											<div class="col-sm-6 rider-invoice-new-right">
												<h4 style="text-align:center;">	<?php echo $langage_lbl_admin['LBL_ORDER_DETAIL_TXT'];?> </h4><hr/>	
												
							      				<div class="fare-breakdown">
							            			<div class="fare-breakdown-inner">
							            			<? $db_menu_item_list = $db_order_data['itemlist']; ?>
							              				<h5><?php echo $langage_lbl['LBL_TOTAL_ITEM_TXT'];?> : <b><?= $db_order_data['TotalItems']; ?></b></h5>
														<?php if(!empty($db_menu_item_list)){ ?>
							              				<table style="width:100%" cellpadding="5" cellspacing="0" border="0">
														<tbody>
															<? foreach ($db_menu_item_list as $key => $val) { ?>
																<tr>
																	<td><?= $val['MenuItem']; ?> X <?php echo $val['iQty']; ?><br/>
																		<?if($val['SubTitle'] != ''){?>
																		<small style="font-size: 10px;">(<?= $val['SubTitle'];?>)</small>
																		<? } ?>
																	</td>
																	<td align="right"><?=$val['fTotPrice']?></td>
																</tr>
															<?php }	 ?>
															<tr>
																<td colspan="2"><hr style="margin-bottom:0px;border-style: dotted;"/></td>
															</tr>
							              				</tbody>
							              				</table>
							              				
														<?php } ?>
														<table style="width:100%" cellpadding="5" cellspacing="0" border="0">
														<tbody>
							                   			<? foreach ($db_order_data['History_Arr'] as $key => $value) {
							                   				if($key == $langage_lbl['LBL_BILL_SUB_TOTAL']){ ?>
							                   					<tr>
																	<td style="font-weight: bold;"><?= $key; ?></td>
																	<td align="right"><?=$value;?></td>
																</tr>
							                   				<?php } else { 
															/*if($key != 'Delivery Charges'){
																?>
								                   				<tr>
																	<td><?= $key; ?></td>
																	<td align="right"><?=$value;?></td>
																</tr>
							                   				<?php } */
															}?>
							                   			<?php }	?>
							                   				<tr>
																<td colspan="2"><hr style="margin-bottom:0px;border-style: dotted;"/></td>
															</tr>
														</tbody>
							                   			</table>
							                   			<table style="width:100%" cellpadding="5" cellspacing="0" border="0">
							                   			<tbody>
							                   			<? foreach ($db_order_data['History_Arr_first'] as $key => $value) { 
							                   				if($key == $langage_lbl['LBL_TOTAL_BILL_AMOUNT_TXT']){  ?>
							                   				<tr>
																<td style="font-weight: bold;"><?= $key; ?></td>
																<td align="right"><?=$value;?></td>
															</tr>
							                   				<?php }else{/*?>
							                   				<tr>
																<td><?= $key; ?></td>
																<td align="right"><?=$value;?></td>
															</tr>
							                   			<?php */}
							                   			 } ?>
							                   			</tbody>
							                   			</table>
							            			</div>
							          			</div>

							          			 <br/><br/><br/>
                                                    <div class="invoice-right-bottom-img">
                                                         <?php
                                                if ($TripData[0]['vImage'] != '') {
                                                    $img_path = $tconfig["tsite_upload_order_images"];
                                                    ?>
                                                        
                                                        <div class="col-sm-6 text-left">
                                                            <b><a href="<?= $img_path . $TripData[0]['vImage']; ?>" target="_blank" ><img src = "<?= $img_path . $TripData[0]['vImage']; ?>" style="width:200px; visibility: hidden;" alt ="Order Images"/></a></b>
                                                        </div>
                                                        <?php } ?>
                                                        <?php if ($OrdersData[0]['vImageSign'] != '') {
                                                    $img_path = $tconfig["tsite_upload_order_images_path_sign_images"];
                                                    ?>
                                                   
                                                    
                                                        <div class="col-sm-6 text-right">
														 
                                                            <b style="height: 150px;"><a href="<?= $img_path . $OrdersData[0]['vImageSign']; ?>" target="_blank" ><img src = "<?= $img_path . $OrdersData[0]['vImageSign']; ?>" style="width:200px; height: 150px; " alt ="Customer Signature" title="Customer Signature" /></a></b>
															<br/>
															<p style="text-align:center; width:100%; display:inline-block; border-top: 1px dotted #ccc; font-size:16px;">Signature</p>
                                                        </div>
                                                    
<?php } ?>
                                                        
                                                    </div>

                                              
                                                <br/>
												<?php if($db_order_data['iStatusCode'] == '8'){ ?>
												<div class="panel panel-warning">
													<div class="panel-heading">
														<p><?= $langage_lbl["LBL_ORDER_CANCEL_WEB_TEXT"];?></p>
														<? if($db_order_data['eCancelledBy'] != '') { ?>
															<p>Cancelled By : <?php echo $db_order_data['eCancelledBy'];?></p>
														<? } if($db_order_data['vCancelReason'] != '') {?>
														<p>Cancellation Reason : <?php echo $db_order_data['vCancelReason'];?></p>
														<? } ?>
														<p><?= $langage_lbl["LBL_CANCELLATION_CHARGE_WEB"]?> For User : <?php echo $generalobj->trip_currency($db_order_data['fCancellationCharge']);?>
															<? if($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes'){ ?>
		                                                        ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"]?># : <?php echo $db_order_data['vOrderAdjusmentId'] ?>)
		                                                    <? } else if($db_order_data['ePaymentOption'] == 'Cash'){ ?>
		                                                        ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"]?> )
		                                                    <? } else if($db_order_data['ePaymentOption'] == 'Card'){ ?>
		                                                        ( <?= $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"]?> )
		                                                    <? } ?>
														</p>
														<p><?php echo $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"]?> To Restarant : <?php echo $generalobj->trip_currency($db_order_data['fRestaurantPaidAmount']);?></p>
														<p><?php echo $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"]?> To Driver: <?php echo $generalobj->trip_currency($db_order_data['fDriverPaidAmount']);?></p>
													</div>
												</div>
												<?php } else if($db_order_data['iStatusCode'] == '7') { ?>
													<div class="panel panel-warning">
														<div class="panel-heading">
															<p><?= $langage_lbl["LBL_ORDER_REFUND_WEB_TEXT"];?></p>
															<? if($db_order_data['eCancelledBy'] != '') { ?>
															<p>Cancelled By : <?php echo $db_order_data['eCancelledBy'];?></p>
															<? } if($db_order_data['vCancelReason'] != '') {?>
															<p>Cancellation Reason : <?php echo $db_order_data['vCancelReason'];?></p>
															<? } ?>
															<p><?= $langage_lbl["LBL_CANCELLATION_CHARGE_WEB"]?> : <?php echo $generalobj->trip_currency($db_order_data['fCancellationCharge']);?>
																<? if($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes'){ ?>
			                                                        ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"]?># : <?php echo $db_order_data['vOrderAdjusmentId'] ?>)
			                                                    <? } else if($db_order_data['ePaymentOption'] == 'Cash'){ ?>
			                                                        ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"]?> )
			                                                    <? } else if($db_order_data['ePaymentOption'] == 'Card'){ ?>
			                                                        ( <?= $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"]?> )
			                                                    <? } ?>
															</p>
															<p>Refunded Amount To User : <?php echo $generalobj->trip_currency($db_order_data['fRefundAmount']);?>
															<p><?php echo $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"]?> To Restaurant: <?php echo $generalobj->trip_currency($db_order_data['fRestaurantPaidAmount']);?></p>
															<p><?php echo $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"]?> To Driver: <?php echo $generalobj->trip_currency($db_order_data['fDriverPaidAmount']);?></p>
													</div>
													</div>
												<?php } ?>
											</div>
											
											<div class="clear"></div>
											<!-- <div class="row invoice-email-but">
												<span>
													<a href="../send_invoice_receipt.php?action_from=mail&iTripId=<?= $db_order_data['iTripId']?>"><button class="btn btn-primary ">E-mail</button></a>
												</span>
											</div> -->
										</div>
									</div>
								</div>
							</div>
							
						</div>
                        <div class="clear"></div>
					</div>
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		
		<!--END MAIN WRAPPER -->
		
		<? include_once('footer.php');?>
		<script src="../assets/js/gmap3.js"></script>
		<script>
			h = window.innerHeight;
			$("#page_height").css('min-height', Math.round( h - 99)+'px');
			
			function from_to(){
				
				$("#map-canvas").gmap3({
					getroute:{
						options:{
             				origin:'<?=$db_order_data['vRestuarantLocationLat'].",".$db_order_data['vRestuarantLocationLong']?>',
							destination:'<?=$db_order_data['vLatitude'].",".$db_order_data['vLongitude']?>',
							travelMode: google.maps.DirectionsTravelMode.DRIVING
						},
						callback: function(results){
							if (!results) return;
							$(this).gmap3({
								map:{
									options:{
										zoom: 13,
										center: [-33.879, 151.235]
									}
								},
								directionsrenderer:{
									options:{
										directions:results
									}
								}
							});
						}
					}
				});
			}
			
				from_to();
			
		</script>
	</body>
	<!-- END BODY-->
</html>
