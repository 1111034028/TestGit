<?php
// stamps_board.php
// 留言板主頁面：顯示所有音域留言，並整合 Google Maps
session_start();
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

$page_title = "音域留言板 - 音域地圖";
$extra_css = '<link rel="stylesheet" href="css/stamps.css">
              <link rel="stylesheet" href="css/theme_dark_modal.css?v='.time().'">
              <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
              <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>';

require_once("inc/header.php");

// ... (Lines 15-37 kept implicitly or not modified heavily) ...
// ACTUALLY I cannot replace scattered code.
// I will target the $extra_css block first.

// And then the Modal block.
// Let's do multiple replacements.


require_once("inc/header.php");

// Pagination
$limit = 9; // Max 3 rows (3x3)
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count Total
$sql_cnt = "SELECT COUNT(*) as c FROM music_marks";
$res_cnt = mysqli_query($link, $sql_cnt);
$total_rows = mysqli_fetch_assoc($res_cnt)['c'];
$total_pages = ceil($total_rows / $limit);

// Fetch Songs for Dropdown
$sql_songs = "SELECT id, title, artist FROM songs ORDER BY title";
$res_songs = mysqli_query($link, $sql_songs);

// Fetch Stamps with Limit
$sql = "SELECT m.*, s.title, s.artist, s.file_path, s.id as song_real_id, st.name as user_name, st.picture as user_pic 
        FROM music_marks m
        JOIN songs s ON m.song_id = s.id
        LEFT JOIN students st ON m.user_id = st.sno
        ORDER BY m.created_at DESC LIMIT $offset, $limit";
