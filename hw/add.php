<?php require_once('../final/inc/auth_guard.php'); ?>
<?php
require_once("../DB/DB_open.php");

$msg = "";
$msg_type = ""; // success or error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sno = $_POST["sno"];
    $name = $_POST["name"];
    $address = $_POST["address"];
    $birthday = $_POST["birthday"];
    $username = $_POST["username"];
    $password = $_POST["password"];

    if (empty($sno) || empty($name)) {
        $msg = "學號與姓名為必填欄位！";
        $msg_type = "error";
    } else {
        // 檢查學號是否重複
        $check_sql = "SELECT sno FROM students WHERE sno = '$sno'";
        $check_result = mysqli_query($link, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $msg = "錯誤：學號 $sno 已存在！";
            $msg_type = "error";
        } else {
            $sql = "INSERT INTO students (sno, name, address, birthday, username, password) 
                    VALUES ('$sno', '$name', '$address', '$birthday', '$username', '$password')";
            
            if (mysqli_query($link, $sql)) {
                $msg = "新增成功！";
                $msg_type = "success";
                // 成功後清空變數
                $sno = $name = $address = $birthday = $username = $password = "";
            } else {
                $msg = "新增失敗：" . mysqli_error($link);
                $msg_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增通訊錄</title>
    <link rel="stylesheet" href="css/indexCSS.css">
    <link rel="stylesheet" href="css/navCSS.css">
    <link rel="stylesheet" href="css/contacts.css">
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.php"; ?>
        </header>

        <main id="contents">
            <h2>新增通訊錄</h2>
            
            <?php if ($msg != ""): ?>
                <div class="<?php echo ($msg_type=='success') ? 'msg-success' : 'msg-error'; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form id="entryTable" action="add.php" method="post">
                <table>
                    <tr>
                        <th>學號:</th>
                        <td><input type="text" name="sno" value="<?php echo isset($sno)?$sno:''; ?>" required></td>
                    </tr>
                    <tr>
                        <th>姓名:</th>
                        <td><input type="text" name="name" value="<?php echo isset($name)?$name:''; ?>" required></td>
                    </tr>
                    <tr>
                        <th>住址:</th>
                        <td><input type="text" name="address" value="<?php echo isset($address)?$address:''; ?>"></td>
                    </tr>
                    <tr>
                        <th>生日:</th>
                        <td><input type="date" name="birthday" value="<?php echo isset($birthday)?$birthday:''; ?>"></td>
                    </tr>
                    <tr>
                        <th>帳號:</th>
                        <td><input type="text" name="username" value="<?php echo isset($username)?$username:''; ?>"></td>
                    </tr>
                    <tr>
                        <th>密碼:</th>
                        <td><input type="text" name="password" value="<?php echo isset($password)?$password:''; ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="btn-container">
                            <input type="submit" value="新增聯絡資料">
                        </td>
                    </tr>
                </table>
            </form>

            <div class="footer-links" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; text-align: center;">
                <a href="index.php">首頁</a> |
                <a href="add.php">新增聯絡資料</a> |
                <a href="search.php">搜尋通訊錄</a> |
                <a href="contacts.php">回通訊錄頁面</a>
            </div>
            
        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
