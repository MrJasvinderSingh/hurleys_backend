<?php
   require_once('config.php');       
  require_once('Stripe.php');
  
  use Stripe\Customer;
  use Stripe\Stripe;
  $token = "";
  //$email  = $_POST['stripeEmail'];
  $email  = "";
  
  Stripe::setApiKey($stripe['secret_key']);
try{ 
  $customer = Customer::create(array(
              "description" => $email,
              "source" => $token // obtained with Stripe.js
            ));
            
  echo "id".$id = $customer['id'];
  echo "<pre>";print_r($customer);exit;            
}catch(Exception $e){
echo "<pre>"; print_r($e); exit;
}
  
  echo "id".$id = $customer['id'];
  echo "<pre>";print_r($customer);exit;
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