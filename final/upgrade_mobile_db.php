<?php
require_once("../DB/DB_open.php");
// Update table to include username
$sql = "ALTER TABLE mobile_tokens ADD COLUMN IF NOT EXISTS username VARCHAR(255) AFTER token";
mysqli_query($link, $sql);
$sql2 = "ALTER TABLE mobile_tokens ADD COLUMN IF NOT EXISTS last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
mysqli_query($link, $sql2);
echo "Database updated successfully";
require_once("../DB/DB_close.php");
?>
