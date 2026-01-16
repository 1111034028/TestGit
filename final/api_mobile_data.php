<?php
header('Content-Type: application/json');
session_start();
require_once("../DB/DB_open.php");

$action = $_GET['action'] ?? '';

if ($action === 'get_home_songs') {
    $query = mysqli_real_escape_string($link, $_GET['q'] ?? '');
    
    if (!empty($query)) {
        // Search mode
        $sql = "SELECT id, title, artist, file_path FROM songs 
                WHERE title LIKE '%$query%' OR artist LIKE '%$query%' 
                ORDER BY title ASC LIMIT 50";
    } else {
        // Default shuffled discovery
        $sql = "SELECT id, title, artist, file_path FROM songs ORDER BY RAND() LIMIT 30";
    }
    
    $result = mysqli_query($link, $sql);
    $songs = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['cover'] = "get_cover.php?id=" . $row['id'];
            $songs[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $songs]);

} elseif ($action === 'get_playlists') {
    if (!isset($_SESSION['sno'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in', 'debug_sid' => session_id()]);
        exit;
    }
    $user_id = $_SESSION['sno'];
    
    // Ensure "My Favorites" exists
    $check_sql = "SELECT id FROM playlists WHERE user_id = '$user_id' AND name = 'My Favorites'";
    $check_res = mysqli_query($link, $check_sql);
    if ($check_res && mysqli_num_rows($check_res) == 0) {
        $create_sql = "INSERT INTO playlists (user_id, name) VALUES ('$user_id', 'My Favorites')";
        mysqli_query($link, $create_sql);
    }
    
    // Sort logic matching desktop
    $sql = "SELECT * FROM playlists WHERE user_id = '$user_id' ORDER BY (name = 'My Favorites') DESC, created_at DESC";
    $result = mysqli_query($link, $sql);
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . mysqli_error($link)]);
        exit;
    }
    $playlists = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $playlists[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $playlists]);

} elseif ($action === 'get_playlist_songs') {
    $pid = intval($_GET['id'] ?? 0);
    if ($pid <= 0) {
        echo json_encode(['status' => 'error', 'message' => '無效的歌單 ID']);
        exit;
    }
    
    // Get playlist info first
    $name_res = mysqli_query($link, "SELECT name FROM playlists WHERE id = $pid LIMIT 1");
    if (!$name_res || mysqli_num_rows($name_res) == 0) {
        echo json_encode(['status' => 'error', 'message' => '找不到該歌單']);
        exit;
    }
    $pname = mysqli_fetch_assoc($name_res)['name'] ?? '我的歌單';

    // Get songs
    $sql = "SELECT s.id, s.title, s.artist, s.file_path, ps.id as link_id 
            FROM playlist_songs ps 
            JOIN songs s ON ps.song_id = s.id 
            WHERE ps.playlist_id = $pid 
            ORDER BY ps.id DESC";
    
    $result = mysqli_query($link, $sql);
    $songs = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['cover'] = "get_cover.php?id=" . $row['id'];
            $songs[] = $row;
        }
    }

    echo json_encode([
        'status' => 'success', 
        'playlist_name' => $pname, 
        'data' => $songs
    ]);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

require_once("../DB/DB_close.php");
