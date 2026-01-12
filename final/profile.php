<?php
session_start();
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}

require_once("../DB/DB_open.php");

$username = $_SESSION["username"];
$sql = "SELECT * FROM students WHERE username = '$username'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);

// Prevent XSS
$name = htmlspecialchars($row['name']);
$address = htmlspecialchars($row['address']);
$birthday = htmlspecialchars($row['birthday']);
$password = htmlspecialchars($row['password']);
$sno = htmlspecialchars($row['sno']);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>個人資料 - 音樂串流平台</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
            background: var(--bg-card);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--text-secondary); }
        .form-group input { 
            width: 100%; 
            padding: 10px; 
            border-radius: 4px; 
            border: 1px solid #444; 
            background: #282828; 
            color: white; 
            font-size: 1rem;
        }
        .form-group input:focus { outline: none; border-color: var(--accent-color); }
        .form-group input[readonly] { background: #1a1a1a; color: #777; cursor: not-allowed; }
        
        .btn-save {
            background: var(--accent-color);
            color: black;
            border: none;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn-save:hover { transform: scale(1.02); }
    </style>
</head>
<body>
    <!-- Nav removed for App Shell integration -->

    <div id="content-container" style="padding-top: 20px;">
        <h1 style="text-align: center; margin-bottom: 30px;">個人資料設定</h1>
        
        <div class="profile-container">
            <form action="profile_act.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="sno" value="<?php echo $sno; ?>">
                
                <div style="text-align: center; margin-bottom: 20px;">
                    <?php 
                        $pic_path = isset($row['picture']) && $row['picture'] ? "img/avatars/" . $row['picture'] : null;
                    ?>
                    <div id="avatar-preview-container" style="width: 100px; height: 100px; border-radius: 50%; background: #535c68; margin: 0 auto; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                        <?php if ($pic_path && file_exists($pic_path)): ?>
                            <img src="<?php echo $pic_path; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span style="font-size: 2.5rem; color: white; font-weight: bold;">
                                <?php echo strtoupper(substr($username, 0, 1)); ?>
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
                    <input type="text" name="username" value="<?php echo $username; ?>" required>
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
    
    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    // Use ID for robust selection
                    const container = document.getElementById('avatar-preview-container');
                    
                    // Check if img exists inside
                    let img = container.querySelector('img');
                    if (!img) {
                        // If showing initials (text), clear it and add img
                        container.innerHTML = '';
                        img = document.createElement('img');
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        container.appendChild(img);
                    }
                    img.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
