<?php
session_start();
if (!isset($_SESSION['userid'])) die;

// Need this piece from functions.php to get the path
$hostarray = explode(".",$_SERVER['HTTP_HOST']);
define('CLIENT',$hostarray[0]);
define('CLIENT_PATH',"/var/www/kizunadb/client/".CLIENT);

$path = CLIENT_PATH."/photos/";
//die($path.$_GET['f'].".jpg");
header("Content-type: image/jpeg");
readfile($path.(is_file($path.$_GET['f'].".jpg") ? $_GET['f'] : "missing_file").".jpg");

