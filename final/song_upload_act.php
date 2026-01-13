<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = mysqli_real_escape_string($link, $_POST["title"]);
    $artist = mysqli_real_escape_string($link, $_POST["artist"]);
    $genre = mysqli_real_escape_string($link, $_POST["genre"]);
    $upload_date = date("Y-m-d H:i:s");
    
    // 取得上傳者 ID
    $username = $_SESSION["username"];
    $sql_user = "SELECT sno FROM students WHERE username = '$username'";
    $result_user = mysqli_query($link, $sql_user);
    $row_user = mysqli_fetch_assoc($result_user);
    $uploader_id = $row_user['sno'];

    // 處理音樂檔案
    $music_file_name = "";
    if (isset($_FILES["music_file"]) && $_FILES["music_file"]["error"] == 0) {
        $ext = pathinfo($_FILES["music_file"]["name"], PATHINFO_EXTENSION);
        $music_file_name = time() . "_" . uniqid() . "." . $ext;
        $target_music = "music/" . $music_file_name;
        
        if (!move_uploaded_file($_FILES["music_file"]["tmp_name"], $target_music)) {
            die("音樂檔案上傳失敗");
        }
    } else {
        die("請選擇有效的音樂檔案");
    }

    // 處理封面圖片
    $cover_content = NULL;
    $cover_type = NULL;
    if (isset($_FILES["cover_file"]) && $_FILES["cover_file"]["error"] == 0) {
        $cover_type = $_FILES["cover_file"]["type"];
        $cover_content = addslashes(file_get_contents($_FILES["cover_file"]["tmp_name"]));
        // 注意：addslashes 對於 binary data 有效，但 mysqli_real_escape_string更好
        // 我們下面會用 mysqli_real_escape_string
    }

    // 寫入資料庫
    // 這裡需要對 content 做 escape
    $cover_content_sql = $cover_content ? "'" . mysqli_real_escape_string($link, file_get_contents($_FILES["cover_file"]["tmp_name"])) . "'" : "NULL";
    $cover_type_sql = $cover_type ? "'$cover_type'" : "NULL";

    $sql = "INSERT INTO songs (title, artist, file_path, cover_image, cover_type, uploader_id, upload_date, play_count, genre) 
            VALUES ('$title', '$artist', '$music_file_name', $cover_content_sql, $cover_type_sql, '$uploader_id', '$upload_date', 0, '$genre')";

    if (mysqli_query($link, $sql)) {
        header("Location: creator.php");
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($link);
    }
}
require_once("../DB/DB_close.php");
?>
