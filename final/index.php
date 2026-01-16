<?php
// index.php 兼作 App Shell (持有播放器) 與 內容頁面 (歌曲列表)
session_start();
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

// ---------------------------------------------------------
// 模式 1：內容頁面 (歌曲列表)
// 如果設定了 'inner' 參數，我們顯示歌曲列表內容
// ---------------------------------------------------------
if (isset($_GET['inner'])) {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if ($page < 1) $page = 1;

    $should_refresh = (isset($_GET['refresh']) && $_GET['refresh'] == 1) || ($page == 1);
    
    if ($should_refresh || !isset($_SESSION['homepage_shuffle_list']) || empty($_SESSION['homepage_shuffle_list'])) {
        $sql = "SELECT id, last_played_at FROM songs";
        $result = mysqli_query($link, $sql);
        
        $pool_fresh = [];
        $pool_recent = [];
        $recent_threshold = time() - 3600;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $last_played_ts = !empty($row['last_played_at']) ? strtotime($row['last_played_at']) : 0;
            if ($last_played_ts > $recent_threshold) $pool_recent[] = $row['id'];
            else $pool_fresh[] = $row['id'];
        }
        
        shuffle($pool_fresh);
        shuffle($pool_recent);
        $candidates = array_merge($pool_fresh, $pool_recent);
        $_SESSION['homepage_shuffle_list'] = $candidates;
    } else {
        $candidates = $_SESSION['homepage_shuffle_list'];
    }
    
    $limit = 18;
    $total_items = count($candidates);
    $total_pages = ceil($total_items / $limit);
    if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
    
    $offset = ($page - 1) * $limit;
    $page_items = array_slice($candidates, $offset, $limit);
    
    if (!empty($page_items)) {
        $ids_str = implode(',', $page_items);
        $sql = "SELECT * FROM songs WHERE id IN ($ids_str) ORDER BY FIELD(id, $ids_str)";
        $result = mysqli_query($link, $sql);
    } else {
        $result = false;
    }

    $page_title = "探索音樂";
    require_once("inc/header.php");
?>
    <link rel="stylesheet" href="css/index_content.css?v=2">
    <div id="content-container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>探索最新音樂</h1>
            <a href="index.php?inner=1&refresh=1" class="btn-primary" style="font-size: 0.9rem; padding: 8px 16px; display: flex; align-items: center; gap: 5px;" title="換一批推薦">
                <span style="font-size: 1.1rem;">↺</span> 換一批
            </a>
        </div>
        
        <div class="song-list">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): $cover = "get_cover.php?id=" . $row['id']; ?>
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
                <?php endwhile; ?>
            <?php else: ?>
                <p style='grid-column: 1/-1; text-align: center; color: #777;'>目前還沒有任何歌曲。</p>
            <?php endif; ?>
        </div>
        
        <?php if (isset($total_pages) && $total_pages > 1): ?>
        <div class="pagination-container">
            <a href="index.php?inner=1&page=1" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&laquo;</a>
            <a href="index.php?inner=1&page=<?php echo $page-1; ?>" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&lsaquo; 上一頁</a>
            <span class="page-info"><?php echo $page; ?> / <?php echo $total_pages; ?></span>
            <a href="index.php?inner=1&page=<?php echo $page+1; ?>" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">下一頁 &rsaquo;</a>
            <a href="index.php?inner=1&page=<?php echo $total_pages; ?>" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">&raquo;</a>
        </div>
        <?php endif; ?>
    </div>
    

    <script src="js/player_bridge.js?v=5"></script>
    <script src="js/index_content.js?v=5"></script>
    <?php include "inc/modal.php"; ?>
    <?php include "foot.html"; ?>
</body>
</html>
<?php exit; } ?>

