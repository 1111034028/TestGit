<?php
session_start();
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

// Generate a random token if not exists or if refresh requested
if (isset($_GET['refresh']) || empty($_SESSION['mobile_token'])) {
    try {
        $_SESSION['mobile_token'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $_SESSION['mobile_token'] = md5(uniqid(rand(), true));
    }
    
    // If it's a refresh, we should also ensure the old status is reset in DB
    $token = $_SESSION['mobile_token'];
    mysqli_query($link, "DELETE FROM mobile_tokens WHERE token='$token'");
}

$token = $_SESSION['mobile_token'];
$user_id = isset($_SESSION['sno']) ? intval($_SESSION['sno']) : 0;
$username = $_SESSION['username'] ?? '';

// Ensure it exists in DB
$token_clean = mysqli_real_escape_string($link, $token);
$user_clean = mysqli_real_escape_string($link, $username);

$check = mysqli_query($link, "SELECT token FROM mobile_tokens WHERE token='$token_clean'");
if (!$check || mysqli_num_rows($check) == 0) {
    mysqli_query($link, "INSERT INTO mobile_tokens (token, username, user_id, status) VALUES ('$token_clean', '$user_clean', $user_id, 'pending') 
            ON DUPLICATE KEY UPDATE status='pending', username='$user_clean', user_id=$user_id");
} else {
    // Also update username/id if it changed
    mysqli_query($link, "UPDATE mobile_tokens SET username='$user_clean', user_id=$user_id WHERE token='$token_clean'");
}

echo json_encode(['status' => 'success', 'token' => $token]);

require_once("../DB/DB_close.php");
?>
