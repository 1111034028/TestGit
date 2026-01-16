<?php
// add_pinned_column.php
// 自動新增 is_pinned 欄位到 playlists 資料表
require_once("../DB/DB_open.php");

echo "<h2>檢查並更新資料庫結構...</h2>";

// 1. 檢查 playlists 表是否有 is_pinned 欄位
$result = mysqli_query($link, "SHOW COLUMNS FROM `playlists` LIKE 'is_pinned'");
$exists = (mysqli_num_rows($result) > 0);

if (!$exists) {
    echo "正在為 `playlists` 資料表新增 `is_pinned` 欄位...<br>";
    $sql = "ALTER TABLE `playlists` ADD `is_pinned` TINYINT(1) NOT NULL DEFAULT '0' AFTER `name`";
    
    if (mysqli_query($link, $sql)) {
        echo "<span style='color:green'>成功新增 `is_pinned` 欄位！</span><br>";
    } else {
        echo "<span style='color:red'>新增失敗: " . mysqli_error($link) . "</span><br>";
    }
} else {
    echo "<span style='color:blue'>`is_pinned` 欄位已存在，無需操作。</span><br>";
}

// 2. 檢查 playlist_songs 表是否有 is_pinned 欄位 (歌曲釘選)
$result_songs = mysqli_query($link, "SHOW COLUMNS FROM `playlist_songs` LIKE 'is_pinned'");
$exists_songs = (mysqli_num_rows($result_songs) > 0);

if (!$exists_songs) {
    echo "正在為 `playlist_songs` 資料表新增 `is_pinned` 欄位...<br>";
    $sql_songs = "ALTER TABLE `playlist_songs` ADD `is_pinned` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sort_order`";
    
    if (mysqli_query($link, $sql_songs)) {
        echo "<span style='color:green'>成功新增 `playlist_songs` 的 `is_pinned` 欄位！</span><br>";
    } else {
        echo "<span style='color:red'>新增失敗: " . mysqli_error($link) . "</span><br>";
    }
} else {
    echo "<span style='color:blue'>`playlist_songs` 的 `is_pinned` 欄位已存在。</span><br>";
}

require_once("../DB/DB_close.php");
?>
