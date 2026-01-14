<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['sno'];

if ($action == 'create') {
    $name = trim($_POST['name'] ?? '');
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
    
    if (!empty($name)) {
        $data = ['user_id' => $user_id, 'name' => $name];
        if (db_insert($link, 'playlists', $data)) {
            if ($is_ajax) echo "SUCCESS";
            else header("Location: my_playlists.php");
        } else {
            echo ($is_ajax) ? "ERROR: 資料庫錯誤" : "Error: " . mysqli_error($link);
        }
    } else {
        if ($is_ajax) echo "ERROR: 名稱不能為空";
    }
} elseif ($action == 'add_song') {
    $playlist_id = intval($_POST['playlist_id']);
    $song_id = intval($_POST['song_id']);
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
    
    // 限制為本人歌單
    if (get_playlist_info($link, $playlist_id, $user_id)) {
        $dup = mysqli_query($link, "SELECT id FROM playlist_songs WHERE playlist_id = $playlist_id AND song_id = $song_id");
        if (mysqli_num_rows($dup) > 0) {
            echo ($is_ajax) ? "ERROR: 此歌曲已在播放清單中" : "<script>alert('此歌曲已在播放清單中！'); history.back();</script>";
        } else {
            if (db_insert($link, 'playlist_songs', ['playlist_id' => $playlist_id, 'song_id' => $song_id])) {
                echo ($is_ajax) ? "SUCCESS" : "<script>alert('已加入歌單！'); history.back();</script>";
            } else {
                echo ($is_ajax) ? "ERROR: 加入失敗" : "Error: " . mysqli_error($link);
            }
        }
    } else {
        echo ($is_ajax) ? "ERROR: 無權限" : die("無權限");
    }
} elseif ($action == 'delete') {
    $id = intval($_POST['playlist_id'] ?? $_GET['playlist_id'] ?? 0);
    if ($id > 0) {
        db_delete($link, 'playlists', "id = $id AND user_id = '$user_id'");
    }
    header("Location: my_playlists.php");
    exit;
} elseif ($action == 'remove_song') {
    $playlist_id = intval($_POST['playlist_id']);
    $song_id = intval($_POST['song_id']);
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
    
    if (get_playlist_info($link, $playlist_id, $user_id)) {
        db_delete($link, 'playlist_songs', "playlist_id = $playlist_id AND song_id = $song_id");
        if ($is_ajax) echo "SUCCESS";
        else header("Location: playlist_view.php?id=$playlist_id");
    } else {
        if ($is_ajax) echo "ERROR: 無權限";
    }
} elseif ($action == 'rename') {
    $playlist_id = intval($_POST['playlist_id']);
    $new_name = trim($_POST['name'] ?? '');
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;

    $row = get_playlist_info($link, $playlist_id, $user_id);
    if ($row) {
        if ($row['name'] === 'My Favorites') {
            echo ($is_ajax) ? "ERROR: 系統預設清單無法更名" : "<script>alert('系統預設清單無法更名'); history.back();</script>";
        } elseif (!empty($new_name)) {
            if (db_update($link, 'playlists', ['name' => $new_name], "id = $playlist_id")) {
                echo ($is_ajax) ? "SUCCESS" : "<script>alert('已更名！'); history.back();</script>";
            } else {
                echo ($is_ajax) ? "ERROR: 更新失敗" : "Error";
            }
        } else {
            echo ($is_ajax) ? "ERROR: 名稱不能為空" : "<script>alert('名稱不能為空'); history.back();</script>";
        }
    } else {
        echo ($is_ajax) ? "ERROR: 無權限" : die("無權限");
    }
} elseif ($action == 'pin_playlist' || $action == 'unpin_playlist') {
    $playlist_id = intval($_POST['playlist_id']);
    $val = ($action == 'pin_playlist') ? 1 : 0;
    
    if (get_playlist_info($link, $playlist_id, $user_id)) {
        db_update($link, 'playlists', ['is_pinned' => $val], "id = $playlist_id");
        if (isset($_POST['ajax'])) echo "SUCCESS";
    }
} elseif ($action == 'pin_song' || $action == 'unpin_song') {
    $link_id = intval($_POST['link_id']);
    $val = ($action == 'pin_song') ? 1 : 0;
    
    // 透過 JOIN 安全更新
    $sql = "UPDATE playlist_songs ps JOIN playlists p ON ps.playlist_id = p.id 
            SET ps.is_pinned = $val WHERE ps.id = $link_id AND p.user_id = '$user_id'";
    mysqli_query($link, $sql);
    if (isset($_POST['ajax'])) echo "SUCCESS";
}

require_once("../DB/DB_close.php");
?>
