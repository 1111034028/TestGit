<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

if (isset($_GET['id'])) {
    $song_id = intval($_GET['id']);
    $username = $_SESSION["username"];
    $user = get_user_info($link, $username);
    $sno = $user['sno'] ?? '';
    
    $where = (is_admin($link, $username)) ? "id = $song_id" : "id = $song_id AND uploader_id = '$sno'";
    
    $res = mysqli_query($link, "SELECT file_path FROM songs WHERE $where");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['file_path']) && file_exists("music/" . $row['file_path'])) {
            unlink("music/" . $row['file_path']);
        }
        db_delete($link, 'songs', "id = $song_id");
        header("Location: creator.php?success=delete");
        exit;
    } else {
        header("Location: creator.php?error=unauthorized");
        exit;
    }
}
header("Location: creator.php");
require_once("../DB/DB_close.php");
?>
