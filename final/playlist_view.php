<?php
require_once("inc/auth_guard.php");

require_once("../DB/DB_open.php");

$playlist_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['sno'];

// 取得歌單資訊
$sql = "SELECT * FROM playlists WHERE id = $playlist_id AND user_id = '$user_id'";
$result = mysqli_query($link, $sql);

if (mysqli_num_rows($result) == 0) {
    die("找不到歌單或無權限");
}

$playlist = mysqli_fetch_assoc($result);

// 取得歌曲
$sql_songs = "SELECT s.*, ps.id as link_id FROM songs s 
              JOIN playlist_songs ps ON s.id = ps.song_id 
              WHERE ps.playlist_id = $playlist_id 
              ORDER BY ps.sort_order ASC, ps.id ASC";
$result_songs = mysqli_query($link, $sql_songs);
?>
<?php
$page_title = $playlist['name'] . " - 歌單";
require_once("inc/header.php");
?>
    <!-- Nav removed -->

    <div id="content-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; flex-direction: column; align-items: flex-start;">
                <a href="my_playlists.php" style="color: #aaa; text-decoration: none; margin-bottom: 8px; font-size: 0.9rem;">&lt; 返回我的歌單</a>
                <div style="display: flex; align-items: center;">
                    <h1 style="margin: 0;"><?php echo htmlspecialchars($playlist['name']); ?></h1>
                    <button class="btn-secondary" style="padding: 4px 10px; font-size: 0.85rem; margin-left: 15px; border-radius: 20px; border: 1px solid #555;" onclick="openRenameModal(<?php echo $playlist_id; ?>, '<?php echo htmlspecialchars($playlist['name'], ENT_QUOTES); ?>')">✎ 編輯</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                 <!-- 刪除歌單 -->
                 <form action="playlist_act.php" method="post" onsubmit="return confirm('確定要刪除整個歌單嗎？');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="playlist_id" value="<?php echo $playlist_id; ?>">
                    <button type="submit" class="btn-secondary" style="border-color: #d63031; color: #d63031;">刪除歌單</button>
                </form>
                <!-- 播放歌單按鈕 -->
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
                        <td style="display: flex; align-items: center; gap: 10px; cursor: pointer;"
                            onclick="playContextSong('<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>', 
                                              '<?php echo htmlspecialchars($row['artist'], ENT_QUOTES); ?>', 
                                              'music/<?php echo $row['file_path']; ?>', 
                                              '<?php echo $cover; ?>', 
                                              <?php echo $row['id']; ?>,
                                              'playlist',
                                              <?php echo $playlist_id; ?>,
                                              '<?php echo htmlspecialchars($playlist['name'], ENT_QUOTES); ?>')"
                            title="點擊播放">
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

    <script src="js/player_bridge.js?v=5"></script>
    <script>
        // Auto-context setting REMOVED to separate viewing from playback logic
        
        function playPlaylist(pid) {
            // Update context manually when "Play All" is clicked
            if (window.parent && window.parent.setPlaylistContext) {
                 window.parent.setPlaylistContext(pid, '<?php echo htmlspecialchars($playlist['name'], ENT_QUOTES); ?>');
            }
            
            // Call loadQueue
            if (window.parent && window.parent.loadQueue) {
                window.parent.loadQueue('playlist', pid, 0); // 0 means start from beginning
            } else {
                alert("播放器未就緒");
            }
        }
    </script>

    <!-- Rename Modal -->
    <div id="rename-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: #282828; padding: 20px; border-radius: 8px; width: 300px; text-align: center;">
            <h3>重新命名播放清單</h3>
            <form id="rename-form" onsubmit="return submitRename()">
                <input type="hidden" id="rename-playlist-id">
                <input type="text" id="rename-input" style="width: 100%; padding: 10px; margin-bottom: 20px; background: #444; color: white; border: none; border-radius: 4px; box-sizing: border-box;" placeholder="輸入新名稱" required>
                <div style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('rename-modal').style.display='none'">取消</button>
                    <button type="submit" class="btn-primary">儲存</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRenameModal(id, currentName) {
            document.getElementById('rename-modal').style.display = 'flex';
            document.getElementById('rename-playlist-id').value = id;
            document.getElementById('rename-input').value = currentName;
            document.getElementById('rename-input').focus();
        }

        async function submitRename() {
            event.preventDefault();
            const id = document.getElementById('rename-playlist-id').value;
            const name = document.getElementById('rename-input').value;
            
            const formData = new FormData();
            formData.append('action', 'rename');
            formData.append('playlist_id', id);
            formData.append('name', name);
            formData.append('ajax', '1');

            try {
                const response = await fetch('playlist_act.php', {
                    method: 'POST',
                    body: formData
                });
                const text = await response.text();
                
                if (text.trim() === 'SUCCESS') {
                    location.reload();
                } else {
                    alert('更名失敗: ' + text);
                }
            } catch (err) {
                console.error(err);
                alert('發生錯誤');
            }
            return false;
        }
    </script>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
