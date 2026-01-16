<?php
// setup_db.php
// 用於自動建立缺失的 music_marks 資料表
require_once("../DB/DB_open.php");

echo "<h2>檢查資料庫結構...</h2>";

// 檢查 music_marks 是否存在
$check = mysqli_query($link, "SHOW TABLES LIKE 'music_marks'");
if (mysqli_num_rows($check) == 0) {
    echo "music_marks 資料表不存在，正在建立...<br>";
    
    $sql = "CREATE TABLE IF NOT EXISTS `music_marks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` char(4) NOT NULL COMMENT '會員ID',
      `song_id` int(11) NOT NULL COMMENT '歌曲ID',
      `latitude` double NOT NULL COMMENT '緯度',
      `longitude` double NOT NULL COMMENT '經度',
      `message` text DEFAULT NULL COMMENT '留言內容',
      `location_name` varchar(255) DEFAULT NULL COMMENT '地點名稱',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '建立時間',
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `song_id` (`song_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if (mysqli_query($link, $sql)) {
        echo "<span style='color:green'>成功建立 `music_marks` 資料表！</span><br>";
    } else {
        echo "<span style='color:red'>建立失敗: " . mysqli_error($link) . "</span><br>";
    }
} else {
    echo "<span style='color:blue'>`music_marks` 資料表已存在，無需操作。</span><br>";
}

require_once("../DB/DB_close.php");
?>
