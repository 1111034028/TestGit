<?php
// 清理播放清單中的重複歌曲
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

$user_id = $_SESSION['sno'];

// 獲取所有使用者的播放清單
$playlists_sql = "SELECT id, name FROM playlists WHERE user_id = '$user_id'";
$playlists_result = mysqli_query($link, $playlists_sql);

$cleaned_count = 0;
$total_playlists = 0;

while ($playlist = mysqli_fetch_assoc($playlists_result)) {
    $playlist_id = $playlist['id'];
    $playlist_name = $playlist['name'];
    $total_playlists++;
    
    // 找出這個播放清單中的重複歌曲
    $duplicates_sql = "SELECT song_id, COUNT(*) as count, MIN(id) as keep_id
                       FROM playlist_songs 
                       WHERE playlist_id = $playlist_id 
                       GROUP BY song_id 
                       HAVING count > 1";
    
    $duplicates_result = mysqli_query($link, $duplicates_sql);
    
    while ($dup = mysqli_fetch_assoc($duplicates_result)) {
        $song_id = $dup['song_id'];
        $keep_id = $dup['keep_id'];
        $count = $dup['count'];
        
        // 刪除重複的（保留最早加入的那一個）
        $delete_sql = "DELETE FROM playlist_songs 
                       WHERE playlist_id = $playlist_id 
                       AND song_id = $song_id 
                       AND id != $keep_id";
        
        if (mysqli_query($link, $delete_sql)) {
            $deleted = $count - 1;
            $cleaned_count += $deleted;
            echo "在播放清單「{$playlist_name}」中移除了 {$deleted} 個重複的歌曲<br>";
        }
    }
}

echo "<br><strong>清理完成！</strong><br>";
echo "檢查了 {$total_playlists} 個播放清單<br>";
echo "總共移除了 {$cleaned_count} 個重複項目<br>";
echo "<br><a href='my_playlists.php'>返回我的播放清單</a>";

require_once("../DB/DB_close.php");
?>
