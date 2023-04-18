<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/hurleys_backend/invoices/PDF/';
//$path = $_SERVER['DOCUMENT_ROOT'].'/ftpconnect/PDF/';
$filename = $_REQUEST['filename'];

$response = array();

$FileUrl = 'https://loyalty.hurleys.ky/invoices/PDF/';
$existing_dir = getcwd();
//echo $path; exit;
// path to dir
chdir($path);

$invoices = array();
$FileExist = 0;

foreach (glob('*' . $filename . '*.pdf') as $customerfile) {
    if ($customerfile) {
        $FileExist = 1;
    }

    if ($FileExist) {
        $fileParts = explode('_', $customerfile);
        $Date = $fileParts[1];
        $time =  $fileParts[2];
        $txno = str_replace('.pdf', '', $fileParts[3] ? $fileParts[3] : $fileParts[4]);
        unset($fileParts);
        $dateFor =  str_split($Date, 2);
        $timefor = str_split($time, 1);
        $FinalTime = '';
        $TimeCount =   count($timefor);
        if ($TimeCount == 3) {
            $FinalTime =  $timefor[0] . ':' . $timefor[1] . $timefor[2];
        } else {
            $FinalTime =  $timefor[0] . $timefor[1] . ':' . $timefor[2] . $timefor[3];
        }


        $invoices[] = array(
            'url' => $FileUrl . $customerfile,
            'filename' => $customerfile,
            'date' => $dateFor[0] . '-' . $dateFor[1] . '-20' . $dateFor[2],
            'time' => $FinalTime,
            'txno' => $txno
        );
    }
}
chdir($existing_dir);


if ($FileExist) {
	
	usort($invoices, function ($item1, $item2) {
        return $item2['txno'] <=> $item1['txno'];
    });

    $response["Action"] = "1";
    $response["invoices"] = $invoices;

    echo json_encode($response);
    exit;
} else {
    $response["Action"] = "0";
    $response["invoices"] = "";

    echo json_encode($response);
    exit;
}
