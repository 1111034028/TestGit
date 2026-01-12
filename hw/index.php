<?php require_once('../final/auth_check.php'); ?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>期末報告</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/itemList.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>

<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php
            require "nav.php";
            ?>
        </header>
        <main id="contents">
            <h2>課堂作業首頁</h2>
            <img src="img/nutc.jpg" alt="臺中科大" width="500px">
        </main>
        <footer id="footer">
            <?php
            require "foot.html";
            ?>
        </footer>
    </div>
</body>

</html>
