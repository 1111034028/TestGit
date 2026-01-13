<?php
require_once("inc/auth_guard.php");

require_once("../DB/DB_open.php");

// 獲取使用者 sno (用於驗證權限)
$username = $_SESSION["username"];
$sql_user = "SELECT sno FROM students WHERE username = '$username'";
$result_user = mysqli_query($link, $sql_user);
$row_user = mysqli_fetch_assoc($result_user);
$sno = $row_user['sno'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $title = mysqli_real_escape_string($link, $_POST['title']);
    $artist = mysqli_real_escape_string($link, $_POST['artist']);
    $genre = mysqli_real_escape_string($link, $_POST['genre']);

    // 驗證擁有權
    $check_sql = "SELECT * FROM songs WHERE id = '$id' AND uploader_id = '$sno'";
    $check_result = mysqli_query($link, $check_sql);
    if (mysqli_num_rows($check_result) == 0) {
        die("無權限或歌曲不存在");
    }
    
    // 構建更新 SQL
    $update_fields = array(
        "title = '$title'",
        "artist = '$artist'",
        "genre = '$genre'"
    );

    // 處理音樂檔案更新
    // 如果有上傳新檔案
    if (isset($_FILES['music_file']) && $_FILES['music_file']['error'] == 0) {
        // 設定上傳目錄
        $target_dir = "music/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // 生成唯一檔名
        $file_ext = strtolower(pathinfo($_FILES["music_file"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["music_file"]["tmp_name"], $target_file)) {
            $update_fields[] = "file_path = '$new_filename'";
        }
    }

    // 處理封面檔案更新
    // 如果有上傳新封面
    if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] == 0) {
        $cover_type = $_FILES["cover_file"]["type"];
        $cover_content = mysqli_real_escape_string($link, file_get_contents($_FILES["cover_file"]["tmp_name"]));
        
        $update_fields[] = "cover_image = '$cover_content'";
        $update_fields[] = "cover_type = '$cover_type'";
    }

    // 執行更新
    $sql = "UPDATE songs SET " . implode(", ", $update_fields) . " WHERE id = '$id'";

    if (mysqli_query($link, $sql)) {
        echo "<script>alert('更新成功！'); window.location.href='creator.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($link);
    }
} else {
    header("Location: creator.php");
}

require_once("../DB/DB_close.php");
?>
