<?php session_start(); ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>建立帳戶 - 音樂串流平台</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <link rel="stylesheet" href="css/login.css">
    <style>
        /* 註冊頁面調整 */
        .login-card {
            max-width: 400px; /* 稍微加寬 */
        }
        .login-container { /* 修改自 #content-container */
            max-width: 600px;
        }
    </style>
</head>
<body class="login-body">
    <!-- 移除導覽列 -->

    <div class="login-container">
        <h1 style="text-align:center;">註冊新帳戶</h1>
        <div class="login-card">
            <?php
            if (isset($_SESSION["reg_msg"])) {
                $msg_color = $_SESSION["reg_status"] == 'success' ? '#00b894' : '#e84393';
                echo '<div style="color: '.$msg_color.'; text-align:center; margin-bottom:15px; font-weight:bold;">' . $_SESSION["reg_msg"] . '</div>';
                unset($_SESSION["reg_msg"]);
                unset($_SESSION["reg_status"]);
            }
            ?>

            <form action="register_act.php" method="post">
                <div class="form-group">
                    <label>學號 (ID)</label>
                    <input type="text" name="sno" required placeholder="例如: S007 (4碼)" maxlength="4">
                </div>
                
                <div class="form-group">
                    <label>姓名</label>
                    <input type="text" name="name" required placeholder="您的真實姓名" maxlength="10">
                </div>

                <div class="form-group">
                    <label>帳號 (Username)</label>
                    <input type="text" name="username" required placeholder="登入用帳號" maxlength="10">
                </div>

                <div class="form-group">
                    <label>密碼 (Password)</label>
                    <input type="password" name="password" required placeholder="設定密碼" maxlength="10">
                </div>

                <div class="form-group">
                    <label>生日</label>
                    <input type="date" name="birthday" required>
                </div>

                <div class="form-group">
                    <label>地址</label>
                    <input type="text" name="address" required placeholder="居住城市">
                </div>

                <button type="submit" class="submit-btn" style="margin-top: 15px;">註冊並加入</button>
            </form>
            
            <div style="text-align: center; margin-top: 25px;">
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    已經有帳號？ <a href="login.php" style="color: var(--primary-accent); font-weight:700;">直接登入</a>
                </p>
            </div>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
