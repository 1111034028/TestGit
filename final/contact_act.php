<?php
session_start();
require_once("../DB/DB_open.php");

// 處理表單資料
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $category = mysqli_real_escape_string($link, $_POST['category']);
    $message = mysqli_real_escape_string($link, $_POST['message']);
    
    $user_id = isset($_SESSION['sno']) ? "'" . mysqli_real_escape_string($link, $_SESSION['sno']) . "'" : "NULL";

    $insert_sql = "INSERT INTO contact_messages (user_id, name, email, category, message) 
                   VALUES ($user_id, '$name', '$email', '$category', '$message')";

    if (mysqli_query($link, $insert_sql)) {
        echo "<script>
                alert('感謝您的回報！客服人員將盡快聯絡您。');
                window.location.href = 'contact_us.php';
              </script>";
    } else {
        echo "<script>
                alert('發送失敗，請稍後再試。');
                window.history.back();
              </script>";
    }
} else {
    header("Location: contact_us.php");
}

require_once("../DB/DB_close.php");
?>
