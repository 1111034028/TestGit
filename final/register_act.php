<?php
session_start();
require_once("../DB/DB_open.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sno = mysqli_real_escape_string($link, $_POST["sno"]);
    $name = mysqli_real_escape_string($link, $_POST["name"]);
    $username = mysqli_real_escape_string($link, $_POST["username"]);
    $password = mysqli_real_escape_string($link, $_POST["password"]); // 注意：實際應用中應進行雜湊處理，但目前依照現有慣例使用明文
    $birthday = mysqli_real_escape_string($link, $_POST["birthday"]);
    $address = mysqli_real_escape_string($link, $_POST["address"]);

    // 初步檢查
    if (empty($sno) || empty($username) || empty($password)) {
        $_SESSION["reg_msg"] = "請填寫所有必填欄位！";
        $_SESSION["reg_status"] = "error";
        header("Location: register.php");
        exit;
    }

    // 檢查學號或帳號是否重複
    $check_sql = "SELECT * FROM students WHERE sno = '$sno' OR username = '$username'";
    $result = mysqli_query($link, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['sno'] == $sno) {
            $_SESSION["reg_msg"] = "註冊失敗：學號 $sno 已被使用。";
        } else {
            $_SESSION["reg_msg"] = "註冊失敗：帳號 $username 已被使用。";
        }
        $_SESSION["reg_status"] = "error";
        header("Location: register.php");
    } else {
        // 新增資料
        $insert_sql = "INSERT INTO students (sno, name, address, birthday, username, password) 
                       VALUES ('$sno', '$name', '$address', '$birthday', '$username', '$password')";
        
        if (mysqli_query($link, $insert_sql)) {
            $_SESSION["reg_msg"] = "註冊成功！請使用新帳號登入。";
            $_SESSION["reg_status"] = "success";
            // 可以選擇直接跳轉登入頁，或留在註冊頁顯示成功
            // 為了體驗，這裡跳轉註冊頁顯示成功訊息，並提示登入
            header("Location: register.php"); 
        } else {
            $_SESSION["reg_msg"] = "系統錯誤：" . mysqli_error($link);
            $_SESSION["reg_status"] = "error";
            header("Location: register.php");
        }
    }
}
require_once("../DB/DB_close.php");
?>
