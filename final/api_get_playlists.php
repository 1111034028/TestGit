<?php
header('Content-Type: application/json');
session_start();
require_once("../DB/DB_open.php");

if (!isset($_SESSION['sno'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['sno'];
$sql = "SELECT * FROM playlists WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($link, $sql);

$playlists = [];
while ($row = mysqli_fetch_assoc($result)) {
    $playlists[] = $row;
}

echo json_encode($playlists);
require_once("../DB/DB_close.php");
?>
