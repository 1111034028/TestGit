<?php
session_start();
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>建立新歌單 - 音樂串流平台</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
</head>
<body>
    <!-- Nav removed -->

    <div id="content-container">
        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <a href="my_playlists.php" class="btn-secondary">所有播放清單</a>
            <a href="playlist_create.php" class="btn-primary">建立播放清單</a>
            <a href="playlist_search.php" class="btn-secondary">搜尋播放清單</a>
        </div>

        <h1>建立新播放清單</h1>
        
        <div style="background: var(--bg-hover); padding: 40px; border-radius: 8px; max-width: 600px; margin: 0 auto;">
            <form action="playlist_act.php" method="post">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label style="display: block; margin-bottom: 10px; font-size: 1.2rem;">播放清單名稱</label>
                    <input type="text" name="name" required placeholder="例如：開車必聽、讀書輕音樂..." 
                           style="width: 100%; padding: 15px; border-radius: 4px; border: 1px solid #444; background: #282828; color: white; font-size: 1.1rem;">
                </div>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn-primary" style="padding: 10px 40px; font-size: 1.1rem;">立即建立</button>
                </div>
            </form>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
