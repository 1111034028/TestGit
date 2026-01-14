<?php
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

// 1. Generate Token
try {
    $token = bin2hex(random_bytes(16)); // 32 chars
} catch (Exception $e) {
    $token = md5(uniqid(rand(), true));
}

// 2. Insert into DB
$sql = "INSERT INTO remote_sessions (session_token, status) VALUES ('$token', 'waiting')";

if (mysqli_query($link, $sql)) {
    echo json_encode(['status' => 'success', 'token' => $token]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => mysqli_error($link)]);
}

require_once("../DB/DB_close.php");
?>
