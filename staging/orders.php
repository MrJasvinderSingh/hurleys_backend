<?php
	include_once('common.php');
	$generalobj->check_member_login();
	$abc = 'rider';
	$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$generalobj->setRole($abc,$url);
	$user = $_SESSION["sess_user"];
	
	
	

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?=$SITE_NAME?> |<?=$langage_lbl['LBL_HEADER_PROFILE_TXT']; ?></title>
		
		<!-- Default Top Script and css -->
		<?php include_once("top/top_script.php");?>
		<link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" >
		<link rel="stylesheet" href="assets/validation/validatrix.css" />
		<!-- End: Default Top Script and css-->
	</head>
	<body>
		<!-- home page -->
		<div id="main-uber-page">
			<!-- Left Menu -->
			<?php include_once("top/left_menu.php");?>
			<!-- End: Left Menu-->
			<!-- Top Menu -->
			<?php include_once("top/header_topbar.php");?>
			<!-- End: Top Menu-->
			<!-- contact page-->
			<div class="page-contant">
				<div class="page-contant-inner">
                    <h2 class="header-page">Orders</h2>
                    <!-- profile page -->
                    <div class="driver-profile-page">                    
						
                        
               <div class="body-div">
                  <div class="form-group">
                      <form action="http://hurleys.anviam.in:8086/admin/orders/pages/savecustomerdetails" id="RegisterCustomerForm" method="post" accept-charset="utf-8"><div style="display:none;"><input type="hidden" name="_method" value="POST"/></div>                    
                        <!-- Changes For Restaurant -->
                        <div class="row">
                          <div class="col-md-12">
                            Customer Details
                            <hr>
                          </div>
                          <div class="col-lg-6">
                            <div class="row">
                               <div class="col-lg-12">
                                  <label>Phone Number<span class="red"> *</span></label>
                               </div>
                               <div class="col-lg-12">
                                  
                                  <input name="data[RegisterUser][vPhone]" placeholder="Phone Number" class="form-control" required="required" maxlength="50" type="text" id="RegisterUserVPhone"/>                               </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="row">
                               <div class="col-lg-12">
                                  <label>Email<span class="red"> *</span></label>
                               </div>
                               <div class="col-lg-12">
                                  <input name="data[RegisterUser][vEmail]" placeholder="Email" class="form-control" value="customers@eatcayman" required="required" maxlength="100" type="text" id="RegisterUserVEmail"/>                               </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="row">
                               <div class="col-lg-12">
                                  <label>First Name<span class="red"> *</span></label>
                               </div>
                               <div class="col-lg-12">
                                  <input name="data[RegisterUser][vName]" placeholder="First Name" class="form-control" required="required" maxlength="100" type="text" id="RegisterUserVName"/> 
                               </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="row">
                               <div class="col-lg-12">
                                  <label>Last Name<span class="red"> *</span></label>
                               </div>
                               <div class="col-lg-12">
                                 <input name="data[RegisterUser][vLastName]" placeholder="Last Name" class="form-control" required="required" maxlength="255" type="text" id="RegisterUserVLastName"/> 
                               </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="row">
                               <div class="col-lg-12">
                                  <label>Destination Address<span class="red"> *</span></label>
                               </div>
                               <div class="col-lg-12">
                               
                                  <input name="data[RegisterUser][tDestinationAddress]" id="autocomplete" placeholder="Enter your delivery address" onFocus="geolocate()" class="form-control" required="required" type="text"/><input type="hidden" name="data[RegisterUser][geolat]" id="geolat"/><input type="hidden" name="data[RegisterUser][geolong]" id="geolong"/>                                   
                               </div>
                            </div>
                          </div>
                            
                            <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-12" id="restaurantsearchform">
                                   
                                  <input type="hidden" name="data[RegisterUser][iUserId]" value="0" id="RegisterUserIUserId"/><div class="submit"><input class="btn btn-primary" id="submitcustomerdetails" style="margin-top:25px;" type="submit" value="Show Restaurants"/></div>                                   
                               </div>
                            </div>
                          </div>
                        </div>
                        </form><!-- ------------------------------------------------ Restaurant List ----------------------------------- -->
                        <div class="row">
                          <div class="col-md-12">
                            All Restaurants
                            <hr>
                          </div>
                            <div class="row" id="searchrestaurants">
                                 
                                <div class="col-lg-12">
                                    <form action="http://hurleys.anviam.in:8086/admin/orders/pages/restaurants" id="restaurantsearch" method="post" accept-charset="utf-8"><div style="display:none;"><input type="hidden" name="_method" value="POST"/></div>                                  <div class="col-lg-1">
                                    <label>Restaurant</label>
                                  </div>
                                  <div class="col-lg-5">
                                       <div class="autocomplete">
                                         <input name="data[Search][restaurant]" placeholder="Search Restaurant" class="form-control" type="text" id="SearchRestaurant"/>                                         <div id="suggesstion-box"></div>
                                </div>
                                       <input type="hidden" name="data[Search][geolat]" value="0" id="SearchGeolat"/><input type="hidden" name="data[Search][geolong]" value="0" id="SearchGeolong"/> 
                                     
                                  </div>
                                  <div class="col-lg-6">
                                      <div class="submit"><input class="btn btn-default" id="submitsearchrestaurant" type="submit" value="Search"/></div></form>                                    
                                  </div>
                                </div>
                            </div>
                            <div class="row" id="viewallrestaurants">
                                
                            </div>
                        </div>






