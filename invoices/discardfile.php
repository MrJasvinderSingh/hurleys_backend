<?php
function validateString($string)
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9_\-]/', '', $string); // Removes special chars.

    return substr(preg_replace('/-+/', '-', $string), 0, -3);
}


$path = $_SERVER['DOCUMENT_ROOT'] . '/invoices/PDF/';
$filename = $_REQUEST['filename'];
$File_Path = $path . validateString($filename) . '.pdf';
$response = array();
if (file_exists($File_Path) && unlink($File_Path)) {

    $response["Action"] = "1";
    $response["message"] = 'success';

    echo json_encode($response);
    exit;
} else {
    $response["Action"] = "0";
    $response["message"] = "No File Found.";

    echo json_encode($response);
    exit;
}
