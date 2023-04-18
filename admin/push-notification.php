<?php
include_once('../common.php');
//echo time();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$msg = '';
if(!empty($id)){
    $del_sql = "DELETE FROM `external_shops` WHERE `id` = '".$id."' ";
    $shop_data = $obj->MySQLSelect($del_sql);
    
    $msg = 'Shop deleted successfully.';
}

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$script = 'FoodMenu';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY f.id DESC';
if($sortby == 1){
  if($order == 0)
  $ord = " ORDER BY f.vMenu_".$default_lang." ASC";
  else
  $ord = " ORDER BY f.vMenu_".$default_lang." DESC";
}
if($sortby == 2){
  if($order == 0)
  $ord = " ORDER BY c.vCompany ASC";
  else
  $ord = " ORDER BY c.vCompany DESC";
}
if($sortby == 3){
  if($order == 0)
  $ord = " ORDER BY f.iDisplayOrder ASC";
  else
  $ord = " ORDER BY f.iDisplayOrder DESC";
}

if($sortby == 4){
  if($order == 0)
  $ord = " ORDER BY MenuItems ASC";
  else
  $ord = " ORDER BY MenuItems DESC";
}

if($sortby == 5){
  if($order == 0)
  $ord = " ORDER BY f.eStatus ASC";
  else
  $ord = " ORDER BY f.eStatus DESC";
}

//End Sorting

// Start Search Parameters
$option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
$searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');

$ssql = '';
if($keyword != '') {
    if($option != '') {
        $option_new = $option;
        if($eStatus != ''){
            $ssql.= " AND ".stripslashes($option_new)." LIKE '%".$generalobjAdmin->clean($keyword)."%' AND f.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
		    } else {
            $ssql.= " AND ".stripslashes($option_new)." LIKE '%".$generalobjAdmin->clean($keyword)."%'";
		    }
    } else {
      if($eStatus != ''){
        $ssql.= " AND (c.vCompany LIKE '%".$generalobjAdmin->clean($keyword)."%' OR f.vMenu_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%') AND f.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
      } else {
        $ssql.= " AND (c.vCompany LIKE '%".$generalobjAdmin->clean($keyword)."%' OR f.vMenu_".$default_lang." LIKE '%".$generalobjAdmin->clean($keyword)."%')";
      }
	}
} else if($eStatus != '' && $keyword == '') {
     $ssql.= " AND f.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
}


// End Search Parameters

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if($eStatus != ''){
  $eStatussql = "";
} else {
 $eStatussql = " AND f.eStatus != 'Deleted'";
}

