<?php
session_start();
require_once("../DB/DB_open.php");

$search = isset($_GET['q']) ? mysqli_real_escape_string($link, $_GET['q']) : '';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>搜尋歌單 - 音樂串流平台</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
</head>
<body>
    <!-- Nav removed -->

    <div id="content-container">
        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <a href="my_playlists.php" class="btn-secondary">所有歌單</a>
            <a href="playlist_create.php" class="btn-secondary">建立歌單</a>
            <a href="playlist_search.php" class="btn-primary">搜尋歌單</a>
        </div>

        <h1>搜尋歌單</h1>
        
        <form action="" method="get" style="margin-bottom: 30px; display: flex; gap: 10px;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="輸入歌單名稱或建立者..." 
                   style="flex-grow: 1; padding: 15px; border-radius: 4px; border: 1px solid #444; background: #282828; color: white; font-size: 1.1rem;">
            <button type="submit" class="btn-primary" style="padding: 0 30px;">搜尋</button>
        </form>

        <div class="song-list">
            <?php
            if ($search) {
                // Search in playlists name or user's name
                // Need join to get creator name
                $sql = "SELECT p.*, s.name as creator_name FROM playlists p 
                        JOIN students s ON p.user_id = s.sno 
                        WHERE p.name LIKE '%$search%' OR s.name LIKE '%$search%' 
                        ORDER BY p.created_at DESC";
                $result = mysqli_query($link, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $pid = $row['id'];
                        // Count songs
                        $c_sql = "SELECT COUNT(*) as cnt FROM playlist_songs WHERE playlist_id = $pid";
                        $c_res = mysqli_query($link, $c_sql);
                        $c_row = mysqli_fetch_assoc($c_res);
                        $count = $c_row['cnt'];
            ?>
                <div class="song-card" onclick="location.href='playlist_view.php?id=<?php echo $pid; ?>'">
                    <div style="width: 100%; height: 160px; background: #444; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 10px;">
                        <span style="font-size: 3rem;">🔍</span>
                    </div>
                    <div class="song-title"><?php echo htmlspecialchars($row['name']); ?></div>
                    <div class="song-artist">
                        by <?php echo htmlspecialchars($row['creator_name']); ?><br>
                        <?php echo $count; ?> 首歌曲
                    </div>
                </div>
            <?php
                    }
                } else {
                    echo "<p style='grid-column: 1/-1; text-align: center; color: #777;'>找不到相符的歌單。</p>";
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center; color: #777;'>請輸入關鍵字進行搜尋。</p>";
            }
            ?>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
