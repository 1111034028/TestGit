<?php
require_once("../DB/DB_open.php");

// Add user_id column if it doesn't exist
$check = mysqli_query($link, "SHOW COLUMNS FROM mobile_tokens LIKE 'user_id'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE mobile_tokens ADD COLUMN user_id INT DEFAULT NULL";
    if (mysqli_query($link, $sql)) {
        echo "Added user_id column.\n";
    } else {
        echo "Error adding user_id: " . mysqli_error($link) . "\n";
    }
} else {
    echo "user_id column already exists.\n";
}

// Ensure username column exists
$check = mysqli_query($link, "SHOW COLUMNS FROM mobile_tokens LIKE 'username'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE mobile_tokens ADD COLUMN username VARCHAR(50) DEFAULT NULL";
    mysqli_query($link, $sql);
    echo "Added username column.\n";
}

// Clean table
mysqli_query($link, "TRUNCATE TABLE mobile_tokens");
echo "Mobile DB Fixed and Cleared.\n";

require_once("../DB/DB_close.php");
?>
