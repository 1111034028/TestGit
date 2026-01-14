<?php
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

$action = $_GET['action'] ?? '';
$token = $_REQUEST['token'] ?? ''; // Accept from GET or POST

if (empty($token)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing token']);
    exit;
}

if ($action === 'push') {
    // Desktop pushing state to server
    // Get raw JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if ($data) {
        $state_json = mysqli_real_escape_string($link, json_encode($data));
        $token_esc = mysqli_real_escape_string($link, $token);
        
        $sql = "UPDATE remote_sessions SET current_state = '$state_json', last_activity = NOW() WHERE session_token = '$token_esc'";
        
        if (mysqli_query($link, $sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($link)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    }

} elseif ($action === 'pull') {
    // Mobile pulling state from server
    $token_esc = mysqli_real_escape_string($link, $token);
    $sql = "SELECT current_state FROM remote_sessions WHERE session_token = '$token_esc' LIMIT 1";
    $result = mysqli_query($link, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo $row['current_state'] ?: '{}'; // Return empty JSON object if null
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Session not found']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

require_once("../DB/DB_close.php");
?>
