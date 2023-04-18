<?php
include_once('../common.php');

// $img = $_POST['image'];
$sql = "SELECT * FROM banner_images ";
$result = $obj->MySQLSelect($sql);

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
    <section class="top-section">
        <div class="container pt-4">
            <h2>Top Banner Images</h2>
            <div class="top-images mt-4">
                <div class="row pt-3">
                    <div class="col-md-2 align-self-center">
                        <span>1.</span>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $sql = "select * from banner_images where id= '1'";
                        $result = $obj->MySQLSelect($sql);// echo '<pre>'; print_r($result); echo '</pre>';
                        if ($result) {
                            
                        ?>
                                <img src="http://52.3.107.59/hurleys_backend/admin/banners/<?php echo $result[0]['image'] ?>" alt="image" style="width:200px ;">
                            
                        <?php } ?>
                    </div>
                    <div class="col-md-4 align-self-center">
                        <button class="btn btn-primary"><a href="update-homepage-banners.php?updateid=1" class="text-decoration-none text-white">Update</a> </button>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col-md-2 align-self-center">
                        <span>2.</span>
                    </div>
                    <div class="col-md-4">
                        <!-- <img src="pexels-photo-302743.jpeg" alt="image" style="width:200px ;"> -->
                        <?php
                        $sql = "select * from banner_images where id= '2'";
                        $result = $obj->MySQLSelect($sql);
                        if ($result) {
                            
                            ?>
                                    <img src="http://52.3.107.59/hurleys_backend/admin/banners/<?php echo $result[0]['image'] ?>" alt="image" style="width:200px ;">
                                
                            <?php } ?>

                    </div>
                    <div class="col-md-4 align-self-center">
                        <button class="btn btn-primary"><a href="update-homepage-banners.php?updateid=2" class="text-decoration-none text-white">update</a> </button>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col-md-2 align-self-center">
                        <span>3.</span>
                    </div>
                    <div class="col-md-4 ">
                        <!-- <img src="photo-1503023345310-bd7c1de61c7d.jpg" alt="image" style="max-width:200px ;height:200px"> -->
                        <?php
                        $sql = "select * from banner_images where id= '3'";
                        $result = $obj->MySQLSelect($sql);
                        if ($result) { ?>
                             <img src="http://52.3.107.59/hurleys_backend/admin/banners/<?php echo $result[0]['image'] ?>" alt="image" style="width:200px ;">
                         <?php } ?>

                    </div>
                    <div class="col-md-4 align-self-center ">
                        <button class="btn btn-primary"><a href="update-homepage-banners.php?updateid=3" class="text-decoration-none text-white">Update</a> </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="middle-section">
        <div class="container pt-4 ">
            <h2>Middle Banner Image</h2>
            <div class="middle-images pt-4">
                <div class="row pt-3">
                    <div class="col-md-2 align-self-center">
                        <span>1.</span>
                    </div>
                    <div class="col-md-4">

                        <!-- <img src="" alt="image" style="width:200px ;"> -->
                        <?php
                        $sql = "select * from banner_images where id= '5'";
                        $result = $obj->MySQLSelect($sql);
                        if ($result) { ?>
                            <img src="http://52.3.107.59/hurleys_backend/admin/banners/<?php echo $result[0]['image'] ?>" alt="image" style="width:200px ;">
                        <?php } ?>

                    </div>
                    <div class="col-md-4 align-self-center">
                        <button class="btn btn-primary"><a href="update-homepage-banners.php?updateid=5" class="text-decoration-none text-white">Update</a> </button>
                    </div>
                </div>
                
                <div class="row pt-3">
                    <div class="col-md-2 align-self-center">
                        <span>2.</span>
                    </div>
                    <div class="col-md-4 ">
                        <!-- <img src="photo-1503023345310-bd7c1de61c7d.jpg" alt="image" style="max-width:200px ;height:200px"> -->
                        <?php
                        $sql = "select * from banner_images where id= '4'";
                        $result = $obj->MySQLSelect($sql);
                        if ($result) {
                            
                            ?>
                                    <img src="http://52.3.107.59/hurleys_backend/admin/banners/<?php echo $result[0]['image'] ?>" alt="image" style="width:200px ;">
                                
                            <?php } ?>

                    </div>
                    <div class="col-md-4 align-self-center ">
                        <button class="btn btn-primary"><a href="update-homepage-banners.php?updateid=4" class="text-decoration-none text-white">Update</a> </button>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <section class="bottom-section">
        <div class="container pt-4 ">
            <h2>Bottom Banner Images</h2>
            <div class="bottom-images pt-4">
                <div class="row pt-3">
                    <div class="col-md-2 align-self-center">
                        <span>1.</span>
                    </div>
                    <div class="col-md-4">
                        <!-- <img src="istockphoto-1335204730-170667a.jpg" alt="image" style="width:200px ;"> -->
                        <?php
                        $sql = "select * from banner_images where id= '6'";
                        $result = $obj->MySQLSelect($sql);
                        if ($result) { ?>
                            <img src="http://52.3.107.59/hurleys_backend/admin/banners/<?php echo $result[0]['image'] ?>" alt="image" style="width:200px ;">
                        <?php } ?>

                    </div>
                    <div class="col-md-4 align-self-center">
                        <button class="btn btn-primary"><a href="update-homepage-banners.php?updateid=6" class="text-decoration-none text-white">Update</a> </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
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