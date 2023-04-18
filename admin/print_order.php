<?php
	if(isset($_GET['iOrderId']) && $_GET['iOrderId'] != '' && $_GET['iOrderId'] != '0'){
		
	}else{
		header("Location: " . $_SERVER["HTTP_REFERER"]);
	}
	
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

	$query = "UPDATE orders SET print = '1'  WHERE iOrderId = '" . $iOrderId . "'";
	$obj->sql_query($query);


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html  moznomarginboxes mozdisallowselectionprint>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<title></title>
	<style type="text/css">
            @font-face {
	font-family: 'Conv_ArchivoNarrow-Regular';
	src: url('<?php //echo $tconfig["tsite_url"]; ?>/fonts/ArchivoNarrow-Regular.eot');
	src: local('☺'), url('<?php //echo $tconfig["tsite_url"]; ?>/fonts/ArchivoNarrow-Regular.woff') format('woff'), url('<?php //echo $tconfig["tsite_url"]; ?>/fonts/ArchivoNarrow-Regular.ttf') format('truetype'), url('<?php //echo $tconfig["tsite_url"]; ?>/fonts/ArchivoNarrow-Regular.svg') format('svg');
	font-weight: normal;
	font-style: normal;
}
            
            
                @page { margin-left: 0.08in; margin-right: 0.58in; margin-top: 0in; margin-bottom: 0.79in;  }
		p { margin-bottom: 0.04in; direction: ltr; line-height: 120%; text-align: left; widows: 2; orphans: 2 }
                a:link:after, a:visited:after {
                content: ""; 
        }
/*body,div,table,thead,tbody,tfoot,tr,th,td,p { font-family:"Calibri"; font-size:xx-small; }*/
body { width: 3in !important; font-family: 'Conv_ArchivoNarrow-Regular'; font-weight: bolder;}
@media print
{    
    .no-print, .no-print *
    {
        display: none !important;
    }
}
	</style>
        <script>
       window.print();
        
        </script>
