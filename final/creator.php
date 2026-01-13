<?php require_once("inc/auth_guard.php");

require_once("../DB/DB_open.php");


$username = $_SESSION["username"];
$sql_user = "SELECT sno FROM students WHERE username = '$username'";
$result_user = mysqli_query($link, $sql_user);
$row_user = mysqli_fetch_assoc($result_user);
$sno = $row_user['sno'];

// 讀取該使用者上傳的歌曲
$sql_songs = "SELECT * FROM songs WHERE uploader_id = '$sno' ORDER BY upload_date DESC";
$result_songs = mysqli_query($link, $sql_songs);
?>
<?php 
$page_title = "創作者工作室 - 音樂串流平台";
require_once("inc/header.php"); 
?>
<script src="js/player_bridge.js"></script>
    <div id="content-container" style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1>創作者工作室</h1>
            <a href="upload.php" class="btn-primary">上傳新歌曲</a>
        </div>

        <div style="background: var(--bg-hover); padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3 style="margin-top: 0;">數據概覽</h3>
            <div style="display: flex; gap: 40px;">
                <div>
                    <span style="color: var(--text-secondary); font-size: 0.9rem;">總上傳歌曲</span>
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo mysqli_num_rows($result_songs); ?></div>
                </div>
                <!-- 未來可加入更多數據 -->
            </div>
        </div>

        <h3>已發布的歌曲</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>封面</th>
                    <th>標題</th>
                    <th>播放次數</th>
                    <th>上傳日期</th>
                    <th>功能</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_songs) > 0) {
                    $count = 1;
                    while ($row = mysqli_fetch_assoc($result_songs)) {
                        $upload_date = date("M d, Y", strtotime($row['upload_date']));
                        $cover = "get_cover.php?id=" . $row['id'];
                ?>
                    <tr>
                        <td style="color: var(--text-secondary);"><?php echo $count++; ?></td>
                        <td>
                            <img src="<?php echo $cover; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td>
                            <div style="font-weight: bold;"><?php echo htmlspecialchars($row['title']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo htmlspecialchars($row['artist']); ?></div>
                        </td>
                        <td><?php echo $row['play_count']; ?></td>
                        <td style="color: var(--text-secondary);"><?php echo $upload_date; ?></td>
                        <td>
                            <a href="song_edit.php?id=<?php echo $row['id']; ?>" class="btn-secondary" style="font-size: 0.7rem; padding: 4px 10px;">編輯</a>
                            <a href="song_delete.php?id=<?php echo $row['id']; ?>" class="btn-secondary" style="font-size: 0.7rem; padding: 4px 10px; border-color: #d63031; color: #d63031;" onclick="return confirm('確定要刪除這首歌嗎？此動作無法復原。')">刪除</a>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding: 30px;'>目前沒有上傳任何歌曲。</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php include "foot.html"; ?>
    <script>
        // playSong removed - using js/player_bridge.js
    </script>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