$sql = "SELECT COUNT(f.id) AS Total FROM food_menu as f LEFT JOIN company c ON f.iCompanyId = c.iCompanyId WHERE 1 = 1  $eStatussql $ssql $dri_ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page);//total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        $start = 0;
        $end = $per_page;
    }
} else {
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;

if(!empty($eStatus)) { 
	$eQuery = "";
} else {
  $eQuery = " AND f.eStatus != 'Deleted'";
}
    
$sql = "SELECT * from external_shops ORDER BY id DESC";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = count($data_drv);

$var_filter = "";
foreach ($_REQUEST as $key=>$val)
{
    if($key != "tpages" && $key != 'page')
    $var_filter.= "&$key=".stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;
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
            

            .modal {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1050;
                display: none;
                width: 100%;
                height: 100%;
                overflow: hidden;
                outline: 0;
                background-color: rgb(0, 0, 0);
                background-color: rgba(0, 0, 0, 0.4);

            }

            .modal-content {
                position: relative;
                display: -ms-flexbox;

                -ms-flex-direction: column;
                flex-direction: column;

                pointer-events: auto;
                background-color: #fffbfb;
                background-clip: padding-box;
                border: 1pxsolidrgba(0, 0, 0, .2);
                border-radius: 0.3rem;
                outline: 0;
                margin-left: 25%;
                width:50%
                /*max-width: min-content;*/
            }

            .modal-header {
                display: -ms-flexbox;
                display: flex;
                -ms-flex-align: start;
                align-items: flex-start;
                -ms-flex-pack: justify;
                justify-content: center;
                padding: 1rem 1rem;
                border-bottom: 1px solid #dee2e6;
                border-top-left-radius: calc(0.3rem - 1px);
                border-top-right-radius: calc(0.3rem - 1px);
            }
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
                                <h2>Send push notification</h2>
                            </div>
                        </div>
                       <hr />
                    </div>
                    <div class="table-list">
                        <div class="row">
                            <!-- start push   -->
                            <div class="container">
                                <form action="http://52.3.107.59/hurleys_backend/admin/push-notification.php" method="post" id="search" >
                                    <label for="status">City:</label>
                                    <select name="eStatus" id="status">
                                        <option value="">Select city</option>
                                        <?php
                                        $sql_city = "SELECT DISTINCT user_city FROM `register_user` WHERE user_city != '' AND fcm_token != ''";
                                        $result_city = $obj->MySQLSelect($sql_city); echo '<pre>'; print_r($result2); echo '</pre>';
                                        foreach ($result_city as $result_city_each) { ?>
                                            <option <?php if (isset($_POST['eStatus']) && $_POST['eStatus'] == $result_city_each['user_city'])  echo "selected = 'selected'"; ?>><?php echo $result_city_each['user_city']; ?></option>
                                        <?php } ?>
                                    </select>

                                    <label for="gender">Gender:</label>
                                    <select name="eGender" id="gender">
                                        <option value="">Select Gender</option>
                                        <option <?php if (isset($_POST['eGender']) && $_POST['eGender'] == 'Male')  echo "selected = 'selected'"; ?>>Male</option>
                                        <option <?php if (isset($_POST['eGender']) && $_POST['eGender'] == 'Female')  echo "selected = 'selected'"; ?>>Female</option>
                                    </select>

                                    <input type="submit" name="Search" value="Search" id="submit" required>
                                </form>

                                <section style="margin-top: 15px;">
                                <table class="table" id="mytable">

                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>SELECT</th>
                                            <th>FIRSTNAME</th>
                                            <th>LASTNAME</th>
                                            <th>EMAIL</th>
                                            <th>PHONE</th>
                                            <th>STATUS</th>
                                            <th>GENDER</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        //include("db.php");
                                        $cond = "fcm_token != ''";
                                        /*if (isset($_POST['eStatus']) && !empty($_POST['eStatus']) && empty($_POST['eGender'])) {
                                            $cond = " AND eStatus = '" . $_POST['eStatus']."'";
                                        } else if (isset($_POST['eGender']) && !empty($_POST['eGender']) && empty($_POST['eStatus'])) {
                                            $eGender = $_POST['eGender'];
                                            $cond = " AND eGender = '" . $eGender . "'";
                                        } else if (isset($_POST['eStatus'])  && !empty($_POST['eStatus']) && isset($_POST['eGender']) && !empty($_POST['eGender'])) {
                                            $eGender = $_POST['eGender'];
                                            $cond = " AND eStatus = " . $_POST['eStatus'] . " AND eGender = '" . $eGender . "'";
                                        }*/
                                        if (isset($_POST['eStatus']) && !empty($_POST['eStatus']) && empty($_POST['eGender'])) {
                                            $cond = " AND user_city = '" . $_POST['eStatus']."'";
                                        } else if (isset($_POST['eGender']) && !empty($_POST['eGender']) && empty($_POST['eStatus'])) {
                                            $eGender = $_POST['eGender'];
                                            $cond = " AND eGender = '" . $eGender . "'";
                                        } else if (isset($_POST['eStatus'])  && !empty($_POST['eStatus']) && isset($_POST['eGender']) && !empty($_POST['eGender'])) {
                                            $eGender = $_POST['eGender'];
                                            $cond = " AND user_city = " . $_POST['eStatus'] . " AND eGender = '" . $eGender . "'";
                                        }
                                        $sql = "select * from register_user WHERE " . $cond;
                                         //echo $sql;
                                        $result = $obj->MySQLSelect($sql);
                                        foreach ($result as $result_each) {
                                            echo '<tr>
                                            <td>' . $result_each["iUserId"] . '</td>
                                            <td><input type="checkbox" id="select" name="select" /></td>
                                            <td class="name" data-userid = "' . $result_each["iUserId"] . '" data-id ="' . $result_each["fcm_token"] . '">' . $result_each["vName"] . '</td>
                                            <td>' . $result_each["vLastName"] . '</td>
                                            <td>' . $result_each["vEmail"] . '</td>
                                            <td class="phone">' . $result_each["vPhone"] . '</td>
                                            <td>' . $result_each["eStatus"] . '</td>
                                            <td>' . $result_each["eGender"] . '</td>
                                            </tr>';
                                        }
                                        ?>

                                    </tbody>

                                </table>
                            </section>
                            

                            </div>
                            <div class="container">
                                <div style="margin-top:10px;">
                                    <button class="btn btn-primary" id="btn" style="float:right;">Send Notification</button>
                                </div>
                            </div>
                            <div id="dialog" class="modal">
                                <div class="modal-content animate-top" >
                                    <div class="modal-header">
                                        <h5>Notification Details</h5>
                                    </div>
                                    <div class="modal-body">
                                        <p>Please enter below details.</p>
                                        <form action="" method="post" id="form" name="form">
                                            <div class="form-group">
                                                <label for="title">Title</label>
                                                <input type="text" class="form-control" id="title" name="title">
                                            </div>
                                            <div class="form-group">
                                                <label for="message">Body</label>
                                                <textarea class="form-control" type="comment" id="comment" name="comment"></textarea>
                                            </div>
                                            <input type="button" id="submitted" value="Send" class="btn btn-primary">
                                                
                                        </form>

                                        <div class="selected-users" style="margin-top:15px;"></div>
                                    </div>

                                </div>
                            </div>
                            <!-- end push     -->
                    </div> <!--TABLE-END-->
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
            <!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/food_menu.php" method="post" >
<input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
<input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
<input type="hidden" name="id" id="iMainId01" value="" >
<input type="hidden" name="iCompanyId" id="iCompanyId" value="<?php echo $iCompanyId; ?>" >
<input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
<input type="hidden" name="status" id="status01" value="" >
<input type="hidden" name="statusVal" id="statusVal" value="" >
<input type="hidden" name="option" value="<?php echo $option; ?>" >
<input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
<input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
<input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
<input type="hidden" name="method" id="method" value="" >
</form>
<?php include_once('footer.php'); ?>
<link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" />
   
    <script src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#mytable').DataTable();
        });
    </script>
    <script>
        var modal = $('#dialog');
        var btn = $("#btn");
        // var span = $(".close");
        $(document).ready(function() {
            btn.on('click', function() {
                var selected_users = '';
                // var selected_userss = '';
                var ifSelected = false;
                $("#mytable > tbody  > tr > td > input").each(function(e, data) {
                    if (data.checked) {
                        ifSelected = true;
                        if($(this).parent().parent().find(".phone").text() != ''){
                            $phone = $(this).parent().parent().find(".phone").text();
                        } else {
                            $phone = '--';
                        }
                        selected_users += 'Name: ' + $(this).parent().parent().find(".name").text() + ' ('+$phone+')<br>';
                        //selected_userss += 'Phone: ' + $(this).parent().parent().find(".phone").text() + '<br>';

                    }
                });
                if (ifSelected) {
                    $('.selected-users').html('<b>Selected Users:</b><br/>' + selected_users );
                    modal.show();
                } else {
                    alert('Please select users.');
                }
            });

        });
        $('body').bind('click', function(e) {
            if ($(e.target).hasClass("modal")) {
                modal.hide();
            }
        })
    </script>
    <script>
        $(document).ready(function() {
            $("#submitted").click(function() {
                var fcm = [];
                $("#mytable > tbody  > tr > td > input").each(function(e, data) {
                    if (data.checked) {
                        
                        fcm.push($(this).parent().parent().find(".name").data('id'));
                    }
                });
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "https://fcm.googleapis.com/fcm/send");

                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Content-Type", "application/json");

                xhr.onload = () => console.log(xhr.responseText);
                var data_fcm = {};
                data_fcm = {
                    "to":fcm,
                    notification:{
                     "body":$("#comment").val(),
                     "title": $("#title").val(),
                      }
                    };
                xhr.send(data_fcm);
                var title = $('input[name = title]').val();
                var comment =$("#comment").val();
                var userid = [];
                $('#mytable tr td input').each(function(e,data){
                    if(data.checked){
                        userid.push($(this).parent().parent().find(".name").data('userid'));
                    }
                });
                if(title != '' && comment != ''){
                    var myform = {
                        title:title, comment:comment, userid:userid.join(',')
                    };
                    console.log('myform'); console.log(myform);
                    $.ajax({
                        url: "sent.php",
                        type: 'POST',
                        data: myform,
                        success: function(response){
                            console.log(response);
                            alert('success');
                            window.location.href='http://localhost/hurleys_backend/admin/push-notification.php';
                        }
                    });
                }else{
                    alert('please enter fields');
                }
            });
        })
    </script>

<script>
/*
    $("#setAllCheck").on('click',function(){
        if($(this).prop("checked")) {
            jQuery("#_list_form input[type=checkbox]").each(function() {
                if($(this).attr('disabled') != 'disabled'){
                    this.checked = 'true';
                }
            });
        }else {
            jQuery("#_list_form input[type=checkbox]").each(function() {
                this.checked = '';
            });
        }
    });*/
    
    
	function changeStatusDeleteExternalShop(id) {
		$('#is_dltSngl_modal').modal('show');
		$(".action_modal_submit").unbind().click(function () {
           window.location.href = document.URL+"?id="+id;
		});
	}
    
    $("#Search").on('click', function(){
        var action = $("#_list_form").attr('action');
        var formValus = $("#frmsearch").serialize();
        window.location.href = action+"?"+formValus;
    });
    
    $('.entypo-export').click(function(e){
         e.stopPropagation();
         var $this = $(this).parent().find('div');
         $(".openHoverAction-class div").not($this).removeClass('active');
         $this.toggleClass('active');
    });
    
    $(document).on("click", function(e) {
        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
          $(".show-moreOptions").removeClass("active");
        }
    });
    
</script>
</body>
<!-- END BODY-->
</html>