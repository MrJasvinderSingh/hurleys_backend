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
    <!--<a class="no-print" style=" width: 100%; text-align: right; display: block; color: #000; text-decoration: none;" onclick="kot();" href="javascript:void(0);"><span style=" background-color:#D9D9D9; padding: 15px 20px;">Print Kot</span></a>-->
<!--    <p style="text-align:center;"><img src="<?php //// echo $this->base; ?>/assets/images/.png" height="80px" width="auto"/></p>-->
    <p style="text-align:center; font-size:20px; text-transform: uppercase;">Hurley's</p>
<p align="center" style="margin-bottom: 0in; line-height: 0.2; text-align: center; font-size: small;">Hurley’s 1053 Crewe Rd,</p>
<p align="center" style="margin-bottom: 0in; line-height: 0.2; text-align: center; font-size: small; ">Grand Harbour, Cayman Islands</p>
<p align="center" style="margin-bottom: 0.08in; line-height: 0.2; text-align: center; font-size: small; padding-bottom: 0.1in;"><?= $db_order_data['CompanyName']; ?></p>
    <? // $db_order_data['vRestuarantLocation']; ?> 
<p align="left" style="margin-bottom: 0in; line-height: 120%; text-align: center; font-size:small;  padding-bottom: 0.1in;">
<!--<span style="font-size: 16px;">Hurleys</span><br/>
<?php //echo @date('h:i A',@strtotime($db_order_data['OrderRequestDatenew']));?><br/>-->
<strong>ORDER DETAILS</strong></p>
<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: small; font-weight: bold;"><?php echo $langage_lbl_admin['LBL_ORDER_NO_TXT'];?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp; <?php echo $db_order_data['vOrderNo']; ?></p>
<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: small; font-weight: bold;"><span><?php echo $langage_lbl_admin['LBL_ORDER_STATUS_TXT'];?> &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp; <?=$db_order_data['vStatus'];?></span></p>
<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: small; font-weight: bold;"><span><?php echo $langage_lbl_admin['LBL_PAYMENT_TYPE_TXT'];?> &nbsp;&nbsp;:&nbsp; <?= $db_order_data['ePaymentOption'];?></span></p>
<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: small; font-weight: bold;"><span><?php echo 'Customer';?> &nbsp;&nbsp;:&nbsp; <?= $db_order_data['UserName'];?></span></p>
<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: small; font-weight: bold;"><span><?php echo $langage_lbl_admin['LBL_RIDER_Phone_Number'];?> &nbsp;&nbsp;:&nbsp; <?= $db_order_data['userPhoneNumber'];?></span></p>
<p align="left" style="margin-bottom: 0in; line-height: 0.3; font-size: small; font-weight: bold;"><span><?php echo $langage_lbl_admin['LBL_PROFILE_ADDRESS'];?> &nbsp;&nbsp;:&nbsp; <?= $db_order_data['DeliveryAddress'];?></span></p>

<p align="left" style="margin-bottom: 0in; border-top:1px solid #888; line-height: 120%; font-size: small; font-weight: bold; padding-top: 0.02in; padding-bottom: 0.02in;"><span>No. Of Items : </span> <?= $db_order_data['TotalItems']; ?> <span style="float: right;">Time: <?=@date('h:i A',@strtotime($db_order_data['DeliveryDate']));?></span></p>
<table width="100%" cellpadding="4" cellspacing="0" style="border: none; font-size: small;">
	
        
        
        <?php $db_menu_item_list = $db_order_data['itemlist']; ?>
		<? foreach ($db_menu_item_list as $key => $val) { ?>
			<tr valign="top">
				<td>
					<?= $val['MenuItem']; ?> X <?php echo $val['iQty']; ?><br/>
					<?if($val['SubTitle'] != ''){?>
					<small style="font-size: 10px;">(<?= $val['SubTitle'];?>)</small>
					<? } ?>
				</td>
				<td><?=$val['fTotPrice']?></td>
			</tr>
		<?php }	 ?>
        <?php // endforeach; ?>

	<!--	<tr valign="top">
			<td width="75%" style="border-top: none; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: none; padding-top: 0in; padding-bottom: 0.04in; padding-left: 0.04in; padding-right: 0in">
				<p align="center">Total</p>
			</td>
			<td width="25%" style="border-top: none; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000; padding-top: 0in; padding-bottom: 0.04in; padding-left: 0.04in; padding-right: 0.04in">
				<p align="center"><?php // //echo $totalamount; ?></p>
			</td>
		</tr>-->
