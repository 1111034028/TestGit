<?php
// 此檔案提供 JSON 格式的登入狀態，供純 HTML 頁面透過 AJAX 查詢
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = [
    'is_logged_in' => false,
    'username' => ''
];

if (isset($_SESSION["login_session"]) && $_SESSION["login_session"] === true) {
    $response['is_logged_in'] = true;
    $response['username'] = $_SESSION["username"];
}

echo json_encode($response);
?>
