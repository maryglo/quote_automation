<?php
require_once ('infusionsoftAPI/src/isdk.php');
$app = new iSDK;

$connection_name = "oo144";
$infusionsoft_api = "1d001cfcf8fde0d687beee8a13344eab";

if ($app->cfgCon($connection_name, $infusionsoft_api)) {
   //echo "Infusionsoft Connected";
} else {
	die("INFUSIONSOFT ERROR");
}


?>
