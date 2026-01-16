<?php
session_start();
require_once("../DB/DB_open.php");
echo "Session SNO: " . ($_SESSION['sno'] ?? 'NULL') . "\n";

if(isset($_SESSION['sno'])) {
    $user_id = $_SESSION['sno'];
    $res = mysqli_query($link, "SELECT * FROM playlists WHERE user_id = '$user_id'");
    echo "Playlists for user $user_id:\n";
    while($row = mysqli_fetch_assoc($res)) {
        echo "- " . $row['name'] . " (ID: " . $row['id'] . ")\n";
    }
} else {
    echo "No user logged in.\n";
}
?>
