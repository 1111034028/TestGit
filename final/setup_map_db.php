<?php
require_once(__DIR__ . "/DB/DB_open.php");

echo "<h2>Setting up Map Database...</h2>";

// 1. Check/Add image_path to music_marks
$check_col = mysqli_query($link, "SHOW COLUMNS FROM music_marks LIKE 'image_path'");
if(mysqli_num_rows($check_col) == 0) {
    $sql = "ALTER TABLE music_marks ADD COLUMN image_path VARCHAR(255) NULL AFTER message";
    if(mysqli_query($link, $sql)) {
        echo "<p style='color:green'>[Success] Added column 'image_path' to 'music_marks'.</p>";
    } else {
        echo "<p style='color:red'>[Error] Failed to add column: " . mysqli_error($link) . "</p>";
    }
} else {
    echo "<p style='color:blue'>[Info] Column 'image_path' already exists.</p>";
}

// 2. Check/Add location_name to music_marks (just in case)
$check_col2 = mysqli_query($link, "SHOW COLUMNS FROM music_marks LIKE 'location_name'");
if(mysqli_num_rows($check_col2) == 0) {
    $sql2 = "ALTER TABLE music_marks ADD COLUMN location_name VARCHAR(100) NULL AFTER message";
    if(mysqli_query($link, $sql2)) {
        echo "<p style='color:green'>[Success] Added column 'location_name' to 'music_marks'.</p>";
    } else {
        echo "<p style='color:red'>[Error] Failed to add column: " . mysqli_error($link) . "</p>";
    }
} else {
    echo "<p style='color:blue'>[Info] Column 'location_name' already exists.</p>";
}

require_once("DB/DB_close.php");
?>
