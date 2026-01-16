<?php
session_start();
require_once("../DB/DB_open.php");

// Handle Login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'mobile_control.php';

    if (empty($username) || empty($password)) {
        $error = "請輸入帳號和密碼";
    } else {
        // Simple auth check (matching login.php logic)
        $sql = "SELECT * FROM students WHERE username = '$username'";
        $result = mysqli_query($link, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if ($row['password'] === $password) { // Plaintext as per project style
                $_SESSION['login_session'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['sno'] = $row['sno'];
                $_SESSION['picture'] = $row['picture'];
                
                header("Location: " . $redirect);
                exit;
            } else {
                $error = "密碼錯誤";
            }
        } else {
            $error = "帳號不存在";
        }
    }
}

$redirect_target = $_GET['redirect'] ?? 'mobile_control.php';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>登入 - Music Stream</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/mobile_auth.css?v=<?php echo time(); ?>">
</head>
<body>
    <h1>Music Stream</h1>
    
    <form class="login-box" method="POST">
        <?php if($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_target); ?>">
        <input type="text" name="username" placeholder="帳號" required autocomplete="off">
        <input type="password" name="password" placeholder="密碼" required>
        <button type="submit">登入</button>
        
        <a href="mobile_register.php?redirect=<?php echo urlencode($redirect_target); ?>">還沒有帳號？註冊</a>
        <br>
        <a href="<?php echo htmlspecialchars($redirect_target); ?>" style="margin-top: 10px; font-size: 0.8rem;">返回遙控介面</a>
    </form>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
