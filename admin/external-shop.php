<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
  require_once(TPATH_CLASS . "class.general_admin.php");
  $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

// For Languages
//$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
//$db_master = $obj->MySQLSelect($sql);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$action = ($id != '') ? 'Update' : 'Add';
$msg = '';
$shop_data = '';
if(isset($id)){
    $get_sql = "SELECT * FROM `external_shops` where id='".$id."' ";
    $shop_data = $obj->MySQLSelect($get_sql);
}

if (isset($_POST['submit'])) {
    $es_name   = isset($_POST['es_name'])?$_POST['es_name']:'';
    $es_url   = isset($_POST['es_url'])?$_POST['es_url']:'';
    if(isset($_FILES['es_image'])){
        $errors= array();
        $file_name = $_FILES['es_image']['name'];
        $file_size =$_FILES['es_image']['size'];
        $file_tmp =$_FILES['es_image']['tmp_name'];
        $file_type=$_FILES['es_image']['type'];
        $file_ext=strtolower(end(explode('.',$_FILES['es_image']['name'])));
        
        $extensions= array("jpeg","jpg","png");
        
        if(in_array($file_ext,$extensions)=== false){
            $errors[]="Extension not allowed, please choose a JPEG or PNG file.";
        }
        
        if($file_size > 2097152){
            $errors[]='File size should be less than 2 MB';
        }
        
        if(empty($errors)==true){
            move_uploaded_file($file_tmp,"images/external-shops/".$file_name);
            $es_image = $file_name;
        }else{
            //print_r($errors);
        }
    }

    if($action == 'Add') {
        $query = "INSERT INTO external_shops(name,url,image) VALUES ( '".$es_name."', '".$es_url."', '".$es_image."' )";
    } else if($action == 'Update') {
        if(!empty($_FILES['es_image']['name'])){
            $query = "UPDATE external_shops SET name = '".$es_name."', url = '".$es_url."', image = '".$es_image."' WHERE id = '".$id."'";
        } else {
            $query = "UPDATE external_shops SET name = '".$es_name."', url = '".$es_url."' WHERE id = '".$id."'";
        }
        $get_sql2 = "SELECT * FROM `external_shops` where id='".$id."' ";
        $shop_data = $obj->MySQLSelect($get_sql2);
        $shop_data[0]['name'] = $es_name;
        $shop_data[0]['url'] = $es_url;
    }
    //echo $query;
    $result = $obj->sql_query($query);
    if($result == 1) {
        $msg = 'Data Saved!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title><?=$SITE_NAME?> | External Shop  <?= $action; ?></title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<? include_once('global_files.php'); ?>
<!-- On OFF switch -->
<link href="../assets/css/jquery-ui.css" rel="stylesheet" />
<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
</head>
<!-- END  HEAD-->
 <!-- BEGIN BODY-->
 <body class="padTop53 " >
    <!-- MAIN WRAPPER -->
    <div id="wrap">
     <?
     include_once('header.php');
     include_once('left_menu.php');
     ?>
     <!--PAGE CONTENT -->
     <div id="content">
          <div class="inner">
              <?php if(!empty($msg)){
                  echo '<div class="row">
                            <div class="col-lg-12">
                                <p class="text-success">'.$msg.'</p>
                            </div>
                        </div>';
              } ?>
             <div class="row">
                  <div class="col-lg-12">
                       <h2><?= $action; ?> External Shop</h2>
                       <a href="external-shops.php" class="back_link">
                            <input type="button" value="Back to Listing" class="add-btn">
                       </a>
                  </div>
             </div>
             <hr />
             <div class="body-div">
                  <div class="form-group">
                        <form id="food_category_form" name="food_category_form" method="post" action="" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="es_id" value="<?= $id; ?>"/>
                            <input type="hidden" name="backlink" id="backlink" value="external-shops.php"/>
							
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Name</label>
                                    <input type="text" class="form-control" name="es_name" id="es_name" value="<?php if(!empty($shop_data)) {echo $shop_data[0]['name']; } ?>" required> 
                                </div>
                            </div>
							
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>URL</label>
                                    <input type="text" class="form-control" name="es_url" id="es_url" value="<?php if(!empty($shop_data)) {echo $shop_data[0]['url']; } ?>" required> 
                                </div>
                            </div>
							
                            <div class="row">
                                <div class="col-lg-12">
                                <img src="<? if(!empty($shop_data[0]['image'])) { echo $tconfig["tsite_url"].$generalobjAdmin->clearCmpName('hurleys_backend/admin/images/external-shops/'.$shop_data[0]['image']); } else { echo 'http://52.3.107.59/hurleys_backend/admin/img/1.png'; } ?>" width="150px;"/>
								<br>
                                    <label>Image</label>
                                    <input type="file" class="form-control" name="es_image" id="es_image" value="" <?php if($action == 'Add'){ echo 'required'; } ?>> 
                                </div>
                            </div>

                            <div class="row">
                              <div class="col-lg-12">
                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> External Shop" >
                                <input type="reset" value="Reset" class="btn btn-default">
                                <a href="external-shops.php" class="btn btn-default back_link">Cancel</a>
                              </div>
                            </div>

                        </form>
                  </div>
             </div>
        </div>
   </div>
   <!--END PAGE CONTENT -->
    </div>
   <!--END MAIN WRAPPER -->
  <?php include_once('footer.php'); ?>
  <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
  <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
  <script>
      
    
  </script>
</body>
<!-- END BODY-->
</html>