$result = mysqli_query($link, $sql);
?>
    <div id="content-container" style="padding-top: 20px;">
        <div class="board-header">
            <div>
                <h1>音域留言板</h1>
                <p style="color: #aaa; font-size: 0.9rem;">探索來自各地的聲音與故事</p>
            </div>
            <!-- 切換檢視模式 -->
            <div style="display: flex; gap: 10px; align-items: center;">
                <div class="view-toggles">
                    <button class="btn-secondary active" id="btn-list-view" onclick="switchView('list')">列表模式</button>
                    <button class="btn-secondary" id="btn-map-view" onclick="switchView('map')">地圖模式</button>
                </div>
                <button class="btn-primary" onclick="openNewStampModal()" style="border-radius: 50%; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; line-height: 1;" title="新增留言">+</button>
            </div>
        </div>

        <!-- 列表視圖 -->
        <div id="list-view" class="stamps-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): 
                    $cover = "get_cover.php?id=" . $row['song_real_id'];
                    $bg_color = substr(md5($row['user_name']), 0, 6);
                    $default_pic = "https://ui-avatars.com/api/?name=" . urlencode($row['user_name']) . "&background=" . $bg_color . "&color=fff";
                    $user_pic = $default_pic;
                    if (!empty($row['user_pic'])) {
                        if (strpos($row['user_pic'], 'http') === 0) {
                            $user_pic = $row['user_pic'];
                        } else {
                            $user_pic = "img/avatars/" . $row['user_pic'];
                        }
                    }
                    // 為了 JS 操作方便，將數據塞入 data attribute
                    $json_data = htmlspecialchars(json_encode([
                        'id' => $row['id'],
                        'lat' => $row['latitude'],
                        'lng' => $row['longitude'],
                        'title' => $row['title'],
                        'artist' => $row['artist'],
                        'message' => $row['message'],
                        'user' => $row['user_name'],
                        'user_color' => $bg_color,
                        'cover' => $cover,
                        'songId' => $row['song_real_id'],
                        'path' => 'music/' . $row['file_path'],
                        'user_pic' => $user_pic,
                        'location' => !empty($row['location_name']) ? $row['location_name'] : number_format($row['latitude'], 4) . ", " . number_format($row['longitude'], 4)
                    ]), ENT_QUOTES, 'UTF-8');
                ?>
                
                <div class="stamp-card" id="stamp-card-<?php echo $row['id']; ?>" data-json="<?php echo $json_data; ?>">
                    <div class="stamp-header">
                        <div class="stamp-user">
                            <img src="<?php echo $user_pic; ?>" class="stamp-avatar" onerror="this.onerror=null;this.src='<?php echo $default_pic; ?>';">
                            <div>
                                <div class="stamp-username"><?php echo htmlspecialchars($row['user_name']); ?></div>
                                <div class="stamp-time"><?php echo date('Y/m/d H:i', strtotime($row['created_at'])); ?></div>
                            </div>
                        </div>
                        <button class="btn-map-pin" onclick="showOnMap(this)" title="在地圖上查看">
                            <img src="img/map.jpg" style="width: 20px; height: 20px; object-fit: cover; border-radius: 50%;">
                        </button>
                    </div>
                    
                    <div class="stamp-message" id="msg-<?php echo $row['id']; ?>">
                        <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                    </div>
                    
                    <?php if (isset($_SESSION['sno']) && $_SESSION['sno'] === $row['user_id']): ?>
                    <div class="stamp-actions" style="margin-bottom: 10px; display: flex; GAP: 10px; justify-content: flex-end;">
                        <button class="btn-sm" onclick="editStamp(<?php echo $row['id']; ?>)" style="background:none; border:none; color:#aaa; cursor:pointer;" title="編輯">✎</button>
                        <button class="btn-sm" onclick="deleteStamp(<?php echo $row['id']; ?>, this)" style="background:none; border:none; color:#aaa; cursor:pointer;" title="刪除">🗑️</button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="stamp-music-box" onclick="playBoardSong(<?php echo $row['song_real_id']; ?>, '<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['artist'], ENT_QUOTES); ?>', 'music/<?php echo $row['file_path']; ?>', '<?php echo $cover; ?>')">
                        <div class="stamp-cover-container">
                            <img src="<?php echo $cover; ?>" class="stamp-cover">
                            <div class="stamp-play-overlay">▶</div>
                        </div>
                        <div class="stamp-music-info">
                            <div class="stamp-song"><?php echo htmlspecialchars($row['title']); ?></div>
                            <div class="stamp-artist"><?php echo htmlspecialchars($row['artist']); ?></div>
                        </div>
                    </div>
                    
                    <div class="stamp-location-tag" style="display: flex; align-items: center; justify-content: flex-end; gap: 5px;">
                        <img src="img/map.jpg" style="width: 14px; height: 14px; object-fit: cover; border-radius: 50%;">
                        <?php 
                            $loc = !empty($row['location_name']) ? htmlspecialchars($row['location_name']) : number_format($row['latitude'], 4) . ", " . number_format($row['longitude'], 4);
                            echo $loc;
                        ?>
                    </div>
                </div>
                
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #777;">
                    目前還沒有任何留言，快去地圖上打卡吧！
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div id="pagination-container" class="pagination-container" style="margin-top: 30px; display: flex; justify-content: center; gap: 5px;">
            <?php if ($page > 1): ?>
                <a href="?page=1" class="btn-secondary" style="padding: 8px 12px;" title="最前頁">&lt;&lt;</a>
                <a href="?page=<?php echo $page-1; ?>" class="btn-secondary" style="padding: 8px 12px;" title="上一頁">&lt;</a>
            <?php endif; ?>
            
            <?php 
            // 限制顯示的頁碼數量（例如目前頁碼的前後 2 頁）
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            
            for($i=$start; $i<=$end; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="btn-secondary <?php echo $i==$page?'active':''; ?>" style="padding: 8px 12px; <?php echo $i==$page?'background:var(--primary-accent); color:white; border-color:var(--primary-accent);':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>" class="btn-secondary" style="padding: 8px 12px;" title="下一頁">&gt;</a>
                <a href="?page=<?php echo $total_pages; ?>" class="btn-secondary" style="padding: 8px 12px;" title="最後頁">&gt;&gt;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- 地圖視圖 (預設隱藏) -->
        <div id="map-view" style="display: none; position: relative;">
            <div id="marker-list-panel" style="position: absolute; left: 10px; top: 10px; bottom: 10px; width: 280px; background: rgba(20, 20, 20, 0.9); backdrop-filter: blur(10px); z-index: 1000; border-radius: 12px; border: 1px solid #333; display: none; flex-direction: column; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                <div style="padding: 15px; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 1rem; color: #fff;">此處有 <span id="marker-count">0</span> 則留言</h3>
                    <button onclick="closeMarkerPanel()" style="background: none; border: none; color: #888; cursor: pointer; font-size: 1.2rem;">&times;</button>
                </div>
                <div id="marker-list-content" style="flex: 1; overflow-y: auto; padding: 10px;">
                    <!-- Items will be injected here -->
                </div>
            </div>
            <div id="leaflet-map" style="width: 100%; height: 600px; border-radius: 12px; z-index: 1; border: 1px solid #333;"></div>
        </div>
    </div>

    <!-- Hidden full stamps data for map initialization -->
    <div id="all-stamps-hidden" style="display: none;">
        <?php 
        // Fetch ALL stamps for the map
        $sql_all = "SELECT m.*, s.title, s.artist, s.file_path, s.id as song_real_id, st.name as user_name, st.picture as user_pic 
                    FROM music_marks m
                    JOIN songs s ON m.song_id = s.id
                    LEFT JOIN students st ON m.user_id = st.sno
                    ORDER BY m.created_at DESC";
        $res_all = mysqli_query($link, $sql_all);
        while($row = mysqli_fetch_assoc($res_all)): 
            $cover = "get_cover.php?id=" . $row['song_real_id'];
            $bg_color = substr(md5($row['user_name']), 0, 6);
            $user_pic = "https://ui-avatars.com/api/?name=" . urlencode($row['user_name']) . "&background=" . $bg_color . "&color=fff";
            if (!empty($row['user_pic'])) {
                $user_pic = (strpos($row['user_pic'], 'http') === 0) ? $row['user_pic'] : "img/avatars/" . $row['user_pic'];
            }
            $data = [
                'id' => $row['id'],
                'lat' => $row['latitude'],
                'lng' => $row['longitude'],
                'title' => $row['title'],
                'artist' => $row['artist'],
                'message' => $row['message'],
                'user' => $row['user_name'],
                'user_color' => $bg_color,
                'cover' => $cover,
                'songId' => $row['song_real_id'],
                'path' => 'music/' . $row['file_path'],
                'user_pic' => $user_pic,
                'location' => !empty($row['location_name']) ? $row['location_name'] : number_format($row['latitude'], 4) . ", " . number_format($row['longitude'], 4),
                'time' => date('Y/m/d H:i', strtotime($row['created_at']))
            ];
            echo '<div class="stamp-data-item" data-json="'.htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8').'"></div>';
        endwhile;
        ?>
    </div>
    
    <!-- New Stamp Modal -->
    <div id="new-stamp-modal" class="modal-overlay" onclick="if(event.target === this) closeNewStampModal()">
        <div class="modal-box modal-w-md">
            <div class="modal-header">
                 <h3 class="modal-title">新增音域留言</h3>
                 <button onclick="closeNewStampModal()" class="modal-close-btn">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="new-stamp-form" onsubmit="submitNewStamp(event)">
                    <div class="form-group">
                        <label class="form-label">選擇歌曲 (必填)</label>
                        <select id="new-stamp-song" required class="form-control center-90">
                            <option value="">-- 請選擇歌曲 --</option>
                            <?php 
                            if ($res_songs && mysqli_num_rows($res_songs) > 0) {
                                mysqli_data_seek($res_songs, 0); // Reset pointer
                                while($s = mysqli_fetch_assoc($res_songs)) {
                                    echo '<option value="'.$s['id'].'">'.htmlspecialchars($s['title']).' - '.htmlspecialchars($s['artist']).'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">留言內容</label>
                        <textarea id="new-stamp-msg" required placeholder="在這裡，我聽著這首歌..." class="form-control center-90 textarea-sm"></textarea>
                    </div>
                    
                    <input type="hidden" id="new-stamp-lat">
                    <input type="hidden" id="new-stamp-lng">
                    
                    <div id="new-stamp-status" class="status-text">
                        <span id="loc-spinner">⌛</span> <span id="loc-text">正在獲取目前位置...</span>
                    </div>

                    <div style="text-align: right;">
                        <button type="submit" class="btn-primary-dark">發佈留言</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Stamp Modal (Purple Theme) -->
    <div id="edit-stamp-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);" onclick="if(event.target === this) closeEditModal()">
        <div class="modal-box" style="background: #181818; padding: 0; border-radius: 16px; width: 90%; max-width: 420px; border: 1px solid #333; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
            
            <!-- Header -->
            <div style="padding: 20px 30px; border-bottom: 1px solid #2a2a2a; display: flex; justify-content: space-between; align-items: center; background: #202020;">
                 <h3 style="margin: 0; color: #a29bfe; font-size: 1.1rem; font-weight: 600;">編輯留言</h3>
                 <button onclick="closeEditModal()" style="background: none; border: none; color: #666; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <!-- Body -->
            <div style="padding: 30px;">
                <label style="display:block; color: #aaa; margin-bottom: 10px; font-size: 0.85rem; font-weight: 500;">留言內容</label>
                <textarea id="edit-stamp-input" rows="5" style="width: 100%; background: #121212; border: 1px solid #333; color: #eee; padding: 15px; border-radius: 8px; resize: none; font-size: 1rem; outline: none; box-sizing: border-box; transition: all 0.2s; line-height: 1.6;" 
                onfocus="this.style.borderColor='#a29bfe'; this.style.backgroundColor='#000';" 
                onblur="this.style.borderColor='#333'; this.style.backgroundColor='#121212'"></textarea>
            </div>

            <!-- Footer -->
            <div style="padding: 20px 30px; display: flex; justify-content: flex-end; gap: 12px; background: #202020; border-top: 1px solid #2a2a2a;">
                <button onclick="closeEditModal()" class="btn-secondary" style="background: transparent; color: #aaa; border: 1px solid transparent; padding: 10px 24px; border-radius: 50px; cursor: pointer; font-size: 0.95rem; transition: 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#aaa'">取消</button>
                <button onclick="submitEditStamp()" class="btn-primary" style="background: #6c5ce7; color: white; border: none; padding: 10px 30px; border-radius: 50px; cursor: pointer; font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 15px rgba(108, 92, 231, 0.4); transition: transform 0.1s;" onmousedown="this.style.transform='scale(0.96)'" onmouseup="this.style.transform='scale(1)'">儲存變更</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal (Redesigned) -->
    <div id="delete-stamp-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);" onclick="if(event.target === this) closeDeleteModal()">
        <div class="modal-box" style="background: #181818; padding: 0; border-radius: 16px; width: 90%; max-width: 380px; border: 1px solid #ff4757; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
            <div style="padding: 20px 30px; border-bottom: 1px solid #2a2a2a; background: #202020; display: flex; justify-content: space-between; align-items: center;">
                 <h3 style="margin: 0; color: #ff4757; font-size: 1.1rem; font-weight: 600;">刪除確認</h3>
                 <button onclick="closeDeleteModal()" style="background: none; border: none; color: #666; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <div style="padding: 30px;">
                <p style="color: #ddd; margin: 0; font-size: 1rem; line-height: 1.6;">您確定要刪除這則音域留言嗎？<br><span style="font-size: 0.85rem; color: #888;">此動作無法復原。</span></p>
            </div>
            <div style="padding: 20px 30px; display: flex; justify-content: flex-end; gap: 12px; background: #202020; border-top: 1px solid #2a2a2a;">
                <button onclick="closeDeleteModal()" class="btn-secondary" style="background: transparent; color: #aaa; border: 1px solid transparent; padding: 10px 24px; border-radius: 50px; cursor: pointer; font-size: 0.95rem; transition: 0.2s;">取消</button>
                <button onclick="confirmDeleteStamp()" class="btn-primary" style="background: #ff4757; color: white; border: none; padding: 10px 30px; border-radius: 50px; cursor: pointer; font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 15px rgba(255, 71, 87, 0.4);">確定刪除</button>
            </div>
        </div>
    </div>

    <script>
        // 傳遞所有資料給 JS（供地圖展現）
        const allStamps = [];
        document.querySelectorAll('.stamp-data-item').forEach(item => {
            try {
                const data = JSON.parse(item.getAttribute('data-json'));
                allStamps.push(data);
            } catch(e) {}
        });
    </script>
    <script src="js/stamps_board.js?v=<?php echo time(); ?>"></script>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
