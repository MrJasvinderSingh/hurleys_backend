<?php
$myFile = 'procedure.txt';
$message = 'procedure<br/>';
$myfile = fopen($myFile, "w") or die("Unable to open file!");
if (file_exists($myFile)) {echo 'asdg';
  $fh = fopen($myFile, 'a');
  fwrite($fh, $message."\n");
} else {
  $fh = fopen($myFile, 'w');print_R($fh);echo $myFile;
  fwrite($fh, $message."\n");
}
fclose($fh);

return true;