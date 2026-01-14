<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $username = $_SESSION["username"];
    $user = get_user_info($link, $username);
    $sno = $user['sno'] ?? '';

    $from = $_POST['from'] ?? 'creator';
    $back_url = ($from === 'admin') ? 'admin.php' : 'creator.php';

    // 驗證擁有權 (上傳者本人或管理員)
    if ($user['role'] !== 'admin') {
        $check_sql = "SELECT id FROM songs WHERE id = $id AND uploader_id = '$sno'";
        if (mysqli_num_rows(mysqli_query($link, $check_sql)) == 0) {
            die("無權限或歌曲不存在");
        }
    }
    
    $update_data = [
        'title' => $_POST['title'],
        'artist' => $_POST['artist'],
        'genre' => $_POST['genre']
    ];

    // 音樂檔案
    if (isset($_FILES['music_file']) && $_FILES['music_file']['error'] == 0) {
        $target_dir = "music/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $new_filename = uniqid() . "." . pathinfo($_FILES["music_file"]["name"], PATHINFO_EXTENSION);
        if (move_uploaded_file($_FILES["music_file"]["tmp_name"], $target_dir . $new_filename)) {
            $update_data['file_path'] = $new_filename;
        }
    }

    // 封面檔案
    if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] == 0) {
        $update_data['cover_image'] = file_get_contents($_FILES["cover_file"]["tmp_name"]);
        $update_data['cover_type'] = $_FILES["cover_file"]["type"];
    }

    if (db_update($link, 'songs', $update_data, "id = $id")) {
        header("Location: $back_url" . (strpos($back_url, '?') !== false ? '&' : '?') . "success=update");
    } else {
        header("Location: $back_url" . (strpos($back_url, '?') !== false ? '&' : '?') . "error=update");
    }
} else {
    header("Location: creator.php");
}

require_once("../DB/DB_close.php");
?>
