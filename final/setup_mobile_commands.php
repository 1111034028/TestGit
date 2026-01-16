<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

$sql = "CREATE TABLE IF NOT EXISTS mobile_commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64),
    command VARCHAR(50),
    payload TEXT,
    is_executed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($link, $sql)) {
    echo "Table mobile_commands created successfully.";
} else {
    echo "Error: " . mysqli_error($link);
}

require_once("../DB/DB_close.php");
?>
