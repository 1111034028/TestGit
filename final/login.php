<?php
session_start(); // 啟用交談期

// 新增：若已登入則直接跳轉至首頁，不顯示登入頁面
if (isset($_SESSION["login_session"]) && $_SESSION["login_session"] === true) {
    header("Location: index.php");
    exit;
}

$username = "";
$password = "";

// 取得表單欄位值
if (isset($_POST["Username"])) { $username = $_POST["Username"]; }
if (isset($_POST["Password"])) { $password = $_POST["Password"]; }

$error_msg = "";

// 檢查是否輸入使用者名稱和密碼
if ($username != "" && $password != "") {
    require_once("../DB/DB_open.php"); 
    
    // 建立 SQL 指令字串 
    $sql = "SELECT * FROM students WHERE password='" . $password . "' AND username='" . $username . "'";
    $result = mysqli_query($link, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION["login_session"] = true;
        $_SESSION["username"] = $username;
        $_SESSION["sno"] = $row['sno']; 
        $_SESSION["picture"] = $row['picture']; // Store avatar path (filename)
        header("Location: index.php"); 
        exit;
    } else {
        $error_msg = "使用者名稱或密碼錯誤！";
        $_SESSION["login_session"] = false;
    }
    require_once("../DB/DB_close.php"); 
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>Final Project - Login</title>
    <!-- 引用本地獨立樣式表 -->
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-body">
    <!-- Nav removed for App Shell integration -->

    <div class="login-container">
        <h1 style="text-align:center;">登入</h1>
        <div class="login-card">
            <?php if ($error_msg): ?>
                <div style="color: #e84393; text-align:center; margin-bottom:15px; font-weight:bold;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="Username" required placeholder="您的帳號" value="<?php echo htmlspecialchars($username); ?>">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="Password" required placeholder="您的密碼">
                </div>
                
                <button type="submit" class="submit-btn" style="margin-top: 10px;">Sign In</button>
            </form>
            
            <div style="text-align: center; margin-top: 25px;">
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    尚未在系統註冊？ <a href="register.php" style="color: var(--primary-accent); font-weight:700;">建立帳戶</a>
                </p>
            </div>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
