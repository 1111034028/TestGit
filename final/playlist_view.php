<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

$playlist_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['sno'];

$playlist = get_playlist_info($link, $playlist_id, $user_id);
if (!$playlist) {
    die("找不到歌單或無權限");
}

$result_songs = get_playlist_songs($link, $playlist_id);

$page_title = $playlist['name'] . " - 歌單";
require_once("inc/header.php");
require_once("inc/modal.php");
?>

    <div id="content-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; flex-direction: column; align-items: flex-start;">
                <a href="my_playlists.php" style="color: #aaa; text-decoration: none; margin-bottom: 8px; font-size: 0.9rem;">&lt; 返回我的歌單</a>
                <div style="display: flex; align-items: center;">
                    <h1 style="margin: 0;"><?php echo htmlspecialchars($playlist['name']); ?></h1>
                    <?php if ($playlist['name'] !== 'My Favorites'): ?>
                        <button class="btn-secondary" style="padding: 4px 10px; font-size: 0.85rem; margin-left: 15px; border-radius: 20px; border: 1px solid #555;" 
                                onclick='openRenameModal(<?php echo $playlist_id; ?>, <?php echo json_encode($playlist["name"]); ?>)'>✎ 編輯</button>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                 <?php if ($playlist['name'] !== 'My Favorites'): ?>
                     <form action="playlist_act.php" method="post" onsubmit="event.preventDefault(); const form = this; openModal('刪除歌單', '確定要刪除整個歌單嗎？', () => form.submit(), true);">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="playlist_id" value="<?php echo $playlist_id; ?>">
                        <button type="submit" class="btn-secondary" style="border-color: #d63031; color: #d63031;">刪除歌單</button>
                    </form>
                <?php endif; ?>
                <button class="btn-primary" onclick='playPlaylist(<?php echo $playlist_id; ?>, <?php echo json_encode($playlist["name"]); ?>)'>▶ 播放全部</button>
            </div>
        </div>

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>歌曲</th>
                    <th>歌手</th>
                    <th width="80">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_songs && mysqli_num_rows($result_songs) > 0) {
                    $count = 1;
                    while ($row = mysqli_fetch_assoc($result_songs)) {
                        $cover = "get_cover.php?id=" . $row['id'];
                        $is_pinned = $row['is_pinned'];
                ?>
                    <tr>
                        <td>
                            <?php echo $count++; ?>
                            <?php if($is_pinned) echo " <span style='color:var(--accent-color); font-size:0.8rem;'>📌</span>"; ?>
                        </td>
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
                            <div class="row-settings-dropdown">
                                <button class="btn-secondary" style="padding: 2px 8px; font-size: 0.8rem;" onclick="toggleDropdown(event, this)">⚙</button>
                                <div class="row-dropdown-menu">
                                    <div class="row-dropdown-item" onclick="togglePinSong(<?php echo $row['link_id']; ?>, <?php echo $is_pinned; ?>)">
                                        <?php echo $is_pinned ? '取消釘選' : '釘選'; ?>
                                    </div>
                                    <div class="row-dropdown-item" style="color: #ff7675;" onclick="removeSong(<?php echo $playlist_id; ?>, <?php echo $row['id']; ?>)">
                                        移除
                                    </div>
                                </div>
                            </div>
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

    <!-- Rename Modal -->
    <div id="rename-modal" class="modal-overlay">
        <div class="modal-box" style="display: block; transform: none;">
            <h3>重新命名播放清單</h3>
            <form id="rename-form" onsubmit="submitRename(event); return false;">
                <input type="hidden" id="rename-playlist-id">
                <input type="text" id="rename-input" style="width: 100%; padding: 10px; margin-bottom: 20px; background: #444; color: white; border: none; border-radius: 4px; box-sizing: border-box;" placeholder="輸入新名稱" required>
                <div style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('rename-modal').style.display='none'">取消</button>
                    <button type="submit" class="btn-primary">儲存</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/player_bridge.js?v=5"></script>
    <script src="js/playlist_manager.js?v=<?php echo time(); ?>"></script>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
