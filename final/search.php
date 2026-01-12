<?php
session_start();
require_once("../DB/DB_open.php");

$keyword = "";
$search_result = null;

// Support both 'q' (from shell) and 'keyword' (legacy)
if (isset($_GET["q"])) {
    $keyword = mysqli_real_escape_string($link, $_GET["q"]);
} elseif (isset($_GET["keyword"])) {
    $keyword = mysqli_real_escape_string($link, $_GET["keyword"]);
}

if ($keyword != "") {
    $sql = "SELECT * FROM songs WHERE title LIKE '%$keyword%' OR artist LIKE '%$keyword%' ORDER BY upload_date DESC";
    $search_result = mysqli_query($link, $sql);
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>搜尋音樂</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <!-- Reuse Player Styles -->
    <style>
        /* Copied from index.php - ideally should be in music.css */
        #player-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #181818;
            border-top: 1px solid #333;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 2000;
        }
        .player-info { display: flex; align-items: center; width: 30%; }
        .player-controls { display: flex; flex-direction: column; align-items: center; width: 40%; }
        .control-buttons { display: flex; align-items: center; gap: 20px; margin-bottom: 8px; }
        .control-btn { background: none; border: none; color: #b3b3b3; cursor: pointer; font-size: 1.2rem; }
        .control-btn:hover { color: white; }
        .play-btn { background: white; color: black; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
        .progress-container { width: 100%; display: flex; align-items: center; gap: 10px; font-size: 0.75rem; color: #b3b3b3; }
        #progress-bar { flex-grow: 1; height: 4px; background: #555; border-radius: 2px; cursor: pointer; position: relative; }
        #progress-fill { height: 100%; background: var(--accent-color); width: 0%; border-radius: 2px; }
        body { padding-bottom: 20px; }
        
        .play-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; border-radius: 8px;
        }
        .song-card:hover .play-overlay { opacity: 1; }
        .card-play-btn { background: var(--accent-color); color: black; border-radius: 50%; width: 48px; height: 48px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        
        /* Search Box */
        .search-container {
            text-align: center;
            margin-bottom: 40px;
        }
        .search-input {
            width: 60%;
            max-width: 500px;
            padding: 15px 25px;
            border-radius: 50px;
            border: none;
            background: white;
            color: #333;
            font-size: 1.1rem;
            outline: none;
        }
        .search-btn {
            padding: 15px 30px;
            border-radius: 50px;
            border: none;
            background: var(--accent-color);
            color: black;
            font-weight: bold;
            font-size: 1rem;
            margin-left: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Nav removed -->
    <div id="content-container" style="margin-top: 20px;">
        <h1 style="text-align: center; margin-bottom: 30px;">搜尋結果</h1>
        
        <?php if ($keyword == ""): ?>
            <div style="text-align: center; color: #777; margin-top: 50px;">
                <p>請使用上方搜尋列輸入關鍵字...</p>
            </div>
        <?php endif; ?>

        <?php if ($search_result): ?>
            <div style="margin-bottom: 20px; color: #b3b3b3;">
                搜尋結果: <?php echo mysqli_num_rows($search_result); ?> 筆
            </div>
            
            <div class="song-list">
                <?php
                if (mysqli_num_rows($search_result) > 0) {
                    while ($row = mysqli_fetch_assoc($search_result)) {
                        $cover = "get_cover.php?id=" . $row['id'];
                ?>
                    <div class="song-card" onclick="playSong('<?php echo $row['title']; ?>', '<?php echo $row['artist']; ?>', 'music/<?php echo $row['file_path']; ?>', '<?php echo $cover; ?>', <?php echo $row['id']; ?>)">
                        <div style="position: relative;">
                            <img src="<?php echo $cover; ?>" class="song-cover">
                            <div class="play-overlay">
                                <button class="card-play-btn">▶</button>
                            </div>
                        </div>
                        <div class="song-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="song-artist"><?php echo htmlspecialchars($row['artist']); ?></div>
                    </div>
                <?php
                    }
                } else {
                    echo "<p style='grid-column: 1/-1; text-align: center; color: #777;'>找不到相符的歌曲。</p>";
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function playSong(title, artist, src, cover, id) {
            // Check if parent exists AND is not self
            if (window.parent && window.parent !== window && window.parent.playSong) {
                window.parent.playSong(title, artist, src, cover, id);
            } else {
                alert("播放器載入錯誤");
            }
            
            // Increment Play Count
            fetch(`play_count.php?id=${id}`);
        }
    </script>
    
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
