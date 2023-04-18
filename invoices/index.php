<?php
echo "Hello <br/>";
$path = $_SERVER['DOCUMENT_ROOT'].'/invoices/PDF/';
if (!file_exists($path)) {
  mkdir($path, 0755, true);
}
$ftp_server = "23.188.0.162";
$ftp_conn = ftp_connect($ftp_server, '21') or die("Could not connect to $ftp_server");
$login = ftp_login($ftp_conn, 'Hurleys', 'Infinity18!');
if($login) { echo "connected <br/>"; }
ftp_pasv($ftp_conn, true) or die("Unable switch to passive mode");
//$info = ftp_systype($ftp_conn);
//echo "Info: $info\n";
//ftp_set_option($ftp_conn, FTP_USEPASVADDRESS, false);
function ftp_is_dir($dir) {
  global $ftp_conn;
  if (@ftp_chdir($ftp_conn, $dir)) {
       ftp_chdir($ftp_conn, '..');
       return true;
  } else {
       return false;
  }
}
$ftp_nlist = ftp_nlist($ftp_conn, ".");
//alphabetical sorting
sort($ftp_nlist);
//echo "Started Downloading Files <br/>";
foreach ($ftp_nlist as $RemoteFile) {
if (!ftp_is_dir($RemoteFile)) {
   //echo "" . $RemoteFile . "<br />\n";
    $localFile =$path.$RemoteFile;
    if(!file_exists($localFile))
    {
        if (ftp_get($ftp_conn, $localFile, $RemoteFile, FTP_BINARY)) {
          //echo "Successfully written to $RemoteFile <br/>";
      } else {
          //echo "There was a problem <br/>";
      }
   }
   else
   {
    //echo "File Already Exist In $localFile <br/>";
   }
}
}
// close connection
ftp_close($ftp_conn);