<?
	include_once("common.php");
	//error_reporting(E_ALL);
	global $generalobj;
	$script="About Us";
	$meta = $generalobj->getStaticPage(1,$_SESSION['sess_lang']);
	 //echo "<pre>";print_r($_);exit;
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>
<?=$meta['meta_title'];?>
</title>
<meta name="keywords" value="<?=$meta['meta_keyword'];?>"/>
<meta name="description" value="<?=$meta['meta_desc'];?>"/>
<!-- Default Top Script and css -->
<?php include_once("top/top_script.php");?>
<!-- End: Default Top Script and css-->
</head>
<body>
<div id="main-uber-page">
  <!-- Left Menu -->
  <?php include_once("top/left_menu.php");?>
  <!-- End: Left Menu-->
  <!-- home page -->
  <!-- Top Menu -->
  <?php include_once("top/header_topbar.php");?>
  <!-- End: Top Menu-->
  <!-- contact page-->
  <div class="page-contant">
    <div class="page-contant-inner">
        
        
       
      <h2 class="header-page trip-detail">
        <?=$meta['page_title'];?>
      </h2>
      <!-- trips detail page -->
      <div class="static-page">
        <?=$meta['page_desc'];?>
      </div>  
      
      
    </div>
    
    
       <!--who we are section
      <div class="who_we_are">
          <div class="who_we_are_inner">
              <h1>Who we are</h1>

    <p>
        Hurley's is a food company striving to be the best place to go when you’re hungry, whether it’s getting food delivered or knowing the best places to visit. First we understand our customers and the local cuisine. We then provide convenient food delivery and a quick and  easy place to help you decide.
    </p>
        </div>
      </div>-->
       <!--who we are section end-->
       
       
       <!---image and content section
       
      <h1 class="delivering_good_to">Delivering good to...</h1>
      <div class="image_content_section">
         <div class="left_side">
             <h3>Customers</h3>
             <p>With your favorite restaurants at your fingertips, Hurley's satisfies your cravings and connects you with possibilities — more time and energy for yourself and those you love.</p>
             <a class="start_an_order" href="http://hurleys.anviam.in:8086/coming-soon.php">Start an order</a>
         </div> 
         <div class="right_side">
            <img src="assets/img/about/1.jpg" /> 
         </div>
      </div>
       
      <div class="image_content_section">
         <div class="left_side">
             <h3>Drivers</h3>
             <p>Delivering with Hurley's, you get flexibility and financial stability. Drive part-time or full-time and earn cash today.</p>
             <a href="http://hurleys.anviam.in:8086/become_a_delivery_partner.php">Drive now</a>
         </div> 
         <div class="right_side">
            <img src="assets/img/about/2.jpg" /> 
         </div>
      </div>
      
      <div class="image_content_section">
         <div class="left_side">
             <h3>Restaurants</h3>
             <p>Hurley's innovative merchant-focused solutions enhance your success by transforming your business. Open your doors to an entire city and see your revenue and market reach grow.</p>
             <a href="http://hurleys.anviam.in:8086/become_a_restaurant_partner.php">Sign up now</a>
         </div> 
         <div class="right_side">
            <img src="assets/img/about/3.jpg" /> 
         </div>
      </div>
     --->
     <!--image and content section end--->
       <script>
            $('.start_an_order').click(function() {
                window.location.href = 'http://hurleys.anviam.in:8086/coming-soon.php';
                return false;
            });
        </script>
    
    
  </div>
  <!-- home page end-->
  <!-- footer part -->
  <?php include_once('footer/footer_home.php');?>
  <!-- End:contact page-->
  <div style="clear:both;"></div>
</div>
<!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<!-- End: Footer Script -->
</body>
</html>