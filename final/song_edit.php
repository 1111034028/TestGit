<?php
session_start();
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}

require_once("../DB/DB_open.php");

// 確保有傳入 ID
if (!isset($_GET['id'])) {
    header("Location: creator.php");
    exit;
}

$song_id = $_GET['id'];
$username = $_SESSION["username"];

// 驗證歌曲是否屬於該使用者 或者 使用者是管理員
// 先抓出使用者 sno 和 role
$sql_user = "SELECT sno, role FROM students WHERE username = '$username'";
$result_user = mysqli_query($link, $sql_user);
$row_user = mysqli_fetch_assoc($result_user);
$sno = $row_user['sno'];
$role = $row_user['role'] ?? 'user';

// 查詢歌曲資料
// 如果是管理員，不限制 uploader_id
if ($role === 'admin') {
    $sql_song = "SELECT * FROM songs WHERE id = '$song_id'";
} else {
    $sql_song = "SELECT * FROM songs WHERE id = '$song_id' AND uploader_id = '$sno'";
}
$result_song = mysqli_query($link, $sql_song);

if (mysqli_num_rows($result_song) == 0) {
    // 找不到歌曲或權限不足
    echo "無權編輯此歌曲或歌曲不存在。<a href='creator.php'>返回</a>";
    exit;
}

$song = mysqli_fetch_assoc($result_song);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>編輯歌曲 - 創作者工作室</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
</head>
<body>

    <div id="content-container" style="max-width: 600px;">
        <h2>編輯歌曲資訊</h2>
        
        <form action="song_edit_act.php" method="post" enctype="multipart/form-data" class="music-form">
            <input type="hidden" name="id" value="<?php echo $song['id']; ?>">
            
            <div class="form-group">
                <label>歌名 (Title)</label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($song['title']); ?>">
            </div>

            <div class="form-group">
                <label>歌手/創作者 (Artist)</label>
                <input type="text" name="artist" required value="<?php echo htmlspecialchars($song['artist']); ?>">
            </div>

            <div class="form-group">
                <label>更換音樂檔案 (選填)</label>
                <div style="font-size: 0.8rem; color: #888; margin-bottom: 5px;">目前檔案：<?php echo $song['file_path']; ?></div>
                <input type="file" name="music_file" accept=".mp3,.wav,.ogg" style="background: #282828;">
                <small style="color: #aaa;">若不需更換請留空</small>
            </div>

            <div class="form-group">
                <label>更換封面圖片 (選填)</label>
                <?php 
                    $cover_show = "get_cover.php?id=" . $song['id'];
                ?>
                <img id="preview-img" src="<?php echo $cover_show; ?>" style="width: 100px; height: 100px; object-fit: cover; margin-bottom: 5px; display: block; border-radius: 4px; border: 1px solid #333;">
                <input type="file" name="cover_file" id="cover-input" accept="image/*" style="background: #282828;">
                <small style="color: #aaa;">若不需更換請留空</small>
            </div>
            
            <script>
                document.getElementById('cover-input').addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.getElementById('preview-img').src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                });
            </script>
            
            <div class="form-group">
                <label>音樂類型 (Genre)</label>
                <select name="genre">
                    <option value="Pop" <?php if($song['genre'] == 'Pop') echo 'selected'; ?>>Pop</option>
                    <option value="J-Pop" <?php if($song['genre'] == 'J-Pop') echo 'selected'; ?>>J-Pop</option>
                    <option value="K-Pop" <?php if($song['genre'] == 'K-Pop') echo 'selected'; ?>>K-Pop</option>
                    <option value="Rock" <?php if($song['genre'] == 'Rock') echo 'selected'; ?>>Rock</option>
                    <option value="Hip-Hop" <?php if($song['genre'] == 'Hip-Hop') echo 'selected'; ?>>Hip-Hop</option>
                    <option value="Electronic" <?php if($song['genre'] == 'Electronic') echo 'selected'; ?>>Electronic</option>
                    <option value="R&B" <?php if($song['genre'] == 'R&B') echo 'selected'; ?>>R&B</option>
                    <option value="Classical" <?php if($song['genre'] == 'Classical') echo 'selected'; ?>>Classical</option>
                    <option value="Jazz" <?php if($song['genre'] == 'Jazz') echo 'selected'; ?>>Jazz</option>
                    <option value="Other" <?php if($song['genre'] == 'Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>

            <div style="margin-top: 30px; display: flex; justify-content: flex-end; align-items: center;">
                <a href="creator.php" style="color: var(--text-secondary); text-decoration: none; margin-right: 20px;">取消</a>
                <button type="submit" class="btn-primary">儲存更新</button>
            </div>
        </form>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
