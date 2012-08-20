<?php
session_start();

include_once( 'config.php' );
include_once( 'VDisk.class.php' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>新浪微博V2接口演示程序-Powered by Sina App Engine</title>
</head>

<body>
<?php 
  $access_token = $_SESSION['token']['access_token'];
  $vd = new VDisk($access_token);
  print_r($vd->account_info());
  print_r($vd->metadata("/apps/mac-software"));
?>


</body>
</html>
