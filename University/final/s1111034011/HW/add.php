<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>新增聯絡資料</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>

<body>
    <div id="wrap">
        <!-- 功能列表 -->
        <header id="header" class="clearheader">
            <?php
            include "nav.html";
            ?>
        </header>
        <!-- 內容區域 -->
        <main id="contents">
            <h2>新增通訊錄</h2>
            <center>
                <?Php
                if (
                    isset($_POST["sno"]) && isset($_POST["name"]) && isset($_POST["address"]) && isset($_POST["birthday"])
                    && isset($_POST["username"]) && isset($_POST["password"])
                ) {
                    $sno = $_POST["sno"];
                    $name = $_POST["name"];
                    $address = $_POST["address"];
                    $birthday = $_POST["birthday"];
                    $username = $_POST["username"];
                    $password = $_POST["password"];
                    if ($sno != "" && $name != "" && $address != "" && $birthday != "" && $username != "" && $password != "") {
                        require_once("DB_open.php");
                        $sql = "INSERT INTO students (sno, name, address, birthday, username, password) 
                        VALUES ('" . $sno . "', '" . $name . "', '" . $address . "', '" . $birthday . "', '" . $username . "', '" . $password . "')";
                        if (mysqli_query($link, $sql)) {
                            echo "<font>新增聯絡資訊成功!</font><br/>";
                        }
                        require_once("DB_close.php");
                    }
                }
                ?>
                <form action="add.php" method="post">
                    <table border="1" width="300">
                        <tr>
                            <td>
                                <font size="2">學號</font>
                            </td>
                            <td><input type="text" name="sno" size="20" maxlength="10" /></td>
                        </tr>
                        <tr>
                            <td>
                                <font size="2">姓名</font>
                            </td>
                            <td><input type="text" name="name" size="20" maxlength="10" /></td>
                        </tr>
                        <tr>
                            <td>
                                <font size="2">住址</font>
                            </td>
                            <td><input type="text" name="address" size="20" maxlength="10" /></td>
                        </tr>
                        <tr>
                            <td>
                                <font size="2">生日</font>
                            </td>
                            <td><input type="date" name="birthday" size="20" maxlength="10" /></td>
                        </tr>
                        <tr>
                            <td>
                                <font size="2">帳號</font>
                            </td>
                            <td><input type="text" name="username" size="20" maxlength="10" /></td>
                        </tr>
                        <tr>
                            <td>
                                <font size="2">密碼</font>
                            </td>
                            <td><input type="text" name="password" size="20" maxlength="10" /></td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="submit" value="新增聯絡資料" />
                            </td>
                        </tr>
                    </table>
                </form>
                <hr /><a href="contacts.php"> 首頁 |</a>
                <a href="add.php"> 新增聯絡資料 |</a>
                <a href="search.php"> 搜尋通訊錄 </a>
            </center>
        </main>
        <!-- 頁尾資訊 -->
        <footer id="footer">
            <?php
            require "infoFooter.html";
            ?>
        </footer>
    </div>
</body>

</html>