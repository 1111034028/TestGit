<?php
session_start();
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

$user_id = $_SESSION['sno'] ?? 0;
if ($user_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$action = $_POST['action'] ?? '';

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

// Check ownership
$check = mysqli_query($link, "SELECT user_id FROM music_marks WHERE id = $id");
if (!$check || mysqli_num_rows($check) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Not found']);
    exit;
}
$row = mysqli_fetch_assoc($check);
if ($row['user_id'] != $user_id) {
    // Check if admin? (Optional)
    echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
    exit;
}

if ($action === 'delete') {
    $sql = "DELETE FROM music_marks WHERE id = $id";
    if (mysqli_query($link, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($link)]);
    }
} elseif ($action === 'edit') {
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $location_name = isset($_POST['location_name']) ? trim($_POST['location_name']) : '';
    
    $message = mysqli_real_escape_string($link, $message);
    $location_name = mysqli_real_escape_string($link, $location_name);
    
    // Image Upload Logic (Deduplication)
    $image_update_sql = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'img/map_uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $tmp_file = $_FILES['image']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_hash = md5_file($tmp_file);
        $filename = $file_hash . '.' . $extension;
        $target_file = $upload_dir . $filename;
        
        if (!file_exists($target_file)) {
            move_uploaded_file($tmp_file, $target_file);
        }
        $safe_path = mysqli_real_escape_string($link, $target_file);
        $image_update_sql = ", image_path = '$safe_path'";
    }

    $sql = "UPDATE music_marks SET 
            message = '$message', 
            location_name = '$location_name' 
            $image_update_sql 
            WHERE id = $id";
            
    if (mysqli_query($link, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($link)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}

require_once("../DB/DB_close.php");
?>
