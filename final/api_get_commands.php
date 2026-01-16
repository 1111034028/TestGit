<?php
session_start();
require_once("../DB/DB_open.php");

// Computer side checks this with its current Session Token
$token = $_SESSION['mobile_token'] ?? '';

if (empty($token)) {
    echo json_encode([]); // No session, no commands
    exit;
}

$token = mysqli_real_escape_string($link, $token);

// Get unexecuted commands for this token
$sql = "SELECT id, command, payload FROM mobile_commands 
        WHERE token = '$token' AND is_executed = 0 
        ORDER BY created_at ASC";

$result = mysqli_query($link, $sql);
$commands = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $commands[] = $row;
        // Mark as executed immediately (or we could do it in a separate call, but this is simpler)
        mysqli_query($link, "UPDATE mobile_commands SET is_executed = 1 WHERE id = " . $row['id']);
    }
}
echo json_encode($commands);
require_once("../DB/DB_close.php");
?>
