<?php
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
$my_site_folder = "hurleys_backend/";
define('TPATH_CLASS',$DOCUMENT_ROOT.'/'.$my_site_folder.'assets/libraries/' );
include_once(TPATH_CLASS."db_info.php");
include_once("include_config_inc.php");
?>