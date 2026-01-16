<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

$sql = "CREATE TABLE IF NOT EXISTS mobile_tokens (
    token VARCHAR(64) PRIMARY KEY,
    status VARCHAR(20) DEFAULT 'pending',
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($link, $sql)) {
    echo "Table mobile_tokens created successfully.";
} else {
    echo "Error creating table: " . mysqli_error($link);
}

require_once("../DB/DB_close.php");
?>