<!-- -------------------------   Menu Items ------------------------------------------- -->
                        <div class="row">
                          <div class="col-md-12">
                            Categories
                            <hr>
                          </div>
<!--                          <div class="col-md-12">
                            <div class="col-md-1">
                              Items
                            </div>
                            <div class="col-md-5">
                              <input name="data[Search][restaurant]" class="form-control" type="text" id="SearchRestaurant">
                            </div>
                            <div class="col-md-6">
                              <input type="submit" class="btn btn-default" value="Search">
                            </div>
                          </div>-->
                        </div>
                        <div class="row" id="displaycategory">
                          
                        </div>
<div class="row">
    
    <div class="row">
        <div class="col-md-12 text-center" id="displayprocessorder">
            
        </div>
    </div>
    
    <div class="col-md-12 text-right">
        <button class="btn btn-primary btn-md" id="previewcart">Preview Cart</button>
    </div>
</div>





  <!----                   Preview --   -------------- --->   
  
  
  <div class="modal fade" id="previewmycart" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>
        <h4 class="modal-title" id="myModdalLabel">Cart Items</h4>
      </div>
      <div class="modal-body" id="showcartitems">
        ...
      </div>
<!--      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Add More</button>
        <button type="button" class="btn btn-primary">Check Out</button>
      </div>-->
    </div>
  </div>
</div>
  
  
                  </div>
               </div>
            





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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBpS6KkKA19pQD7Ai663AbGInvuSZWxICI&libraries=places&callback=initAutocomplete" async defer></script>

