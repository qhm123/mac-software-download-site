<?php
session_start();

include_once( 'weibo/config.php' );
include_once( 'weibo/VDisk.class.php' );

$access_token = $_SESSION['token']['access_token'];
$vd = new VDisk($access_token);

$url = $vd->down_files($_GET['path']);

header("Location: ". $url);
?>
