<?php                     
  require_once('config.php');       
  require_once('Stripe.php');
   
  use Stripe\Account;
  use Stripe\Stripe;
  
  Stripe::setApiKey($stripe['secret_key']);
try{ 
  $customer = Account::create(array(
  "managed" => false,
  "country" => "US",
  "email" => ""
));
  echo "id".$id = $customer['id'];
  echo "<pre>";print_r($customer);exit;
  //$details = json_decode($customer);
  //$array = get_object_vars($details);
   
  //echo $array[status]; echo "<br><br>";
  //echo "<pre>"; print_r($array); exit;
}catch(Exception $e){
echo "<pre>"; print_r($e); exit;
}
  echo "<pre>";print_r($customer);exit;
  /*$details = json_decode($customer);
  $array = get_object_vars($details);
   
  echo $array[status]; echo "<br><br>";
  echo "<pre>"; print_r($array); exit; */

?>