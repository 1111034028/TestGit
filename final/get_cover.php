<?php
require_once("../DB/DB_open.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // 從資料庫撈取圖片資料
    $sql = "SELECT cover_image, cover_type FROM songs WHERE id = $id";
    $result = mysqli_query($link, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['cover_image'])) {
            // 設定正確的 Content-Type
            header("Content-Type: " . $row['cover_type']);
            echo $row['cover_image'];
            exit;
        }
    }
}

// 如果沒有圖片或 ID 錯誤，回傳預設圖片
header("Content-Type: image/png");
readfile("img/music.png");
?>
