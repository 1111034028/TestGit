<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>使用者註冊</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link rel="stylesheet" href="css/getForm.css" media="all">
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.html"; ?>
        </header>

        <main id="contents">
            <?php
            // 讀取 Cookie (如果存在)
            $name = isset($_COOKIE['userName']) ? $_COOKIE['userName'] : "";
            $address = isset($_COOKIE['address']) ? $_COOKIE['address'] : "";
            $birthday = isset($_COOKIE['birthday']) ? $_COOKIE['birthday'] : "";
            $username = isset($_COOKIE['username']) ? $_COOKIE['username'] : "";
            ?>

            <h2>使用者註冊</h2>
            
            <div class="container">
                <form action="getData.php" method="post" class="form-box">
                    <div class="form-group">
                        <label for="name">姓名 (Name)</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required placeholder="請輸入真實姓名">
                    </div>
                    <div class="form-group">
                        <label for="address">聯絡地址 (Address)</label>
                        <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($address); ?>" required placeholder="請輸入聯絡地址">
                    </div>
                    <div class="form-group">
                        <label for="birthday">出生日期 (Birthday)</label>
                        <input type="date" name="birthday" id="birthday" value="<?php echo htmlspecialchars($birthday); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="username">使用者帳號 (Account)</label>
                        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required placeholder="用於登入系統">
                    </div>
                    <div class="form-group">
                        <label for="pw">登入密碼 (Password)</label>
                        <input type="password" name="pw" id="pw" required placeholder="請設定 6-10 位密碼">
                    </div>
                    <div class="form-group">
                        <label for="pw2">密碼確認 (Confirm)</label>
                        <input type="password" name="pw2" id="pw2" required placeholder="請再次輸入密碼">
                    </div>
                    
                    <button type="submit" class="submit-btn">註冊</button>
                    
                    <div style="text-align: center;">
                        <a href="index.php" class="back-link">← 取消並回到首頁</a>
                    </div>
                </form>
            </div>
        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>
</body>
</html>
