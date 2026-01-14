<?php
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo json_encode([]);
    exit;
}

// 1. Fetch Pending Commands
$commands = [];
$ids_to_update = [];

$sql = "SELECT * FROM remote_commands WHERE session_token = '$token' AND status = 'pending' ORDER BY created_at ASC";
$result = mysqli_query($link, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $commands[] = $row;
        $ids_to_update[] = $row['id'];
    }
}

// 2. Mark as Executed
if (!empty($ids_to_update)) {
    $ids_str = implode(',', $ids_to_update);
    mysqli_query($link, "UPDATE remote_commands SET status = 'executed' WHERE id IN ($ids_str)");
    
    // Also touch the session to keep it alive (optional, if we had expiry logic)
    // mysqli_query($link, "UPDATE remote_sessions SET last_activity = NOW() WHERE session_token = '$token'");
}

// 3. Return Commands
echo json_encode($commands);

require_once("../DB/DB_close.php");
?>
