<?
 ob_start();
	include_once('../common.php');
	if(!isset($generalobjAdmin))
	{
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();
  
	$success=$_REQUEST['success'];
	$sql = "SELECT * FROM custom_notifications WHERE Status = 'Active' order by id";
	$db_customnotif = $obj->MySQLSelect($sql);
	$script = 'Custom Notification';
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
<meta charset="UTF-8" />
<title>
<?=$SITE_NAME;?>
| Custom Notification</title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
<? include_once('global_files.php');?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- MAIN WRAPPER -->
<div id="wrap">
  <? include_once('header.php'); ?>
  <? include_once('left_menu.php'); ?>
  <!--PAGE CONTENT -->
  <div id="content">
    <div class="inner">
      <div id="add-hide-show-div">
        <div class="row">
          <div class="col-lg-12">
            <h2>Custom Notification</h2>
            <a href="javascript:void(0)" id="append_custom_notif" class="add-btn">Add Custom Notification</a>
          </div>
        </div>
        <hr />
      </div>
      <div style="clear:both;"></div>
      <? if ($success == 1) {?>
      <div class="alert alert-success alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        Custom Notification Updated successfully. </div>
      <br/>
      <?}
					else if($success == 2)
					{
					?>
      <div class="alert alert-danger alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you. </div>
      <br/>
      <?
						}
					?>
		<div class="alert alert-danger alert-dismissable" style='display:none;'>
			<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
			Please fill all fields!
		</div>
		<br/>
		<div class="alert alert-success alert-dismissable" style='display:none;'>
			<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
			<p></p>
		</div>
		<br/>

      <div class="table-list">
        <div class="row">
          <div class="col-lg-12">
            <div class="table-responsive">
              <form action="custom_notification_action.php" method="post" id="formId">
                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                  <thead>
                    <tr>
                      <th>Message</th>
                      <th>Latitude</th>
                      <th>Longitude</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?  
					if(count($db_customnotif) > 0){
						$i = 0;
						foreach ($db_customnotif as $key => $value) {
								echo '<tr>
										<td><input class="form-control" type="text" name="message" value="'.$value['message'].'" required/><input type="hidden" class="customnotif_id" name="id" value="'.$value['id'].'"/></td>
										<td><input class="form-control" name="lat" id="lat_'.$value['id'].'" type="text" value='.$value['lat'].' required/></td>
										<td><input class="form-control" name="lon" id="lon_'.$value['id'].'" type="text" value='.$value['lon'].' required/></td>
										<td><a href="javascript:void(0)" class="removetr">Remove</a>&nbsp; | &nbsp;<a href="javascript:void(0)" class="addcustom">Update</a></td>';
																				
								echo '</tr>';
								$i++;
							}
					}
												?>
                  </tbody>
				  <tfoot>
					<tr>
					  <!--<td colspan="5" align="center"><input type="submit" name="btnSubmit" class="btn btn-default" value="Edit Custom Notification"></td>-->
					</tr>
					</tfoot>
                </table>
              </form>
            </div>
          </div>
          <!--TABLE-END-->
        </div>
      </div>
    </div>
  </div>
  <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<?
			include_once('footer.php');
		?>
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<!--<script>
			$(document).ready(function () {
				$('#dataTables-example').dataTable();
			});
		</script>-->
    <script type="text/javascript">
      $("form").submit(function(event){
         event.preventDefault();
          var value = $( 'input[name=eDefault]:checked' ).val();
          var ratio = $('#ratio_'+ value).val();
          //if(ratio != 1){
            $('#formId').get(0).submit();
          //} else {
            //alert("Please change euro currency ratio to 1.0000 since your making it as default. Also adjust other currency ratio as per euro.");
            //return false;
         // }
      });
	  $('#append_custom_notif').on('click',function(){
		  $('#dataTables-example').find('tbody').append('<tr><td><input class="form-control" type="text" name="message" value="" required /><input type="hidden" class="customnotif_id" name="id" value=""/></td><td><input class="form-control" name="lat" type="text" value="" required/></td><td><input class="form-control" name="lon" type="text" value="" required/></td><td><a href="javascript:void(0)" class="removetr">Remove</a>&nbsp; | &nbsp;<a href="javascript:void(0)" class="addcustom">Add</a></td></tr>');
	  });
	$(document).on('click','.removetr',function(){
		var maintr = $(this).parents('tr');
		var mainid = $(this).parents('tr').find('.customnotif_id').val();
		if(mainid != ''){
			var result = confirm("Are you sure you want to delete?");
			if (result) {
				$.ajax({
					type:'POST',
					url:'custom_notification_action.php',
					data:{'id':mainid,'type':'delete'},
					cache: false,
					success:function(data){
						if(data == 'loggedout'){
							location.reload();
						}
						var dataa = $.parseJSON(data);
						if(dataa.id){
							maintr.remove();
							$('.alert-danger').hide();
							$('.alert-success').find('p').html('Notificaiton deleted successfully!');
							$('.alert-success').show()
						}
						
					}
				});
			}
		}else{
			maintr.remove();
		}
	});
	
	$(document).on('click','.addcustom',function(){
		var maintr 			= $(this).parents('tr');
		var maintrcutnotif 	= $(this).parents('tr').find('.customnotif_id');
		var mainid 			= $(this).parents('tr').find('.customnotif_id').val();
		var message 		= $(this).parents('tr').find('input[name="message"]').val();
		var lat 			= $(this).parents('tr').find('input[name="lat"]').val();
		var lon 			= $(this).parents('tr').find('input[name="lon"]').val();
		if($.trim(lat) == '' || $.trim(lon) == '' || $.trim(message) == ''){
			$('.alert-danger').show();
			return false;
		}
		
		$.ajax({
			type:'POST',
			url:'custom_notification_action.php',
			data:{'id':mainid,'type':'add','message':message,'lat':lat,'lon':lon},
			cache: false,
			success:function(data){
				if(data == 'loggedout'){
					location.reload();
				}
				var dataa = $.parseJSON(data);
				if(dataa.id){
					$('.alert-danger').hide();
					if(mainid != ''){
						$('.alert-success').find('p').html('Notificaiton udpated successfully!');
						$('.alert-success').show()
					}else{
						$('.alert-success').find('p').html('Notificaiton added successfully!');
						$('.alert-success').show()
					}
					maintrcutnotif.val(data);
					maintr.find('.addcustom').text('Update');
				}
			}
		});
	});
    </script>
</body>
<!-- END BODY-->
</html>