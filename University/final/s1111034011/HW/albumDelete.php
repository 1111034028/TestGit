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
            <h2>刪除照片</h2>
            <?php
            require_once("DB_open.php");
            // 如果以 GET 方式傳遞過來的 id 參數不是空字串
            if (!empty($_GET["id"])) {
                $sqlimg = 'SELECT picurl FROM album WHERE album_id = ' . $_GET["id"] . '';
                $result = mysqli_query($link, $sqlimg);
                $filename = './photos/' . mysqli_fetch_row($result)[0];
                $sql = 'DELETE FROM album WHERE album_id="' . $_GET["id"] . '"';
                mysqli_query($link, $sql);
                $rowDeleted = mysqli_affected_rows($link);
                if ($rowDeleted > 0) {
                    echo "資料刪除成功<br/>";
                    if (file_exists($filename)) {
                        unlink($filename);
                        echo "圖片刪除成功<br/>";
                    } else {
                        echo "圖片刪除失敗<br/>";
                    }
                } else {
                    echo "資料刪除失敗<br>";
                }
            }
            ?>
            <p><a href="album.php">回系統首頁</a></p>
        </main>
        <footer id="footer">
            <?php include "infoFooter.html"; ?>
        </footer>
    </div>
</body>

</html>