<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$script = 'Militarylocations';
$tbl_name = 'militarylocations';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY id ASC';
if($sortby == 1){
  if($order == 0)
  $ord = " ORDER BY base ASC";
  else
  $ord = " ORDER BY base DESC";
}

if($sortby == 2){
  if($order == 0)
  $ord = " ORDER BY region ASC";
  else
  $ord = " ORDER BY region DESC";
}

if($sortby == 3){
  if($order == 0)
  $ord = " ORDER BY building ASC";
  else
  $ord = " ORDER BY building DESC";
}

if($sortby == 4){
  if($order == 0)
  $ord = " ORDER BY latitude ASC";
  else
  $ord = " ORDER BY latitude DESC";
}

if($sortby == 5){
  if($order == 0)
  $ord = " ORDER BY longitude ASC";
  else
  $ord = " ORDER BY longitude DESC";
}
//End Sorting


// Start Search Parameters
$option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
$searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

$ssql = '';
if($keyword != ''){
    if($option != '') {
        if($eStatus != ''){
            $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%' AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
        }else {
            $ssql.= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%'";
        }
    }else {
        if($eStatus != ''){
            $ssql.= " AND (base LIKE '%".$keyword."%' OR region LIKE '%".$keyword."%' OR building LIKE '%".$keyword."%') AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
        } else {
            $ssql.= " AND (base LIKE '%".$keyword."%' OR region LIKE '%".$keyword."%' OR building LIKE '%".$keyword."%')";
        }
    }
} else if($eStatus != '' && $keyword == '') {
     $ssql.= " AND eStatus = '".$generalobjAdmin->clean($eStatus)."'";
}
// End Search Parameters

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if(!empty($eStatus)) { 
    $estatusquery = "";
} else {
    $estatusquery = " AND eStatus != 'Deleted'";
}
$sql = "SELECT COUNT(id) AS Total FROM militarylocations WHERE 1=1 $estatusquery $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
if(!empty($eStatus)) { 
    $esql = "";
} else {
    $esql = " AND eStatus != 'Deleted'";
}
$sql = "SELECT * FROM ".$tbl_name."  WHERE 1=1 $esql $ssql $ord LIMIT $start, $per_page ";
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
        <title><?=$SITE_NAME?> | Country</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php');?>
    </head>
    <!-- END  HEAD-->
    
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>

            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2><?php echo "Military Locations";?></h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                                
                            </div>
                        </div>
                        <div class="row clearfix">
                            <div class="col-lg-12">
                                <hr />
                                <div id="response" class="text-center "></div>
                            <form class="form-horizontal" action="action/importmillitarylocation.php" method="post" name="uploadCSV"  enctype="multipart/form-data" id="frmCSVImport">
                                <div class="input-row">
                                    <div class="col-md-6">
                                    <label class="control-label">Choose CSV File to import data</label> 
                                    <input type="file" name="file" id="file" accept=".csv"  />
                                    </div>
                                    <div class="col-md-6">
                                    <button type="submit" id="submit" name="import"
                                        class="btn btn-submit">Import</button>
                                    </div>
                                    

                                </div>
                                <div id="labelError"></div>
                            </form>
                            </div>
                        </div>
                        <hr />
                    </div>
                    
                   
                    
                    
                    
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                              <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="10%" class=" padding-right10"><select name="option" id="option" class="form-control">
                                          <option value="">All</option>
                                          <option  value="base" <?php if ($option == "base") { echo "selected"; } ?> >Base</option>
                                          <option value="region" <?php if ($option == 'region') {echo "selected"; } ?> >Region</option>
                                          <option value="building" <?php if ($option == 'building') {echo "selected"; } ?> >Building</option>
                                          <!-- <option value="eStatus" <?php if ($option == 'eStatus') {echo "selected"; } ?> >Status</option> -->
                                    </select>
                                    </td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eStatus" id="estatus_value" class="form-control">
                                            <option value="" >Select Status</option>
                                            <option value='Active' <?php if ($eStatus == 'Active') { echo "selected"; } ?> >Active</option>
                                            <option value="Inactive" <?php if ($eStatus == 'Inactive') {echo "selected"; } ?> >Inactive</option>
                                            <option value="Deleted" <?php if ($eStatus == 'Deleted') {echo "selected"; } ?> >Delete</option>
                                        </select>
                                    </td>
                                    <td>
                                      <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                      <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='militarylocations.php'"/>
                                    </td>
<!--                                    <td width="30%"><a class="add-btn" href="#" style="text-align: center;">Add Military Location</a></td>-->
                                </tr>
                              </tbody>
                        </table>
                        
                      </form>
                    
                    
                    
                    
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                    <div class="changeStatus col-lg-12 option-box-left">
                                    <span class="col-lg-2 new-select001">
                                            <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <option value='Active' <?php if ($option == 'Active') { echo "selected"; } ?> >Make Active</option>
                                                    <option value="Inactive" <?php if ($option == 'Inactive') {echo "selected"; } ?> >Make Inactive</option>
                                                    <?php if($eStatus != 'Deleted') { ?>
                                                    <option value="Deleted" <?php if ($option == 'Delete') {echo "selected"; } ?> >Make Delete</option>
                                                    <?php } ?>
                                            </select>
                                    </span>
                                    </div>
                                    <?php //if(!empty($data_drv)) {?>
