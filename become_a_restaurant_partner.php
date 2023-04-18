<?php if(isset($_POST['submit'])) {  
    header('Location: sign-up-restaurant.php');  
}
?>   
                
<?
	include_once("common.php");
	//error_reporting(E_ALL);
	global $generalobj;
	$script="Become A Restaurant Partner";
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
Become A Restaurant Partner
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
     <div class="delivery_partner_banner_section restaurant">
        <div class="container">
            
            <div class="left_sec">
                <div class="image">
                    <img src="assets/img/delivery_partner/laptop.png" />
                </div>
                <div class="download_btn">
                   <h4>Merchant sign-up sheet</h4> 
                   <a href="https://drive.google.com/uc?export=download&id=199UpklwK9gAGl2PbYCHbkOElIzex7tUy" target="_blank">Download form</a>
                </div>
            </div>
            
            <div class="right_sec">
                <h1>Activate Chow call in days</h1>
                <form method="POST">
                  <div class="email">
                      <input type="text" name="store" value=""  placeholder="Store Name" required>
                  </div>
                   
                   
                  <div id="locationField">
        		        <i class="fas fa-map-marker"></i>
                        <input id="autocomplete" placeholder="Enter your delivery address"
                             onFocus="geolocate()" type="text"></input> 
                  </div>
                   
                  <div class="phone_and_zip">
                      <input type="text" name="full_name" value=""  placeholder="Full Name" required>
                  </div>
                  
                  <div class="phone_and_zip mobile">
                      <input type="text" name="store_mobile_no" value=""  placeholder="Mobile Phone" required>
                  </div>
                   
                  <div class="email contact_email">
                      <input type="email" name="store_email" value=""  placeholder="Contact Email" required>
                  </div>
                   
                  <input type="submit" name="submit" value="Sign Up" class="submit_btn" /> 
                  
                </form>
                <div>
                  <?php  
                  $_SESSION['storename'] = $_POST['store'] ;
                  $_SESSION['f_name'] = $_POST['full_name'] ;
                  $_SESSION['mobile'] = $_POST['store_mobile_no'] ;
                  $_SESSION['s_email'] = $_POST['store_email'] ;
                  ?>
                
              
                
                   </div> 
                
            </div>
        </div>
     </div>
     <!--Become A Delivery Partner end--->
    
    
    <div class="restaurant_center_section">
        <div class="container">
            <div class="content">
                <h1>Get more customers with less staff</h1>
                <p>Our app and site will put your menu in front of the whole town.</p>
                
                <h1>We'll work with what you have</h1>
                <p>EatCayman can send your orders over fax,
                computer, or tablet.</p>
                
                <h1>Get back to what you care about</h1>
                <p>We will take care of the customer and logistics
                before, during, and after the delivery.</p>

            </div>
            
            <div class="image">
               <img src="assets/img/delivery_partner/mobile.jpg" />    
            </div>
            
        </div>
    </div>
    
    
    <!--testimonial section--->
    <div class="restaurant_image_section">
        <div class="container">
            <div class="main_content">
                <h1>Restaurants Love Us</h1>
                <p class="sub-heading">Local Drivers for Local Restaurants</p>
            </div> 
            
            <div class="image">
                <img src="assets/img/delivery_partner/food.jpg" />
            </div>
            
        </div>
       
    </div>
    <!--testimonial section end--->
    
    
    <!--Become A Delivery Partner--->
     <div class="delivery_partner_bottom_section restaurant_bottom">
        <div class="container"> 
            <div class="left_sec">
                <div class="content"> 
                   <h3> 1. Sign Up online</h3>
                    <p>Submit a form with your store and contact information.</p> 
                </div>
                     
              <div class="content"> 
                <h3>2. We'll contact you</h3>
                <p>Our sales team will get back to you quickly, and we'll collect any more info we need to get you listed.</p>
              </div>
                  
               <div class="content">  
                <h3>3. Start getting orders this week</h3>
                <p>Once you're listed, you'll start receiving orders from customers on EatCayman.</p> 
               </div>
            </div>
            
            <div class="right_sec">
                <h1>Start delivering this week</h1> 
                <form method="post">
                  <div class="email">
                      <input type="text" name="store" value=""  placeholder="Store Name" required>
                  </div>
                   
                   
                  <div id="locationField">
        		        <i class="fas fa-map-marker"></i>
                        <input id="autocomplete" placeholder="Enter your delivery address"
                             onFocus="geolocate()" type="text"></input> 
                 </div>
                   
                  <div class="phone_and_zip">
                      <input type="text" name="full_name" value=""  placeholder="Full Name" required>
                  </div>
                  
                  <div class="phone_and_zip mobile">
                      <input type="text" name="store_mobile_no" value=""  placeholder="Mobile Phone" required>
                  </div>
                   
                  <div class="email contact_email">
                      <input type="email" name="store_email" value=""  placeholder="Contact Email" required>
                  </div>
                   
                  <input type="submit" name="submit" value="Sign Up" class="submit_btn" /> 
                </form>
                
            </div>
        </div>
     </div>
     <!--Become A Delivery Partner end--->
  
  
  
  <!--search box--->
		 
<script>
      // This example displays an address form, using the autocomplete feature
      // of the Google Places API to help users fill in the information.

      // This example requires the Places library. Include the libraries=places
      // parameter when you first load the API. For example:
      // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

      var placeSearch, autocomplete;
      var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'short_name',
        country: 'long_name',
        postal_code: 'short_name'
      };

      function initAutocomplete() {
        // Create the autocomplete object, restricting the search to geographical
        // location types.
        autocomplete = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
            {types: ['geocode']});

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);
      }

      function fillInAddress() {
        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();

        for (var component in componentForm) {
          document.getElementById(component).value = '';
          document.getElementById(component).disabled = false;
        }

        // Get each component of the address from the place details
        // and fill the corresponding field on the form.
        for (var i = 0; i < place.address_components.length; i++) {
          var addressType = place.address_components[i].types[0];
          if (componentForm[addressType]) {
            var val = place.address_components[i][componentForm[addressType]];
            document.getElementById(addressType).value = val;
          }
        }
      }

      // Bias the autocomplete object to the user's geographical location,
      // as supplied by the browser's 'navigator.geolocation' object.
      function geolocate() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            var circle = new google.maps.Circle({
              center: geolocation,
              radius: position.coords.accuracy
            });
            autocomplete.setBounds(circle.getBounds());
          });
        }
      }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBpS6KkKA19pQD7Ai663AbGInvuSZWxICI&libraries=places&callback=initAutocomplete"
        async defer></script>  
    
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