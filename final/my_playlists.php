<?php
session_start();
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}

require_once("../DB/DB_open.php");
$user_id = $_SESSION['sno'];
$sql = "SELECT * FROM playlists WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>我的歌單 - 音樂串流平台</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
</head>
<body>
    <div id="content-container" style="margin-top: 20px;">
        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <a href="my_playlists.php" class="btn-primary">所有播放清單</a>
            <a href="playlist_create.php" class="btn-secondary">建立播放清單</a>
            <a href="playlist_search.php" class="btn-secondary">搜尋播放清單</a>
        </div>
        
        <h1>我的播放清單</h1>

        <div class="song-list">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Count songs
                    $pid = $row['id'];
                    $c_sql = "SELECT COUNT(*) as cnt FROM playlist_songs WHERE playlist_id = $pid";
                    $c_res = mysqli_query($link, $c_sql);
                    $c_row = mysqli_fetch_assoc($c_res);
                    $count = $c_row['cnt'];
            ?>
                <div class="song-card" onclick="location.href='playlist_view.php?id=<?php echo $pid; ?>'">
                    <div style="width: 100%; height: 160px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 10px;">
                        <span style="font-size: 3rem;">🎵</span>
                    </div>
                    <div class="song-title"><?php echo htmlspecialchars($row['name']); ?></div>
                    <div class="song-artist"><?php echo $count; ?> 首歌曲</div>
                </div>
            <?php
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center; color: #777;'>尚未建立任何歌單。</p>";
            }
            ?>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
