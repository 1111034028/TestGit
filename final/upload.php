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
    <title>上傳歌曲 - 創作者工作室</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
</head>
<body>

    <div id="content-container" style="max-width: 600px;">
        <h2>上傳新歌曲</h2>
        
        <form action="song_upload_act.php" method="post" enctype="multipart/form-data" class="music-form">
            <div class="form-group">
                <label>歌名 (Title)</label>
                <input type="text" name="title" required placeholder="請輸入歌曲名稱">
            </div>

            <div class="form-group">
                <label>歌手/創作者 (Artist)</label>
                <input type="text" name="artist" required value="<?php echo $_SESSION['username']; ?>">
            </div>

            <div class="form-group">
                <label>音樂檔案 (MP3/WAV)</label>
                <input type="file" name="music_file" required accept=".mp3,.wav,.ogg" style="background: #282828;">
            </div>

            <div class="form-group">
                <label>封面圖片 (Optional)</label>
                <input type="file" name="cover_file" accept="image/*" style="background: #282828;">
            </div>
            
            <div class="form-group">
                <label>音樂類型 (Genre)</label>
                <select name="genre">
                    <option value="Pop">Pop</option>
                    <option value="J-Pop">J-Pop</option>
                    <option value="K-Pop">K-Pop</option>
                    <option value="Rock">Rock</option>
                    <option value="Hip-Hop">Hip-Hop</option>
                    <option value="Electronic">Electronic</option>
                    <option value="R&B">R&B</option>
                    <option value="Classical">Classical</option>
                    <option value="Jazz">Jazz</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div style="margin-top: 30px; display: flex; justify-content: flex-end; align-items: center;">
                <a href="creator.php" style="color: var(--text-secondary); text-decoration: none; margin-right: 20px;">取消</a>
                <button type="submit" class="btn-primary">上傳發布</button>
            </div>
        </form>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
