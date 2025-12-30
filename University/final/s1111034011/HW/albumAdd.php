<?php
// 新增相簿
if (isset($_POST["album_title"])) {
    $D = $_POST["album_date"];
    $L = $_POST["album_location"];
    $T = $_POST["album_title"];
    $P = $_FILES["picurl"]["name"];

    require_once("DB_open.php");
    $query_insert = "INSERT INTO album (album_date, location, title, picurl) VALUES ('"
        . $D . "', '" . $L . "', '" . $T . "', '" . $P . "')";

    mysqli_query($link, $query_insert);

    // 上傳圖片檔案
    if (is_uploaded_file($_FILES["picurl"]["tmp_name"])) {
        $file = 'photos/' . basename($_FILES["picurl"]["name"]);
        if (
            $_FILES["picurl"]["type"] == 'image/png'
            || $_FILES["picurl"]["type"] == 'image/jpeg'
        ) { // 判斷檔案是否為圖片
            if (move_uploaded_file($_FILES["picurl"]["tmp_name"], $file)) {
                // 上傳圖片檔案成功
            } else {
                echo "上傳失敗。";
            }
        } else { // 如果不為圖片的話
            echo "此副檔名需為 jpg、jpeg、png。";
        }
    }

    require_once("DB_close.php"); // 引入資料庫關閉設定
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>期末報告</title>
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/albumCSS.css" media="all">
</head>

<body>
    <div id="wrap">
        <!-- 功能列表 -->
        <header id="header" class="clearheader">
            <?php include "nav.html"; ?>
        </header>
        <!-- 內容區域 -->
        <main id="contents">
            <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <div class="subjectDiv">相簿管理 - 新增相簿</div>

                        <div class="normalDiv">
                            <form action="<?php $_SERVER["PHP_SELF"] ?>" method="post" enctype="multipart/form-data"
                                name="form1" id="form1">
                                <p>相片名稱：<input type="text" name="album_title" id="album_title" /></p>
                                <p>拍攝時間：<input type="text" name="album_date" id="album_date"
                                        value="<?php echo date("Y-m-d H:i:s"); ?>" /></p>
                                <p>拍攝地點：<input type="text" name="album_location" id="album_location" /></p>
                                <p>照片<input type="file" name="picurl" id="picurl" /></p>

                                <input type="submit" name="action" id="action" value="確定新增" />
                                <a href="album.php">回相簿管理</a>
                            </form>
                        </div>
                    </td>
                </tr>
            </table>
        </main>
        <!-- 頁尾資訊 -->
        <footer id="footer">
            <?php include "infoFooter.html"; ?>
        </footer>
    </div>
</body>

</html>