<?php
session_start();
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}

require_once("../DB/DB_open.php");

$playlist_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['sno'];

// Fetch playlist info
$sql = "SELECT * FROM playlists WHERE id = $playlist_id AND user_id = '$user_id'";
$result = mysqli_query($link, $sql);

if (mysqli_num_rows($result) == 0) {
    die("找不到歌單或無權限");
}

$playlist = mysqli_fetch_assoc($result);

// Fetch songs
$sql_songs = "SELECT s.*, ps.id as link_id FROM songs s 
              JOIN playlist_songs ps ON s.id = ps.song_id 
              WHERE ps.playlist_id = $playlist_id 
              ORDER BY ps.sort_order ASC, ps.id ASC";
$result_songs = mysqli_query($link, $sql_songs);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title><?php echo htmlspecialchars($playlist['name']); ?> - 歌單</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
</head>
<body>
    <!-- Nav removed -->

    <div id="content-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <a href="my_playlists.php" style="color: #aaa; text-decoration: none;">&lt; 返回我的歌單</a>
                <h1 style="margin-top: 10px;"><?php echo htmlspecialchars($playlist['name']); ?></h1>
            </div>
            <div style="display: flex; gap: 10px;">
                 <!-- Delete Playlist -->
                 <form action="playlist_act.php" method="post" onsubmit="return confirm('確定要刪除整個歌單嗎？');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="playlist_id" value="<?php echo $playlist_id; ?>">
                    <button type="submit" class="btn-secondary" style="border-color: #d63031; color: #d63031;">刪除歌單</button>
                </form>
                <!-- Play Playlist Button -->
                <button class="btn-primary" onclick="playPlaylist(<?php echo $playlist_id; ?>)">▶ 播放全部</button>
            </div>
        </div>

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>歌曲</th>
                    <th>歌手</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_songs) > 0) {
                    $count = 1;
                    while ($row = mysqli_fetch_assoc($result_songs)) {
                        $cover = "get_cover.php?id=" . $row['id'];
                ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td style="display: flex; align-items: center; gap: 10px;">
                            <img src="<?php echo $cover; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['artist']); ?></td>
                        <td>
                            <form action="playlist_act.php" method="post" style="display: inline;">
                                <input type="hidden" name="action" value="remove_song">
                                <input type="hidden" name="playlist_id" value="<?php echo $playlist_id; ?>">
                                <input type="hidden" name="song_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-secondary" style="font-size: 0.8rem; padding: 4px 8px;">移除</button>
                            </form>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; padding: 30px;'>歌單內沒有歌曲。</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function playPlaylist(pid) {
            // Call parent function to load playlist
            if (window.parent && window.parent.loadQueue) {
                window.parent.loadQueue('playlist', pid);
            } else {
                alert("播放器未就緒");
            }
        }
    </script>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
