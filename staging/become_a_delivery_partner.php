<?php if(isset($_POST['submit'])) {  
    header('Location: sign-up.php');  
}
?> 
<?
	include_once("common.php");
	//error_reporting(E_ALL);
	global $generalobj;
	$script="Become A Delivery Partner";
	$meta = $generalobj->getStaticPage(1,$_SESSION['sess_lang']);
	 //echo "<pre>";print_r($_);exit;
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>
<? // =$meta['meta_title'];?> 
Become A Delivery Partner
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
        
        
    <?php /*
      <h2 class="header-page trip-detail">
        <?=$meta['page_title'];?>
      </h2>
      <!-- trips detail page -->
      <div class="static-page">
        <?=$meta['page_desc'];?>
      </div> */ ?>
      
      
    </div>
    
    
     <!--Become A Delivery Partner--->
     <div class="delivery_partner_banner_section">
        <div class="container">
            <div class="image_sec">
                <img src="assets/img/delivery_partner/bag.png" />
            </div>
            <div class="left_sec">
                <div class="content">
                    <h1>Bring home the bacon.</h1>
                    <p>Start delivering today and make great money on your own schedule.</p>
                </div>
            </div>
            <div class="right_sec">
                <h1>Get your first check this week</h1>
                <form method="post">
                  <div class="email">
                      <input type="email" name="email" value=""  placeholder="Email" required>
                  </div>
                  
                  <div class="phone_and_zip">
                      <input type="text" name="phone_number" value=""  placeholder="Phone Number" required>
                  </div>
                  
                  <div class="phone_and_zip">
                      <input type="text" name="zip_code" value=""  placeholder="Your ZIP / Postal Code" required>
                  </div>
                  
                  <div class="radio_btn">
                      <input type="checkbox" name="checkbox" value=""  placeholde="Your ZIP / Postal Code" required><div class="radio_content">I consent to receive memails, calls, or SMS messages including by automatic telephone dialing system from EatCayman to my email or phone number(s) above for informational and/or marketing purposes. Consent to receive messages is not a condition to make a purchase or sign up. I agree to the independent contractor agreement and have read the Driver Privacy Policy.</div>
                  </div>
                  
                  <input type="submit" name="submit" value="Sign Up" class="submit_btn">
                  
                  <!--<p class="sign_up"><a href="#">Already started signing up?</a></p>-->
                    
                </form>
                
                 <?php  
                  $_SESSION['store_eamil'] = $_POST['email'] ;
                  $_SESSION['Phone'] = $_POST['phone_number'] ;
                  $_SESSION['zip'] = $_POST['zip_code'] ; 
                  ?>
                  
            </div>
        </div>
     </div>
     <!--Become A Delivery Partner end--->
    
    
    <!--testimonial section--->
    <div class="testimonial_section">
        <div class="container">
            <div class="main_content">
                <h1>"Why I love EatCayman..."</h1>
                <p>People drive for a variety of reasons: to spend time with their kids, earn extra money, to pay for school, even to exercise and see the world.</p>
            </div>
            
            
             <div class="item">
            <div class="content">
                Driving is a vacation. It might pay for our wedding, it might send us to the Bahamas, who knows. We'll figure it out when we get there. Sean & Krystina

            </div>
            <div class="img">
                <img src="assets/img/delivery_partner/chowdriver1.png" />
            </div>
            
        </div>    
        
        <div class="item">
            <div class="content">
                You can't really beat the freedom. Money when you want it is pretty dang convenient. And it's consistent.
            </div>
            <div class="img">
                <img src="assets/img/delivery_partner/chowdriver2.png" />
            </div>
            
        </div> 
        
        <div class="item">
            <div class="content">
                I love EatCayman. It's a lot of fun going to different restaurants. Some of them, I don't even know that there's this little hole in the wall until I get an order from them.
            </div>
            <div class="img">
                <img src="assets/img/delivery_partner/chowdriver3.png" />
            </div>
            
        </div> 
            
        </div>
       
    </div>
    <!--testimonial section end--->
    
    
    <!--Become A Delivery Partner--->
     <div class="delivery_partner_bottom_section">
        <div class="container"> 
            <div class="left_sec">
            <div class="content"> 
               <h3> 1. Get activated</h3>
                <p>Give your info, and we'll do a background
                check to get you on your way.</p>
                <a class="myBtnnew firstblock">See full requirements.</a> 
            </div>
                
              <div class="content"> 
                <h3>2. Get ready</h3>
                <p>Pick up basics of getting some gear
                and the iOS or Android app.</p>
              </div>
                  
               <div class="content">  
                <h3>3. Get paid</h3>
                <p>Turn on the app, accept some orders, and
                start bringing home the bacon.</p>
                <a class="myBtnnew secondblock">See full FAQ.</a>
               </div>
            </div>
            
            <div class="right_sec">
                <h1>Get ready to deliver</h1>
                <form method="post">
                  <div class="email">
                      <input type="email" name="email" value=""  placeholder="Email" required>
                  </div>
                  
                  <div class="phone_and_zip">
                      <input type="text" name="phone_number" value=""  placeholder="Phone Number" required>
                  </div>
                  
                  <div class="phone_and_zip">
                      <input type="text" name="zip_code" value=""  placeholder="Your ZIP / Postal Code" required>
                  </div>
                  
                  <div class="radio_btn">
                      <input type="checkbox" name="checkbox" value=""  placeholde="Your ZIP / Postal Code" required><div class="radio_content">I consent to receive memails, calls, or SMS messages including by automatic telephone dialing system from EatCayman to my email or phone number(s) above for informational and/or marketing purposes. Consent to receive messages is not a condition to make a purchase or sign up. I agree to the independent contractor agreement and have read the Driver Privacy Policy.</div>
                  </div>
                  
                  <input type="submit" name="submit" value="Sign Up" class="submit_btn" />
                  
                  <!--<p class="sign_up"><a href="#">Already started signing up?</a></p>-->
                    
                </form>
            </div>
        </div>
     </div>
     <!--Become A Delivery Partner end--->
     
     
      <!-- new The Modal -->
        <div class="modal mymodelnew"> 
          <!-- Modal content -->
          <div class="modal-content">
            <span class="closenew">&times;</span>
            <div id="first_block" class="Dglow">
                <h4>Requirements to Drive</h4>
                <ul>
                    <li>Drivers must be 18 or older</li>
                    <li>Drivers cannot have any major violations in the last 7 years, including: DUI, Reckless Driving, Homicide or Assault, Driving with a suspended license, failure to stop or report and driving with a license that is suspended or expired</li>
                    <li>Drivers cannot have more than 3 incidents* in the last 3 years</li>
                </ul>
                
                <h4>After you are conditionally approved to be onboarded, EatCayman will run a criminal background check with your consent</h4>
                
                <ul>
                    <li>EatCayman conducts background checks in accordance with applicable federal, state, and local laws and regulations.</li>
                    <li>EatCayman will always conduct an individualized assessment.</li>
                    <li>EatCayman will only consider arrests or criminal accusations that are pending or that resulted in a conviction within the past 7 years.</li>
                </ul>
                
                <p>
                    *An incident is an accident or a moving violation other than a major violation. An accident and violation occurring at the same time will be considered one incident.
                </p>
                
            </div>
            
            
            <div id="second_block" class="Dglow">
                <h3>Frequently Asked Questions</h3>
                
                <h4>What does being a EatCayman driver entail?</h4>
                <p>Deliver food and other items from local merchants to customers.</p> 
                
                <h4>What's required to be a EatCayman driver?</h4>
                <p>
                   You must be 18 years old and have an iPhone or Android smartphone. You can use any car to deliver, as long as you have a valid driver’s license, insurance, and a clean driving record.
                </p> 
                
                <h4>Are other vehicle types allowed?</h4>
                <p>Yes, you can use motorcycles, scooters, bikes, or even walk in some markets. See the list of vehicle types when signing up.</p>
                
                <h4>Where is EatCayman available?</h4>
                <p>We currently operate in Jacksonville,NC</p>
                
            </div>
          </div> 
        </div>
        
        
        
