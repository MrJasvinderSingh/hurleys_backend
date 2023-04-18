<?php 

/*****************************************************************************************************************/
/** 								TEMPLATE MEMORY LOAD 														**/
/*****************************************************************************************************************/
ob_start();
@session_start();
/*****************************************************************************************************************/
@header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
define( '_TEXEC', 1 );
define('TPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( TPATH_BASE .DS.'assets'.DS.'libraries'.DS.'defines.php' ); 
require_once ( TPATH_BASE .DS.'assets'.DS.'libraries'.DS.'configuration.php' );
/*****************************************************************************************************************/
/*****************************************************************************************************************/
if(isset($currency) && $currency != '')
{
	$_SESSION['sess_currency']	=	$currency;
}
else
{
	$sql1="SELECT * FROM `currency` WHERE `eDefault` = 'Yes' AND `eStatus` = 'Active' ";
	$db_currency_mst=$obj->MySQLSelect($sql1);
	$_SESSION['sess_currency']	=	$db_currency_mst[0]['vName'];
	$_SESSION['sess_currency_smybol']	=	$db_currency_mst[0]['vSymbol'];
}
/*****************************************************************************************************************/
/*****************************************************************************************************************/
$lang = isset($_REQUEST['lang'])?$_REQUEST['lang']:'';
if(isset($lang) && $lang != '')
{
	$_SESSION['sess_lang']	=	$lang;
	$sql1="select vTitle, vCode, vCurrencyCode, eDefault,eDirectionCode from language_master where  vCode = '".$_SESSION['sess_lang']."' limit 0,1";
	$db_lng_mst1=$obj->MySQLSelect($sql1);
	$_SESSION['eDirectionCode']	=	$db_lng_mst1[0]['eDirectionCode'];
	$posturi = $_SERVER['HTTP_REFERER'];
	header("Location:".$posturi);
	exit;
}

/*****************************************************************************************************************/
if(!isset($_SESSION['sess_lang']))
{
	$sql="select vTitle, vCode, vCurrencyCode, eDefault,eDirectionCode from language_master where eDefault='Yes' limit 0,1";
	$db_lng_mst=$obj->MySQLSelect($sql);
	$_SESSION['sess_lang']	=	$db_lng_mst[0]['vCode'];
	$_SESSION['eDirectionCode']	=	$db_lng_mst[0]['eDirectionCode'];
}
#/***********************************************************************************************************/#

#/***********************************************************************************************************/#
$APP_TYPE = "Delivery";
define('APP_TYPE',"Delivery");
$REFERRAL_SCHEME_ENABLE = "Yes";
define('REFERRAL_SCHEME_ENABLE',"Yes");
$WALLET_ENABLE = "Yes";
define('WALLET_ENABLE',"Yes");
#/***********************************************************************************************************/#
#/***********************************************************************************************************/#
$parent_ufx_catid = "1000000000000000000000000000000000000";
include_once ('common_inc.php'); 
/*************************************************************************************************************/
?>