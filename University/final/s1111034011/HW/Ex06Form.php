<!-- Ex06Form.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>登入表單</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/itemList.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>

<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php
            require "nav.html";
            ?>
        </header>
        <main id="contents" class="clearheader">
            <h2>使用者註冊</h2>
            <?php
            //$error = ""; // 初始錯誤變數
            //$name = "";  // 保留的欄位值
            //$username = ""; // 保留的欄位值
            
            $error = isset($_GET["error"]) ? $_GET["error"] : "";
            $name = isset($_COOKIE["Name"]) ? $_COOKIE["Name"] : "";
            $username = isset($_COOKIE["UserName"]) ? $_COOKIE["UserName"] : "";

            // 檢查是否有錯誤訊息，或者來自cookie的資料
            if (isset($_COOKIE["Name"]) && isset($_COOKIE["UserName"])) {
                $name = $_COOKIE["Name"];
                $username = $_COOKIE["UserName"];
            }

            if (isset($_GET["error"])) {
                $error = $_GET["error"];
            }
            ?>

            <!-- 顯示錯誤訊息 -->
            <div style="color:red"><?php echo $error ?></div>

            <!-- 表單 -->
            <form action="Ex06Get.php" method="get">
                姓名: <input type="text" name="Name" size="10" value="<?php echo $name ?>" /><br />
                帳號: <input type="text" name="UserName" size="10" value="<?php echo $username ?>" /><br />
                請輸入密碼: <input type="password" name="Pass1" size="10" /><br />
                再輸一次密碼: <input type="password" name="Pass2" size="10" /><br /><br />
                <input type="submit" name="Reg" value="註冊使用者" />
            </form>
        </main>
        <footer id="footer">
            <?php
            require "infoFooter.html";
            ?>
        </footer>
    </div>
</body>

</html>