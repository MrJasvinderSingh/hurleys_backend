<?php
// include("db.php");
$response = array('success' => false);
if (isset($_POST['title']) && $_POST['title'] != '' && isset($_POST['comment']) && $_POST['comment'] != '' && ($_POST['userid']) && $_POST['userid'] != '') {
  
    $userid = explode(",", $_POST['userid']);
    // print_r($userid);
    // exit;
       
    $title = $_POST['title'];
    $comment = $_POST['comment'];
    foreach($userid as $uId){
        $sql = "INSERT INTO notifications(title,body,userid) VALUES('" . $title . "','" . $comment . "','" . $uId . "')";
    $result = $obj->query($sql);

    }
    //print_r($sql);
    if ($result) {
        $response['success'] = true;
        // echo "success";
    }
}