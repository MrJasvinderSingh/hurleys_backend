<?php 
require_once 'Twilio/autoload.php'; 
use Twilio\Rest\Client; 
if(isset($_POST['phone_submit'])) {
// Update the path below to your autoload.php, 
// see https://getcomposer.org/doc/01-basic-usage.md 

 
$sid    = "ACf5b5026e5badeba49a06e69b62a26623"; 
$token  = "80c057992d097731f1c9529957fc5439"; 
$twilio = new Client($sid, $token); 
$to = $_POST['phone_no'];
$str_no = str_replace("-", "", $to) ;
$to_no = "+1".$str_no;
$message = $twilio->messages 
                  ->create($to_no, // to 
                           array( 
                               "from" => "+19382229343",       
                               "body" =>  "Android user: #  
Apple user:
# 
"
                           ) 
                  ); 
 
// print($message->sid);

if ($message->sid) {
	echo "<script>alert('App Download links were texted to you.');</script>";
} 
else {
	echo "code not working";
}

}
?>

<?php
include_once("common.php");
$script="Home";
$meta_arr = $generalobj->getsettingSeo(7);
$meta1 = $generalobj->getStaticPage(23,$_SESSION['sess_lang']);
$meta2 = $generalobj->getStaticPage(24,$_SESSION['sess_lang']);
$meta3 = $generalobj->getStaticPage(25,$_SESSION['sess_lang']);
$meta4 = $generalobj->getStaticPage(26,$_SESSION['sess_lang']);
$homepage_banner = $generalobj->getStaticPage(19,$_SESSION['sess_lang']);
$meta5 = $generalobj->getStaticPage(27,$_SESSION['sess_lang']);
$meta6 = $generalobj->getStaticPage(28,$_SESSION['sess_lang']);
$image3 = $generalobj->getStaticPage(29,$_SESSION['sess_lang']);
$image4 = $generalobj->getStaticPage(30,$_SESSION['sess_lang']);
$meta7 = $generalobj->getStaticPage(32,$_SESSION['sess_lang']);

$data = $generalobj->gethomeData($_SESSION['sess_lang']);
// echo"<pre>";print_r($_SESSION);die;

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <!--<title><?=$SITE_NAME?></title>-->
	<title><?php echo $meta_arr['meta_title'];?></title>
	<meta name="keywords" value="<?=$meta_arr['meta_keyword'];?>"/>
	<meta name="description" value="<?=$meta_arr['meta_desc'];?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <!-- home page -->
    <div id="main-uber-page">
        <!-- Left Menu --> 
    <?php //include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- First Section -->
		<?php //include_once("top/header.php");?>
        <!-- End: First Section -->
        <?php //include_once("top/home.php");?>
    <!-- home page end-->
    <!-- footer part -->
    <?php //include_once('footer/footer_home.php');?>
    
    <div style="clear:both;"></div>
     </div>
    <!-- footer part end -->
    
    
            
          
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<!-- End: Footer Script -->


</body>
</html>