<!-- --------------------------------------------------------- -->
<!-- 模式 2：應用程式外殼 (App Shell) -->
<!-- --------------------------------------------------------- -->
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>Music Stream</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/index_shell.css">
    <script src="js/components.js"></script>
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
            <a href="anime_map.php" target="content-frame" onclick="highlightNav('nav-map')" class="nav-item" id="nav-map" title="音域地圖">
                <img src="img/map.jpg" class="nav-icon" style="border-radius: 4px; object-fit: cover;"> <span class="nav-text">音域地圖</span>
            </a>
            <div onclick="initRemoteControl(); highlightNav('nav-remote')" class="nav-item" id="nav-remote" title="手機互動">
                <img src="img/phone.jpg" class="nav-icon" style="border-radius: 4px; object-fit: cover;"> <span class="nav-text">手機互動</span>
            </div>
            
            <?php
            if (isset($_SESSION['username'])) {
                $check_u = $_SESSION['username'];
                $chk_sql = "SELECT role FROM students WHERE username = '$check_u'";
                $chk_res = mysqli_query($link, $chk_sql);
                if ($chk_res && mysqli_num_rows($chk_res) > 0) {
                    $chk_row = mysqli_fetch_assoc($chk_res);
                    if ($chk_row['role'] === 'admin') {
            ?>
            <hr style="border: 0; border-top: 1px solid #444; margin: 10px 20px;">
            <a href="admin.php" target="content-frame" onclick="highlightNav('nav-admin')" class="nav-item" id="nav-admin" title="後台管理">
                <img src="img/8abbdb093fee4da88e0983a89d8498c2.png" class="nav-icon" style="border-radius: 4px; object-fit: cover; background: #fff; padding: 2px;"> <span class="nav-text">後台管理</span>
            </a>
            <?php
                    }
                }
            }
            ?>
            
            <hr style="border: 0; border-top: 1px solid #282828; margin: 10px 20px;">
            
            <a href="../hw/index.php" class="nav-item" title="回作業首頁">
                <span class="nav-icon">↩</span> <span class="nav-text">回作業首頁</span>
            </a>
        </div>
        
        <div style="display: flex; flex-direction: column; flex: 1;">
            <div id="top-bar">
                <form class="search-container" action="search.php" target="content-frame" onsubmit="highlightNav('nav-search')">
                    <button type="submit" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer;" title="搜尋">
                        <img src="img/istockphoto-1151843591-612x612.jpg" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover; display: block;">
                    </button>
                    <input type="text" name="q" class="search-input" placeholder="搜尋歌曲、專輯、藝人...">
                </form>
                
                <div style="margin-left: auto; display: flex; align-items: center; gap: 15px; position: relative;">
                    <?php if(isset($_SESSION['login_session'])): ?>
                       <div id="user-menu-btn" onclick="toggleUserMenu()" style="cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 5px; border-radius: 50%;">
                            <?php if (isset($_SESSION['picture']) && !empty($_SESSION['picture']) && file_exists("img/avatars/" . $_SESSION['picture'])): ?>
                                <img id="user-avatar-img" src="img/avatars/<?php echo htmlspecialchars($_SESSION['picture']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #282828; display: block;">
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #535c68; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; border: 2px solid #282828;">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="user-dropdown" class="dropdown-menu" style="right: 5px; top: 60px; min-width: 180px;">
                            <div style="padding: 10px 15px; border-bottom: 1px solid #3e3e3e; margin-bottom: 5px;">
                                <div style="font-weight: bold;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                <div style="font-size: 0.8rem; color: #aaa;">會員</div>
                            </div>
                            <a href="profile.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()">👤 個人檔案</a>
                            <a href="my_messages.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()">💬 客服紀錄</a>
                            <a href="creator.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()">🎙️ 創作者中心</a>
                            <?php
                            $u = $_SESSION['username'];
                            $sql_role = "SELECT role FROM students WHERE username = '$u'";
                            $res_role = mysqli_query($link, $sql_role);
                            if ($res_role && mysqli_num_rows($res_role) > 0) {
                                $row_role = mysqli_fetch_assoc($res_role);
                                if (($row_role['role'] ?? 'user') === 'admin') {
                                    echo '<a href="admin.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()"><img src="img/8abbdb093fee4da88e0983a89d8498c2.png" style="width: 20px; height: 20px; margin-right: 5px; vertical-align: middle;"> 後台管理</a>';
                                }
                            }
                            ?>
                            <hr style="border: 0; border-top: 1px solid #3e3e3e; margin: 5px 0;">
                            <a href="logout.php" class="menu-item" style="color: #ff4757;"><img src="img/exit.png" style="width: 20px; height: 20px; margin-right: 5px; vertical-align: middle;"> 登出</a>
                        </div>
                     <?php else: ?>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <a href="register.php" class="btn-secondary" style="padding: 8px 20px; font-size: 0.9rem; text-decoration: none;">註冊</a>
                            <a href="login.php" class="btn-primary" style="padding: 8px 20px; font-size: 0.9rem; text-decoration: none;">登入</a>
                        </div>
                     <?php endif; ?>
                </div>
            </div>
            
            <div id="content-area">
                <iframe src="about:blank" name="content-frame" id="content-frame"></iframe>
            </div>
        </div>
    </div>

    <div id="player-bar" style="display: none;">
        <div id="progress-container">
            <div id="progress-bg"></div>
            <div id="progress-fill"></div>
            <input type="range" id="progress-slider" min="0" value="0" step="0.1" title="進度條">
        </div>
        <div class="player-left">
            <button class="control-btn" onclick="prevSong()">⏮</button>
            <button class="control-btn play-btn" onclick="togglePlay()" id="main-play-btn">▶</button>
            <button class="control-btn" onclick="nextSong()">⏭</button>
            <span class="time-display">
                <span id="curr-time">0:00</span> / <span id="total-time">0:00</span>
            </span>
        </div>
        <div class="player-center">
            <img id="player-cover" src="" onclick="toggleNowPlaying()" title="展開播放介面" style="cursor: pointer;">
            <div class="track-info">
                <div id="player-title"></div>
                <div id="player-artist"></div>
            </div>
            <button class="control-btn" id="like-btn" onclick="toggleLike()" title="加入最愛">♡</button>
            <button class="control-btn" id="add-playlist-btn" onclick="openCurrentPlaylistModal()" title="加入播放清單" style="font-size: 1.4rem; font-weight: bold;">+</button>
        </div>
        <div class="player-right">
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
            <div id="np-playlist-name" style="padding: 0 20px 10px 20px; font-size: 0.85rem; color: #ff4757; border-bottom: 1px solid #222;"></div>
            <div id="np-queue-list"></div>
        </div>
    </div>

    <div id="playlist-modal">
        <div class="modal-content">
            <form><!-- Dynamic --></form>
        </div>
    </div>

    <div id="remote-modal" class="modal-overlay" onclick="if(event.target === this) this.style.display='none'">
        <div class="modal-box" style="background: white; color: black; max-width: 320px; display: block; transform: none; position: relative;">
            <button onclick="document.getElementById('remote-modal').style.display='none'" style="position: absolute; right: 10px; top: 10px; border: none; background: none; font-size: 1.5rem; cursor: pointer; color: #333;">&times;</button>
            <h3 style="margin-top: 10px; color: #333;">手機互動配對</h3>
            <div id="qr-code-container" style="margin: 20px auto; background: white; padding: 10px; display: inline-block; border: 1px solid #eee; border-radius: 10px;"></div>
            <p id="mobile-url-hint" style="color: #555; font-size: 0.9rem; margin-bottom: 15px;">掃描上方 QR Code 開始互動</p>
            <button onclick="refreshRemoteToken()" style="width: 100%; padding: 10px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; color: #555; font-size: 0.85rem; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 5px;">
                <span>↻</span> 刷新連線碼
            </button>
        </div>
    </div>

    <?php include "inc/modal.php"; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="js/components.js?v=2"></script>
    <script src="js/remote_client.js?v=3"></script>
    <script src="js/shell_ui.js?v=1"></script>
    <script src="js/player_core.js?v=1"></script>
    <script src="js/player_view.js?v=1"></script>
    <script src="js/playlist_manager.js?v=1"></script>
    <?php if (isset($_SESSION['first_login']) && $_SESSION['first_login'] === true): 
        $w_user = $_SESSION['username']; 
        unset($_SESSION['first_login']); 
    ?>
    <script>showAlert("登入成功", "歡迎 <?php echo htmlspecialchars($w_user); ?> 登入！");</script>
    <?php endif; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
