<?php
$host="localhost";
$user="root";
$password="";
$database="user";
$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error){
    die('connect Error ('. $mysqli->connect_errno .') '.$mysqli->connect_error);
}

// else{
//     echo "success";
// }