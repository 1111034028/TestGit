<?php
require_once("../DB/DB_open.php");

// Add last_played_at column if it doesn't exist
$sql = "SHOW COLUMNS FROM songs LIKE 'last_played_at'";
$result = mysqli_query($link, $sql);

if (mysqli_num_rows($result) == 0) {
    $alter_sql = "ALTER TABLE songs ADD COLUMN last_played_at DATETIME DEFAULT NULL";
    if (mysqli_query($link, $alter_sql)) {
        echo "Column 'last_played_at' added successfully.";
    } else {
        echo "Error adding column: " . mysqli_error($link);
    }
} else {
    echo "Column 'last_played_at' already exists.";
}

require_once("../DB/DB_close.php");
?>
