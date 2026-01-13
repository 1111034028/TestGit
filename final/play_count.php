<?php
require_once("../DB/DB_open.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "UPDATE songs SET play_count = play_count + 1, last_played_at = NOW() WHERE id = $id";
    mysqli_query($link, $sql);
}

require_once("../DB/DB_close.php");
?>
