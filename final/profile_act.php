<?php
session_start();
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}

require_once("../DB/DB_open.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data
    $sno = $_POST['sno'];
    $new_username = mysqli_real_escape_string($link, $_POST['username']); // New username from form
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $address = mysqli_real_escape_string($link, $_POST['address']);
    $birthday = mysqli_real_escape_string($link, $_POST['birthday']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    
    // 1. Security Check: ensure POST sno matches logged in user
    // We rely on session username to identify the *current* user before change
    $current_session_username = $_SESSION["username"];
    $check_sql = "SELECT sno FROM students WHERE username = '$current_session_username'";
    $check_result = mysqli_query($link, $check_sql);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['sno'] !== $sno) {
        die("Security Check Failed: You cannot edit this profile.");
    }
    
    // 2.5 Handle Avatar Upload
    $picture_sql_fragment = "";
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $upload_dir = "img/avatars/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_filename = "avatar_" . $sno . "_" . time() . "." . $ext;
        $target_file = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
            $picture_sql_fragment = ", picture = '$new_filename'";
            $_SESSION['picture'] = $new_filename; // Update session immediately
        }
    }

    // 3. Update DB
    $sql = "UPDATE students SET 
            username = '$new_username',
            name = '$name', 
            address = '$address', 
            birthday = '$birthday', 
            password = '$password' 
            $picture_sql_fragment
            WHERE sno = '$sno'";
            
    if (mysqli_query($link, $sql)) {
        // 4. Update Session if username changed
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
