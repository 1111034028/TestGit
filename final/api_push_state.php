<?php
session_start();
require_once("../DB/DB_open.php");

$token = $_SESSION['mobile_token'] ?? '';
if (empty($token)) { echo "No token"; exit; }

$data = json_decode(file_get_contents('php://input'), true);

$title = mysqli_real_escape_string($link, $data['title'] ?? '');
$artist = mysqli_real_escape_string($link, $data['artist'] ?? '');
$cover = mysqli_real_escape_string($link, $data['cover'] ?? '');
$is_playing = isset($data['isPlaying']) && $data['isPlaying'] ? 1 : 0;
$curr = floatval($data['currentTime'] ?? 0);
$total = floatval($data['duration'] ?? 0);

$token = mysqli_real_escape_string($link, $token);

$sql = "INSERT INTO mobile_state (token, current_song_title, current_artist, current_cover, is_playing, current_time, total_time)
        VALUES ('$token', '$title', '$artist', '$cover', $is_playing, $curr, $total)
        ON DUPLICATE KEY UPDATE 
            current_song_title='$title', 
            current_artist='$artist', 
            current_cover='$cover', 
            is_playing=$is_playing, 
            current_time=$curr, 
            total_time=$total";

mysqli_query($link, $sql);
echo "OK";

require_once("../DB/DB_close.php");
?>
