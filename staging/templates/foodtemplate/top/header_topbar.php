<?php
	if ($_SESSION['sess_user'] == 'company') {
		$sql = "select * from company where iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
		$db_user = $obj->MySQLSelect($sql);
	}
	if ($_SESSION['sess_user'] == 'driver') {
		$sql = "select * from register_driver where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
		$db_user = $obj->MySQLSelect($sql);
	}
	if ($_SESSION['sess_user'] == 'rider'){
		$sql = "select * from register_user where iUserId = '".$_SESSION['sess_iUserId']."'";
		$db_user = $obj->MySQLSelect($sql);
	}
	$col_class = "";
	if($user != "") { 
		$col_class = "top-inner-color";
	}

$cc_loginurl = "http://hurleys.anviam.in:8086/rider-login";
if($_SERVER['REQUEST_URI'] == "/become_a_restaurant_partner.php" )
{
    $cc_loginurl = "http://hurleys.anviam.in:8086/company-login";
}
elseif($_SERVER['REQUEST_URI'] == "/become_a_delivery_partner.php" )
{
    $cc_loginurl = "http://hurleys.anviam.in:8086/driver-login";
}
else
{
   $cc_loginurl = "http://hurleys.anviam.in:8086/rider-login"; 
}

$logo = "logo.png";
if(isset($data[0]['BannerBgImage']) && (!empty($data[0]['BannerBgImage']))) {?>
<style>
.top-part-inner-home {
  background: url('<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['BannerBgImage'];?>');
  background-size: cover;
}
</style>
<?php } ?>
<?php if($script == 'Home'){ ?>
	<!-- top part -->
	<div class="top-part-home <?=$col_class;?>">
		<div class="top-part-inner-home">
		  <?php $logoName = strstr($_SERVER['SCRIPT_NAME'],'/') && strstr($_SERVER['SCRIPT_NAME'],'/index.php')?'logo.png':'logo-inner.png' ;?>
		  <div class="top-logo-menu-part">
		      <div class="top-logo-menu-part-inner">
			        <?php if($user=="") { ?>
			          	<div class="logo">
				            <img src="assets/img/<?php echo $logo;?>" alt="">

				            <?php /* 
				            <span class="top-logo-link" ><a href="about" class="<?=(isset($script) && $script == 'About Us')?'active':'';?>"><?=$langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a><a href="contact-us" class="<?=(isset($script) && $script == 'Contact Us')?'active':'';?>"><?=$langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></span> */ ?>
			          	</div>
				        <div class="menu-part">
				            <ul>
				              <?php if(isset($_REQUEST['edit_lbl'])){ ?>
				               <?php /* <li>
				                  <a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a>
				                </li> */ ?>
				                <li>
				                  <a href="<?php echo $cc_loginurl; ?>" id="cc_link__" class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a>
				                </li>
				               <?php } else {?>
				               <?php /* <li>
				                  <a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a>
				                </li>  ?>
				                <li>
				                  <a href="<?php echo $cc_loginurl; ?>" id="cc_link__" class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a>
				                </li>
				               <?php */}?>
				            </ul>
				        </div>
				    <?php } else { ?>
				    	<?php if($user != "") { 
				    		if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') && ($db_user[0]['vImgName'] == 'NONE' || $db_user[0]['vImgName'] == ''))  {
					          $img_url = "assets/img/profile-user-img.png";
					        } else {
					          	if($_SESSION['sess_user'] == 'company') {
						            $img_path = $tconfig["tsite_upload_images_compnay"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
					            } else if($_SESSION['sess_user'] == 'driver') {
						            $img_path = $tconfig["tsite_upload_images_driver"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
					            } else {
						            $img_path = $tconfig["tsite_upload_images_passenger"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImgName'];
					          	}  
					        } ?>
					        <div class="logo">
					            <a href="index.php"><img src="assets/img/<?php echo $logo; ?>" alt=""></a>
					            <?php if($user == 'driver'){ ?>
						          <span class="top-logo-link" ><a href="profile" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a><a href="logout"><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></span>
						        <?php } else { ?>
						          <span class="top-logo-link" ><a href="dashboard" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a><a href="logout"><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></span>
						        <?php } ?>
				          	</div>
				          	<div class="top-link-login-new">
					          <div class="user-part-login">
					            <b><img src="<?= $img_url ?>" alt=""></b>
					            <div class="top-link-login">
					                <label><img src="assets/img/arrow-menu.png" alt=""></label>
					                <ul>
					                    <?php if($user == 'driver'){ ?>
					                      <li><a href="profile" class="<?=(isset($script) && $script == 'Profile')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>

					                      <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
					                    <?php } else if($user == 'company'){ ?>
					                      <li><a href="dashboard" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>

					                      <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
					                    <?php } else if($user == 'rider') { ?>
					                      <li><a href="profile-rider" class="<?=(isset($script) && $script == 'Profile')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>
					                      <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
					                    <?php } ?>
					                </ul>
					            </div>
					          </div>
					        </div>
				    	<?php } ?>
				    <?php } ?>
				    
				   
				    
		      </div>
		  </div>

		  <div class="banner-text">
		    <div class="banner-text-inner">
		      <h1>
		      	<?if(!empty($data[0]['BannerBigTitle'])) { 
		      		echo $data[0]['BannerBigTitle']; 
		      	} else {
		      		echo 'FOOD DELIVERY';
		      	} ?>
		      	<b>
		      		<?if(!empty($data[0]['BannerSmallTitle'])) { 
		      			echo $data[0]['BannerSmallTitle']; 
		      		} else {
		      			echo 'ON DEMAND';
		      		}?>
		      	</b>
		      </h1>
		      
<!--search box--->
          <!--<form method="post" action="restaurants.php">
		    <div id="locationField">
		        <i class="fas fa-map-marker"></i>
                <input id="autocomplete" placeholder="Enter your delivery address"
                     onFocus="geolocate()" type="text" name="address" required>
                     <input type="hidden" id="geolat" name="geolat" />
                      <input type="hidden" id="geolong" name="geolong" />
                <input type="submit" value="Find Restaurants" class="search_btn" />     
            </div>
          </form>  --->
            <script>
               // $('.search_btn').click(function() {
                 //   window.location.href = 'http://hurleys.anviam.in:8086/coming-soon.php';
                //   window.location.href = 'http://hurleys.anviam.in:8086/restaurants.php';
                //    return false;
                //});
            </script>
            
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

       // for (var component in componentForm) {
         //document.getElementById(component).value = '';
       //   document.getElementById(component).disabled = false;
      //  }

        // Get each component of the address from the place details
        // and fill the corresponding field on the form.
       /* for (var i = 0; i < place.address_components.length; i++) {
          var addressType = place.address_components[i].types[0];
          if (componentForm[addressType]) {
            var val = place.address_components[i][componentForm[addressType]];
           
            document.getElementById(addressType).value = val;
          }
        }*/
      //  alert(place.geometry.location.lat());
         document.getElementById('geolat').value =  place.geometry.location.lat();
              document.getElementById('geolong').value = place.geometry.location.lng();
           
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

		    <!--search box end--->		      
		      
		      <?if(!empty($data[0]['BannerContent'])) { 
		      		 //echo  $data[0]['BannerContent']; 
		      	} else { 
		      			//echo '<p>Satisfy your cravings with CubeFood with a culinary experience that beats any five-star restaurants<span><a href="about">Read More</a></span></p>';
		      		} ?>
		      
		      <div style="clear:both;"></div>
		    </div>
		    
		    
		    
		  </div>
		  
          <!--<div class="banner-apps"><span class="mobileapp">Get the app
			 <a href="#" target="_blank"><i class="fab fa-android"></i></a>
			 <a href="#" target="_blank"><i class="fab fa-apple"></i></a>
			 </span>
			 <button id="myBtn" class="myBtn">Get the app</button>
		 </div> 
		 <div class="banner_bottom_text">
			     <h1>Veteran owned</h1>
		 </div>-->
		 
		 
		 
		 
        
           <!--<script>
                $('.phone_submit').click(function() {
                    window.location.href = 'http://hurleys.anviam.in:8086/coming-soon.php';
                    return false;
                });
            </script>-->
          
		 
		 
		</div>
	</div>
<?php } else { ?>
		<!-- top part -->
	<div class="top-part <?=$col_class;?>" id="top-part">
		<div class="top-part-inner">
		  <?php $logoName = strstr($_SERVER['SCRIPT_NAME'],'/') && strstr($_SERVER['SCRIPT_NAME'],'/index.php')?'logo.png':'logo-inner.png' ;?>
		  <div class="top-logo-menu-part">
		      <div class="top-logo-menu-part-inner">
			        <?php if($user=="") { ?>
			          	<div class="logo">
				            <a href="index.php"><img src="assets/img/<?php echo $logo;?>" alt=""></a>

				            <span class="top-logo-link" ><a href="about" class="<?=(isset($script) && $script == 'About Us')?'active':'';?>"><?=$langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a><a href="contact-us" class="<?=(isset($script) && $script == 'Contact Us')?'active':'';?>"><?=$langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></span>
			          	</div>
				        <div class="menu-part">
				            <ul>
				              <?php if(isset($_REQUEST['edit_lbl'])){ ?>
				               <?php /* <li>
				                  <a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a>
				                </li> */ ?>
				                <li>
				                  <a href="<?php echo $cc_loginurl; ?>"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a>
				                </li>
				               <?php } else {?>
				               <?php /* <li>
				                  <a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a>
				                </li> */ ?>
				                <li>
				                  <a href="<?php echo $cc_loginurl; ?>"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a>
				                </li>
				               <?php }?>
				            </ul>
				        </div>
				    <?php } else { ?>
				    	<?php if($user != "") { 
				    		if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') && ($db_user[0]['vImgName'] == 'NONE' || $db_user[0]['vImgName'] == ''))  {
					          $img_url = "assets/img/profile-user-img.png";
					        } else {
					          	if($_SESSION['sess_user'] == 'company') {
						            $img_path = $tconfig["tsite_upload_images_compnay"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
					            } else if($_SESSION['sess_user'] == 'driver') {
						            $img_path = $tconfig["tsite_upload_images_driver"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
					            } else {
						            $img_path = $tconfig["tsite_upload_images_passenger"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImgName'];
					          	}  
					        } ?>
					        <div class="logo">
					            <a href="index.php"><img src="assets/img/<?php echo $logo; ?>" alt=""></a>
					            <?php if($user == 'driver'){ ?>
						          <span class="top-logo-link" ><a href="profile" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a><a href="logout"><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></span>
						        <?php } else { ?>
						          <span class="top-logo-link" ><a href="dashboard" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a><a href="logout"><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></span>
						        <?php } ?>
				          	</div>
				          	<div class="top-link-login-new">
					          <div class="user-part-login">
					            <b><img src="<?= $img_url ?>" alt=""></b>
					            <div class="top-link-login">
					                <label><img src="assets/img/arrow-menu.png" alt=""></label>
					                <ul>
					                    <?php if($user == 'driver'){ ?>
					                      <li><a href="profile" class="<?=(isset($script) && $script == 'Profile')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>

					                      <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
					                    <?php } else if($user == 'company'){ ?>
					                      <li><a href="dashboard" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>

					                      <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
					                    <?php } else if($user == 'rider') { ?>
					                      <li><a href="profile-rider" class="<?=(isset($script) && $script == 'Profile')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>
					                      <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
					                    <?php } ?>
					                </ul>
					            </div>
					          </div>
					        </div>
				    	<?php } ?>
				    <?php } ?>
		      </div>
		  </div>
		</div>
	</div>
<?php } ?>
<div style="clear:both;"></div>
