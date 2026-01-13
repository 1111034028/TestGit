<?php
require_once("inc/auth_guard.php");

require_once("../DB/DB_open.php");
$user_id = $_SESSION['sno'];
$sql = "SELECT * FROM playlists WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($link, $sql);
?>
<?php 
$page_title = "我的歌單 - 音樂串流平台";
require_once("inc/header.php"); 
?>
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
                    // 計算歌曲數
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
                    <div class="song-artist" style="display: flex; justify-content: space-between; align-items: center;">
                        <span><?php echo $count; ?> 首歌曲</span>
                        <button class="btn-secondary" style="padding: 4px 8px; font-size: 0.8rem;" onclick="openRenameModal(<?php echo $pid; ?>, '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>')">⚙ 設定</button>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center; color: #777;'>尚未建立任何歌單。</p>";
            }
            ?>
        </div>
    </div>
    
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
            event.stopPropagation(); // Prevent card click
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
