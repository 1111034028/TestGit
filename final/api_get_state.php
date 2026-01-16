<?php
require_once("../DB/DB_open.php");

$token = $_GET['token'] ?? '';
if (empty($token)) { echo json_encode(null); exit; }

$token = mysqli_real_escape_string($link, $token);
$sql = "SELECT * FROM mobile_state WHERE token = '$token'";
$result = mysqli_query($link, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    echo json_encode(null);
}

require_once("../DB/DB_close.php");
?>
