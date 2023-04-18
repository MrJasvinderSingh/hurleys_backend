<?php
 $response = array();
include_once("common.php");
$iDriverId = isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'';
$vCarType = isset($_REQUEST['selected'])?$_REQUEST['selected']:'';
$vCarTyp = explode(",", $vCarType);
if($iDriverId != '')
{
	$userSQL = "SELECT c.iCountryId from register_driver AS rd LEFT JOIN country AS c ON c.vCountryCode=rd.vCountry where rd.iDriverId='".$iDriverId."'";
	$drivers = $obj->MySQLSelect($userSQL);

	$iCountryId = $drivers[0]['iCountryId'];
	if($iCountryId != ''){
		//echo $Vehicle_type_name = ($APP_TYPE == 'Delivery')? 'Deliver':$APP_TYPE ;	
		$vehicle_type_sql = "SELECT vt.*,c.vCountry,ct.vCity,st.vState,lm.vLocationName from  vehicle_type as vt left join country as c ON c.iCountryId = vt.iCountryId left join state as st ON st.iStateId = vt.iStateId left join city as ct ON ct.iCityId = vt.iCityId left join location_master as lm ON lm.iLocationId = vt.iLocationid where  vt.iLocationId = '-1' OR lm.iCountryId ='".$iCountryId."'";
                
              //  echo $vehicle_type_sql; exit;
                //vt.eType='".$Vehicle_type_name."' AND
                //lm.iCountryId IN('".$iCountryId."', '-1' )
                
		$vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
                //print_r($vehicle_type_data);
		foreach ($vehicle_type_data as $key => $value) { 
			$vname= $value['vVehicleType_'.$default_lang];
			$vCountry = $value['vCountry'];
			$vCity = $value['vCity'];
			$vState = $value['vState'];
		?>
						<?php
						$localization = '';
						if(!empty($value['vLocationName'])) {
							$localization.= $value['vLocationName'];
						}
						else
                                                {
                                                    $localization = "All Location";
                                                }
                                                
                                                
                                               
                                                //. " ( Location : "..")"
                                                $response[] = array(
                                                  'text'=>$vname . '('.$localization.')' ,
                                                    'val'=>$value['iVehicleTypeId'],
                                                    'selected'=>in_array($value['iVehicleTypeId'],$vCarTyp) ? 'yes' : ''
                                                );
                                                //print_r($response);
                                                
						?>


<?php } } }   echo json_encode($response);  ?>

