<?php
	include_once("common.php");
	//error_reporting(E_ALL);
	global $generalobj;
	$script="Restaurants";
	$meta = $generalobj->getStaticPage(1,$_SESSION['sess_lang']);
	 //echo "<pre>";print_r($_);exit;
        $sql="select cmp.*,cn.vCountry as country,ct.vCity as city,st.vState as state from company cmp
left join country cn on cn.vCountryCode = cmp.vCountry
left join city ct on ct.iCityId = cmp.vCity
left join state st on st.iStateId = cmp.vState
WHERE cmp.eStatus = 'Active' AND ct.vCity='Jacksonville'";
$data_company = $obj->MySQLSelect($sql);

$reg_date1 = $data_company[0]['tRegistrationDate'];
// Tuesday, Aug  22<sup>nd</sup> 2017
if($reg_date1 != "0000-00-00 00:00:00"){
	$reg_date = date("l, M d \<\s\u\p\>S\<\/\s\u\p\>\ Y",strtotime($reg_date1));
}else{
	$reg_date = "";
}
 // exit;
if($data_company[0]['vImage'] != "")
	$image_path = $tconfig["tsite_upload_images_compnay"].'/'.$iCompanyId.'/2_'.$data_company[0]['vImage'];
else{
	$image_path = "../assets/img/profile-user-img.png";
}

$rating_width = ($data_company[0]['vAvgRating'] * 100) / 5;
if($data_company[0]['vAvgRating'] > 0){
	$Rating = '<span title="'.$data_company[0]['vAvgRating'].'" style="display: block; width: 65px; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 0;">
	<span style="margin: 0;float:left;display: block; width: '.$rating_width.'%; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 -13px;"></span>
	</span>';
}else{
	$Rating = "No ratings received";
}


function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}


$postlat = $_POST['geolat'];
$postlong = $_POST['geolong'];

//echo "<pre>"; print_r($data_company); echo "</pre>"; exit;
$result = array();
foreach($data_company as $keydc=>$valuedc) : 
    $distancedata = '';
    
     $distancedata = distance($postlat, $postlong, $valuedc['vRestuarantLocationLat'], $valuedc['vRestuarantLocationLong'], 'M');
     if($distancedata < 5) 
     {
    $result[] = array(
        'title'=>$valuedc['vCompany'],
        'image'=>$valuedc['vImage'] ?  'http://hurleys.anviam.in:8086/webimages/upload/Company'.DS.$valuedc['iCompanyId'].DS.$valuedc['vImage'] : 'http://hurleys.anviam.in:8086/assets/img/logo.png',
        'minorder'=>$valuedc['fMinOrderValue'],
        'rating'=>$valuedc['vAvgRating'],
        'distance'=> $distancedata
    );
     }
endforeach; 

if(!empty($result)) {
array_multisort( array_column($result, "distance"), SORT_ASC, $result );
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>
<? // =$meta['meta_title'];?>
Restaurants
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
    
    <div class="banner_section">
       <div class="container">
           <h4>All Restaurants</h4>
           <?php //echo "<pre>"; print_r($result); echo "</pre>";   ?>
       </div>
    </div>
    
    <div class="all_restaurants">
         
        <div class="container"> 
              <?php if(!empty($result)) { foreach($result as $key=>$value) :  
              
             
              
              ?>
            <div class="restaurants_items">
                <div class="img" style="background:url('');" style="align:center;">
                    <img src="<?=  $value['image'] ?>" width="110"  height="auto"/>
                </div>
                <div class="content">
                    <h5><?= $value['title'] ?> <span class="check"><i class="fa fa-check-circle" aria-hidden="true"></i></span></h5> 
                    <span class="items">Min Order </span>
                    <span class="price"><?= $value['minorder'] ?></span>
                    <span class="rating"><?= $value['rating'] ?> <i class="fa fa-star" aria-hidden="true"></i> <span>&nbsp;</span></span>
                    <span class="delivery_price">Delivery @ $<?= $value['minorder'] ?></span>
                    <h6>Distance : <?= round($value['distance'] , 2) ?> M</h6>
                </div> 
            </div>
            <?php  endforeach; } else { echo "<h2>Sorry no result found..!</h2>"; }  ?>
            
            
             
              
        </div>
    </div>
    
     
    
    
       
        
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