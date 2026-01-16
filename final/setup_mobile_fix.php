<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

// Add user_id column if it doesn't exist (assuming it might be missing or we want to ensure it works)
// Note: In MySQL, ADD COLUMN IF NOT EXISTS is only available in newer versions (8.0+). 
// For compatibility, we can query checks.

$check = mysqli_query($link, "SHOW COLUMNS FROM mobile_tokens LIKE 'user_id'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE mobile_tokens ADD COLUMN user_id INT DEFAULT NULL";
    if (mysqli_query($link, $sql)) {
        echo "Added user_id column.<br>";
    } else {
        echo "Error adding user_id: " . mysqli_error($link) . "<br>";
    }
} else {
    echo "user_id column already exists.<br>";
}

// Also ensure username column exists as fallback/legacy
$check = mysqli_query($link, "SHOW COLUMNS FROM mobile_tokens LIKE 'username'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE mobile_tokens ADD COLUMN username VARCHAR(50) DEFAULT NULL";
    mysqli_query($link, $sql);
}

// Clear old tokens to avoid confusion
mysqli_query($link, "DELETE FROM mobile_tokens");

echo "Mobile DB Fixed.";
require_once("../DB/DB_close.php");
?>
