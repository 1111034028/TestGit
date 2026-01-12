<?php
session_start();
require_once("../DB/DB_open.php");

if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $song_id = $_GET['id'];
    $username = $_SESSION["username"];
    
    // 取得使用者 sno
    $sql_user = "SELECT sno FROM students WHERE username = '$username'";
    $result_user = mysqli_query($link, $sql_user);
    $row_user = mysqli_fetch_assoc($result_user);
    $sno = $row_user['sno'];
    
    // 檢查歌曲是否屬於該使用者 (或是否為管理員 - 暫未實作管理員標記，先檢查擁有權)
    // 為了之後方便，若 sno 為特定管理員ID也可刪除 (例如 S001)
    // 假設 S001 是 super admin
    $is_admin = ($sno === 'S001');

    if ($is_admin) {
        $sql_check = "SELECT * FROM songs WHERE id = $song_id";
    } else {
        $sql_check = "SELECT * FROM songs WHERE id = $song_id AND uploader_id = '$sno'";
    }

    $result_check = mysqli_query($link, $sql_check);

    if (mysqli_num_rows($result_check) > 0) {
        $row = mysqli_fetch_assoc($result_check);
        
        // 刪除檔案
        if (!empty($row['file_path']) && file_exists("music/" . $row['file_path'])) {
            unlink("music/" . $row['file_path']);
        }
        // 封面已改為資料庫儲存，無需 unlink files

        // 刪除資料庫紀錄
        $sql_delete = "DELETE FROM songs WHERE id = $song_id";
        mysqli_query($link, $sql_delete);
        
        header("Location: creator.php");
    } else {
        echo "無權限刪除此歌曲或歌曲不存在。";
        echo "<br><a href='creator.php'>返回</a>";
    }
}
require_once("../DB/DB_close.php");
?>
