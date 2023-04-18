<?php
include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');
include_once('include_config.php');
//echo TPATH_CLASS; exit;
include_once(TPATH_CLASS . 'configuration.php');
//error_reporting(-1);
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
global $generalobj, $obj;
$generalobjAdmin->check_member_login();
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 205) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$driversql = "SELECT iDriverId, vEmail, Concat(vName,' ',vLastName) as drivername FROM register_driver WHERE eStatus = 'active' AND vAvailability = 'Available' AND tLocationUpdateDate > '$str_date';";
//AND tLocationUpdateDate > '$str_date'
//and vAvailability = 'Available' and vTripStatus != 'Active'
$drivers = $obj->MySQLSelect($driversql);

                                    if (count($drivers) > 0) {
                                        $i = 0;
                                        echo "<select name='driverid' class='filter-by-text form-control'>";
                                        foreach ($drivers as $val) {
                                            $i++;
                                            ?>
                <option type="radio" name="driver_id" id="driveridcheck<?= $i ?>" value="<?= $val['iDriverId'] ?>">
                  <?= $val['drivername'] . ' - ' . $val['vEmail'] ?>
                </option>
                <?php
                                        }
                                        echo "</select>";
                                    } else {
                                        echo "<h3>No Driver found right now. Please try again.</h3>";
                                    }
                                    ?>
