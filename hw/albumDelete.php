<?php require_once('../final/auth_check.php'); ?>
<?php
require_once("../DB/DB_open.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // 先查詢圖片路徑以便刪除檔案 (選做)
    // $sql_query = "SELECT picurl FROM album WHERE album_id=$id"; ... unlink(...);

    $sql = "DELETE FROM album WHERE album_id=$id";
    
    if (mysqli_query($link, $sql)) {
        header("Location: album.php");
        exit;
    } else {
        echo "刪除失敗: " . mysqli_error($link);
    }
}
require_once("../DB/DB_close.php");
?>
