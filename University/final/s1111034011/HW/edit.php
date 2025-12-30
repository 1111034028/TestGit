<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>期末報告</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php include "nav.html"; ?>
        </header>
        <main id="contents">
            <h2>編輯通訊錄</h2>
            <?php
                $id = $_GET["id"];
                $action = $_GET["action"];
                require_once("DB_open.php");
                switch ($action){
                    case "update":
                        $sno = $_POST["sno"];
                        $name = $_POST["name"];
                        $address = $_POST["address"];
                        $birthday = $_POST["birthday"];
                        $username = $_POST["username"];
                        $password = $_POST["password"];
                        $sql = "UPDATE students SET sno='".$id."', name='".$name."', address='".$address."', birthday='".$birthday."', username='".$username."', password='".$password."' WHERE sno='$id'";
                        if (!mysqli_query($link, $sql)) {
                            die("SQL 錯誤: " . mysqli_error($link));
                        }
                        mysqli_query($link, $sql);
                        header("Location: contacts.php");
                        break;
                    case "del":
                        $sql = "DELETE FROM students WHERE sno='".$id."'";
                        mysqli_query($link, $sql);
                        header("Location: contacts.php");
                        break;
                    case "edit":
                        $sql = "SELECT * FROM students WHERE sno='".$id."'";
                        $result = mysqli_query($link, $sql);
                        if (!$result) {
                            die("SQL 查詢失敗: " . mysqli_error($link));
                        }
                        $row = mysqli_fetch_assoc($result);
                        $number = $row['sno'];
                        $name = $row['name'];
                        $address = $row['address'];
                        $birthday = $row['birthday'];
                        $username = $row['username'];
                        $password = $row['password'];
            ?>
            <form action="edit.php?action=update&id=<?php echo $id ?>" method="post">
                <table border="1" style='margin: 0 auto'>
                <tr><td><font size="2">學號: </font></td>
                    <td><input type="text" name="id" size="20" maxlength="10" value="<?php echo $id?>"></td></tr>
                    <tr><td><font size="2">姓名: </font></td>
                    <td><input type="text" name="name" size="20" maxlength="10" value="<?php echo $name?>"></td></tr>
                    <tr><td><font size="2">住址: </font></td>
                    <td><input type="text" name="address" size="20" maxlength="20" value="<?php echo $address?>"></td></tr>
                    <tr><td><font size="2">生日: </font></td>
                    <td><input type="date" name="birthday" value="<?php echo $birthday?>"></td></tr>
                    <tr><td><font size="2">帳號: </font></td>
                    <td><input type="text" name="username" size="20" maxlength="10" value="<?php echo $username?>"></td></tr>
                    <tr><td><font size="2">密碼: </font></td>
                    <td><input type="text" name="password" size="20" maxlength="10" value="<?php echo $password?>"></td></tr>
                    <tr><td colspan="2" align="center"><input type="submit" value="更新聯絡資料"></td></tr>
                </table>
            </form>
            <hr/><a href="contacts.php">首頁</a>
            <a href="add.php">新增聯絡資料</a>
            <a href="search.php">搜尋通訊錄</a>
            <?php
                        break;
                }
                require_once("DB_close.php")  
            ?>
        </main>
        <footer id="footer">
            <?php include "infoFooter.html"; ?>
        </footer>
</div>
</body>
</html>
