<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

$sql = "CREATE TABLE IF NOT EXISTS mobile_state (
    token VARCHAR(64) PRIMARY KEY,
    current_song_title VARCHAR(255),
    current_artist VARCHAR(255),
    current_cover VARCHAR(500),
    is_playing BOOLEAN DEFAULT 0,
    current_time FLOAT DEFAULT 0,
    total_time FLOAT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($link, $sql)) {
    echo "Table mobile_state created successfully.";
} else {
    echo "Error: " . mysqli_error($link);
}

require_once("../DB/DB_close.php");
?>