<script> 
    $(document).ready(function(){
        $(".firstblock").click(function(){
            document.getElementById('first_block').style.display = 'block';
        });
        
        $(".secondblock").click(function(){
            document.getElementById('second_block').style.display = 'block';
        });
        
        $(".closenew").click(function(){
            document.getElementById('first_block').style.display = 'none';
            document.getElementById('second_block').style.display = 'none';
        });
    });
</script>

<style>
    .Dglow { display: none; }
</style>
         
        
        
        

<script>
    // Get the modal
    var modalnew = document.getElementsByClassName('mymodelnew')[0];  
    // Get the button that opens the modal
    var btnnew = document.getElementsByClassName("myBtnnew")[0];  
    // Get the <span> element that closes the modal
    var spannew = document.getElementsByClassName("closenew")[0]; 
    // When the user clicks the button, open the modal 
    btnnew.onclick = function() {
        modalnew.style.display = "block";
    } 
    // When the user clicks on <span> (x), close the modal
    spannew.onclick = function() {
        modalnew.style.display = "none";
    } 
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event1) {
        if (event1.target == modalnew) {
            modalnew.style.display = "none";
        }
    } 
    var mybtnnew =  document.getElementsByClassName("myBtnnew");
    
    for(j=0; j<=mybtnnew.length; j++){
        mybtnnew[j].onclick = function() {
            modalnew.style.display = "block";
        }
    
    }
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