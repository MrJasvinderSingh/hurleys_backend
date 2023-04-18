<?php
include_once('../common.php');

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
        <title><?=$SITE_NAME?> |  External Shops</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php');?>
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
                                <h2>External Shops</h2>
                            </div>
                        </div>
                       <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <a class="add-btn" href="external-shop.php" style="text-align: center;">Add Shop</a>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                     <?php if(!empty($data_drv)) {?>
                                    <!--<div class="panel-heading">
                                        <form name="_export_form" id="_export_form" method="post" >
                                            <button type="button" onClick="showExportTypes('FoodMenu')" >Export</button>
                                        </form>
                                   </div>-->
                                   <?php } ?>
                                    </div>
                            <div style="clear:both;"></div>
                            <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                            <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                            <!-- <th width="3%" class="align-center"><input type="checkbox" id="setAllCheck" ></th> -->
                            <th width="13%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Name <?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>


                            <th width="18%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)">URL <?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
<th>Image</th>
														<th width="8%" class="align-center">Action</th>
                              </tr>
                          </thead>
                          <tbody>
                          <? 
                          if(!empty($data_drv)) {
													for ($i = 0; $i < count($data_drv); $i++) { 
													?>
														<tr class="gradeA" >
															<td><?= $generalobjAdmin->clearCmpName($data_drv[$i]['name']); ?></td>
                                                            <td><a href="<?= $generalobjAdmin->clearCmpName($data_drv[$i]['url']); ?>" target="_blank"><?= $generalobjAdmin->clearCmpName($data_drv[$i]['url']); ?></td>
                                                            <td><img src="<? if(!empty($data_drv[$i]['image'])) { echo $tconfig["tsite_url"].$generalobjAdmin->clearCmpName('hurleys_backend/admin/images/external-shops/'.$data_drv[$i]['image']); } else { echo 'http://52.3.107.59/hurleys_backend/admin/img/1.png'; } ?>" width="50px;"/></td>
								
															
															<td align="center" class="action-btn001">
                                <div class="share-button openHoverAction-class" style="display: block;">
                                <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                <div class="social show-moreOptions for-five openPops_<?= $data_drv[$i]['id']; ?>">
                                    <ul>
                                        <li class="entypo-twitter" data-network="twitter"><a href="external-shop.php?id=<?=$data_drv[$i]['id']; ?>" data-toggle="tooltip" title="Edit">
                                            <img src="img/edit-icon.png" alt="Edit">
                                        </a></li>
                                       
                                        <?php if($eStatus != 'Deleted') { ?>
                                        <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDeleteExternalShop('<?php echo $data_drv[$i]['id']; ?>')"  data-toggle="tooltip" title="Delete">
                                            <img src="img/delete-icon.png" alt="Delete" >
                                        </a></li>
                                        <?php } ?>
                                      <?php  if (SITE_TYPE == 'Demo') { ?>
                                      <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="resetTripStatus('<?php echo $data_drv[$i]['id']; ?>')"  data-toggle="tooltip" title="Reset">
                                                  <img src="img/reset-icon.png" alt="Reset">
                                              </a></li>
                                      <?php }

                                      } ?>
                                    </ul>
                                </div>
                                </div>
                              </td>
															
														</tr>
													<?php } else { ?>
                            <tr class="gradeA">
                                <td colspan="12"> No Records Found.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </form>
                <?php include('pagination_n.php'); ?>
                        </div>
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