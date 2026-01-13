<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    echo json_encode(['error' => 'Not logged in', 'liked' => false]);
    exit;
}

require_once("../DB/DB_open.php");

$user_id = $_SESSION['sno'];
$song_id = isset($_REQUEST['song_id']) ? intval($_REQUEST['song_id']) : 0;
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? 'toggle' : 'check';

if ($song_id <= 0) {
    echo json_encode(['error' => 'Invalid song ID']);
    exit;
}

// 尋找或建立 "我的最愛" 播放清單
$playlist_name = "My Favorites";
$p_sql = "SELECT id FROM playlists WHERE user_id = '$user_id' AND name = '$playlist_name' LIMIT 1";
$p_result = mysqli_query($link, $p_sql);

if (mysqli_num_rows($p_result) > 0) {
    $p_row = mysqli_fetch_assoc($p_result);
    $playlist_id = $p_row['id'];
} else {
    // 建立它
    $create_sql = "INSERT INTO playlists (user_id, name) VALUES ('$user_id', '$playlist_name')";
    if (mysqli_query($link, $create_sql)) {
        $playlist_id = mysqli_insert_id($link);
    } else {
        echo json_encode(['error' => 'Failed to create playlist']);
        exit;
    }
}

// 檢查歌曲是否在播放清單中
$c_sql = "SELECT id FROM playlist_songs WHERE playlist_id = $playlist_id AND song_id = $song_id";
$c_result = mysqli_query($link, $c_sql);
$exists = mysqli_num_rows($c_result) > 0;

if ($action === 'check') {
    echo json_encode(['liked' => $exists]);
} elseif ($action === 'toggle') {
    if ($exists) {
        // 移除
        $del_sql = "DELETE FROM playlist_songs WHERE playlist_id = $playlist_id AND song_id = $song_id";
        mysqli_query($link, $del_sql);
        echo json_encode(['liked' => false]);
    } else {
        // 新增
        $add_sql = "INSERT INTO playlist_songs (playlist_id, song_id) VALUES ($playlist_id, $song_id)";
        mysqli_query($link, $add_sql);
        echo json_encode(['liked' => true]);
    }
}

require_once("../DB/DB_close.php");
?>
