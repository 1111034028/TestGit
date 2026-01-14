<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sno = $_POST['sno'];
    $current_session_username = $_SESSION["username"];
    
    // 安全檢查
    $user = get_user_info($link, $current_session_username);
    if (!$user || $user['sno'] !== $sno) {
        die("安全檢查失敗：您無法編輯此個人資料。");
    }

    $new_username = $_POST['username'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];
    $password_plain = $_POST['password'];
    
    // 密碼處理 (依需求儲存明文)
    $password_save = $password_plain;
    
    $update_data = [
        'username' => $new_username,
        'name' => $name,
        'address' => $address,
        'birthday' => $birthday,
        'password' => $password_save
    ];
    
    // 處理頭貼
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $upload_dir = "img/avatars/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        // 計算檔案雜湊值 (MD5) 用於重複檢測
        $file_hash = md5_file($_FILES['avatar']['tmp_name']);
        $new_filename = $file_hash . "." . $ext;
        $target_path = $upload_dir . $new_filename;
        
        // 檢查是否已存在相同內容的檔案
        if (file_exists($target_path)) {
            // 若存在，直接使用該檔案 (去重)
            $update_data['picture'] = $new_filename;
            $_SESSION['picture'] = $new_filename;
        } else {
            // 若不存在，則儲存新檔
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
                $update_data['picture'] = $new_filename;
                $_SESSION['picture'] = $new_filename;
            }
        }
    }

    if (db_update($link, 'students', $update_data, "sno = '" . mysqli_real_escape_string($link, $sno) . "'")) {
        if ($new_username !== $current_session_username) {
            $_SESSION["username"] = $new_username;
        }
        // Pass new picture filename to allow frontend instant update
        $new_pic = isset($update_data['picture']) ? $update_data['picture'] : ''; 
        header("Location: profile.php?success=profile_update&new_pic=" . $new_pic);
    } else {
        header("Location: profile.php?error=profile_update");
    }
} else {
    header("Location: profile.php");
}

require_once("../DB/DB_close.php");
?>
