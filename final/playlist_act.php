<?php
session_start();
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}

require_once("../DB/DB_open.php");

$action = isset($_POST['action']) ? $_POST['action'] : '';
$user_id = $_SESSION['sno']; // Assuming sno is user_id

if ($action == 'create') {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    if (!empty($name)) {
        $sql = "INSERT INTO playlists (user_id, name) VALUES ('$user_id', '$name')";
        if (mysqli_query($link, $sql)) {
            // Success
            header("Location: my_playlists.php");
        } else {
            echo "Error: " . mysqli_error($link);
        }
    }
} elseif ($action == 'add_song') {
    $playlist_id = intval($_POST['playlist_id']);
    $song_id = intval($_POST['song_id']);
    
    // Check ownership
    $check_sql = "SELECT id FROM playlists WHERE id = $playlist_id AND user_id = '$user_id'";
    if (mysqli_num_rows(mysqli_query($link, $check_sql)) > 0) {
        $sql = "INSERT INTO playlist_songs (playlist_id, song_id) VALUES ($playlist_id, $song_id)";
        mysqli_query($link, $sql);
        // Alert?
        echo "<script>alert('已加入歌單！'); history.back();</script>";
    } else {
        die("無權限");
    }
} elseif ($action == 'delete') {
    $id = intval($_POST['playlist_id']);
    $sql = "DELETE FROM playlists WHERE id = $id AND user_id = '$user_id'";
    mysqli_query($link, $sql);
    header("Location: my_playlists.php");
} elseif ($action == 'remove_song') {
    $playlist_id = intval($_POST['playlist_id']);
    $song_id = intval($_POST['song_id']);
    
    // Check ownership
    $check_sql = "SELECT id FROM playlists WHERE id = $playlist_id AND user_id = '$user_id'";
    if (mysqli_num_rows(mysqli_query($link, $check_sql)) > 0) {
        $sql = "DELETE FROM playlist_songs WHERE playlist_id = $playlist_id AND song_id = $song_id";
        mysqli_query($link, $sql);
        header("Location: playlist_view.php?id=$playlist_id");
    }
}

require_once("../DB/DB_close.php");
?>
