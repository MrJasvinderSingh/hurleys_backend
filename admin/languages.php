<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$default_lang 	= $generalobj->get_default_lang();

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata,true);

$selectedlanguage = isset($_REQUEST['selectedlanguage'])?stripslashes($_REQUEST['selectedlanguage']):$allservice_cat_data[0]['iServiceId'];
if($selectedlanguage){
	$table_name = 'language_label_'.$selectedlanguage;
}else{
	$table_name = 'language_label';
}
$script = 'language_label';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY vValue ASC';
if($sortby == 1){
  if($order == 0)
  $ord = " ORDER BY vLabel ASC";
  else
  $ord = " ORDER BY vLabel DESC";
}

if($sortby == 2){
  if($order == 0)
  $ord = " ORDER BY vValue ASC";
  else
  $ord = " ORDER BY vValue DESC";
}

/* if($sortby == 3){
  if($order == 0)
  $ord = " ORDER BY vGroup ASC";
  else
  $ord = " ORDER BY vGroup DESC";
}

if($sortby == 4){
  if($order == 0)
  $ord = " ORDER BY eStatus ASC";
  else
  $ord = " ORDER BY eStatus DESC";
} */
//End Sorting

$adm_ssql = "";
if (SITE_TYPE == 'Demo') {
    //$adm_ssql = " And ad.tRegistrationDate > '" . WEEK_DATE . "'";
}

// Start Search Parameters
$option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
$checktext = isset($_REQUEST['checktext'])?stripslashes($_REQUEST['checktext']):"";
//$searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
$default_lang_title = $generalobj->get_default_lang_name();
$hdn_del_id 	= isset($_POST['hdn_del_id'])?$_POST['hdn_del_id']:'';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$page_id = isset($_REQUEST['lp_id']) ? $_REQUEST['lp_id'] : 0;
$pageid = isset($_REQUEST['lp_id'])?stripslashes($_REQUEST['lp_id']):"";
$lp_name = isset($_REQUEST['lp_name']) ? $_REQUEST['lp_name'] : "All ";
$ssql = '';

if($keyword != ''){
    if($option != '') {
        if (strpos($option, 'eStatus') !== false) {
            $ssql.= " AND ".addslashes($option)." LIKE '".addslashes($keyword)."'";
        }else {
            if($checktext == 'Yes' && $option == 'vValue'){
                $ssql.= " AND ".addslashes($option)." LIKE '".addslashes($keyword)."'";
            } else {
                $ssql.= " AND ".addslashes($option)." LIKE '%".addslashes($keyword)."%'";
            }
        }
    }else {
        $ssql.= " AND (vLabel  LIKE '%".addslashes($keyword)."%' OR vValue  LIKE '%".addslashes($keyword)."%') ";
    }
}
// End Search Parameters

if($pageid != "") {
	$ssql.= " AND lPage_id = '".$pageid."'";
}

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$sql="select vTitle from language_master where vCode = '".$default_lang."'";
$lang_title = $obj->MySQLSelect($sql);

$sql = "SELECT COUNT(LanguageLabelId) AS Total FROM ".$table_name." WHERE vCode = '".$default_lang."' and eStatus='Active' $ssql";

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

$sql = "SELECT LanguageLabelId,lPage_id,vCode,vLabel,vValue FROM ".$table_name." WHERE vCode = '".$default_lang."' and eStatus='Active' $ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);

