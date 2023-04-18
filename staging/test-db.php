<?php

$server = '23.188.0.162'; //"DESKTOP-S3N90HL";
//$connMsSql = sqlsrv_connect($server, array("database"=>'test', "UID"=>'anviamdb',"PWD"=>'anviam123'));
$connMsSql = sqlsrv_connect($server, array("database" => 'FrontOff', 
                                            "UID" => 'HurleyAccess', 
                                            "PWD" => 'yQs#M6=V'));

if (!$connMsSql) {
    echo '<pre>';die( print_r( sqlsrv_errors(), true));
} else {
    echo "connected.";
}
