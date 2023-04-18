<?php 
include_once("common.php");

foreach ($_REQUEST as $key => $value) 
{
	$value = urldecode(stripslashes($value));
	$req .= "&$key=$value";
	$req .= '<br><br>';
}	

$to = "loyaltyhurleys@gmail.com";
$NOREPLY_EMAIL = "loyaltyhurleys@gmail.com";
$EMAIL_FROM_NAME = "Hurleys App";
$subject = "Reply from pubnub callback  - ".date('Y-m-d H:i:s');	
$replyto = ""; 

$message = "Pubnub callback";
$message .= "<br />";
$message .= $req;

$header = "From:loyaltyhurleys@gmail.com \r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-type: text/html\r\n";

$emailsend = mail ($to,$subject,$message,$header);
//$emailsend = $generalobj->send_email_smtp($to,$NOREPLY_EMAIL,$EMAIL_FROM_NAME,$subject,$message,$replyto);

if($emailsend){
	//echo 'send';
}else{
	//echo 'not send';

}



?>