$endRecord = count($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key=>$val) {
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
        <title><?=$SITE_NAME?> | Language Label</title>
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
                                <? if($selectedlanguage == '1'){ 
                                    $name = "Food";
                                } else if($selectedlanguage == '2'){
                                    $name = "Grocery";
                                } else if($selectedlanguage == '3'){
                                    $name = "Wine";
                                }?>
                                <h2>Language Label (<?php echo $name;?>)</h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                            </div>
                        </div>
                        <hr />
				<?php if (SITE_TYPE != 'Demo') { ?>
                <div class="languages-top-part">
					<?php if(!isset($_SESSION['sess_editingToken'])){ ?>
						<h3 class="box_a">For Easy editing click "Enable Online Web Editing"</h3>
					<? } else { ?> 
					<h3 class="box_a">To disable Easy editing click "Disable Online Web Editing"</h3>
					<? } ?>
                      
                       <div class="admin_bax1">
                                <p><?php if(!isset($_SESSION['sess_editingToken'])){ ?>
								<a href="easy_editing_save.php?type=enable&platform=web" class="btn btn-primary">Enable Online Web Editing</a>
                                <?php }else { ?>
                                    <a href="easy_editing_save.php?type=disable&platform=web" class="btn btn-danger">Disable Online Web Editing</a>  <a href="<?php echo $tconfig['tsite_url']; ?>" target="_blank" class="btn btn-primary">View Website</a> 
                                <?php } ?>
                                </p>
                            </div>
                            <!-- div class="col-md-4 admin_bax1">
                            	<p style="margin: 6px 0 10px;">2. <a href="easy_editing_save.php?type=enable&platform=android">Click here to enable for android</a></p>
                            </div>
                            <div class="col-md-4 admin_bax2">
                           		 <p style="margin: 6px 0 10px;">3. <a href="easy_editing_save.php?type=enable&platform=ios">Click here to enable for IOS</a></p>
                            </div>
                            -->
                      </div>
				<?php } ?>
                    <div class="clearfix"></div>
                </div>
                    <?php include('valid_msg.php'); ?>

                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table" >
                              <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="10%" class=" padding-right10"><select name="option" id="option" class="form-control">
                                          <option value="">All</option>
                                          <option value="vLabel" <?php if ($option == "vLabel") { echo "selected"; } ?> >Code</option>
                                          <option value="vValue" <?php if ($option == 'vValue') {echo "selected"; } ?> >Value In <?=$lang_title[0]['vTitle']?> Language</option>
                                         <!-- <option value="ad.eStatus" <?php if ($option == 'ad.eStatus') {echo "selected"; } ?> >Status</option>-->
                                    </select>
                                    </td>
                                    <td width="15%"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="10%" id="exactcheckbox">
                                        <div class="checkbox" style="margin-left:5px;">
                                        <input type="checkbox" name="checktext" value="Yes" id="exactcheckbox_val" <? if($checktext == 'Yes'){echo 'checked';}?> >Exact Value
                                        </div>
                                    </td>
                                    <td>
                                      <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                      <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='languages.php'"/>
                                    </td>
                                    <td width="30%"><a class="add-btn" href="languages_action.php?selectedlanguage=<?=$selectedlanguage?>" style="text-align: center;">Add Label</a></td>
                                </tr>
                              </tbody>
                        </table>
                        
                      </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                    <div class="changeStatus col-lg-12 option-box-left">
                                    <!--  <span class="col-lg-2 new-select001">
                                           <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    
                                                    <option value="Deleted" <?php if ($option == 'Delete') {echo "selected"; } ?> >Make Delete</option>
                                            </select>
                                    </span>-->
                                    <? if(count($allservice_cat_data) > 1){?>
                                    <form method="get" action="" name="mylangform">
                                        <span class="col-lg-2 new-select001">
                                            <select name="selectedlanguage" id="selectedlanguage" class="form-control" onChange="getOtherlangtableData(this.value);">
                                            <? foreach($allservice_cat_data as $langOpt) { ?>
                                                <option value="<?php echo $langOpt['iServiceId']; ?>" <?php if($selectedlanguage == $langOpt['iServiceId']) { echo "selected"; } ?>><?php echo $generalobjAdmin->clearName($langOpt['vServiceName']); ?></option>
                                            <? } ?>
                                            </select>
                                        </span>
                                        <? } else { ?>
                                        <input type="hidden" name="selectedlanguage" id="selectedlanguage" value="<?= $allservice_cat_data[0]['iServiceId'];?>">
                                    </form>
                                    <? } ?>
                                    </div>
                                    <?php  if(!empty($data_drv)) { ?>
                                    <div class="panel-heading">
                                        <form name="_export_form" id="_export_form" method="post" >
                                            <button type="button" onClick="showExportTypes('languages')" >Export</button>
                                        </form>
                                   </div>
                                   <?php } ?>
                                    </div>
                                    <div style="clear:both;"></div>
                                        <div class="table-responsive">
                                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                            <table class="table table-striped table-bordered table-hover dd-tt">
                                                <thead>
                                                    <tr>
                                                       <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>
                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Code <?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)">Value In <?=$lang_title[0]['vTitle']?> Language <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        
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
                                                        <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['vLabel']; ?>" />&nbsp;</td>
                                                        <td><?= $data_drv[$i]['vLabel']; ?></td>
                                                        <td><?= $data_drv[$i]['vValue']; ?></td>
                                                        <td align="center" style="text-align:center;" class="action-btn001">
                                                        <!--  <div class="share-button openHoverAction-class" style="display: block;">
                                                                <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                <div class="social show-moreOptions openPops_<?= $data_drv[$i]['LanguageLabelId']; ?>"> 
                                                                    <ul>
                                                                        <li class="entypo-twitter" data-network="twitter">-->
                                                                            <a href="languages_action.php?id=<?= $data_drv[$i]['LanguageLabelId']; ?>&selectedlanguage=<?=$selectedlanguage?>" data-toggle="tooltip" title="Edit">
                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                        </a><!-- </li>
                                                                        <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                                                                        
                                                                        <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['vLabel']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                            <img src="img/delete-icon.png" alt="Delete" >
                                                                        </a></li>
                                                                        <?php } ?> 
                                                                    </ul>
                                                                </div>
                                                            </div> -->
                                                        </td>
                                                        </tr>
                                                    <?php } } else { ?>
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
                                            Language Label module will list all labels on this page.
                                    </li>
                                    <li>
                                            Administrator can Edit / Delete any language label. 
                                    </li>
                                    <li>
                                            Administrator can export data in XLS or PDF format.
                                    </li>
                                    <!--li>
                                            "Export by Search Data" will export only search result data in XLS or PDF format.
                                    </li-->
                            </ul>
                    </div>
                    </div>
                </div>
                <!--END PAGE CONTENT -->
            </div>
            <!--END MAIN WRAPPER -->
            
<form name="pageForm" id="pageForm" action="action/languages.php" method="post" >
<input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
<input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
<input type="hidden" name="vLabel" id="iMainId01" value="" >
<input type="hidden" name="status" id="status01" value="" >
<input type="hidden" name="statusVal" id="statusVal" value="" >
<input type="hidden" name="option" value="<?php echo $option; ?>" >
<input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
<input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
<input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
<input type="hidden" name="checktext" id="checktext" value="<?php echo $checktext; ?>" >
<input type="hidden" name="selectedlanguage" id="selectedlanguage" value="<?php echo $selectedlanguage; ?>" >
<input type="hidden" name="method" id="method" value="" >
</form>
<?php include_once('footer.php');  ?>
    <script>
    $(document).ready(function() {
        $('#exactcheckbox').hide(); 
        $('#option').each(function(){
          if (this.value == 'vValue') {
              $('#exactcheckbox').show(); 
          }
        });

    });

    $(function() {
        $('#option').change(function(){
          if($('#option').val() == 'vValue') {
              $('#exactcheckbox').show();
          } else {
              $('#exactcheckbox').hide();
              $("#exactcheckbox_val").val("");
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
       // alert(action);
        var formValus = $("#frmsearch").serialize();

        var selectedlanguage = "<? echo $selectedlanguage?>";
    // alert(action+formValus);
        window.location.href = action+"?"+formValus+"&selectedlanguage="+selectedlanguage;
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
    $(document).ready(function(){
        $('#selectedlanguage').change(function(){
            mylangform.submit();
        });
    });
    </script>
</body>
<!-- END BODY-->
</html>