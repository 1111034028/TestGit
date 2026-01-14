<?php
session_start();
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = [
        'user_id' => $_SESSION['sno'] ?? null,
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'category' => $_POST['category'],
        'subject' => mb_substr($_POST['message'], 0, 30) . '...',
        'message' => $_POST['message']
    ];

    if (db_insert($link, 'contact_messages', $data)) {
        if (isset($_POST['ajax'])) { echo "SUCCESS"; exit; }
        header("Location: my_messages.php?success=contact");
    } else {
        if (isset($_POST['ajax'])) { echo "error:資料庫寫入失敗"; exit; }
        header("Location: contact_us.php?error=send");
    }
} else {
    header("Location: contact_us.php");
}

require_once("../DB/DB_close.php");
?>
