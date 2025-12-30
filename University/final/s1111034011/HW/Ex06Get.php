<!-- Ex06Get.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>期末作業</title>
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
        <main id="contents">
            <?php
            $error = "";   // 初始錯誤變數
            $msg = "";     // 顯示的訊息
            $showform = true; // 預設顯示表單
            
            // 取得表單欄位值
            $name = isset($_GET["Name"]) ? $_GET["Name"] : "";
            $username = isset($_GET["UserName"]) ? $_GET["UserName"] : "";
            $pass1 = isset($_GET["Pass1"]) ? $_GET["Pass1"] : "";
            $pass2 = isset($_GET["Pass2"]) ? $_GET["Pass2"] : "";

            // 檢查帳號欄位是否有輸入資料
            if (isset($_GET["Reg"])) {
                // 設置 cookie 來保存使用者填寫的資料
                setcookie("Name", $name, time() + 3600);  // 保存1小時
                setcookie("UserName", $username, time() + 3600);  // 保存1小時
                
                if (empty($username)) {
                    // 欄位沒填
                    $error = "帳號欄位空白<br/>";
                } else {
                    if (empty($pass1)) {
                        // 密碼欄位沒填
                        $error = "密碼欄位空白<br/>";
                    } else {
                        // 檢查兩次密碼是否相同
                        if ($pass1 != $pass2) {
                            // 密碼錯誤
                            $error = "密碼輸入不相同<br/>";
                        } else {
                            // 沒有錯誤，顯示資料
                            $showform = false;
                            $msg = "姓名: " . $name . "<br/>";
                            $msg .= "帳號: " . $username . "<br/>";
                            $msg .= "密碼: " . $pass1 . "<br/>";
                        }
                    }
                }
            }

            // 如果有錯誤，顯示錯誤訊息
            if ($showform) {
                echo "<div style='color:red'>$error</div>";
                echo "<a href='Ex06Form.php?error=$error'>回到註冊頁</a>";
            } else {
                // 顯示表單處理結果
                echo $msg;
                echo "<a href='Ex06Form.php?error=$error'>回到註冊頁</a>";
            }
            ?>
        </main>
        <footer id="footer">
            <?php
            require "infoFooter.html";
            ?>
        </footer>
    </div>
</body>

</html>