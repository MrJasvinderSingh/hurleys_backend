<?php

date_default_timezone_set('US/Eastern'); 
// Code for Local Server Only
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

ini_set('memory_limit', '1024M');
$hst_arr = explode("/",$_SERVER["REQUEST_URI"]);
$hst_var = $hst_arr[1];


if(isset($UpdateDatabase) && $UpdateDatabase == 'yes')
{
	define( 'TSITE_SERVER',$hostName);
	define( 'TSITE_DB',$databaseName);
	define( 'TSITE_USERNAME',$userName);
	define( 'TSITE_PASS',$passwordName);
}
else
{


   // define( 'TSITE_SERVER','localhost');
  	//define( 'TSITE_DB','hurleys_loyality_stagedb'); //_stage
  	//define( 'TSITE_USERNAME','hurleys_loyality_stage');
  	//define( 'TSITE_PASS','Hu43r8l^rey_@Stage!');
	
	  define( 'TSITE_SERVER','localhost');
  	define( 'TSITE_DB','hurleys_db'); //_stage
  	define( 'TSITE_USERNAME','root');
  	define( 'TSITE_PASS','');

	

}

?>
