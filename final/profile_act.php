<?php
require_once("inc/auth_guard.php");

require_once("../DB/DB_open.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得 POST 資料
    $sno = $_POST['sno'];
    $new_username = mysqli_real_escape_string($link, $_POST['username']); // 來自表單的新使用者名稱
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $address = mysqli_real_escape_string($link, $_POST['address']);
    $birthday = mysqli_real_escape_string($link, $_POST['birthday']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    
    // 安全檢查：確保 POST sno 與登入使用者相符
    // 我們依靠 session username 來識別 *當前* 使用者，然後再進行變更
    $current_session_username = $_SESSION["username"];
    $check_sql = "SELECT sno FROM students WHERE username = '$current_session_username'";
    $check_result = mysqli_query($link, $check_sql);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['sno'] !== $sno) {
        die("安全檢查失敗：您無法編輯此個人資料。");
    }
    
    // 處理頭貼上傳
    $picture_sql_fragment = "";
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $upload_dir = "img/avatars/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_filename = "avatar_" . $sno . "_" . time() . "." . $ext;
        $target_file = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
            $picture_sql_fragment = ", picture = '$new_filename'";
            $_SESSION['picture'] = $new_filename; // 立即更新 Session
        }
    }

    // 更新資料庫
    $sql = "UPDATE students SET 
            username = '$new_username',
            name = '$name', 
            address = '$address', 
            birthday = '$birthday', 
            password = '$password' 
            $picture_sql_fragment
            WHERE sno = '$sno'";
            
    if (mysqli_query($link, $sql)) {
        // 如果使用者名稱已變更，更新 Session
        if ($new_username !== $current_session_username) {
            $_SESSION["username"] = $new_username;
        }
        echo "<script>alert('資料更新成功！'); window.location.href='profile.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($link);
    }
} else {
    header("Location: profile.php");
}

require_once("../DB/DB_close.php");
?>
