<?php
// Isomorphic Shell Pattern
// index.php acts as both the App Shell (Persistent Player) AND the Content Page (Song List)

session_start();
require_once("../DB/DB_open.php");

// ---------------------------------------------------------
// MODE 1: Content Page (Song List)
// If 'inner' param is set, we show the song list content
// ---------------------------------------------------------
if (isset($_GET['inner'])) {
    // 獲取所有歌曲 (倒序)
    $sql = "SELECT * FROM songs ORDER BY upload_date DESC";
    $result = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>音樂串流平台 - 首頁</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <link rel="stylesheet" href="css/index_content.css">
</head>
<body>
    <!-- Nav removed in favor of App Shell Sidebar -->

    <div id="content-container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>探索最新音樂</h1>
            <button class="btn-primary" onclick="playAllShuffle()">🌏 隨機播放全部</button>
        </div>
        
        <div class="song-list">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $cover = "get_cover.php?id=" . $row['id'];
            ?>
                <div class="song-card">
                    <div style="position: relative;" onclick="playSongInContext('<?php echo htmlspecialchars(addslashes($row['title'])); ?>', '<?php echo htmlspecialchars(addslashes($row['artist'])); ?>', 'music/<?php echo $row['file_path']; ?>', '<?php echo $cover; ?>', <?php echo $row['id']; ?>)">
                        <img src="<?php echo $cover; ?>" class="song-cover">
                        <div class="play-overlay">
                            <span class="card-play-btn">▶</span>
                        </div>
                    </div>
                    <div class="song-title"><?php echo htmlspecialchars($row['title']); ?></div>
                    <div class="song-artist" style="display: flex; justify-content: space-between; align-items: center;">
                        <span><?php echo htmlspecialchars($row['artist']); ?></span>
                        <button style="background: none; border: none; color: #aaa; cursor: pointer; font-size: 1.2rem;" onclick="openPlaylistModal(<?php echo $row['id']; ?>)" title="加入播放清單">+</button>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center; color: #777;'>目前還沒有任何歌曲。</p>";
            }
            ?>
        </div>
    </div>
    
    <!-- Playlist Modal -->
    <div id="playlist-modal">
        <div class="modal-content">
            <h3>加入播放清單</h3>
            <form action="playlist_act.php" method="post">
                <input type="hidden" name="action" value="add_song">
                <input type="hidden" name="song_id" id="modal-song-id">
                <select name="playlist_id" id="modal-playlist-select" style="width: 100%; padding: 10px; margin-bottom: 20px; background: #444; color: white; border: none;" required>
                    <!-- Options loaded via JS -->
                </select>
                <div style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('playlist-modal').style.display='none'">取消</button>
                    <button type="submit" class="btn-primary">確定</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/index_content.js"></script>

    <?php include "foot.html"; ?>
</body>
</html>
<?php 
    exit; 
} 
?>

