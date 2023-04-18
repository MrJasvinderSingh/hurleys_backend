<?php
	require_once('config.php');       
	require_once('Stripe.php');

	use Stripe\Transfer;
	use Stripe\Charge;
	use Stripe\Stripe;
	$amount = 3000;
	$currency = "usd"; 
	$vStripeCusId = ""; 
	//$email  = $_POST['stripeEmail'];
	$email  = "";
	
	Stripe::setApiKey($stripe['secret_key']);
	try{ 
		
		$charge = Charge::create(array(
		"amount" => $amount,
		"currency" => $currency,
		"customer" => $vStripeCusId,
		"description" => "Charge for "
		));         
		
		//echo "status - ".$charge['status'];             
		//echo "id".$id = $charge['id'];
		//echo "<pre>";print_r($charge);exit;
		
		// Transfer to connect account - https://stripe.com/docs/connect/charges-transfers
		// Create a Transfer to the connected account (later):
		$transfer = Transfer::create(array(
		"amount" => 1500,
		"currency" => $currency,                     
		"destination" => "",
		"description" => "Transfer for "             
		));
		// Transfer to connect account - https://stripe.com/docs/connect/charges-transfers
		echo "id".$id = $transfer['id'];
		echo "<pre>";print_r($transfer);exit;            
		}catch(Exception $e){
		echo "<pre>"; print_r($e); exit;
	}
	
	echo "id".$id = $charge['id'];
	echo "<pre>";print_r($charge);exit;
	/*$details = json_decode($customer);
		$array = get_object_vars($details);
		
		echo $array[status]; echo "<br><br>";
		echo "<pre>"; print_r($array); exit;
	*/
	/* $charge = Stripe_Charge::create(array(
		'customer' => $customer->id,
		'amount'   => 5000,
		'currency' => 'usd',
		'description' => 'Widget, Qty 1'
		));
		$details = json_decode($charge);
		$array = get_object_vars($details);
		
		echo $array[status]; echo "<br><br>";
		echo "<pre>"; print_r($array); exit;
		
	echo '<h1>Successfully charged $50.00!</h1>';   */
?>