<!--                                    <div class="panel-heading">
                                        <form name="_export_form" id="_export_form" method="post" >
                                            <button type="button" onclick="showExportTypes('militarylocations')" >Export</button>
                                        </form>
                                   </div>-->
                                   <?php //} ?>


                                    </div>
                                    <div style="clear:both;"></div>
                                        <div class="table-responsive">
                                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>
                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Base <?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)">Region <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        
														<th width="20%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)">Building <?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        
                                                        <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php if($sortby == '4'){ echo $order; }else { ?>0<?php } ?>)">Latitude <?php if ($sortby == 4) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php if($sortby == '5'){ echo $order; }else { ?>0<?php } ?>)">Longitude <?php if ($sortby == 5) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="8%" align="center" style="text-align:center;">Status</th>
                                                        <th width="8%" align="center" style="text-align:center;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    if(!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) { 
                                                        
                                                        $default = '';
                                                        if($data_drv[$i]['eDefault']=='Yes'){
                                                                $default = 'disabled';
                                                        } ?>
                                                    <tr class="gradeA">
                                                        <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['id']; ?>" />&nbsp;</td>
                                                        <td><?= $data_drv[$i]['base']; ?></td>
                                                        <td><?= $data_drv[$i]['region']; ?></td>
                                                        <td><?= $data_drv[$i]['building']; ?></td>
                                                        <td><?= $data_drv[$i]['latitude']; ?></td>
                                                        <td><?= $data_drv[$i]['longitude']; ?></td>
                                                         <td align="center" style="text-align:center;">
                                                                <?php if($data_drv[$i]['eStatus'] == 'Active') {
                                                                $dis_img = "img/active-icon.png";
                                                                }else if($data_drv[$i]['eStatus'] == 'Inactive'){
                                                                $dis_img = "img/inactive-icon.png";
                                                                }else if($data_drv[$i]['eStatus'] == 'Deleted'){
                                                                $dis_img = "img/delete-icon.png";
                                                                }?>
                                                                <img src="<?= $dis_img; ?>" alt="<?=$data_drv[$i]['eStatus'];?>" data-toggle="tooltip" title="<?=$data_drv[$i]['eStatus'];?>">
                                                            </td>
                                                             
                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iLocationId']; ?>">
                                                                        <ul>
<!--                                                                            <li class="entypo-twitter" data-network="twitter"><a href="#id=<?= $data_drv[$i]['id']; ?>" data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a></li>-->
                                                                            <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                                                                            <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['id']; ?>','Inactive')"  data-toggle="tooltip" title="Make Active">
                                                                                <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                            </a></li>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['id']; ?>','Active')" data-toggle="tooltip" title="Make Inactive">
                                                                                <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >    
                                                                            </a></li>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $data_drv[$i]['id']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                                <img src="img/delete-icon.png" alt="Delete" >
                                                                            </a></li>
                                                                            <?php } ?>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php } }else { ?>
                                                        <tr class="gradeA">
                                                            <td colspan="7"> No Records Found.</td>
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
                    <div class="admin-notes">
                            <h4>Notes:</h4>
                            <ul>
                                    <li>
                                            Military module will list all military locations on this page.
                                    </li>
                                    <li>
                                            Administrator can Activate / Deactivate / Delete any military locations. 
                                    </li>
                                    <li>
                                            Administrator can export data in XLS or PDF format.
                                    </li>
                            </ul>
                    </div>
                    </div>
                </div>
                <!--END PAGE CONTENT -->
            </div>
            <!--END MAIN WRAPPER -->
            
<form name="pageForm" id="pageForm" action="action/militarylocations.php" method="post" >
<input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
<input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
<input type="hidden" name="iLocationId" id="iMainId01" value="" >
<input type="hidden" name="status" id="status01" value="" >
<input type="hidden" name="statusVal" id="statusVal" value="" >
<input type="hidden" name="option" value="<?php echo $option; ?>" >
<input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
<input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
<input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
<input type="hidden" name="method" id="method" value="" >
</form>
<?php
include_once('footer.php');
?>
<script>
$(document).ready(
	function() {
		$("#frmCSVImport").on(
		"submit",
		function() {

			$("#response").attr("class", "");
			$("#response").html("");
			var fileType = ".csv";
			var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+("
					+ fileType + ")$");
			if (!regex.test($("#file").val().toLowerCase())) {
				$("#response").addClass("error");
				$("#response").addClass("display-block");
				$("#response").html(
						"Invalid File. Upload : <b>" + fileType
								+ "</b> Files.");
				return false;
			}
			return true;
		});
	});    
    
$(document).ready(function() {
    $('#eStatus_options').hide(); 
    $('#option').each(function(){
        if (this.value == 'eStatus') {
            $('#eStatus_options').show(); 
            $('.searchform').hide(); 
        }
    });
});
$(function() {
    $('#option').change(function(){
        if($('#option').val() == 'eStatus') {
            $('#eStatus_options').show();
            $("input[name=keyword]").val("");
            $('.searchform').hide(); 
        } else {
            $('#eStatus_options').hide();
            $("#estatus_value").val("");
            $('.searchform').show();
        } 
    });
});

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
    });
    
    $("#Search").on('click', function(){
        //$('html').addClass('loading');
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