<?php
require_once("../DB/DB_open.php");

$token = $_GET['token'] ?? '';
if (empty($token)) {
    echo json_encode(['status' => 'error']);
    exit;
}

$token = mysqli_real_escape_string($link, $token);
$sql = "SELECT status FROM mobile_tokens WHERE token = '$token'";
$result = mysqli_query($link, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['status' => $row['status']]);
} else {
    echo json_encode(['status' => 'not_found']);
}

require_once("../DB/DB_close.php");
?>
