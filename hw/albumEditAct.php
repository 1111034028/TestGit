<?php require_once('../final/inc/auth_guard.php'); ?>
<?php
require_once("../DB/DB_open.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['album_id'];
    $title = $_POST['title'];
    $album_date = $_POST['album_date'];
    $location = $_POST['location'];
    
    $sql = "UPDATE album SET title='$title', album_date='$album_date', location='$location' WHERE album_id=$id";
    
    // 如果有上傳新照片
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $upload_dir = "img/";
        $filename = basename($_FILES["photo"]["name"]);
        $target_file = $upload_dir . time() . "_" . $filename;
        $db_filename = time() . "_" . $filename;
        
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            // 更新圖片路徑
            $sql = "UPDATE album SET title='$title', album_date='$album_date', location='$location', picurl='$db_filename' WHERE album_id=$id";
        }
    }
    
    if (mysqli_query($link, $sql)) {
        ?>
        <!DOCTYPE html>
        <html lang="zh-TW">
        <head>
            <meta charset="UTF-8">
            <title>更新成功</title>
            <link rel="stylesheet" href="css/navCSS.css">
            <link rel="stylesheet" href="css/indexCSS.css">
        </head>
        <body>
            <div id="wrap">
                <header id="header" class="clearheader"><?php require "nav.php"; ?></header>
                <main id="contents" style="text-align: center; padding: 50px;">
                    <h2>相簿更新</h2>
                    <p style="font-size: 1.2em; color: green;">資料更新成功</p>
                    <a href="album.php" style="color: blue; text-decoration: underline;">回系統首頁</a>
                </main>
                <footer id="footer"><?php require "foot.html"; ?></footer>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "更新失敗: " . mysqli_error($link);
    }
}
require_once("../DB/DB_close.php");
?>
