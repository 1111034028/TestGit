<?php require_once('../final/auth_check.php'); ?>
<?php
require_once("../DB/DB_open.php");

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['sno']) ? $_POST['sno'] : '');
$msg = "";
$msg_type = "";

// 處理刪除動作
if ($action == 'delete' && !empty($id)) {
    $sql = "DELETE FROM students WHERE sno = '$id'";
    if (mysqli_query($link, $sql)) {
        header("Location: contacts.php"); // 刪除成功跳轉回列表
        exit;
    } else {
        $msg = "刪除失敗：" . mysqli_error($link);
        $msg_type = "error";
    }
}

// 處理更新動作
if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'update') {
    $name = $_POST["name"];
    $address = $_POST["address"];
    $birthday = $_POST["birthday"];
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "UPDATE students SET 
            name = '$name', 
            address = '$address', 
            birthday = '$birthday', 
            username = '$username', 
            password = '$password' 
            WHERE sno = '$id'";
    
    if (mysqli_query($link, $sql)) {
        $msg = "資料更新成功！";
        $msg_type = "success";
    } else {
        $msg = "更新失敗：" . mysqli_error($link);
        $msg_type = "error";
    }
}

// 讀取舊資料 (用於編輯表單顯示)
$row = null;
if (($action == 'edit' || $action == 'update') && !empty($id)) {
    $sql = "SELECT * FROM students WHERE sno = '$id'";
    $result = mysqli_query($link, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯通訊錄</title>
    <link rel="stylesheet" href="css/navCSS.css">
    <link rel="stylesheet" href="css/indexCSS.css">
    <link rel="stylesheet" href="css/tableForm.css">
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.php"; ?>
        </header>

        <main id="contents">
            <h2>編輯通訊錄</h2>
            
            <?php if ($msg != ""): ?>
                <div class="<?php echo ($msg_type=='success') ? 'msg-success' : 'msg-error'; ?>">
                    <?php echo $msg; ?>
                </div>
                <?php if ($msg_type == 'success'): ?>
                    <div style="text-align:center; margin-bottom:20px;">
                        <a href="contacts.php" style="font-weight:bold;">返回通訊錄首頁</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($row): ?>
            <form action="edit.php" method="post">
                <input type="hidden" name="action" value="update">
                <table>
                    <tr>
                        <th>學號 (唯讀):</th>
                        <!-- 學號通常作為主鍵不給修改 -->
                        <td>
                            <input type="text" name="sno" value="<?php echo htmlspecialchars($row['sno']); ?>" readonly style="background-color: #eee; cursor: not-allowed; color: #888;">
                        </td>
                    </tr>
                    <tr>
                        <th>姓名:</th>
                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required></td>
                    </tr>
                    <tr>
                        <th>住址:</th>
                        <td><input type="text" name="address" value="<?php echo htmlspecialchars($row['address']); ?>"></td>
                    </tr>
                    <tr>
                        <th>生日:</th>
                        <td><input type="date" name="birthday" value="<?php echo htmlspecialchars($row['birthday']); ?>"></td>
                    </tr>
                    <tr>
                        <th>帳號:</th>
                        <td><input type="text" name="username" value="<?php echo htmlspecialchars($row['username']); ?>"></td>
                    </tr>
                    <tr>
                        <th>密碼:</th>
                        <td><input type="text" name="password" value="<?php echo htmlspecialchars($row['password']); ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="btn-container">
                            <input type="submit" value="更新資料">
                            <a href="contacts.php" class="btn" style="background-color:#b2bec3; text-decoration:none;">取消</a>
                        </td>
                    </tr>
                </table>
            </form>
            <?php else: ?>
                <p style="text-align:center; color:red;">查無此資料或操作無效。</p>
            <?php endif; ?>

            <div class="footer-links" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <a href="index.php">首頁</a> |
                <a href="add.php">新增聯絡資料</a> |
                <a href="search.php">搜尋通訊錄</a>
            </div>
            
            <div style="margin-top: 40px; font-size: 0.9em; color: #888;">
                班級：你的班級 / 姓名：你的姓名 / 學號：你的學號 <span style="color: #e84393;">●</span>
            </div>
        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
