<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>期末報告</title>
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link rel="stylesheet" href="css/navCSS.css" media="all">
</head>
<body>
<div id="wrap">
    <!-- 功能列表 -->
    <header id="header" class="clearheader">
        <?php include "nav.html"; ?>
    </header>
    <!-- 內容區域 -->
    <main id="contents">
        <h2>相簿更新</h2>
        <?php
        require_once("DB_open.php");
        // 是否是表單送回
        if (isset($_POST["Edit"])) {
            // 更新所指定編號的記錄
            $sql='UPDATE album
                SET album_date = "'.$_POST["album_date"].'",
                    title = "'.$_POST["album_title"].'",
                    location = "'.$_POST["album_location"].'"
                WHERE album_id = '.$_POST["album_id"].';';
            mysqli_query($link, $sql);
            echo "資料更新成功<br/>";
        }
        ?>
        <p><a href="album.php">回系統首頁</a></p>
    </main>
</div>
</body>
</html>
