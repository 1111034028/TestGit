<?php
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$shuffle = isset($_GET['shuffle']) ? $_GET['shuffle'] : 0;

$songs = [];

if ($type == 'playlist' && $id > 0) {
    // Fetch songs from a specific playlist
    $sql = "SELECT s.* FROM songs s 
            JOIN playlist_songs ps ON s.id = ps.song_id 
            WHERE ps.playlist_id = $id 
            ORDER BY ps.sort_order ASC";
} else {
    // Default: Fetch all songs
    $sql = "SELECT * FROM songs ORDER BY upload_date DESC";
    
    // If global shuffle is requested server-side (optional, but client can also shuffle)
    // Here we just fetch all, client handles shuffle logic usually for cleaner state,
    // but for 'Global Shuffle' mode, fetching random order from DB is efficient.
    if ($shuffle == 1 && $type == 'all') {
        $sql = "SELECT * FROM songs ORDER BY RAND()";
    }
}

$result = mysqli_query($link, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cover = "get_cover.php?id=" . $row['id'];
        $songs[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'artist' => $row['artist'],
            'file_path' => "music/" . $row['file_path'],
            'cover' => $cover,
            'genre' => $row['genre']
        ];
    }
}

echo json_encode($songs);
require_once("../DB/DB_close.php");
?>
