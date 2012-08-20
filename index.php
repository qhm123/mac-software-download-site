<?php
session_start();

include_once( 'weibo/config.php' );
include_once( 'weibo/VDisk.class.php' );
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">

  <title></title>

  <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
  <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">

  <script src="jquery-1.8.0.min.js"></script>
  <script src="bootstrap/js/bootstrap.js"></script>
</head>
<body>
  <div class="container">
    <div class="hero-unit">
      <h1>Mac OS X 软件 个人下载站</h1>
    </div>

    <div class="row">
      <div class="span9">
        <h2>所有软件</h2>

  <?php 
    $access_token = $_SESSION['token']['access_token'];
    $vd = new VDisk($access_token);
    //print_r($vd->account_info());
    $data = $vd->metadata("/apps/mac-software");
    $softwares = $data['contents'];
    //print_r($softwares);
  ?>
        <div class="row">
          <?php foreach ($softwares as $item): ?>
            <div class="span2">
              <img src="img/120628050823101.png" width="120px" height="120px" alt="sublime">
              <h3>name</h3><span><?php echo $item['size']; ?></span>
              <?php echo $item['path']; ?>
              <a href="download.php?path=<?php echo $item['path']; ?>" class="btn btn-success"><i class="icon-download icon-white"></i> 下载</a>
              <button class="btn btn-danger"><i class="icon-heart icon-white"></i> 喜欢</button>
            </div>
          <?php endforeach ?>
        </div>
      </div>

      <div class="span3">
        <h2>下载排行榜</h2>
        <ol>
          <li><a href="detail.php?id=0">Mac QQ</a></li>
          <li>Mac QQ</li>
          <li>Mac QQ</li>
          <li>Mac QQ</li>
          <li>Mac QQ</li>
        </ol>
      </div>
    </div>

    <hr>

    <footer>
      <p>Powered By SAE and VDisk, Made By @鸣_qhm123.</p>
    </footer>
  </div>
</body>
</html>
