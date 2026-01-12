<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    echo "<script>";
    echo "alert('未登入無法觀看 " . $currentPage . " 網頁');";
    echo "window.location.href = '/s1111034028/final/login.php';";
    echo "</script>";
    exit;
}
?>
