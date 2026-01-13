<?php
// 同構 Shell 模式
// index.php 兼作 App Shell (持有播放器) 與 內容頁面 (歌曲列表)

session_start();
require_once("../DB/DB_open.php");

// ---------------------------------------------------------
// 模式 1：內容頁面 (歌曲列表)
// 如果設定了 'inner' 參數，我們顯示歌曲列表內容
// ---------------------------------------------------------
if (isset($_GET['inner'])) {
    // ---------------------------------------------------------
    // Smart Shuffle & Pagination Logic
    // ---------------------------------------------------------
    
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if ($page < 1) $page = 1;

    // 1. Refresh Logic: Always refresh if explicit request OR if it's the first page
    // This allows "Every refresh shows different songs" while keeping pagination consistent (Page 2 comes from current session)
    $should_refresh = (isset($_GET['refresh']) && $_GET['refresh'] == 1) || ($page == 1);
    
    if ($should_refresh || !isset($_SESSION['homepage_shuffle_list']) || empty($_SESSION['homepage_shuffle_list'])) {
        // Fetch ALL songs with last_played_at
        $sql = "SELECT id, last_played_at FROM songs";
        $result = mysqli_query($link, $sql);
        
        $pool_fresh = [];  // Not played recently
        $pool_recent = []; // Played recently
        $recent_threshold = time() - 3600; // 1 Hour ago
        
        while ($row = mysqli_fetch_assoc($result)) {
            $last_played_ts = !empty($row['last_played_at']) ? strtotime($row['last_played_at']) : 0;
            
            if ($last_played_ts > $recent_threshold) {
                // Played recently: Add to secondary pool
                $pool_recent[] = $row['id'];
            } else {
                // Fresh: Add to primary pool
                $pool_fresh[] = $row['id'];
            }
        }
        
        // Shuffle both pools independently
        shuffle($pool_fresh);
        shuffle($pool_recent);
        
        // Merge: Fresh songs first, then recent ones
        // This ensures "Unplayed songs appear" (at top) but doesn't hide everything if user played all songs
        $candidates = array_merge($pool_fresh, $pool_recent);
        
        // Save to Session
        $_SESSION['homepage_shuffle_list'] = $candidates;
    } else {
        $candidates = $_SESSION['homepage_shuffle_list'];
    }
    
    // 2. Pagination Logic
    $limit = 18; // 6 cols * 3 rows
    $total_items = count($candidates);
    $total_pages = ceil($total_items / $limit);
    if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
    
    $offset = ($page - 1) * $limit;
    $page_items = array_slice($candidates, $offset, $limit);
    
    // 3. Fetch Details for Current Page Items
    if (!empty($page_items)) {
        $ids_str = implode(',', $page_items);
        $sql = "SELECT * FROM songs WHERE id IN ($ids_str) ORDER BY FIELD(id, $ids_str)";
        $result = mysqli_query($link, $sql);
    } else {
        $result = false;
    }

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>音樂串流平台 - 首頁</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <link rel="stylesheet" href="css/index_content.css?v=2">
</head>
<body>
    <!-- 移除導覽列，改用 App Shell 側邊欄 -->

    <div id="content-container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>探索最新音樂</h1>
            <a href="index.php?inner=1&refresh=1" class="btn-primary" style="font-size: 0.9rem; padding: 8px 16px; display: flex; align-items: center; gap: 5px;" title="換一批推薦">
                <span style="font-size: 1.1rem;">↺</span> 換一批
            </a>
        </div>
        
        <div class="song-list">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $cover = "get_cover.php?id=" . $row['id'];
            ?>
                <div class="song-card">
                    <div style="position: relative; cursor: pointer;" onclick="playContextSong('<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['artist'], ENT_QUOTES); ?>', 'music/<?php echo $row['file_path']; ?>', '<?php echo $cover; ?>', <?php echo $row['id']; ?>, 'all', 0, '所有歌曲')" title="點擊播放">
                        <img src="<?php echo $cover; ?>" class="song-cover">
                        <div class="play-overlay">
                            <span class="card-play-btn">▶</span>
                        </div>
                    </div>
                    <div class="song-title"><?php echo htmlspecialchars($row['title']); ?></div>
                    <div class="song-artist" style="display: flex; justify-content: space-between; align-items: center;">
                        <span><?php echo htmlspecialchars($row['artist']); ?></span>
                        <button style="background: none; border: none; color: #aaa; cursor: pointer; font-size: 1.2rem;" onclick="event.stopPropagation(); openPlaylistModal(<?php echo $row['id']; ?>)" title="加入播放清單">+</button>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center; color: #777;'>目前還沒有任何歌曲。</p>";
            }
            ?>
        </div>
        
        <!-- Pagination Controls -->
        <?php if (isset($total_pages) && $total_pages > 1): ?>
        <div class="pagination-container">
            <!-- First Page -->
            <?php if ($page > 1): ?>
                <a href="index.php?inner=1&page=1" class="page-btn">&laquo; 最前頁</a>
                <a href="index.php?inner=1&page=<?php echo $page-1; ?>" class="page-btn">&lsaquo; 上一頁</a>
            <?php else: ?>
                <span class="page-btn disabled">&laquo; 最前頁</span>
                <span class="page-btn disabled">&lsaquo; 上一頁</span>
            <?php endif; ?>
            
            <span class="page-info">第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁</span>
            
            <!-- Next & Last Page -->
            <?php if ($page < $total_pages): ?>
                <a href="index.php?inner=1&page=<?php echo $page+1; ?>" class="page-btn">下一頁 &rsaquo;</a>
                <a href="index.php?inner=1&page=<?php echo $total_pages; ?>" class="page-btn">最後頁 &raquo;</a>
            <?php else: ?>
                <span class="page-btn disabled">下一頁 &rsaquo;</span>
                <span class="page-btn disabled">最後頁 &raquo;</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        

    </div>
    
    <!-- Playlist Modal -->
    <div id="playlist-modal">
        <div class="modal-content">
            <form>
                <!-- Dynamic content will be inserted here by JS -->
            </form>
        </div>
    </div>

    <script src="js/player_bridge.js?v=5"></script>
    <script src="js/index_content.js?v=5"></script>

    <?php include "foot.html"; ?>
