<?php
require_once("inc/auth_guard.php");

require_once("../DB/DB_open.php");

$action = isset($_POST['action']) ? $_POST['action'] : '';
$user_id = $_SESSION['sno']; // 假設 sno 是 user_id

if ($action == 'create') {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
    
    if (!empty($name)) {
        $sql = "INSERT INTO playlists (user_id, name) VALUES ('$user_id', '$name')";
        if (mysqli_query($link, $sql)) {
            // 成功
            if ($is_ajax) {
                echo "SUCCESS";
            } else {
                header("Location: my_playlists.php");
            }
        } else {
            if ($is_ajax) {
                echo "ERROR: " . mysqli_error($link);
            } else {
                echo "Error: " . mysqli_error($link);
            }
        }
    } else {
        if ($is_ajax) echo "ERROR: 名稱不能為空";
    }
} elseif ($action == 'add_song') {
    $playlist_id = intval($_POST['playlist_id']);
    $song_id = intval($_POST['song_id']);
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
    
    // 檢查擁有權
    $check_sql = "SELECT id FROM playlists WHERE id = $playlist_id AND user_id = '$user_id'";
    if (mysqli_num_rows(mysqli_query($link, $check_sql)) > 0) {
        // 檢查歌曲是否已在播放清單中
        $duplicate_check = "SELECT id FROM playlist_songs WHERE playlist_id = $playlist_id AND song_id = $song_id";
        $duplicate_result = mysqli_query($link, $duplicate_check);
        
        if (mysqli_num_rows($duplicate_result) > 0) {
            // 歌曲已存在
            if ($is_ajax) {
                echo "ERROR: 此歌曲已在播放清單中";
            } else {
                echo "<script>alert('此歌曲已在播放清單中！'); history.back();</script>";
            }
        } else {
            // 加入歌曲
            $sql = "INSERT INTO playlist_songs (playlist_id, song_id) VALUES ($playlist_id, $song_id)";
            mysqli_query($link, $sql);
            
            if ($is_ajax) {
                echo "SUCCESS";
            } else {
                echo "<script>alert('已加入歌單！'); history.back();</script>";
            }
        }
    } else {
        if ($is_ajax) {
            echo "ERROR: 無權限";
        } else {
            die("無權限");
        }
    }
} elseif ($action == 'delete') {
    $id = intval($_POST['playlist_id']);
    $sql = "DELETE FROM playlists WHERE id = $id AND user_id = '$user_id'";
    mysqli_query($link, $sql);
    header("Location: my_playlists.php");
} elseif ($action == 'remove_song') {
    $playlist_id = intval($_POST['playlist_id']);
    $song_id = intval($_POST['song_id']);
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
    
    // 檢查擁有權
    $check_sql = "SELECT id FROM playlists WHERE id = $playlist_id AND user_id = '$user_id'";
    if (mysqli_num_rows(mysqli_query($link, $check_sql)) > 0) {
        $sql = "DELETE FROM playlist_songs WHERE playlist_id = $playlist_id AND song_id = $song_id";
        mysqli_query($link, $sql);
        
        if ($is_ajax) {
            echo "SUCCESS";
        } else {
            header("Location: playlist_view.php?id=$playlist_id");
        }
    } else {
        if ($is_ajax) echo "ERROR: 無權限";
    }
} elseif ($action == 'rename') {
    $playlist_id = intval($_POST['playlist_id']);
    $new_name = mysqli_real_escape_string($link, $_POST['name']);
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;

    // Check ownership
    $check_sql = "SELECT id FROM playlists WHERE id = $playlist_id AND user_id = '$user_id'";
    if (mysqli_num_rows(mysqli_query($link, $check_sql)) > 0) {
        if (!empty($new_name)) {
            $sql = "UPDATE playlists SET name = '$new_name' WHERE id = $playlist_id";
            if (mysqli_query($link, $sql)) {
                if ($is_ajax) {
                    echo "SUCCESS";
                } else {
                    echo "<script>alert('已更名！'); history.back();</script>";
                }
            } else {
                if ($is_ajax) echo "ERROR: " . mysqli_error($link);
                else echo "Error: " . mysqli_error($link);
            }
        } else {
            if ($is_ajax) echo "ERROR: 名稱不能為空";
            else echo "<script>alert('名稱不能為空'); history.back();</script>";
        }
    } else {
        if ($is_ajax) echo "ERROR: 無權限";
        else die("無權限");
    }
}


require_once("../DB/DB_close.php");
