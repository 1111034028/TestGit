<?php require_once('../final/inc/auth_guard.php'); ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>註冊結果</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link rel="stylesheet" href="css/getForm.css" media="all">
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.php"; ?>
        </header>

        <main id="contents">
            <div class="container message-box">
            <?php
                require "../DB/DB_open.php";

                $name = $_POST['name'] ?? "";
                $address = $_POST['address'] ?? "";
                $birthday = $_POST['birthday'] ?? "";
                $username = $_POST['username'] ?? "";
                $pw = $_POST['pw'] ?? "";
                $pw2 = $_POST['pw2'] ?? "";

                if ($pw !== $pw2) {
                    setcookie("userName", $name, time() + 60);
                    setcookie("address", $address, time() + 60);
                    setcookie("birthday", $birthday, time() + 60);
                    setcookie("username", $username, time() + 60);

                    echo "<div class='error-title'>Oops! 密碼不一致</div>";
                    echo "<p style='color:#636e72'>請確認您兩次輸入的密碼完全相同。</p>";
                    echo "<a href='getForm.php' class='submit-btn' style='text-decoration:none; display:block;'>返回修正資料</a>";
                } else {
                    // 重複性檢查：姓名、地址、日期、帳號、密碼皆相同
                    $sql_check = "SELECT * FROM students WHERE 
                                  name = '$name' AND 
                                  address = '$address' AND 
                                  birthday = '$birthday' AND 
                                  username = '$username' AND 
                                  password = '$pw'";
                    $res_check = mysqli_query($link, $sql_check);

                    if ($res_check && mysqli_num_rows($res_check) > 0) {
                        echo "<div class='error-title'>使用者已存在</div>";
                        echo "<p style='color:#636e72'>您輸入的資料與系統內現有帳戶完全吻合，無需重複註冊。</p>";
                        echo "<a href='getForm.php' class='submit-btn' style='text-decoration:none; display:block;'>返回註冊頁面</a>";
                    } else {
                        // 無重複，執行原有的學號生成與寫入邏輯
                        $sql_max = "SELECT sno FROM students ORDER BY sno DESC LIMIT 1";
                        $res_max = mysqli_query($link, $sql_max);
                        if ($res_max && mysqli_num_rows($res_max) > 0) {
                            $row_max = mysqli_fetch_assoc($res_max);
                            $last_sno = $row_max['sno'];
                            $num = intval(substr($last_sno, 1)) + 1;
                            $sno = "S" . str_pad($num, 3, "0", STR_PAD_LEFT);
                        } else {
                            $sno = "S001";
                        }

                        $sql = "INSERT INTO students (sno, name, address, birthday, username, password) 
                                VALUES ('$sno', '$name', '$address', '$birthday', '$username', '$pw')";
                        
                        if (mysqli_query($link, $sql)) {
                            setcookie("userName", "", time() - 3600);
                            setcookie("address", "", time() - 3600);
                            setcookie("birthday", "", time() - 3600);
                            setcookie("username", "", time() - 3600);

                            echo "<div class='success-badge'>✓ 註冊完成</div>";
                            echo "<h1>Welcome to ID:</h1>";
                            echo "<span class='assigned-sno'>" . htmlspecialchars($sno) . "</span>";
                            
                            echo "<div class='data-row'><span class='data-label'>姓名</span><span class='data-value'>" . htmlspecialchars($name) . "</span></div>";
                            echo "<div class='data-row'><span class='data-label'>用戶帳號</span><span class='data-value'>" . htmlspecialchars($username) . "</span></div>";
                            echo "<div class='data-row'><span class='data-label'>系統狀態</span><span class='data-value'>已於資料庫建立帳戶</span></div>";

                            echo "<a href='index.php' class='submit-btn' style='text-decoration:none; display:block; margin-top:30px;'>回到首頁</a>";
                        } else {
                            echo "<div class='error-title'>註冊失敗</div>";
                            echo "<p>" . mysqli_error($link) . "</p>";
                            echo "<a href='getForm.php' class='back-link'>回到註冊頁</a>";
                        }
                    }
                }
                require "../DB/DB_close.php";
            ?>
            </div>
        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>
</body>
</html>
