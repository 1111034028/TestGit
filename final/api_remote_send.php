<?php
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

$token = $_POST['token'] ?? '';
$command = $_POST['command'] ?? '';
$payload = $_POST['payload'] ?? '';

if (empty($token) || empty($command)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

// 1. Verify Token & Update Status to Active if needed
$sql_check = "SELECT status FROM remote_sessions WHERE session_token = '$token' LIMIT 1";
$res_check = mysqli_query($link, $sql_check);

if ($res_check && mysqli_num_rows($res_check) > 0) {
    $row = mysqli_fetch_assoc($res_check);
    if ($row['status'] == 'waiting') {
        // First contact, activate session
        mysqli_query($link, "UPDATE remote_sessions SET status = 'active' WHERE session_token = '$token'");
    } elseif ($row['status'] == 'closed' || $row['status'] == 'expired') {
        echo json_encode(['status' => 'error', 'message' => 'Session expired']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
    exit;
}

// 2. Insert Command
// Escape inputs
$token_esc = mysqli_real_escape_string($link, $token);
$cmd_esc = mysqli_real_escape_string($link, $command);
$pl_esc = mysqli_real_escape_string($link, $payload);

$sql_ins = "INSERT INTO remote_commands (session_token, command, payload, status) VALUES ('$token_esc', '$cmd_esc', '$pl_esc', 'pending')";

if (mysqli_query($link, $sql_ins)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($link)]);
}

require_once("../DB/DB_close.php");
?>
