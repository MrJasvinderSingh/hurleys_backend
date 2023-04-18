<?php
include_once('../common.php');
include_once('../generalFunctions.php');
require_once('../assets/libraries/pubnub/autoloader.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
global $generalobj, $obj;
$generalobjAdmin->check_member_login();
if (isset($_POST['action']) && ($_POST['action'] == 'assignorder')) {
    $assignorder = AssignOrderToDriver($_POST['iOrderId'], $_POST['driverid']);
	//echo 'yes';
   
    //header("Refresh:0");
}
if (isset($_POST['removedrive'])) {
	global $generalobj, $obj;
    $driverid = $_POST['driverid'];
    $orderid = $_POST['orderid'];
    $sql = "select d.iDriverId,d.iGcmRegId,d.eDeviceType,d.tSessionId,d.iAppVersion,d.vLang,o.vOrderNo , o.iCompanyId from orders as o LEFT JOIN register_driver as d ON o.iDriverId=d.iDriverId where o.iOrderId = '" . $orderid . "'";
    $drv_data_order = $obj->MySQLSelect($sql);
    $updateorder = "update orders set iDriverId = 0,iStatusCode=2 where iOrderId = $orderid";


    $updatdrivereq = "update driver_request set eStatus = 'RemovedbyAdmin' where iOrderId = $orderid";


    $updatedr = $obj->sql_query($updateorder);
    // echo "1";
    $updatedr1 = $obj->sql_query($updatdrivereq);
    //  echo "2";
    $getreqid = "select iRequestId from driver_request where iOrderId = $orderid and estatus = 'RemovedbyAdmin'";
    $getreqids = $obj->MySQLSelect($getreqid);
    // echo "3";
    $irequestid = $getreqids[0]['iRequestId'];
    $deleteorPreq = "delete from passenger_requests where iRequestId = $irequestid";
    $obj->sql_query($deleteorPreq);
    // echo "4";
    $deletetrips = "delete from trips where iOrderId = $orderid";
    $obj->sql_query($deletetrips);
    // echo "5";
    $CheckDriverwithotherorders = CheckDriverStatusIFonGoingWithOrders($driverid);
    if ($CheckDriverwithotherorders == 'no') {
        $updatedriver = "update register_driver set vAvailability = 'Available' , vTripStatus = 'Finished' where iDriverId = '$driverid';";
        $obj->sql_query($updatedriver);
        //  echo "6";
    }






    $uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";
    $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations", "PUBNUB_DISABLED");
    $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
    $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");


    ## Send Notification To Driver ##
    $Message = "OrderCancelByAdmin";



    $drvLangCode = $drv_data_order[0]['vLang'];
    $drvOrderNo = $drv_data_order[0]['vOrderNo'];
    $iDriverId = $drv_data_order[0]['iDriverId'];

    $message_arr_res = array();
    $message_arr_res['Message'] = $Message;
    $message_arr_res['iOrderId'] = $orderid;
    $message_arr_res['vOrderNo'] = $drvOrderNo;
    $message_arr_res['vTitle'] = 'Order Unassign By Admin';
    $message_arr_res['tSessionId'] = $drv_data_order[0]['tSessionId'];
    ;
    $drvmessage = json_encode($message_arr_res, JSON_UNESCAPED_UNICODE);
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }

    $alertSendAllowed = true;
    /* For PubNub Setting */
    $tableName = "register_driver";
    $iMemberId_VALUE = $iDriverId;
    $iMemberId_KEY = "iDriverId";
    $iAppVersion = $drv_data_order[0]['iAppVersion'];
    $eDeviceType = $drv_data_order[0]['eDeviceType'];
    $iGcmRegId = $drv_data_order[0]['iGcmRegId'];
    $tSessionId = $drv_data_order[0]['tSessionId'];
    $registatoin_ids = $iGcmRegId;
    $drvdeviceTokens_arr_ios = array();
    $drvregistation_ids_new = array();
    /* For PubNub Setting Finished */

    if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
        $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
        $DriverchannelName = "DRIVER_" . $drv_data_order[0]['iCompanyId'];
        $info = $pubnub->publish($DriverchannelName, $drvmessage);
        if ($eDeviceType != "Android") {
            array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
        }
    }

    if ($alertSendAllowed == true) {

        if ($eDeviceType == "Android") {
            array_push($drvregistation_ids_new, $iGcmRegId);

            $Dmessage = array("message" => $drvmessage);

            $result = send_notification($drvregistation_ids_new, $Dmessage, 0);
        } else {
            array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
            sendApplePushNotification(0, $drvdeviceTokens_arr_ios, $drvmessage, $alertMsg, 0);
        }
    }


    ## Send Notification To Driver ##






   echo 'yes';
}

?>