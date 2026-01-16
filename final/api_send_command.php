<?php
require_once("../DB/DB_open.php");

// Allow POST requests
$token = $_POST['token'] ?? '';
$command = $_POST['command'] ?? '';
$payload = $_POST['payload'] ?? '';

if (empty($token) || empty($command)) {
    echo json_encode(['status' => 'error', 'msg' => 'Missing params']);
    exit;
}

$token = mysqli_real_escape_string($link, $token);
$command = mysqli_real_escape_string($link, $command);
$payload = mysqli_real_escape_string($link, $payload);

$sql = "INSERT INTO mobile_commands (token, command, payload) VALUES ('$token', '$command', '$payload')";

if (mysqli_query($link, $sql)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'msg' => mysqli_error($link)]);
}

require_once("../DB/DB_close.php");
?>
