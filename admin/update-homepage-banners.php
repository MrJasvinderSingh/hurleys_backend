<?php

/*
Array
(
    [0] => Array
        (
            [0] => 1
            [id] => 1
            [1] => cb1c3848b0508be1e91b2a144a44a302.png
            [image] => cb1c3848b0508be1e91b2a144a44a302.png
            [2] => https://hurleys.ky/shop-landing/
            [url] => https://hurleys.ky/shop-landing/
            [3] => 2022-10-11 10:18:23
            [date_updated] => 2022-10-11 10:18:23
        )

)
*/

include_once('../common.php');
$id=$_GET['updateid'];
$sql="select * from banner_images where id=$id";

$results = $obj->sql_query($sql);

  //$statement = $mysqli->prepare("update crud set id=$id,name='$u_name',phone='$u_phone',email='$u_email' where id=$id");
if ($_SERVER["REQUEST_METHOD"] =="POST"){
    //echo $id;
     //$img = $_POST["image"];
     $url = $_POST['url'];
     if(isset($_FILES['image'])){
        $banner=$_FILES['image']['name']; 
        $expbanner=explode('.',$banner);
        $bannerexptype=$expbanner[1];
        date_default_timezone_set('Australia/Melbourne');
        $date = date('m/d/Yh:i:sa', time());
        $rand=rand(10000,99999);
        $encname=$date.$rand;
        $bannername=md5($encname).'.'.$bannerexptype;
        $bannerpath="banners/".$bannername;
        move_uploaded_file($_FILES["image"]["tmp_name"],$bannerpath);
    }
     

     
     if(!empty($_FILES['image']['name'])){
        $query = "update banner_images set image='".$bannername."',url='".$url."' where id=".$id;
    } else {
        $query = "update banner_images set url='".$url."' where id=".$id;
    }
     //$statement = $obj->prepare("update banner_images set id=$id,image='$bannername' where id='".$id ."' ");
    
   
     $result = $obj->sql_query($query);
     if($result == 1) {
       //print "Hello " . $u_name . "!, your data has been updated!"; exit;
         header('location:homepage-banners.php');
     }
     
   
   
}
?>

<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> |  Push Notifications</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php');?>

        <style>
            
            .inner h2 {
    float: none;
    margin: 20px 0;}
    .btn-primary a {color:#ffffff;}
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
      <!-- Main LOading -->
      <!-- MAIN WRAPPER -->
      <div id="wrap">
          <?php include_once('header.php'); ?>
          <?php include_once('left_menu.php'); ?>
          <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                    <?php if(!empty($msg)){
                  echo '<div class="row">
                            <div class="col-lg-12">
                                <p class="text-success">'.$msg.'</p>
                            </div>
                        </div>';
              } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <h2>Home Page Banners</h2>
                            </div>
                        </div>
                       <hr />
                    </div>
                    <div class="table-list">
                        <div class="row">
                            <!-- start push   -->
                            <div class="container">
    <form action="" method="POST" enctype="multipart/form-data">
        
    <label for="file">Banner :</label> 
    <img src="banners/<?php echo $results[0]['image'] ?>" width="200px;">
        <input type="file" id="image" name="image"> <br>
        <label for="url">URL :</label> 
        <input type="url" id= "url" name="url" required value="<?php echo $results[0]['url'] ?>">
        <br> <br>
        <input type="submit" id="button" name="button" value="update" class="btn btn-primary">
    </form>
    </div>
                            <!-- end push     -->
                    </div> <!--TABLE-END-->
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>

<?php include_once('footer.php'); ?>
<link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" />
   
    <script src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>

    </body>
<!-- END BODY-->
</html>