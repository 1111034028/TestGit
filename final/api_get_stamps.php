<?php
// api_get_stamps.php
// 獲取附近或所有的音域留言
header('Content-Type: application/json');
require_once("../DB/DB_open.php");

// 參數：lat, lng, radius (公尺, 預設 500)
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;
$radius = isset($_GET['radius']) ? intval($_GET['radius']) : 500;

// Check session user for ownership flag
session_start();
$current_user_id = $_SESSION['sno'] ?? 0;

if ($lat != 0 && $lng != 0) {
    // 附近模式 (Nearby Mode)
    $sql = "SELECT m.*, s.title, s.artist, s.file_path, s.id as song_real_id, st.name as user_name,
            ( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) 
            * cos( radians( longitude ) - radians($lng) ) + sin( radians($lat) ) 
            * sin( radians( latitude ) ) ) ) * 1000 AS distance 
            FROM music_marks m
            JOIN songs s ON m.song_id = s.id
            LEFT JOIN students st ON m.user_id = st.sno
            HAVING distance < $radius
            ORDER BY distance ASC, m.created_at DESC
            LIMIT 50";
} else {
    // 全域模式 (Global Map Mode)
    // Optional: Viewport filtering using minLat, maxLat, etc.
    // For now, return latest 100 or all
    $sql = "SELECT m.*, s.title, s.artist, s.file_path, s.id as song_real_id, st.name as user_name
            FROM music_marks m
            JOIN songs s ON m.song_id = s.id
            LEFT JOIN students st ON m.user_id = st.sno
            ORDER BY m.created_at DESC
            LIMIT 100";
}

$result = mysqli_query($link, $sql);
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Flag for ownership
        $row['is_mine'] = ($row['user_id'] == $current_user_id);
        // Prepare cover url
        $row['cover'] = "get_cover.php?id=" . $row['song_real_id'];
        $data[] = $row;
    }
}

echo json_encode($data);

require_once("../DB/DB_close.php");
?>
