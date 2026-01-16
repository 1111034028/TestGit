<?php
header('Content-Type: application/json');
session_start();
require_once("../DB/DB_open.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$anime_name = mysqli_real_escape_string($link, $_POST['anime_name'] ?? '');
$location_name = mysqli_real_escape_string($link, $_POST['location_name'] ?? '');
$latitude = floatval($_POST['latitude'] ?? 0);
$longitude = floatval($_POST['longitude'] ?? 0);
$description = mysqli_real_escape_string($link, $_POST['description'] ?? '');
$user_id = $_SESSION['username'] ?? 'guest';

if (empty($anime_name) || empty($location_name) || $latitude == 0 || $longitude == 0) {
    echo json_encode(['success' => false, 'message' => '缺少必要欄位']);
    exit;
}

$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'img/map_uploads/';
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('map_') . '.' . $extension;
    $target_file = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_path = $target_file;
    }
}

$sql = "INSERT INTO anime_locations (user_id, anime_name, location_name, latitude, longitude, description, image_path) 
        VALUES ('$user_id', '$anime_name', '$location_name', $latitude, $longitude, '$description', '$image_path')";

if (mysqli_query($link, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($link)]);
}

require_once("../DB/DB_close.php");
?>