</head>
<body lang="en-US" dir="ltr"  onload="OpenInNewtab();">
    
    <p style="text-align:center; font-size:22px; text-transform: uppercase;">Hurley's</p>
	<p align="center" style="margin-bottom: 0in; line-height: 0.2; text-align: center; font-size: medium;">Hurley’s 1053 Crewe Rd,</p>
	<p align="center" style="margin-bottom: 0in; line-height: 0.2; text-align: center; font-size: medium; ">Grand Harbour, Cayman Islands</p>
	<p align="center" style="margin-bottom: 0.08in; line-height: 0.2; text-align: center; font-size: medium; padding-bottom: 0.1in;"><?= $db_order_data['CompanyName']; ?></p>
		<? // $db_order_data['vRestuarantLocation']; ?> 
	<p align="left" style="margin-bottom: 0in; line-height: 120%; text-align: center; font-size:medium;  padding-bottom: 0.1in;">
	<!--<span style="font-size: 16px;">Hurleys</span><br/>
	<?php //echo @date('h:i A',@strtotime($db_order_data['OrderRequestDatenew']));?><br/>-->
	<strong>ORDER DETAILS</strong></p>
	<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: medium; font-weight: bold;"><?php echo $langage_lbl_admin['LBL_ORDER_NO_TXT'];?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp; <?php echo $db_order_data['vOrderNo']; ?></p>
	<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: medium; font-weight: bold;"><span><?php echo $langage_lbl_admin['LBL_ORDER_STATUS_TXT'];?> &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp; <?=$db_order_data['vStatus'];?></span></p>
	<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: medium; font-weight: bold;"><span><?php echo $langage_lbl_admin['LBL_PAYMENT_TYPE_TXT'];?> &nbsp;&nbsp;:&nbsp; <?= $db_order_data['ePaymentOption'];?></span></p>
	<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: medium; font-weight: bold;"><span><?php echo 'Customer';?> &nbsp;&nbsp;:&nbsp; <?= $db_order_data['UserName'];?></span></p>
	<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: medium; font-weight: bold;"><span><?php echo $langage_lbl_admin['LBL_RIDER_Phone_Number'];?> &nbsp;&nbsp;:&nbsp; <?= $db_order_data['userPhoneNumber'];?></span></p>
	<p align="left" style="margin-bottom: 0in; line-height: 1; font-size: medium; font-weight: bold;"><span><?php echo $langage_lbl_admin['LBL_PROFILE_ADDRESS'];?> &nbsp;&nbsp;:&nbsp; <?= $db_order_data['DeliveryAddress'];?></span></p>
	<?php
	if($db_order_data['dScdeliveryDate'] != ''){
		$pickupdate = @date('d M Y',@strtotime($db_order_data['dScdeliveryDate']));
	}else{
		$pickupdate = date('d M Y',@strtotime($db_order_data['DeliveryDate']));
	}
	if($db_order_data['dScdeliveryTime'] != ''){
		$pickuptime = $db_order_data['dScdeliveryTime'];
	}else{
		$pickuptime = @date('h:i A',@strtotime($db_order_data['DeliveryDate']));
	}
	?>
	
	<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: medium; font-weight: bold;"><span> Pickup Date &nbsp;&nbsp;:&nbsp; <strong><?=$pickupdate;?></strong></span></p>
	<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: medium; font-weight: bold;"><span> Pickup Time &nbsp;&nbsp;:&nbsp; <strong><?=$pickuptime;?></strong></span></p>
	<?php
	if($db_order_data['vInstruction'] != ''){
	?>
		<p align="left" style="margin-bottom: 0in; line-height: 1.2; font-size: medium; font-weight: bold;"><span> Special Instructions &nbsp;&nbsp;:&nbsp; <strong><?=$db_order_data['vInstruction'];?></strong></span></p>
	<?php
	}
	?>
	<p align="left" style="margin-bottom: 0in; border-top:1px solid #888; line-height: 120%; font-size: medium; font-weight: bold; padding-top: 0.02in; padding-bottom: 0.02in;"><span>No. Of Items : </span> <?= $db_order_data['TotalItems']; ?> <span style="float: right;">Time: <?=@date('h:i A',@strtotime($db_order_data['DeliveryDate']));?></span></p>
	<table width="100%" cellpadding="4" cellspacing="0" style="border: none; font-size: medium;">
		
			
			
			<?php $db_menu_item_list = $db_order_data['itemlist']; ?>
			<? foreach ($db_menu_item_list as $key => $val) { ?>
				<tr valign="top">
					<td>
						<?= $val['MenuItem']; ?> X <?php echo $val['iQty']; ?><br/>
						<?if($val['SubTitle'] != ''){?>
						<small style="font-size: 16px;">(<?= $val['SubTitle'];?>)</small>
						<? } ?>
					</td>
					<td><?=$val['fTotPrice']?></td>
				</tr>
			<?php }	 ?>
	</table>
	<? 
	foreach ($db_order_data['History_Arr'] as $key => $value) {
		if($key == $langage_lbl['LBL_BILL_SUB_TOTAL']){ ?>
			<p align="left" style="border-top: 1px solid #888; margin-bottom: 0.08in; padding-top: 0.1in; line-height: 100%; font-size: medium; padding-bottom: 0.2in;"><?= $key; ?>
			<span style="float:right;"><?= $value; ?></span></p>
		<?php 
		} else {
		}?>
	<?php 
	}	?>
	<? foreach ($db_order_data['History_Arr_first'] as $key => $value) { 
		if($key == $langage_lbl['LBL_TOTAL_BILL_AMOUNT_TXT']){  ?>
		<tr>
			<p align="left" style="border-top: 1px solid #888; margin-bottom: 0.08in; padding-top: 0.1in; line-height: 100%; font-size: medium; padding-bottom: 0.2in;"><?= $key; ?>
			<span style="float:right;"><?=$value;?></span></p>
		</tr>
		<?php }else{
			
		}
	 } ?>
	<script type="text/javascript">
		function kot()
		{
			var win = window.open('<?php // echo $this->base; ?>/vendors/printkot/<?php // echo $orderid; ?>', '_blank');
			win.focus();
	}
	</script>
</body>
</html>
