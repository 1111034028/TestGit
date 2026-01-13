<?php
session_start();
require_once("../DB/DB_open.php");

$keyword = "";
$search_result = null;

// 支援 'q' (來自 Shell) 與 'keyword' (舊版)
if (isset($_GET["q"])) {
    $keyword = mysqli_real_escape_string($link, $_GET["q"]);
} elseif (isset($_GET["keyword"])) {
    $keyword = mysqli_real_escape_string($link, $_GET["keyword"]);
}

if ($keyword != "") {
    // 1. Pagination Init
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if ($page < 1) $page = 1;
    $limit = 18; // Same grid size as homepage
    $offset = ($page - 1) * $limit;

    // 2. Count Total Results
    $count_sql = "SELECT COUNT(*) as total FROM songs WHERE title LIKE '%$keyword%' OR artist LIKE '%$keyword%'";
    $count_result = mysqli_query($link, $count_sql);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_items = $count_row['total'];
    $total_pages = ceil($total_items / $limit);
    if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

    // 3. Fetch Page Items with Weighted Sorting (Title Match > Artist Match)
    $sql = "SELECT * FROM songs 
            WHERE title LIKE '%$keyword%' OR artist LIKE '%$keyword%' 
            ORDER BY 
              (title LIKE '%$keyword%') DESC, 
              (artist LIKE '%$keyword%') DESC, 
              upload_date DESC 
            LIMIT $offset, $limit";
    $search_result = mysqli_query($link, $sql);
}
?>
<?php
$page_title = "搜尋音樂";
require_once("inc/header.php");
?>
    <!-- 重複使用播放器樣式 -->
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="css/index_content.css?v=2">
<body>
    <!-- 移除導覽列 -->
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
                    echo "<p style='grid-column: 1/-1; text-align: center; color: #777;'>找不到相符的歌曲。</p>";
                }
                ?>
            </div>
            
            <!-- Pagination Controls -->
            <?php if (isset($total_pages) && $total_pages > 1): ?>
            <div class="pagination-container">
                <!-- First Page -->
                <?php if ($page > 1): ?>
                    <a href="search.php?q=<?php echo urlencode($keyword); ?>&page=1" class="page-btn">&laquo; 最前頁</a>
                    <a href="search.php?q=<?php echo urlencode($keyword); ?>&page=<?php echo $page-1; ?>" class="page-btn">&lsaquo; 上一頁</a>
                <?php else: ?>
                    <span class="page-btn disabled">&laquo; 最前頁</span>
                    <span class="page-btn disabled">&lsaquo; 上一頁</span>
                <?php endif; ?>
                
                <span class="page-info">第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁</span>
                
                <!-- Next & Last Page -->
                <?php if ($page < $total_pages): ?>
                    <a href="search.php?q=<?php echo urlencode($keyword); ?>&page=<?php echo $page+1; ?>" class="page-btn">下一頁 &rsaquo;</a>
                    <a href="search.php?q=<?php echo urlencode($keyword); ?>&page=<?php echo $total_pages; ?>" class="page-btn">最後頁 &raquo;</a>
                <?php else: ?>
                    <span class="page-btn disabled">下一頁 &rsaquo;</span>
                    <span class="page-btn disabled">最後頁 &raquo;</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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
<?php require_once("../DB/DB_close.php"); ?>
