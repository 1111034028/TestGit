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
    
    // 建立 SQL 指令字串 (僅查詢帳號)
    $sql = "SELECT * FROM students WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
    $result = mysqli_query($link, $sql);
    
    $login_success = false;
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $db_password = $row['password'];
        
        // 驗證密碼：支援加密雜湊 (password_verify) 或 明文 (舊資料相容)
        if (password_verify($password, $db_password) || $password === $db_password) {
            $login_success = true;
            
            // 如果是明文密碼，自動升級為雜湊 (可選，這裡暫不做自動更新以避免副作用)
            
            $_SESSION["login_session"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["sno"] = $row['sno']; 
            $_SESSION["picture"] = $row['picture']; 
            $_SESSION["first_login"] = true; 
            header("Location: index.php"); 
            exit;
        }
    }
    
    if (!$login_success) {
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
    <title>會員登入 - 音樂串流平台</title>
    <!-- 引用本地獨立樣式表 -->
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-body">
    <!-- Header with Hamburger and Title -->
    <div id="page-header" style="display: none; align-items: center; padding: 15px 25px; background: #121212; color: white; border-bottom: 1px solid #282828;">
        <a href="index.php" style="color:white; text-decoration:none; display:flex; align-items:center;">
             <div style="font-size: 1.5rem; margin-right: 20px; cursor: pointer;">☰</div>
             <div style="font-weight: bold; font-size: 1.2rem; letter-spacing: 1px;">Music Stream</div>
        </a>
    </div>
    <script src="js/auth_shell.js"></script>

    <div class="login-container">
        <h1 style="text-align:center;">會員登入</h1>
        <div class="login-card">
            <?php 
                if (isset($_GET['error']) && $_GET['error'] == 'auth') {
                    echo '<div style="color: #ff7675; text-align:center; margin-bottom:15px; font-weight:bold; background: rgba(255, 118, 117, 0.1); padding: 10px; border-radius: 8px;">請先登入以使用完整功能 (包括客服系統)</div>';
                }
                
                if ($error_msg): 
            ?>
                <div style="color: #e84393; text-align:center; margin-bottom:15px; font-weight:bold;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="form-group">
                    <label>帳號 (Username)</label>
                    <input type="text" name="Username" required placeholder="您的帳號" value="<?php echo htmlspecialchars($username); ?>">
                </div>
                
                <div class="form-group">
                    <label>密碼 (Password)</label>
                    <input type="password" name="Password" required placeholder="您的密碼">
                </div>
                
                <button type="submit" class="submit-btn" style="margin-top: 10px;">登入</button>
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
