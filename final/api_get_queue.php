<?php
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$shuffle = isset($_GET['shuffle']) ? $_GET['shuffle'] : 0;

$songs = [];

if ($type == 'playlist' && $id > 0) {
    // 從特定播放清單取得歌曲
    $sql = "SELECT s.* FROM songs s 
            JOIN playlist_songs ps ON s.id = ps.song_id 
            WHERE ps.playlist_id = $id 
            ORDER BY ps.sort_order ASC";
} else {
    // 預設：取得所有歌曲
    $sql = "SELECT * FROM songs";
    
    // 如果不是 shuffle 模式，預設依照上傳與排序
    if ($shuffle != 1 || $type != 'all') {
         $sql .= " ORDER BY upload_date DESC";
    }
}

$result = mysqli_query($link, $sql);

if ($result) {
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    
    // Check for anchoring current song
    $current_id = isset($_GET['current_id']) ? intval($_GET['current_id']) : 0;
    $current_song = null;

    // -------------------------------------------------------------
    // 加權隨機洗牌演算法 (Weighted Shuffle)
    // -------------------------------------------------------------
    if ($shuffle == 1) {
        // 1. Extract Current Song if exists
        if ($current_id > 0) {
            foreach ($rows as $key => $row) {
                if ($row['id'] == $current_id) {
                    $current_song = $row;
                    unset($rows[$key]);
                    break;
                }
            }
            $rows = array_values($rows); // Re-index
        }

        // 2. Shuffle Remaining Rows
        foreach ($rows as &$item) {
            if (empty($item['last_played_at'])) {
                // 從未播放過：給予極大權重 (例如 100年)
                $weight = 3153600000;
            } else {
                // 計算距離現在經過的秒數
                $last_time = strtotime($item['last_played_at']);
                $diff = time() - $last_time;
                $diff = max($diff, 1);
                
                // 權重 = 經過時間的平方
                $weight = pow($diff, 2);
            }
            
            $r = mt_rand() / mt_getrandmax();
            if ($r <= 0) $r = 0.00000001;
            $item['_shuffle_score'] = (1 / $weight) * log($r);
        }
        unset($item);

        usort($rows, function($a, $b) {
            return ($b['_shuffle_score'] <=> $a['_shuffle_score']);
        });
        
        // 3. Prepend Current Song
        if ($current_song) {
            array_unshift($rows, $current_song);
        }
    }

    // 輸出處理
    foreach ($rows as $row) {
        $cover = "get_cover.php?id=" . $row['id'];
        $songs[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'artist' => $row['artist'],
            'file_path' => "music/" . $row['file_path'],
            'cover' => $cover,
            'genre' => $row['genre']
            // 除錯用: 'last_played' => $row['last_played_at']
        ];
    }
}

echo json_encode($songs);
require_once("../DB/DB_close.php");
?>
