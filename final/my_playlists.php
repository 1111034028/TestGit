<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

$user_id = $_SESSION['sno'];
$result = get_user_playlists($link, $user_id);

$page_title = "我的歌單 - 音樂串流平台";
require_once("inc/header.php"); 
require_once("inc/modal.php");
?>
    <div id="content-container" style="margin-top: 20px;">
        <h1>我的播放清單</h1>

        <div class="song-list">
            <!-- 建立新歌單卡片 -->
            <div class="song-card" onclick="openCreateModal()" style="border: 2px dashed #333; background: rgba(255,255,255,0.02); display: flex; flex-direction: column; align-items: center; justify-content: center; transition: all 0.3s;">
                <div style="font-size: 4rem; color: #444; margin-bottom: 10px;">+</div>
                <div style="color: #888; font-weight: bold;">建立新播放清單</div>
            </div>

            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $pid = $row['id'];
                    $count = count_playlist_songs($link, $pid);
                    $is_fav = ($row['name'] === 'My Favorites');
                    $is_pinned = $row['is_pinned'];
            ?>
                <div class="song-card" onclick="location.href='playlist_view.php?id=<?php echo $pid; ?>'">
                    <div style="width: 100%; height: 160px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 10px; position: relative;">
                        <span style="font-size: 3rem;">🎵</span>
                        <?php if ($is_pinned): ?>
                            <div style="position: absolute; top: 10px; right: 10px;" title="已釘選">
                                <img src="img/pinned-6851740_640.png" style="width: 24px; height: 24px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="song-title">
                        <?php echo htmlspecialchars($row['name']); ?>
                        <?php if ($is_fav) echo " <span style='font-size:0.8rem; color:var(--accent-color);'>★</span>"; ?>
                    </div>
                    <div class="song-artist" style="display: flex; justify-content: space-between; align-items: center;">
                        <span><?php echo $count; ?> 首歌曲</span>
                        <div class="settings-dropdown" onclick="event.stopPropagation();">
                            <button class="btn-secondary" style="padding: 4px 8px; font-size: 0.8rem;" onclick="toggleDropdown(event, this)">⚙ 設定</button>
                            <div class="dropdown-menu">
                                <div class="dropdown-item" onclick='openRenameModal(<?php echo $pid; ?>, <?php echo json_encode($row["name"]); ?>)'>編輯</div>
                                <?php if (!$is_fav): ?>
                                    <div class="dropdown-item" onclick="togglePinPlaylist(<?php echo $pid; ?>, <?php echo $is_pinned; ?>)">
                                        <?php echo $is_pinned ? '取消釘選' : '釘選'; ?>
                                    </div>
                                    <div class="dropdown-item" style="color: #ff7675; border-top: 1px solid #404040;" onclick='deletePlaylist(<?php echo $pid; ?>, <?php echo json_encode($row["name"]); ?>)'>
                                        刪除
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                }
            }
            ?>
        </div>
    </div>
    
    <!-- Modals -->
    <div id="create-modal" class="modal-overlay">
        <div class="modal-box" style="display: block; transform: none;">
            <h3>建立新播放清單</h3>
            <form id="create-form" onsubmit="submitCreate(event); return false;">
                <input type="text" id="create-input" style="width: 100%; padding: 12px; margin: 15px 0 25px; background: #181818; color: white; border: 1px solid #444; border-radius: 4px; box-sizing: border-box; font-size: 1rem;" placeholder="名稱：例如我的放鬆歌單" required>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('create-modal').style.display='none'">取消</button>
                    <button type="submit" class="btn-primary" style="padding: 8px 25px;">建立</button>
                </div>
            </form>
        </div>
    </div>

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

    <script src="js/playlist_manager.js?v=<?php echo time(); ?>"></script>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
