<?php
session_start();
require_once("../DB/DB_open.php");

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['sno'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['sno'];
$song_id = isset($_GET['song_id']) ? intval($_GET['song_id']) : 0;

if ($song_id === 0) {
    echo json_encode(['error' => 'Invalid song_id']);
    exit;
}

// Get all user's playlists with information about whether this song is in each playlist
$sql = "SELECT p.id, p.name, 
        (SELECT COUNT(*) FROM playlist_songs ps 
         WHERE ps.playlist_id = p.id AND ps.song_id = $song_id) as in_playlist
        FROM playlists p
        WHERE p.user_id = '$user_id'
        ORDER BY p.name ASC";

$result = mysqli_query($link, $sql);

$playlists = [];
while ($row = mysqli_fetch_assoc($result)) {
    $playlists[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'in_playlist' => (int)$row['in_playlist'] > 0
    ];
}

echo json_encode($playlists);

require_once("../DB/DB_close.php");
?>
