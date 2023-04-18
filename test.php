<?php 

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$db = 'hurleys_db';
$conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);

 

 
$sql = "SELECT * FROM register_user";
$result = $conn->query($sql);

print_r($result);