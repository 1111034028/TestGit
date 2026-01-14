<?php
/**
 * Database Helper Functions for Music Stream
 */

/**
 * Get all playlists for a specific user
 */
function get_user_playlists($link, $user_id) {
    $user_id = mysqli_real_escape_string($link, $user_id);
    $sql = "SELECT * FROM playlists WHERE user_id = '$user_id' 
            ORDER BY CASE WHEN name = 'My Favorites' THEN 1 ELSE 0 END DESC, is_pinned DESC, created_at DESC";
    return mysqli_query($link, $sql);
}

/**
 * Get info for a specific playlist owned by a user
 */
function get_playlist_info($link, $playlist_id, $user_id) {
    $playlist_id = intval($playlist_id);
    $user_id = mysqli_real_escape_string($link, $user_id);
    $sql = "SELECT * FROM playlists WHERE id = $playlist_id AND user_id = '$user_id'";
    $res = mysqli_query($link, $sql);
    return mysqli_fetch_assoc($res);
}

/**
 * Get songs for a specific playlist
 */
function get_playlist_songs($link, $playlist_id) {
    $playlist_id = intval($playlist_id);
    $sql = "SELECT s.*, ps.id as link_id, ps.is_pinned FROM songs s 
            JOIN playlist_songs ps ON s.id = ps.song_id 
            WHERE ps.playlist_id = $playlist_id 
            ORDER BY ps.is_pinned DESC, ps.id DESC";
    return mysqli_query($link, $sql);
}

/**
 * Count songs in a playlist
 */
function count_playlist_songs($link, $playlist_id) {
    $playlist_id = intval($playlist_id);
    $sql = "SELECT COUNT(*) as cnt FROM playlist_songs WHERE playlist_id = $playlist_id";
    $res = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['cnt'] ?? 0;
}

/**
 * Search playlists by name or creator
 */
function search_playlists($link, $keyword) {
    $keyword = mysqli_real_escape_string($link, $keyword);
    $sql = "SELECT p.*, s.name as creator_name FROM playlists p 
            JOIN students s ON p.user_id = s.sno 
            WHERE (p.name LIKE '%$keyword%' OR s.name LIKE '%$keyword%') 
            AND p.name != 'My Favorites'
            ORDER BY p.created_at DESC";
    return mysqli_query($link, $sql);
}

/**
 * Get a specific contact message
 */
function get_contact_message($link, $id, $user_id = null) {
    $id = intval($id);
    $sql = "SELECT * FROM contact_messages WHERE id = $id";
    if ($user_id) {
        $user_id = mysqli_real_escape_string($link, $user_id);
        $sql .= " AND user_id = '$user_id'";
    }
    $res = mysqli_query($link, $sql);
    return mysqli_fetch_assoc($res);
}

/**
 * Get replies for a contact message
 */
function get_message_replies($link, $message_id) {
    $message_id = intval($message_id);
    $sql = "SELECT * FROM contact_replies WHERE message_id = $message_id ORDER BY created_at ASC";
    return mysqli_query($link, $sql);
}

/**
 * Get user info by username
 */
function get_user_info($link, $username) {
    if (!$username) return null;
    $username = mysqli_real_escape_string($link, $username);
    $sql = "SELECT * FROM students WHERE username = '$username'";
    $res = mysqli_query($link, $sql);
    return mysqli_fetch_assoc($res);
}

/**
 * Check if a user has admin role
 */
function is_admin($link, $username) {
    if (!$username) return false;
    $user = get_user_info($link, $username);
    return ($user && ($user['role'] ?? 'user') === 'admin');
}

/**
 * Get total count of a table
 */
function get_total_count($link, $table, $where = "") {
    $sql = "SELECT COUNT(*) as total FROM $table";
    if ($where) $sql .= " WHERE $where";
    $res = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($res);
    return intval($row['total'] ?? 0);
}

/**
 * Generic Paginated Fetch
 */
function db_get_paginated($link, $table, $page, $limit, $order = "", $where = "") {
    $page = max(1, intval($page));
    $limit = max(1, intval($limit));
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT * FROM $table";
    if ($where) $sql .= " WHERE $where";
    if ($order) $sql .= " ORDER BY $order";
    $sql .= " LIMIT $offset, $limit";
    
    return mysqli_query($link, $sql);
}

/**
 * Generic Insert
 * @param array $data Associative array of column => value
 */
function db_insert($link, $table, $data) {
    $columns = implode(", ", array_keys($data));
    $values = [];
    foreach ($data as $val) {
        if (is_null($val)) $values[] = "NULL";
        else $values[] = "'" . mysqli_real_escape_string($link, $val) . "'";
    }
    $values_str = implode(", ", $values);
    $sql = "INSERT INTO $table ($columns) VALUES ($values_str)";
    return mysqli_query($link, $sql);
}

/**
 * Generic Update
 * @param array $data Associative array of column => value
 */
function db_update($link, $table, $data, $where) {
    $sets = [];
    foreach ($data as $col => $val) {
        if (is_null($val)) $sets[] = "$col = NULL";
        else $sets[] = "$col = '" . mysqli_real_escape_string($link, $val) . "'";
    }
    $sets_str = implode(", ", $sets);
    $sql = "UPDATE $table SET $sets_str WHERE $where";
    return mysqli_query($link, $sql);
}

/**
 * Generic Delete
 */
function db_delete($link, $table, $where) {
    if (empty($where)) return false; // Safety
    $sql = "DELETE FROM $table WHERE $where";
    return mysqli_query($link, $sql);
}
?>
