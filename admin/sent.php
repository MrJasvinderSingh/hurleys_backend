<?php
include_once('../common.php');
$response = array('success'=> false);
if(isset($_POST['title']) && $_POST['title'] !='' && isset($_POST['comment']) && $_POST['comment'] !='' && isset($_POST['userid']) && $_POST['userid'] !=''){
    $userid = explode(",",$_POST['userid']);
    $title= $_POST['title'];
    $comment = $_POST['comment'];
    $i = 0;
    foreach($userid as $uId){
        $sql = "INSERT INTO notifications_data(title,body,userid) VALUES('".$title."', '".$comment."', '".$uId."')";
        //$result = $mysqli->query($sql);
        $result[$i] = $obj->sql_query($sql);
        $i++;
    }
    if($result){
        $response['success'] = $result;
        echo "success";
    }
}