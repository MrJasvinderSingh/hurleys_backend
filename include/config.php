<? 
$template = "";
$template = strtolower($template) ?: "uber";
$templatePath = "templates/".$template."/";

$sess_hosttype = "food";
//if($_SESSION['sess_systype'] == "food"){
if($sess_hosttype == "food"){
  $templatePath = "templates/foodtemplate/";
}
?> 