<!-- --------------------------------------------------------- -->
<!-- MODE 2: App Shell -->
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
        <!-- Sidebar -->
        <div id="sidebar">
            <div style="display: flex; align-items: center; padding-left: 15px; margin-bottom: 20px; height: 40px;">
                <button class="menu-btn" onclick="toggleSidebar()" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer; margin-right:10px;">☰</button>
                <div class="sidebar-logo" onclick="location.href='index.php'" style="margin:0;">Music Stream</div>
            </div>
            
            <a href="index.php?inner=1" target="content-frame" onclick="highlightNav('nav-home')" class="nav-item active" id="nav-home" title="首頁">
                <span class="nav-icon">🏠</span> <span class="nav-text">首頁</span>
            </a>
            <a href="search.php" target="content-frame" onclick="highlightNav('nav-search')" class="nav-item" id="nav-search" title="搜尋">
                <span class="nav-icon">🔍</span> <span class="nav-text">搜尋</span>
            </a>
            <a href="my_playlists.php" target="content-frame" onclick="highlightNav('nav-library')" class="nav-item" id="nav-library" title="播放清單">
                <span class="nav-icon">📚</span> <span class="nav-text">播放清單</span>
            </a>
            <!-- Separator -->
            <hr style="border: 0; border-top: 1px solid #282828; margin: 10px 20px;">
            
            <a href="../hw/index.php" class="nav-item" title="回作業首頁">
                <span class="nav-icon">↩️</span> <span class="nav-text">回作業首頁</span>
            </a>
        </div>
        
        <!-- Content -->
        <div style="display: flex; flex-direction: column; flex: 1;">
            <div id="top-bar">
                <!-- Menu Btn moved to Sidebar -->
                <form class="search-container" action="search.php" target="content-frame" onsubmit="highlightNav('nav-search')">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="q" class="search-input" placeholder="搜尋歌曲、專輯、藝人...">
                </form>
                
                <div style="margin-left: auto; display: flex; gap: 15px; position: relative;">
                    <?php if(isset($_SESSION['login_session'])): ?>
                       <!-- Avatar (High margin for Equal Top/Right Spacing) -->
                       <div onclick="toggleUserMenu()" style="width: 55px; height: 55px; background: #535c68; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; color: white; overflow: hidden; font-size: 1.5rem; margin-right: 30px; margin-top: 30px; align-self: flex-start;" title="帳戶選單">
                           <?php 
                               if (isset($_SESSION['picture']) && !empty($_SESSION['picture']) && file_exists("img/avatars/" . $_SESSION['picture'])) {
                                   echo '<img src="img/avatars/' . htmlspecialchars($_SESSION['picture']) . '" style="width: 100%; height: 100%; object-fit: cover;">';
                               } else {
                                   echo strtoupper(substr($_SESSION['username'], 0, 1));
                               }
                           ?>
                       </div>
                       
                       <!-- Dropdown Menu -->
                       <div id="user-menu" style="display: none; position: absolute; top: 100px; right: 35px; background: #282828; border-radius: 8px; width: 220px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); z-index: 100; overflow: hidden; padding: 10px 0;">
                           <!-- Header Info -->
                           <div style="padding: 10px 20px; border-bottom: 1px solid #3e3e3e; margin-bottom: 5px;">
                               <div style="font-weight: bold; color: white;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                               <div style="font-size: 0.8rem; color: #aaa;">@<?php echo htmlspecialchars($_SESSION['username']); ?></div>
                           </div>
                           
                           <!-- Menu Items -->
                           <a href="creator.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()">
                               <span style="width: 24px; text-align: center;">🎨</span> 創作者工作室
                           </a>
                           
                           <a href="profile.php" target="content-frame" class="menu-item" onclick="toggleUserMenu()">
                               <span style="width: 24px; text-align: center;">👤</span> 個人資料
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
        <!-- Progress Bar at Top -->
        <div id="progress-bar" onclick="seek(event)">
            <div id="progress-fill"></div>
        </div>

        <!-- Left: Controls & Time -->
        <div class="player-left">
            <button class="control-btn" onclick="prevSong()">⏮</button>
            <button class="control-btn play-btn" onclick="togglePlay()" id="main-play-btn">▶</button>
            <button class="control-btn" onclick="nextSong()">⏭</button>
            <span class="time-display">
                <span id="curr-time">0:00</span> / <span id="total-time">0:00</span>
            </span>
        </div>

        <!-- Center: Info & Like -->
        <div class="player-center">
            <img id="player-cover" src="">
            <div class="track-info">
                <div id="player-title"></div>
                <div id="player-artist"></div>
            </div>
            <button class="control-btn" id="like-btn" onclick="toggleLike()" title="加入最愛">♡</button>
        </div>

        <!-- Right: Tools & Volume -->
        <div class="player-right">
            <!-- Volume Control -->
            <div class="volume-container">
                <span id="volume-value">100%</span>
                <input type="range" id="volume-slider" min="0" max="1" step="0.01" value="1">
                <button class="control-btn" id="volume-btn" onclick="toggleMute()" title="靜音">🔊</button>
            </div>
            
            <button class="control-btn" id="mode-btn" onclick="toggleMode()" title="切換模式">🔁</button>
            <!-- Shuffle (Optional, can be integrated into mode or separate) -->
            <!-- <button class="control-btn" title="隨機播放">🔀</button> -->
            
           <div id="queue-info" style="font-size: 0.7rem; color: #777; border: 1px solid #333; padding: 2px 6px; border-radius: 4px;">Q: 0</div>
        </div>
        
        <audio id="audio-player"></audio>
    </div>

    <script src="js/index_shell.js"></script>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
