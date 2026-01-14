<?php
// 此檔案用於確保使用者已登入
// 若未登入，將重導向至登入頁面

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php?error=auth");
    exit;
}

