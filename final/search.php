<?php
session_start();
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

$keyword = "";
$search_result = null;

if (isset($_GET["q"])) {
    $keyword = mysqli_real_escape_string($link, $_GET["q"]);
} elseif (isset($_GET["keyword"])) {
    $keyword = mysqli_real_escape_string($link, $_GET["keyword"]);
}

if ($keyword != "") {
    // 1. Pagination Init
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if ($page < 1) $page = 1;
    $limit = 18;
    $offset = ($page - 1) * $limit;

    // 2. Count Total Results
    $count_sql = "SELECT COUNT(*) as total FROM songs WHERE title LIKE '%$keyword%' OR artist LIKE '%$keyword%'";
    $count_result = mysqli_query($link, $count_sql);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_items = $count_row['total'];
    $total_pages = ceil($total_items / $limit);
    if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

    // 3. Fetch Page Items with Weighted Sorting
    $sql = "SELECT * FROM songs 
            WHERE title LIKE '%$keyword%' OR artist LIKE '%$keyword%' 
            ORDER BY 
              (title LIKE '%$keyword%') DESC, 
              (artist LIKE '%$keyword%') DESC, 
              upload_date DESC 
            LIMIT $offset, $limit";
    $search_result = mysqli_query($link, $sql);
}

$page_title = "搜尋音樂";
require_once("inc/header.php");
?>
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="css/index_content.css?v=2">
<body>
    <div id="content-container" style="margin-top: 20px;">
        <h1 style="text-align: center; margin-bottom: 30px;">搜尋結果</h1>
        
        <?php if ($keyword == ""): ?>
            <div style="text-align: center; color: #777; margin-top: 50px;">
                <p>請使用上方搜尋列輸入關鍵字...</p>
            </div>
        <?php else: ?>
            <h2 style="margin-bottom: 15px; border-left: 4px solid var(--accent-color); padding-left: 10px;">歌曲</h2>
            <?php if ($search_result && mysqli_num_rows($search_result) > 0): ?>
                <div style="margin-bottom: 20px; color: #b3b3b3;">
                    找到 <?php echo $total_items; ?> 筆歌曲
                </div>
                
                <div class="song-list" style="margin-bottom: 40px;">
                    <?php
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
                    <?php } ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination-container" style="margin-bottom: 50px;">
                    <?php if ($page > 1): ?>
                        <a href="search.php?q=<?php echo urlencode($keyword); ?>&page=1" class="page-btn">&laquo;</a>
                        <a href="search.php?q=<?php echo urlencode($keyword); ?>&page=<?php echo $page-1; ?>" class="page-btn">&lsaquo; 上一頁</a>
                    <?php endif; ?>
                    <span class="page-info"><?php echo $page; ?> / <?php echo $total_pages; ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="search.php?q=<?php echo urlencode($keyword); ?>&page=<?php echo $page+1; ?>" class="page-btn">下一頁 &rsaquo;</a>
                        <a href="search.php?q=<?php echo urlencode($keyword); ?>&page=<?php echo $total_pages; ?>" class="page-btn">&raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <p style="text-align: center; color: #777; padding: 20px;">找不到相符的歌曲。</p>
            <?php endif; ?>

            <?php
            $pl_result = search_playlists($link, $keyword);
            ?>
            <h2 style="margin-top: 40px; margin-bottom: 15px; border-left: 4px solid #1db954; padding-left: 10px;">播放清單</h2>
            <?php if (mysqli_num_rows($pl_result) > 0): ?>
                <div class="song-list">
                    <?php
                    while ($row = mysqli_fetch_assoc($pl_result)) {
                        $pid = $row['id'];
                        $song_count = count_playlist_songs($link, $pid);
                    ?>
                        <div class="song-card" onclick="location.href='playlist_view.php?id=<?php echo $pid; ?>'">
                            <div style="width: 100%; height: 160px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 10px;">
                                <span style="font-size: 3rem;">🎵</span>
                            </div>
                            <div class="song-title"><?php echo htmlspecialchars($row['name']); ?></div>
                            <div class="song-artist">
                                由 <?php echo htmlspecialchars($row['creator_name']); ?> 建立<br>
                                <?php echo $song_count; ?> 首歌曲
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #777; padding: 20px;">找不到相符的播放清單。</p>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <div id="playlist-modal">
        <div class="modal-content">
            <form><!-- Dynamic content --></form>
        </div>
    </div>

    <script src="js/player_bridge.js?v=5"></script>
    <script src="js/index_content.js?v=5"></script>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
