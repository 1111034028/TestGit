<?php
// api_save_stamp.php
// 接收 POST 請求儲存音域留言
header('Content-Type: application/json');
session_start();

require_once("../DB/DB_open.php");

if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true) {
    $user_id = $_SESSION['sno'];
} elseif (isset($_POST['token'])) {
    $token = mysqli_real_escape_string($link, $_POST['token']);
    // Check token validity from new table
    $sql_t = "SELECT user_id FROM mobile_tokens WHERE token = '$token' AND status = 'connected' LIMIT 1";
    $res_t = mysqli_query($link, $sql_t);
    if ($res_t && mysqli_num_rows($res_t) > 0) {
        $row_t = mysqli_fetch_assoc($res_t);
        $user_id = $row_t['user_id'];
        // If user_id is null (guest mode), maybe allow it or assign a guest ID? 
        // For now let's assume valid user_id is needed, or default to 0 (system) if null
        if(!$user_id) $user_id = 99999; // Temporary Guest ID
    }
}

if (empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $user_id is already set
    $song_id = isset($_POST['song_id']) ? intval($_POST['song_id']) : 0;
    $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : 0;
    $lng = isset($_POST['lng']) ? floatval($_POST['lng']) : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // 基本驗證
    if ($song_id <= 0 || $lat == 0 || $lng == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data parameters']);
        exit;
    }
    
    $message = mysqli_real_escape_string($link, $message);
    
    // Image Upload Logic (with Deduplication)
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'img/map_uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $tmp_file = $_FILES['image']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        // Calculate hash of file content to detect duplicates
        $file_hash = md5_file($tmp_file);
        $filename = $file_hash . '.' . $extension;
        $target_file = $upload_dir . $filename;
        
        if (file_exists($target_file)) {
            // Duplicate found, reuse existing path
            $image_path = mysqli_real_escape_string($link, $target_file);
        } else {
            // New image, move to dir
            if (move_uploaded_file($tmp_file, $target_file)) {
                $image_path = mysqli_real_escape_string($link, $target_file);
            }
        }
    }

    $message = mysqli_real_escape_string($link, $message);
    $raw_name = isset($_POST['location_name']) ? trim($_POST['location_name']) : "";
    $location_name = $raw_name;

    // Only auto-generate name if user input is empty or looks like coords
    if (empty($raw_name) || preg_match('/^-?\d+\.\d+/', $raw_name)) {
        // ... (Nominatim Logic) ...
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng&zoom=18&addressdetails=1&accept-language=zh-TW";
        $opts = ["http" => ["method" => "GET", "header" => "User-Agent: MusicStampApp/1.0\r\n"]];
        $context = stream_context_create($opts);
        $geo_json = @file_get_contents($url, false, $context);
        $fetched_name = "$lat, $lng"; // default
        if ($geo_json) {
            $geo_data = json_decode($geo_json, true);
            $fetched_name = $geo_data['display_name'] ?? $fetched_name;
        }

        // Use AI to refine fetched name
        require_once("inc/ai_config.php");
        $ai_prompt = "請將以下地址轉化為台灣在地、簡短且具備地標感的打卡地點（10字內）。只給名稱即可。\n地址：$fetched_name";
        
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => OPENAI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => '你是一位台灣地理專家。'],
                ['role' => 'user', 'content' => $ai_prompt]
            ],
            'temperature' => 0.5,
            'max_tokens' => 50
        ]));
        
        $ai_response = curl_exec($ch);
        curl_close($ch);
        
        if ($ai_response) {
            $ai_data = json_decode($ai_response, true);
            if (isset($ai_data['choices'][0]['message']['content'])) {
                $location_name = trim($ai_data['choices'][0]['message']['content'], " \"\n\r\t");
            } else {
                $location_name = $fetched_name;
            }
        } else {
            $location_name = $fetched_name;
        }
    }
    
    $location_name = mysqli_real_escape_string($link, $location_name);
    
    // Check if image_path column exists or handle it gracefully? 
    // Assuming table logic is handled. We insert image_path if present.
    // If column doesn't exist, this query might fail. We should ideally add column first.
    // For now    // --- DATA INTEGRITY CHECK ---
    // Auto-add columns if they don't exist (Self-Healing)
    $chk = mysqli_query($link, "SHOW COLUMNS FROM music_marks LIKE 'image_path'");
    if(mysqli_num_rows($chk) == 0) {
        mysqli_query($link, "ALTER TABLE music_marks ADD COLUMN image_path VARCHAR(255) NULL AFTER message");
    }
    $chk2 = mysqli_query($link, "SHOW COLUMNS FROM music_marks LIKE 'location_name'");
    if(mysqli_num_rows($chk2) == 0) {
        mysqli_query($link, "ALTER TABLE music_marks ADD COLUMN location_name VARCHAR(100) NULL AFTER message");
    }
    // ----------------------------

    
    $sql = "INSERT INTO music_marks (user_id, song_id, latitude, longitude, message, location_name, image_path) 
            VALUES ('$user_id', $song_id, $lat, $lng, '$message', '$location_name', '$image_path')";
            
    if (mysqli_query($link, $sql)) {
        echo json_encode([
            'status' => 'success', 
            'id' => mysqli_insert_id($link),
            'location_name' => $location_name
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($link)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

require_once("../DB/DB_close.php");
?>
