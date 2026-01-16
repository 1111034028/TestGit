<?php
session_start();
require_once("../DB/DB_open.php");

$error = '';
$redirect_target = $_REQUEST['redirect'] ?? 'mobile_control.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sno = mysqli_real_escape_string($link, $_POST["sno"] ?? '');
    $name = mysqli_real_escape_string($link, $_POST["name"] ?? '');
    $username = mysqli_real_escape_string($link, $_POST["username"] ?? '');
    $password = mysqli_real_escape_string($link, $_POST["password"] ?? '');
    $birthday = mysqli_real_escape_string($link, $_POST["birthday"] ?? '');
    $address = mysqli_real_escape_string($link, $_POST["address"] ?? '');

    // Basic Validation
    if (empty($sno) || empty($username) || empty($password) || empty($name)) {
        $error = "請填寫所有必填欄位";
    } else {
        // Check duplicate
        $check_sql = "SELECT * FROM students WHERE sno = '$sno' OR username = '$username'";
        $result = mysqli_query($link, $check_sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if ($row['sno'] == $sno) {
                $error = "學號 $sno 已被使用";
            } else {
                $error = "帳號 $username 已被使用";
            }
        } else {
            // Insert
            $insert_sql = "INSERT INTO students (sno, name, address, birthday, username, password) 
                           VALUES ('$sno', '$name', '$address', '$birthday', '$username', '$password')";
            
            if (mysqli_query($link, $insert_sql)) {
                // Create Default Playlist
                $pl_sql = "INSERT INTO playlists (user_id, name) VALUES ('$sno', 'My Favorites')";
                mysqli_query($link, $pl_sql);

                // Auto Login
                $_SESSION['login_session'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['sno'] = $sno;
                $_SESSION['picture'] = ''; // New user no pic

                header("Location: " . $redirect_target);
                exit;
            } else {
                $error = "系統錯誤: " . mysqli_error($link);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>註冊 - Music Stream</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/mobile_auth.css?v=<?php echo time(); ?>">
</head>
<body>
    <h2>註冊新帳戶</h2>
    <form method="POST">
        <?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_target); ?>">
        
        <input type="text" name="sno" placeholder="學號 (ID)" required maxlength="4">
        <input type="text" name="name" placeholder="姓名" required maxlength="10">
        <input type="text" name="username" placeholder="帳號" required maxlength="10">
        <input type="password" name="password" placeholder="密碼" required maxlength="10">
        <input type="text" onfocus="(this.type='date')" name="birthday" placeholder="生日" required>
        <input type="text" name="address" placeholder="地址" required>
        
        <button type="submit">註冊並登入</button>
        
        <a href="mobile_login.php?redirect=<?php echo urlencode($redirect_target); ?>">已有帳號？登入</a>
    </form>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
