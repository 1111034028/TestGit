<?php
// api_delete_stamp.php
header('Content-Type: application/json');
session_start();
require_once("../DB/DB_open.php");

if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stamp_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $user_id = $_SESSION['sno'];
    
    if ($stamp_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        exit;
    }
    
    // Verify ownership
    $check_sql = "SELECT user_id FROM music_marks WHERE id = $stamp_id";
    $result = mysqli_query($link, $check_sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['user_id'] !== $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
            exit;
        }
        
        // Delete
        $del_sql = "DELETE FROM music_marks WHERE id = $stamp_id";
        if (mysqli_query($link, $del_sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($link)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Stamp not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
require_once("../DB/DB_close.php");
?>
