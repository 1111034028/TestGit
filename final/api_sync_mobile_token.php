<?php
session_start();
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

$username = $_SESSION['username'] ?? '';
$currentToken = $_GET['token'] ?? '';

if (empty($username)) {
    echo json_encode(['status' => 'not_logged_in']);
    exit;
}

$user_clean = mysqli_real_escape_string($link, $username);
// Find the most recently active token for this user
$sql = "SELECT token FROM mobile_tokens WHERE username = '$user_clean' ORDER BY last_active DESC LIMIT 1";
$res = mysqli_query($link, $sql);

if ($row = mysqli_fetch_assoc($res)) {
    $foundToken = $row['token'];
    if ($foundToken !== $currentToken) {
        echo json_encode(['status' => 'new_token', 'token' => $foundToken]);
    } else {
        echo json_encode(['status' => 'up_to_date']);
    }
} else {
    echo json_encode(['status' => 'no_token']);
}

require_once("../DB/DB_close.php");
?>
