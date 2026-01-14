<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

$username_session = $_SESSION["username"];
$row = get_user_info($link, $username_session);

if (!$row) die("找不到使用者資料");

// 防止 XSS
$name = htmlspecialchars($row['name']);
$address = htmlspecialchars($row['address']);
$birthday = htmlspecialchars($row['birthday']);
$password = htmlspecialchars($row['password']);
$sno = htmlspecialchars($row['sno']);

$page_title = "個人資料 - 音樂串流平台";
$extra_css = '<link rel="stylesheet" href="css/profile.css">';
require_once("inc/header.php");
?>
    <div id="content-container" style="padding-top: 20px;">
        <h1 style="text-align: center; margin-bottom: 30px;">個人資料設定</h1>
        
        <div class="profile-container">
            <form action="profile_act.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="sno" value="<?php echo $sno; ?>">
                
                <div style="text-align: center; margin-bottom: 20px;">
                    <?php 
                        $pic_path = isset($row['picture']) && $row['picture'] ? "img/avatars/" . $row['picture'] : null;
                    ?>
                    <div id="avatar-preview-container">
                        <?php if ($pic_path && file_exists($pic_path)): ?>
                            <img src="<?php echo $pic_path; ?>" alt="Avatar">
                        <?php else: ?>
                            <span style="font-size: 2.5rem; color: white; font-weight: bold;">
                                <?php echo strtoupper(substr($username_session, 0, 1)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <label for="avatar-input" style="color: var(--accent-color); cursor: pointer; display: block; margin-top: 10px; font-size: 0.9rem;">更換頭像</label>
                    <input type="file" name="avatar" id="avatar-input" style="display: none;" accept="image/*" onchange="previewAvatar(this)">
                </div>

                <div class="form-group">
                    <label>會員編號 (唯讀)</label>
                    <input type="text" value="<?php echo $sno; ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label>登入帳號</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($username_session); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>真實姓名</label>
                    <input type="text" name="name" value="<?php echo $name; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>通訊地址</label>
                    <input type="text" name="address" value="<?php echo $address; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>生日</label>
                    <input type="date" name="birthday" value="<?php echo $birthday; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>密碼</label>
                    <input type="text" name="password" value="<?php echo $password; ?>" required>
                    <small style="color: #aaa; display: block; margin-top: 5px;">建議定期更換密碼以確保安全</small>
                </div>
                
                <button type="submit" class="btn-save">儲存變更</button>
            </form>
        </div>
    </div>
    
    <script src="js/manage_notifications.js"></script>
    <script src="js/profile.js"></script>

    <?php include "inc/modal.php"; ?>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