</table>
<? 
foreach ($db_order_data['History_Arr'] as $key => $value) {
	if($key == $langage_lbl['LBL_BILL_SUB_TOTAL']){ ?>
		<p align="left" style="border-top: 1px solid #888; margin-bottom: 0.08in; padding-top: 0.1in; line-height: 100%; font-size: small; padding-bottom: 0.2in;"><?= $key; ?>
		<span style="float:right;"><?= $value; ?></span></p>
	<?php 
	} else {
		/*if($key != 'Delivery Charges'){
		?>
		<tr>
			<td><?= $key; ?></td>
			<td align="right"><?=$value;?></td>
		</tr>
		<?php } */
	}?>
<?php 
}	?>
<? foreach ($db_order_data['History_Arr_first'] as $key => $value) { 
	if($key == $langage_lbl['LBL_TOTAL_BILL_AMOUNT_TXT']){  ?>
	<tr>
		<p align="left" style="border-top: 1px solid #888; margin-bottom: 0.08in; padding-top: 0.1in; line-height: 100%; font-size: small; padding-bottom: 0.2in;"><?= $key; ?>
		<span style="float:right;"><?=$value;?></span></p>
	</tr>
	<?php }else{
		
	}
 } ?>
<!--<p align="left" style="border-top: 1px solid #888; margin-bottom: 0.08in; padding-top: 0.1in; line-height: 100%; font-size: small; padding-bottom: 0.2in;"><span>Net Qty:</span> <?php // echo $totalQuantity; ?> <span style="float:right;">Bill Total :  Rs. <?php // echo $result['amount'];  ?></span></p>
<p align="left" style="margin-bottom: 0.08in; line-height: 0.4; font-size: small; padding-bottom: 0.02in; text-align: right;"><span>&nbsp;</span>  <span style="float: none;">Vat@<?php // echo Tax; ?>% : Rs. <?php // echo $result['tax']; ?></span></p>
<p align="left" style="margin-bottom: 0.08in; line-height: 0.4; font-size: small; padding-bottom: 0.02in; text-align: right;"><span>&nbsp;</span>  <span style="float: none;">S.Tax With SBC & KKC @<?php // echo Surcharge; ?>% : Rs. <?php // echo $result['surcharge']; ?></span></p>
<p align="left" style="margin-bottom: 0.08in; line-height: 0.4; font-size: small; padding-bottom: 0.02in; text-align: right;"><span>&nbsp;</span>  <span style="float: none;">Discount. : <?php // echo $result['discamount']; ?></span></p>
<p align="left" style="margin-bottom: 0.08in; line-height: 0.4; font-size: small; padding-bottom: 0.2in; text-align: right;"><span>&nbsp;</span>  <span style="float: none;">R. Off. : <?php // echo round(($result['amount'] +$result['tax'] + $result['surcharge'] - $result['discamount']  - $result['total']), 2); ?></span></p>
<p align="left" style="border-top: 1px solid #888; border-bottom: 1px solid #888; margin-bottom: 0.08in; line-height: 0.9; font-size: small; padding-bottom: 0.1in; padding-top: 0.1in; text-align:center;"><span>Amount Payable </span>  <span style="float: right;">Rs.  <?php // echo round($result['total']); ?></span></p>
<p align="left" style=" padding-top: 0.04in; margin-bottom: 0.04in; line-height: 100%; text-align: right; font-size: small;">E&amp;OE</p>
<p align="left" style=" padding-top: 0.04in; margin-bottom: 0.04in; line-height: 100%; text-align: left; font-size: small;">Payment Mode &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : <?php // echo $paytype; ?></p>
<p align="left" style="margin-bottom: 0.04in; line-height: 100%; text-align: left; font-size: small;">Card No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php // echo $token; ?></p>
<p align="left" style="margin-bottom: 0.04in; line-height: 100%; text-align: left; font-size: small;">Card Balance&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rs. <?php // echo ($balance + round($result['total']) -30); ?></p>
<p align="left" style="margin-bottom: 0.08in; line-height: 100%; text-align: left; font-size: small;">Bill Amount&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rs. <?php // echo round($result['total']); ?></p>
<p align="left" style="margin-bottom: 0.08in; line-height: 100%; text-align: left; font-size: small;">Current Balance&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rs. <?php // echo ($balance - 30); ?></p>
<p align="left" style="margin-bottom: 0.08in; line-height: 100%; text-align: left; font-size: medium;">
<p align="center" style="margin-top:0.4in; margin-bottom: 0in; line-height: 120%; text-align: center; font-size: small; width: 100%; border-top: 1px solid #888; border-bottom: 1px solid #888; padding-bottom: 0.2in;"><br/>Order No: <?php // echo $counter; ?></p>
<p align="center" style="margin-bottom: 0in; line-height: 150%; text-align: center; font-size: small; width: 100%;"><br/><br/></p>
-->
<script type="text/javascript">
    function kot()
    {
        var win = window.open('<?php // echo $this->base; ?>/vendors/printkot/<?php // echo $orderid; ?>', '_blank');
        win.focus();
}
</script>
</body>
</html>