</body>
</html>
<?php 
    exit; 
} 
?>

<!-- --------------------------------------------------------- -->
<!-- 模式 2：應用程式外殼 (App Shell) -->
<!-- --------------------------------------------------------- -->
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>音樂串流平台</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <link rel="stylesheet" href="css/index_shell.css">
</head>
<body>
    <div id="main-layout">
        <!-- 側邊欄 -->
        <div id="sidebar">
            <div style="display: flex; align-items: center; padding-left: 15px; margin-bottom: 20px; height: 40px;">
                <button class="menu-btn" onclick="toggleSidebar()" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer; margin-right:10px;">☰</button>
                <div class="sidebar-logo" onclick="location.href='index.php'" style="margin:0;">Music Stream</div>
            </div>
            
            <a href="index.php?inner=1" target="content-frame" onclick="highlightNav('nav-home')" class="nav-item active" id="nav-home" title="首頁">
                <span class="nav-icon">🔙</span> <span class="nav-text">首頁</span>
            </a>
            <a href="search.php" target="content-frame" onclick="highlightNav('nav-search')" class="nav-item" id="nav-search" title="搜尋">
                <img src="img/istockphoto-1151843591-612x612.jpg" class="nav-icon" style="border-radius: 50%; object-fit: cover;"> <span class="nav-text">搜尋</span>
            </a>
            <a href="my_playlists.php" target="content-frame" onclick="highlightNav('nav-library')" class="nav-item" id="nav-library" title="播放清單">
                <span class="nav-icon">❏</span> <span class="nav-text">播放清單</span>
            </a>
            <!-- 分隔線 -->
            <hr style="border: 0; border-top: 1px solid #282828; margin: 10px 20px;">
            
            <a href="../hw/index.php" class="nav-item" title="回作業首頁">
                <span class="nav-icon">↩</span> <span class="nav-text">回作業首頁</span>
            </a>
        </div>
        
        <!-- 內容區域 -->
        <div style="display: flex; flex-direction: column; flex: 1;">
            <div id="top-bar">
                <!-- 選單按鈕已移至側邊欄 -->
                <form class="search-container" action="search.php" target="content-frame" onsubmit="highlightNav('nav-search')">
                    <button type="submit" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer;" title="搜尋">
                        <img src="img/istockphoto-1151843591-612x612.jpg" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover; display: block;">
                    </button>
                    <input type="text" name="q" class="search-input" placeholder="搜尋歌曲、專輯、藝人...">
                </form>
                
                <div style="margin-left: auto; display: flex; gap: 15px; position: relative;">
                    <?php if(isset($_SESSION['login_session'])): ?>
                       <!-- 頭像 (增加邊距以平衡頂部/右側間距) -->
                       <div onclick="toggleUserMenu()" style="width: 55px; height: 55px; background: #535c68; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; color: white; overflow: hidden; font-size: 1.5rem; margin-right: 30px; margin-top: 30px; align-self: flex-start;" title="帳戶選單">
                           <?php 
                               if (isset($_SESSION['picture']) && !empty($_SESSION['picture']) && file_exists("img/avatars/" . $_SESSION['picture'])) {
                                   echo '<img src="img/avatars/' . htmlspecialchars($_SESSION['picture']) . '" style="width: 100%; height: 100%; object-fit: cover;">';
                               } else {
                                   echo strtoupper(substr($_SESSION['username'], 0, 1));
                               }
                           ?>
                       </div>
                       
                       <!-- 下拉選單 -->
                       <div id="user-menu" style="display: none; position: absolute; top: 100px; right: 35px; background: #282828; border-radius: 8px; width: 220px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); z-index: 100; overflow: hidden; padding: 10px 0;">
                           <!-- 標頭資訊 -->
                           <div style="padding: 10px 20px; border-bottom: 1px solid #3e3e3e; margin-bottom: 5px;">
                               <div style="font-weight: bold; color: white;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                               <div style="font-size: 0.8rem; color: #aaa;">@<?php echo htmlspecialchars($_SESSION['username']); ?></div>
                           </div>
                           
                           <!-- 選單項目 -->
                           <a href="creator.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()">
                               <span style="width: 24px; text-align: center;">🎨</span> 創作者工作室
                           </a>
                           
                           <a href="profile.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()">
                               <span style="width: 24px; text-align: center;">👤</span> 個人資料
                           </a>
                           
                           <a href="my_messages.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()">
                               <span style="width: 24px; text-align: center;">💬</span> 我的客服
                           </a>
                           
                           <?php
                           // Admin Check
                           $chk_user = $_SESSION['username'];
                           $chk_sql = "SELECT role FROM students WHERE username = '$chk_user'";
                           $chk_res = mysqli_query($link, $chk_sql);
                           if ($chk_res && mysqli_num_rows($chk_res) > 0) {
                               $chk_row = mysqli_fetch_assoc($chk_res);
                               if (($chk_row['role'] ?? 'user') === 'admin') {
                           ?>
                               <a href="admin.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()">
                                   <span style="width: 24px; text-align: center;">🛡️</span> 後台管理
                               </a>
                           <?php
                               }
                           }
                           ?>
                           
                           <hr style="border: 0; border-top: 1px solid #3e3e3e; margin: 5px 0;">
                           
                           <a href="logout.php" class="menu-item" style="color: #ff4757;">
                               <span style="width: 24px; text-align: center;">🚪</span> 登出
                           </a>
                       </div>
                    <?php else: ?>
                         <a href="login.php" class="btn-primary" style="padding: 8px 20px; font-size: 0.9rem;">登入</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div id="content-area">
                <iframe src="index.php?inner=1" name="content-frame" id="content-frame"></iframe>
            </div>
        </div>
    </div>

    <div id="player-bar" style="display: none;">
        <!-- 頂部進度條 (複合層：背景線、紅線、滑塊) -->
        <div id="progress-container">
            <div id="progress-bg"></div>
            <div id="progress-fill"></div>
            <input type="range" id="progress-slider" min="0" value="0" step="0.1" title="進度條">
        </div>

        <!-- 左側：控制與時間 -->
        <div class="player-left">
            <button class="control-btn" onclick="prevSong()">⏮</button>
            <button class="control-btn play-btn" onclick="togglePlay()" id="main-play-btn">▶</button>
            <button class="control-btn" onclick="nextSong()">⏭</button>
            <span class="time-display">
                <span id="curr-time">0:00</span> / <span id="total-time">0:00</span>
            </span>
        </div>

        <!-- 中間：資訊與按讚 -->
        <div class="player-center">
            <img id="player-cover" src="" onclick="toggleNowPlaying()" title="展開播放介面" style="cursor: pointer;">
            <div class="track-info">
                <div id="player-title"></div>
                <div id="player-artist"></div>
            </div>
            <button class="control-btn" id="like-btn" onclick="toggleLike()" title="加入最愛">♡</button>
        </div>

        <!-- 右側：工具與音量 -->
        <div class="player-right">
            <!-- 音量控制 -->
            <div class="volume-container">
                <span id="volume-value">100%</span>
                <input type="range" id="volume-slider" min="0" max="1" step="0.01" value="1">
                <button class="control-btn" id="volume-btn" onclick="toggleMute()" title="靜音">🔊</button>
            </div>
            
            <button class="control-btn" id="shuffle-btn" onclick="toggleShuffle()" title="隨機播放" style="opacity: 0.5;">🔀</button>
            <button class="control-btn" id="loop-btn" onclick="toggleLoop()" title="循環播放">🔁</button>
            
           <div id="queue-info" style="font-size: 0.7rem; color: #777; border: 1px solid #333; padding: 2px 6px; border-radius: 4px;">Q: 0</div>
        </div>
        
        <audio id="audio-player"></audio>
    </div>

    <div id="now-playing-overlay" style="display: none;">
        <div class="np-left">
            <img id="np-big-cover" src="" onclick="toggleNowPlaying()" title="收起播放介面">
        </div>
        <div class="np-right">
            <div class="np-queue-header">
                <h3>即將播放</h3>
                <span id="np-queue-count" style="font-size: 0.8rem; color: #aaa;"></span>
            </div>
            <div id="np-playlist-name" style="padding: 0 20px 10px 20px; font-size: 0.85rem; color: #ff4757; border-bottom: 1px solid #222;">
                <!-- Playlist name will be inserted here -->
            </div>
            <div id="np-queue-list">
                <!-- Javascript rendered queue -->
            </div>
        </div>
    </div>

    <script src="js/index_shell.js?v=3"></script>

    <!-- 系統預設歡迎提示 -->
    <?php if (isset($_SESSION['first_login']) && $_SESSION['first_login'] === true): 
        $w_user = $_SESSION['username']; 
        unset($_SESSION['first_login']); 
    ?>
    <script>
        // 依需求顯示系統預設提示
        alert("歡迎 <?php echo htmlspecialchars($w_user); ?> 登入！");
    </script>
    <?php endif; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
