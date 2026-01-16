<?php
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

$sql = "SELECT * FROM anime_locations ORDER BY created_at DESC";
$result = mysqli_query($link, $sql);

$locations = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = $row;
    }
}

echo json_encode($locations);
require_once("../DB/DB_close.php");
?>