<script type="text/javascript"> 
 $(document).ready(function(){
     $('#searchrestaurants').hide();
//     $('input#RegisterUserVPhone').keyup( function() {
//         
//          if( this.value.length == 10 )
//        {
//                $.ajax({
//                    type:"POST",
//                    url: "/EatCayman/admin/orders/pages/checkexisting",
//                    data: { phone: this.value},//only input.
//                    dataType: "json",
//                    success: function(response){
//                        if(response.status == true)
//                        {
//                            $('#RegisterUserVName').val(response.RegisterUserVName);
//                            $('#RegisterUserVLastName').val(response.RegisterUserVLastName);
//                            $('#autocomplete').val(response.autocomplete);
//                            $('#RegisterUserIUserId').val(response.iUserId);
//                            $('#RegisterUserVEmail').val(response.RegisterUserVEmail);
//                            $('#geolat').val(response.geolat);
//                            $('#geolong').val(response.geolong);
//                            $('#SearchGeolat').val(response.geolat);
//                            $('#SearchGeolong').val(response.geolong);
//                           
//                        }
//                        
//                        
//                    }
//                });
//
//        }
//  
//        
//      
//});
     
     
    var formRegisterCustomer = $("#RegisterCustomerForm");
    $("#submitcustomerdetails").click(function(e){
    e.preventDefault();
    $.ajax({
            type:"POST",
            url:formRegisterCustomer.attr("action"),
            data:$("#RegisterCustomerForm input").serialize(),//only input
            success: function(response){
                //$("#RegisterCustomerForm input").prop("disabled", true);
                $("#viewallrestaurants").html('');
                 $('#searchrestaurants').show();
                $("#viewallrestaurants").html(response);
                //console.log(response);  
            }
        });
    });
   
    
    
    
     var resturantsearchform = $("#restaurantsearch");
    $("#submitsearchrestaurant").click(function(e){
    e.preventDefault();
    $.ajax({
            type:"POST",
            url:resturantsearchform.attr("action"),
            data:$("#restaurantsearch input").serialize(),//only input
            success: function(response){
               $("#viewallrestaurants").html('');
                $("#viewallrestaurants").html(response);
                //console.log(response);  
            }
        });
    });
    
    
    
    

    
    
    
    $('#previewcart').click(function(){
         $.ajax({
                    type:"POST",
                    url: "/admin/orders/carts/view",
                    success: function(response){
                          $("#showcartitems").html(''); 
                        $("#showcartitems").html(response); 
                        $("#previewmycart").modal('show'); 
                    }
                });
    });
    
    
    
    
    //$("#RegisterUserVPhone").inputmask("99/99/9999" });
    
    $("#RegisterUserVPhone").inputmask("999-999-9999",{ "oncomplete": function(){ checkexisting(); } });
    function checkexisting()
    {
        $phone = $('#RegisterUserVPhone').val();
        $.ajax({
                    type:"POST",
                    url: "/admin/orders/pages/checkexisting",
                    data: { phone: $phone},//only input.
                    dataType: "json",
                    success: function(response){

                        if(response.status == true)
                        {
                            $('#RegisterUserVName').val(response.RegisterUserVName);
                            $('#RegisterUserVLastName').val(response.RegisterUserVLastName);
                            $('#autocomplete').val(response.autocomplete);
                            $('#RegisterUserIUserId').val(response.iUserId);
                            $('#RegisterUserVEmail').val(response.RegisterUserVEmail);
                            $('#geolat').val(response.geolat);
                            $('#geolong').val(response.geolong);
                            $('#SearchGeolat').val(response.geolat);
                            $('#SearchGeolong').val(response.geolong);
                           
                        }
                        
                        
                    }
                });
    }
    
    
    
    
    
    $("#SearchRestaurant").keyup(function(){
            
		$.ajax({
		type: "POST",
		url: "/admin/orders/pages/getsearchlist",
		data: {keyword : $(this).val(),lat:$('#SearchGeolat').val(), long: $('#SearchGeolong').val()},
		beforeSend: function(){
			$("#SearchRestaurant").css("background","#fff  165px");
		},
		success: function(data){
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#SearchRestaurant").css("background","#FFF");
		},
                error: function()
                {
                    alert('something went wrong');
                }
		});
	});
    
    
    
    
    });
    
    
    function selectCompany(val) {
        $("#SearchRestaurant").val(val);
        $("#suggesstion-box").hide();
        }
    
    
    function showcategory(companyid)
    {
        var iCompanyId = companyid;
         $.ajax({
                    type:"POST",
                    url: "/admin/orders/pages/getmenucategory",
                    data: { iCompanyId: iCompanyId},//only input.
                    success: function(response){
                         $("#viewallrestaurants").html('');
                        $("#displaycategory").html('');  
                        $("#displaycategory").html(response);  
                    }
                });
    }
    
    
         function showmenuitems(iFoodMenuId)
 {
     var id = iFoodMenuId;
    $.ajax({
                    type:"POST",
                    url: "/admin/orders/pages/getmenuitems",
                    data: { iFoodMenuId: id},//only input.
                    success: function(response){
                        $("#displaymenu").html('');  
                        $("#displaymenu").html(response);  
                    }
                });
 }
 
 
 
</script>
<style>
.autocomplete {
  /*the container must be positioned relative:*/
  position: relative;
  border: 1px dotted #ccc;
}

.autocomplete .suggesstion-box ul {
  position: absolute;
  border: 1px solid #d4d4d4;
  border-bottom: none;
  border-top: none;
  z-index: 99;
  /*position the autocomplete items to be the same width as the container:*/
  top: 100%;
  left: 0;
  right: 0;
}
.suggesstion-box ul li {
  padding: 10px;
  cursor: pointer;
  background-color: #fff; 
  border-bottom: 1px solid #d4d4d4; 
}
.suggesstion-box ul li:hover {
  /*when hovering an item:*/
  background-color: #e9e9e9; 
}
.suggesstion-box ul li:active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}
    
    
</style>
         <style>
             .pretipppppp{
                 list-style : none outside none;
                 clear: both;
             }
             .pretipppppp li { float: left; margin-right: 10px;}
             
             
             
             .categorylist { list-style: none outside none;}
             .categorylist li { margin-bottom:  10px;}
             </style>
             
                        
					</div>
					<div style="clear:both;"></div>
				</div>
				
			</div> 
			<!-- footer part -->
			<?php include_once('footer/footer_home.php');?>
			<!-- footer part end -->
            <!-- -->
			<div  class="clearfix"></div>
		</div>
		<!-- home page end-->
		<!-- Footer Script -->
		<?php include_once('top/footer_script.php');
		$lang = get_langcode($_SESSION['sess_lang']);?>
		<style>
		.upload-error .help-block{
		    color:#b94a48;
		}
		</style>
		<script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
		<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
		<?php if($lang != 'en') { ?>
		<script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script>
		<?php } ?>
		<script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
		<!-- End: Footer Script -->
		
	</body>
</html>


